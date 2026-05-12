<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>workwithoutlimits</title>
  
  <?php include 'includes/header.php'; ?>
  
</head>

<body>
  <?php include 'includes/navbar.php'; ?>
  <div class="container py-5 mt-5 text-center">
    <h1 class="display-4 fw-bold">Find work <span class="text-warning">without limits</span></h1>
    <p class="lead text-dark mt-3 mb-4">
      Connect with employers or find the talent you need —
      fast, free, and straightforward.
    </p>
    <a href="auth/register.php" class="btn btn-warning btn-lg text-dark fw-bold me-2">Get Started</a>
  </div>
  <br>

  <!-- CARDS -->
  <div class="container pb-5" id="cards">
    <div class="row g-4">
      <div class="col-md-4">
        <div class="card bg-light-subtle text-black h-100 border-1 rounded-4">
          <div class="card-body text-center p-4">
            <i class="bi bi-search fs-1 text-warning"></i>
            <h5 class="mt-3 fw-bold">Browse Jobs</h5>
            <p class="text-black">Explore a wide range of job listings and find the perfect match for your skills and aspirations.</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card bg-light-subtle text-black h-100 border-1 rounded-4">
          <div class="card-body text-center p-4">
            <i class="bi bi-send fs-1 text-warning"></i>
            <h5 class="mt-3 fw-bold">Apply Easily</h5>
            <p class="text-black">Submit applications with just a few clicks and get noticed by employers quickly.</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card bg-light-subtle text-black h-100 border-1 rounded-4">
          <div class="card-body text-center p-4">
            <i class="bi bi-briefcase fs-1 text-warning"></i>
            <h5 class="mt-3 fw-bold">Post and Hire</h5>
            <p class="text-black">Employers can post job openings and find the right talent to grow their teams.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
  <br>
  <br>
  <br>
</body>

<?php include 'includes/footer.php'; ?>

</html>