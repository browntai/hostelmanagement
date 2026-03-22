<?php
if(!isset($page_title)) $page_title = "HostelHub — Find Your Perfect Accommodation";
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Find and book premium hostel accommodations across Kenya. Browse verified properties with real photos, reviews, and instant booking.">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon.png">
    <title><?php echo $page_title; ?></title>
    <link href="dist/css/style.min.css" rel="stylesheet">
    <link href="assets/css/public-pages.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <?php if(isset($extra_css)) echo $extra_css; ?>
</head>

<body class="pub-page">
