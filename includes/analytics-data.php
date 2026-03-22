<?php
// Function to get monthly revenue for the last 6 months
function getMonthlyRevenue($mysqli, $tenantId = null) {
    $data = array();
    for ($i = 5; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-$i months"));
        $monthName = date('M', strtotime("-$i months"));
        
        $sql = "SELECT SUM(amount) as total FROM payments WHERE status='verified' AND DATE_FORMAT(created_at, '%Y-%m') = ?";
        if ($tenantId !== null) {
            $sql .= " AND tenant_id = ?";
        }
        
        $stmt = $mysqli->prepare($sql);
        if ($tenantId !== null) {
            $stmt->bind_param('si', $month, $tenantId);
        } else {
            $stmt->bind_param('s', $month);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $data[] = array(
            'month' => $monthName,
            'total' => $row['total'] ? (float)$row['total'] : 0
        );
    }
    return $data;
}

// Function to get occupancy statistics
function getOccupancyStats($mysqli, $tenantId = null) {
    $stats = array();
    
    // Total Rooms
    $sqlTotal = "SELECT COUNT(*) as total FROM rooms";
    if ($tenantId !== null) {
        $sqlTotal .= " WHERE tenant_id = $tenantId";
    }
    $resTotal = $mysqli->query($sqlTotal);
    $rowTotal = $resTotal->fetch_assoc();
    $totalRooms = $rowTotal['total'];
    
    // Booked Rooms (Occupied)
    $sqlBooked = "SELECT COUNT(DISTINCT id) as occupied FROM rooms WHERE (status = 'booked' OR (room_no, hostel_id) IN (SELECT roomno, hostel_id FROM bookings WHERE booking_status IN ('approved', 'pending')))";
    if ($tenantId !== null) {
        $sqlBooked .= " AND tenant_id = $tenantId";
    }
    $resBooked = $mysqli->query($sqlBooked);
    $rowBooked = $resBooked->fetch_assoc();
    $occupied = $rowBooked['occupied'];
    
    $stats['total'] = $totalRooms;
    $stats['occupied'] = $occupied;
    $stats['available'] = max(0, $totalRooms - $occupied);
    
    return $stats;
}

// Function to get user growth for Admin
function getUserGrowth($mysqli) {
    $data = array();
    for ($i = 5; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-$i months"));
        $monthName = date('M', strtotime("-$i months"));
        
        $sql = "SELECT COUNT(*) as count FROM users WHERE DATE_FORMAT(created_at, '%Y-%m') = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('s', $month);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        $data[] = array(
            'month' => $monthName,
            'count' => $row['count']
        );
    }
    return $data;
}

/**
 * Get daycare statistics for a landlord
 */
function getDaycareStats($mysqli, $landlord_id) {
    $stats = array();
    
    // Total pending requests
    $qPending = "SELECT COUNT(*) as cnt FROM daycare_bookings db 
                 JOIN hostels h ON db.hostel_id = h.id 
                 WHERE h.tenant_id = ? AND db.status = 'pending'";
    $stmt = $mysqli->prepare($qPending);
    $stmt->bind_param('i', $landlord_id);
    $stmt->execute();
    $stats['pending'] = $stmt->get_result()->fetch_object()->cnt;
    
    // Today's check-ins
    $today = date('Y-m-d');
    $qToday = "SELECT COUNT(*) as cnt FROM daycare_bookings db 
                JOIN hostels h ON db.hostel_id = h.id 
                WHERE h.tenant_id = ? AND db.booking_date = ? AND db.status = 'checked_in'";
    $stmt = $mysqli->prepare($qToday);
    $stmt->bind_param('is', $landlord_id, $today);
    $stmt->execute();
    $stats['today_checked_in'] = $stmt->get_result()->fetch_object()->cnt;
    
    // Total registered children
    $qChildren = "SELECT COUNT(DISTINCT c.id) as cnt FROM children c 
                  JOIN users u ON c.parent_id = u.id 
                  JOIN bookings b ON u.email = b.emailid 
                  JOIN hostels h ON b.hostel_id = h.id 
                  WHERE h.tenant_id = ?";
    $stmt = $mysqli->prepare($qChildren);
    $stmt->bind_param('i', $landlord_id);
    $stmt->execute();
    $stats['total_children'] = $stmt->get_result()->fetch_object()->cnt;
    
    return $stats;
}

/**
 * Get daycare overview for a client
 */
function getClientDaycareOverview($mysqli, $client_id) {
    $overview = array();
    
    // Last booking status
    $qLast = "SELECT status, booking_date, time_slot FROM daycare_bookings 
              WHERE client_id = ? ORDER BY created_at DESC LIMIT 1";
    $stmt = $mysqli->prepare($qLast);
    $stmt->bind_param('i', $client_id);
    $stmt->execute();
    $overview['last_booking'] = $stmt->get_result()->fetch_object();
    
    // Registered children count
    $qChildren = "SELECT COUNT(*) as cnt FROM children WHERE parent_id = ?";
    $stmt = $mysqli->prepare($qChildren);
    $stmt->bind_param('i', $client_id);
    $stmt->execute();
    $overview['children_count'] = $stmt->get_result()->fetch_object()->cnt;
    
    return $overview;
}
?>
