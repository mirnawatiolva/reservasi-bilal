// Admin Dashboard Scripts

// Sidebar Toggle
document.addEventListener('DOMContentLoaded', function() {
    const sidebarCollapse = document.getElementById('sidebarCollapse');
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('content');

    function applyDesktopSidebarState(isCollapsed) {
        sidebar.classList.toggle('collapsed', isCollapsed);
        content.classList.toggle('expanded', isCollapsed);
        document.body.classList.toggle('sidebar-collapsed', isCollapsed);

        // Hard-set style to avoid being overridden by other CSS rules.
        sidebar.style.width = isCollapsed ? '88px' : '';
        content.style.marginLeft = isCollapsed ? '88px' : '';
    }

    if (sidebarCollapse && sidebar && content) {
        sidebarCollapse.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                sidebar.classList.toggle('show');
                content.classList.toggle('sidebar-show');
                return;
            }

            const isCollapsed = !sidebar.classList.contains('collapsed');
            applyDesktopSidebarState(isCollapsed);
        });
    }

    // Close sidebar when a link is clicked on mobile
    const sidebarLinks = document.querySelectorAll('.sidebar a');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                sidebar.classList.remove('show');
                content.classList.remove('sidebar-show');
            }
        });
    });
});

// Set active menu item
function setActiveMenu(href) {
    const links = document.querySelectorAll('.sidebar a');
    links.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === href) {
            link.classList.add('active');
        }
    });
}

// Auto set active menu based on current page
document.addEventListener('DOMContentLoaded', function() {
    const currentPage = window.location.pathname.split('/').pop() || 'index.php';
    setActiveMenu(currentPage);

    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            if (sidebar && content) {
                sidebar.classList.remove('show');
                content.classList.remove('sidebar-show');
            }
            return;
        }

        if (sidebar && content) {
            applyDesktopSidebarState(false);
            sidebar.style.width = '';
            content.style.marginLeft = '';
        }
    });
});

// Logout function
function logout() {
    if (confirm('Apakah Anda yakin ingin logout?')) {
        alert('Anda telah logout. Redirect ke halaman utama...');
        window.location.href = '../index.php';
    }
}

// Delete confirmation
function confirmDelete(name) {
    return confirm(`Apakah Anda yakin ingin menghapus ${name}?`);
}

// Success message
function showSuccess(message) {
    const alert = document.createElement('div');
    alert.className = 'alert alert-success alert-dismissible fade show';
    alert.setAttribute('role', 'alert');
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    document.querySelector('.container-fluid').insertBefore(alert, document.querySelector('.container-fluid').firstChild);
    setTimeout(() => alert.remove(), 3000);
}

// Error message
function showError(message) {
    const alert = document.createElement('div');
    alert.className = 'alert alert-danger alert-dismissible fade show';
    alert.setAttribute('role', 'alert');
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    document.querySelector('.container-fluid').insertBefore(alert, document.querySelector('.container-fluid').firstChild);
}

// Export table to CSV
function exportToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) return;

    let csv = [];
    const rows = table.querySelectorAll('tr');

    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const csvRow = [];
        cols.forEach(col => {
            csvRow.push('"' + col.innerText.replace(/"/g, '""') + '"');
        });
        csv.push(csvRow.join(','));
    });

    const csvContent = csv.join('\n');
    const link = document.createElement('a');
    link.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csvContent);
    link.download = filename + '.csv';
    link.click();
}

// Print table
function printTable(tableId) {
    const table = document.getElementById(tableId);
    if (!table) return;

    const printWindow = window.open('', '', 'height=500,width=1000');
    printWindow.document.write('<html><head><title>Print</title>');
    printWindow.document.write('<link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">');
    printWindow.document.write('</head><body>');
    printWindow.document.write(table.outerHTML);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
}

// Form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;

    const inputs = form.querySelectorAll('[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });

    return isValid;
}

// Real-time table search
function setupTableSearch(searchInputId, tableId) {
    const searchInput = document.getElementById(searchInputId);
    const table = document.getElementById(tableId);

    if (!searchInput || !table) return;

    searchInput.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = table.querySelectorAll('tbody tr');

        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
}

// Chart color scheme
const chartColors = {
    primary: '#007bff',
    secondary: '#6c757d',
    success: '#28a745',
    danger: '#dc3545',
    warning: '#ffc107',
    info: '#17a2b8',
    light: '#f8f9fa',
    dark: '#343a40'
};

// Format currency
function formatCurrency(value) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
}

// Format date
function formatDate(date) {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}
