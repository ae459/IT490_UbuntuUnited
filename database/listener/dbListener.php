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
