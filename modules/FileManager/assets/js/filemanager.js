/**
 * FileManager Frontend JavaScript
 *
 * Handles file upload, download, edit, and delete operations
 *
 * @package WPPluginBoilerplate
 * @since 1.1.0
 */

(function ($) {
    'use strict';

    const FileManager = {
        /**
         * Initialize
         */
        init() {
            this.bindEvents();
            this.loadFiles();
        },

        /**
         * Bind event listeners
         */
        bindEvents() {
            // Upload toggle
            $(document).on('click', '.wpbp-filemanager__upload-toggle', function (e) {
                e.preventDefault();
                $(this).siblings('.wpbp-filemanager__upload-form').slideToggle();
            });

            // Upload cancel
            $(document).on('click', '.wpbp-filemanager__upload-cancel', function (e) {
                e.preventDefault();
                $(this).closest('.wpbp-filemanager__upload-form').slideUp();
                $('#wpbp-file-upload-form')[0].reset();
            });

            // File upload
            $(document).on('submit', '#wpbp-file-upload-form', (e) => {
                e.preventDefault();
                this.handleUpload(e.currentTarget);
            });

            // Download file
            $(document).on('click', '.wpbp-file-download', (e) => {
                e.preventDefault();
                this.handleDownload($(e.currentTarget).data('file-id'));
            });

            // Edit file
            $(document).on('click', '.wpbp-file-edit', (e) => {
                e.preventDefault();
                this.showEditModal($(e.currentTarget).data('file-id'));
            });

            // Delete file
            $(document).on('click', '.wpbp-file-delete', (e) => {
                e.preventDefault();
                this.handleDelete($(e.currentTarget).data('file-id'));
            });

            // Modal close
            $(document).on('click', '.wpbp-modal__close, .wpbp-modal__cancel, .wpbp-modal__overlay', function () {
                $(this).closest('.wpbp-modal').remove();
            });

            // Edit form submit
            $(document).on('submit', '.wpbp-filemanager-edit-form', (e) => {
                e.preventDefault();
                this.handleEdit(e.currentTarget);
            });

            // Keyboard shortcuts
            $(document).on('keydown', (e) => {
                if (e.key === 'Escape') {
                    $('.wpbp-modal').remove();
                }
            });
        },

        /**
         * Load files for current area
         */
        loadFiles() {
            const $container = $('.wpbp-filemanager');
            const area = $container.data('area');

            $.ajax({
                url: wpbpFileManager.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpbp_list_files',
                    nonce: wpbpFileManager.nonce,
                    area: area,
                },
                success: (response) => {
                    if (response.success) {
                        this.renderFiles(response.data.files);
                    } else {
                        this.showError(response.data || 'Error loading files');
                    }
                },
                error: (jqXHR, textStatus, errorThrown) => {
                    console.error('AJAX error:', textStatus, errorThrown);
                    this.showError('Error loading files. Please try again.');
                },
            });
        },

        /**
         * Render files list
         */
        renderFiles(files) {
            const $container = $('.wpbp-filemanager__list');
            const $loading = $container.find('.wpbp-filemanager__loading');
            const $empty = $container.find('.wpbp-filemanager__empty');
            const $table = $container.find('.wpbp-filemanager__table');
            const $tbody = $table.find('tbody');

            $loading.hide();

            if (!files || files.length === 0) {
                $empty.show();
                $table.hide();
                return;
            }

            $empty.hide();
            $table.show();
            $tbody.empty();

            files.forEach((file) => {
                const row = this.createFileRow(file);
                $tbody.append(row);
            });
        },

        /**
         * Create file row HTML
         */
        createFileRow(file) {
            const iconClass = this.getFileIconClass(file.type);
            const showSize = $('.wpbp-filemanager__table th:contains("Size")').length > 0;
            const showDate = $('.wpbp-filemanager__table th:contains("Date")').length > 0;
            const showAuthor = $('.wpbp-filemanager__table th:contains("Author")').length > 0;

            let html = `
                <tr data-file-id="${file.id}">
                    <td>
                        <span class="dashicons ${iconClass}"></span>
                        ${this.escapeHtml(file.title)}
                    </td>
                    <td>${this.escapeHtml(file.type.toUpperCase())}</td>
            `;

            if (showSize) {
                html += `<td>${this.escapeHtml(file.size)}</td>`;
            }

            if (showDate) {
                html += `<td>${this.escapeHtml(file.date)}</td>`;
            }

            if (showAuthor) {
                html += `<td>${this.escapeHtml(file.author)}</td>`;
            }

            html += '<td class="wpbp-filemanager__actions">';

            // Download button (always visible)
            html += `
                <button class="wpbp-file-download button button-small"
                        data-file-id="${file.id}"
                        title="${wpbpFileManager.strings.download || 'Download'}">
                    <span class="dashicons dashicons-download"></span>
                </button>
            `;

            // Edit button
            if (file.can_edit && wpbpFileManager.canEdit) {
                html += `
                    <button class="wpbp-file-edit button button-small"
                            data-file-id="${file.id}"
                            title="${wpbpFileManager.strings.edit || 'Edit'}">
                        <span class="dashicons dashicons-edit"></span>
                    </button>
                `;
            }

            // Delete button
            if (file.can_delete && wpbpFileManager.canDelete) {
                html += `
                    <button class="wpbp-file-delete button button-small wpbp-button-danger"
                            data-file-id="${file.id}"
                            title="${wpbpFileManager.strings.delete || 'Delete'}">
                        <span class="dashicons dashicons-trash"></span>
                    </button>
                `;
            }

            html += '</td></tr>';

            return html;
        },

        /**
         * Get Dashicons class for file type
         */
        getFileIconClass(ext) {
            const iconMap = {
                pdf: 'dashicons-pdf',
                doc: 'dashicons-media-document',
                docx: 'dashicons-media-document',
                xls: 'dashicons-media-spreadsheet',
                xlsx: 'dashicons-media-spreadsheet',
                ppt: 'dashicons-media-document',
                pptx: 'dashicons-media-document',
                zip: 'dashicons-media-archive',
                jpg: 'dashicons-format-image',
                jpeg: 'dashicons-format-image',
                png: 'dashicons-format-image',
                gif: 'dashicons-format-image',
            };

            return iconMap[ext.toLowerCase()] || 'dashicons-media-default';
        },

        /**
         * Handle file upload
         */
        handleUpload(form) {
            const $form = $(form);
            const $progress = $form.find('.wpbp-upload-progress');
            const $progressFill = $progress.find('.wpbp-upload-progress__fill');
            const $progressText = $progress.find('.wpbp-upload-progress__text');
            const formData = new FormData(form);

            formData.append('action', 'wpbp_upload_file');
            formData.append('nonce', wpbpFileManager.nonce);

            // Show progress bar
            $form.find('.wpbp-form-actions').hide();
            $progress.show();

            $.ajax({
                url: wpbpFileManager.ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function () {
                    const xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener(
                        'progress',
                        function (e) {
                            if (e.lengthComputable) {
                                const percentComplete = (e.loaded / e.total) * 100;
                                $progressFill.css('width', percentComplete + '%');
                                $progressText.text(`${Math.round(percentComplete)}%`);
                            }
                        },
                        false
                    );
                    return xhr;
                },
                success: (response) => {
                    if (response.success) {
                        this.showSuccess(wpbpFileManager.strings.uploadSuccess);
                        $form[0].reset();
                        $form.closest('.wpbp-filemanager__upload-form').slideUp();
                        this.loadFiles();
                    } else {
                        this.showError(response.data || wpbpFileManager.strings.uploadError);
                    }
                },
                error: (jqXHR, textStatus, errorThrown) => {
                    console.error('Upload error:', textStatus, errorThrown);
                    this.showError(wpbpFileManager.strings.uploadError);
                },
                complete: () => {
                    $progress.hide();
                    $progressFill.css('width', '0%');
                    $form.find('.wpbp-form-actions').show();
                },
            });
        },

        /**
         * Handle file download
         */
        handleDownload(fileId) {
            const url = `${wpbpFileManager.ajaxurl}?action=wpbp_download_file&nonce=${wpbpFileManager.nonce}&file_id=${fileId}`;
            window.location.href = url;
        },

        /**
         * Show edit modal
         */
        showEditModal(fileId) {
            $.ajax({
                url: wpbpFileManager.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpbp_get_file_data',
                    nonce: wpbpFileManager.nonce,
                    file_id: fileId,
                },
                success: (response) => {
                    if (response.success) {
                        this.renderEditModal(response.data);
                    } else {
                        this.showError(response.data || 'Error loading file data');
                    }
                },
                error: () => {
                    this.showError('Error loading file data');
                },
            });
        },

        /**
         * Render edit modal
         */
        renderEditModal(fileData) {
            const $template = $('#wpbp-filemanager-edit-modal-template');
            const $modal = $($template.html()).clone();

            // Populate form
            $modal.find('input[name="file_id"]').val(fileData.id);
            $modal.find('input[name="title"]').val(fileData.title);

            // File info
            $modal.find('.wpbp-file-info__name').text(fileData.file.name);
            $modal.find('.wpbp-file-info__size').text(fileData.file.size);
            $modal.find('.wpbp-file-info__type').text(fileData.file.type.toUpperCase());

            // Add to body
            $('body').append($modal);
            $modal.find('input[name="title"]').focus();
        },

        /**
         * Handle file edit
         */
        handleEdit(form) {
            const $form = $(form);
            const formData = new FormData(form);
            formData.append('action', 'wpbp_edit_file');
            formData.append('nonce', wpbpFileManager.nonce);

            $.ajax({
                url: wpbpFileManager.ajaxurl,
                type: 'POST',
                data: Object.fromEntries(formData),
                success: (response) => {
                    if (response.success) {
                        this.showSuccess(wpbpFileManager.strings.editSuccess || 'File updated successfully');
                        $form.closest('.wpbp-modal').remove();
                        this.loadFiles();
                    } else {
                        this.showError(response.data || 'Error updating file');
                    }
                },
                error: () => {
                    this.showError('Error updating file');
                },
            });
        },

        /**
         * Handle file delete
         */
        handleDelete(fileId) {
            if (!confirm(wpbpFileManager.strings.confirmDelete)) {
                return;
            }

            $.ajax({
                url: wpbpFileManager.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpbp_delete_file',
                    nonce: wpbpFileManager.nonce,
                    file_id: fileId,
                },
                success: (response) => {
                    if (response.success) {
                        this.showSuccess(wpbpFileManager.strings.deleteSuccess);
                        this.loadFiles();
                    } else {
                        this.showError(response.data || wpbpFileManager.strings.deleteError);
                    }
                },
                error: () => {
                    this.showError(wpbpFileManager.strings.deleteError);
                },
            });
        },

        /**
         * Show success message
         */
        showSuccess(message) {
            this.showNotice(message, 'success');
        },

        /**
         * Show error message
         */
        showError(message) {
            this.showNotice(message, 'error');
        },

        /**
         * Show notice
         */
        showNotice(message, type = 'info') {
            const $notice = $(`
                <div class="wpbp-notice wpbp-notice--${type}" role="alert">
                    <span class="wpbp-notice__message">${this.escapeHtml(message)}</span>
                    <button class="wpbp-notice__close" type="button">
                        <span class="dashicons dashicons-no-alt"></span>
                    </button>
                </div>
            `);

            $('.wpbp-filemanager').prepend($notice);

            // Auto-remove after 5 seconds
            setTimeout(() => {
                $notice.fadeOut(() => $notice.remove());
            }, 5000);

            // Manual close
            $notice.find('.wpbp-notice__close').on('click', function () {
                $(this).closest('.wpbp-notice').fadeOut(function () {
                    $(this).remove();
                });
            });
        },

        /**
         * Escape HTML
         */
        escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;',
            };
            return String(text).replace(/[&<>"']/g, (m) => map[m]);
        },
    };

    // Initialize on document ready
    $(document).ready(() => {
        if ($('.wpbp-filemanager').length) {
            FileManager.init();
        }
    });
})(jQuery);
