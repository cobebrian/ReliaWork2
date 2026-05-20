<?php ob_start(); ?>
<h5 class="fw-bold mb-1 text-center">Create Account</h5>
<p class="text-muted text-center small mb-4">Register to access the job fair system</p>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<form method="POST" action="<?= APP_URL ?>/register" novalidate>
    <?= csrfField() ?>

    <!-- Last Name -->
    <div class="mb-3">
        <label class="form-label fw-semibold">
            Last Name <span class="text-danger">*</span>
        </label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input type="text" class="form-control" name="lastname"
                   placeholder="dela Cruz" required
                   value="<?= htmlspecialchars($old['lastname'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
    </div>

    <!-- First Name -->
    <div class="mb-3">
        <label class="form-label fw-semibold">
            First Name <span class="text-danger">*</span>
        </label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input type="text" class="form-control" name="firstname"
                   placeholder="Juan" required
                   value="<?= htmlspecialchars($old['firstname'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
    </div>

    <!-- Middle Name -->
    <div class="mb-3">
        <label class="form-label fw-semibold">
            Middle Name <span class="text-muted small fw-normal">(optional)</span>
        </label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person-dash"></i></span>
            <input type="text" class="form-control" name="middlename"
                   placeholder="Santos"
                   value="<?= htmlspecialchars($old['middlename'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
    </div>

    <!-- Email -->
    <div class="mb-3">
        <label class="form-label fw-semibold">
            Email Address <span class="text-danger">*</span>
        </label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" class="form-control" name="email"
                   placeholder="you@example.com" required autocomplete="email"
                   value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
    </div>

    <!-- Password -->
    <div class="mb-3">
        <label class="form-label fw-semibold">
            Password <span class="text-danger">*</span>
        </label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control" id="password" name="password"
                   placeholder="••••••••" required autocomplete="new-password">
            <button class="btn btn-outline-secondary" type="button" id="togglePw" tabindex="-1">
                <i class="bi bi-eye" id="togglePwIcon"></i>
            </button>
        </div>
        <!-- Password requirements -->
        <ul class="list-unstyled small ps-1 mt-2 mb-0">
            <li id="req-len"   class="text-muted"><i class="bi bi-circle me-1"></i>At least 8 characters</li>
            <li id="req-upper" class="text-muted"><i class="bi bi-circle me-1"></i>One uppercase letter (A–Z)</li>
            <li id="req-num"   class="text-muted"><i class="bi bi-circle me-1"></i>One number (0–9)</li>
            <li id="req-spec"  class="text-muted"><i class="bi bi-circle me-1"></i>One special character (!@#$...)</li>
        </ul>
    </div>

    <!-- Confirm Password -->
    <div class="mb-4">
        <label class="form-label fw-semibold">
            Confirm Password <span class="text-danger">*</span>
        </label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
            <input type="password" class="form-control" id="confirm_password"
                   name="confirm_password" placeholder="••••••••" required autocomplete="new-password">
        </div>
        <div id="matchMsg" class="small mt-1 d-none"></div>
    </div>

    <button type="submit" class="btn btn-success w-100 py-2 fw-semibold">
        <i class="bi bi-person-plus me-2"></i>Create Account
    </button>
</form>

<hr class="my-4">
<p class="text-center text-muted small mb-0">
    Already have an account?
    <a href="<?= APP_URL ?>/login" class="fw-semibold text-decoration-none">Sign in</a>
</p>

<script>
// Toggle password visibility
document.getElementById('togglePw')?.addEventListener('click', function () {
    const pw   = document.getElementById('password');
    const icon = document.getElementById('togglePwIcon');
    pw.type    = pw.type === 'password' ? 'text' : 'password';
    icon.className = pw.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
});

// Live password requirements
document.getElementById('password')?.addEventListener('input', function () {
    const val    = this.value;
    const checks = {
        'req-len':   val.length >= 8,
        'req-upper': /[A-Z]/.test(val),
        'req-num':   /[0-9]/.test(val),
        'req-spec':  /[^A-Za-z0-9]/.test(val),
    };
    for (const [id, ok] of Object.entries(checks)) {
        const el = document.getElementById(id);
        if (!el) continue;
        el.className = ok ? 'text-success' : 'text-muted';
        el.querySelector('i').className = ok
            ? 'bi bi-check-circle-fill me-1'
            : 'bi bi-circle me-1';
    }
    checkMatch();
});

// Confirm password match indicator
document.getElementById('confirm_password')?.addEventListener('input', checkMatch);
function checkMatch() {
    const pw  = document.getElementById('password').value;
    const cpw = document.getElementById('confirm_password').value;
    const msg = document.getElementById('matchMsg');
    if (!cpw) { msg.classList.add('d-none'); return; }
    msg.classList.remove('d-none');
    if (pw === cpw) {
        msg.className = 'small mt-1 text-success';
        msg.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>Passwords match';
    } else {
        msg.className = 'small mt-1 text-danger';
        msg.innerHTML = '<i class="bi bi-x-circle-fill me-1"></i>Passwords do not match';
    }
}
</script>
<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/auth.php';
