<?php
include 'auth/check.php';
include 'config/db.php';
include 'navbar.php';

// Tambah booking
if (isset($_POST['add_booking'])) {
  $table_id = $_POST['table_id'];
  $customer_name = $_POST['customer_name'];
  $time_slot = new DateTime($_POST['time_slot']);
  $duration = (int) $_POST['duration'];
  $end_time = clone $time_slot;
  $end_time->modify("+{$duration} hours");

  // Cek bentrok booking
  $stmt = $conn->prepare("
    SELECT * FROM bookings 
    WHERE table_id = :table_id
      AND (
        (time_slot <= :end_time AND DATE_ADD(time_slot, INTERVAL duration HOUR) >= :time_slot)
      )
  ");
  $stmt->execute([
    ':table_id' => $table_id,
    ':time_slot' => $time_slot->format('Y-m-d H:i:s'),
    ':end_time' => $end_time->format('Y-m-d H:i:s')
  ]);
  $conflict = $stmt->fetch();

 if ($conflict) {
  $error = "❌ Booking gagal: Meja ini sudah dibooking pada jam tersebut!";
} else {
  // Buat waktu mulai & selesai lebih dulu
  $time_slot = new DateTime($_POST['time_slot']);
  $end_time = clone $time_slot;
  $end_time->modify("+{$duration} hours");

  // Lalu siapkan query
  $stmt = $conn->prepare("INSERT INTO bookings (table_id, customer_name, time_slot, duration) 
  VALUES (:table_id, :customer_name, :time_slot, :duration)");

  $stmt->bindParam(':table_id', $table_id);
  $stmt->bindParam(':customer_name', $customer_name);
  $stmt->bindParam(':time_slot', $time_slot->format('Y-m-d H:i:s')); // harus string
  $stmt->bindParam(':duration', $duration);
  $stmt->execute();

  $success = "✅ Booking berhasil disimpan!";
}

}

// Hapus booking
if (isset($_GET['delete_booking'])) {
  $stmt = $conn->prepare("DELETE FROM bookings WHERE id = :id");
  $stmt->bindParam(':id', $_GET['delete_booking']);
  $stmt->execute();
}

// Ambil data
$tables = $conn->query("SELECT * FROM tables")->fetchAll(PDO::FETCH_ASSOC);
$bookings = $conn->query("SELECT b.*, t.name as table_name FROM bookings b JOIN tables t ON b.table_id = t.id ORDER BY b.time_slot DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Booking</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
  <h2 class="mb-4">Manajemen Pemesanan</h2>

  <?php if (isset($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
  <?php elseif (isset($success)): ?>
    <div class="alert alert-success"><?= $success ?></div>
  <?php endif; ?>

  <!-- Form Booking -->
  <form method="POST" class="mb-4 row g-2 align-items-end">
    <div class="col-md-3">
      <label>Meja</label>
      <select name="table_id" class="form-select" required>
        <option value="">Pilih Meja</option>
        <?php foreach ($tables as $t): ?>
          <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <label>Nama Customer</label>
      <input name="customer_name" class="form-control" required>
    </div>
    <div class="col-md-3">
      <label>Jam Mulai</label>
      <input name="time_slot" type="datetime-local" class="form-control" required>
    </div>
    <div class="col-md-2">
      <label>Durasi (jam)</label>
      <input name="duration" type="number" min="1" class="form-control" required>
    </div>
    <div class="col-md-2">
      <button name="add_booking" class="btn btn-success w-100">Booking</button>
    </div>
  </form>

  <!-- Tabel Booking -->
  <h4>Daftar Booking</h4>
  <table class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>Customer</th>
        <th>Meja</th>
        <th>Mulai</th>
        <th>Durasi</th>
        <th>Selesai</th>
        <th>Status</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($bookings as $b): ?>
        <?php
          $now = new DateTime();
          $start = new DateTime($b['time_slot']);
          $end = clone $start;
          $end->modify("+{$b['duration']} hours");

          if ($now >= $start && $now <= $end) {
              $status = "Terisi";
              $rowClass = "table-success";
          } elseif ($now > $end) {
              $status = "Terlambat";
              $rowClass = "table-danger";
          } else {
              $status = "Menunggu";
              $rowClass = "";
          }
        ?>
        <tr class="<?= $rowClass ?>">
          <td><?= htmlspecialchars($b['customer_name']) ?></td>
          <td><?= htmlspecialchars($b['table_name']) ?></td>
          <td><?= $start->format('H:i') ?></td>
          <td><?= $b['duration'] ?> jam</td>
          <td><?= $end->format('H:i') ?></td>
          <td><span class="badge bg-<?= $status === 'Terisi' ? 'success' : ($status === 'Terlambat' ? 'danger' : 'secondary') ?>"><?= $status ?></span></td>
          <td>
            <a href="?delete_booking=<?= $b['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus booking ini?')">Hapus</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div class="text-muted mt-3">
    <strong>Keterangan:</strong> <span class="badge bg-success">Terisi</span> = sedang dipakai,
    <span class="badge bg-danger">Terlambat</span> = lewat waktu,
    <span class="badge bg-secondary">Menunggu</span> = belum mulai
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
