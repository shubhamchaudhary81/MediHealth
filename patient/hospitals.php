// Fetch hospitals with search and pagination
$hospitals_query = "SELECT h.*, 
                   (SELECT COUNT(*) FROM doctor d WHERE d.hospitalid = h.id) as doctor_count
                   FROM hospital h 
                   WHERE h.status = 'approved' ";

if (!empty($search)) {
    $hospitals_query .= "AND (h.name LIKE ? OR h.city LIKE ? OR h.district LIKE ? OR h.zone LIKE ?) ";
}

$hospitals_query .= "ORDER BY h.name ASC LIMIT ? OFFSET ?"; 