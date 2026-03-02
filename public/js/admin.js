/**
 * CommercialiZe Suite - Admin JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // ========== Sidebar Toggle ==========
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarClose = document.getElementById('sidebarClose');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.add('show');
            sidebarOverlay.classList.add('show');
        });
    }
    
    if (sidebarClose) {
        sidebarClose.addEventListener('click', function() {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        });
    }
    
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        });
    }
    
    // ========== Sidebar Submenu Toggle ==========
    document.querySelectorAll('.sidebar-item.has-submenu > .sidebar-link').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const parent = this.parentElement;
            
            // Close other submenus
            document.querySelectorAll('.sidebar-item.has-submenu.open').forEach(function(item) {
                if (item !== parent) {
                    item.classList.remove('open');
                }
            });
            
            parent.classList.toggle('open');
        });
    });
    
    // ========== Tooltips ==========
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipTriggerList.forEach(function(tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // ========== Confirm Delete ==========
    window.confirmDelete = function(formId, message) {
        message = message || 'Êtes-vous sûr de vouloir supprimer cet élément ?';
        if (confirm(message)) {
            document.getElementById(formId).submit();
        }
    };
    
    // ========== Format Numbers ==========
    window.formatNumber = function(num, decimals = 0) {
        return new Intl.NumberFormat('fr-FR', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        }).format(num);
    };
    
    // ========== Parse Number from Formatted String ==========
    window.parseFormattedNumber = function(str) {
        return parseFloat(str.replace(/\s/g, '').replace(',', '.')) || 0;
    };
    
    // ========== Auto-dismiss Alerts ==========
    document.querySelectorAll('.alert-dismissible').forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = bootstrap.Alert.getInstance(alert);
            if (bsAlert) {
                bsAlert.close();
            } else {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 150);
            }
        }, 5000);
    });
    
    // ========== File Input Preview ==========
    document.querySelectorAll('input[type="file"][data-preview]').forEach(function(input) {
        input.addEventListener('change', function() {
            const preview = document.getElementById(this.dataset.preview);
            if (preview && this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (preview.tagName === 'IMG') {
                        preview.src = e.target.result;
                    } else {
                        preview.innerHTML = '<img src="' + e.target.result + '" class="img-fluid">';
                    }
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
    
    // ========== Dynamic Select (AJAX) ==========
    document.querySelectorAll('select[data-ajax-url]').forEach(function(select) {
        select.addEventListener('change', function() {
            const targetId = this.dataset.target;
            const target = document.getElementById(targetId);
            const url = this.dataset.ajaxUrl + '?id=' + this.value;
            
            if (target && this.value) {
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        target.innerHTML = '<option value="">Sélectionner...</option>';
                        data.forEach(item => {
                            const option = document.createElement('option');
                            option.value = item.id;
                            option.textContent = item.name;
                            target.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error:', error));
            }
        });
    });
    
    // ========== Print Button ==========
    document.querySelectorAll('[data-print]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            window.print();
        });
    });
    
    // ========== Copy to Clipboard ==========
    document.querySelectorAll('[data-copy]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const text = this.dataset.copy;
            navigator.clipboard.writeText(text).then(() => {
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-check"></i> Copié !';
                setTimeout(() => {
                    this.innerHTML = originalText;
                }, 2000);
            });
        });
    });
    
    // ========== Date Range Validation ==========
    const fromDate = document.querySelector('input[name="from"]');
    const toDate = document.querySelector('input[name="to"]');
    
    if (fromDate && toDate) {
        fromDate.addEventListener('change', function() {
            toDate.min = this.value;
        });
        toDate.addEventListener('change', function() {
            fromDate.max = this.value;
        });
    }
    
    // ========== Table Select All ==========
    document.querySelectorAll('.select-all').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const table = this.closest('table');
            table.querySelectorAll('tbody input[type="checkbox"]').forEach(cb => {
                cb.checked = this.checked;
            });
        });
    });
    
    // ========== Loading Button State ==========
    document.querySelectorAll('form').forEach(function(form) {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.classList.contains('no-loading')) {
                submitBtn.disabled = true;
                const originalContent = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Chargement...';
                submitBtn.dataset.originalContent = originalContent;
            }
        });
    });
    
    // ========== Keyboard Shortcuts ==========
    document.addEventListener('keydown', function(e) {
        // Ctrl + S to save
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            const form = document.querySelector('form');
            if (form) {
                form.submit();
            }
        }
        
        // Escape to close modals
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal.show').forEach(modal => {
                bootstrap.Modal.getInstance(modal)?.hide();
            });
        }
    });
    
    // ========== Initialize Select2 if available ==========
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({
            theme: 'bootstrap-5',
            placeholder: 'Sélectionner...',
            allowClear: true
        });
    }
    
    console.log('CommercialiZe Suite initialized successfully!');
});

// ========== AJAX Helper Functions ==========
const API = {
    get: async function(url) {
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        return response.json();
    },
    
    post: async function(url, data) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        });
        return response.json();
    },
    
    delete: async function(url) {
        const response = await fetch(url, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        return response.json();
    }
};
