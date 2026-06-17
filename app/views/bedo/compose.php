<?php ob_start(); ?>

<?php if (!empty($success)): ?><div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
<?php if (!empty($error)): ?><div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

<div class="row g-4">
    <!-- ── Left: Form ─────────────────────────────────────────────────────── -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-megaphone-fill me-2 text-primary"></i>Post Job Fair Advertisement</h6>
                <small class="text-muted">Select a job fair to auto-fill details, then customize and publish.</small>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/bedo/posts/store" id="postForm">
                    <?= csrfField() ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Select Job Fair <span class="text-danger">*</span></label>
                        <select name="job_fair_request_id" id="fairSelect" class="form-select" required onchange="loadFairDetails(this.value)">
                            <option value="">— Choose an approved job fair —</option>
                            <?php foreach ($jobFairs as $f): ?>
                            <option value="<?= $f['id'] ?>"
                                    data-date="<?= htmlspecialchars($f['requested_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                    data-venue="<?= htmlspecialchars($f['venue'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($f['title'], ENT_QUOTES, 'UTF-8') ?>
                                (<?= $f['vacancy_count'] ?> vacancies | <?= $f['agency_count'] ?> companies)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Advertisement Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="postTitle" class="form-control" required
                               placeholder="e.g. 🔔 Barangay Job Fair — Free Entry!">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Event Date</label>
                            <input type="date" name="event_date" id="postDate" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Event Time</label>
                            <input type="text" name="event_time" id="postTime" class="form-control" placeholder="e.g. 8:00 AM - 5:00 PM">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Venue</label>
                        <input type="text" name="venue" id="postVenue" class="form-control" placeholder="Barangay Hall, Covered Court...">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Description / Ad Copy</label>
                        <textarea name="description" id="postDescription" class="form-control" rows="6"
                                  placeholder="Write your advertisement here. Include details about the job fair, participating companies, available positions, and instructions for job seekers..."></textarea>
                        <small class="text-muted">This text will appear on the public landing page.</small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" name="status" value="published" class="btn btn-primary flex-fill fw-semibold">
                            <i class="bi bi-broadcast me-2"></i>Publish Now
                        </button>
                        <button type="submit" name="status" value="draft" class="btn btn-outline-secondary">
                            <i class="bi bi-save me-1"></i>Save Draft
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ── Right: Job Fair Preview ────────────────────────────────────────── -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm" id="previewCard" style="display:none;">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-eye me-2 text-success"></i>Job Fair Details Preview</h6>
                <small class="text-muted">This information will be auto-inserted into your advertisement.</small>
            </div>
            <div class="card-body" id="previewContent">
                <!-- filled by JS -->
            </div>
        </div>

        <div class="card border-0 shadow-sm" id="emptyPreview">
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-arrow-left-circle display-4 d-block mb-2 opacity-50"></i>
                Select a job fair on the left to preview its details.
            </div>
        </div>
    </div>
</div>

<script>
function loadFairDetails(fairId) {
    if (!fairId) {
        document.getElementById('previewCard').style.display = 'none';
        document.getElementById('emptyPreview').style.display = '';
        return;
    }

    // Auto-fill date/venue from select option
    const opt = document.querySelector(`#fairSelect option[value="${fairId}"]`);
    if (opt) {
        document.getElementById('postDate').value = opt.dataset.date || '';
        document.getElementById('postVenue').value = opt.dataset.venue || '';
        document.getElementById('postTitle').value = '🔔 ' + opt.text.split(' (')[0] + ' — Free Entry!';
    }

    // Fetch full details
    fetch('<?= APP_URL ?>/bedo/compose/preview/' + fairId)
        .then(r => r.json())
        .then(data => {
            if (data.error) return;
            const fair = data.fair;
            const agencies = data.agencies;
            const vacancies = data.vacancies;

            // Build preview
            let html = `
            <div class="mb-3 p-3 bg-light rounded">
                <h6 class="fw-bold mb-1">${fair.title}</h6>
                <div class="small text-muted">
                    <i class="bi bi-calendar-event me-1"></i>${fair.requested_date || 'TBD'}
                    &bull; <i class="bi bi-geo-alt me-1"></i>${fair.venue || 'TBD'}
                </div>
            </div>`;

            if (agencies.length > 0) {
                html += `<h6 class="fw-semibold mb-2"><i class="bi bi-building me-1 text-primary"></i>Participating Companies (${agencies.length})</h6>`;
                agencies.forEach(a => {
                    html += `<div class="mb-2 p-2 border rounded small">
                        <div class="fw-semibold">${a.agency_name}</div>
                        <div class="text-muted">${a.vacancies_summary || 'Vacancies TBD'}</div>
                    </div>`;
                });
            }

            if (vacancies.length > 0) {
                html += `<h6 class="fw-semibold mt-3 mb-2"><i class="bi bi-briefcase me-1 text-success"></i>Available Positions (${vacancies.length})</h6>`;
                html += '<div class="row g-2">';
                vacancies.forEach(v => {
                    html += `<div class="col-md-6">
                        <div class="p-2 border rounded small h-100">
                            <div class="fw-semibold">${v.position}</div>
                            <div class="text-muted">${v.company_name}</div>
                            <span class="badge bg-primary">${v.available_slots} slot(s)</span>
                        </div>
                    </div>`;
                });
                html += '</div>';

                // Auto-fill description
                let desc = `📢 JOB FAIR ADVERTISEMENT\n\n`;
                desc += `📅 Date: ${fair.requested_date || 'To be announced'}\n`;
                desc += `📍 Venue: ${fair.venue || 'To be announced'}\n`;
                desc += `🕗 Time: 8:00 AM - 5:00 PM\n\n`;
                desc += `🏢 Participating Companies:\n`;
                agencies.forEach(a => { desc += `• ${a.agency_name}\n`; });
                desc += `\n💼 Available Positions:\n`;
                vacancies.forEach(v => { desc += `• ${v.position} at ${v.company_name} (${v.available_slots} slot/s)\n`; });
                desc += `\n✅ FREE ADMISSION — Bring your resume and valid ID!\n`;
                desc += `📞 For more info, contact the Barangay Employment Desk.`;
                document.getElementById('postDescription').value = desc;
            }

            document.getElementById('previewContent').innerHTML = html;
            document.getElementById('previewCard').style.display = '';
            document.getElementById('emptyPreview').style.display = 'none';
        })
        .catch(() => {});
}
</script>

<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
