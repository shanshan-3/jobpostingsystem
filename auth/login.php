<?php session_start();
require_once '../config/database.php';

$error ="";

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
  $email = trim($_POST['email']);
  $password = $_POST['password'];

  if(empty($email) || empty($password)){
    $error = "All fields are required.";
  } else {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password'])) {
      $error = "Invalid email or password.";
    }else {
      $_SESSION['user_id'] = $user['user_id'];
      $_SESSION['role']    = $user['role'];
      $_SESSION['email']   = $user['email'];
      
      if($user['role'] == 'employer'){

        $check = $pdo->prepare("SELECT profile_id FROM employer_profiles WHERE user_id = ?");
        $check->execute([$user['user_id']]);
          
      if(!$check->fetch()){
          header("Location: ../employer/profile.php?setup=1");
          exit();
        }else {
          header("Location: ../employer/dashboard.php");
          exit();
        }
      }else {
        $check = $pdo->prepare("SELECT profile_id FROM seeker_profiles WHERE user_id = ?");
        $check->execute([$user['user_id']]);

        if(!$check->fetch()){
          header("Location: ../jobseeker/profile.php?setup=1");
          exit();
        }else{
          header("Location: ../jobseeker/dashboard.php");
          exit();
        }
      }
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

        <h3 class="text-center fw-bold mb-1">Login</h3>
        <p class="text-center text-muted mb-4">Join work<span class="text-warning">without</span>limits today</p>
        
        <?php if ($error): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">

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

          <div class="d-grid">
            <button type="submit" class="btn btn-warning text-dark fw-bold">Login</button>
          </div>

        </form>

        <p class="text-center text-muted mt-3 mb-0">
          Not registered? <a href="register.php" class="text-warning">Create an account</a>
        </p>

      </div>
    </div>
  </div>
</div>