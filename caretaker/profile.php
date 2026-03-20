<?php
    session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    check_login();

    $aid = $_SESSION['id'];

    if(isset($_POST['update'])){
        if(isset($_POST['emailid'])){
            $email = $_POST['emailid'];
            $profile_pic = null;
            
            if(isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0){
                $ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
                $allowed = array('jpg', 'jpeg', 'png', 'gif');
                if(in_array(strtolower($ext), $allowed)){
                    $new_name = "user_" . $aid . "_" . time() . "." . $ext;
                    $upload_target = "../uploads/profiles/" . $new_name;
                    if(!is_dir("../uploads/profiles")) mkdir("../uploads/profiles", 0777, true);
                    
                    if(move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_target)){
                        $profile_pic = $new_name;
                    }
                }
            }

            if($profile_pic) {
                $query="UPDATE users set email=?, profile_pic=? where id=?";
                $stmt = $mysqli->prepare($query);
                $stmt->bind_param('ssi', $email, $profile_pic, $aid);
                $stmt->execute();
            } else {
                $query="UPDATE users set email=? where id=?";
                $stmt = $mysqli->prepare($query);
                $stmt->bind_param('si', $email, $aid);
                $stmt->execute();
            }
            
            echo"<script>alert('Profile updated successfully');</script>";
        }
    }
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Profile - Caretaker Portal</title>
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/favicon.png">
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="../assets/css/public-pages.css" rel="stylesheet">
    <link href="../assets/css/custom-gradients.css" rel="stylesheet">
</head>

<body>
    <div class="preloader"><div class="lds-ripple"><div class="lds-pos"></div><div class="lds-pos"></div></div></div>
    <div id="main-wrapper" data-theme="light" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed" data-boxed-layout="full">
        <header class="topbar" data-navbarbg="skin6">
            <?php include 'includes/navigation.php'?>
        </header>
        <aside class="left-sidebar" data-sidebarbg="skin6">
            <div class="scroll-sidebar" data-sidebarbg="skin6">
                <?php include 'includes/sidebar.php'?>
            </div>
        </aside>
        <div class="page-wrapper">
            <div class="page-breadcrumb">
                <div class="row">
                    <div class="col-7 align-self-center">
                        <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">My Profile</h4>
                        <p class="text-muted small">Manage your account information</p>
                    </div>
                </div>
            </div>
            <div class="container-fluid">
                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <?php	
                        $ret="SELECT u.*, h.name as hostel_name FROM users u LEFT JOIN hostels h ON u.assigned_hostel_id = h.id WHERE u.id=?";
                        $stmt= $mysqli->prepare($ret) ;
                        $stmt->bind_param('i',$aid);
                        $stmt->execute();
                        $res=$stmt->get_result();
                        if($row=$res->fetch_object()):
                        ?>
                        <div class="col-lg-4 col-md-5 mb-4">
                            <div class="card pub-card h-100 shadow-md border-0">
                                <div class="card-body text-center py-5">
                                    <p class="text-muted small text-uppercase font-weight-ExtraBold mb-4" style="letter-spacing: 1px;">Caretaker Designation</p>
                                    <div class="mb-5 position-relative">
                                        <?php 
                                        $pic = $row->profile_pic ? "../uploads/profiles/" . $row->profile_pic : "../assets/images/users/user-icn.png";
                                        ?>
                                        <div class="position-relative d-inline-block">
                                            <div class="pub-avatar-ring">
                                                <img src="<?php echo $pic; ?>" alt="user" class="rounded-circle shadow-lg" width="160" height="160" style="object-fit: cover; border: 5px solid #fff;">
                                            </div>
                                            <span class="badge badge-success position-absolute border-white border-2" style="bottom: 10px; right: 10px; width: 20px; height: 20px; border-radius: 50%;">&nbsp;</span>
                                        </div>
                                    </div>
                                    <h3 class="font-weight-ExtraBold text-dark mt-2" style="font-family: var(--theme-highlight-font);"><?php echo htmlentities($row->full_name); ?></h3>
                                    <div class="badge badge-light-primary px-3 py-2 rounded-pill mb-3">Verified Caretaker</div>
                                    <p class="text-muted">Assigned to: <br><strong class="text-primary"><?php echo htmlentities($row->hostel_name); ?></strong></p>
                                    
                                    <div class="custom-file mt-4 shadow-sm border rounded overflow-hidden">
                                        <input type="file" name="profile_pic" class="custom-file-input" id="profile_pic" accept="image/*">
                                        <label class="custom-file-label text-left border-0" for="profile_pic" style="border-radius: 0;">Change Avatar</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8 col-md-7 mb-4">
                            <div class="card pub-card h-100 shadow-md border-0">
                                <div class="card-header bg-white py-4 border-bottom">
                                    <h4 class="mb-0 text-dark font-weight-ExtraBold" style="font-family: var(--theme-highlight-font);"><i class="fas fa-id-card mr-2 text-primary"></i>Personal Information</h4>
                                </div>
                                <div class="card-body py-4">
                                    <div class="row">
                                        <div class="col-md-6 mb-4">
                                            <label class="text-muted font-weight-ExtraBold small text-uppercase mb-2 d-block" style="letter-spacing: 0.5px;">Full Name</label>
                                            <input type="text" value="<?php echo htmlentities($row->full_name); ?>" disabled class="form-control bg-light border-0" style="font-weight: 600;">
                                        </div>
                                        <div class="col-md-6 mb-4">
                                            <label class="text-muted font-weight-ExtraBold small text-uppercase mb-2 d-block" style="letter-spacing: 0.5px;">Email Address</label>
                                            <input type="email" class="form-control border shadow-none" name="emailid" value="<?php echo htmlentities($row->email); ?>" required style="font-weight: 500;">
                                        </div>
                                        <div class="col-md-6 mb-4">
                                            <label class="text-muted font-weight-ExtraBold small text-uppercase mb-2 d-block" style="letter-spacing: 0.5px;">Access Role</label>
                                            <input type="text" value="Caretaker" disabled class="form-control bg-light border-0" style="font-weight: 600;">
                                        </div>
                                        <div class="col-md-6 mb-4">
                                            <label class="text-muted font-weight-ExtraBold small text-uppercase mb-2 d-block" style="letter-spacing: 0.5px;">Assigned Property</label>
                                            <input type="text" value="<?php echo htmlentities($row->hostel_name); ?>" disabled class="form-control bg-light border-0" style="font-weight: 600;">
                                        </div>
                                    </div>

                                    <div class="mt-5 pt-4 border-top text-right">
                                        <button type="submit" name="update" class="btn btn-pub-solid px-5 shadow-sm">
                                            <i class="fas fa-save mr-2"></i> Save Profile Changes
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                 </form>
            </div>
            <?php include 'includes/footer.php' ?>
        </div>
    </div>
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/popper.js/dist/umd/popper.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="../dist/js/app-style-switcher.js"></script>
    <script src="../dist/js/feather.min.js"></script>
    <script src="../assets/libs/perfect-scrollbar/dist/perfect-scrollbar.jquery.min.js"></script>
    <script src="../dist/js/sidebarmenu.js"></script>
    <script src="../dist/js/custom.min.js"></script>
    <script>
        $(document).ready(function() {
            $(".preloader").fadeOut();
        });
    </script>
</body>
</html>
