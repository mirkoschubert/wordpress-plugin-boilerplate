<?php
/**
 * Template: Files List
 *
 * Display files in a specific area with upload form
 *
 * @package     WPPluginBoilerplate\Modules\FileManager
 * @since       1.1.0
 *
 * Variables available:
 * @var array $atts Shortcode attributes
 * @var Service $this Service instance
 */

if (!defined('ABSPATH')) {
    exit;
}

$area_slug = $atts['area'];
$show_upload = filter_var($atts['show_upload'], FILTER_VALIDATE_BOOLEAN);
$area_config = $this->get_area_config($area_slug);
$can_upload = $show_upload && current_user_can($this->get_module_option('upload_capability', 'upload_downloads'));
?>

<div class="wpbp-filemanager" data-area="<?php echo esc_attr($area_slug); ?>">

    <div class="wpbp-filemanager__header">
        <h3><?php echo esc_html($area_config['name']); ?></h3>
        <?php if (!empty($area_config['description'])): ?>
            <p class="wpbp-filemanager__description"><?php echo esc_html($area_config['description']); ?></p>
        <?php endif; ?>
    </div>

    <?php if ($can_upload): ?>
        <div class="wpbp-filemanager__upload">
            <button class="wpbp-filemanager__upload-toggle button">
                <span class="dashicons dashicons-upload"></span>
                <?php esc_html_e('Upload File', 'wp-plugin-boilerplate'); ?>
            </button>

            <div class="wpbp-filemanager__upload-form" style="display: none;">
                <form id="wpbp-file-upload-form" enctype="multipart/form-data">
                    <input type="hidden" name="area" value="<?php echo esc_attr($area_slug); ?>">
                    <input type="hidden" name="website" value=""><!-- Honeypot -->

                    <div class="wpbp-form-field">
                        <label for="wpbp-file-title">
                            <?php esc_html_e('File Title', 'wp-plugin-boilerplate'); ?> *
                        </label>
                        <input
                            type="text"
                            id="wpbp-file-title"
                            name="title"
                            required
                            placeholder="<?php esc_attr_e('Enter file title', 'wp-plugin-boilerplate'); ?>"
                        >
                    </div>

                    <div class="wpbp-form-field">
                        <label for="wpbp-file-input">
                            <?php esc_html_e('Select File', 'wp-plugin-boilerplate'); ?> *
                        </label>
                        <input
                            type="file"
                            id="wpbp-file-input"
                            name="file"
                            required
                        >
                        <p class="wpbp-form-help">
                            <?php
                            $max_size = $this->get_module_option('max_file_size', 0);
                            if ($max_size > 0) {
                                printf(
                                    esc_html__('Maximum file size: %s MB', 'wp-plugin-boilerplate'),
                                    $max_size
                                );
                            }
                            ?>
                        </p>
                    </div>

                    <div class="wpbp-form-actions">
                        <button type="submit" class="button button-primary">
                            <span class="dashicons dashicons-upload"></span>
                            <?php esc_html_e('Upload', 'wp-plugin-boilerplate'); ?>
                        </button>
                        <button type="button" class="button wpbp-filemanager__upload-cancel">
                            <?php esc_html_e('Cancel', 'wp-plugin-boilerplate'); ?>
                        </button>
                    </div>

                    <div class="wpbp-upload-progress" style="display: none;">
                        <div class="wpbp-upload-progress__bar">
                            <div class="wpbp-upload-progress__fill"></div>
                        </div>
                        <p class="wpbp-upload-progress__text"><?php esc_html_e('Uploading...', 'wp-plugin-boilerplate'); ?></p>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <div class="wpbp-filemanager__list">
        <div class="wpbp-filemanager__loading">
            <span class="dashicons dashicons-update-alt wpbp-spin"></span>
            <?php esc_html_e('Loading files...', 'wp-plugin-boilerplate'); ?>
        </div>

        <div class="wpbp-filemanager__empty" style="display: none;">
            <span class="dashicons dashicons-folder-alt"></span>
            <p><?php esc_html_e('No files found in this area.', 'wp-plugin-boilerplate'); ?></p>
        </div>

        <table class="wpbp-filemanager__table" style="display: none;">
            <thead>
                <tr>
                    <th><?php esc_html_e('Title', 'wp-plugin-boilerplate'); ?></th>
                    <th><?php esc_html_e('Type', 'wp-plugin-boilerplate'); ?></th>
                    <?php if ($this->is_option_enabled('show_file_size')): ?>
                        <th><?php esc_html_e('Size', 'wp-plugin-boilerplate'); ?></th>
                    <?php endif; ?>
                    <?php if ($this->is_option_enabled('show_upload_date')): ?>
                        <th><?php esc_html_e('Date', 'wp-plugin-boilerplate'); ?></th>
                    <?php endif; ?>
                    <?php if ($this->is_option_enabled('show_author')): ?>
                        <th><?php esc_html_e('Author', 'wp-plugin-boilerplate'); ?></th>
                    <?php endif; ?>
                    <th class="wpbp-filemanager__actions-col"><?php esc_html_e('Actions', 'wp-plugin-boilerplate'); ?></th>
                </tr>
            </thead>
            <tbody>
                <!-- Files will be inserted here via JavaScript -->
            </tbody>
        </table>
    </div>

    <!-- Edit Modal Template (hidden, cloned by JS) -->
    <template id="wpbp-filemanager-edit-modal-template">
        <div class="wpbp-modal">
            <div class="wpbp-modal__overlay"></div>
            <div class="wpbp-modal__content">
                <div class="wpbp-modal__header">
                    <h3><?php esc_html_e('Edit File', 'wp-plugin-boilerplate'); ?></h3>
                    <button class="wpbp-modal__close" type="button">
                        <span class="dashicons dashicons-no-alt"></span>
                    </button>
                </div>

                <div class="wpbp-modal__body">
                    <form class="wpbp-filemanager-edit-form">
                        <input type="hidden" name="file_id" value="">

                        <div class="wpbp-form-field">
                            <label for="wpbp-edit-title">
                                <?php esc_html_e('File Title', 'wp-plugin-boilerplate'); ?>
                            </label>
                            <input
                                type="text"
                                id="wpbp-edit-title"
                                name="title"
                                required
                            >
                        </div>

                        <div class="wpbp-file-info">
                            <p>
                                <strong><?php esc_html_e('Filename:', 'wp-plugin-boilerplate'); ?></strong>
                                <span class="wpbp-file-info__name"></span>
                            </p>
                            <p>
                                <strong><?php esc_html_e('File Size:', 'wp-plugin-boilerplate'); ?></strong>
                                <span class="wpbp-file-info__size"></span>
                            </p>
                            <p>
                                <strong><?php esc_html_e('Type:', 'wp-plugin-boilerplate'); ?></strong>
                                <span class="wpbp-file-info__type"></span>
                            </p>
                        </div>

                        <div class="wpbp-modal__actions">
                            <button type="submit" class="button button-primary">
                                <?php esc_html_e('Save Changes', 'wp-plugin-boilerplate'); ?>
                            </button>
                            <button type="button" class="button wpbp-modal__cancel">
                                <?php esc_html_e('Cancel', 'wp-plugin-boilerplate'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>
