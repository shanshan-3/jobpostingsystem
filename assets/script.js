document.addEventListener('DOMContentLoaded', function() {
        const jobModal = document.getElementById('jobModal');

        if (!jobModal) {
            return;
        }

        jobModal.addEventListener('show.bs.modal', function(event) {
            const triggerButton = event.relatedTarget;

            if (!triggerButton) {
                return;
            }

            const title = triggerButton.getAttribute('data-job-title') || 'Job Details';
            const company = triggerButton.getAttribute('data-job-company') || '';
            const description = triggerButton.getAttribute('data-job-description') || 'No description available.';

            const modalTitle = jobModal.querySelector('#jobModalLabel');
            const modalDescription = jobModal.querySelector('#jobModalDescription');

            if (modalTitle) {
                modalTitle.textContent = title;
            }

            if (modalDescription) {
                modalDescription.textContent = description || company || 'No description available.';
            }
        });
    });
    
    document.addEventListener('DOMContentLoaded', function() {
    const jobListings = document.getElementById('jobListings');
    
    function fetchFilteredJobs() {
        if (!jobListings) return;
        
        const keyword = document.getElementById('keywordSearch').value;
        const arrangement = document.getElementById('arrangementFilter').value;
        const workType = document.getElementById('workTypeFilter').value;
        
        const params = new URLSearchParams({
            keyword: keyword,
            arrangement: arrangement,
            work_type: workType
        });

        jobListings.innerHTML = '<div class="text-center py-5 w-100"><div class="spinner-border text-primary"></div></div>';

        fetch(`fetch-jobs.php?${params.toString()}`)
            .then(response => response.text())
            .then(html => {
                jobListings.innerHTML = html;
            })
            .catch(error => {
                console.error('Error fetching jobs:', error);
                jobListings.innerHTML = '<p class="text-danger">Failed to load jobs.</p>';
            });
    }

    const keywordInput = document.getElementById('keywordSearch');
    const arrangementSelect = document.getElementById('arrangementFilter');
    const workTypeSelect = document.getElementById('workTypeFilter');
    const clearBtn = document.getElementById('clearFilters');

    if (keywordInput) keywordInput.addEventListener('keyup', fetchFilteredJobs);
    if (arrangementSelect) arrangementSelect.addEventListener('change', fetchFilteredJobs);
    if (workTypeSelect) workTypeSelect.addEventListener('change', fetchFilteredJobs);
    
    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            keywordInput.value = '';
            arrangementSelect.value = '';
            workTypeSelect.value = '';
            fetchFilteredJobs();
        });
    }

    fetchFilteredJobs();

    const jobModal = document.getElementById('jobModal');
    if (jobModal) {
        jobModal.addEventListener('show.bs.modal', function(event) {
            const btn = event.relatedTarget;
            
            jobModal.querySelector('#jobModalLabel').textContent = btn.dataset.jobTitle;
            jobModal.querySelector('#jobModalCompany').innerHTML = `<i class="bi bi-building me-1"></i> ${btn.dataset.jobCompany}`;
            jobModal.querySelector('#jobModalDescription').textContent = btn.dataset.jobDescription;
            jobModal.querySelector('#jobModalArrangement').textContent = btn.dataset.jobArrangement;
            jobModal.querySelector('#jobModalWorkType').textContent = btn.dataset.jobWorktype;
            
            jobModal.querySelector('#jobModalSkills').textContent = btn.dataset.jobSkills || 'Not specified.';
            jobModal.querySelector('#jobModalAccessibility').textContent = btn.dataset.jobAccessibility || 'Not specified.';
            
            jobModal.querySelector('#jobModalDate').textContent = `Posted: ${btn.dataset.jobCreated}`;
            
            const actionContainer = jobModal.querySelector('#jobModalAction');
            const hasApplied = btn.dataset.jobApplied === '1';
            const jobId = btn.dataset.jobId;

            if (hasApplied) {
                actionContainer.innerHTML = `<button class="btn btn-success" disabled>Already Applied</button>`;
            } else {
                actionContainer.innerHTML = `
                    <form action="apply-job.php" method="POST">
                        <input type="hidden" name="job_id" value="${jobId}">
                        <button type="submit" class="btn btn-dark text-warning fw-bold">Apply Now</button>
                    </form>`;
            }
        });
    }
});