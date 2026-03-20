<?php
session_start();
include('../includes/dbconn.php');

// Security Check
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("location:index.php");
    exit();
}

// Approval Action
if(isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    $query = "UPDATE hostels SET status = 'approved' WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $id);
    if($stmt->execute()) {
        echo "<script>alert('Hostel approved successfully!'); window.location.href='manage-approvals.php';</script>";
    }
}

// Rejection Action
if(isset($_GET['reject'])) {
    $id = intval($_GET['reject']);
    $query = "UPDATE hostels SET status = 'rejected' WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $id);
    if($stmt->execute()) {
        echo "<script>alert('Hostel rejected.'); window.location.href='manage-approvals.php';</script>";
    }
}

// Suspend Action
if(isset($_GET['suspend'])) {
    $id = intval($_GET['suspend']);
    $query = "UPDATE hostels SET status = 'suspended' WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $id);
    if($stmt->execute()) {
        echo "<script>alert('Hostel suspended.'); window.location.href='manage-approvals.php';</script>";
    }
}

// Mark as Available (Approve)
if(isset($_GET['available'])) {
    $id = intval($_GET['available']);
    $query = "UPDATE hostels SET status = 'approved' WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $id);
    if($stmt->execute()) {
        echo "<script>alert('Hostel marked as available/approved.'); window.location.href='manage-approvals.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Approvals - Super Admin</title>
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="../assets/css/custom-gradients.css" rel="stylesheet">
    <link href="../assets/css/public-pages.css" rel="stylesheet">
    <!-- DataTables -->
    <link href="../assets/extra-libs/datatables.net-bs4/css/dataTables.bootstrap4.css" rel="stylesheet">
</head>
<body>
    <div class="preloader">
        <div class="lds-ripple">
            <div class="lds-pos"></div>
            <div class="lds-pos"></div>
        </div>
    </div>

    <div id="main-wrapper" data-theme="light" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed" data-boxed-layout="full">

        <header class="topbar" data-navbarbg="skin6">
            <?php include 'includes/navigation.php'?>
        </header>

        <aside class="left-sidebar" data-sidebarbg="skin6">
            <div class="scroll-sidebar" data-sidebarbg="skin6">
                <?php include 'includes/super-sidebar.php'; ?>
            </div>
        </aside>

        <div class="page-wrapper">
            <div class="page-breadcrumb">
                <div class="row">
                    <div class="col-7 align-self-center">
                        <h3 class="page-title text-truncate text-dark font-weight-medium mb-1">Hostel Verification</h3>
                        <div class="d-flex align-items-center">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb m-0 p-0">
                                    <li class="breadcrumb-item"><a href="super_dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Hostel Approvals</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container-fluid">

                <!-- ===== VERIFICATION HISTORY (TOP) ===== -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow-sm" style="border-top: 4px solid #17c788;">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mr-3"
                                         style="width:42px;height:42px;background:rgba(95,118,232,0.12);">
                                        <i data-feather="check-circle" style="color:#17c788;width:20px;height:20px;"></i>
                                    </div>
                                    <div>
                                        <h4 class="card-title mb-0">Verification History</h4>
                                        <small class="text-muted">All approved, rejected &amp; suspended hostels</small>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table id="history_table" class="table table-hover align-middle mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Hostel Name</th>
                                                <th>Landlord</th>
                                                <th>Status</th>
                                                <th class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sql = "SELECT h.*, t.name as tenant_name
                                                    FROM hostels h
                                                    JOIN tenants t ON h.tenant_id = t.id
                                                    WHERE h.status != 'pending'
                                                    ORDER BY h.created_at DESC";
                                            $res = $mysqli->query($sql);
                                            while($row = $res->fetch_assoc()) {
                                                if($row['status'] == 'approved') {
                                                    $badgeStyle = 'background:#e6f9f0;color:#28a745;';
                                                    $badgeLabel = 'Approved';
                                                } elseif($row['status'] == 'suspended') {
                                                    $badgeStyle = 'background:#fff8e1;color:#d39e00;';
                                                    $badgeLabel = 'Suspended';
                                                } else {
                                                    $badgeStyle = 'background:#fde8e8;color:#dc3545;';
                                                    $badgeLabel = 'Rejected';
                                                }
                                                echo "<tr>";
                                                echo "<td class='text-muted'>".$row['id']."</td>";
                                                echo "<td><strong>".htmlentities($row['name'])."</strong></td>";
                                                echo "<td>".htmlentities($row['tenant_name'])."</td>";
                                                echo "<td><span style='".$badgeStyle." padding:4px 12px;border-radius:20px;font-size:0.8rem;font-weight:600;'>".$badgeLabel."</span></td>";
                                                echo "<td class='text-center'>
                                                        <a href='hostel-details-admin.php?id=".$row['id']."' class='btn btn-sm btn-outline-info mr-1' title='View Details'><i data-feather='eye'></i></a>
                                                        <a href='super_impersonate.php?tenant_id=".$row['tenant_id']."' class='btn btn-sm btn-outline-secondary mr-1' title='Manage as Tenant'><i data-feather='user'></i></a>";
                                                if($row['status'] == 'approved') {
                                                    echo "<a href='manage-approvals.php?suspend=".$row['id']."' class='btn btn-sm btn-outline-warning mr-1' title='Suspend' onclick=\"return confirm('Suspend this hostel?')\"><i data-feather='slash'></i></a>";
                                                } else {
                                                    echo "<a href='manage-approvals.php?available=".$row['id']."' class='btn btn-sm btn-outline-success mr-1' title='Activate' onclick=\"return confirm('Activate this hostel?')\"><i data-feather='play'></i></a>";
                                                }
                                                if($row['status'] != 'rejected') {
                                                    echo "<a href='manage-approvals.php?reject=".$row['id']."' class='btn btn-sm btn-outline-danger' title='Reject' onclick=\"return confirm('Reject this hostel?')\"><i data-feather='x'></i></a>";
                                                }
                                                echo "</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===== PENDING APPROVALS (BOTTOM) ===== -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm" style="border-top: 4px solid #e2a600;">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mr-3"
                                         style="width:42px;height:42px;background:rgba(226,166,0,0.12);">
                                        <i data-feather="clock" style="color:#e2a600;width:20px;height:20px;"></i>
                                    </div>
                                    <div>
                                        <h4 class="card-title mb-0">Pending Approvals</h4>
                                        <small class="text-muted">Hostels awaiting your review</small>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table id="approvals_table" class="table table-hover align-middle mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Hostel Name</th>
                                                <th>Landlord</th>
                                                <th>City</th>
                                                <th>Submitted</th>
                                                <th class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sql = "SELECT h.*, t.name as tenant_name
                                                    FROM hostels h
                                                    JOIN tenants t ON h.tenant_id = t.id
                                                    WHERE h.status = 'pending'
                                                    ORDER BY h.created_at DESC";
                                            $res = $mysqli->query($sql);
                                            $pendingCount = 0;
                                            while($row = $res->fetch_assoc()) {
                                                $pendingCount++;
                                                echo "<tr>";
                                                echo "<td class='text-muted'>".$row['id']."</td>";
                                                echo "<td><strong>".htmlentities($row['name'])."</strong></td>";
                                                echo "<td>".htmlentities($row['tenant_name'])."</td>";
                                                echo "<td>".htmlentities($row['city'])."</td>";
                                                echo "<td>".date('M d, Y', strtotime($row['created_at']))."</td>";
                                                echo "<td class='text-center'>
                                                        <a href='manage-approvals.php?approve=".$row['id']."' class='btn btn-sm btn-success mr-1' onclick=\"return confirm('Approve this hostel?')\"><i data-feather='check'></i> Approve</a>
                                                        <a href='manage-approvals.php?reject=".$row['id']."' class='btn btn-sm btn-danger mr-1' onclick=\"return confirm('Reject this hostel?')\"><i data-feather='x'></i> Reject</a>
                                                        <a href='hostel-details-admin.php?id=".$row['id']."' class='btn btn-sm btn-outline-info mr-1' title='View Details'><i data-feather='eye'></i></a>
                                                        <a href='super_impersonate.php?tenant_id=".$row['tenant_id']."' class='btn btn-sm btn-outline-secondary' title='Manage as Tenant'><i data-feather='user'></i></a>
                                                      </td>";
                                                echo "</tr>";
                                            }
                                            if($pendingCount === 0) {
                                                echo "<tr><td colspan='6' class='text-center text-muted py-5'>
                                                    <i data-feather='inbox' style='width:36px;height:36px;opacity:.35;'></i>
                                                    <p class='mt-2 mb-0'>No pending approvals — all caught up!</p>
                                                </td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

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
    <!-- DataTables -->
    <script src="../assets/extra-libs/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../assets/extra-libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(".preloader").fadeOut();
        feather.replace();
        $('#history_table').DataTable({ order: [[0, 'desc']] });
        $('#approvals_table').DataTable({ order: [[0, 'desc']] });
    </script>
</body>
</html>
