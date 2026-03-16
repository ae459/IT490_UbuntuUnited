const express = require("express");
const mysql = require("mysql2/promise");
const axios = require("axios");
const twilio = require("twilio");
const path = require("path");
require("dotenv").config({ path: path.join(__dirname, ".env"), override: true });

const app = express();
app.use(express.json());

// If your PHP page calls this from a different origin/port.
app.use((req, res, next) => {
  res.header("Access-Control-Allow-Origin", "*");
  res.header("Access-Control-Allow-Headers", "Content-Type");
  res.header("Access-Control-Allow-Methods", "GET,POST,OPTIONS");
  if (req.method === "OPTIONS") return res.sendStatus(204);
  next();
});

const dbHost = (process.env.DB_HOST || "127.0.0.1").trim();
const dbUser = (process.env.DB_USER || "").trim();
const dbPassword = process.env.DB_PASSWORD || "";
const dbName = (process.env.DB_NAME || "ticketdb").trim();
const notifyRequireDb = String(process.env.NOTIFY_REQUIRE_DB || "true").toLowerCase() === "true";

const pool = mysql.createPool({
  host: dbHost,
  port: Number(process.env.DB_PORT || 3306),
  user: dbUser,
  password: dbPassword,
  database: dbName,
  waitForConnections: true,
  connectionLimit: 10
});

const smsClient = twilio(
  process.env.TWILIO_ACCOUNT_SID || "",
  process.env.TWILIO_AUTH_TOKEN || ""
);

async function sendMailjetEmail({ to, subject, text }) {
  const apiKey = process.env.MAILJET_API_KEY;
  const apiSecret = process.env.MAILJET_API_SECRET;
  const fromEmail = process.env.MAILJET_FROM_EMAIL;
  const fromName = process.env.MAILJET_FROM_NAME || "UbuntuUnited";
  const apiBase = (process.env.MAILJET_API_BASE || "https://api.mailjet.com").replace(/\/+$/, "no-reply.UbunutUnited.com");

  if (!apiKey || !apiSecret) {
    throw new Error("Mailjet credentials are missing");
  }
  if (!fromEmail) {
    throw new Error("MAILJET_FROM_EMAIL is missing");
  }

  const payload = {
    Messages: [
      {
        From: { Email: fromEmail, Name: fromName },
        To: [{ Email: to }],
        Subject: subject,
        TextPart: text
      }
    ]
  };

  const response = await axios.post(`${apiBase}/v3.1/send`, payload, {
    auth: {
      username: apiKey,
      password: apiSecret
    },
    headers: {
      "Content-Type": "application/json"
    },
    timeout: 15000
  });

  const message = response.data?.Messages?.[0] || {};
  const status = message.Status || "unknown";
  if (status.toLowerCase() !== "success") {
    throw new Error(`Mailjet send failed with status: ${status}`);
  }

  return {
    provider: "mailjet",
    status,
    messageId: message?.To?.[0]?.MessageUUID || null
  };
}

function normalizeChannel(channel) {
  if (!channel) return "";
  const c = String(channel).trim().toLowerCase();
  if (c === "email") return "email";
  if (c === "text" || c === "sms") return "text";
  return "";
}

// Enforce "text or email, but not both".
function validateSingleChannel({ channel, toEmail, toPhone }) {
  const c = normalizeChannel(channel);
  if (!c) return { ok: false, message: "channel must be 'email' or 'text'" };

  const hasEmail = Boolean(toEmail && String(toEmail).trim());
  const hasPhone = Boolean(toPhone && String(toPhone).trim());

  if (c === "email" && hasPhone) {
    return { ok: false, message: "For channel=email, do not include phone" };
  }
  if (c === "text" && hasEmail) {
    return { ok: false, message: "For channel=text, do not include email" };
  }

  return { ok: true, channel: c };
}

async function resolveRecipient(userId, channel, overrides) {
  // Prefer explicit destination from request; fallback to users table.
  if (channel === "email" && overrides.toEmail) return { to: overrides.toEmail };
  if (channel === "text" && overrides.toPhone) return { to: overrides.toPhone };

  // Requires users.email and users.phone columns. If missing, error clearly.
  const [rows] = await pool.execute(
    "SELECT email, phone FROM users WHERE id = ? LIMIT 1",
    [userId]
  );
  if (!rows.length) {
    throw new Error(`User ${userId} not found`);
  }

  if (channel === "email") {
    if (!rows[0].email) throw new Error("Recipient email not found for user");
    return { to: rows[0].email };
  }

  if (!rows[0].phone) throw new Error("Recipient phone not found for user");
  return { to: rows[0].phone };
}

async function sendOne({ id, user_id, channel, title, message, toEmail, toPhone, skipDbUpdate = false }) {
  const ch = normalizeChannel(channel);
  if (!ch) throw new Error("Invalid channel");

  const recipient = await resolveRecipient(user_id, ch, { toEmail, toPhone });

  if (ch === "email") {
    const mailjetResult = await sendMailjetEmail({
      to: recipient.to,
      subject: title,
      text: message
    });
    if (!skipDbUpdate && id != null) {
      await pool.execute(
        "UPDATE notifications SET status='SENT', sent_at=NOW() WHERE id=?",
        [id]
      );
    }
    return { sent: true, provider: "mailjet", messageId: mailjetResult.messageId, to: recipient.to };
  }

  const sms = await smsClient.messages.create({
    from: process.env.TWILIO_FROM_NUMBER,
    to: recipient.to,
    body: `${title}: ${message}`
  });
  if (!skipDbUpdate && id != null) {
    await pool.execute(
      "UPDATE notifications SET status='SENT', sent_at=NOW() WHERE id=?",
      [id]
    );
  }
  return { sent: true, provider: "twilio", sid: sms.sid, to: recipient.to };
}

// Create + send immediately.
app.post("/api/notifications/send", async (req, res) => {
  try {
    const { user_id, channel, title, message, toEmail, toPhone } = req.body || {};
    if (!user_id || !title || !message) {
      return res.status(400).json({ success: false, message: "user_id, title, message are required" });
    }

    const valid = validateSingleChannel({ channel, toEmail, toPhone });
    if (!valid.ok) return res.status(400).json({ success: false, message: valid.message });

    let insertedId = null;
    let skipDbUpdate = false;

    if (notifyRequireDb) {
      const [inserted] = await pool.execute(
        "INSERT INTO notifications (user_id, channel, title, message, status) VALUES (?, ?, ?, ?, 'PENDING')",
        [Number(user_id), valid.channel, String(title), String(message)]
      );
      insertedId = inserted.insertId;
    } else {
      try {
        const [inserted] = await pool.execute(
          "INSERT INTO notifications (user_id, channel, title, message, status) VALUES (?, ?, ?, ?, 'PENDING')",
          [Number(user_id), valid.channel, String(title), String(message)]
        );
        insertedId = inserted.insertId;
      } catch (dbErr) {
        // In no-DB mode, still allow direct provider send with explicit recipient.
        skipDbUpdate = true;
        console.warn("DB unavailable in no-DB mode:", dbErr.message);
      }
    }

    const result = await sendOne({
      id: insertedId,
      user_id: Number(user_id),
      channel: valid.channel,
      title: String(title),
      message: String(message),
      toEmail,
      toPhone,
      skipDbUpdate
    });

    return res.json({ success: true, notification_id: insertedId, result, db_persisted: insertedId != null });
  } catch (err) {
    console.error("send error:", err.message);
    return res.status(500).json({ success: false, message: err.message });
  }
});

// Process existing pending notifications from DB.
app.post("/api/notifications/process-pending", async (req, res) => {
  try {
    const [rows] = await pool.query(
      "SELECT id, user_id, channel, title, message FROM notifications WHERE status='PENDING' ORDER BY created_at ASC LIMIT 25"
    );

    const results = [];
    for (const row of rows) {
      try {
        const r = await sendOne(row);
        results.push({ id: row.id, success: true, ...r });
      } catch (e) {
        results.push({ id: row.id, success: false, error: e.message });
      }
    }

    res.json({ success: true, processed: rows.length, results });
  } catch (err) {
    console.error("process error:", err.message);
    res.status(500).json({ success: false, message: err.message });
  }
});

// Quick health check.
app.get("/api/health", async (_req, res) => {
  try {
    await pool.query("SELECT 1");
    res.json({ success: true, service: "notifications-backend", db: "ok" });
  } catch (err) {
    res.status(500).json({ success: false, db: "error", message: err.message });
  }
});

const port = Number(process.env.PORT || 3001);
app.listen(port, () => {
  console.log(`notifications backend listening on http://localhost:${port}`);
});