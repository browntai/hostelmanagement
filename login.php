<?php
    session_start();
    include('includes/dbconn.php');
    
    // Store hostel_id if provided
    $selected_hostel = '';
    if(isset($_GET['hostel_id'])){
        $selected_hostel = intval($_GET['hostel_id']);
        $_SESSION['selected_hostel_for_booking'] = $selected_hostel;
    }
    
    if(isset($_POST['login']))
    {
        $email=$_POST['email'];
        $password=$_POST['password'];
        $password = md5($password);
        
        // Unified authentication query
        $stmt = $mysqli->prepare("
            SELECT u.id, u.tenant_id, u.role, u.status, u.email, u.full_name, t.status as tenant_status 
            FROM users u 
            LEFT JOIN tenants t ON u.tenant_id = t.id 
            WHERE u.email=? AND u.password=?
        ");
        $stmt->bind_param('ss', $email, $password);
        $stmt->execute();
        $stmt->bind_result($id, $tenant_id, $role, $status, $user_email, $full_name, $tenant_status);
        $rs = $stmt->fetch();
        $stmt->close();
        
        if($rs){
            if($status === 'suspended') {
                echo "<script>alert('Your account has been suspended. Please contact Admin.');</script>";
            } else if($role !== 'admin' && $tenant_status === 'suspended') {
                echo "<script>alert('The service for this hostel is currently suspended. Please contact Landlord.');</script>";
            } else {
                $_SESSION['id'] = $id;
                $_SESSION['login'] = $user_email;
                $_SESSION['tenant_id'] = $tenant_id;
                $_SESSION['role'] = $role;
                $_SESSION['full_name'] = $full_name;

                // Log activity
                $ip=$_SERVER['REMOTE_ADDR'];
                $geopluginURL='http://www.geoplugin.net/php.gp?ip='.$ip;
                $addrDetailsArr = @unserialize(file_get_contents($geopluginURL));
                $city = isset($addrDetailsArr['geoplugin_city']) ? $addrDetailsArr['geoplugin_city'] : 'Unknown';
                $country = isset($addrDetailsArr['geoplugin_countryName']) ? $addrDetailsArr['geoplugin_countryName'] : 'Unknown';
                
                $log="insert into userLog(userId,userEmail,userIp,city,country) values('$id','$user_email','$ip','$city','$country')";
                $mysqli->query($log);

                include_once('includes/log-helper.php');
                include_once('includes/toast-helper.php');
                logActivity($id, $user_email, ucfirst($role), 'Login', 'User logged in successfully');
                setToast('success', "Welcome back, $full_name!");

                // Redirect based on role
                if($role == 'admin') {
                    header("location:admin/super_dashboard.php");
                } else if($role == 'landlord') {
                    header("location:landlord/dashboard.php");
                } else if($role == 'caretaker') {
                    header("location:caretaker/dashboard.php");
                } else {
                    // Client redirect
                    if(isset($_SESSION['selected_hostel_for_booking']) && !empty($_SESSION['selected_hostel_for_booking'])){
                        $hostel_id = $_SESSION['selected_hostel_for_booking'];
                        header("location:client/book-hostel.php?hostel_id=$hostel_id");
                    } else {
                        header("location:client/dashboard.php");
                    }
                }
                exit;
            }
        } else {
            include_once('includes/toast-helper.php');
            setToast('error', 'Invalid Email or Password!');
            header("Location: login.php");
            exit;
        }
    }
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Login to your HostelHub account to manage bookings and properties.">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon.png">
    <title>Login — HostelHub</title>
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
                <h2>Welcome Back</h2>
                <p>Sign in to access your dashboard, manage bookings, and explore new properties.</p>

                <ul class="auth-features">
                    <li><i class="fas fa-shield-alt"></i> <span>Secure & encrypted login</span></li>
                    <li><i class="fas fa-tachometer-alt"></i> <span>Quick access to your dashboard</span></li>
                    <li><i class="fas fa-bell"></i> <span>Booking updates & notifications</span></li>
                    <li><i class="fas fa-heart"></i> <span>Saved properties & wishlist</span></li>
                </ul>
            </div>
        </div>

        <!-- Right Form Panel -->
        <div class="auth-split-right">
            <div class="auth-form-card">
                <h2>Sign In</h2>
                <p class="auth-subtitle">Enter your credentials to continue</p>

                <form method="POST">
                    <div class="form-group">
                        <label for="uname"><i class="fas fa-envelope" style="margin-right:4px; font-size:0.75rem; color:var(--pub-soft);"></i> Email Address</label>
                        <input class="form-control" name="email" id="uname" type="email" placeholder="you@example.com" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="pwd"><i class="fas fa-lock" style="margin-right:4px; font-size:0.75rem; color:var(--pub-soft);"></i> Password</label>
                        <div class="input-group">
                            <input class="form-control" name="password" id="pwd" type="password" placeholder="Enter your password" required>
                            <div class="input-group-append">
                                <span class="input-group-text toggle-password">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye-fill" viewBox="0 0 16 16">
                                        <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"/>
                                        <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"/>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div style="text-align:right; margin-bottom:20px;">
                        <a href="forgot-password.php" style="font-size:0.85rem; color:var(--pub-blue); font-weight:500; text-decoration:none;">Forgot password?</a>
                    </div>

                    <button type="submit" name="login" class="btn-auth-submit">
                        <i class="fas fa-sign-in-alt"></i> &nbsp;Sign In
                    </button>

                    <div class="auth-divider">or</div>

                    <div class="auth-links">
                        Don't have an account? <a href="client-registration.php<?php echo $selected_hostel ? '?hostel_id='.$selected_hostel : ''; ?>">Create Account</a>
                    </div>

                    <div class="auth-links" style="margin-top:10px;">
                        <a href="admin/index.php" style="color:#5a6b7d; font-weight:500;"><i class="fas fa-building" style="margin-right:4px;"></i> Property Owner Portal</a>
                    </div>

                    <div class="auth-links" style="margin-top:16px;">
                        <a href="index.php" style="color:#8a9bb2; font-weight:400;"><i class="fas fa-arrow-left" style="margin-right:4px;"></i> Back to Home</a>
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
    <script src="dist/js/show-password.js"></script>
</body>

</html>
