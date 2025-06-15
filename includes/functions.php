<?php
function generate_confirmation_code($conn) {
    do {
        $prefix = substr(str_shuffle("ABCDEFGHJKLMNPQRSTUVWXYZ"), 0, 3);
        $suffix = substr(str_shuffle("0123456789"), 0, 3);
        $code = $prefix . '-' . $suffix;
        
        // Check if code exists in database - use booking_id instead of id
        $stmt = $conn->prepare("SELECT booking_id FROM bookings WHERE confirmation_code = ?");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $result = $stmt->get_result();
    } while ($result->num_rows > 0); // Keep generating until we get a unique code
    
    return $code;
}