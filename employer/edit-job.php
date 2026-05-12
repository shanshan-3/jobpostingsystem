<?php
include '../includes/header.php';
include '../functions/job-functions.php';
include '../functions/user-functions.php';
include '../includes/session.php';
include '../config/database.php';

require_role('employer');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$job_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$employer_id = $_SESSION['user_id'];

if (!$job_id || $job_id <= 0) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $stmt = $pdo->prepare("
        DELETE FROM job_posting
        WHERE job_id = ?
        AND employer_id = ?
        ");
        $stmt->execute([$job_id, $employer_id]);
        header('Location: dashboard.php?msg=deleted');
        exit;
    }

    if ($action === 'update') {
        $status = isset($_POST['status']) ? 'active' : 'closed';

        $accessibility = isset($_POST['accessibility']) ? implode(', ', $_POST['accessibility']) : '';

        $data = [
            'title' => $_POST['job_title'],
            'description' => $_POST['job_description'],
            'required_skills' => $_POST['required_skills'],
            'work_type' => $_POST['work_type'],
            'arrangement' => $_POST['arrangement'],
            'accessibility_features' => $accessibility,
            'status' => $status
        ];

        update_job($pdo, $job_id, $data);
        header('Location: dashboard.php?msg=updated');
        exit;
    }
}

$stmt = $pdo->prepare("SELECT * FROM job_posting WHERE job_id = ? AND employer_id = ?");
$stmt->execute([$job_id, $employer_id]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    header('Location: dashboard.php');
    exit;
}

$saved_accessibility = explode(', ', $job['accessibility_features'] ?? '');

?>
<?php include '../includes/navbar.php'; ?>

<div class="container py-5">
    <div class="col-12 col-md-8 col-lg-6 mx-auto">
        <div class="card shadow-sm border-0">
            <form action="edit-job.php?id=<?= $job_id ?>" method="POST">
                <div class="card-header bg-white pt-4 pb-0 border-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h3 mb-1" style="font-weight: 600;">Edit Job</h2>
                        <p class="text-muted small mb-0">Fill out the form below to edit the job listing.</p>
                    </div>
                    <div class="form-check form-switch d-flex flex-column align-items-end">
                        <input class="form-check-input fs-4 m-0" type="checkbox" role="switch" id="jobStatusToggle" name="status" value="active" <?= ($job['status'] === 'active') ? 'checked' : '' ?>>
                        <label class="form-check-label small fw-bold mt-1 <?= ($job['status'] === 'active') ? 'text-success' : 'text-muted' ?>" for="jobStatusToggle" id="jobStatusLabel">
                            <?= ($job['status'] === 'active') ? 'Active' : 'Closed' ?>
                        </label>
                    </div>
                </div>
                <br>
                <div class="card-body">


                    <div class="mb-3">
                        <label for="job_title" class="form-label fw-semibold">Job Title</label>
                        <input type="text" class="form-control" id="job_title" name="job_title" value="<?= htmlspecialchars($job['title']) ?>" placeholder="Enter the job title" required>
                    </div>

                    <div class="mb-3">
                        <label for="job_description" class="form-label fw-semibold">Job Description</label>
                        <textarea class="form-control" id="job_description" name="job_description" rows="4" placeholder="Enter the job description" required><?= htmlspecialchars($job['description']) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="required_skills" class="form-label fw-semibold">Required Skills</label>
                        <input type="text" class="form-control" id="required_skills" name="required_skills" value="<?= htmlspecialchars($job['required_skills']) ?>" placeholder="Write the required skills separated by commas." required>
                        <div class="form-text">Separate skills with commas.</div>
                    </div>

                    <div class="mb-4">
                        <select name="work_type" class="form-select" required>
                            <option value="" disabled selected>Select Work Type</option>
                            <option value="fulltime" <?= ($job['work_type'] === 'fulltime') ? 'selected' : '' ?>>Fulltime</option>
                            <option value="parttime" <?= ($job['work_type'] === 'parttime') ? 'selected' : '' ?>>Part-time</option>
                            <option value="freelance" <?= ($job['work_type'] === 'freelance') ? 'selected' : '' ?>>Freelance</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="arrangement" class="form-label fw-semibold">Work Arrangement</label>
                        <div class="form-check">
                            <input type="radio" class="form-check-input" id="remote" name="arrangement" value="remote" <?= ($job['arrangement'] === 'remote') ? 'checked' : '' ?> required>
                            <label for="remote" class="form-check-label me-3">Remote</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" class="form-check-input" id="hybrid" name="arrangement" value="hybrid" <?= ($job['arrangement'] === 'hybrid') ? 'checked' : '' ?> required>
                            <label for="hybrid" class="form-check-label me-3">Hybrid</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" class="form-check-input" id="onsite" name="arrangement" value="on-site" <?= ($job['arrangement'] === 'on-site') ? 'checked' : '' ?> required>
                            <label for="onsite" class="form-check-label me-3">On-site</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Accessibility Options</label>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="wheelchair_accessible" name="accessibility[]" value="Wheelchair Accessible" <?= in_array('Wheelchair Accessible', $saved_accessibility) ? ' checked' : '' ?>>
                            <label for="wheelchair_accessible" class="form-check-label me-3">Wheelchair Accessible Office</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="remote_work-only" name="accessibility[]" value="Remote Work Only" <?= in_array('Remote Work Only', $saved_accessibility) ? ' checked' : '' ?>>
                            <label for="remote_work-only" class="form-check-label me-3">Remote Work Only</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="flexible" name="accessibility[]" value="Flexible Hours" <?= in_array('Flexible Hours', $saved_accessibility) ? ' checked' : '' ?>>
                            <label for="flexible" class="form-check-label me-3">Flexible Hours</label>
                        </div>
                    </div>
                    <br>
                    <div class="d-flex justify-content-between align-items-center border-top pt-4">
                        <button type="submit" name="action" value="delete" class="btn btn-link text-danger text-decoration-none px-0" onclick="return confirm('Are you sure you want to delete this job?')" formnovalidate>
                            <i class="bi bi-trash"></i> Delete Job
                        </button>

                        <div class="d-flex gap-2">
                            <a href="dashboard.php" class="btn btn-light text-muted">Cancel</a>
                            <button type="submit" name="action" value="update" class="btn btn-warning px-4 fw-bold">
                                Save Changes
                            </button>
                        </div>
                    </div>

            </form>
        </div>
    </div>
</div>
</div>

<!-- Toggle Script -->
<script>
    const toggle = document.getElementById('jobStatusToggle');
    const label = document.getElementById('jobStatusLabel');

    toggle.addEventListener('change', function() {
        if (this.checked) {
            label.textContent = "Active";
            label.classList.remove('text-muted');
            label.classList.add('text-success');
        } else {
            label.textContent = "Closed";
            label.classList.remove('text-success');
            label.classList.add('text-muted');
        }
    });
</script>