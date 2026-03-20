<?php
    session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    check_login();

    include('../includes/tenant_manager.php');
    $tm = new TenantManager($mysqli);
    $tenantId = $tm->getCurrentTenantId();

    include('../includes/hostel-helper.php');

    if(!isset($_GET['id']) || empty($_GET['id'])){
        echo "<script>window.location.href='manage-hostels.php';</script>";
        exit;
    }

    $hostel_id = intval($_GET['id']);
    // Pass tenantId to ensure tenant isolation
    $hostel = getHostelById($mysqli, $hostel_id, $tenantId);

    if(!$hostel){
        echo "<script>alert('Hostel not found or access denied.');</script>";
        echo "<script>window.location.href='manage-hostels.php';</script>";
        exit;
    }

    // Get related data
    $images = getHostelImages($mysqli, $hostel_id);
    $amenities = getHostelAmenities($mysqli, $hostel_id);
    $types = getHostelTypes($mysqli, $hostel_id);
    $available_rooms = getAvailableRoomsCount($mysqli, $hostel_id);
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/favicon.png">
    <title>Hostel Details - Hostel Management System</title>
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="../assets/css/custom-gradients.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="../dist/css/icons/font-awesome/css/fontawesome-all.min.css" rel="stylesheet">
    
    <style>
        .hostel-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .image-gallery {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 10px;
            margin-bottom: 30px;
            max-height: 400px;
        }
        .gallery-main {
            grid-row: 1 / 3;
        }
        .gallery-main img, .gallery-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
        }
        .gallery-thumb {
            height: 195px;
        }
        .type-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            background: #fff;
        }
        .amenity-badge {
            display: inline-block;
            padding: 5px 10px;
            background: #f8f9fa;
            border-radius: 20px;
            margin-right: 5px;
            margin-bottom: 5px;
            border: 1px solid #e9ecef;
        }
        .amenity-badge i {
            color: #28a745;
            margin-right: 5px;
        }
        .lightbox {
            display: none;
            position: fixed;
            z-index: 9999;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            align-items: center;
            justify-content: center;
        }
        .lightbox.active {
            display: flex;
        }
        .lightbox img {
            max-width: 90%;
            max-height: 90%;
        }
        .lightbox-close {
            position: absolute;
            top: 20px;
            right: 40px;
            color: white;
            font-size: 40px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="preloader">
        <div class="lds-ripple">
            <div class="lds-pos"></div>
            <div class="lds-pos"></div>
        </div>
    </div>
    
    <div id="main-wrapper" data-theme="light" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed" data-boxed-layout="full">
        
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
                        <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Hostel Details</h4>
                        <div class="d-flex align-items-center">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb m-0 p-0">
                                    <li class="breadcrumb-item"><a href="manage-hostels.php">Manage Hostels</a></li>
                                    <li class="breadcrumb-item text-muted active" aria-current="page"><?php echo htmlentities($hostel->name); ?></li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-body">
                                <div class="hostel-header">
                                    <h2 class="text-white mb-0"><?php echo htmlentities($hostel->name); ?></h2>
                                    <p class="mb-0 text-white-50"><i class="fas fa-map-marker-alt"></i> <?php echo htmlentities($hostel->address . ', ' . $hostel->city); ?></p>
                                </div>
                                
                                <!-- Image Gallery -->
                                <?php if(count($images) > 0): ?>
                                <div class="image-gallery">
                                    <div class="gallery-main">
                                        <img src="../<?php echo $images[0]->image_path; ?>" alt="Main" onclick="openLightbox('../<?php echo $images[0]->image_path; ?>')">
                                    </div>
                                    <?php if(isset($images[1])): ?>
                                    <div class="gallery-thumb">
                                        <img src="../<?php echo $images[1]->image_path; ?>" alt="Thumb" onclick="openLightbox('../<?php echo $images[1]->image_path; ?>')">
                                    </div>
                                    <?php endif; ?>
                                    <?php if(isset($images[2])): ?>
                                    <div class="gallery-thumb">
                                        <img src="../<?php echo $images[2]->image_path; ?>" alt="Thumb" onclick="openLightbox('../<?php echo $images[2]->image_path; ?>')">
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                                
                                <h4 class="card-title mt-4">Description</h4>
                                <p class="card-text"><?php echo nl2br(htmlentities($hostel->description)); ?></p>
                                
                                <h4 class="card-title mt-4">Amenities</h4>
                                <div>
                                    <?php foreach($amenities as $amenity): ?>
                                    <div class="amenity-badge">
                                        <i class="fas <?php echo $amenity->icon_class; ?>"></i>
                                        <?php echo $amenity->amenity_name; ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Status & Pricing</h4>
                                <hr>
                                
                                <div class="mb-3">
                                    <label class="text-muted mb-0">Status</label>
                                    <div>
                                        <span class="badge badge-<?php 
                                            echo $hostel->status == 'approved' ? 'success' : ($hostel->status == 'suspended' ? 'warning' : 'danger'); 
                                        ?> px-3 py-2"><?php echo ucfirst($hostel->status); ?></span>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="text-muted mb-0">Available Rooms</label>
                                    <h3 class="text-primary"><?php echo $available_rooms; ?></h3>
                                </div>

                                <?php if($tm->isSuperAdmin()): ?>
                                <h5 class="mt-4">Management Actions</h5>
                                <div class="btn-group-vertical w-100">
                                    <?php if($hostel->status != 'approved'): ?>
                                    <a href="manage-approvals.php?approve=<?php echo $hostel_id; ?>" class="btn btn-success mb-2" onclick="return confirm('Approve this hostel listing?')">
                                        <i class="fas fa-check mr-2"></i> Approve Hostel
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if($hostel->status == 'approved'): ?>
                                    <a href="manage-approvals.php?suspend=<?php echo $hostel_id; ?>" class="btn btn-warning mb-2" onclick="return confirm('Suspend this hostel?')">
                                        <i class="fas fa-slash mr-2"></i> Suspend Hostel
                                    </a>
                                    <?php endif; ?>

                                    <?php if($hostel->status != 'rejected'): ?>
                                    <a href="manage-approvals.php?reject=<?php echo $hostel_id; ?>" class="btn btn-danger mb-2" onclick="return confirm('Reject this hostel listing?')">
                                        <i class="fas fa-times mr-2"></i> Reject Hostel
                                    </a>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                                
                                <h5 class="mt-4">Room Types</h5>
                                <?php foreach($types as $type): ?>
                                <div class="type-card">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1"><?php echo $type->type_name; ?></h6>
                                            <small class="text-muted"><?php echo $type->available_count; ?> units</small>
                                        </div>
                                        <div class="text-right">
                                            <h5 class="mb-0 text-dark">KSh <?php echo number_format($type->price_per_month, 2); ?></h5>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                
                                <h5 class="mt-4">Contact Info</h5>
                                <p class="mb-2"><i class="fas fa-phone mr-2"></i> <?php echo htmlentities($hostel->phone); ?></p>
                                <p class="mb-2"><i class="fas fa-envelope mr-2"></i> <?php echo htmlentities($hostel->email); ?></p>
                                
                                <hr>
                                
                                <div class="mt-3">
                                    <?php if(!$tm->isSuperAdmin()): ?>
                                    <a href="edit-hostel.php?id=<?php echo $hostel_id; ?>" class="btn btn-info btn-block">
                                        <i class="fas fa-edit mr-2"></i> Edit Hostel
                                    </a>
                                    <a href="manage-hostels.php" class="btn btn-secondary btn-block">
                                        <i class="fas fa-arrow-left mr-2"></i> Back to List
                                    </a>
                                    <?php else: ?>
                                    <a href="manage-approvals.php" class="btn btn-secondary btn-block">
                                        <i class="fas fa-arrow-left mr-2"></i> Back to Approvals
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php include 'includes/footer.php' ?>
        </div>
    </div>
    
    <!-- Lightbox -->
    <div id="lightbox" class="lightbox" onclick="closeLightbox()">
        <span class="lightbox-close">&times;</span>
        <img id="lightbox-img" src="" alt="Full size">
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
        $(".preloader").fadeOut();
        
        function openLightbox(imageSrc) {
            document.getElementById('lightbox-img').src = imageSrc;
            document.getElementById('lightbox').classList.add('active');
        }
        
        function closeLightbox() {
            document.getElementById('lightbox').classList.remove('active');
        }
    </script>
</body>

</html>
