<?php
    session_start();
    include('includes/dbconn.php');

    // Store hostel_id if provided
    $selected_hostel = '';
    if(isset($_GET['hostel_id'])){
        $selected_hostel = intval($_GET['hostel_id']);
        $_SESSION['selected_hostel_for_booking'] = $selected_hostel;
    }

    if(isset($_POST['submit'])){
        $idno = $_POST['idno'];
        $fname = $_POST['fname'];
        $mname = $_POST['mname'];
        $lname = $_POST['lname'];
        $gender = $_POST['gender'];
        $contactno = $_POST['contactno'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $cpassword = $_POST['cpassword'];

        $redirect_url = 'client-registration.php' . ($selected_hostel ? '?hostel_id='.$selected_hostel : '');

        // Validation
        if($password != $cpassword){
            include_once('includes/toast-helper.php');
            setToast('error', 'Passwords do not match!');
            header("Location: $redirect_url");
            exit;
        } else {
            // Check if email already exists in users table
            $check_query = "SELECT email FROM users WHERE email=?";
            $check_stmt = $mysqli->prepare($check_query);
            $check_stmt->bind_param('s', $email);
            $check_stmt->execute();
            $check_stmt->store_result();

            if($check_stmt->num_rows > 0){
                include_once('includes/toast-helper.php');
                setToast('error', 'Email already registered. Please login instead.');
                header("Location: $redirect_url");
                exit;
            } else {
                // Hash password
                $hashed_password = md5($password);
                $full_name = trim($fname . ' ' . $mname . ' ' . $lname);

                // Get tenant_id (default to 1)
                $tenant_id = 1; 

                // Start transaction
                $mysqli->begin_transaction();

                try {
                    // 1. Insert into users table
                    $query1 = "INSERT INTO users (tenant_id, email, password, role, full_name, first_name, middle_name, last_name, gender, contact_no, id_no, original_table) 
                               VALUES (?, ?, ?, 'client', ?, ?, ?, ?, ?, ?, ?, 'client_registration')";
                    $stmt1 = $mysqli->prepare($query1);
                    $stmt1->bind_param('isssssssss', $tenant_id, $email, $hashed_password, $full_name, $fname, $mname, $lname, $gender, $contactno, $idno);
                    $stmt1->execute();
                    $user_id = $mysqli->insert_id;

                    $mysqli->commit();

                    // Auto-login after registration
                    $_SESSION['id'] = $user_id;
                    $_SESSION['login'] = $email;
                    $_SESSION['tenant_id'] = $tenant_id;
                    $_SESSION['role'] = 'client';
                    $_SESSION['full_name'] = $full_name;

                    // Log activity
                    $ip = $_SERVER['REMOTE_ADDR'];
                    $log_query = "INSERT INTO userlog (userId, userEmail, userIp) VALUES (?, ?, ?)";
                    $log_stmt = $mysqli->prepare($log_query);
                    $log_stmt->bind_param('iss', $user_id, $email, $ip);
                    $log_stmt->execute();

                    include_once('includes/toast-helper.php');
                    setToast('success', "Welcome to HostelHub, $full_name!");
                    
                    // Redirect
                    if(isset($_SESSION['selected_hostel_for_booking']) && !empty($_SESSION['selected_hostel_for_booking'])){
                        $hostel_id = $_SESSION['selected_hostel_for_booking'];
                        header("Location: client/book-hostel.php?hostel_id=$hostel_id");
                    } else {
                        header("Location: client/dashboard.php");
                    }
                    exit;

                } catch (Exception $e) {
                    $mysqli->rollback();
                    include_once('includes/toast-helper.php');
                    setToast('error', "Registration failed: " . $e->getMessage());
                    header("Location: $redirect_url");
                    exit;
                }
            }
        }
    }
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Create your HostelHub account to browse and book hostel accommodations.">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon.png">
    <title>Register — HostelHub</title>
    <link href="dist/css/style.min.css" rel="stylesheet">
    <link href="assets/css/public-pages.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    
    <script type="text/javascript">
    function valid() {
        if(document.bookings.password.value != document.bookings.cpassword.value){
            alert("Password and Confirm Password do not match!");
            document.bookings.cpassword.focus();
            return false;
        }
        return true;
    }
    </script>
</head>

<body class="pub-page" style="background:var(--pub-light);">

    <div class="auth-split">
        <!-- Left Branding Panel -->
        <div class="auth-split-left">
            <div class="auth-brand-content">
                <img src="assets/images/big/icon.png" alt="HostelHub">
                <h2>Join HostelHub</h2>
                <p>Create your free account to discover and book verified hostel accommodations.</p>

                <ul class="auth-features">
                    <li><i class="fas fa-search"></i> <span>Browse verified properties</span></li>
                    <li><i class="fas fa-calendar-check"></i> <span>Instant room booking</span></li>
                    <li><i class="fas fa-star"></i> <span>Read & write reviews</span></li>
                    <li><i class="fas fa-heart"></i> <span>Save your favourites</span></li>
                </ul>
            </div>
        </div>

        <!-- Right Form Panel -->
        <div class="auth-split-right">
            <div class="auth-form-card auth-form-wide">
                <h2>Create Account</h2>
                <p class="auth-subtitle">Fill in your details to get started</p>

                <form method="POST" name="bookings" onsubmit="return valid();">
                    <div class="form-group">
                        <label for="idno"><i class="fas fa-id-card" style="margin-right:4px; font-size:0.75rem; color:var(--pub-soft);"></i> ID Number</label>
                        <input class="form-control" name="idno" id="idno" type="text" placeholder="Enter your ID number" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fname">First Name</label>
                                <input class="form-control" name="fname" id="fname" type="text" placeholder="First name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="mname">Middle Name</label>
                                <input class="form-control" name="mname" id="mname" type="text" placeholder="Middle name">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="lname">Last Name</label>
                                <input class="form-control" name="lname" id="lname" type="text" placeholder="Last name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="gender">Gender</label>
                                <select class="form-control" name="gender" id="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contactno"><i class="fas fa-phone" style="margin-right:4px; font-size:0.75rem; color:var(--pub-soft);"></i> Phone Number</label>
                                <input class="form-control" name="contactno" id="contactno" type="tel" placeholder="Phone number" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email"><i class="fas fa-envelope" style="margin-right:4px; font-size:0.75rem; color:var(--pub-soft);"></i> Email</label>
                                <input class="form-control" name="email" id="email" type="email" placeholder="Email address" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password"><i class="fas fa-lock" style="margin-right:4px; font-size:0.75rem; color:var(--pub-soft);"></i> Password</label>
                                <div class="input-group">
                                    <input class="form-control" name="password" id="password" type="password" placeholder="Password" required>
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
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cpassword"><i class="fas fa-lock" style="margin-right:4px; font-size:0.75rem; color:var(--pub-soft);"></i> Confirm Password</label>
                                <div class="input-group">
                                    <input class="form-control" name="cpassword" id="cpassword" type="password" placeholder="Confirm password" required>
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
                        </div>
                    </div>

                    <button type="submit" name="submit" class="btn-auth-submit" style="margin-top:8px;">
                        <i class="fas fa-user-plus"></i> &nbsp;Create Account
                    </button>

                    <div class="auth-divider">or</div>

                    <div class="auth-links">
                        Already have an account? <a href="login.php<?php echo $selected_hostel ? '?hostel_id='.$selected_hostel : ''; ?>">Sign In</a>
                    </div>

                    <div class="auth-links" style="margin-top:12px;">
                        <a href="index.php" style="color:#8a9bb2; font-weight:400;"><i class="fas fa-arrow-left" style="margin-right:4px;"></i> Back to Browse Properties</a>
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
