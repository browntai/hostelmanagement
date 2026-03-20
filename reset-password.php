<?php
session_start();
include('includes/dbconn.php');

$token = isset($_GET['token']) ? $_GET['token'] : '';
$validToken = false;

if($token){
    $stmt = $mysqli->prepare("SELECT email FROM password_resets WHERE token=? AND expires_at > NOW()");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $res = $stmt->get_result();
    if($res->num_rows > 0){
        $validToken = true;
        $email = $res->fetch_object()->email;
    }
}

if(isset($_POST['reset_password']) && $validToken){
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];
    
    if($password === $cpassword){
        $hash = md5($password);
        $upd = $mysqli->prepare("UPDATE users SET password=? WHERE email=?");
        $upd->bind_param('ss', $hash, $email);
        if($upd->execute()){
            // Delete token
            $del = $mysqli->prepare("DELETE FROM password_resets WHERE email=?");
            $del->bind_param('s', $email);
            $del->execute();
            
            include_once('includes/toast-helper.php');
            setToast('success', 'Password reset successfully! Login now.');
            header("Location: login.php");
            exit;
        } else {
            include_once('includes/toast-helper.php');
            setToast('error', "Database error.");
        }
    } else {
        include_once('includes/toast-helper.php');
        setToast('error', "Passwords do not match.");
    }
}
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Reset your HostelHub password.">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon.png">
    <title>Reset Password — HostelHub</title>
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
                <h2>Reset Password</h2>
                <p>Choose a new, strong password for your account to keep it secure.</p>

                <ul class="auth-features">
                    <li><i class="fas fa-key"></i> <span>Choose a strong password</span></li>
                    <li><i class="fas fa-lock"></i> <span>At least 6 characters</span></li>
                    <li><i class="fas fa-check-circle"></i> <span>Confirm to avoid typos</span></li>
                </ul>
            </div>
        </div>

        <!-- Right Form Panel -->
        <div class="auth-split-right">
            <div class="auth-form-card">
                <h2>New Password</h2>

                <?php if(!$validToken): ?>
                    <div class="alert alert-danger" style="border-radius:var(--pub-radius-sm); font-size:0.9rem;">
                        <i class="fas fa-exclamation-circle"></i> &nbsp;Invalid or expired token.
                    </div>
                    <div class="auth-links">
                        <a href="forgot-password.php">Request a new reset link</a>
                    </div>
                <?php else: ?>
                    <p class="auth-subtitle">Enter your new password below</p>

                    <form method="POST">
                        <div class="form-group">
                            <label><i class="fas fa-lock" style="margin-right:4px; font-size:0.75rem; color:var(--pub-soft);"></i> New Password</label>
                            <input class="form-control" name="password" type="password" placeholder="Enter new password" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-lock" style="margin-right:4px; font-size:0.75rem; color:var(--pub-soft);"></i> Confirm Password</label>
                            <input class="form-control" name="cpassword" type="password" placeholder="Confirm new password" required>
                        </div>
                        
                        <button type="submit" name="reset_password" class="btn-auth-submit">
                            <i class="fas fa-check"></i> &nbsp;Reset Password
                        </button>
                    </form>
                <?php endif; ?>

                <div class="auth-links" style="margin-top:20px;">
                    <a href="login.php" style="color:#8a9bb2; font-weight:400;"><i class="fas fa-arrow-left" style="margin-right:4px;"></i> Back to Login</a>
                </div>
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
