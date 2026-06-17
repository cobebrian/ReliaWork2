<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReliaWork2 — Barangay Job Fair</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --primary:#0a3d62; --accent:#1e88e5; --warning:#f39c12; }
        body { font-family:'Segoe UI',sans-serif; background:#f4f7fc; }

        /* ── Top Navbar ── */
        .landing-nav {
            background: var(--primary);
            padding: 12px 0;
            position: sticky; top:0; z-index:1000;
            box-shadow: 0 2px 12px rgba(0,0,0,.25);
        }
        .landing-nav .brand { color:#fff; font-weight:800; font-size:1.3rem; text-decoration:none; }
        .landing-nav .brand span { color:#ffd600; }

        /* ── Hero ── */
        .hero {
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 60%, #26c6da 100%);
            color:#fff; padding:80px 0 60px;
        }
        .hero h1 { font-size:2.8rem; font-weight:900; line-height:1.15; }
        .hero .badge-stat {
            background:rgba(255,255,255,.15);
            border:1px solid rgba(255,255,255,.3);
            border-radius:50px;
            padding:8px 20px;
            display:inline-flex;
            align-items:center;
            gap:8px;
            font-size:.9rem;
            backdrop-filter:blur(4px);
        }

        /* ── Section titles ── */
        .section-title { font-size:1.6rem; font-weight:800; color:var(--primary); }
        .section-sub { color:#6c757d; }

        /* ── Job Fair Cards ── */
        .fair-card {
            border:none;
            border-radius:16px;
            box-shadow:0 4px 20px rgba(0,0,0,.08);
            transition:transform .2s, box-shadow .2s;
            overflow:hidden;
        }
        .fair-card:hover { transform:translateY(-4px); box-shadow:0 8px 32px rgba(0,0,0,.14); }
        .fair-card .card-accent {
            height:5px;
            background:linear-gradient(90deg, var(--accent), #26c6da);
        }
        .fair-card .event-badge {
            background:var(--primary);
            color:#fff;
            border-radius:10px;
            padding:6px 14px;
            font-size:.8rem;
            font-weight:600;
        }

        /* ── Vacancy Wall ── */
        .vacancy-card {
            border:none;
            border-radius:12px;
            box-shadow:0 2px 12px rgba(0,0,0,.07);
            transition:box-shadow .2s;
        }
        .vacancy-card:hover { box-shadow:0 4px 20px rgba(0,0,0,.13); }
        .vacancy-card .slot-badge {
            background:#e8f5e9; color:#2e7d32;
            border-radius:20px; font-size:.78rem; font-weight:700;
            padding:3px 10px;
        }

        /* ── Announcement ticker ── */
        .ticker-wrap { background:var(--warning); padding:8px 0; overflow:hidden; }
        .ticker-content { white-space:nowrap; animation:ticker 30s linear infinite; display:inline-block; }
        @keyframes ticker { 0%{transform:translateX(100vw)} 100%{transform:translateX(-100%)} }

        /* ── Login panel in navbar ── */
        .nav-login-form { display:flex; gap:8px; align-items:center; }
        .nav-login-form input { border-radius:8px; padding:5px 10px; font-size:.85rem; border:none; }
        .nav-login-form .btn { font-size:.85rem; padding:5px 16px; border-radius:8px; }

        /* ── Footer ── */
        .landing-footer { background:var(--primary); color:#fff; padding:32px 0; margin-top:60px; }
        .landing-footer a { color:rgba(255,255,255,.7); text-decoration:none; }
        .landing-footer a:hover { color:#fff; }
    </style>
</head>
<body>

<!-- ── TOP NAVBAR ─────────────────────────────────────────────────────────── -->
<nav class="landing-nav">
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <!-- Brand -->
            <a href="<?= APP_URL ?>/" class="brand">
                <i class="bi bi-briefcase-fill me-2"></i>Relia<span>Work2</span>
                <small class="opacity-75 fw-normal ms-1" style="font-size:.7rem;">Barangay Job Fair</small>
            </a>

            <!-- Right side: Login form or user info -->
            <?php if (isset($_SESSION['user_id']) && isset($_SESSION['user'])): ?>
            <div class="d-flex align-items-center gap-2">
                <span class="text-white-50 small">
                    <i class="bi bi-person-circle me-1"></i>
                    <?= htmlspecialchars($_SESSION['user']['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </span>
                <a href="<?= APP_URL ?>/dashboard" class="btn btn-warning btn-sm fw-semibold">
                    <i class="bi bi-speedometer2 me-1"></i>My Dashboard
                </a>
                <a href="<?= APP_URL ?>/logout" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                </a>
            </div>
            <?php else: ?>
            <form method="POST" action="<?= APP_URL ?>/login" class="nav-login-form" id="navLoginForm">
                <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
                <input type="email" name="email" placeholder="Email" required autocomplete="email"
                       style="width:160px;">
                <input type="password" name="password" placeholder="Password" required
                       style="width:130px;">
                <button type="submit" class="btn btn-warning fw-semibold">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Sign In
                </button>
                <a href="<?= APP_URL ?>/register" class="btn btn-outline-light btn-sm">Register</a>
            </form>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- ── ANNOUNCEMENT TICKER ───────────────────────────────────────────────── -->
<?php if (!empty($announcements)): ?>
<div class="ticker-wrap">
    <div class="ticker-content">
        <?php foreach ($announcements as $a): ?>
        <span class="me-5">
            <i class="bi bi-megaphone-fill me-1"></i>
            <strong><?= htmlspecialchars($a['title'], ENT_QUOTES, 'UTF-8') ?></strong>
            — <?= htmlspecialchars(substr(strip_tags($a['content']), 0, 100), ENT_QUOTES, 'UTF-8') ?>...
        </span>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- ── HERO SECTION ──────────────────────────────────────────────────────── -->
<section class="hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h1 class="mb-3">Find Your <span style="color:#ffd600;">Dream Job</span><br>at the Barangay Job Fair</h1>
                <p class="fs-5 opacity-85 mb-4">
                    Free job placement services for all barangay residents.
                    Meet top companies, submit your resume, and get hired today.
                </p>
                <div class="d-flex flex-wrap gap-3 mb-4">
                    <div class="badge-stat">
                        <i class="bi bi-calendar-check-fill" style="color:#ffd600;"></i>
                        <span><strong><?= $stats['upcoming_fairs'] ?></strong> Upcoming Fair(s)</span>
                    </div>
                    <div class="badge-stat">
                        <i class="bi bi-briefcase-fill" style="color:#ffd600;"></i>
                        <span><strong><?= $stats['total_vacancies'] ?></strong> Open Positions</span>
                    </div>
                    <div class="badge-stat">
                        <i class="bi bi-building-fill" style="color:#ffd600;"></i>
                        <span><strong><?= $stats['total_companies'] ?></strong> Companies</span>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="#vacancies" class="btn btn-warning btn-lg fw-bold px-4">
                        <i class="bi bi-search me-2"></i>Browse Jobs
                    </a>
                    <a href="#job-fairs" class="btn btn-outline-light btn-lg px-4">
                        <i class="bi bi-calendar-event me-2"></i>View Job Fairs
                    </a>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="<?= APP_URL ?>/register" class="btn btn-light btn-lg px-4">
                        <i class="bi bi-person-plus me-2"></i>Register Free
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-5 text-center d-none d-lg-block">
                <i class="bi bi-people-fill" style="font-size:10rem;opacity:.25;"></i>
            </div>
        </div>
    </div>
</section>

<!-- ── PUBLISHED JOB FAIR ADS ─────────────────────────────────────────────── -->
<section id="job-fairs" class="py-5">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="section-title"><i class="bi bi-megaphone-fill me-2 text-primary"></i>Upcoming Job Fairs</h2>
            <p class="section-sub">Posted by the Barangay Employment Desk Officer</p>
        </div>

        <?php if (empty($posts)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-calendar-x display-4 d-block mb-2 opacity-50"></i>
            <p>No job fair advertisements posted yet. Check back soon!</p>
        </div>
        <?php else: ?>
        <div class="row g-4">
        <?php foreach ($posts as $post): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card fair-card h-100">
                <div class="card-accent"></div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="event-badge">
                            <i class="bi bi-calendar-event me-1"></i>
                            <?= $post['event_date'] ? date('M d, Y', strtotime($post['event_date'])) : formatDate($post['requested_date']) ?>
                        </span>
                        <?php if ($post['event_time']): ?>
                        <span class="badge bg-light text-dark border small">
                            <i class="bi bi-clock me-1"></i><?= htmlspecialchars($post['event_time'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                        <?php endif; ?>
                    </div>

                    <h5 class="fw-bold mb-2"><?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?></h5>

                    <?php if ($post['venue'] || $post['fair_venue']): ?>
                    <p class="text-muted small mb-2">
                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                        <?= htmlspecialchars($post['venue'] ?: $post['fair_venue'], ENT_QUOTES, 'UTF-8') ?>
                    </p>
                    <?php endif; ?>

                    <div class="d-flex gap-2 mb-3">
                        <?php if ($post['company_count'] > 0): ?>
                        <span class="badge bg-primary-subtle text-primary">
                            <i class="bi bi-building me-1"></i><?= $post['company_count'] ?> Companies
                        </span>
                        <?php endif; ?>
                        <?php if ($post['vacancy_count'] > 0): ?>
                        <span class="badge bg-success-subtle text-success">
                            <i class="bi bi-briefcase me-1"></i><?= $post['vacancy_count'] ?> Positions
                        </span>
                        <?php endif; ?>
                        <?php if ($post['total_slots'] > 0): ?>
                        <span class="badge bg-warning-subtle text-warning">
                            <?= $post['total_slots'] ?> Slots
                        </span>
                        <?php endif; ?>
                    </div>

                    <?php if ($post['description']): ?>
                    <div class="text-muted small" style="white-space:pre-wrap;max-height:120px;overflow:hidden;">
                        <?= htmlspecialchars(substr($post['description'], 0, 250), ENT_QUOTES, 'UTF-8') ?>
                        <?= strlen($post['description']) > 250 ? '...' : '' ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-white border-0 pt-0 pb-3 px-4">
                    <a href="#vacancies" class="btn btn-primary w-100 fw-semibold">
                        <i class="bi bi-search me-2"></i>View Available Positions
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ── JOB VACANCY WALL ──────────────────────────────────────────────────── -->
<section id="vacancies" class="py-5" style="background:#fff;">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="section-title"><i class="bi bi-briefcase-fill me-2 text-success"></i>Job Vacancy Wall</h2>
            <p class="section-sub">All available positions from participating companies</p>
        </div>

        <!-- Search/Filter -->
        <div class="row justify-content-center mb-4">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" id="vacSearch" class="form-control" placeholder="Search positions, companies..."
                           oninput="filterVacancies(this.value)">
                </div>
            </div>
        </div>

        <?php if (empty($vacancies)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-briefcase display-4 d-block mb-2 opacity-50"></i>
            <p>No open vacancies at the moment. Check back soon!</p>
        </div>
        <?php else: ?>
        <div class="row g-3" id="vacancyGrid">
        <?php foreach ($vacancies as $v): ?>
        <div class="col-md-6 col-lg-4 vacancy-item">
            <div class="card vacancy-card h-100 p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="fw-bold mb-0 vac-position"><?= htmlspecialchars($v['position'], ENT_QUOTES, 'UTF-8') ?></h6>
                    <span class="slot-badge flex-shrink-0 ms-2"><?= $v['available_slots'] ?> slot/s</span>
                </div>
                <p class="text-primary small fw-semibold mb-1 vac-company">
                    <i class="bi bi-building me-1"></i><?= htmlspecialchars($v['company_name'], ENT_QUOTES, 'UTF-8') ?>
                </p>
                <?php if ($v['company_location']): ?>
                <p class="text-muted small mb-1">
                    <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($v['company_location'], ENT_QUOTES, 'UTF-8') ?>
                </p>
                <?php endif; ?>
                <?php if ($v['job_fair_title']): ?>
                <p class="text-muted small mb-2">
                    <i class="bi bi-calendar-event me-1"></i><?= htmlspecialchars($v['job_fair_title'], ENT_QUOTES, 'UTF-8') ?>
                    <?php if ($v['requested_date']): ?>
                    — <?= date('M d', strtotime($v['requested_date'])) ?>
                    <?php endif; ?>
                </p>
                <?php endif; ?>
                <?php if ($v['qualifications']): ?>
                <div class="bg-light rounded p-2 small text-muted mt-auto">
                    <i class="bi bi-check2-circle me-1 text-success"></i>
                    <?= htmlspecialchars(substr($v['qualifications'], 0, 100), ENT_QUOTES, 'UTF-8') ?>
                    <?= strlen($v['qualifications']) > 100 ? '...' : '' ?>
                </div>
                <?php endif; ?>
                <?php if ($v['mobile_number'] || $v['gmail_address']): ?>
                <div class="mt-2 small text-muted">
                    <?php if ($v['mobile_number']): ?>
                    <span><i class="bi bi-phone me-1"></i><?= htmlspecialchars($v['mobile_number'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                    <?php if ($v['gmail_address']): ?>
                    <span class="ms-2"><i class="bi bi-envelope me-1"></i><?= htmlspecialchars($v['gmail_address'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="<?= APP_URL ?>/register" class="btn btn-sm btn-outline-primary mt-2 w-100">
                    <i class="bi bi-person-plus me-1"></i>Register to Apply
                </a>
                <?php else: ?>
                <a href="<?= APP_URL ?>/applicant/vacancies" class="btn btn-sm btn-primary mt-2 w-100">
                    <i class="bi bi-send me-1"></i>Apply Now
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ── ANNOUNCEMENTS ─────────────────────────────────────────────────────── -->
<?php if (!empty($announcements)): ?>
<section class="py-5" style="background:#f4f7fc;">
    <div class="container">
        <h2 class="section-title mb-4 text-center">
            <i class="bi bi-bell-fill me-2 text-warning"></i>Announcements
        </h2>
        <div class="row g-3 justify-content-center">
        <?php foreach ($announcements as $a):
            $colors = ['general'=>'primary','emergency'=>'danger','job_opportunity'=>'success'];
            $icons  = ['general'=>'bi-info-circle','emergency'=>'bi-exclamation-triangle','job_opportunity'=>'bi-briefcase'];
            $color  = $colors[$a['type']] ?? 'secondary';
            $icon   = $icons[$a['type']] ?? 'bi-bell';
        ?>
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100 border-top border-<?= $color ?> border-3">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi <?= $icon ?> text-<?= $color ?> fs-5"></i>
                        <span class="badge bg-<?= $color ?>"><?= ucfirst($a['type']) ?></span>
                        <span class="text-muted small ms-auto"><?= formatDate($a['created_at']) ?></span>
                    </div>
                    <h6 class="fw-bold"><?= htmlspecialchars($a['title'], ENT_QUOTES, 'UTF-8') ?></h6>
                    <p class="text-muted small mb-0">
                        <?= htmlspecialchars(substr(strip_tags($a['content']), 0, 120), ENT_QUOTES, 'UTF-8') ?>...
                    </p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ── FOOTER ─────────────────────────────────────────────────────────────── -->
<footer class="landing-footer">
    <div class="container">
        <div class="row">
            <div class="col-md-5 mb-3">
                <h5 class="fw-bold text-white mb-2">
                    <i class="bi bi-briefcase-fill me-2"></i>ReliaWork2
                </h5>
                <p class="opacity-75 small">
                    Barangay Resource &amp; Labor Pool System<br>
                    SDG 8: Decent Work &amp; Economic Growth
                </p>
            </div>
            <div class="col-md-3 mb-3">
                <h6 class="text-white fw-bold mb-2">Quick Links</h6>
                <div class="d-flex flex-column gap-1">
                    <a href="#job-fairs">Job Fairs</a>
                    <a href="#vacancies">Browse Vacancies</a>
                    <a href="<?= APP_URL ?>/register">Register as Job Seeker</a>
                    <a href="<?= APP_URL ?>/login">Sign In</a>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <h6 class="text-white fw-bold mb-2">For Job Seekers</h6>
                <p class="opacity-75 small">
                    Attend the job fair with your resume and valid ID.<br>
                    Registration is <strong class="text-warning">FREE</strong> for all barangay residents.
                </p>
                <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="<?= APP_URL ?>/register" class="btn btn-warning btn-sm fw-semibold">
                    <i class="bi bi-person-plus me-1"></i>Register Now
                </a>
                <?php endif; ?>
            </div>
        </div>
        <hr style="border-color:rgba(255,255,255,.15);">
        <p class="text-center opacity-50 small mb-0">
            &copy; <?= date('Y') ?> ReliaWork2 — Barangay Employment Desk Officer System
        </p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Vacancy search filter
function filterVacancies(q) {
    q = q.toLowerCase().trim();
    document.querySelectorAll('.vacancy-item').forEach(item => {
        const pos  = item.querySelector('.vac-position')?.textContent.toLowerCase() || '';
        const comp = item.querySelector('.vac-company')?.textContent.toLowerCase() || '';
        item.style.display = (!q || pos.includes(q) || comp.includes(q)) ? '' : 'none';
    });
}

// Nav login flash on error
<?php if (!empty($loginError)): ?>
const navForm = document.getElementById('navLoginForm');
if (navForm) {
    const errDiv = document.createElement('div');
    errDiv.className = 'alert alert-danger alert-dismissible fade show mt-2';
    errDiv.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    navForm.closest('.container-fluid').after(errDiv);
}
<?php endif; ?>
</script>
</body>
</html>
