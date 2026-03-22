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
    <title><?php echo htmlentities($hostel->name); ?> - Hostel Details</title>
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/favicon.png">
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="../assets/css/custom-gradients.css" rel="stylesheet">
    
    <style>
        .hostel-banner {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('<?php echo (count($images) > 0) ? "../" . $images[0]->image_path : "../assets/images/default-hostel.jpg"; ?>');
            background-size: cover;
            background-position: center;
            height: 250px;
            border-radius: 15px;
            display: flex;
            align-items: flex-end;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        .gallery-item {
            border-radius: 12px;
            overflow: hidden;
            height: 150px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        .gallery-item:hover {
            transform: scale(1.03);
        }
        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .stat-box {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 15px;
            border: 1px solid rgba(0,0,0,0.05);
        }
        .type-pill {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 12px 15px;
            margin-bottom: 12px;
            border-left: 3px solid #17c788;
            transition: all 0.3s ease;
        }
        .type-pill:hover {
            background: #fff;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .amenity-chip {
            display: inline-flex;
            align-items: center;
            padding: 8px 15px;
            background: rgba(23, 199, 136, 0.05);
            color: #17c788;
            border-radius: 50px;
            margin: 0 8px 8px 0;
            font-size: 0.85rem;
            font-weight: 500;
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
        .lightbox.active { display: flex; }
        .lightbox img { max-width: 90%; max-height: 90%; border-radius: 8px; }
        .lightbox-close { position: absolute; top: 20px; right: 40px; color: white; font-size: 40px; cursor: pointer; }
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
            <div class="container-fluid pt-4">
                
                <div class="hostel-banner text-white">
                    <div class="w-100">
                        <div class="d-flex justify-content-between align-items-end">
                            <div>
                                <h1 class="font-weight-bold mb-1"><?php echo htmlentities($hostel->name); ?></h1>
                                <p class="mb-0 opacity-7 text-uppercase small letter-spacing-1">
                                    <i data-feather="map-pin" class="feather-xs mr-1"></i> 
                                    <?php echo htmlentities($hostel->address . ', ' . $hostel->city); ?>
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="badge badge-light-primary text-primary px-3 py-2 rounded-pill font-weight-medium">
                                    <?php echo ucfirst($hostel->status); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-body">
                                <h5 class="card-title text-dark font-weight-bold mb-3">Hostel Overview</h5>
                                <p class="text-muted leading-relaxed"><?php echo nl2br(htmlentities($hostel->description)); ?></p>
                                
                                <h5 class="card-title text-dark font-weight-bold mt-4 mb-3">Amenities</h5>
                                <div class="d-flex flex-wrap">
                                    <?php foreach($amenities as $amenity): ?>
                                    <span class="amenity-chip">
                                        <i class="fas <?php echo $amenity->icon_class; ?> mr-2"></i>
                                        <?php echo $amenity->amenity_name; ?>
                                    </span>
                                    <?php endforeach; ?>
                                </div>

                                <h5 class="card-title text-dark font-weight-bold mt-4 mb-3">Gallery</h5>
                                <div class="row g-3">
                                    <?php foreach($images as $img): ?>
                                    <div class="col-6 col-md-3 mb-3">
                                        <div class="gallery-item shadow-sm" onclick="openLightbox('../<?php echo $img->image_path; ?>')">
                                            <img src="../<?php echo $img->image_path; ?>" alt="Gallery">
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-body">
                                <h5 class="card-title text-dark font-weight-bold mb-4">Management Console</h5>
                                
                                <div class="row text-center mb-4">
                                    <div class="col-6 border-right">
                                        <h4 class="text-primary font-weight-bold mb-0"><?php echo $available_rooms; ?></h4>
                                        <small class="text-muted text-uppercase font-weight-medium">Vacant Rooms</small>
                                    </div>
                                    <div class="col-6">
                                        <h4 class="text-success font-weight-bold mb-0"><?php echo count($types); ?></h4>
                                        <small class="text-muted text-uppercase font-weight-medium">Room Types</small>
                                    </div>
                                </div>

                                <?php if($tm->isSuperAdmin()): ?>
                                <div class="d-grid gap-2 mb-4">
                                    <?php if($hostel->status != 'approved'): ?>
                                    <a href="manage-approvals.php?approve=<?php echo $hostel_id; ?>" class="btn btn-success btn-block rounded-pill py-2 shadow-sm mb-2" onclick="return confirm('Approve this hostel listing?')">
                                        <i data-feather="check" class="feather-sm mr-2"></i> Approve Listing
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if($hostel->status == 'approved'): ?>
                                    <a href="manage-approvals.php?suspend=<?php echo $hostel_id; ?>" class="btn btn-warning btn-block rounded-pill py-2 shadow-sm mb-2" onclick="return confirm('Suspend this hostel?')">
                                        <i data-feather="pause" class="feather-sm mr-2"></i> Suspend
                                    </a>
                                    <?php endif; ?>

                                    <?php if($hostel->status != 'rejected'): ?>
                                    <a href="manage-approvals.php?reject=<?php echo $hostel_id; ?>" class="btn btn-danger btn-block rounded-pill py-2 shadow-sm mb-2" onclick="return confirm('Reject this hostel listing?')">
                                        <i data-feather="x" class="feather-sm mr-2"></i> Reject
                                    </a>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                                
                                <h6 class="font-weight-bold text-dark mb-3">Room Configurations</h6>
                                <?php foreach($types as $type): ?>
                                <div class="type-pill d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0 text-dark font-weight-medium"><?php echo $type->type_name; ?></h6>
                                        <small class="text-muted"><?php echo $type->available_count; ?> Available</small>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-primary font-weight-bold d-block">KSh <?php echo number_format($type->price_per_month, 0); ?></span>
                                        <small class="text-muted">per month</small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                
                                <hr class="my-4">
                                
                                <h6 class="font-weight-bold text-dark mb-3">Proprietor Contact</h6>
                                <div class="d-flex align-items-center mb-2">
                                    <div class="bg-light-info text-info p-2 rounded-circle mr-3">
                                        <i data-feather="phone" class="feather-xs"></i>
                                    </div>
                                    <span class="text-muted small"><?php echo htmlentities($hostel->phone); ?></span>
                                </div>
                                <div class="d-flex align-items-center mb-4">
                                    <div class="bg-light-info text-info p-2 rounded-circle mr-3">
                                        <i data-feather="mail" class="feather-xs"></i>
                                    </div>
                                    <span class="text-muted small"><?php echo htmlentities($hostel->email); ?></span>
                                </div>
                                
                                <div class="mt-4">
                                    <?php if(!$tm->isSuperAdmin()): ?>
                                    <a href="edit-hostel.php?id=<?php echo $hostel_id; ?>" class="btn btn-primary btn-block rounded-pill py-2">
                                        <i data-feather="edit-3" class="feather-sm mr-2"></i> Edit Property
                                    </a>
                                    <?php endif; ?>
                                    <a href="<?php echo $tm->isSuperAdmin() ? 'manage-approvals.php' : 'manage-hostels.php'; ?>" class="btn btn-light btn-block rounded-pill py-2 mt-2">
                                        <i data-feather="arrow-left" class="feather-sm mr-2"></i> Back
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php include 'includes/footer.php' ?>
        </div>
    </div>
    
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
        $(document).ready(function() {
            $(".preloader").fadeOut();
            
            window.openLightbox = function(imageSrc) {
                document.getElementById('lightbox-img').src = imageSrc;
                document.getElementById('lightbox').classList.add('active');
            };
            
            window.closeLightbox = function() {
                document.getElementById('lightbox').classList.remove('active');
            };
        });
    </script>
</body>

</html>
