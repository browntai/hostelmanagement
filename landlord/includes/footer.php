<!-- Custom Gradient Styles -->
<link href="../assets/css/custom-gradients.css" rel="stylesheet">
<footer class="footer text-center text-muted">
&copy; <?php echo date("Y"); ?> - Thomas brown - Developed by <a href="https://www.linkedin.com/in/tai-brown-4352003a0?utm_source=share&utm_campaign=share_via&utm_content=profile&utm_medium=android_app">ThomasBrown</a>
</footer>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<?php showAlerts(); ?>

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

<!-- Broadcast Notification Modal -->
<?php if(isset($_SESSION['role']) && ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'landlord')): ?>
<div class="modal fade" id="broadcastModal" tabindex="-1" role="dialog" aria-labelledby="broadcastModalLabel" aria-hidden="true" style="z-index: 9999;">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title m-0 text-white font-weight-bold" id="broadcastModalLabel"><i class="fas fa-paper-plane mr-2"></i>Send Quick Notification</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-dark small text-uppercase">Target Audience</label>
                        <select name="target" class="form-control custom-select bg-light border-0 shadow-none" style="height: 45px; border-radius: 8px;" onchange="toggleIndividualSelect(this.value)">
                            <option value="all_clients"><?php echo ($_SESSION['role'] == 'admin') ? 'All Clients (Global)' : 'All My Clients'; ?></option>
                            <?php if($_SESSION['role'] == 'admin'): ?>
                            <option value="all_landlords">All Landlords</option>
                            <option value="global">Global (Everyone)</option>
                            <?php endif; ?>
                            <option value="individual">Specific User</option>
                        </select>
                    </div>

                    <div class="form-group mb-4" id="modalUserSelectGroup" style="display:none;">
                        <label class="font-weight-bold text-dark small text-uppercase">Search User</label>
                        <select name="receiver_id" class="form-control custom-select bg-light border-0 shadow-none" style="height: 45px; border-radius: 8px;">
                            <?php
                            include_once '../includes/tenant_manager.php';
                            include_once '../includes/dbconn.php';
                            $tm = new TenantManager($mysqli);
                            $tid = $tm->getCurrentTenantId();
                            if($_SESSION['role'] == 'admin') {
                                $uRes = $mysqli->query("SELECT id, full_name, role FROM users WHERE id != {$_SESSION['id']} ORDER BY full_name ASC");
                            } elseif (!empty($tid)) {
                                $uRes = $mysqli->query("SELECT id, full_name FROM users WHERE tenant_id = $tid AND role = 'client' ORDER BY full_name ASC");
                            } else {
                                $uRes = $mysqli->query("SELECT id, full_name FROM users WHERE 1=0");
                            }
                            
                            if ($uRes) {
                                while($uRow = $uRes->fetch_assoc()) {
                                    $roleTag = isset($uRow['role']) ? " (".ucfirst($uRow['role']).")" : "";
                                    echo "<option value='{$uRow['id']}'>".htmlentities($uRow['full_name']).$roleTag."</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-dark small text-uppercase">Subject</label>
                        <input type="text" name="title" class="form-control bg-light border-0 shadow-none" style="height: 45px; border-radius: 8px;" placeholder="e.g. System Update" required>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-dark small text-uppercase">Message Body</label>
                        <textarea name="message" class="form-control bg-light border-0 shadow-none" style="border-radius: 8px;" rows="4" placeholder="Type your message here..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3">
                    <button type="button" class="btn btn-link text-muted font-weight-medium" data-dismiss="modal">Discard</button>
                    <button type="submit" name="send_broadcast" class="btn btn-primary px-5 font-weight-bold shadow-sm" style="border-radius: 8px; height: 45px;">Send Broadcast</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
function toggleIndividualSelect(val) {
    var el = document.getElementById('modalUserSelectGroup');
    if(el) el.style.display = (val === 'individual') ? 'block' : 'none';
}
</script>
<script type="text/javascript">
    (function loadPoller() {
        if (window.jQuery) {
            var script = document.createElement('script');
            var path = window.location.pathname;
            var isSubDir = path.includes('/client/') || path.includes('/landlord/') || path.includes('/admin/');
            script.src = (isSubDir ? '../' : '') + 'assets/js/notification-poller.js';
            document.head.appendChild(script);
        } else {
            setTimeout(loadPoller, 100);
        }
    })();
</script>
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
<!-- Profile Completion Reminder Modal -->
<?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'landlord' && (!isset($_SESSION['is_profile_complete']) || !$_SESSION['is_profile_complete'])): ?>
<div class="modal fade" id="profileReminderModal" tabindex="-1" role="dialog" aria-labelledby="profileReminderModalLabel" aria-hidden="true" data-backdrop="static" style="z-index: 10000;">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-body p-5 text-center">
                <div class="mb-4">
                    <div class="display-4 text-warning mb-3">🚀</div>
                    <h3 class="font-weight-bold text-dark">Complete Your Profile!</h3>
                    <p class="text-muted">Hi <strong><?php echo $_SESSION['full_name'] ?? 'Landlord'; ?></strong>, you're almost there! To build trust with potential tenants, please finish setting up your profile with a <strong>profile photo</strong> and <strong>contact information</strong>.</p>
                </div>
                <div class="d-flex flex-column gap-2">
                    <a href="profile.php" class="btn btn-primary btn-lg font-weight-bold shadow-sm mb-3" style="border-radius: 12px;">
                        <i class="fas fa-user-edit mr-2"></i> Update Profile Now
                    </a>
                    <button type="button" class="btn btn-link text-muted font-weight-medium" data-dismiss="modal">
                        Remind Me in 10 Minutes
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var jqCheck = setInterval(function() {
            if (window.jQuery) {
                clearInterval(jqCheck);
                
                const REMINDER_INTERVAL = 10 * 60 * 1000; // 10 minutes in ms
                const MODAL_ID = '#profileReminderModal';
                
                function checkProfileReminder() {
                    const userId = '<?php echo $_SESSION['id']; ?>';
                    const storageKey = 'profile_reminder_time_' + userId;
                    const lastShown = localStorage.getItem(storageKey);
                    const now = Date.now();
                    
                    if (!lastShown || (now - lastShown >= REMINDER_INTERVAL)) {
                        $(MODAL_ID).modal('show');
                        localStorage.setItem(storageKey, now);
                    }
                }

                // Delay initial check slightly to allow page load
                setTimeout(checkProfileReminder, 2000);
                
                // Re-check every 30 seconds
                setInterval(checkProfileReminder, 30000);
            }
        }, 100);
    });
</script>
<?php endif; ?>
<?php endif; ?>
