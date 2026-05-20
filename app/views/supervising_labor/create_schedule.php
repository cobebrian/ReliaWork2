<?php ob_start(); ?>
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-calendar-plus me-2 text-primary"></i>Create New Schedule</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/supervising-labor/schedules/store">
                    <?= csrfField() ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Event Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required
                               placeholder="e.g. Community Job Fair 2025"
                               value="<?= htmlspecialchars($old['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Event Date <span class="text-danger">*</span></label>
                            <input type="date" name="event_date" class="form-control" required
                                   min="<?= date('Y-m-d') ?>"
                                   value="<?= htmlspecialchars($old['event_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Event Time</label>
                            <input type="time" name="event_time" class="form-control"
                                   value="<?= htmlspecialchars($old['event_time'] ?? '08:00', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Venue</label>
                        <input type="text" name="venue" class="form-control"
                               placeholder="e.g. Barangay Hall Plaza"
                               value="<?= htmlspecialchars($old['venue'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="3"
                                  placeholder="Brief description of the event..."><?= htmlspecialchars($old['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-2"></i>Create Schedule
                        </button>
                        <a href="<?= APP_URL ?>/supervising-labor/schedules" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/main.php';
