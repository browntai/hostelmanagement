<?php
    session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    include_once('../includes/toast-helper.php');
    check_login();

    $uid = $_SESSION['id'];

    // Check if daycare is enabled for the assigned hostel
    $email = $_SESSION['login'];
    $daycare_enabled = false;
    $stmt = $mysqli->prepare("SELECT hostel_id FROM bookings WHERE emailid=? AND booking_status IN ('confirmed', 'approved') ORDER BY postingDate DESC LIMIT 1");
    if($stmt) {
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if($row = $res->fetch_object()) {
            include_once('../includes/hostel-helper.php');
            $daycare_enabled = isServiceEnabled($mysqli, $row->hostel_id, 'daycare');
        }
        $stmt->close();
    }

    if(!$daycare_enabled) {
        header("Location: dashboard.php");
        exit();
    }

    if(isset($_POST['register_child'])) {
        $full_name = $_POST['full_name'];
        $age = intval($_POST['age']);
        $medical_info = $_POST['medical_info'];

        $stmt = $mysqli->prepare("INSERT INTO children (parent_id, full_name, age, medical_info) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('isis', $uid, $full_name, $age, $medical_info);
        
        if($stmt->execute()) {
            setToast('success', 'Child registered successfully!');
        } else {
            setToast('error', 'Registration failed.');
        }
        header("Location: register-child.php");
        exit();
    }

    if(isset($_GET['delete'])) {
        $id = intval($_GET['delete']);
        $stmt = $mysqli->prepare("DELETE FROM children WHERE id=? AND parent_id=?");
        $stmt->bind_param('ii', $id, $uid);
        if($stmt->execute()) {
            setToast('success', 'Child record removed.');
        }
        header("Location: register-child.php");
        exit();
    }
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Child Registration - Hostel Management System</title>
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="../assets/css/custom-gradients.css" rel="stylesheet">
    <link href="../dist/css/icons/font-awesome/css/fontawesome-all.min.css" rel="stylesheet">
</head>
<body>
    <div class="preloader">
        <div class="lds-ripple"><div class="lds-pos"></div><div class="lds-pos"></div></div>
    </div>
    <div id="main-wrapper" data-theme="light" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed" data-boxed-layout="full">
        <header class="topbar" data-navbarbg="skin6"><?php include '../includes/client-navigation.php'?></header>
        <aside class="left-sidebar" data-sidebarbg="skin6"><div class="scroll-sidebar"><?php include '../includes/client-sidebar.php'?></div></aside>
        <div class="page-wrapper">
            <div class="page-breadcrumb">
                <div class="row">
                    <div class="col-7 align-self-center">
                        <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Child Registration</h4>
                        <nav aria-label="breadcrumb"><ol class="breadcrumb m-0 p-0 text-muted"><li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">Children Directory</li></ol></nav>
                    </div>
                </div>
            </div>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-5">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h4 class="card-title">Register New Child</h4>
                                <form method="POST">
                                    <div class="form-group mb-3">
                                        <label>Full Name</label>
                                        <input type="text" name="full_name" class="form-control" required placeholder="Enter child's full name">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>Age (Years)</label>
                                        <input type="number" name="age" class="form-control" required min="1" max="12">
                                    </div>
                                    <div class="form-group mb-4">
                                        <label>Medical Information / Allergies</label>
                                        <textarea name="medical_info" class="form-control" rows="3" placeholder="Any health issues or allergies staff should know about? (Optional)"></textarea>
                                    </div>
                                    <button type="submit" name="register_child" class="btn btn-primary btn-block">Register Child</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h4 class="card-title">My Registered Children</h4>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Name</th>
                                                <th>Age</th>
                                                <th>Medical Info</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $res = $mysqli->query("SELECT * FROM children WHERE parent_id = $uid ORDER BY full_name ASC");
                                            if($res->num_rows == 0):
                                            ?>
                                            <tr><td colspan="4" class="text-center py-4 text-muted">No children registered yet.</td></tr>
                                            <?php
                                            else:
                                                while($row = $res->fetch_object()):
                                            ?>
                                            <tr>
                                                <td class="font-weight-medium text-dark"><?php echo htmlentities($row->full_name); ?></td>
                                                <td><?php echo $row->age; ?> yrs</td>
                                                <td><small class="text-muted"><?php echo $row->medical_info ? htmlentities($row->medical_info) : 'None'; ?></small></td>
                                                <td>
                                                    <a href="?delete=<?php echo $row->id; ?>" class="text-danger" onclick="return confirm('Remove this record?')"><i class="fas fa-trash"></i></a>
                                                </td>
                                            </tr>
                                            <?php endwhile; endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include '../includes/footer.php' ?>
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
    <script>$(".preloader").fadeOut();</script>
</body>
</html>
