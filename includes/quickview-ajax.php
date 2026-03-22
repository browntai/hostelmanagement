<?php
    session_start();
    include('dbconn.php');
    include('hostel-helper.php');

    if(!isset($_GET['id'])){
        echo "Invalid Request";
        exit;
    }

    $hostel_id = intval($_GET['id']);
    $hostel = getHostelById($mysqli, $hostel_id);
    
    if(!$hostel){
        echo "Hostel not found";
        exit;
    }

    $images = getHostelImages($mysqli, $hostel_id);
    $amenities = getHostelAmenities($mysqli, $hostel_id);
    $types = getHostelTypes($mysqli, $hostel_id);
?>

<div class="qv-container">
    <div class="row">
        <div class="col-md-6">
            <div id="qvCarousel" class="carousel slide" data-ride="carousel">
                <div class="carousel-inner">
                    <?php foreach($images as $index => $img): ?>
                        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                            <img src="<?php echo $img->image_path; ?>" class="d-block w-100" style="height:350px; object-fit:cover; border-radius:12px;" alt="...">
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if(count($images) > 1): ?>
                <a class="carousel-control-prev" href="#qvCarousel" role="button" data-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                </a>
                <a class="carousel-control-next" href="#qvCarousel" role="button" data-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="qv-details px-3">
                <h3 style="font-weight:800; color:var(--pub-navy);"><?php echo $hostel->name; ?></h3>
                <p class="text-muted mb-3"><i class="fas fa-map-marker-alt text-primary"></i> <?php echo $hostel->city; ?>, <?php echo $hostel->address; ?></p>
                
                <div class="qv-description mb-4" style="font-size:0.95rem; line-height:1.6; color:#4a5568;">
                    <?php echo mb_strimwidth($hostel->description, 0, 200, "..."); ?>
                </div>

                <div class="mb-4">
                    <h6 style="font-weight:700; color:var(--pub-navy);">Top Amenities</h6>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <?php foreach(array_slice($amenities, 0, 4) as $a): ?>
                            <span class="badge badge-light p-2" style="border:1px solid #e2e8f0; border-radius:8px;">
                                <i class="fas <?php echo $a->icon_class; ?> mr-1 text-primary"></i> <?php echo $a->amenity_name; ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 style="font-weight:700; color:var(--pub-navy);">Starting Price</h6>
                    <div style="font-size:1.5rem; font-weight:800; color:var(--pub-blue);">
                        KSh <?php echo number_format(getHostelLowestPrice($mysqli, $hostel_id), 0); ?> <span style="font-size:0.9rem; color:#8a9bb2; font-weight:400;">/ month</span>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <a href="hostel-details.php?id=<?php echo $hostel_id; ?>" class="btn btn-primary flex-grow-1" style="border-radius:10px; padding:12px; font-weight:700;">Full Details</a>
                    <button id="qvWishlistBtn" class="btn btn-outline-primary" style="border-radius:10px; padding:12px;"><i class="far fa-star"></i></button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if(isset($_SESSION['login'])): ?>
<script>
    var hostelId = <?php echo $hostel_id; ?>;
    var qvBtn = $('#qvWishlistBtn');

    // Initial check
    $.get('includes/check-wishlist.php?hostel_id=' + hostelId, function(response) {
        try {
            var res = typeof response === 'object' ? response : JSON.parse(response);
            if(res.exists) {
                qvBtn.html('<i class="fas fa-star" style="color:#f59e0b;"></i>');
                qvBtn.css({'background':'rgba(245,158,11,0.1)', 'border-color':'#f59e0b'});
            }
        } catch(e) {}
    });

    // Toggle handler
    qvBtn.off('click').on('click', function(e) {
        e.preventDefault();
        qvBtn.prop('disabled', true).css('opacity', '0.7');

        $.ajax({
            url: 'includes/toggle-wishlist.php',
            type: 'POST',
            data: { hostel_id: hostelId },
            success: function(response) {
                try {
                    var res = typeof response === 'object' ? response : JSON.parse(response);
                    if(res.status === 'added') {
                        qvBtn.html('<i class="fas fa-star" style="color:#f59e0b;"></i>');
                        qvBtn.css({'background':'rgba(245,158,11,0.1)', 'border-color':'#f59e0b'});
                    } else if (res.status === 'removed') {
                        qvBtn.html('<i class="far fa-star"></i>');
                        qvBtn.css({'background':'transparent', 'border-color':'var(--pub-blue)'});
                    } else {
                        alert('Error: ' + (res.message || 'Unknown error'));
                    }
                } catch(e) {
                    alert('Server Error');
                }
            },
            error: function() { alert('Connection Error'); },
            complete: function() {
                qvBtn.prop('disabled', false).css('opacity', '1');
            }
        });
    });
</script>
<?php else: ?>
<script>
    $('#qvWishlistBtn').on('click', function(e) {
        e.preventDefault();
        alert('Please login to add to wishlist');
        window.location.href = 'login.php?hostel_id=<?php echo $hostel_id; ?>';
    });
</script>
<?php endif; ?>
