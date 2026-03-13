<?php

function loadEnvFile($envPath){
	if (!is_readable($envPath)) {
		throw new Exception("Environment file not found: " . $envPath);
	}

	$lines = file($envPath, FILE_IGNORE_NEW_LINES);
	if($lines === false){
		throw new Exception("Failed to read environment file: " . $envPath);
	}

	foreach ($lines as $line) {
		$line = trim($line);
    
		if ($line === '' || strpos($line, '#') === 0) {
			continue;
		}

		$parts = explode('=', $line, 2);
		if (count($parts) !== 2) {
			continue;
		}
		$key = trim($parts[0]);
		$value = trim($parts[1]);

		if ($key === '') {
			continue;
		}

		if((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
		   (str_starts_with($value, "'") && str_ends_with($value, "'"))){
			$value = substr($value, 1, -1);
		}

		putenv($key . "=" . $value);
		$_ENV[$key] = $value;
		$_SERVER[$key] = $value;
	}
}

?>

