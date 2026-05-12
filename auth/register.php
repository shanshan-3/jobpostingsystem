<?php session_start();
include "../config/database.php";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  $email    = trim($_POST["email"]);
  $password = $_POST["password"];
  $role     = $_POST["role"];

  $allowed_roles = ['seeker', 'employer'];

  if (empty($email) || empty($password) || empty($role)) {
    $error = "All fields are required.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Invalid email format.";
  } elseif (strlen($password) < 6) {
    $error = "Password must be at least 6 characters.";
  } elseif (!in_array($role, $allowed_roles)) {
    $error = "Invalid role selected.";
  } else {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
      $error = "Email is already registered.";
    } else {
      $hashed = password_hash($password, PASSWORD_DEFAULT);

      $insert = $pdo->prepare(
        "INSERT INTO users (email, password, role) VALUES (?, ?, ?)"
      );
      $insert->execute([$email, $hashed, $role]);

      $_SESSION['user_id'] = $pdo->lastInsertId();
      $_SESSION['role'] = $role;
      $_SESSION['email'] = $email;

      if ($role == 'seeker') {
        header("Location: ../jobseeker/profile.php?setup=1");
      } else {
        header("Location: ../employer/profile.php?setup=1");
      }
      exit();
    }
  }
}
include "../includes/header.php";
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <nav class="container">
    <a class="navbar-brand fw-bold" href="../index.php">
      work<span class="text-warning">without</span>limits
    </a>
  </nav>
</nav>

<div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center py-5">
  <div class="col-11 col-sm-8 col-md-6 col-lg-5">
    <div class="card border-0 shadow">
      <div class="card-body p-5">

        <h3 class="text-center fw-bold mb-1">Create Account</h3>
        <p class="text-center text-muted mb-4">Join work<span class="text-warning">without</span>limits today</p>

        <form action="register.php" method="POST">
          <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>
          <div class="mb-3">
            <div class="input-group">
              <span class="input-group-text">
                <i class="bi bi-envelope-open"></i>
              </span>
              <input type="email" name="email" class="form-control"
                placeholder="Email Address" autocomplete="off" required>
            </div>
          </div>

          <div class="mb-3">
            <div class="input-group">
              <span class="input-group-text">
                <i class="bi bi-key"></i>
              </span>
              <input type="password" name="password" class="form-control"
                placeholder="Password" autocomplete="off" required>
            </div>
          </div>

          <div class="mb-4">
            <select name="role" class="form-select" required>
              <option value="" disabled selected>Select Role</option>
              <option value="seeker" <?= (isset($role) && $role == 'seeker')   ? 'selected' : '' ?>>Seeker</option>
              <option value="employer" <?= (isset($role) && $role == 'employer') ? 'selected' : '' ?>>Employer</option>
            </select>
          </div>

          <div class="d-grid">
            <button type="submit" class="btn btn-warning text-dark fw-bold">Sign Up</button>
          </div>

        </form>

        <p class="text-center text-muted mt-3 mb-0">
          Already have an account? <a href="login.php" class="text-warning">Log in</a>
        </p>

      </div>
    </div>
  </div>
</div>