<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php">🎱 Billiard Admin</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navItems">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navItems">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="tables.php">Meja</a></li>
        <li class="nav-item"><a class="nav-link" href="bookings.php">Pemesanan</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
      <span class="navbar-text text-light">Login sebagai: <?= $_SESSION['admin'] ?></span>
    </div>
  </div>
</nav>
