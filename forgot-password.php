<?php
session_start();
include('includes/dbconn.php');

if(isset($_POST['send_reset'])){
    $email = $_POST['email'];
    
    // Check if email exists
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE email=?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if($res->num_rows > 0){
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $ins = $mysqli->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $ins->bind_param('sss', $email, $token, $expiry);
        
        if($ins->execute()){
            // Simulate sending email
            $resetLink = "http://localhost/HostelManagement-PHP/reset-password.php?token=" . $token;
            include_once('includes/toast-helper.php');
            setToast('success', "Reset link generated (Simulation): $resetLink");
        } else {
            include_once('includes/toast-helper.php');
            setToast('error', "Error generating token.");
        }
    } else {
        include_once('includes/toast-helper.php');
        setToast('error', "Email not found.");
    }
}
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Reset your HostelHub account password.">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon.png">
    <title>Forgot Password — HostelHub</title>
    <link href="dist/css/style.min.css" rel="stylesheet">
    <link href="assets/css/public-pages.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
</head>

<body class="pub-page" style="background:var(--pub-light);">

    <div class="auth-split">
        <!-- Left Branding Panel -->
        <div class="auth-split-left">
            <div class="auth-brand-content">
                <img src="assets/images/big/icon.png" alt="HostelHub">
                <h2>Forgot Your Password?</h2>
                <p>No worries — we'll help you reset it. Enter your email and we'll send you a recovery link.</p>

                <ul class="auth-features">
                    <li><i class="fas fa-envelope"></i> <span>Check your email for a link</span></li>
                    <li><i class="fas fa-clock"></i> <span>Link valid for 1 hour</span></li>
                    <li><i class="fas fa-shield-alt"></i> <span>Secure password reset</span></li>
                </ul>
            </div>
        </div>

        <!-- Right Form Panel -->
        <div class="auth-split-right">
            <div class="auth-form-card">
                <h2>Recover Password</h2>
                <p class="auth-subtitle">Enter your registered email address</p>

                <form method="POST">
                    <div class="form-group">
                        <label for="uname"><i class="fas fa-envelope" style="margin-right:4px; font-size:0.75rem; color:var(--pub-soft);"></i> Email Address</label>
                        <input class="form-control" name="email" id="uname" type="email" placeholder="you@example.com" required>
                    </div>
                    
                    <button type="submit" name="send_reset" class="btn-auth-submit">
                        <i class="fas fa-paper-plane"></i> &nbsp;Send Reset Link
                    </button>

                    <div class="auth-divider">or</div>

                    <div class="auth-links">
                        <a href="login.php"><i class="fas fa-arrow-left" style="margin-right:4px;"></i> Back to Login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="assets/libs/popper.js/dist/umd/popper.min.js"></script>
    <script src="assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <?php 
        include_once('includes/toast-helper.php');
        showAlerts(); 
    ?>
</body>
</html>
