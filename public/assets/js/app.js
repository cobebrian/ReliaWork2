/**
 * ReliaWork2 — Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function () {

    // ── Sidebar Toggle (mobile) ──────────────────────────────────────────────
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('show');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function (e) {
            if (window.innerWidth < 992 &&
                sidebar.classList.contains('show') &&
                !sidebar.contains(e.target) &&
                e.target !== sidebarToggle) {
                sidebar.classList.remove('show');
            }
        });
    }

    // ── Flash Message Auto-Dismiss ───────────────────────────────────────────
    const flashMessages = document.querySelectorAll('.flash-message');
    flashMessages.forEach(function (msg) {
        setTimeout(function () {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(msg);
            if (bsAlert) {
                bsAlert.close();
            }
        }, 5000); // Auto-dismiss after 5 seconds
    });

    // ── Confirm Dialogs ──────────────────────────────────────────────────────
    // Already handled inline with onclick="return confirm(...)"
    // This adds a generic handler for data-confirm attributes
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            const msg = el.getAttribute('data-confirm') || 'Are you sure?';
            if (!confirm(msg)) {
                e.preventDefault();
                return false;
            }
        });
    });

    // ── AJAX Date Availability Check ─────────────────────────────────────────
    // Handled inline in barangay_captain/create_request.php
    // This is a fallback/global handler if needed

    // ── Form Validation Feedback ─────────────────────────────────────────────
    const forms = document.querySelectorAll('form[novalidate]');
    forms.forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // ── Tooltip Initialization ────────────────────────────────────────────────
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipTriggerList.forEach(function (el) {
        new bootstrap.Tooltip(el);
    });

    // ── Active Sidebar Link Highlight ─────────────────────────────────────────
    const currentPath = window.location.pathname;
    document.querySelectorAll('.sidebar-nav-link').forEach(function (link) {
        const href = link.getAttribute('href');
        if (href && currentPath.includes(new URL(href, window.location.origin).pathname)) {
            link.classList.add('active');
        }
    });

    // ── Print Button ──────────────────────────────────────────────────────────
    document.querySelectorAll('[data-action="print"]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            window.print();
        });
    });

    // ── Number Input Sanitization ─────────────────────────────────────────────
    document.querySelectorAll('input[type="number"]').forEach(function (input) {
        input.addEventListener('input', function () {
            const min = parseInt(this.getAttribute('min') || '0');
            const val = parseInt(this.value);
            if (!isNaN(val) && val < min) {
                this.value = min;
            }
        });
    });

});
