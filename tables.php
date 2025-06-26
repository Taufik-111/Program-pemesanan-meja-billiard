<?php
include 'auth/check.php';
include 'config/db.php';
include 'navbar.php';


// Ambil semua meja
$tables = $conn->query("SELECT * FROM tables")->fetchAll(PDO::FETCH_ASSOC);

// Cek waktu sekarang
$now = new DateTime();

// Pisahkan meja berdasarkan status booking saat ini
$available = [];
$unavailable = [];

foreach ($tables as $table) {
    // Cek apakah meja sedang dibooking sekarang
    $stmt = $conn->prepare("SELECT * FROM bookings 
        WHERE table_id = :id 
        AND time_slot <= :now 
        AND DATE_ADD(time_slot, INTERVAL duration HOUR) > :now");
    $stmt->execute([
        ':id' => $table['id'],
        ':now' => $now->format('Y-m-d H:i:s')
    ]);
    $isBooked = $stmt->fetch();

    if ($isBooked) {
        $unavailable[] = $table;
    } else {
        $available[] = $table;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Data Meja</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">
  <h2 class="mb-4">Data Meja</h2>

  <div class="row">
    <div class="col-md-6">
      <h5 class="text-success">🟢 Meja Tersedia</h5>
      <ul class="list-group">
        <?php foreach ($available as $t): ?>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <?= htmlspecialchars($t['name']) ?>
            <span class="badge bg-success">Tersedia</span>
          </li>
        <?php endforeach; ?>
        <?php if (count($available) === 0): ?>
          <li class="list-group-item text-muted">Tidak ada meja tersedia</li>
        <?php endif; ?>
      </ul>
    </div>

    <div class="col-md-6">
      <h5 class="text-danger">🔴 Meja Terpakai</h5>
      <ul class="list-group">
        <?php foreach ($unavailable as $t): ?>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <?= htmlspecialchars($t['name']) ?>
            <span class="badge bg-danger">Terpakai</span>
          </li>
        <?php endforeach; ?>
        <?php if (count($unavailable) === 0): ?>
          <li class="list-group-item text-muted">Semua meja tersedia</li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
