<?php include 'auth/check.php'; ?>
<?php include 'navbar.php'; ?>

<!DOCTYPE html>
<html>
<head>
  <title>Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
  <h2 class="mb-4">Dashboard</h2>
  <div class="alert alert-success">Selamat datang, <strong><?= $_SESSION['admin'] ?></strong>!</div>
  <p>Gunakan menu di atas untuk mengelola meja dan pemesanan.</p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
