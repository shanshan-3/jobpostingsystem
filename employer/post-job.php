<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../functions/user-functions.php';
require_once '../functions/job-functions.php'; 

require_role('employer');

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    $data = [
        ':employer_id' => $_SESSION['user_id'],
        ':title' => trim($_POST['job_title'] ?? ''),
        ':description' => trim($_POST['job_description'] ?? ''),
        ':required_skills' => trim($_POST['required_skills'] ?? ''),
        ':work_type' => trim($_POST['work_type'] ?? ''),
        ':arrangement' => trim($_POST['arrangement'] ?? ''),
        ':accessibility_features' => isset($_POST['accessibility']) ? implode(', ', $_POST['accessibility']) : '',
    ];
    if (post_job($pdo, $data)) {
        header('Location: dashboard.php');
        exit;
    } else {
        echo '<div class="alert alert-danger">Failed to post the job. Please try again.</div>';
    }
}
require_once '../includes/header.php';
?>
<?php include '../includes/navbar.php'; ?>

<div class="container py-5">
    <div class="col-12 col-md-8 col-lg-6 mx-auto">
        <h2 class="text-center mb-4" style="font-weight: 600;">Post a New Job</h2>
        <p class="text-center text-muted mb-4">Fill out the form below to post a new job opening.</p>
        <div class="card shadow-sm">
            <div class="card-body">

                <form action="post-job.php" method="POST">

                    <div class="mb-3">
                        <label for="job_title" class="form-label fw-semibold">Job Title</label>
                        <input type="text" class="form-control" id="job_title" name="job_title" placeholder="Enter the job title" required>

                    </div>

                    <div class="mb-3">
                        <label for="job_description" class="form-label fw-semibold">Job Description</label>
                        <textarea class="form-control" id="job_description" name="job_description" rows="4" placeholder="Enter the job description" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="required_skills" class="form-label fw-semibold">Required Skills</label>
                        <input type="text" class="form-control" id="required_skills" name="required_skills" placeholder="Write the required skills separated by commas." required>
                        <div class="form-text">Separate skills with commas.</div>
                    </div>

                    <div class="mb-4">
                        <select name="work_type" class="form-select" required>
                            <option value="" disabled selected>Select Work Type</option>
                            <option value="Fulltime">Fulltime</option>
                            <option value="Part-time">Part-time</option>
                            <option value="Freelance">Freelance</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="arrangement" class="form-label fw-semibold">Work Arrangement</label>
                        <div class="form-check">
                            <input type="radio" class="form-check-input" id="remote" name="arrangement" value="remote" required>
                            <label for="remote" class="form-check-label me-3">remote</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" class="form-check-input" id="hybrid" name="arrangement" value="hybrid" required>
                            <label for="hybrid" class="form-check-label me-3">hybrid</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" class="form-check-input" id="onsite" name="arrangement" value="on-site" required>
                            <label for="onsite" class="form-check-label me-3">on-site</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Accessibility Features</label>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="wheelchair_accessible" name="accessibility[]" value="Wheelchair Accessible">
                            <label for="wheelchair_accessible" class="form-check-label me-3">Wheelchair Accessible Office</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="remote_work-only" name="accessibility[]" value="Remote Work Only">
                            <label for="remote_work-only" class="form-check-label me-3">Remote Work Only</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="flexible" name="accessibility[]" value="Flexible Hours">
                            <label for="flexible" class="form-check-label me-3">Flexible Hours</label>
                        </div>
                    </div>
                    <br>
                    <button type="submit" class="btn btn-warning btn-lg text-dark fw-bold w-100">
                        Post Job
                    </button>

                </form>
            </div>
        </div>
    </div>
</div>