<?php
require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../functions/user-functions.php';
require_once '../functions/job-functions.php';
require_once '../includes/header.php';

require_role('seeker');

$seeker_id = $_SESSION['user_id'];

$filters = [];
$jobs = get_jobs($pdo, $filters);


?>
<?php require_once '../includes/navbar.php'; ?>

<div class="container py-4">
    <div class="mb-4">
        <h3 class="fw-bold mb-1"><i class="bi bi-briefcase text-dark me-2"></i>Browse Jobs</h3>
        <p class="text-muted small mb-0">Find jobs that match your skills and accessibility needs.</p>
    </div>
    <!-- FILTER BAR -->
    <div class="filter-bar card bg-dark p-3 mb-4 shadow bg-white rounded-3">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-5">
                <i class="bi bi-search text-muted"></i>
                <label class="mb-0 text-muted fw-semibold">Keyword Search</label>
                <input type="text" class="form-control form-control-sm mt-1" id="keywordSearch" placeholder="Search by title or description...">
            </div>
            <div class="col-12 col-md-3">
                <i class="bi bi-geo-alt text-muted"></i>
                <label class="mb-0 text-muted fw-semibold">Arrangement</label>
                <select type="dropdown" class="form-select form-select-sm mt-1" id="arrangementFilter">
                    <option value="" selected>All Arrangements</option>
                    <option value="remote">Remote</option>
                    <option value="onsite">On-site</option>
                    <option value="hybrid">Hybrid</option>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <i class="bi bi-clock me-1"></i>
                <label class="mb-0 text-muted fw-semibold">Work Type</label>
                <select type="dropdown" class="form-select form-select-sm mt-1" id="workTypeFilter">
                    <option value="" selected>All Types</option>
                    <option value="fulltime">Full-time</option>
                    <option value="parttime">Part-time</option>
                    <option value="freelance">Freelance</option>
                </select>
            </div>
            <div class="col-12 col-md-1 d-flex align-items-end">
                <button class="btn w-100 " id="clearFilters">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
    </div>

<!-- Jobs Header -->
    <div id="jobHead" class="row g-4">
        <div class="container py-4">
            <h3 class="fw-bold mb-1"><i class="bi bi-briefcase text-dark me-2"></i>Jobs</h3>
            <p class="text-muted small mb-0">Explore the latest job postings that match your skills and preferences.</p>
        </div>
    </div>

    <div id="jobListings" class="row g-4">
        <div class="text-center py-5">
            <div class="spinner-border text-primary"></div>
        </div>
    </div>
</div>
<!-- Job Modal -->
<div class="modal fade" id="jobModal" tabindex="-1" aria-labelledby="jobModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white d-flex justify-content-between align-items-start">
                <div>
                    <h5 class="modal-title fw-bold" id="jobModalLabel">Job Details</h5>
                    <p class="mb-0 text-white small" id="jobModalCompany"></p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <span id="jobModalArrangement" class="badge bg-secondary me-1"></span>
                    <span id="jobModalWorkType" class="badge bg-secondary"></span>
                </div>
                <h6 class="fw-bold">Description</h6>
                <p id="jobModalDescription">Loading job details...</p>
                <div class="mt-4">
                    <h6 class="fw-bold">Required Skills</h6>
                    <p id="jobModalSkills">Not specified.</p>
                </div>
                <div class="mt-4">
                    <h6 class="fw-bold">Accessibility Features</h6>
                    <p id="jobModalAccessibility">Not specified.</p>
                </div>
            </div>
            <div class="modal-footer bg-light d-flex justify-content-between">
                <span class="text-muted small" id="jobModalDate"></span>
                <div id="jobModalAction">
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/script.js"></script>