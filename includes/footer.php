<!-- Custom Gradient Styles -->
<link href="../assets/css/custom-gradients.css" rel="stylesheet">
<footer class="footer text-center text-muted">
&copy; <?php echo date("Y"); ?> - Thomas brown - Developed by <a href="https://www.linkedin.com/in/tai-brown-4352003a0?utm_source=share&utm_campaign=share_via&utm_content=profile&utm_medium=android_app">ThomasBrown</a>
</footer>

<!-- Toastify CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">


<script>
    // Global stub for jvm to prevent dashboard1.min.js from crashing
    window.jvm = {
        Map: {
            maps: {}
        }
    };
    
    // Check for jQuery every 50ms and mock vectorMap when found
    (function mockVectorMap() {
        if (window.jQuery && window.jQuery.fn) {
            window.jQuery.fn.vectorMap = function() { return this; };
        } else {
            setTimeout(mockVectorMap, 50);
        }
    })();
</script>

<!-- Toastify JS -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<?php 
    if(file_exists(dirname(__FILE__).'/toast-helper.php')) {
        include_once('toast-helper.php'); 
        showAlerts();
    }
?>
<script type="text/javascript">
    (function loadPoller() {
        if (window.jQuery) {
            var script = document.createElement('script');
            // Check if we are in a subdirectory
            var path = window.location.pathname;
            var isSubDir = path.includes('/client/') || path.includes('/landlord/') || path.includes('/admin/');
            script.src = (isSubDir ? '../' : '') + 'assets/js/notification-poller.js';
            document.head.appendChild(script);
        } else {
            setTimeout(loadPoller, 100);
        }
    })();
</script>

<?php if(isset($_SESSION['login']) && isset($mysqli)): ?>
    <!-- Review Modal (Global) -->
    <div class="modal fade" id="reviewModal" tabindex="-1" role="dialog" aria-labelledby="reviewModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-white" id="reviewModalLabel"><i class="fas fa-star mr-2"></i>Write a Review</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="write-review.php" method="POST">
                    <div class="modal-body">
                        <div class="alert alert-info py-2" style="border-radius: 8px;">
                            <small><i class="fas fa-info-circle mr-1"></i> Please rate your stay. Your feedback helps us improve!</small>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-medium">Select Hostel</label>
                            <select name="hostel_id" class="form-control" required>
                                <?php
                                $uEmail = $_SESSION['login'];
                                // Fetch the most recent hostel name for pre-selection
                                $latestHostel = "";
                                $checkQ = "SELECT h.name FROM bookings b JOIN hostels h ON b.hostel_id = h.id WHERE b.emailid = ? ORDER BY b.postingDate DESC LIMIT 1";
                                $cStmt = $mysqli->prepare($checkQ);
                                $cStmt->bind_param('s', $uEmail);
                                $cStmt->execute();
                                $cRes = $cStmt->get_result();
                                if($crow = $cRes->fetch_object()){
                                    $latestHostel = $crow->name;
                                }

                                $q = "SELECT DISTINCT h.id, h.name FROM bookings b JOIN hostels h ON b.hostel_id = h.id WHERE b.emailid = ? AND b.booking_status IN ('approved', 'checked-out', 'confirmed')";
                                $stmt = $mysqli->prepare($q);
                                $stmt->bind_param('s', $uEmail);
                                $stmt->execute();
                                $res = $stmt->get_result();
                                if($res->num_rows > 0){
                                    while($row = $res->fetch_object()){
                                        $selected = ($latestHostel == $row->name) ? "selected" : "";
                                        echo "<option value='{$row->id}' $selected>{$row->name}</option>";
                                    }
                                } else {
                                    echo "<option value='' disabled>No bookings found</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-medium">Rating</label>
                            <div class="rating-select">
                                <select name="rating" class="form-control" required>
                                    <option value="5" selected>5 - Excellent</option>
                                    <option value="4">4 - Very Good</option>
                                    <option value="3">3 - Good</option>
                                    <option value="2">2 - Poor</option>
                                    <option value="1">1 - Terrible</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-medium">Comment</label>
                            <textarea name="comment" class="form-control" rows="4" required placeholder="Describe your experience..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">Cancel</button>
                        <button type="submit" name="submit_review" class="btn btn-warning px-4 text-white font-weight-bold">Submit Review</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <style>
        /* Ensure modal is above everything and not clipped */
        #reviewModal { z-index: 1050 !important; }
        .modal-backdrop { z-index: 1040 !important; }
        .modal-content { border-radius: 12px; overflow: hidden; }
        .bg-warning { background-color: #ff9800 !important; } /* Match screenshot's warm orange */
    </style>
<script type="text/javascript">
    (function loadAjaxNav() {
        if (window.jQuery) {
            var script = document.createElement('script');
            var path = window.location.pathname;
            var isSubDir = path.includes('/client/') || path.includes('/landlord/') || path.includes('/admin/');
            script.src = (isSubDir ? '../' : '') + 'assets/js/ajax-navigation.js';
            document.head.appendChild(script);
        } else {
            setTimeout(loadAjaxNav, 100);
        }
    })();
</script>
<?php endif; ?>
