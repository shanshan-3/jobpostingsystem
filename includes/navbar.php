<?php
$role = $_SESSION['role'] ?? 'guest';
$name = $_SESSION['user']['name'] ?? '';
$current = basename($_SERVER['PHP_SELF']);

$profile = null;
if ($role === 'seeker' && isset($_SESSION['user_id'])) {
  if (!isset($pdo)) require_once __DIR__ . '/../config/database.php';
  $stmt = $pdo->prepare("SELECT full_name FROM seeker_profiles WHERE user_id = ?");
  $stmt->execute([$_SESSION['user_id']]);
  $profile = $stmt->fetch(PDO::FETCH_ASSOC);

} elseif ($role === 'employer' && isset($_SESSION['user_id'])) {
  if (!isset($pdo)) require_once __DIR__ . '/../config/database.php';
  $stmt = $pdo->prepare("SELECT company_name FROM employer_profiles WHERE user_id = ?");
  $stmt->execute([$_SESSION['user_id']]);
  $profile = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($role == 'seeker') {
  $brandLink = '../jobseeker/dashboard.php';
} elseif ($role == 'employer') {
  $brandLink = '../employer/dashboard.php';
} else {
  $brandLink = '../index.php';
}

?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark nav-underline">
  <div class="container">

    <a class="navbar-brand fw-bold" href="<?= $brandLink ?>">
      work<span class="text-warning font-weight-bold">without</span>limits
    </a>

    <?php if ($role !== 'guest'): ?>
      <button class="navbar-toggler" type="button"
        data-bs-toggle="collapse"
        data-bs-target="#mainNav"
        aria-controls="mainNav"
        aria-expanded="false"
        aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
    <?php endif; ?>

    <div class="collapse navbar-collapse" id="mainNav">

      <?php if ($role === 'guest'): ?>

        <ul class="navbar-nav ms-auto align-items-center gap-2">
          <li class="nav-item">
            <a class="btn btn-light btn-sm" href="auth/login.php">Login</a>
          </li>
          <li class="nav-item">
            <a class="btn btn-warning btn-sm bi bi-person text-dark fw-semibold" href="auth/register.php"> Sign up</a>
          </li>
        </ul>

      <?php elseif ($role === 'seeker'): ?>

        <ul class="navbar-nav me-auto align-items-center">
          <li class="nav-item">
            <a class="nav-link <?= $current === 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">Dashboard</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $current === 'jobs.php' ? 'active' : '' ?>" href="jobs.php">Browse Jobs</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $current === 'applications.php' ? 'active' : '' ?>" href="applications.php">My Applications</a>
          </li>
        </ul>
        <ul class="navbar-nav ms-auto align-items-center">
          <li class="nav-item">
            <a class="nav-link <?= $current === 'profile.php' ? 'active' : '' ?>" href="profile.php">
              <i class="bi bi-person-circle"></i> <?= htmlspecialchars($profile['full_name'] ?? '') ?>
            </a>
          </li>
          <li class="nav-item">
            <a class="btn btn-outline-danger btn-sm" href="../auth/logout.php">Logout</a>
          </li>
        </ul>

      <?php elseif ($role === 'employer'): ?>

        <ul class="navbar-nav me-auto align-items-center gap-2">
          <li class="nav-item">
            <a class="nav-link <?= $current === 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">Dashboard</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $current === 'post-job.php' ? 'active' : '' ?>" href="post-job.php">Post a Job</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $current === 'jobs.php' ? 'active' : '' ?>" href="jobs.php">My Jobs</a>
          </li>
        </ul>
        <ul class="navbar-nav ms-auto align-items-center gap-2">
          <li class="nav-item">
            <a class="nav-link <?= $current === 'profile.php' ? 'active' : '' ?>" href="profile.php">
              <i class="bi bi-person-circle"></i> <?= htmlspecialchars($profile['company_name'] ?? '') ?>
            </a>
          </li>
          <li class="nav-item">
            <a class="btn btn-outline-danger btn-sm" href="../auth/logout.php">Logout</a>
          </li>
        </ul>

      <?php endif; ?>

    </div>
  </div>
</nav>