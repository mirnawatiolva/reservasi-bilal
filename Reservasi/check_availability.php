<?php
require_once 'database/koneksi.php';

header('Content-Type: application/json');

$date = $_GET['date'] ?? '';

if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo json_encode(['error' => 'Invalid date']);
    exit;
}

$bookedSlots = [];

$query = "SELECT TIME(schedule) as slot_time FROM reservasi WHERE status NOT IN ('Cancel', 'Refund Selesai') AND DATE(schedule) = ?";
$stmt = mysqli_prepare($conn, $query);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 's', $date);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $bookedSlots[] = $row['slot_time'];
    }
    mysqli_stmt_close($stmt);
}

echo json_encode([
    'date' => $date,
    'booked_slots' => $bookedSlots
]);
