<?php ob_start(); ?>

<style>
/* ── Custom Calendar ─────────────────────────────────────────────────────── */
.rw-calendar {
    font-family: inherit;
    width: 100%;
    max-width: 420px;
    border: 1px solid #dee2e6;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0,0,0,.08);
    background: #fff;
    user-select: none;
}
.rw-cal-header {
    background: #1e2a3a;
    color: #fff;
    padding: 14px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.rw-cal-header .month-label {
    font-weight: 700;
    font-size: 1rem;
    letter-spacing: .3px;
}
.rw-cal-nav {
    background: none;
    border: none;
    color: #fff;
    font-size: 1.1rem;
    cursor: pointer;
    padding: 4px 10px;
    border-radius: 6px;
    transition: background .15s;
}
.rw-cal-nav:hover { background: rgba(255,255,255,.15); }

.rw-cal-weekdays {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}
.rw-cal-weekdays span {
    text-align: center;
    font-size: .72rem;
    font-weight: 700;
    color: #6c757d;
    padding: 8px 0;
    text-transform: uppercase;
    letter-spacing: .5px;
}

.rw-cal-days {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    padding: 8px;
    gap: 3px;
}
.rw-day {
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    font-size: .85rem;
    cursor: pointer;
    transition: all .15s;
    position: relative;
    font-weight: 500;
}
/* Empty cells (padding days) */
.rw-day.empty { cursor: default; }

/* Past dates */
.rw-day.past {
    color: #ced4da;
    cursor: not-allowed;
}

/* Available dates */
.rw-day.available:hover {
    background: #e8f4fd;
    color: #0d6efd;
}

/* BOOKED — red indicator */
.rw-day.booked {
    background: #fff5f5;
    color: #dc3545;
    cursor: not-allowed;
    font-weight: 700;
}
.rw-day.booked::after {
    content: '';
    position: absolute;
    bottom: 4px;
    left: 50%;
    transform: translateX(-50%);
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: #dc3545;
}
.rw-day.booked:hover {
    background: #ffe0e0;
}

/* Today */
.rw-day.today {
    border: 2px solid #0d6efd;
    color: #0d6efd;
    font-weight: 700;
}

/* Selected */
.rw-day.selected {
    background: #0d6efd !important;
    color: #fff !important;
    font-weight: 700;
    box-shadow: 0 2px 8px rgba(13,110,253,.35);
}
.rw-day.selected::after { display: none; }

/* Legend */
.cal-legend {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    padding: 10px 16px 12px;
    border-top: 1px solid #f0f0f0;
    background: #fafafa;
}
.cal-legend-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: .78rem;
    color: #555;
}
.cal-dot {
    width: 12px; height: 12px;
    border-radius: 50%;
    flex-shrink: 0;
}
.cal-dot.dot-available { background: #e8f4fd; border: 2px solid #0d6efd; }
.cal-dot.dot-booked    { background: #dc3545; }
.cal-dot.dot-selected  { background: #0d6efd; }
.cal-dot.dot-today     { background: #fff; border: 2px solid #0d6efd; }
</style>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-plus-circle me-2 text-primary"></i>Create Job Fair Request
                </h6>
            </div>
            <div class="card-body">

                <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <form method="POST" action="<?= APP_URL ?>/barangay-captain/store-request" id="requestForm">
                    <?= csrfField() ?>

                    <!-- Event Title -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            Event Title <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="title" class="form-control" required
                               placeholder="e.g. Barangay Poblacion Job Fair 2025"
                               value="<?= htmlspecialchars($old['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <!-- Date Picker with Calendar -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            Requested Date <span class="text-danger">*</span>
                        </label>

                        <!-- Hidden input submitted with form -->
                        <input type="hidden" name="requested_date" id="requestedDate"
                               value="<?= htmlspecialchars($old['requested_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

                        <!-- Selected date display -->
                        <div class="mb-2">
                            <div id="selectedDateDisplay"
                                 class="form-control d-flex align-items-center gap-2"
                                 style="cursor:pointer; background:#f8f9fa; min-height:42px;">
                                <i class="bi bi-calendar3 text-primary"></i>
                                <span id="selectedDateText" class="text-muted">Click a date on the calendar below</span>
                            </div>
                        </div>

                        <!-- Date status badge -->
                        <div id="dateStatus" class="mb-3 d-none">
                            <span id="dateStatusBadge"></span>
                        </div>

                        <!-- Custom Calendar -->
                        <div class="rw-calendar" id="rwCalendar">
                            <div class="rw-cal-header">
                                <button type="button" class="rw-cal-nav" id="prevMonth">
                                    <i class="bi bi-chevron-left"></i>
                                </button>
                                <span class="month-label" id="monthLabel">Loading...</span>
                                <button type="button" class="rw-cal-nav" id="nextMonth">
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            </div>
                            <div class="rw-cal-weekdays">
                                <span>Su</span><span>Mo</span><span>Tu</span>
                                <span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                            </div>
                            <div class="rw-cal-days" id="calDays">
                                <!-- Rendered by JS -->
                            </div>
                            <div class="cal-legend">
                                <div class="cal-legend-item">
                                    <div class="cal-dot dot-available"></div>
                                    <span>Available</span>
                                </div>
                                <div class="cal-legend-item">
                                    <div class="cal-dot dot-booked"></div>
                                    <span>Booked / Unavailable</span>
                                </div>
                                <div class="cal-legend-item">
                                    <div class="cal-dot dot-selected"></div>
                                    <span>Selected</span>
                                </div>
                                <div class="cal-legend-item">
                                    <div class="cal-dot dot-today"></div>
                                    <span>Today</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Venue -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Venue</label>
                        <input type="text" name="venue" class="form-control"
                               placeholder="e.g. Barangay Hall, Covered Court"
                               value="<?= htmlspecialchars($old['venue'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="4"
                                  placeholder="Describe the purpose and details of the job fair..."><?= htmlspecialchars($old['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                            <i class="bi bi-send me-2"></i>Submit Request
                        </button>
                        <a href="<?= APP_URL ?>/barangay-captain/dashboard" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    // ── State ─────────────────────────────────────────────────────────────────
    const today       = new Date();
    today.setHours(0, 0, 0, 0);
    const tomorrow    = new Date(today); tomorrow.setDate(today.getDate() + 1);

    let currentYear   = today.getFullYear();
    let currentMonth  = today.getMonth(); // 0-indexed
    let selectedDate  = null;             // 'YYYY-MM-DD' string
    let bookedDates   = new Set();        // Set of 'YYYY-MM-DD' strings

    // Pre-fill if old input exists
    const prefilledDate = '<?= htmlspecialchars($old['requested_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>';
    if (prefilledDate) {
        selectedDate = prefilledDate;
        const [y, m] = prefilledDate.split('-').map(Number);
        currentYear  = y;
        currentMonth = m - 1;
    }

    // ── DOM refs ──────────────────────────────────────────────────────────────
    const monthLabel       = document.getElementById('monthLabel');
    const calDays          = document.getElementById('calDays');
    const prevBtn          = document.getElementById('prevMonth');
    const nextBtn          = document.getElementById('nextMonth');
    const hiddenInput      = document.getElementById('requestedDate');
    const selectedText     = document.getElementById('selectedDateText');
    const dateStatus       = document.getElementById('dateStatus');
    const dateStatusBadge  = document.getElementById('dateStatusBadge');
    const submitBtn        = document.getElementById('submitBtn');

    const MONTHS = ['January','February','March','April','May','June',
                    'July','August','September','October','November','December'];
    const DAYS   = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

    // ── Fetch booked dates from server ────────────────────────────────────────
    function loadBookedDates() {
        fetch('<?= APP_URL ?>/api/booked-dates')
            .then(r => r.json())
            .then(data => {
                bookedDates = new Set(data.booked || []);
                renderCalendar();
            })
            .catch(() => renderCalendar()); // render even if fetch fails
    }

    // ── Render calendar ───────────────────────────────────────────────────────
    function renderCalendar() {
        monthLabel.textContent = `${MONTHS[currentMonth]} ${currentYear}`;

        const firstDay  = new Date(currentYear, currentMonth, 1).getDay(); // 0=Sun
        const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();

        calDays.innerHTML = '';

        // Empty cells before first day
        for (let i = 0; i < firstDay; i++) {
            const empty = document.createElement('div');
            empty.className = 'rw-day empty';
            calDays.appendChild(empty);
        }

        // Day cells
        for (let d = 1; d <= daysInMonth; d++) {
            const dateStr = `${currentYear}-${String(currentMonth + 1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
            const dateObj = new Date(currentYear, currentMonth, d);
            dateObj.setHours(0, 0, 0, 0);

            const cell = document.createElement('div');
            cell.className = 'rw-day';
            cell.textContent = d;
            cell.dataset.date = dateStr;

            const isPast   = dateObj < tomorrow;
            const isBooked = bookedDates.has(dateStr);
            const isToday  = dateObj.getTime() === today.getTime();
            const isSel    = dateStr === selectedDate;

            if (isSel) {
                cell.classList.add('selected');
            } else if (isPast) {
                cell.classList.add('past');
            } else if (isBooked) {
                cell.classList.add('booked');
                cell.title = '🔴 This date is already booked';
            } else {
                cell.classList.add('available');
            }

            if (isToday && !isSel) cell.classList.add('today');

            // Click handler — only for available future dates
            if (!isPast && !isBooked) {
                cell.addEventListener('click', () => selectDate(dateStr, dateObj));
            }

            calDays.appendChild(cell);
        }
    }

    // ── Select a date ─────────────────────────────────────────────────────────
    function selectDate(dateStr, dateObj) {
        selectedDate = dateStr;
        hiddenInput.value = dateStr;

        // Update display
        const dayName = DAYS[dateObj.getDay()];
        const display = `${dayName}, ${MONTHS[dateObj.getMonth()]} ${dateObj.getDate()}, ${dateObj.getFullYear()}`;
        selectedText.textContent = display;
        selectedText.classList.remove('text-muted');
        selectedText.classList.add('fw-semibold', 'text-dark');

        // Show available badge
        dateStatus.classList.remove('d-none');
        dateStatusBadge.innerHTML =
            '<span class="badge bg-success fs-6 px-3 py-2">' +
            '<i class="bi bi-check-circle-fill me-2"></i>Date Available — You can submit this request' +
            '</span>';

        submitBtn.disabled = false;
        renderCalendar(); // re-render to show selection
    }

    // ── Navigation ────────────────────────────────────────────────────────────
    prevBtn.addEventListener('click', () => {
        currentMonth--;
        if (currentMonth < 0) { currentMonth = 11; currentYear--; }
        renderCalendar();
    });

    nextBtn.addEventListener('click', () => {
        currentMonth++;
        if (currentMonth > 11) { currentMonth = 0; currentYear++; }
        renderCalendar();
    });

    // ── Form validation ───────────────────────────────────────────────────────
    document.getElementById('requestForm').addEventListener('submit', function (e) {
        if (!hiddenInput.value) {
            e.preventDefault();
            alert('Please select a date from the calendar.');
        }
    });

    // ── Init ──────────────────────────────────────────────────────────────────
    loadBookedDates();

    // If pre-filled, show the selected date display
    if (selectedDate) {
        const [y, m, d] = selectedDate.split('-').map(Number);
        const dateObj = new Date(y, m - 1, d);
        const dayName = DAYS[dateObj.getDay()];
        selectedText.textContent = `${dayName}, ${MONTHS[m - 1]} ${d}, ${y}`;
        selectedText.classList.remove('text-muted');
        selectedText.classList.add('fw-semibold', 'text-dark');
        submitBtn.disabled = false;
    }

})();
</script>

<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/main.php';
