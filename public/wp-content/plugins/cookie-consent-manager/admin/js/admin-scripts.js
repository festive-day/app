/**
 * Cookie Consent Manager - Admin JavaScript
 *
 * Handles AJAX CRUD operations and form validation
 *
 * @package Cookie_Consent_Manager
 */

(function($) {
    'use strict';

    var CCMAdmin = {
        currentTab: 'categories',
        currentPage: {
            categories: 1,
            cookies: 1,
            logs: 1
        },
        filters: {
            cookies: {},
            logs: {}
        },

        /**
         * Initialize admin interface
         */
        init: function() {
            this.setupTabs();
            this.setupCategories();
            this.setupCookies();
            this.setupLogs();
            this.setupSettings();
            this.setupModals();
        },

        /**
         * Setup tab navigation
         */
        setupTabs: function() {
            var self = this;
            var tab = this.getUrlParameter('tab') || 'categories';
            
            // Show initial tab
            this.showTab(tab);

            // Handle tab clicks
            $(document).on('click', '.ccm-nav-tabs .nav-tab', function(e) {
                e.preventDefault();
                var tabName = $(this).data('tab');
                self.showTab(tabName);
                self.updateUrl(tabName);
            });
        },

        /**
         * Show specific tab
         */
        showTab: function(tabName) {
            this.currentTab = tabName;
            
            // Update nav tabs
            $('.ccm-nav-tabs .nav-tab').removeClass('nav-tab-active');
            $('.ccm-nav-tabs .nav-tab[data-tab="' + tabName + '"]').addClass('nav-tab-active');
            
            // Show/hide tab content
            $('.ccm-tab-content').removeClass('active');
            $('.ccm-tab-content.ccm-tab-' + tabName).addClass('active');

            // Load tab data
            if (tabName === 'categories') {
                this.loadCategories();
            } else if (tabName === 'cookies') {
                this.loadCookies();
            } else if (tabName === 'logs') {
                this.loadLogs();
            }
        },

        /**
         * Update URL without reload
         */
        updateUrl: function(tab) {
            var url = new URL(window.location);
            url.searchParams.set('tab', tab);
            window.history.pushState({}, '', url);
        },

        /**
         * Get URL parameter
         */
        getUrlParameter: function(name) {
            var url = new URL(window.location);
            return url.searchParams.get(name);
        },

        /**
         * Setup categories tab
         */
        setupCategories: function() {
            var self = this;

            // Add category button
            $(document).on('click', '.ccm-btn-add-category', function() {
                self.openCategoryModal();
            });

            // Edit category button
            $(document).on('click', '.ccm-btn-edit-category', function() {
                var id = $(this).data('id');
                self.openCategoryModal(id);
            });

            // Delete category button
            $(document).on('click', '.ccm-btn-delete-category', function() {
                var id = $(this).data('id');
                self.deleteCategory(id);
            });

            // Category form submit
            $(document).on('submit', '#ccm-category-form', function(e) {
                e.preventDefault();
                self.saveCategory();
            });
        },

        /**
         * Load categories list
         */
        loadCategories: function() {
            var self = this;
            var $tbody = $('#ccm-categories-tbody');
            
            $tbody.html('<tr class="ccm-loading"><td colspan="6" class="ccm-loading-message">Loading categories...</td></tr>');

            $.ajax({
                url: ccmAdmin.ajaxUrl,
                type: 'GET',
                data: {
                    action: 'ccm_list_categories',
                    nonce: ccmAdmin.nonce
                },
                success: function(response) {
                    if (response.success && response.data && Array.isArray(response.data)) {
                        self.renderCategories(response.data);
                    } else {
                        $tbody.html('<tr><td colspan="6" class="ccm-empty-message">No categories found.</td></tr>');
                    }
                },
                error: function() {
                    $tbody.html('<tr><td colspan="6" class="ccm-empty-message">Error loading categories.</td></tr>');
                }
            });
        },

        /**
         * Render categories table
         */
        renderCategories: function(categories) {
            var $tbody = $('#ccm-categories-tbody');
            $tbody.empty();

            if (categories.length === 0) {
                $tbody.html('<tr><td colspan="6" class="ccm-empty-message">No categories found.</td></tr>');
                return;
            }

            $.each(categories, function(index, category) {
                var row = '<tr>' +
                    '<td class="column-name"><strong>' + self.escapeHtml(category.name) + '</strong></td>' +
                    '<td class="column-slug"><code>' + self.escapeHtml(category.slug) + '</code></td>' +
                    '<td class="column-description">' + (category.description || '—') + '</td>' +
                    '<td class="column-required">' + (category.is_required == 1 ? '<span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>' : '—') + '</td>' +
                    '<td class="column-cookies">' + (category.cookie_count || 0) + '</td>' +
                    '<td class="column-actions">' +
                        '<div class="ccm-btn-row-actions">' +
                            '<button type="button" class="button button-small ccm-btn-edit ccm-btn-edit-category" data-id="' + category.id + '">Edit</button>' +
                            (category.is_required != 1 ? '<button type="button" class="button button-small ccm-btn-delete ccm-btn-delete-category" data-id="' + category.id + '">Delete</button>' : '') +
                        '</div>' +
                    '</td>' +
                '</tr>';
                $tbody.append(row);
            });
        },

        /**
         * Open category modal
         */
        openCategoryModal: function(id) {
            var $modal = $('#ccm-category-modal');
            var $form = $('#ccm-category-form');
            var $title = $modal.find('.ccm-modal-title');

            if (id) {
                $title.text('Edit Category');
                $('#ccm-category-id').val(id);
                // Load category data and populate form
                this.loadCategoryData(id);
            } else {
                $title.text('Add Category');
                $form[0].reset();
                $('#ccm-category-id').val('');
                if (typeof tinyMCE !== 'undefined' && tinyMCE.get('ccm-category-description')) {
                    tinyMCE.get('ccm-category-description').setContent('');
                }
            }

            $modal.show();
            $('#ccm-modal-overlay').show();
        },

        /**
         * Load category data for editing
         */
        loadCategoryData: function(id) {
            var self = this;
            // This would load from the list or make an AJAX call
            // For now, we'll get it from the table row
        },

        /**
         * Save category
         */
        saveCategory: function() {
            var self = this;
            var $form = $('#ccm-category-form');
            var formData = $form.serialize();
            var id = $('#ccm-category-id').val();
            var action = id ? 'ccm_update_category' : 'ccm_create_category';

            // Get description from TinyMCE if available
            if (typeof tinyMCE !== 'undefined' && tinyMCE.get('ccm-category-description')) {
                formData += '&description=' + encodeURIComponent(tinyMCE.get('ccm-category-description').getContent());
            }

            formData += '&action=' + action + '&nonce=' + ccmAdmin.nonce;

            $.ajax({
                url: ccmAdmin.ajaxUrl,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        self.closeModal();
                        self.loadCategories();
                        self.showNotice('Category saved successfully.', 'success');
                    } else {
                        self.showNotice(response.data.message || 'Error saving category.', 'error');
                    }
                },
                error: function() {
                    self.showNotice('Error saving category.', 'error');
                }
            });
        },

        /**
         * Delete category
         */
        deleteCategory: function(id) {
            var self = this;
            
            if (!confirm('Are you sure you want to delete this category? All associated cookies will also be deleted.')) {
                return;
            }

            $.ajax({
                url: ccmAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ccm_delete_category',
                    id: id,
                    nonce: ccmAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        self.loadCategories();
                        self.showNotice('Category deleted successfully.', 'success');
                    } else {
                        self.showNotice(response.data.message || 'Error deleting category.', 'error');
                    }
                },
                error: function() {
                    self.showNotice('Error deleting category.', 'error');
                }
            });
        },

        /**
         * Setup cookies tab
         */
        setupCookies: function() {
            var self = this;

            // Add cookie button
            $(document).on('click', '.ccm-btn-add-cookie', function() {
                self.openCookieModal();
            });

            // Category filter
            $(document).on('change', '#ccm-filter-category', function() {
                self.filters.cookies.category_id = $(this).val();
                self.loadCookies();
            });

            // Cookie form submit
            $(document).on('submit', '#ccm-cookie-form', function(e) {
                e.preventDefault();
                self.saveCookie();
            });
        },

        /**
         * Load cookies list
         */
        loadCookies: function() {
            var self = this;
            var $tbody = $('#ccm-cookies-tbody');
            
            $tbody.html('<tr class="ccm-loading"><td colspan="6" class="ccm-loading-message">Loading cookies...</td></tr>');

            var data = {
                action: 'ccm_list_cookies',
                nonce: ccmAdmin.nonce,
                page: this.currentPage.cookies
            };

            if (this.filters.cookies.category_id) {
                data.category_id = this.filters.cookies.category_id;
            }

            $.ajax({
                url: ccmAdmin.ajaxUrl,
                type: 'GET',
                data: data,
                success: function(response) {
                    if (response.success && response.data && Array.isArray(response.data)) {
                        self.renderCookies(response.data);
                        // Update pagination if provided
                        if (response.pagination) {
                            // TODO: Implement pagination UI
                        }
                    } else {
                        $tbody.html('<tr><td colspan="6" class="ccm-empty-message">No cookies found.</td></tr>');
                    }
                },
                error: function() {
                    $tbody.html('<tr><td colspan="6" class="ccm-empty-message">Error loading cookies.</td></tr>');
                }
            });
        },

        /**
         * Render cookies table
         */
        renderCookies: function(cookies) {
            var $tbody = $('#ccm-cookies-tbody');
            $tbody.empty();

            if (cookies.length === 0) {
                $tbody.html('<tr><td colspan="6" class="ccm-empty-message">No cookies found.</td></tr>');
                return;
            }

            var self = this;
            $.each(cookies, function(index, cookie) {
                var row = '<tr>' +
                    '<td class="column-name"><strong>' + self.escapeHtml(cookie.name) + '</strong></td>' +
                    '<td class="column-category">' + self.escapeHtml(cookie.category_name || '—') + '</td>' +
                    '<td class="column-provider">' + self.escapeHtml(cookie.provider || '—') + '</td>' +
                    '<td class="column-purpose">' + (cookie.purpose ? self.escapeHtml(cookie.purpose.substring(0, 100)) + (cookie.purpose.length > 100 ? '...' : '') : '—') + '</td>' +
                    '<td class="column-expiration">' + self.escapeHtml(cookie.expiration || '—') + '</td>' +
                    '<td class="column-actions">' +
                        '<div class="ccm-btn-row-actions">' +
                            '<button type="button" class="button button-small ccm-btn-edit ccm-btn-edit-cookie" data-id="' + cookie.id + '">Edit</button>' +
                            '<button type="button" class="button button-small ccm-btn-delete ccm-btn-delete-cookie" data-id="' + cookie.id + '">Delete</button>' +
                        '</div>' +
                    '</td>' +
                '</tr>';
                $tbody.append(row);
            });
        },

        /**
         * Open cookie modal
         */
        openCookieModal: function(id) {
            var $modal = $('#ccm-cookie-modal');
            var $form = $('#ccm-cookie-form');
            var $title = $modal.find('.ccm-modal-title');

            if (id) {
                $title.text('Edit Cookie');
                $('#ccm-cookie-id').val(id);
            } else {
                $title.text('Add Cookie');
                $form[0].reset();
                $('#ccm-cookie-id').val('');
            }

            // Load categories for dropdown
            this.loadCategoriesForSelect();

            $modal.show();
            $('#ccm-modal-overlay').show();
        },

        /**
         * Load categories for select dropdown
         */
        loadCategoriesForSelect: function() {
            var self = this;
            var $select = $('#ccm-cookie-category, #ccm-filter-category');
            
            $.ajax({
                url: ccmAdmin.ajaxUrl,
                type: 'GET',
                data: {
                    action: 'ccm_list_categories',
                    nonce: ccmAdmin.nonce
                },
                success: function(response) {
                    if (response.success && response.data && Array.isArray(response.data)) {
                        var options = '<option value="">Select a category</option>';
                        $.each(response.data, function(index, category) {
                            options += '<option value="' + category.id + '">' + self.escapeHtml(category.name) + '</option>';
                        });
                        $select.html(options);
                    }
                }
            });
        },

        /**
         * Save cookie
         */
        saveCookie: function() {
            var self = this;
            var $form = $('#ccm-cookie-form');
            var formData = $form.serialize();
            var id = $('#ccm-cookie-id').val();
            var action = id ? 'ccm_update_cookie' : 'ccm_create_cookie';

            formData += '&action=' + action + '&nonce=' + ccmAdmin.nonce;

            $.ajax({
                url: ccmAdmin.ajaxUrl,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        self.closeModal();
                        self.loadCookies();
                        self.showNotice('Cookie saved successfully.', 'success');
                    } else {
                        self.showNotice(response.data.message || 'Error saving cookie.', 'error');
                    }
                },
                error: function() {
                    self.showNotice('Error saving cookie.', 'error');
                }
            });
        },

        /**
         * Setup logs tab
         */
        setupLogs: function() {
            var self = this;

            // Apply filters
            $(document).on('click', '.ccm-btn-apply-filters', function() {
                self.filters.logs.start_date = $('#ccm-filter-start-date').val();
                self.filters.logs.end_date = $('#ccm-filter-end-date').val();
                self.filters.logs.event_type = $('#ccm-filter-event-type').val();
                self.currentPage.logs = 1;
                self.loadLogs();
            });

            // Clear filters
            $(document).on('click', '.ccm-btn-clear-filters', function() {
                $('#ccm-filter-start-date').val('');
                $('#ccm-filter-end-date').val('');
                $('#ccm-filter-event-type').val('');
                self.filters.logs = {};
                self.currentPage.logs = 1;
                self.loadLogs();
            });

            // Export logs
            $(document).on('click', '.ccm-btn-export-logs', function() {
                self.exportLogs();
            });
        },

        /**
         * Load logs list
         */
        loadLogs: function() {
            var self = this;
            var $tbody = $('#ccm-logs-tbody');
            
            $tbody.html('<tr class="ccm-loading"><td colspan="7" class="ccm-loading-message">Loading logs...</td></tr>');

            var data = {
                action: 'ccm_view_logs',
                nonce: ccmAdmin.nonce,
                page: this.currentPage.logs,
                per_page: 50
            };

            if (this.filters.logs.start_date) {
                data.start_date = this.filters.logs.start_date;
            }
            if (this.filters.logs.end_date) {
                data.end_date = this.filters.logs.end_date;
            }
            if (this.filters.logs.event_type) {
                data.event_type = this.filters.logs.event_type;
            }

            $.ajax({
                url: ccmAdmin.ajaxUrl,
                type: 'GET',
                data: data,
                success: function(response) {
                    if (response.success && response.data && Array.isArray(response.data)) {
                        self.renderLogs(response.data);
                        // Update pagination if provided
                        if (response.pagination) {
                            // TODO: Implement pagination UI
                        }
                    } else {
                        $tbody.html('<tr><td colspan="7" class="ccm-empty-message">No logs found.</td></tr>');
                    }
                },
                error: function() {
                    $tbody.html('<tr><td colspan="7" class="ccm-empty-message">Error loading logs.</td></tr>');
                }
            });
        },

        /**
         * Render logs table
         */
        renderLogs: function(logs) {
            var $tbody = $('#ccm-logs-tbody');
            $tbody.empty();

            if (logs.length === 0) {
                $tbody.html('<tr><td colspan="7" class="ccm-empty-message">No logs found.</td></tr>');
                return;
            }

            var self = this;
            $.each(logs, function(index, log) {
                var accepted = log.accepted_categories ? JSON.parse(log.accepted_categories).join(', ') : '—';
                var rejected = log.rejected_categories ? JSON.parse(log.rejected_categories).join(', ') : '—';
                
                var row = '<tr>' +
                    '<td class="column-id">' + log.id + '</td>' +
                    '<td class="column-timestamp">' + self.escapeHtml(log.event_timestamp) + '</td>' +
                    '<td class="column-event-type"><code>' + self.escapeHtml(log.event_type) + '</code></td>' +
                    '<td class="column-accepted">' + self.escapeHtml(accepted) + '</td>' +
                    '<td class="column-rejected">' + self.escapeHtml(rejected) + '</td>' +
                    '<td class="column-version">' + self.escapeHtml(log.consent_version || '—') + '</td>' +
                    '<td class="column-ip">' + self.escapeHtml(log.ip_address || '—') + '</td>' +
                '</tr>';
                $tbody.append(row);
            });
        },

        /**
         * Export logs
         */
        exportLogs: function() {
            var url = ccmAdmin.ajaxUrl + '?action=ccm_export_logs&nonce=' + ccmAdmin.nonce;
            
            if (this.filters.logs.start_date) {
                url += '&start_date=' + encodeURIComponent(this.filters.logs.start_date);
            }
            if (this.filters.logs.end_date) {
                url += '&end_date=' + encodeURIComponent(this.filters.logs.end_date);
            }

            window.location.href = url;
        },

        /**
         * Setup settings tab
         */
        setupSettings: function() {
            var self = this;

            $(document).on('submit', '#ccm-settings-form', function(e) {
                e.preventDefault();
                self.saveSettings();
            });
        },

        /**
         * Save settings
         */
        saveSettings: function() {
            var self = this;
            var $form = $('#ccm-settings-form');
            var formData = $form.serialize();

            // Get banner message from TinyMCE if available
            if (typeof tinyMCE !== 'undefined' && tinyMCE.get('ccm-banner-message')) {
                formData += '&banner_message=' + encodeURIComponent(tinyMCE.get('ccm-banner-message').getContent());
            }

            formData += '&action=ccm_save_settings&nonce=' + ccmAdmin.nonce;

            $.ajax({
                url: ccmAdmin.ajaxUrl,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        self.showNotice('Settings saved successfully.', 'success', '#ccm-settings-save-message');
                    } else {
                        self.showNotice(response.data.message || 'Error saving settings.', 'error', '#ccm-settings-save-message');
                    }
                },
                error: function() {
                    self.showNotice('Error saving settings.', 'error', '#ccm-settings-save-message');
                }
            });
        },

        /**
         * Setup modals
         */
        setupModals: function() {
            var self = this;

            // Close modal buttons
            $(document).on('click', '.ccm-modal-close, .ccm-modal-cancel, .ccm-modal-overlay', function() {
                self.closeModal();
            });

            // Prevent modal close when clicking inside
            $(document).on('click', '.ccm-modal-content', function(e) {
                e.stopPropagation();
            });
        },

        /**
         * Close modal
         */
        closeModal: function() {
            $('.ccm-modal').hide();
            $('#ccm-modal-overlay').hide();
        },

        /**
         * Show notice message
         */
        showNotice: function(message, type, selector) {
            selector = selector || '.ccm-admin-wrapper';
            type = type || 'success';
            
            var $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + this.escapeHtml(message) + '</p></div>');
            $(selector).prepend($notice);
            
            setTimeout(function() {
                $notice.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        },

        /**
         * Escape HTML
         */
        escapeHtml: function(text) {
            var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text ? text.replace(/[&<>"']/g, function(m) { return map[m]; }) : '';
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        CCMAdmin.init();
    });

    // Make CCMAdmin available globally for debugging
    window.CCMAdmin = CCMAdmin;

})(jQuery);
