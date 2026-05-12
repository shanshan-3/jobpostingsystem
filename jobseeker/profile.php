<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../functions/user-functions.php';

require_role('seeker');

$user_id = $_SESSION['user_id'];
$isSetup = isset($_GET['setup']) && $_GET['setup'] == '1';
$error   = "";
$success = "";

$accessibility_options = [
    'wheelchair_accessible'   => 'Wheelchair Accessible Office',
    'remote_work_only'               => 'Remote Work Only',
    'flexible_hours'                 => 'Flexible Hours',
];

$profile    = get_seeker_profile($pdo, $user_id) ?? [];
$field      = fn(string $key): string => htmlspecialchars($profile[$key] ?? '', ENT_QUOTES, 'UTF-8');
$savedNeeds = !empty($profile['accessibility_needs'])
    ? array_map('trim', explode(',', $profile['accessibility_needs']))
    : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $full_name       = trim($_POST['full_name']        ?? '');
    $phone           = trim($_POST['phone']            ?? '');
    $location        = trim($_POST['location']         ?? '');
    $skills          = trim($_POST['skills']           ?? '');
    $work_preference = trim($_POST['work_preferences'] ?? '');
    $bio             = trim($_POST['bio']              ?? '');

    $checkedNeeds        = $_POST['accessibility_needs'] ?? [];
    $checkedNeeds        = array_filter($checkedNeeds, fn($v) => array_key_exists($v, $accessibility_options));
    $accessibility_needs = implode(',', $checkedNeeds);

    if ($full_name === '') {
        $error = "Full name is required.";
    } elseif (!in_array($work_preference, ['Remote', 'Hybrid', 'Onsite'], true)) {
        $error = "Invalid work preference selected.";
    }

    $uploadDir  = '../uploads/';
    $resumePath = '';

    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
        $ext     = strtolower(pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'doc', 'docx'];

        if (!in_array($ext, $allowed, true)) {
            $error = "Invalid file type. Only PDF, DOC, DOCX allowed.";
        } elseif ($_FILES['resume']['size'] > 5 * 1024 * 1024) {
            $error = "File too large. Max 5MB.";
        } else {
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $filename = 'resume_' . $user_id . '_' . time() . '.' . $ext;
            $destPath = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['resume']['tmp_name'], $destPath)) {
                $resumePath = 'uploads/' . $filename;
            } else {
                $error = "Upload failed.";
            }
        }
    } elseif (isset($_FILES['resume']) && $_FILES['resume']['error'] !== UPLOAD_ERR_NO_FILE) {
        $error = "Upload error code: " . $_FILES['resume']['error'];
    }

    if (empty($resumePath) && !empty($profile['resume_path'])) {
        $resumePath = $profile['resume_path'];
    }

    if (empty($error)) {
        $saved = save_seeker_profile($pdo, [
            'user_id'             => $user_id,
            'full_name'           => $full_name,
            'phone'               => $phone,
            'location'            => $location,
            'skills'              => $skills,
            'work_preference'     => $work_preference,
            'accessibility_needs' => $accessibility_needs,
            'bio'                 => $bio,
            'resume_path'         => $resumePath,
        ]);

        if ($saved) {
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Failed to save profile. Please try again.";
        }
    }
}
include '../includes/header.php';
?>

<?php require_once '../includes/navbar.php'; ?>

<div class="container py-5">
    <div class="col-12 col-md-8 col-lg-6 mx-auto">
        <div class="card border-0 shadow">
            <div class="card-body p-5">
                <h3 class="text-center fw-bold mb-1">Setup Your Profile</h3>
                <p class="text-center text-muted mb-4">Let's set up your jobseeker profile</p>

                <?php if ($isSetup): ?>
                    <div class="alert alert-info" role="alert">
                        Welcome! Please complete your profile to get started.
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success" role="alert">
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form action="profile.php" method="POST" enctype="multipart/form-data">

                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" id="full_name"
                            class="form-control" value="<?= $field('full_name') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" name="phone" id="phone"
                            class="form-control" value="<?= $field('phone') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" name="location" id="location"
                            class="form-control" value="<?= $field('location') ?>"
                            placeholder="City or region">
                    </div>

                    <div class="mb-3">
                        <label for="skills" class="form-label">Skills</label>
                        <input type="text" name="skills" id="skills"
                            class="form-control" value="<?= $field('skills') ?>"
                            placeholder="e.g. typing, data entry, customer service">
                        <div class="form-text">Separate each skill with a comma.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Accessibility Needs</label>
                        <?php foreach ($accessibility_options as $value => $label): ?>
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="accessibility_needs[]"
                                    value="<?= htmlspecialchars($value) ?>"
                                    id="need_<?= htmlspecialchars(str_replace(' ', '_', $value)) ?>"
                                    <?= in_array($value, $savedNeeds) ? 'checked' : '' ?>>
                                <label class="form-check-label"
                                    for="need_<?= htmlspecialchars(str_replace(' ', '_', $value)) ?>">
                                    <?= htmlspecialchars($label) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Work Preference</label>
                        <?php
                        $workOptions = ['Remote', 'Hybrid', 'Onsite'];
                        $savedPref   = $profile['work_preference'] ?? 'Remote';
                        foreach ($workOptions as $opt):
                        ?>
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="radio"
                                    name="work_preferences"
                                    id="pref_<?= $opt ?>"
                                    value="<?= $opt ?>"
                                    <?= $savedPref === $opt ? 'checked' : '' ?>
                                    required>
                                <label class="form-check-label" for="pref_<?= $opt ?>">
                                    <?= $opt ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="mb-3">
                        <label for="bio" class="form-label">Bio</label>
                        <textarea name="bio" id="bio" class="form-control" rows="4"
                            placeholder="Tell employers about yourself…"><?= $field('bio') ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="resume" class="form-label">Upload Resume</label>
                        <?php if (!empty($profile['resume_path'])): ?>
                            <p class="small text-muted mb-1">
                                Current: <a href="../<?= htmlspecialchars($profile['resume_path']) ?>"
                                    target="_blank">View file</a> — upload below to replace it.
                            </p>
                        <?php endif; ?>
                        <input type="file" name="resume" id="resume"
                            class="form-control" accept=".pdf,.doc,.docx">
                        <div class="form-text">Accepted: PDF, DOC, DOCX. Max 5MB.</div>
                    </div>

                    <button type="submit" class="btn btn-warning btn-lg text-dark fw-bold w-100">
                        Save Profile
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>