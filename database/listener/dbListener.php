<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);

require_once(__DIR__ . "/db.php");
require_once(__DIR__ . "/../../rabbitmqphp_example/rabbitMQLib.inc");

function makeSessionKey() {
	return bin2hex(random_bytes(32));
}

function registerUser($pdo, $req) {
	$username = $req["username"];
	$password = $req["password"];

	$check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
	$check->execute([$username]);
	$exists = $check->fetch(PDO::FETCH_ASSOC);

	if ($exists) {
        return [
            "type" => "register_result",
            "request_id" => $req["request_id"],
            "success" => false,
            "message" => "Username already exists"
        ];
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);

    $ins = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
    $ins->execute([$username, $hash]);

    return [
        "type" => "register_result",
        "request_id" => $req["request_id"],
        "success" => true,
        "message" => "User created"
    ];
}

function loginUser($pdo, $req) {
	$username = $req["username"];
    $password = $req["password"];

    $q = $pdo->prepare("SELECT id, password_hash FROM users WHERE username = ?");
    $q->execute([$username]);
    $user = $q->fetch(PDO::FETCH_ASSOC);

	if (!$user) {
        return [
            "type" => "login_result",
            "request_id" => $req["request_id"],
            "success" => false,
            "message" => "Invalid username or password"
        ];
    }

$ok = password_verify($password, $user["password_hash"]);
if (!$ok) {
	return [
		"type" => "login_result",
		"request_id" => $req["request_id"],
		"success" => false,
		"message" => "Invalid username or password"
	];
}

$sessionKey = makeSessionKey();
$expiresAt = date("Y-m-d H:i:s", time() + 3600);

$ins = $pdo->prepare("INSERT INTO sessions (user_id, session_key, expires_at, is_active) VALUES (?, ?, ?, 1)");
$ins->execute([(int)$user["id"], $sessionKey, $expiresAt]);
return [
        "type" => "login_result",
        "request_id" => $req["request_id"],
        "success" => true,
        "session_key" => $sessionKey,
        "message" => "Login success"
    ];
}

function validateSession($pdo, $req) {
	$key = $req["session_key"];

	$q = $pdo->prepare("
        SELECT u.username
        FROM sessions s
        JOIN users u ON u.id = s.user_id
        WHERE s.session_key = ? AND s.is_active = 1 AND s.expires_at > NOW()
        LIMIT 1
    ");
    $q->execute([$key]);
    $row = $q->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
	return [
		"type" => "validate_session_result",
		"request_id" => $req["request_id"],
		"valid" => false
	];
}

return [
	"type" => "validate_session_result",
	"request_id" => $req["request_id"],
	"valid" => true,
	"username" => $row["username"]
    ];
}

function getUserIDFromSession($pdo, $sessionKey) {
	$q = $pdo->prepare("SELECT user_id FROM sessions WHERE session_key=? AND is_active=1 AND expires_at > NOW() LIMIT 1");
	$q->execute([$sessionKey]);
	$row = $q->fetch(PDO::FETCH_ASSOC);

	if (!$row) {
		return 0;
	}
	return (int)$row["user_id"];
}

function searchArtists($pdo, $req) {
	$term = "%" . $req["term"] . "%";

	$q = $pdo->prepare("SELECT id, name, genre FROM artists WHERE name LIKE ? ORDER BY name ASC LIMIT 25");
	$q->execute([$term]);
	$rows = $q->fetchALL(PDO::FETCH_ASSOC);
	
	return ["success"=>true, "artists"=>$rows];
}

function searchVenues($pdo, $req) {
	$term =  "%" . $req["term"] . "%";
	
	$q = $pdo->prepare("SELECT id, name, city, state FROM venues WHERE name LIKE ? ORDER BY name ASC LIMIT 25");
	$q->execute([$term]);
	$rows = $q->fetchALL(PDO::FETCH_ASSOC);

	return ["success"=>true, "venues"=>$rows];
}

function searchEvents($pdo, $req) {
	$term = "%" . $req["term"] . "%";

	$q = $pdo->prepare("
		SELECT e.id, e.title, e.event_date, e.status, v.name AS venue_name
		FROM events e
		LEFT JOIN venues v ON v.id = e.venue_id
		WHERE e.title LIKE ?
		ORDER BY e.event_date ASC
		LIMIT 25
	");
	$q->execute([$term]);
	$rows = $q->fetchALL(PDO::FETCH_ASSOC);

	return ["success"=>true, "events"=>$rows];
}

function addReview($pdo, $req) {
	$userId = getUserIdFromSession($pdo, $req["session_key"]);
	if ($userId == 0) {
		return ["success"=>false, "message"=>"Invalid session"];
	}

	$eventId = (int)$req["event_id"];
	$rating = (int)$req["rating"];
	$text = $req["review_text"];

	if ($rating < 1 || $rating > 5) {
		return ["success"=>false, "message"=>"Rating must be 1 to 5"];
	}

	$q = $pdo->prepare("
		INSERT INTO reviews (user_id, event_id, rating, review_text)
		VALUES (?, ?, ?, ?)
		ON DUPLICATE KEY UPDATE rating=VALUES(rating), review_text=VALUES(review_text), updated_at=NOW()
	");
	$q->execute([$userId, $eventId, $rating, $text]);

	return ["success"=>true, "message"=>"Review saved"];
}

function getEventReviews($pdo, $req) {
	$eventId = (int)$req["event_id"];

	$q = $pdo->prepare("
		SELECT r.rating, r.review_text, r.created_at, u.username
		FROM reviews r
		JOIN users u ON u.id = r.user_id
		WHERE r.event_id=?
		ORDER BY r.created_at DESC
		LIMIT 50
	");
	$q->execute([$eventId]);
	$rows = $q->fetchALL(PDO::FETCH_ASSOC);

	return ["success"=>true, "reviews"=>$rows];
}

function getRecommendations($pdo, $req) {
	$userId = getUserIdFromSession($pdo, $req["session_key"]);
	if ($userId == 0) {
		return ["success"=>false, "message"=>"Invalid session"];
	}
	
	$q = $pdo->prepare("
		SELECT DISTINCT ea.artist_id
		FROM reviews r
		JOIN event_artists ea on ea.event_id = r.event_id
		WHERE r.user_id=? AND r.rating>=4
		LIMIT 10
	");
	$q->execute([$userId]);
	$liked = $q->fetchALL(PDO::FETCH_ASSOC);

	if (count($liked) == 0) {
		$popular = $pdo->query("
			SELECT id, title, event_date, status
			FROM events
			ORDER BY event_date ASC
			LIMIT 10
		")->fetchALL(PDO::FETCH_ASSOC);

		return ["success"=>true, "events"=>$popular];
	}

	$artistIds = [];
	foreach ($liked as $a) {
		$artistIds[] = (int)$a["artist_id"];
	}

	$placeholders = implode(",", array_fill(0, count($artistIds), "?"));

	$q2 = $pdo->prepare("
		SELECT DISTINCT e.id, e.title, e.event_date, e.status
		FROM events e
		JOIN event_artists ea ON ea.event_id=e.id
		WHERE ea.artist_id IN ($placeholders)
		ORDER BY e.event_date ASC
		LIMIT 15
	");
	$q2->execute($artistIds);
	$rows = $q2->fetchALL(PDO::FETCH_ASSOC);
	
	return ["success"=>true, "events"=>$rows];
}

function addFriend($pdo, $req) {
	$userId = getUserIdFromSession($pdo, $req["session_key"]);
	if ($userId == 0) return ["success"=>false, "message"=>"Cannot add yourself"];

	$q = $pdo->prepare("INSERT IGNORE INTO friends (user_id, friend_user_id, status) VALUES (?, ?, 'PENDING')");
	$q->execute([$userId, $friendId]);

	return ["success"=>true, "message"=>"Friend request sent"];
}

function acceptFriend($pdo, $req) {
	$userId = getUserIdFromSession($pdo, $req["session_key"]);
	if ($userId == 0) return ["success"=>false, "message"=>"Invalid session"];

	$friendId = (int)$req["friend_user_id"];

	$q = $pdo->prepare("UPDATE friends SET status='ACCEPTED' WHERE user_id=? AND friend_user_id=?");
	$q->execute([$friendId, $userId]);

	$q2 = $pdo->prepare("INSERT IGNORE INTO friends (user_id, friend_user_id, status) VALUES (?, ?, 'ACCEPTED')");
	$q2->execute([$userId, $friendId]);

	return ["success"=>true, "message"=>"Friend accepted"];
}

function listFriends($pdo, $req) {
	$userId = getUserIdFromSession($pdo, $req["session_key"]);
	if ($userId == 0) return ["success"=>false, "message"=>"Invalid session"];

	$q = $pdo->prepare("
		SELECT f.friend_user_id, u.username, f.status
		FROM friends f
		JOIN users u on u.id = f.friend_user_id
		WHERE f.user_id=?
		ORDER BY u.username ASC
	");
	$q->execute([$userId]);
	$rows = $q->fetchALL(PDO::FETCH_ASSOC);

	return ["success"=>true, "friends"=>$rows];
}

function createInvite($pdo, $req) {
	$inviterId = getUserIdFromSession($pdo, $req["session_key"]);
	if ($inviterId == 0) return ["success"=>false, "message"=>"Invalid session"];

	$eventId = (int)$req["event_id"];
	$inviteeUserId = isset($req["invitee_user_id"]) ? (int)$req["invitee_user_id"] : null;

	$token = bin2hex(random_bytes(16));
	$expiresAt = date("Y-m-d H:i:s", time() + 86400);

	$q = $pdo->prepare("
		INSERT INTO event_invites (event_id, inviter_user_id, invitee_user_id, invite_token, expires_at)
		VALUES (?, ?, ?, ?, ?)
	");
	$q->execute([$eventId, $inviterId, $inviteeUserId, $token, $expiresAt]);

	return ["success"=>true, "invite_token"=>$token];
}

function acceptInviteByToken($pdo, $req) {
	$userId = getUserIdFromSession($pdo, $req["session_key"]);
	if ($userId == 0) return ["success"=>false, "message"=>"Invalid session"];

	$token = $req["invite_token"];

	$q = $pdo->prepare("SELECT id, expires_at, status FROM event_invites WHERE invite_token=? LIMIT 1");
	$q->execute([$token]);
	$invite = $q->fetch(PDO::FETCH_ASSOC);

	if (!$invite) return ["success"=>false, "message"=>"Invite not found"];
	if ($invite["status"] != "SENT") return ["success"=>false, "message"=>"Invite already used"];
	if ($invite["expires_at"] != null && strtotime($invite["expires_at"]) < time()) return ["success"=>false, "message"=>"Invite expired"];
	
	$u = $pdo->prepare("UPDATE event_invites SET invitee_user_id=?, status='ACCEPTED' WHERE id=?");
	$u->execute([$userId, (int)$invite["id"]]);
	
	return ["success"=>true, "message"=>"Invite accepted"];
}

function createNotification($pdo, $req) {
	$userId = (int)$req["user_id"];
	$channel = $req["channel"];
	$title = $req["title"];
	$message = $req["message"];

	$q = $pdo->prepare("
		INSERT INTO notifications (user_id, channel, title, message, status)
		VALUES (?, ?, ?, ?, 'PENDING')
	");
	$q->execute([$userId, $channel, $title, $message]);

	return ["success"=>true, "message"=>"Notification saved"];
}

function getPendingNotifications($pdo, $req) {
	$q = $pdo->query("
		SELECT id, user_id, channel, title, message
		FROM notifications
		WHERE status='PENDING'
		ORDER BY created_at ASC
		LIMIT 25
	");
	$rows = $q->fetchALL(PDO::FETCH_ASSOC);

	return ["success"=>true, "notifications"=>$rows];
}

function markNotificationSent($pdo, $req) {
	$id = (int)$req["notification_id"];

	$q = $pdo->prepare("UPDATE notifications SET status='SENT', sent_at=NOW() WHERE id=?");
	$q->execute([$id]);

	return ["success"=>true, "message"=>"Marked sent"];

}

function bookEvent($pdo, $req) {
	$userId = getUserIdFromSession($pdo, $req["session_key"]);
	if ($userId == 0) {
		return ["success"=>false, "message"=>"Invalid session"];
	}

	$eventId = (int)$req["event_id"];
	$qty = (int)$req["qty"];
	if ($qty < 1) $qty = 1;

	$q = $pdo->prepare("INSERT INTO bookings (user_id, event_id, qty) VALUES (?, ?, ?)");
	$q->execute([$userId, $eventId, $qty]);

	return ["success"=>true, "message"=>"Event booked"];
}

function listMyBookings($pdo, $req) {
	$userId = getUserIdFromSession($pdo, $req["session_key"]);
	if ($userId == 0) {
		return ["success"=>false, "message"=>"Invalid session"];
	}

	$q = $pdo->prepare("
		SELECT b.id, b.qty, b.created_at, e.id AS event_id, e.title, e.event_date
		FROM bookings b
		JOIN events e on e.id = b.event_id
		WHERE b.user_id = ?
		ORDER BY b.created_at DESC
		LIMIT 50
	");
	$q->execute([$userId]);
	$rows = $q->fetchALL(PDO::FETCH_ASSOC);

	return ["success"=>true, "bookings"=>$rows];
}

function requestProcessor($req) {
	echo "REQUEST RECIEVED\n";
	print_r($req);

	$pdo = getDb();

	if (!isset($req["type"])) {
		return ["type"=>"error","success"=>false,"message"=>"Missing type"];
	}

	if (!isset($req["request_id"])) {
		$req["request_id"] = "no_id";
	}

	if ($req["type"] == "register") {
		return registerUser($pdo, $req);
	}

	if ($req["type"] == "login") {
		return loginUser($pdo, $req);
	}

	if ($req["type"] == "validate_session") {
		return validateSession($pdo, $req);
	}

	if ($req["type"] == "search_artists") return searchArtists($pdo, $req);
	if ($req["type"] == "search_venues") return searchVenues($pdo, $req);
	if ($req["type"] == "search_events") return searchEvents($pdo, $req);

	if ($req["type"] == "add_review") return addReview($pdo, $req);
	if ($req["type"] == "get_events_reviews") return getEventReviews($pdo, $req);

	if ($req["type"] == "get_recommendations") return getRecommendations($pdo, $req);

	if ($req["type"] == "add_friend") return addFriend($pdo, $req);
	if ($req["type"] == "accept_friend") return acceptFriend($pdo, $req);
	if ($req["type"] == "list_friends") return listFriends($pdo, $req);

 	if ($req["type"] == "create_invite") return createInvite($pdo, $req);
	if ($req["type"] == "accept_invite_by_token") return acceptInviteByToken($pdo, $req);

	if ($req["type"] == "create_notification") return createNotification($pdo, $req);
	if ($req["type"] == "get_pending_notifications") return getPendingNotifications($pdo, $req);
	if ($req["type"] == "mark_notification_sent") return markNotificationSent($pdo, $req);

	if ($req["type"] == "book_event") return bookEvent($pdo, $req);
	if ($req["type"] == "list_my_bookings") return listMyBookings($pdo, $req);

return [
        "type" => "error",
        "request_id" => $req["request_id"],
        "success" => false,
        "message" => "Unknown request type"
    ];
}

$iniPath = __DIR__ . "/../../rabbitmqphp_example/testRabbitMQ.ini";

$server = new rabbitMQServer($iniPath, "testServer");
echo "DB Listener running...\n";
$server->process_requests("requestProcessor");
