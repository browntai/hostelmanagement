<?php
/**
 * Hostel Helper Functions
 * Utility functions for hostel-related operations
 */

/**
 * Get complete hostel information by ID
 */
function getHostelById($mysqli, $hostel_id, $tenant_id = null) {
    // Join with users table to get landlord contact info
    // We look for a user with role='landlord' and the same tenant_id as the hostel
    $query = "SELECT h.*, u.contact_no as landlord_contact, u.email as landlord_email 
              FROM hostels h 
              LEFT JOIN users u ON h.tenant_id = u.tenant_id AND u.role = 'landlord'
              WHERE h.id = ?";
    $params = [$hostel_id];
    $types = 'i';
    
    // Add tenant isolation if tenant_id provided
    if ($tenant_id !== null) {
        $query .= " AND h.tenant_id = ?";
        $params[] = $tenant_id;
        $types .= 'i';
    }
    
    // Limit 1 to ensure we don't get duplicates if multiple landlords (though there should ideally be one per tenant)
    $query .= " LIMIT 1";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_object();
}

/**
 * Get all images for a hostel
 */
function getHostelImages($mysqli, $hostel_id) {
    $query = "SELECT * FROM hostel_images WHERE hostel_id = ? ORDER BY is_primary DESC, display_order ASC";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $hostel_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $images = [];
    while ($row = $result->fetch_object()) {
        $images[] = $row;
    }
    
    return $images;
}

/**
 * Get primary/featured image for a hostel
 */
function getHostelFeaturedImage($mysqli, $hostel_id) {
    $query = "SELECT image_path FROM hostel_images WHERE hostel_id = ? AND is_primary = 1 LIMIT 1";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $hostel_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_object()) {
        return $row->image_path;
    }
    
    // If no primary image, get first image
    $query = "SELECT image_path FROM hostel_images WHERE hostel_id = ? ORDER BY display_order ASC LIMIT 1";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $hostel_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_object()) {
        return $row->image_path;
    }
    
    // Return placeholder if no images
    return 'assets/images/hostel-placeholder.jpg';
}

/**
 * Get all amenities for a hostel
 */
function getHostelAmenities($mysqli, $hostel_id) {
    $query = "SELECT a.* FROM amenities a 
              INNER JOIN hostel_amenities ha ON a.id = ha.amenity_id 
              WHERE ha.hostel_id = ? 
              ORDER BY a.display_order ASC";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $hostel_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $amenities = [];
    while ($row = $result->fetch_object()) {
        $amenities[] = $row;
    }
    
    return $amenities;
}

/**
 * Get all room types and pricing for a hostel
 */
function getHostelTypes($mysqli, $hostel_id) {
    // Dynamically calculate available_count by counting rooms with status='available' per room type
    // rooms.status is kept in sync by the booking/cancellation system - it's the authoritative source
    $query = "SELECT ht.*, htm.price_per_month,
                (
                    SELECT COUNT(*) FROM rooms r
                    WHERE r.hostel_id = htm.hostel_id
                    AND r.room_type_id = htm.type_id
                    AND r.status = 'available'
                ) AS available_count
              FROM hostel_types ht 
              INNER JOIN hostel_type_mapping htm ON ht.id = htm.type_id 
              WHERE htm.hostel_id = ? 
              ORDER BY ht.display_order ASC";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $hostel_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $types = [];
    while ($row = $result->fetch_object()) {
        $types[] = $row;
    }
    
    return $types;
}

/**
 * Get lowest price for a hostel
 */
function getHostelLowestPrice($mysqli, $hostel_id) {
    $query = "SELECT MIN(price_per_month) as min_price 
              FROM hostel_type_mapping 
              WHERE hostel_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $hostel_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_object()) {
        return $row->min_price;
    }
    
    return 0;
}

/**
 * Count available rooms for a hostel
 * Returns number of rooms with status='available' (kept in sync by the booking system)
 */
function getAvailableRoomsCount($mysqli, $hostel_id) {
    $query = "SELECT COUNT(*) as count FROM rooms
              WHERE hostel_id = ?
              AND status = 'available'";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $hostel_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_object()) {
        return $row->count;
    }

    return 0;
}

/**
 * Search hostels with filters
 */
function searchHostels($mysqli, $filters = []) {
    $query = "SELECT DISTINCT h.* FROM hostels h WHERE h.status = 'approved'";
    $params = [];
    $types = '';
    
    // City filter
    if (!empty($filters['city'])) {
        $query .= " AND h.city LIKE ?";
        $params[] = '%' . $filters['city'] . '%';
        $types .= 's';
    }
    
    // Hostel type filter
    if (!empty($filters['types']) && is_array($filters['types'])) {
        $placeholders = implode(',', array_fill(0, count($filters['types']), '?'));
        $query .= " AND h.id IN (
            SELECT hostel_id FROM hostel_type_mapping WHERE type_id IN ($placeholders)
        )";
        foreach ($filters['types'] as $type_id) {
            $params[] = $type_id;
            $types .= 'i';
        }
    }
    
    // Amenities filter
    if (!empty($filters['amenities']) && is_array($filters['amenities'])) {
        $amenity_count = count($filters['amenities']);
        $placeholders = implode(',', array_fill(0, $amenity_count, '?'));
        $query .= " AND h.id IN (
            SELECT hostel_id FROM hostel_amenities 
            WHERE amenity_id IN ($placeholders)
            GROUP BY hostel_id
            HAVING COUNT(DISTINCT amenity_id) = ?
        )";
        foreach ($filters['amenities'] as $amenity_id) {
            $params[] = $amenity_id;
            $types .= 'i';
        }
        $params[] = $amenity_count;
        $types .= 'i';
    }
    
    // Price range filter
    if (!empty($filters['min_price']) || !empty($filters['max_price'])) {
        $price_query = " AND h.id IN (SELECT hostel_id FROM hostel_type_mapping WHERE 1=1";
        
        if (!empty($filters['min_price'])) {
            $price_query .= " AND price_per_month >= ?";
            $params[] = $filters['min_price'];
            $types .= 'd';
        }
        
        if (!empty($filters['max_price'])) {
            $price_query .= " AND price_per_month <= ?";
            $params[] = $filters['max_price'];
            $types .= 'd';
        }
        
        $price_query .= ")";
        $query .= $price_query;
    }
    
    $query .= " ORDER BY h.created_at DESC";
    
    // Pagination
    if (!empty($filters['limit'])) {
        $query .= " LIMIT ?";
        $params[] = $filters['limit'];
        $types .= 'i';
        
        if (!empty($filters['offset'])) {
            $query .= " OFFSET ?";
            $params[] = $filters['offset'];
            $types .= 'i';
        }
    }
    
    $stmt = $mysqli->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $hostels = [];
    while ($row = $result->fetch_object()) {
        $hostels[] = $row;
    }
    
    return $hostels;
}

/**
 * Get all available amenities
 */
function getAllAmenities($mysqli) {
    $query = "SELECT * FROM amenities ORDER BY display_order ASC";
    $result = $mysqli->query($query);
    
    $amenities = [];
    while ($row = $result->fetch_object()) {
        $amenities[] = $row;
    }
    
    return $amenities;
}

/**
 * Get all hostel types
 */
function getAllHostelTypes($mysqli) {
    $query = "SELECT * FROM hostel_types ORDER BY display_order ASC";
    $result = $mysqli->query($query);
    
    $types = [];
    while ($row = $result->fetch_object()) {
        $types[] = $row;
    }
    
    return $types;
}

/**
 * Save hostel images to database
 */
function saveHostelImages($mysqli, $hostel_id, $image_paths, $primary_index = 0) {
    foreach ($image_paths as $index => $image_path) {
        $is_primary = ($index == $primary_index) ? 1 : 0;
        $display_order = $index;
        
        $query = "INSERT INTO hostel_images (hostel_id, image_path, is_primary, display_order) 
                  VALUES (?, ?, ?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('isii', $hostel_id, $image_path, $is_primary, $display_order);
        $stmt->execute();
    }
}

/**
 * Update hostel featured image
 */
function updateFeaturedImage($mysqli, $hostel_id, $image_path) {
    $query = "UPDATE hostels SET featured_image = ? WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('si', $image_path, $hostel_id);
    return $stmt->execute();
}

/**
 * Check if a specific service is enabled for a hostel
 */
function isServiceEnabled($mysqli, $hostel_id, $service_key) {
    $query = "SELECT is_enabled FROM hostel_services WHERE hostel_id = ? AND service_key = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('is', $hostel_id, $service_key);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_object()) {
        return (bool)$row->is_enabled;
    }
    
    return false;
}
?>
