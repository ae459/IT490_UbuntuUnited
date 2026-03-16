<?php

function loadEnv($path) {
if (!file_exists($path)) {
return false;
}

$lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if ($lines === false) {
return false;
}

foreach ($lines as $line) {
$line = trim($line);

if ($line === "" || strpos($line, "#") === 0) {
continue;
}

$parts = explode("=", $line, 2);
if (count($parts) != 2) {
continue;
}

$key = trim($parts[0]);
$val = trim($parts[1]);

// remove quotes
if (strlen($val) >= 2) {
$first = $val[0];
$last = $val[strlen($val) - 1];
if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
$val = substr($val, 1, -1);
}
}

$_ENV[$key] = $val;
$_SERVER[$key] = $val;
putenv($key . "=" . $val);
}

return true;
}
