<?php
include('../include/config.php');
header('Content-Type: application/json');
session_start();


// Ambil dan escape input
$username = mysqli_real_escape_string($con, $_SESSION['username'] ?? '');
$id_ps = mysqli_real_escape_string($con, $_GET['id_ps'] ?? '');
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';


// Validasi id_ps harus numerik
if (!is_numeric($id_ps)) {
  echo json_encode([]);
  exit;
}

// Base query
$query = "SELECT * FROM tb_trans WHERE id_ps = '$id_ps' AND userx = '$username' AND (is_deleted IS NULL OR is_deleted != 1)";

// Filter tanggal jika ada input
if (!empty($start_date) && !empty($end_date)) {
  if (DateTime::createFromFormat('Y-m-d', $start_date) && DateTime::createFromFormat('Y-m-d', $end_date)) {
    $start_datetime = $start_date . ' 00:00:00';
    $end_datetime = $end_date . ' 23:59:59';
    $query .= " AND start >= '$start_datetime' AND start <= '$end_datetime'";
  }
}

// Urutkan berdasarkan ID ASCENDING
$query .= " ORDER BY id ASC";

// Eksekusi query
$result = mysqli_query($con, $query);

// Cek error query
if (!$result) {
  echo json_encode(['error' => mysqli_error($con), 'query' => $query]);
  exit;
}

$data = [];
$total_records = 0;

while ($row = mysqli_fetch_assoc($result)) {
  $total_records++;

  // Durasi aktual jika manual_stop tersedia
  $actual_duration = $row['durasi'];
  if ($row['manual_stop']) {
    $start_time = strtotime($row['start']);
    $stop_time = strtotime($row['manual_stop']);
    $actual_duration = round(($stop_time - $start_time) / 60); // dalam menit
  }

  $data[] = [
    'id' => $row['id'],
    'start' => date('d-m-Y H:i', strtotime($row['start'])),
    'usercreate' => $row['usercreate'],
    'end' => ($row['end'] == null) ? '-' : date('d-m-Y H:i', strtotime($row['end'])),
    'type' => $row['extra'] == 1 ? 'Extra' : 'Reguler',
    'duration' => $row['durasi'] . ' Min',
    'actual_duration' => $actual_duration . ' Min',
    'actual_stop' => ($row['manual_stop'] ?
      ($row['mode_stop'] == 'AUTO' ? '(A)' : '(M)') . ' ' . date('d-m-Y H:i', strtotime($row['manual_stop']))
      : '-'),
    'price' => 'Rp ' . number_format($row['harga'], 0, ',', '.'),
    'raw_price' => $row['harga'],
    'raw_duration' => $row['durasi'],
    'date_only' => date('d-m-Y', strtotime($row['start']))
  ];
}

// Kirimkan data sebagai JSON
echo json_encode([
  'data' => $data,
  'total_records' => $total_records,
  'date_range' => [
    'start' => $start_date,
    'end' => $end_date
  ]
]);
?>
