<?php
session_start();
include('../includes/dbconn.php');
include('../includes/check-login.php');
check_login();

$userId = $_SESSION['id'];

if(isset($_POST['submit_review'])){
    $hostel_id = $_POST['hostel_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];
    
    // Check if valid booking exists (optional strict check)
    // $check = "SELECT id FROM bookings WHERE client_id=? AND hostel_id=? AND booking_status='approved'";
    
    $query = "INSERT INTO reviews (hostel_id, user_id, rating, comment) VALUES (?, ?, ?, ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('iiis', $hostel_id, $userId, $rating, $comment);
    if($stmt->execute()){
        echo "<script>alert('Review submitted successfully!'); window.location.href='dashboard.php';</script>";
    } else {
        echo "<script>alert('Error submitting review.');</script>";
    }
}
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
    <meta charset="utf-8">
    <title>Write a Review - Hostel Management System</title>
    <link href="../dist/css/style.min.css" rel="stylesheet">`n    <link href="../assets/css/custom-gradients.css" rel="stylesheet">
</head>
<body>
    <!-- Simple wrapper for popup or standalone -->
    <div class="container mt-5">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Write a Review</h4>
                <form method="POST">
                    <div class="form-group">
                        <label>Select Hostel</label>
                        <select name="hostel_id" class="form-control" required>
                            <?php
                            // Get hostels this user has booked
                            // Assuming 'emailid' in bookings matches user email, or better yet if we normalized it to user_id.
                            // The system uses emailid in bookings table.
                            $uEmail = $_SESSION['login'];
                            $q = "SELECT DISTINCT h.id, h.name FROM bookings b JOIN hostels h ON b.hostel_id = h.id WHERE b.emailid = ? AND b.booking_status IN ('approved', 'checked-out')";
                            $stmt = $mysqli->prepare($q);
                            $stmt->bind_param('s', $uEmail);
                            $stmt->execute();
                            $res = $stmt->get_result();
                            while($row = $res->fetch_object()){
                                echo "<option value='{$row->id}'>{$row->name}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Rating</label>
                        <select name="rating" class="form-control">
                            <option value="5">5 - Excellent</option>
                            <option value="4">4 - Very Good</option>
                            <option value="3">3 - Good</option>
                            <option value="2">2 - Poor</option>
                            <option value="1">1 - Terrible</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Comment</label>
                        <textarea name="comment" class="form-control" rows="4" required placeholder="Describe your experience..."></textarea>
                    </div>
                    <button type="submit" name="submit_review" class="btn btn-primary">Submit Review</button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
