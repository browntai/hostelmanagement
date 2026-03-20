<?php
    $dbuser = getenv('DB_USER') ?: "root";
    $dbpass = getenv('DB_PASS') ?: "";
    $host   = getenv('DB_HOST') ?: "localhost";
    $db     = getenv('DB_NAME') ?: "hostelmsphp";
    $mysqli = new mysqli($host, $dbuser, $dbpass, $db);
