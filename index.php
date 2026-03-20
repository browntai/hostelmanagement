<?php
    session_start();
    include('includes/dbconn.php');
    include('includes/hostel-helper.php');

    // Get filter parameters
    $city_filter = isset($_GET['city']) ? $_GET['city'] : '';
    $type_filters = isset($_GET['types']) ? $_GET['types'] : [];
    $amenity_filters = isset($_GET['amenities']) ? $_GET['amenities'] : [];
    $min_price = isset($_GET['min_price']) ? $_GET['min_price'] : '';
    $max_price = isset($_GET['max_price']) ? $_GET['max_price'] : '';

    // Pagination
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $per_page = 12;
    $offset = ($page - 1) * $per_page;

    // Build filters array
    $filters = [
        'limit' => $per_page,
        'offset' => $offset
    ];

    if(!empty($city_filter)) $filters['city'] = $city_filter;
    if(!empty($type_filters)) $filters['types'] = $type_filters;
    if(!empty($amenity_filters)) $filters['amenities'] = $amenity_filters;
    if(!empty($min_price)) $filters['min_price'] = $min_price;
    if(!empty($max_price)) $filters['max_price'] = $max_price;

    // Get hostels
    $hostels = searchHostels($mysqli, $filters);

    // Get all available filter options
    $all_types = getAllHostelTypes($mysqli);
    $all_amenities = getAllAmenities($mysqli);

    // Get unique cities
    $city_query = "SELECT DISTINCT city FROM hostels WHERE status='approved' ORDER BY city";
    $city_result = $mysqli->query($city_query);
    $cities = [];
    while($row = $city_result->fetch_object()){
        $cities[] = $row->city;
    }

    // Quick stats
    $stat_hostels = $mysqli->query("SELECT COUNT(*) as c FROM hostels WHERE status='approved'")->fetch_object()->c;
    $stat_rooms = $mysqli->query("SELECT COUNT(*) as c FROM rooms r JOIN hostels h ON r.hostel_id=h.id WHERE h.status='approved' AND r.status='available'")->fetch_object()->c;
    $stat_cities = count($cities);
    $stat_tenants = $mysqli->query("SELECT COUNT(*) as c FROM users WHERE role='client'")->fetch_object()->c;
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Find and book premium hostel accommodations across Kenya. Browse verified properties with real photos, reviews, and instant booking.">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon.png">
    <title>HostelHub — Find Your Perfect Accommodation</title>
    <link href="dist/css/style.min.css" rel="stylesheet">
    <link href="assets/css/public-pages.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body class="pub-page">

    <!-- ── Navigation ── -->
    <nav class="pub-navbar" id="pubNav">
        <div class="container">
            <a class="pub-nav-brand" href="index.php">
                <img src="assets/images/big/icon.png" alt="HostelHub">
                <span>HostelHub</span>
            </a>

            <button class="pub-nav-toggle" onclick="document.getElementById('navLinks').classList.toggle('show')">
                <i class="fas fa-bars"></i>
            </button>

            <ul class="pub-nav-links" id="navLinks">
                <li><a href="index.php" class="active"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="gallery.php"><i class="fas fa-images"></i> Gallery</a></li>
                <li><a href="about.php"><i class="fas fa-info-circle"></i> About</a></li>
                <li><a href="contact.php"><i class="fas fa-envelope"></i> Contact</a></li>
            </ul>

            <div class="pub-nav-actions">
                <?php if(isset($_SESSION['login'])): ?>
                    <a href="client/dashboard.php" class="btn-pub-solid"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <?php else: ?>
                    <a href="login.php" class="btn-pub-outline">Login</a>
                    <a href="client-registration.php" class="btn-pub-solid">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- ══════════════════════════════════════════════════════════
         HERO BANNER
         ══════════════════════════════════════════════════════════ -->
    <section class="pub-hero" style="position:relative; height:auto; min-height:500px; padding: 180px 0 140px; background-image:url('assets/images/hostel-img.jpg'); background-size:cover; background-position:center; display:flex; align-items:center;">
        <div class="overlay-black" style="background:rgba(13,20,50,0.72);"></div>
        <div class="container" style="position:relative; z-index:10;">
            <div class="pub-hero-content text-center" style="margin-bottom:0;">
                <h1 style="color:#fff; font-size:3.2rem; font-weight:700; margin-bottom:15px;">Find Your Perfect Stay</h1>
                <p style="font-size:1.15rem; color:rgba(255,255,255,0.9); max-width:700px; margin:0 auto;">Browse through our collection of verified hostels with real photos, honest reviews, and instant booking — all in one place.</p>
            </div>
        </div>
    </section>


    <!-- ══════════════════════════════════════════════════════════
         FEATURES — "What We Offer"
         ══════════════════════════════════════════════════════════ -->
    <section class="features-section">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">What We Offer</span>
                <h2>Everything You Need</h2>
                <p>From booking to move-in, we've got every step covered for your hostel search</p>
            </div>
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="feature-box hover-shadow transation-3s">
                        <div class="feature-icon"><i class="fas fa-calendar-check"></i></div>
                        <h5>Easy Booking</h5>
                        <p>Reserve your room instantly with our streamlined booking process. No paperwork needed.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="feature-box hover-shadow transation-3s">
                        <div class="feature-icon"><i class="fas fa-building"></i></div>
                        <h5>Accommodation</h5>
                        <p>Quality verified hostels with real photos and honest descriptions across all counties.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="feature-box hover-shadow transation-3s">
                        <div class="feature-icon"><i class="fas fa-list-alt"></i></div>
                        <h5>Smart Listing</h5>
                        <p>Filter by location, price, amenities, and room type to find exactly what you need.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="feature-box hover-shadow transation-3s">
                        <div class="feature-icon"><i class="fas fa-graduation-cap"></i></div>
                        <h5>Student Resources</h5>
                        <p>Hostels near universities and colleges with student-friendly pricing and amenities.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════════════════════
         RECENTLY LISTED HOSTELS — Property Grid
         ══════════════════════════════════════════════════════════ -->
    <section class="pub-section" id="browse">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Browse Properties</span>
                <h2>Recently Listed Hostels</h2>
                <p>Discover verified hostels with available rooms ready to book</p>
            </div>

            <!-- Property Search & Filters -->
            <div class="hero-search-form" style="margin-top:0; margin-bottom: 40px; box-shadow: none; border: 1px solid rgba(23, 199, 136, 0.2); background: rgba(255, 255, 255, 0.6);">
                <form method="GET" action="">
                    <div class="row align-items-end">
                        <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                            <div class="form-group mb-0">
                                <label>Location</label>
                                <select name="city" class="form-control">
                                    <option value="">All Locations</option>
                                    <?php foreach($cities as $city): ?>
                                        <option value="<?php echo $city; ?>" <?php echo ($city_filter == $city) ? 'selected' : ''; ?>>
                                            <?php echo $city; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                            <div class="form-group mb-0">
                                <label>Min Price/Month</label>
                                <input type="number" name="min_price" class="form-control" placeholder="Min" value="<?php echo $min_price; ?>">
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                            <div class="form-group mb-0">
                                <label>Max Price/Month</label>
                                <input type="number" name="max_price" class="form-control" placeholder="Max" value="<?php echo $max_price; ?>">
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <button type="submit" class="btn-search"><i class="fas fa-search"></i> Search</button>
                        </div>
                    </div>

                    <?php if(count($all_types) > 0): ?>
                    <div class="row mt-3">
                        <div class="col-12">
                            <label style="font-size:0.8rem; margin-bottom:4px;">Room Types</label>
                            <div>
                                <?php foreach($all_types as $type): ?>
                                    <label class="filter-checkbox" style="display:inline-flex; align-items:center; gap:4px; margin-right:14px; font-size:0.85rem; color:var(--theme-secondary-color); cursor:pointer;">
                                        <input type="checkbox" name="types[]" value="<?php echo $type->id; ?>"
                                               <?php echo in_array($type->id, $type_filters) ? 'checked' : ''; ?>>
                                        <?php echo $type->type_name; ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </form>
            </div>

            <div class="row">
                <?php if(count($hostels) > 0): ?>
                    <?php foreach($hostels as $hostel):
                        $featured_image = getHostelFeaturedImage($mysqli, $hostel->id);
                        $lowest_price = getHostelLowestPrice($mysqli, $hostel->id);
                        $amenities = getHostelAmenities($mysqli, $hostel->id);
                        $available_rooms = getAvailableRoomsCount($mysqli, $hostel->id);
                    ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="pub-hostel-card hover-shadow transation-3s" style="border-radius:var(--pub-radius); overflow:hidden;">
                            <!-- Image with hover zoom -->
                            <div class="card-img-wrap hover-zoomer" style="position:relative;">
                                <img src="<?php echo $featured_image; ?>" alt="<?php echo htmlentities($hostel->name); ?>"
                                     onerror="this.src='assets/images/hostel-placeholder.jpg'"
                                     style="height:230px; width:100%; object-fit:cover;">
                                <!-- Badges -->
                                <span class="property-badge" style="left:14px;">New</span>
                                <span class="rooms-badge"><?php echo $available_rooms; ?> rooms</span>
                            </div>

                            <!-- Card Body -->
                            <div class="card-body-inner">
                                <h5><?php echo htmlentities($hostel->name); ?></h5>
                                <p class="location-text">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo htmlentities($hostel->city); ?>
                                </p>

                                <div class="price-row">
                                    <span class="price-amount">
                                        KSh <?php echo number_format($lowest_price, 0); ?>
                                        <small>/month</small>
                                    </span>
                                </div>
                            </div>

                            <!-- Amenities Bar -->
                            <div class="amenity-bar">
                                <?php if(count($amenities) > 0): ?>
                                    <?php foreach(array_slice($amenities, 0, 3) as $amenity): ?>
                                        <span><i class="fas <?php echo $amenity->icon_class; ?>"></i> <?php echo $amenity->amenity_name; ?></span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span><i class="fas fa-door-open"></i> <?php echo $available_rooms; ?> Rooms</span>
                                <?php endif; ?>
                            </div>

                            <!-- Card Footer Meta -->
                            <div class="card-footer-meta">
                                <span><i class="fas fa-user"></i> Verified Owner</span>
                                <a href="hostel-details.php?id=<?php echo $hostel->id; ?>" style="color:var(--theme-primary-color); font-weight:600; text-decoration:none;">
                                    View Details <i class="fas fa-arrow-right" style="font-size:0.75rem;"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="pub-no-results">
                            <i class="fas fa-search"></i>
                            <h4>No properties found</h4>
                            <p>Try adjusting your search filters or browse all available properties</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════════════════════
         PARALLAX STATISTICS — Fact Counters
         ══════════════════════════════════════════════════════════ -->
    <section class="parallax-stats" style="background-image:url('assets/images/hostel-img.jpg');">
        <div class="overlay-black"></div>
        <div class="container" style="position:relative; z-index:5;">
            <div class="row text-center">
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <div class="count-icon"><i class="fas fa-building"></i></div>
                    <span class="count-num" data-target="<?php echo $stat_hostels; ?>">0</span>
                    <span class="count-label">Properties</span>
                </div>
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <div class="count-icon"><i class="fas fa-door-open"></i></div>
                    <span class="count-num" data-target="<?php echo $stat_rooms; ?>">0</span>
                    <span class="count-label">Rooms Available</span>
                </div>
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <div class="count-icon"><i class="fas fa-map-marked-alt"></i></div>
                    <span class="count-num" data-target="<?php echo $stat_cities; ?>">0</span>
                    <span class="count-label">Locations</span>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="count-icon"><i class="fas fa-users"></i></div>
                    <span class="count-num" data-target="<?php echo $stat_tenants; ?>">0</span>
                    <span class="count-label">Happy Tenants</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ── Gallery Teaser ── -->
    <section class="gallery-teaser">
        <div class="container">
            <div class="teaser-icon"><i class="fas fa-images"></i></div>
            <h3>Explore Our Photo Gallery</h3>
            <p>Browse stunning photos from all our verified properties. Like your favorites and discover your next perfect home.</p>
            <a href="gallery.php" class="btn-gallery">
                <i class="fas fa-camera"></i> Browse Gallery <i class="fas fa-arrow-right" style="font-size:0.85rem;"></i>
            </a>
        </div>
    </section>

    <!-- ── Why Choose Us ── -->
    <section class="pub-section pub-section-alt">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Why Us</span>
                <h2>Why Choose HostelHub</h2>
                <p>We make finding your ideal accommodation simple, safe, and hassle-free</p>
            </div>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="pub-card text-center">
                        <div class="pub-card-icon mx-auto"><i class="fas fa-shield-alt"></i></div>
                        <h5>Verified Properties</h5>
                        <p>Every listing is verified by our team. Real photos, honest descriptions, no surprises on move-in day.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="pub-card text-center">
                        <div class="pub-card-icon mx-auto"><i class="fas fa-bolt"></i></div>
                        <h5>Instant Booking</h5>
                        <p>Book your room in minutes with our streamlined process. No lengthy paperwork or waiting.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="pub-card text-center">
                        <div class="pub-card-icon mx-auto"><i class="fas fa-star"></i></div>
                        <h5>Real Reviews</h5>
                        <p>Read genuine reviews from real tenants to make informed decisions about your next home.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── Footer ── -->
    <footer class="pub-footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="pub-footer-brand">
                        <img src="assets/images/big/icon.png" alt="HostelHub">
                        <span>HostelHub</span>
                    </div>
                    <p class="pub-footer-desc">
                        Your trusted platform for finding quality hostel accommodations. We connect tenants with verified property owners across Kenya.
                    </p>
                    <div class="pub-footer-social">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Quick Links</h5>
                    <ul class="pub-footer-links">
                        <li><a href="index.php">Browse Properties</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <li><a href="login.php">Login</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>For Property Owners</h5>
                    <ul class="pub-footer-links">
                        <li><a href="admin/index.php">Landlord Login</a></li>
                        <li><a href="client-registration.php">Register Account</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Legal</h5>
                    <ul class="pub-footer-links">
                        <li><a href="privacy.php">Privacy Policy</a></li>
                        <li><a href="contact.php">Support</a></li>
                    </ul>
                </div>
            </div>
            <div class="pub-footer-bottom">
                &copy; <?php echo date('Y'); ?> HostelHub. All rights reserved.
            </div>
        </div>
    </footer>

    <script src="assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const nav = document.getElementById('pubNav');
            if (window.scrollY > 50) {
                nav.classList.add('scrolled');
            } else {
                nav.classList.remove('scrolled');
            }
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        // ── jQuery Counter Animation (scroll-triggered) ──
        $(function() {
            var counted = false;
            $(window).scroll(function() {
                var statsSection = $('.parallax-stats');
                if (statsSection.length === 0) return;
                var oTop = statsSection.offset().top - window.innerHeight;
                if (!counted && $(window).scrollTop() > oTop) {
                    counted = true;
                    $('.count-num').each(function() {
                        var $this = $(this);
                        var target = parseInt($this.data('target')) || 0;
                        $({ countNum: 0 }).animate({ countNum: target }, {
                            duration: 2000,
                            easing: 'swing',
                            step: function() {
                                $this.text(Math.floor(this.countNum));
                            },
                            complete: function() {
                                $this.text(this.countNum);
                            }
                        });
                    });
                }
            });
        });
    </script>
</body>

</html>
