<?php
/**
 * Profile Completion Reminder Modal
 * Include this file in dashboard pages (after jQuery/Bootstrap scripts are loaded via footer/body).
 * Requires: $mysqli and $_SESSION['id'] to be set.
 *
 * Usage:
 *   Client profile link:    profile.php    (relative to client/ folder)
 *   Landlord profile link:  profile.php    (relative to landlord/ folder)
 *   Caretaker profile link: profile.php    (relative to caretaker/ folder)
 *
 * Pass $profileLink before including this file, e.g.:
 *   $profileLink = 'profile.php';
 */

if (!isset($mysqli) || !isset($_SESSION['id'])) return;

$_uid = $_SESSION['id'];

// Fetch fields needed to determine completeness
$_pStmt = $mysqli->prepare("SELECT first_name, last_name, contact_no, gender FROM users WHERE id=? LIMIT 1");
$_pStmt->bind_param('i', $_uid);
$_pStmt->execute();
$_pRes = $_pStmt->get_result();
$_pRow = $_pRes->fetch_object();

if (!$_pRow) return; // No user found, skip

// Determine what's missing
$_missing = [];
if (empty(trim($_pRow->first_name ?? ''))) $_missing[] = 'First Name';
if (empty(trim($_pRow->last_name ?? '')))  $_missing[] = 'Last Name';
if (empty(trim($_pRow->contact_no ?? ''))) $_missing[] = 'Contact Number';
if (empty(trim($_pRow->gender ?? '')))     $_missing[] = 'Gender';

$_profileLink = isset($profileLink) ? $profileLink : 'profile.php';
$_missingCount = count($_missing);

if ($_missingCount === 0) return; // Profile is complete, no modal needed
?>

<!-- ======================================================= -->
<!-- Profile Completion Reminder Modal                        -->
<!-- ======================================================= -->
<div class="modal fade" id="profileReminderModal" tabindex="-1" role="dialog"
     aria-labelledby="profileReminderModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 480px;">
        <div class="modal-content border-0 shadow-lg overflow-hidden" style="border-radius: 16px;">

            <!-- Accent header bar -->
            <div style="background: linear-gradient(135deg, #129d6b 0%, #17c788 100%); padding: 28px 28px 20px; text-align: center;">
                <div style="width: 64px; height: 64px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 14px;">
                    <i data-feather="user" style="color:#fff; width:30px; height:30px;"></i>
                </div>
                <h5 id="profileReminderModalLabel" style="color:#fff; font-weight:800; margin:0; font-size:1.15rem; letter-spacing:-0.2px;">
                    Complete Your Profile
                </h5>
                <p style="color:rgba(255,255,255,0.82); font-size:0.82rem; margin:6px 0 0;">
                    <?php echo $_missingCount === 1 ? '1 field is' : $_missingCount . ' fields are'; ?> missing from your account
                </p>
            </div>

            <!-- Body -->
            <div class="modal-body px-4 py-4">
                <p class="text-muted mb-3" style="font-size:0.88rem;">
                    Your profile is incomplete. Filling in your details helps us provide you with a better experience and is required for bookings and communications.
                </p>

                <!-- Missing fields list -->
                <ul class="list-unstyled mb-4">
                    <?php foreach ($_missing as $_field): ?>
                    <li class="d-flex align-items-center mb-2">
                        <span style="width:22px;height:22px;background:#fff3cd;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-right:10px;flex-shrink:0;">
                            <i data-feather="alert-circle" style="width:13px;height:13px;color:#e6a817;"></i>
                        </span>
                        <span style="font-size:0.88rem;font-weight:600;color:#444;"><?php echo htmlspecialchars($_field); ?></span>
                        <span class="ml-auto badge" style="background:#fff3cd;color:#85620b;font-weight:700;font-size:0.7rem;padding:3px 8px;border-radius:20px;">Missing</span>
                    </li>
                    <?php endforeach; ?>
                </ul>

                <!-- Action buttons -->
                <div class="d-flex flex-column" style="gap:10px;">
                    <a href="<?php echo htmlspecialchars($_profileLink); ?>"
                       class="btn btn-block font-weight-bold"
                       style="background:linear-gradient(135deg,#129d6b,#17c788);color:#fff;border:none;border-radius:10px;padding:12px;font-size:0.92rem;letter-spacing:0.2px;">
                        <i data-feather="edit-2" style="width:15px;height:15px;margin-right:6px;"></i>
                        Update My Profile Now
                    </a>
                    <button type="button" class="btn btn-block btn-light font-weight-medium"
                            data-dismiss="modal"
                            style="border-radius:10px;padding:11px;font-size:0.88rem;color:#777;border:1px solid #e9ecef;">
                        Remind Me Later
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    // Show the modal after a short delay for better UX
    setTimeout(function () {
        $('#profileReminderModal').modal('show');
        // Re-render feather icons inside the modal
        if (typeof feather !== 'undefined') feather.replace();
    }, 800);
});
</script>
