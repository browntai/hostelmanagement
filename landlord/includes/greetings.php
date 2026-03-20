<?php
// Landlord Greetings
$hour = date('H');
if ($hour < 12) {
    $welcome_string = "Good Morning";
} elseif ($hour < 18) {
    $welcome_string = "Good Afternoon";
} else {
    $welcome_string = "Good Evening";
}

$aid = $_SESSION['id'];
$ret = "SELECT full_name from users where id=?";
$stmt = $mysqli->prepare($ret);
$stmt->bind_param('i', $aid);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_object()) {
    echo "<h3 class='page-title text-truncate text-dark font-weight-medium mb-1'>$welcome_string, $row->full_name! <span class='badge badge-info'>Landlord</span></h3>";
}
?>
