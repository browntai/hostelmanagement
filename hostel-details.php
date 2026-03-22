<?php
    session_start();
    include('includes/dbconn.php');
    include('includes/hostel-helper.php');

    // Get hostel ID
    if(!isset($_GET['id']) || empty($_GET['id'])){
        header("Location: index.php");
        exit;
    }

    $hostel_id = intval($_GET['id']);
    $hostel = getHostelById($mysqli, $hostel_id);

if (!$hostel) {
    echo "Hostel not found.";
    exit;
}

// Visibility Check: Only 'approved' hostels are public.
$canView = false;
if ($hostel->status === 'approved') {
    $canView = true;
} elseif (isset($_SESSION['id'])) {
    if ($_SESSION['role'] === 'admin') {
        $canView = true;
    } elseif ($_SESSION['role'] === 'landlord' && $_SESSION['tenant_id'] == $hostel->tenant_id) {
        $canView = true;
    }
}

if (!$canView) {
    header("Location: index.php?error=not_available");
    exit();
}

    // Get related data
    $images = getHostelImages($mysqli, $hostel_id);
    $amenities = getHostelAmenities($mysqli, $hostel_id);
    $types = getHostelTypes($mysqli, $hostel_id);
    $available_rooms = getAvailableRoomsCount($mysqli, $hostel_id);

    // Get caretaker contact (public) — prefer caretaker assigned to this specific hostel
    $caretaker = null;
    $ct_stmt = $mysqli->prepare("SELECT full_name, contact_no, email FROM users WHERE role = 'caretaker' AND assigned_hostel_id = ? LIMIT 1");
    $ct_stmt->bind_param('i', $hostel_id);
    $ct_stmt->execute();
    $ct_result = $ct_stmt->get_result();
    if ($ct_result->num_rows > 0) {
        $caretaker = $ct_result->fetch_object();
    } else {
        // Fallback: any caretaker under the same tenant
        $ct_stmt2 = $mysqli->prepare("SELECT full_name, contact_no, email FROM users WHERE role = 'caretaker' AND tenant_id = ? LIMIT 1");
        $ct_stmt2->bind_param('i', $hostel->tenant_id);
        $ct_stmt2->execute();
        $ct_result2 = $ct_stmt2->get_result();
        if ($ct_result2->num_rows > 0) {
            $caretaker = $ct_result2->fetch_object();
        }
    }
?>

<?php 
    $page_title = htmlentities($hostel->name) . " — HostelHub";
    include('includes/public-header.php'); 
?>
    
    <style>
        .detail-gallery {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 10px;
            margin-bottom: 30px;
            max-height: 500px;
        }
        .gallery-main { grid-row: 1 / 3; }
        .gallery-main img, .gallery-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: var(--pub-radius-sm);
            cursor: pointer;
            transition: var(--pub-transition);
        }
        .gallery-main img:hover, .gallery-thumb img:hover {
            filter: brightness(0.9);
        }
        .gallery-thumb { height: 245px; }
        .detail-section {
            margin-bottom: 32px;
        }
        .detail-section h4 {
            font-weight: 700;
            color: var(--pub-navy);
            padding-bottom: 10px;
            margin-bottom: 18px;
            border-bottom: 2px solid rgba(46, 94, 153, 0.12);
            font-size: 1.15rem;
        }
        .amenity-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 14px;
        }
        .amenity-tag {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: var(--pub-radius-sm);
            background: rgba(46, 94, 153, 0.06);
            transition: var(--pub-transition);
        }
        .amenity-tag:hover { background: rgba(46, 94, 153, 0.12); }
        .amenity-tag i {
            color: var(--pub-blue);
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }
        .amenity-tag span { color: #4a5568; font-size: 0.9rem; }
        .type-card {
            border: 1px solid rgba(123, 164, 208, 0.15);
            border-radius: var(--pub-radius);
            padding: 22px;
            margin-bottom: 14px;
            background: #fff;
            transition: var(--pub-transition);
        }
        .type-card:hover {
            box-shadow: var(--pub-shadow-sm);
            border-color: rgba(46, 94, 153, 0.25);
        }
        .type-card h5 {
            color: var(--pub-blue);
            font-weight: 700;
            margin-bottom: 8px;
        }
        .type-price {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--pub-navy);
        }
        .booking-sidebar {
            position: sticky;
            top: 90px;
            background: #fff;
            border: 2px solid rgba(46, 94, 153, 0.15);
            border-radius: var(--pub-radius);
            padding: 24px;
            box-shadow: var(--pub-shadow-sm);
        }
        .booking-sidebar h4 {
            color: var(--pub-navy);
            font-weight: 700;
            text-align: center;
            margin-bottom: 6px;
        }
        .contact-box {
            background: rgba(46, 94, 153, 0.04);
            padding: 16px;
            border-radius: var(--pub-radius-sm);
            margin-top: 16px;
        }
        .contact-box h5 {
            color: var(--pub-navy);
            font-weight: 700;
            font-size: 0.95rem;
            margin-bottom: 12px;
        }
        .contact-box p {
            margin-bottom: 8px;
            font-size: 0.9rem;
            color: #4a5568;
        }
        .contact-box p i {
            color: var(--pub-blue);
            margin-right: 8px;
            width: 16px;
            text-align: center;
        }
        .lightbox {
            display: none;
            position: fixed;
            z-index: 9999;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.92);
            align-items: center;
            justify-content: center;
        }
        .lightbox.active { display: flex; }
        .lightbox img { max-width: 90%; max-height: 90%; border-radius: 8px; }
        .lightbox-close {
            position: absolute;
            top: 20px; right: 40px;
            color: white;
            font-size: 36px;
            cursor: pointer;
            transition: var(--pub-transition);
        }
        .lightbox-close:hover { color: var(--pub-soft); }
        .review-card {
            background: #fff;
            border: 1px solid rgba(123, 164, 208, 0.12);
            border-radius: var(--pub-radius-sm);
            padding: 18px;
            margin-bottom: 14px;
            transition: var(--pub-transition);
        }
        .review-card:hover { box-shadow: var(--pub-shadow-sm); }
        @media (max-width: 768px) {
            .detail-gallery { grid-template-columns: 1fr; max-height: none; }
            .gallery-main { grid-row: auto; }
            .gallery-thumb { height: 180px; }
        }
    </style>
</head>

<body class="pub-page">

    <?php include('includes/public-nav.php'); ?>

    <!-- ── Hero ── -->
    <?php 
    $hero_bg = (count($images) > 0) ? $images[0]->image_path : 'assets/images/hostel-img.jpg';
    ?>
    <section class="pub-hero pub-hero-small" style="background: linear-gradient(135deg, rgba(13, 20, 50, 0.82) 0%, rgba(13, 20, 50, 0.5) 100%), url('<?php echo $hero_bg; ?>') center/cover no-repeat;">
        <div class="container" style="text-align:left;">
            <div class="pub-hero-content">
                <div class="d-flex align-items-center flex-wrap gap-3">
                    <h1 class="mb-0"><?php echo htmlentities($hostel->name); ?></h1>
                    <?php if(isServiceEnabled($mysqli, $hostel_id, 'daycare')): ?>
                        <span class="badge badge-success py-2 px-3" style="background: var(--pub-gradient); border:none; border-radius: 20px; font-size: 0.85rem;">
                            <i class="fas fa-child mr-1"></i> Daycare Available
                        </span>
                    <?php endif; ?>
                </div>
                <p style="margin-top:10px; margin-bottom:0; max-width:none;"><i class="fas fa-map-marker-alt"></i> <?php echo htmlentities($hostel->address . ', ' . $hostel->city); ?></p>
            </div>
        </div>
    </section>

    <!-- ── Main Content ── -->
    <div class="container" style="padding-top:40px; padding-bottom:60px;">
        <div class="row">
            <div class="col-lg-8">
                <!-- Image Gallery -->
                <?php if(count($images) > 0): ?>
                <div class="detail-gallery">
                    <div class="gallery-main">
                        <img src="<?php echo $images[0]->image_path; ?>" alt="Main" onclick="openLightbox('<?php echo $images[0]->image_path; ?>')">
                    </div>
                    <?php if(isset($images[1])): ?>
                    <div class="gallery-thumb">
                        <img src="<?php echo $images[1]->image_path; ?>" alt="Thumb" onclick="openLightbox('<?php echo $images[1]->image_path; ?>')">
                    </div>
                    <?php endif; ?>
                    <?php if(isset($images[2])): ?>
                    <div class="gallery-thumb">
                        <img src="<?php echo $images[2]->image_path; ?>" alt="Thumb" onclick="openLightbox('<?php echo $images[2]->image_path; ?>')">
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Description -->
                <div class="detail-section">
                    <h4>About This Property</h4>
                    <p style="color:#4a5568; line-height:1.8;"><?php echo nl2br(htmlentities($hostel->description)); ?></p>
                </div>

                <!-- Room Types & Pricing -->
                <div class="detail-section">
                    <h4>Available Room Types</h4>
                    <?php foreach($types as $type): ?>
                    <div class="type-card">
                        <div class="row align-items-center">
                            <div class="col-md-7">
                                <h5><?php echo $type->type_name; ?></h5>
                                <p style="color:#5a6b7d; font-size:0.9rem; margin-bottom:6px;"><?php echo $type->description; ?></p>
                                <span style="font-size:0.85rem; color:var(--pub-blue); font-weight:600;">
                                    <i class="fas fa-door-open" style="margin-right:4px;"></i> <?php echo $type->available_count; ?> units available
                                </span>
                            </div>
                            <div class="col-md-5 text-right">
                                <div class="type-price">KSh <?php echo number_format($type->price_per_month, 0); ?></div>
                                <small style="color:#8a9bb2;">per month</small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Amenities -->
                <div class="detail-section">
                    <h4>Amenities & Facilities</h4>
                    <div class="amenity-grid">
                        <?php foreach($amenities as $amenity): ?>
                        <div class="amenity-tag">
                            <i class="fas <?php echo $amenity->icon_class; ?>"></i>
                            <span><?php echo $amenity->amenity_name; ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Location -->
                <div class="detail-section">
                    <h4>Location</h4>
                    <div class="contact-box">
                        <p><i class="fas fa-map-marker-alt"></i> <strong>Address:</strong> <?php echo htmlentities($hostel->address); ?></p>
                        <p><i class="fas fa-city"></i> <strong>City:</strong> <?php echo htmlentities($hostel->city); ?></p>
                        <?php if($hostel->state): ?>
                        <p><i class="fas fa-map"></i> <strong>County:</strong> <?php echo htmlentities($hostel->state); ?></p>
                        <?php endif; ?>
                        <?php if($hostel->postal_code): ?>
                        <p><i class="fas fa-mail-bulk"></i> <strong>Postal Code:</strong> <?php echo htmlentities($hostel->postal_code); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Reviews -->
                <div class="detail-section">
                    <h4>Guest Reviews</h4>
                    <?php
                    $rev_q = "SELECT r.*, u.full_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.hostel_id = ? ORDER BY r.created_at DESC";
                    $rev_stmt = $mysqli->prepare($rev_q);
                    $rev_stmt->bind_param('i', $hostel_id);
                    $rev_stmt->execute();
                    $rev_res = $rev_stmt->get_result();
                    
                    if($rev_res->num_rows > 0):
                        while($row = $rev_res->fetch_object()):
                    ?>
                    <div class="review-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 style="font-weight:700; color:var(--pub-navy); margin-bottom:2px;"><?php echo htmlentities($row->full_name); ?></h6>
                                <small style="color:#8a9bb2;"><?php echo date('M d, Y', strtotime($row->created_at)); ?></small>
                            </div>
                            <span style="color:#f59e0b;">
                                <?php for($i=0; $i<$row->rating; $i++) echo '<i class="fas fa-star"></i>'; ?>
                                <?php for($i=$row->rating; $i<5; $i++) echo '<i class="far fa-star"></i>'; ?>
                            </span>
                        </div>
                        <p style="margin:10px 0 0; color:#4a5568; font-size:0.9rem; line-height:1.6;"><?php echo htmlentities($row->comment); ?></p>
                    </div>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <div style="text-align:center; padding:40px 20px; color:#8a9bb2;">
                        <i class="far fa-comment-dots" style="font-size:2.5rem; margin-bottom:14px; display:block; color:var(--pub-soft);"></i>
                        <p>No reviews yet. Be the first to review!</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="booking-sidebar">
                    <h4>Book This Property</h4>
                    <p style="text-align:center; color:#5a6b7d; font-size:0.9rem; margin-bottom:20px;">
                        <i class="fas fa-door-open" style="color:var(--pub-blue);"></i> <?php echo $available_rooms; ?> rooms available
                    </p>
                    
                    <?php if(isset($_SESSION['login'])): ?>
                        <a href="client/book-hostel.php?hostel_id=<?php echo $hostel_id; ?>" class="btn-auth-submit" style="display:block; text-align:center; text-decoration:none;">
                            <i class="fas fa-calendar-check"></i> &nbsp;Book Now
                        </a>
                    <?php else: ?>
                        <a href="client-registration.php?hostel_id=<?php echo $hostel_id; ?>" class="btn-auth-submit" style="display:block; text-align:center; text-decoration:none;">
                            <i class="fas fa-user-plus"></i> &nbsp;Register & Book
                        </a>
                        <p style="text-align:center; margin-top:10px; font-size:0.85rem; color:#5a6b7d;">
                            Already have an account? <a href="login.php?hostel_id=<?php echo $hostel_id; ?>" style="color:var(--pub-blue); font-weight:600;">Login</a>
                        </p>
                    <?php endif; ?>

                    <hr style="border-color:rgba(123,164,208,0.12); margin:20px 0;">

                    <div class="contact-box">
                        <h5><i class="fas fa-address-card" style="margin-right:6px;"></i> Contact Information</h5>

                        <?php if($caretaker): ?>
                            <p style="font-size:0.82rem; color:#8a9bb2; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:6px; font-weight:600;">Caretaker</p>
                            <p style="font-weight:600; color:var(--pub-navy); margin-bottom:8px;">
                                <i class="fas fa-user-tie" style="color:var(--pub-blue); margin-right:6px;"></i><?php echo htmlentities($caretaker->full_name); ?>
                            </p>
                            <?php if($caretaker->contact_no): ?>
                            <p><i class="fas fa-phone"></i> <?php echo htmlentities($caretaker->contact_no); ?></p>
                            <?php endif; ?>
                            <?php if($caretaker->email): ?>
                            <p><i class="fas fa-envelope"></i> <?php echo htmlentities($caretaker->email); ?></p>
                            <?php endif; ?>
                        <?php else: ?>
                            <p style="color:#8a9bb2; font-size:0.9rem;"><i class="fas fa-info-circle"></i> No caretaker assigned yet.</p>
                        <?php endif; ?>

                        <?php if(isset($_SESSION['login'])): ?>
                            <?php 
                            $contact_no = isset($hostel->landlord_contact) ? $hostel->landlord_contact : $hostel->phone;
                            $email_addr = isset($hostel->landlord_email) ? $hostel->landlord_email : $hostel->email;
                            if ($contact_no || $email_addr):
                            ?>
                            <hr style="border-color:rgba(123,164,208,0.15); margin:12px 0;">
                            <p style="font-size:0.82rem; color:#8a9bb2; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:6px; font-weight:600;">Landlord</p>
                            <?php if($contact_no): ?>
                            <p><i class="fas fa-phone"></i> <?php echo htmlentities($contact_no); ?></p>
                            <?php endif; ?>
                            <?php if($email_addr): ?>
                            <p><i class="fas fa-envelope"></i> <?php echo htmlentities($email_addr); ?></p>
                            <?php endif; ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <?php if(isset($_SESSION['login'])): ?>
                    <hr style="border-color:rgba(123,164,208,0.12); margin:20px 0;">
                    <button id="wishlistBtn" class="btn-pub-outline" style="width:100%; justify-content:center;">
                        <i class="far fa-heart"></i> Add to Wishlist
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Lightbox -->
    <div id="lightbox" class="lightbox" onclick="closeLightbox()">
        <span class="lightbox-close">&times;</span>
        <img id="lightbox-img" src="" alt="Full size">
    </div>

<?php include('includes/public-footer.php'); ?>


        function openLightbox(imageSrc) {
            document.getElementById('lightbox-img').src = imageSrc;
            document.getElementById('lightbox').classList.add('active');
        }
        
        function closeLightbox() {
            document.getElementById('lightbox').classList.remove('active');
        }

        $(document).ready(function() {
            // Wishlist Handler
            $('#wishlistBtn').on('click', function(e) {
                e.preventDefault();
                const btn = $(this);
                const hostelId = <?php echo $hostel_id; ?>;
                
                btn.prop('disabled', true).css('opacity', '0.7');

                $.ajax({
                    url: 'includes/toggle-wishlist.php',
                    type: 'POST',
                    data: { hostel_id: hostelId },
                    success: function(response) {
                        try {
                            const res = typeof response === 'object' ? response : JSON.parse(response);
                            if(res.status === 'added') {
                                btn.html('<i class="fas fa-heart"></i> In Wishlist');
                                btn.css({'background':'var(--pub-gradient)', 'color':'#fff', 'border-color':'transparent'});
                            } else if (res.status === 'removed') {
                                btn.html('<i class="far fa-heart"></i> Add to Wishlist');
                                btn.css({'background':'transparent', 'color':'var(--pub-blue)', 'border-color':'var(--pub-blue)'});
                            } else {
                                alert('Error: ' + (res.message || 'Unknown error'));
                            }
                        } catch(e) {
                            alert('Server Error');
                        }
                    },
                    error: function() { alert('Connection Error'); },
                    complete: function() {
                        btn.prop('disabled', false).css('opacity', '1');
                    }
                });
            });

            // Check initial wishlist status
            <?php if(isset($_SESSION['login'])): ?>
            $.get('includes/check-wishlist.php?hostel_id=<?php echo $hostel_id; ?>', function(response) {
                try {
                    const res = typeof response === 'object' ? response : JSON.parse(response);
                    if(res.exists) {
                        $('#wishlistBtn').html('<i class="fas fa-heart"></i> In Wishlist');
                        $('#wishlistBtn').css({'background':'var(--pub-gradient)', 'color':'#fff', 'border-color':'transparent'});
                    }
                } catch(e) {}
            });
            <?php endif; ?>
        });
    </script>
</body>

</html>
