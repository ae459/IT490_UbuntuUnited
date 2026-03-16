# Tonight's Session Changes

Generated from current git working tree/diff on 2026-03-13.

## 1) `database/listener/db.php`
```diff
diff --git a/database/listener/db.php b/database/listener/db.php
index c850af2..a36648a 100644
--- a/database/listener/db.php
+++ b/database/listener/db.php
@@ -1,7 +1,7 @@
 <?php
 
 function getDb() {
-	$host = "10.185.132.84";
+	$host = "10.185.132.28";
 	$db   = "ticketdb";
 	$user = "appuser";
 	$pass = "app123";
```

## 2) `database/listener/dbListener.php`
```diff
diff --git a/database/listener/dbListener.php b/database/listener/dbListener.php
index 4203b9f..a9e6dd0 100644
--- a/database/listener/dbListener.php
+++ b/database/listener/dbListener.php
@@ -1,7 +1,8 @@
 <?php
 error_reporting(E_ALL);
-ini_set("display_errors", 1);
-ini_set("display_startup_errors", 1);
+// Keep runtime errors out of response/output in production.
+ini_set("display_errors", 0);
+ini_set("display_startup_errors", 0);
@@ -406,17 +407,32 @@ function listMyBookings($pdo, $req) {
 
 
 function requestProcessor($req) {
-	echo "REQUEST RECEIVED\n";
-	print_r($req);
+	// Avoid leaking request payloads (passwords/session keys/API params).
+	$debug = getenv("APP_DEBUG");
+	if ($debug !== false && strtolower(trim($debug)) === "true") {
+		error_log("REQUEST RECEIVED: " . json_encode(array("type" => $req["type"] ?? null, "request_id" => $req["request_id"] ?? null)));
+	}
 
-	$pdo = getDb();
+	if (!isset($req["request_id"])) {
+		$req["request_id"] = "no_id";
+	}
 
 	if (!isset($req["type"])) {
-		return ["type"=>"error","success"=>false,"message"=>"Missing type"];
+		return ["type"=>"error","request_id"=>$req["request_id"],"success"=>false,"message"=>"Missing type"];
 	}
 
-	if (!isset($req["request_id"])) {
-		$req["request_id"] = "no_id";
+	try {
+		$pdo = getDb();
+	} catch (Throwable $e) {
+		if ($debug !== false && strtolower(trim($debug)) === "true") {
+			error_log("DB connection error: " . $e->getMessage());
+		}
+		return [
+			"type" => "error",
+			"request_id" => $req["request_id"],
+			"success" => false,
+			"message" => "Database connection unavailable"
+		];
 	}
@@ -457,6 +473,16 @@ function requestProcessor($req) {
 	// Ticketmaster API integration
 	if ($req["type"] == "get_ticketmaster_events") {
 		$params = isset($req["params"]) && is_array($req["params"]) ? $req["params"] : array();
+		if (!function_exists("fetchTicketmasterEvents")) {
+			return array(
+				"type" => "ticketmaster_events_result",
+				"request_id" => $req["request_id"],
+				"success" => false,
+				"message" => "Ticketmaster service unavailable",
+				"count" => 0,
+				"events" => array()
+			);
+		}
 		$results = fetchTicketmasterEvents($params);
@@ -468,18 +494,6 @@ function requestProcessor($req) {
 		);
 	}
-
-	// Handler to return the Ticketmaster API key (for admin/debug only)
-	if ($req["type"] == "get_ticketmaster_api_key") {
-		$apiKey = getenv('TICKETMASTER_API_KEY');
-		return array(
-			"type" => "ticketmaster_api_key_result",
-			"request_id" => $req["request_id"],
-			"success" => $apiKey !== false && trim($apiKey) !== "",
-			"api_key" => $apiKey !== false ? $apiKey : null,
-			"message" => $apiKey !== false ? "API key retrieved" : "API key not set"
-		);
-	}
 
 	return [
 		"type" => "error",
```

## 3) `home.php`
```diff
diff --git a/home.php b/home.php
index 7177d8a..3d44f44 100644
--- a/home.php
+++ b/home.php
@@ -143,106 +143,5 @@ if (is_array($tmRes) && isset($tmRes["success"]) && $tmRes["success"] === true)
     </div>
 </div>
 
-</body>
-</html>require_once(__DIR__ . "/rabbitmq_helper.php");
-
-if (!isset($_SESSION["session_key"])) {
-    header("Location: login.php");
-    exit();
-}
-
-$req = array();
-$req["type"] = "validate_session";
-$req["request_id"] = uniqid();
-$req["session_key"] = $_SESSION["session_key"];
-
-$res = sendToDB($req);
-
-if(!isset($res["valid"]) || $res["valid"] != true) {
-	session_destroy();
-	header("Location: login.php");
-	exit();
-}
-
-$username = "";
-if (isset($res["username"])) {
-	$username = $res["username"];
-}
-?>
-
-<!DOCTYPE html>
-<html>
-<head>
-	<title>UbuntuUnited Home</title>
-	<link rel="stylesheet" href="css/style.css">
-</head>
-<body>
-
-<div class="navbar">
-    <div class="logo">UbuntuUnited</div>
-    <div class="nav-links">
-        <a href="home.php">Home</a>
-        <a href="events.php">Events</a>
-        <a href="reviews.php">Reviews</a>
-        <a href="recommendations.php">Recommendations</a>
-        <a href="friends.php">Friends</a>
-        <a href="invite.php">Invites</a>
-        <a href="notifications.php">Notifications</a>
-        <a href="search.php">Search</a>
-        <a href="logout.php">Logout</a>
-    </div>
-</div>
-
-<div class="page-wrapper">
-    <div class="container">
-        <h1>Welcome <?php echo htmlspecialchars($username); ?>!</h1>
-        <p class="subtitle">UbuntuUnited is your event hub for browsing events, reading reviews, inviting friends, and managing notifications.</p>
-
-        <div class="quick-links">
-            <div class="quick-link-card">
-                <h3>Browse Events</h3>
-                <p>Look through concerts, artists, and venues.</p>
-                <a class="btn" href="events.php">Open Events</a>
-            </div>
-
-            <div class="quick-link-card">
-                <h3>Reviews</h3>
-                <p>Read reviews and write your own event feedback.</p>
-                <a class="btn" href="reviews.php">Open Reviews</a>
-            </div>
-
-            <div class="quick-link-card">
-                <h3>Recommendations</h3>
-                <p>See suggested events based on ratings and reviews.</p>
-                <a class="btn" href="recommendations.php">View Recommendations</a>
-            </div>
-
-            <div class="quick-link-card">
-                <h3>Friends</h3>
-                <p>Manage your friend list and requests.</p>
-                <a class="btn" href="friends.php">Open Friends</a>
-            </div>
-
-            <div class="quick-link-card">
-                <h3>Invites</h3>
-                <p>Create invite links and share events with others.</p>
-                <a class="btn" href="invite.php">Create Invite</a>
-            </div>
-
-            <div class="quick-link-card">
-                <h3>Notifications / 2FA</h3>
-                <p>Manage alerts and prepare the text-based 2FA flow.</p>
-                <a class="btn" href="notifications.php">Open Notifications</a>
-            </div>
-
-            <div class="quick-link-card">
-                <h3>Search</h3>
-                <p>Search artists, events, and venues in one place.</p>
-                <a class="btn" href="search.php">Open Search</a>
-            </div>
-
-    </div>
-</div>
-
 </body>
 </html>
```

## 4) `rabbitmqphp_example/rabbitMQLib.inc`
```diff
diff --git a/rabbitmqphp_example/rabbitMQLib.inc b/rabbitmqphp_example/rabbitMQLib.inc
index d2d6f41..45b125d 100644
--- a/rabbitmqphp_example/rabbitMQLib.inc
+++ b/rabbitmqphp_example/rabbitMQLib.inc
@@ -40,12 +40,8 @@ class rabbitMQServer
 
 	function process_message($msg)
 	{
-		// send the ack to clear the item from the queue
-		if ($msg->getRoutingKey() !== "*")
-    {
-      return;
-    }
-    $this->conn_queue->ack($msg->getDeliveryTag());
+		// Ack immediately to prevent consumer lockups from unacked deliveries.
+		$this->conn_queue->ack($msg->getDeliveryTag());
 		try
 		{
 			if ($msg->getReplyTo())
@@ -177,8 +173,16 @@ class rabbitMQClient
 		$uid = $response->getCorrelationId();
 		if (!isset($this->response_queue[$uid]))
 		{
-		  echo  "unknown uid\n";
-		  return true;
+		  // If correlation_id is missing/mismatched but only one request is pending,
+		  // map this response to that request to avoid indefinite blocking.
+		  if (count($this->response_queue) === 1) {
+			$keys = array_keys($this->response_queue);
+			$uid = $keys[0];
+		  } else {
+			// Ack unexpected messages so stale responses cannot block the consumer.
+			$this->conn_queue->ack($response->getDeliveryTag());
+			return true;
+		  }
 		}
     $this->conn_queue->ack($response->getDeliveryTag());
 		$body = $response->getBody();
@@ -194,6 +198,7 @@ class rabbitMQClient
 	function send_request($message)
 	{
 		$uid = uniqid();
+		$callbackQueueName = $this->queue."_response_".$uid;
@@ -215,7 +220,8 @@ class rabbitMQClient
       $exchange->setType($this->exchange_type);
 
       $callback_queue = new AMQPQueue($channel);
-      $callback_queue->setName($this->queue."_response");
+			// Use a unique reply queue per request to prevent cross-request collisions.
+			$callback_queue->setName($callbackQueueName);
       $callback_queue->declare();
 			$callback_queue->bind($exchange->getName(),$this->routing_key.".response");
@@ -223,7 +229,7 @@ class rabbitMQClient
 			$this->conn_queue->setName($this->queue);
 			$this->conn_queue->bind($exchange->getName(),$this->routing_key);
 
-			$exchange->publish($json_message,$this->routing_key,AMQP_NOPARAM,array('reply_to'=>$callback_queue->getName(),'correlation_id'=>$uid));
+			$exchange->publish($json_message,$this->routing_key,AMQP_NOPARAM,array('reply_to'=>$callbackQueueName,'correlation_id'=>$uid));
       $this->response_queue[$uid] = "waiting";
 			$callback_queue->consume(array($this,'process_response'));
```

## 5) `ticketmaster_service.php`
```diff
diff --git a/ticketmaster_service.php b/ticketmaster_service.php
index 4237aa8..152ec5a 100644
--- a/ticketmaster_service.php
+++ b/ticketmaster_service.php
@@ -2,7 +2,11 @@
 
 require_once(__DIR__ . "/env_loader.php");
 
-loadEnvFile(__DIR__ . "/.env");
+
+$envPath = __DIR__ . "/.env";
+if (is_readable($envPath)) {
+	loadEnvFile($envPath);
+}
 
 function fetchTicketmasterEvents(array $params = array()) {
 	$apiKey = getenv('TICKETMASTER_API_KEY');
```

## 6) `.gitignore` (new file)
```gitignore
.env
.env.*
!.env.example

# Logs
*.log
logs/

# OS/editor temp
.DS_Store
*.swp
*.swo
```

## 7) `.env.example` (new file)
```env
TICKETMASTER_API_KEY=your_ticketmaster_api_key_here
APP_DEBUG=false
```

## 8) `.env` (staged as deleted from git tracking)
```diff
diff --git a/.env b/.env
deleted file mode 100644
index a540ee8..0000000
--- a/.env
+++ /dev/null
@@ -1 +0,0 @@
-TICKETMASTER_API_KEY=loVXIQ87vpvuAxtbh3j1yNUdmn7uvyMq
```
