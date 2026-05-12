<?php
require_once "../includes/session.php";
require_once "../config/database.php";
require_once "../functions/user-functions.php";

require_role('employer');

$user_id = $_SESSION['user_id'];
$isSetup = isset($_GET['setup']) && $_GET['setup'] === '1';
$error = "";
$success = "";

$profile = get_employer_profile($pdo, $user_id);
$field = fn($key) => htmlspecialchars($profile[$key] ?? '', ENT_QUOTES, 'UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_name = trim($_POST['company_name']    ?? '');
    $industry     = trim($_POST['industry']        ?? '');
    $location     = trim($_POST['location']        ?? '');
    $description  = trim($_POST['description']     ?? '');
    $contact_email = trim($_POST['contact_email']   ?? '');

    if ($company_name == '') {
        $error = "Company name is required.";
    } elseif ($industry == '') {
        $error = "Industry is required.";
    } elseif ($location == '') {
        $error = "Location is required.";
    } elseif ($description == '') {
        $error = "Company description is required.";
    } elseif ($contact_email == '') {
        $error = "Contact email is required.";
    }

    if (empty($error)) {
        $saved = save_employer_profile($pdo, [
            'user_id' => $user_id,
            'company_name' => $company_name,
            'industry' => $industry,
            'location' => $location,
            'description' => $description,
            'contact_email' => $contact_email
        ]);

        if ($saved) {
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Failed to save profile. Please try again.";
        }
    }
}
include "../includes/header.php";
?>
<?php include "../includes/navbar.php"; ?>

<div class="container py-5">
    <div class="col-12 col-md-8 col-lg-6 mx-auto">
        <div class="card border-0 shadow">
            <div class="card-body p-5">
                <h3 class="text-center fw-bold mb-1">Setup Profile</h3>
                <p class="text-center text-muted mb-4">Let's set up your employer profile</p>

                <?php if ($isSetup): ?>
                    <div class="alert alert-info" role="alert">
                        Welcome! Please complete your company profile to get started.
                    </div>
                <?php endif; ?>

                <form action="profile.php" method="POST">

                    <div class="mb-3">
                        <label for="company_name" class="form-label">Company Name</label>
                        <input type="text" class="form-control" id="company_name" name="company_name" value="<?= $field('company_name') ?>" placeholder="Enter your company name" required>
                    </div>

                    <div class="mb-3">
                        <label for="industry" class="form-label">Industry</label>
                        <input type="text" class="form-control" id="industry" name="industry" value="<?= $field('industry') ?>" placeholder="e.g. Technology, Finance, Healthcare" required>
                    </div>

                    <div class="mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" class="form-control" id="location" name="location" value="<?= $field('location') ?>" placeholder="Enter your company's location" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Company Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4" placeholder="Briefly describe your company and its mission" required><?= $field('description') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="contact_email" class="form-label">Contact Email</label>
                        <input type="email" class="form-control" id="contact_email" name="contact_email" value="<?= $field('contact_email') ?>" placeholder="name@company.com" required>
                    </div>

                    <button type="submit" class="btn btn-warning btn-lg text-dark fw-bold w-100">
                        Save Profile
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>