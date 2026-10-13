<?php
/**
 * ============================================================================
 * PATRICK GREY FOREX PORTFOLIO — ADMIN DASHBOARD FOOTER
 * ============================================================================
 * File: admin/includes/admin_footer.php
 */

declare(strict_types=1);

if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'admin_footer.php') {
    http_response_code(403);
    exit('Direct access is not permitted.');
}
?>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script>
    (function () {
        'use strict';

        // Client-side table search filter for admin data tables
        const searchInputs = document.querySelectorAll('[data-table-search]');
        searchInputs.forEach(function (input) {
            const targetSelector = input.getAttribute('data-table-search');
            const table = document.querySelector(targetSelector);
            if (!table) return;

            input.addEventListener('input', function () {
                const query = input.value.trim().toLowerCase();
                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(function (row) {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(query) ? '' : 'none';
                });
            });
        });

        // Confirmation dialog before deleting records
        const deleteForms = document.querySelectorAll('form[data-confirm]');
        deleteForms.forEach(function (form) {
            form.addEventListener('submit', function (e) {
                const msg = form.getAttribute('data-confirm') || 'Are you sure you want to permanently delete this record?';
                if (!window.confirm(msg)) {
                    e.preventDefault();
                }
            });
        });
    })();
    </script>
</body>
</html>
