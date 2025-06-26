<?php
include 'auth/check.php';
include 'config/db.php';
include 'navbar.php';

// Tambah order
if (isset($_POST['add_order'])) {
  $stmt = $conn->prepare("INSERT INTO orders (booking_id, menu_id, quantity) VALUES (:booking_id, :menu_id, :quantity)");
  $stmt->execute([
    ':booking_id' => $_POST['booking_id'],
    ':menu_id' => $_POST['menu_id'],
    ':quantity' => $_POST['quantity']
  ]);
}

// Hapus order
if (isset($_GET['delete'])) {
  $stmt = $conn->prepare("DELETE FROM orders WHERE id = :id");
  $stmt->execute([':id' => $_GET['delete']]);
}

// Ambil data booking aktif
$bookings = $conn->query("
  SELECT b.id, t.name AS table_name, b.customer_name
  FROM bookings b 
  JOIN tables t ON b.table_id = t.id
  ORDER BY b.id DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Ambil daftar menu
$menus = $conn->query("SELECT * FROM menus ORDER BY category, name")->fetchAll(PDO::FETCH_ASSOC);

// Ambil semua orders
$orders = $conn->query("
  SELECT o.*, m.name AS menu_name, m.price, b.customer_name, t.name AS table_name
  FROM orders o
  JOIN menus m ON o.menu_id = m.id
  JOIN bookings b ON o.booking_id = b.id
  JOIN tables t ON b.table_id = t.id
  ORDER BY o.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Pemesanan Menu</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
  <h2 class="mb-4">Pemesanan Makanan & Minuman</h2>

  <!-- Form Tambah Order -->
  <form method="POST" class="row g-2 align-items-end mb-4">
    <div class="col-md-4">
      <label>Booking / Meja</label>
      <select name="booking_id" class="form-select" required>
        <option value="">Pilih Booking</option>
        <?php foreach ($bookings as $b): ?>
          <option value="<?= $b['id'] ?>">
            <?= htmlspecialchars($b['table_name']) ?> - <?= htmlspecialchars($b['customer_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4">
      <label>Menu</label>
      <select name="menu_id" class="form-select" required>
        <option value="">Pilih Menu</option>
        <?php foreach ($menus as $m): ?>
          <option value="<?= $m['id'] ?>">
            <?= htmlspecialchars($m['name']) ?> (Rp <?= number_format($m['price']) ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <label>Jumlah</label>
      <input type="number" name="quantity" class="form-control" value="1" min="1" required>
    </div>
    <div class="col-md-2">
      <button name="add_order" class="btn btn-primary w-100">Tambah</button>
    </div>
  </form>

  <!-- Daftar Pesanan -->
  <h4>Daftar Pesanan</h4>
  <table class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>Waktu</th>
        <th>Meja</th>
        <th>Customer</th>
        <th>Menu</th>
        <th>Jumlah</th>
        <th>Total</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php $grand_total = 0; ?>
      <?php foreach ($orders as $o): ?>
        <?php $total = $o['price'] * $o['quantity']; ?>
        <?php $grand_total += $total; ?>
        <tr>
          <td><?= date('H:i', strtotime($o['created_at'])) ?></td>
          <td><?= htmlspecialchars($o['table_name']) ?></td>
          <td><?= htmlspecialchars($o['customer_name']) ?></td>
          <td><?= htmlspecialchars($o['menu_name']) ?></td>
          <td><?= $o['quantity'] ?></td>
          <td>Rp <?= number_format($total) ?></td>
          <td>
            <a href="?delete=<?= $o['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus pesanan ini?')">Hapus</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr>
        <th colspan="5">Total Seluruh</th>
        <th colspan="2">Rp <?= number_format($grand_total) ?></th>
      </tr>
    </tfoot>
  </table>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
