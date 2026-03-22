<?php
    $DB_host = getenv('DB_HOST') ?: "localhost";
    $DB_user = getenv('DB_USER') ?: "root";
    $DB_pass = getenv('DB_PASS') ?: "";
    $DB_name = getenv('DB_NAME') ?: "hostelmsphp";
    try
    {
        $DB_con = new PDO("mysql:host={$DB_host};dbname={$DB_name}",$DB_user,$DB_pass);
        $DB_con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
        catch(PDOException $e)
    {
        $e->getMessage();
    }
?>
