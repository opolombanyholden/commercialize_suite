/**
 * CommercialiZe Suite - Main JavaScript
 * Version: 1.0.0
 * Dependencies: Bootstrap 5.3+, jQuery 3.7+ (optional)
 */

(function() {
    'use strict';

    // ============================================
    // CONFIGURATION
    // ============================================
    const CZ = {
        config: {
            sidebarCollapsedKey: 'cz_sidebar_collapsed',
            currency: 'FCFA',
            locale: 'fr-FR',
            dateFormat: 'dd/MM/yyyy',
            apiBaseUrl: '/api/v1'
        },
        
        // ============================================
        // INITIALIZATION
        // ============================================
        init: function() {
            this.initSidebar();
            this.initTooltips();
            this.initPopovers();
            this.initAlerts();
            this.initForms();
            this.initTables();
            this.initModals();
            this.initConfirmDialogs();
            console.log('CommercialiZe Suite initialized');
        },

        // ============================================
        // SIDEBAR
        // ============================================
        initSidebar: function() {
            const sidebar = document.querySelector('.sidebar');
            const toggle = document.querySelector('.sidebar-toggle');
            const overlay = document.querySelector('.sidebar-overlay');
            
            if (!sidebar) return;
            
            // Restore collapsed state
            if (localStorage.getItem(this.config.sidebarCollapsedKey) === 'true') {
                document.body.classList.add('sidebar-collapsed');
            }
            
            // Toggle sidebar
            if (toggle) {
                toggle.addEventListener('click', () => {
                    if (window.innerWidth >= 992) {
                        document.body.classList.toggle('sidebar-collapsed');
                        localStorage.setItem(
                            this.config.sidebarCollapsedKey,
                            document.body.classList.contains('sidebar-collapsed')
                        );
                    } else {
                        sidebar.classList.toggle('show');
                    }
                });
            }
            
            // Close on overlay click (mobile)
            if (overlay) {
                overlay.addEventListener('click', () => {
                    sidebar.classList.remove('show');
                });
            }
            
            // Close on escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && sidebar.classList.contains('show')) {
                    sidebar.classList.remove('show');
                }
            });
        },

        // ============================================
        // TOOLTIPS & POPOVERS
        // ============================================
        initTooltips: function() {
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));
        },
        
        initPopovers: function() {
            const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
            popoverTriggerList.forEach(el => new bootstrap.Popover(el));
        },

        // ============================================
        // ALERTS AUTO-DISMISS
        // ============================================
        initAlerts: function() {
            const alerts = document.querySelectorAll('.alert-dismissible[data-auto-dismiss]');
            alerts.forEach(alert => {
                const delay = parseInt(alert.dataset.autoDismiss) || 5000;
                setTimeout(() => {
                    const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                    bsAlert.close();
                }, delay);
            });
        },

        // ============================================
        // FORMS
        // ============================================
        initForms: function() {
            // Auto-format currency inputs
            document.querySelectorAll('input[data-format="currency"]').forEach(input => {
                input.addEventListener('blur', function() {
                    const value = parseFloat(this.value.replace(/[^\d.-]/g, ''));
                    if (!isNaN(value)) {
                        this.value = CZ.formatNumber(value);
                    }
                });
                input.addEventListener('focus', function() {
                    this.value = this.value.replace(/[^\d.-]/g, '');
                });
            });
            
            // Prevent double submission
            document.querySelectorAll('form[data-prevent-double-submit]').forEach(form => {
                form.addEventListener('submit', function() {
                    const submitBtn = this.querySelector('[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Traitement...';
                    }
                });
            });
            
            // Auto-resize textareas
            document.querySelectorAll('textarea[data-auto-resize]').forEach(textarea => {
                textarea.style.overflow = 'hidden';
                const resize = () => {
                    textarea.style.height = 'auto';
                    textarea.style.height = textarea.scrollHeight + 'px';
                };
                textarea.addEventListener('input', resize);
                resize();
            });
        },

        // ============================================
        // TABLES
        // ============================================
        initTables: function() {
            // Select all checkbox
            document.querySelectorAll('[data-select-all]').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const target = this.dataset.selectAll;
                    document.querySelectorAll(target).forEach(cb => {
                        cb.checked = this.checked;
                    });
                });
            });
            
            // Row click to navigate
            document.querySelectorAll('tr[data-href]').forEach(row => {
                row.style.cursor = 'pointer';
                row.addEventListener('click', function(e) {
                    if (e.target.closest('a, button, input, .dropdown')) return;
                    window.location.href = this.dataset.href;
                });
            });
        },

        // ============================================
        // MODALS
        // ============================================
        initModals: function() {
            // Focus first input on modal show
            document.querySelectorAll('.modal').forEach(modal => {
                modal.addEventListener('shown.bs.modal', function() {
                    const firstInput = this.querySelector('input:not([type="hidden"]), textarea, select');
                    if (firstInput) firstInput.focus();
                });
            });
        },

        // ============================================
        // CONFIRM DIALOGS
        // ============================================
        initConfirmDialogs: function() {
            document.querySelectorAll('[data-confirm]').forEach(el => {
                el.addEventListener('click', function(e) {
                    const message = this.dataset.confirm || 'Êtes-vous sûr ?';
                    if (!confirm(message)) {
                        e.preventDefault();
                        e.stopPropagation();
                    }
                });
            });
        },

        // ============================================
        // UTILITY FUNCTIONS
        // ============================================
        formatNumber: function(num, decimals = 0) {
            return new Intl.NumberFormat(this.config.locale, {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            }).format(num);
        },
        
        formatCurrency: function(amount) {
            return this.formatNumber(amount) + ' ' + this.config.currency;
        },
        
        formatDate: function(date) {
            if (!(date instanceof Date)) {
                date = new Date(date);
            }
            return date.toLocaleDateString(this.config.locale);
        },
        
        debounce: function(func, wait = 300) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },
        
        // ============================================
        // AJAX HELPERS
        // ============================================
        ajax: {
            get: function(url, options = {}) {
                return CZ.ajax.request('GET', url, null, options);
            },
            
            post: function(url, data, options = {}) {
                return CZ.ajax.request('POST', url, data, options);
            },
            
            put: function(url, data, options = {}) {
                return CZ.ajax.request('PUT', url, data, options);
            },
            
            delete: function(url, options = {}) {
                return CZ.ajax.request('DELETE', url, null, options);
            },
            
            request: function(method, url, data, options = {}) {
                const config = {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    },
                    ...options
                };
                
                if (data && method !== 'GET') {
                    config.body = JSON.stringify(data);
                }
                
                return fetch(url, config)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    });
            }
        },

        // ============================================
        // NOTIFICATIONS
        // ============================================
        notify: {
            success: function(message) {
                CZ.notify.show(message, 'success');
            },
            
            error: function(message) {
                CZ.notify.show(message, 'danger');
            },
            
            warning: function(message) {
                CZ.notify.show(message, 'warning');
            },
            
            info: function(message) {
                CZ.notify.show(message, 'info');
            },
            
            show: function(message, type = 'info') {
                const container = document.getElementById('notification-container') || CZ.notify.createContainer();
                const id = 'notification-' + Date.now();
                
                const icons = {
                    success: 'check-circle',
                    danger: 'exclamation-circle',
                    warning: 'exclamation-triangle',
                    info: 'info-circle'
                };
                
                const alert = document.createElement('div');
                alert.id = id;
                alert.className = `alert alert-${type} alert-dismissible fade show`;
                alert.innerHTML = `
                    <i class="fas fa-${icons[type]} me-2"></i>${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                
                container.appendChild(alert);
                
                setTimeout(() => {
                    const el = document.getElementById(id);
                    if (el) {
                        const bsAlert = bootstrap.Alert.getOrCreateInstance(el);
                        bsAlert.close();
                    }
                }, 5000);
            },
            
            createContainer: function() {
                const container = document.createElement('div');
                container.id = 'notification-container';
                container.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
                document.body.appendChild(container);
                return container;
            }
        },

        // ============================================
        // LOADING OVERLAY
        // ============================================
        loading: {
            show: function(message = 'Chargement...') {
                if (document.getElementById('loading-overlay')) return;
                
                const overlay = document.createElement('div');
                overlay.id = 'loading-overlay';
                overlay.innerHTML = `
                    <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.9); display: flex; align-items: center; justify-content: center; z-index: 9999;">
                        <div style="text-align: center;">
                            <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
                            <p class="text-muted mb-0">${message}</p>
                        </div>
                    </div>
                `;
                document.body.appendChild(overlay);
            },
            
            hide: function() {
                const overlay = document.getElementById('loading-overlay');
                if (overlay) overlay.remove();
            }
        }
    };

    // ============================================
    // DOCUMENT ITEM MANAGEMENT (for invoices/quotes)
    // ============================================
    window.DocumentItems = {
        index: 0,
        
        init: function(containerId, templateId) {
            this.container = document.getElementById(containerId);
            this.template = document.getElementById(templateId);
            if (this.container && this.container.children.length === 0) {
                this.add();
            }
        },
        
        add: function(data = {}) {
            if (!this.template) return;
            
            const clone = this.template.content.cloneNode(true);
            const html = clone.querySelector('tr') || clone.querySelector('div');
            
            // Replace INDEX placeholder
            html.innerHTML = html.innerHTML.replace(/INDEX/g, this.index);
            html.dataset.index = this.index;
            
            // Fill data if provided
            if (data) {
                Object.keys(data).forEach(key => {
                    const input = html.querySelector(`[name*="[${key}]"]`);
                    if (input) input.value = data[key];
                });
            }
            
            this.container.appendChild(html);
            this.index++;
            this.attachListeners(html);
            this.calculateTotals();
        },
        
        remove: function(btn) {
            const row = btn.closest('tr') || btn.closest('[data-index]');
            if (this.container.children.length > 1) {
                row.remove();
                this.calculateTotals();
            } else {
                CZ.notify.warning('Au moins une ligne est requise');
            }
        },
        
        attachListeners: function(row) {
            const qtyInput = row.querySelector('[name*="[quantity]"]');
            const priceInput = row.querySelector('[name*="[unit_price]"]');
            
            [qtyInput, priceInput].forEach(input => {
                if (input) {
                    input.addEventListener('input', () => this.calculateRowTotal(row));
                }
            });
        },
        
        calculateRowTotal: function(row) {
            const qty = parseFloat(row.querySelector('[name*="[quantity]"]')?.value) || 0;
            const price = parseFloat(row.querySelector('[name*="[unit_price]"]')?.value) || 0;
            const total = qty * price;
            
            const totalInput = row.querySelector('[name*="[total]"]');
            const totalDisplay = row.querySelector('.row-total');
            
            if (totalInput) totalInput.value = total.toFixed(2);
            if (totalDisplay) totalDisplay.textContent = CZ.formatNumber(total);
            
            this.calculateTotals();
        },
        
        calculateTotals: function() {
            let subtotal = 0;
            
            this.container.querySelectorAll('[name*="[total]"]').forEach(input => {
                subtotal += parseFloat(input.value) || 0;
            });
            
            const subtotalEl = document.getElementById('subtotal');
            const subtotalInput = document.querySelector('[name="subtotal"]');
            
            if (subtotalEl) subtotalEl.textContent = CZ.formatNumber(subtotal);
            if (subtotalInput) subtotalInput.value = subtotal.toFixed(2);
            
            // Calculate taxes
            let totalTax = 0;
            document.querySelectorAll('.tax-checkbox:checked').forEach(cb => {
                const rate = parseFloat(cb.dataset.rate) || 0;
                const taxAmount = subtotal * (rate / 100);
                totalTax += taxAmount;
                
                const taxDisplay = document.getElementById('tax-' + cb.value);
                if (taxDisplay) taxDisplay.textContent = CZ.formatNumber(taxAmount);
            });
            
            const totalAmount = subtotal + totalTax;
            
            const taxTotalEl = document.getElementById('tax-total');
            const totalEl = document.getElementById('total-amount');
            const totalInput = document.querySelector('[name="total_amount"]');
            
            if (taxTotalEl) taxTotalEl.textContent = CZ.formatNumber(totalTax);
            if (totalEl) totalEl.textContent = CZ.formatNumber(totalAmount);
            if (totalInput) totalInput.value = totalAmount.toFixed(2);
        }
    };

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => CZ.init());
    } else {
        CZ.init();
    }

    // Expose globally
    window.CZ = CZ;

})();
