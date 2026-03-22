<?php
    session_start();
    include('includes/dbconn.php');
    include('includes/hostel-helper.php');

    // Filter Logic
    $city = isset($_GET['city']) ? $mysqli->real_escape_string($_GET['city']) : '';
    $type = isset($_GET['type']) ? $mysqli->real_escape_string($_GET['type']) : '';
    $search = isset($_GET['search']) ? $mysqli->real_escape_string($_GET['search']) : '';

    $query = "SELECT h.*, 
              (SELECT MIN(price_per_month) FROM hostel_type_mapping WHERE hostel_id = h.id) as min_price,
              (SELECT image_path FROM hostel_images WHERE hostel_id = h.id LIMIT 1) as main_image
              FROM hostels h 
              WHERE h.status = 'approved'";

    if($city) $query .= " AND h.city = '$city'";
    if($search) $query .= " AND (h.name LIKE '%$search%' OR h.description LIKE '%$search%' OR h.address LIKE '%$search%')";
    
    // If type filter is active, we might need a join or subquery. 
    // For simplicity, let's just filter by type if it's provided.
    if($type) {
        $query .= " AND h.id IN (SELECT hostel_id FROM hostel_types WHERE type_name LIKE '%$type%')";
    }

    $query .= " ORDER BY h.created_at DESC";
    $result = $mysqli->query($query);
    
    $page_title = "Browse Hostels — HostelHub";
    include('includes/public-header.php');
?>

    <?php include('includes/public-nav.php'); ?>

    <!-- ── Hero ── -->
    <section class="pub-hero pub-hero-small" style="background: linear-gradient(135deg, rgba(13, 20, 50, 0.8) 0%, rgba(13, 20, 50, 0.4) 100%), url('assets/images/heroes/hostels_hero.png') center/cover no-repeat;">
        <div class="container">
            <div class="pub-hero-content">
                <h1>Find Your Next Home</h1>
                <p>Browse our curated collection of verified hostels across Kenya</p>
            </div>
        </div>
    </section>

    <!-- ── Search & Filter Bar ── -->
    <section class="pub-section" style="padding: 40px 0 20px;">
        <div class="container">
            <div class="pub-card" style="margin-top: -80px; position: relative; z-index: 10; padding: 30px;">
                <form action="hostels.php" method="GET" class="row align-items-end">
                    <div class="col-lg-4 col-md-6 mb-3 mb-lg-0">
                        <label class="form-label" style="font-weight:700; color:var(--pub-navy); font-size:0.85rem; margin-bottom:8px;">Search Properties</label>
                        <div class="input-group">
                            <span class="input-group-text" style="background:#fff; border-right:none; color:var(--pub-soft);"><i class="fas fa-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Hostel name, location..." value="<?php echo htmlentities($search); ?>" style="border-left:none;">
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                        <label class="form-label" style="font-weight:700; color:var(--pub-navy); font-size:0.85rem; margin-bottom:8px;">Location</label>
                        <select name="city" class="form-control">
                            <option value="">All Cities</option>
                            <?php 
                            $cities = $mysqli->query("SELECT DISTINCT city FROM hostels WHERE status='approved' ORDER BY city");
                            while($c = $cities->fetch_object()):
                            ?>
                            <option value="<?php echo $c->city; ?>" <?php if($city == $c->city) echo 'selected'; ?>><?php echo $c->city; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                        <label class="form-label" style="font-weight:700; color:var(--pub-navy); font-size:0.85rem; margin-bottom:8px;">Room Type</label>
                        <select name="type" class="form-control">
                            <option value="">Any Type</option>
                            <option value="Single" <?php if($type == 'Single') echo 'selected'; ?>>Single Room</option>
                            <option value="Double" <?php if($type == 'Double') echo 'selected'; ?>>Double Room</option>
                            <option value="Studio" <?php if($type == 'Studio') echo 'selected'; ?>>Studio / Bedsitter</option>
                            <option value="Apartment" <?php if($type == 'Apartment') echo 'selected'; ?>>Apartment</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <button type="submit" class="btn-pub-solid w-100" style="padding:11px;">Find Rooms</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- ── Hostels Grid ── -->
    <section class="pub-section">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 style="font-weight:800; color:var(--pub-navy); margin:0;">
                    <?php echo $result->num_rows; ?> Hostels Found
                </h4>
                <?php if($city || $search || $type): ?>
                    <a href="hostels.php" class="text-danger" style="font-size:0.85rem; font-weight:600;"><i class="fas fa-times"></i> Clear Filters</a>
                <?php endif; ?>
            </div>

            <div class="row">
                <?php if($result->num_rows > 0): ?>
                    <?php while($h = $result->fetch_object()): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="pub-card hostel-card p-0">
                                <div class="hostel-card-img">
                                    <img src="<?php echo $h->main_image ? $h->main_image : 'assets/images/hostel-img.jpg'; ?>" alt="<?php echo $h->name; ?>">
                                    <div class="quick-view-overlay">
                                        <button class="btn-quick-view" onclick="openQuickView(<?php echo $h->id; ?>)">
                                            <i class="fas fa-eye mr-1"></i> Quick View
                                        </button>
                                    </div>
                                    <div class="hostel-card-badge">Verified</div>
                                    <div class="hostel-card-price">From KSh <?php echo number_format($h->min_price, 0); ?></div>
                                </div>
                                <div class="hostel-card-body">
                                    <h5><?php echo $h->name; ?></h5>
                                    <p class="hostel-loc"><i class="fas fa-map-marker-alt"></i> <?php echo $h->city; ?>, <?php echo $h->address; ?></p>
                                    <div class="hostel-amenities">
                                        <?php 
                                            $h_amenities = getHostelAmenities($mysqli, $h->id);
                                            $count = 0;
                                            foreach($h_amenities as $a): 
                                                if($count++ >= 3) break;
                                        ?>
                                            <span><i class="fas <?php echo $a->icon_class; ?>"></i> <?php echo $a->amenity_name; ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                    <a href="hostel-details.php?id=<?php echo $h->id; ?>" class="btn-pub-outline w-100">View Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <div style="background:rgba(46,94,153,0.05); border-radius:100px; width:80px; height:80px; line-height:80px; margin:0 auto 20px; font-size:2rem; color:var(--pub-soft);">
                            <i class="fas fa-search-minus"></i>
                        </div>
                        <h5 style="font-weight:700; color:var(--pub-navy);">No Hostels Found</h5>
                        <p style="color:#8a9bb2;">Try adjusting your search filters or browse all properties.</p>
                        <a href="hostels.php" class="btn-pub-solid mt-3">Browse All Hostels</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

<?php include('includes/public-footer.php'); ?>

    <!-- Quick View Modal -->
    <div class="modal fade" id="quickViewModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="position:absolute; right:15px; top:10px; z-index:100; font-size:2rem; color:var(--pub-navy); cursor:pointer; background:none; border:none;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <div id="quickViewContent">
                        <!-- Content loaded via AJAX -->
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openQuickView(id) {
            $('#quickViewModal').modal('show');
            $('#quickViewContent').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');
            
            $.ajax({
                url: 'includes/quickview-ajax.php',
                type: 'GET',
                data: { id: id },
                success: function(response) {
                    $('#quickViewContent').html(response);
                    // Re-initialize carousel if needed
                    $('.carousel').carousel();
                },
                error: function() {
                    $('#quickViewContent').html('<div class="alert alert-danger m-3">Error loading details. Please try again.</div>');
                }
            });
        }
    </script>
