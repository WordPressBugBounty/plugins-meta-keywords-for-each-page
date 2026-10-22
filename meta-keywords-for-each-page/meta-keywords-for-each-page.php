<?php
/*
 * Plugin Name: Meta Keywords for Each Page
 * Plugin URI: 
 * Description: WordPress Plugin to Enhance SEO by Adding Meta Keywords for Better Website Optimization.
 * Author: Hemant Jangir
 * Author URI: https://www.linkedin.com/in/hemant-jangir-b35469190/
 * Text Domain: meta-keywords-for-each-page
 * Domain Path: /languages
 * Version: 1.0.9
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * License: GPL2
 */

if (!defined("ABSPATH"))
    exit;

if (!class_exists('Meta_Keywords_for_Each_Page')) {
    class Meta_Keywords_for_Each_Page {

        public function __construct() {
            add_action('admin_enqueue_scripts', array($this, 'mkephem_enqueue_admin_scripts'));
            add_action('add_meta_boxes', array($this, 'mkephem_meta_box'));
            add_action('save_post', array($this, 'mkephem_save_metadata'), 0);
            add_action('admin_init', array($this, 'mkephem_add_meta_boxes_to_all_taxonomies'));
            add_action('admin_menu', array($this, 'mkephem_register_custom_post_type_meta_keywords_page'));
            add_action('admin_post_mkephem_save_meta_keywords_action', array($this, 'mkephem_handle_meta_keywords_save'));
            add_action('admin_post_mkephem_export_csv', array($this, 'mkephem_export_csv'));
            add_action('admin_post_mkephem_import_csv', array($this, 'mkephem_import_csv'));
            add_action('wp_head', array($this, 'display_meta_keywords'), 5);
        }

        /**
         * Enqueue admin scripts and styles.
         */
        public function mkephem_enqueue_admin_scripts($hook_suffix) {
            if (!is_admin()) {
                return;
            }

            $allowed_hooks = array(
                'toplevel_page_mkephem-meta-keywords',
                'post.php',
                'post-new.php',
                'edit-tags.php',
                'term.php'
            );

            if (!in_array($hook_suffix, $allowed_hooks, true)) {
                return;
            }

            wp_enqueue_style('mkephem-admin-style', plugin_dir_url(__FILE__) . 'assets/css/admin-style.css', array(), '1.0.0');
            wp_enqueue_script('mkephem-admin-script', plugin_dir_url(__FILE__) . 'assets/js/admin-script.js', array('jquery'), '1.0.0', true);
        }

        /**
         * Add meta boxes.
         */
        public function mkephem_meta_box() {
            add_meta_box('mkephem_meta_keywords', __('Keywords Box', 'meta-keywords-for-each-page'), array($this, 'mkephem_meta_keywords_callback'));
        }

        /**
         * Add Meta box fields.
         */
        public function mkephem_meta_keywords_callback() {
            global $post;
            $meta_keyword = get_post_meta($post->ID, 'mkephem_meta_keywords', true);
            wp_nonce_field(basename(__FILE__), 'mkephem_meta_keywords_nonce'); ?>
            <div class="mkephem-form">
                <table class="form-table">
                    <tr class="form-field">
                        <th scope="row">
                            <label for="mkephem_meta_keywords"><?php echo esc_html__('Meta Keywords', 'meta-keywords-for-each-page'); ?></label>
                        </th>
                        <td>
                            <textarea name="mkephem_meta_keywords" id="mkephem_meta_keywords" placeholder="Meta keywords" class="large-text mkephem-meta-keywords-input" rows="5" data-preview-id="mkephem_post_meta_keywords_preview"><?php echo esc_textarea($meta_keyword); ?></textarea>
                            <p class="description"><?php echo esc_html__('Enter the meta keywords for this post.', 'meta-keywords-for-each-page'); ?></p>
                            <div class="mkephem-meta-preview">
                                <label><?php echo esc_html__('Preview generated meta tag:', 'meta-keywords-for-each-page'); ?></label>
                                <pre><code id="mkephem_post_meta_keywords_preview"><?php echo esc_html($this->mkephem_build_meta_keyword_tag($meta_keyword)); ?></code></pre>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            <?php
        }

        /**
         * Save post Meta.
         *
         * @param array $post_id ID of current Post.
         *
         * @return void
         */
        public function mkephem_save_metadata($post_id) {
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            }

            /* Verify the nonce before proceeding. */
            if (!isset($_POST['mkephem_meta_keywords_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['mkephem_meta_keywords_nonce'])), basename(__FILE__))) {
                return $post_id;
            }

            // If current user can't edit this post
            if (!current_user_can('edit_posts', $post_id)) {
                return;
            }

            // Save meta box data.
            if (isset($_POST['mkephem_meta_keywords'])) {
                $meta_keyword = sanitize_textarea_field(wp_unslash($_POST['mkephem_meta_keywords']));
                update_post_meta($post_id, 'mkephem_meta_keywords', $meta_keyword);
            }
        }

        /**
         * Add meta boxes to all taxonomies.
         */
        public function mkephem_add_meta_boxes_to_all_taxonomies() {
            $taxonomies = get_taxonomies(['show_ui' => true], 'names'); // Get all taxonomies with UI enabled
            foreach ($taxonomies as $taxonomy) {
                add_action("{$taxonomy}_add_form_fields", array($this, 'mkephem_add_taxonomy_meta_box'));
                add_action("{$taxonomy}_edit_form_fields", array($this, 'mkephem_edit_taxonomy_meta_box'));
                add_action("created_{$taxonomy}", array($this, 'mkephem_save_taxonomy_meta'), 10, 2);
                add_action("edited_{$taxonomy}", array($this, 'mkephem_save_taxonomy_meta'), 10, 2);
            }
        }

        /**
         * Display the meta box when adding a new term.
         */
        public function mkephem_add_taxonomy_meta_box($taxonomy) {
            ?>
            <div class="form-field">
                <label for="mkephem_meta_keywords"><?php echo esc_html__('Meta Keywords', 'meta-keywords-for-each-page'); ?></label>
                <textarea name="mkephem_meta_keywords" id="mkephem_meta_keywords" rows="5" class="mkephem-meta-keywords-input" data-preview-id="mkephem_term_meta_keywords_preview"></textarea>
                <p><?php echo esc_html__('Add meta keywords for this taxonomy term.', 'meta-keywords-for-each-page'); ?></p>
                <div class="mkephem-meta-preview">
                    <label><?php echo esc_html__('Preview generated meta tag:', 'meta-keywords-for-each-page'); ?></label>
                    <pre><code id="mkephem_term_meta_keywords_preview"><?php echo esc_html($this->mkephem_build_meta_keyword_tag('')); ?></code></pre>
                </div>
            </div>
            <?php
        }

        /**
         * Display the meta box when editing an existing term.
         */
        public function mkephem_edit_taxonomy_meta_box($term) {
            $term_id = $term->term_id;
            $meta_keyword = get_term_meta($term_id, 'mkephem_meta_keywords', true); // Retrieve existing value
            wp_nonce_field('mkephem_meta_keywords_nonce_action', 'mkephem_meta_keywords_nonce');
            ?>
            <tr class="form-field">
                <th scope="row">
                    <label for="mkephem_meta_keywords"><?php echo esc_html__('Meta Keywords', 'meta-keywords-for-each-page'); ?></label>
                </th>
                <td>
                    <textarea name="mkephem_meta_keywords" id="mkephem_meta_keywords" rows="5" class="mkephem-meta-keywords-input" data-preview-id="mkephem_term_meta_keywords_preview"><?php echo esc_textarea($meta_keyword); ?></textarea>
                    <p class="description"><?php echo esc_html__('Edit meta keywords for this taxonomy term.', 'meta-keywords-for-each-page'); ?></p>
                    <div class="mkephem-meta-preview">
                        <label><?php echo esc_html__('Preview generated meta tag:', 'meta-keywords-for-each-page'); ?></label>
                        <pre><code id="mkephem_term_meta_keywords_preview"><?php echo esc_html($this->mkephem_build_meta_keyword_tag($meta_keyword)); ?></code></pre>
                    </div>
                </td>
            </tr>
            <?php
        }

        /**
         * Save the field value for taxonomy terms.
         */
        public function mkephem_save_taxonomy_meta($term_id, $tt_id) {
            if (isset($_POST['mkephem_meta_keywords_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['mkephem_meta_keywords_nonce'])), 'mkephem_meta_keywords_nonce_action')) {
                if (isset($_POST['mkephem_meta_keywords'])) {
                    $meta_keyword = sanitize_text_field(wp_unslash($_POST['mkephem_meta_keywords']));
                    update_term_meta($term_id, 'mkephem_meta_keywords', $meta_keyword);
                }
            }
        }

        /**
         * Register the Meta Keywords options page.
         */
        public function mkephem_register_custom_post_type_meta_keywords_page() {
            add_menu_page(
                __('Meta Keywords', 'meta-keywords-for-each-page'),  // Page title
                __('Meta Keywords', 'meta-keywords-for-each-page'),  // Menu title
                'manage_options',               // Capability
                'mkephem-meta-keywords',        // Menu slug
                array($this, 'mkephem_render_meta_keywords_page'), // Callback function
                'dashicons-edit',               // Icon
                20                              // Position
            );
        }

        /**
         * Render the Meta Keywords options page.
         */
        public function mkephem_render_meta_keywords_page() {
            if (!current_user_can('manage_options')) {
                return;
            }

            // Check for the success message
            if (isset($_GET['message']) && $_GET['message'] === 'saved') {
                if (isset($_GET['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'mkephem_success_message_nonce')) {
                    echo '<div class="updated"><p>' . esc_html__('Meta Keywords Saved!', 'meta-keywords-for-each-page') . '</p></div>';
                } else {
                    echo '<div class="error"><p>' . esc_html__('Invalid request.', 'meta-keywords-for-each-page') . '</p></div>';
                }
            }

            if (isset($_GET['message']) && $_GET['message'] === 'imported') {
                echo '<div class="updated"><p>' . esc_html__('CSV imported successfully.', 'meta-keywords-for-each-page') . '</p></div>';
            }

            // Display the form
            ?>
            <div class="wrap">
                <h1><?php echo esc_html__('Meta Keywords', 'meta-keywords-for-each-page'); ?></h1>
                <div class="mkephem_import_export_section">
                    <!-- Import Form -->
                    <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data" style="display:inline-block;">
                        <?php wp_nonce_field('mkephem_import_csv', 'mkephem_import_csv_nonce'); ?>
                        <input type="hidden" name="action" value="mkephem_import_csv">
                        <input type="file" name="mkephem_import_file" accept=".csv" required>
                        <input type="submit" class="button" value="<?php esc_attr_e('Import CSV', 'meta-keywords-for-each-page'); ?>">
                    </form>
                    <!-- Export Form -->
                    <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline-block;margin-right:20px;">
                        <?php wp_nonce_field('mkephem_export_csv', 'mkephem_export_csv_nonce'); ?>
                        <input type="hidden" name="action" value="mkephem_export_csv">
                        <input type="submit" class="button" value="<?php esc_attr_e('Export Meta Keywords (CSV)', 'meta-keywords-for-each-page'); ?>">
                    </form>
                </div>
                <hr>

                <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <?php
                    wp_nonce_field('mkephem_save_meta_keywords', 'mkephem_meta_keywords_nonce');
                    ?>
                    <input type="hidden" name="action" value="mkephem_save_meta_keywords_action">
                    <?php
                    $flag = false;
                    $custom_post_types = get_post_types(['publicly_queryable' => true], 'objects');
                    $skip_post_types = array('attachment', 'post', 'product');
                    foreach ($custom_post_types as $post_type => $details) {
                        if (in_array($post_type, $skip_post_types)) {
                            continue;
                        }
                        $flag = true;
                        $saved_keywords = get_option($post_type . '_meta_keywords', '');
                        ?>
                        <div class="cpt-section">
                        <h2><?php echo esc_html($details->labels->name); ?> (<?php echo esc_html($post_type); ?>)</h2>
                        <p><?php echo esc_html__('Frontend URL:', 'meta-keywords-for-each-page'); ?> <a href="<?php echo esc_url(get_post_type_archive_link($post_type)); ?>" target="_blank"><?php echo esc_url(get_post_type_archive_link($post_type)); ?></a></p>
                        <textarea name="<?php echo esc_attr($post_type . '_meta_keywords'); ?>" rows="5" cols="70" class="mkephem-meta-keywords-input" data-preview-id="<?php echo esc_attr($post_type . '_meta_keywords_preview'); ?>"><?php echo esc_textarea($saved_keywords); ?></textarea>
                        <div class="mkephem-meta-preview">
                            <label><?php echo esc_html__('Preview generated meta tag:', 'meta-keywords-for-each-page'); ?></label>
                            <pre><code id="<?php echo esc_attr($post_type . '_meta_keywords_preview'); ?>"><?php echo esc_html($this->mkephem_build_meta_keyword_tag($saved_keywords)); ?></code></pre>
                        </div>
                        </div>
                        <hr>
                        <?php
                    }
                    if ($flag) {
                    ?>
                    <p class="submit">
                        <input type="submit" class="button-primary" value="<?php esc_attr_e('Save Meta Keywords', 'meta-keywords-for-each-page'); ?>">
                    </p>
                    <?php }else{ ?>
                        <p><?php echo esc_html__('No custom post type found.', 'meta-keywords-for-each-page'); ?>
                    <?php } ?>
                </form>
            </div>
            <?php
        }

        /**
         * Handle saving the meta keywords.
         */
        public function mkephem_handle_meta_keywords_save() {
            if (isset($_POST['mkephem_meta_keywords_nonce']) && wp_verify_nonce(sanitize_textarea_field(wp_unslash($_POST['mkephem_meta_keywords_nonce'])), 'mkephem_save_meta_keywords')) {
                $custom_post_types = get_post_types(['publicly_queryable' => true], 'objects');
                foreach ($custom_post_types as $post_type => $details) {
                    if (isset($_POST[$post_type . '_meta_keywords'])) {
                        update_option($post_type . '_meta_keywords', sanitize_textarea_field(wp_unslash($_POST[$post_type . '_meta_keywords'])));
                    }
                }
                wp_safe_redirect(add_query_arg(
                    array(
                        'message' => 'saved',
                        '_wpnonce' => wp_create_nonce('mkephem_success_message_nonce')
                    ),
                    wp_get_referer()
                ));
                exit;
            }
        }

        /**
         * Export CSV handler.
         */
        public function mkephem_export_csv() {
            if ( ! current_user_can( 'manage_options' ) ) {
                wp_die( esc_html__( 'You do not have permission to perform this action.', 'meta-keywords-for-each-page' ) );
            }

            if ( ! isset( $_POST['mkephem_export_csv_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mkephem_export_csv_nonce'] ) ), 'mkephem_export_csv' ) ) {
                wp_die( esc_html__( 'Invalid request.', 'meta-keywords-for-each-page' ) );
            }

            global $wp_filesystem;

            // Initialize WP_Filesystem.
            if ( ! function_exists( 'WP_Filesystem' ) ) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
            }
            WP_Filesystem();

            if ( ! $wp_filesystem ) {
                wp_die( esc_html__( 'Filesystem error: WP_Filesystem could not be initialized.', 'meta-keywords-for-each-page' ) );
            }

            // Create a temporary file.
            $temp_file = wp_tempnam( 'meta-keywords-export' );
            if ( ! $temp_file ) {
                wp_die( esc_html__( 'Failed to create temporary file.', 'meta-keywords-for-each-page' ) );
            }

            // Build CSV content in memory.
            $csv_output = '';
            $csv_output .= "source,object_type,object_id,object_key,title,meta_keywords\n";

            // Export posts (public post types)
            $post_types = get_post_types( [ 'public' => true ], 'names' );
            if ( ! empty( $post_types ) ) {
                $args = [
                    'post_type'   => $post_types,
                    'post_status' => 'any',
                    'numberposts' => -1,
                    'fields'      => 'ids',
                ];
                $all_posts = get_posts( $args );
                if ( ! empty( $all_posts ) ) {
                    foreach ( $all_posts as $post_id ) {
                        $meta = get_post_meta( $post_id, 'mkephem_meta_keywords', true );
                        if ( ! empty( $meta ) ) {
                            $post_type = get_post_type( $post_id );
                            $title     = get_the_title( $post_id );
                            $row = [ 'post', $post_type, $post_id, '', $title, $meta ];
                            $csv_output .= $this->mkephem_array_to_csv_line( $row );
                        }
                    }
                }
            }

            // Export terms (taxonomies with show_ui)
            $taxonomies = get_taxonomies( [ 'show_ui' => true ], 'names' );
            if ( ! empty( $taxonomies ) ) {
                foreach ( $taxonomies as $taxonomy ) {
                    $terms = get_terms( [ 'taxonomy' => $taxonomy, 'hide_empty' => false ] );
                    if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
                        foreach ( $terms as $term ) {
                            $meta = get_term_meta( $term->term_id, 'mkephem_meta_keywords', true );
                            if ( ! empty( $meta ) ) {
                                $row = [ 'term', $taxonomy, $term->term_id, '', $term->name, $meta ];
                                $csv_output .= $this->mkephem_array_to_csv_line( $row );
                            }
                        }
                    }
                }
            }

            // Export options for custom post types meta keywords (post_type_meta_keywords)
            $cpts = get_post_types( [ 'publicly_queryable' => true ], 'names' );
            if ( ! empty( $cpts ) ) {
                foreach ( $cpts as $pt ) {
                    $opt_name = $pt . '_meta_keywords';
                    $val      = get_option( $opt_name, '' );
                    if ( ! empty( $val ) ) {
                        $row = [ 'option', $pt, '', $opt_name, '', $val ];
                        $csv_output .= $this->mkephem_array_to_csv_line( $row );
                    }
                }
            }

            // Write CSV content to temp file using WP_Filesystem.
            $wp_filesystem->put_contents( $temp_file, $csv_output, FS_CHMOD_FILE );

            // Output CSV for download.
            $filename = 'meta-keywords-export-' . gmdate( 'Y-m-d-His' ) . '.csv';
            header( 'Content-Type: text/csv; charset=utf-8' );
            header( 'Content-Disposition: attachment; filename=' . $filename );

            // Use WP_Filesystem to read file contents instead of readfile().
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo $wp_filesystem->get_contents( $temp_file );

            // Delete temporary file.
            $wp_filesystem->delete( $temp_file );

            exit;
        }

        /**
         * Helper: Convert array to CSV line
         */
        private function mkephem_array_to_csv_line( $fields ) {
            $escaped = array_map(
                function ( $field ) {
                    $field = str_replace( '"', '""', $field ); // Escape quotes
                    return '"' . $field . '"';
                },
                $fields
            );
            return implode( ',', $escaped ) . "\n";
        }


        /**
         * Import CSV handler.
         */
        public function mkephem_import_csv() {
            if ( ! current_user_can( 'manage_options' ) ) {
                wp_die( esc_html__( 'You do not have permission to perform this action.', 'meta-keywords-for-each-page' ) );
            }

            if (
                ! isset( $_POST['mkephem_import_csv_nonce'] ) ||
                ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mkephem_import_csv_nonce'] ) ), 'mkephem_import_csv' )
            ) {
                wp_die( esc_html__( 'Invalid request.', 'meta-keywords-for-each-page' ) );
            }

            if ( ! isset( $_FILES['mkephem_import_file']['error'] ) || UPLOAD_ERR_OK !== $_FILES['mkephem_import_file']['error'] ) {
                wp_safe_redirect( add_query_arg( 'message', 'import_failed', wp_get_referer() ) );
                exit;
            }

            $file = isset( $_FILES['mkephem_import_file']['tmp_name'] ) ? sanitize_text_field( $_FILES['mkephem_import_file']['tmp_name'] ) : '';
            if ( ! is_uploaded_file( $file ) ) {
                wp_safe_redirect( add_query_arg( 'message', 'import_failed', wp_get_referer() ) );
                exit;
            }

            // Initialize WP_Filesystem.
            if ( ! function_exists( 'WP_Filesystem' ) ) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
            }

            global $wp_filesystem;
            WP_Filesystem();

            if ( ! $wp_filesystem || ! $wp_filesystem->exists( $file ) ) {
                wp_safe_redirect( add_query_arg( 'message', 'import_failed', wp_get_referer() ) );
                exit;
            }

            $content = $wp_filesystem->get_contents( $file );
            if ( false === $content ) {
                wp_safe_redirect( add_query_arg( 'message', 'import_failed', wp_get_referer() ) );
                exit;
            }

            $rows      = array_map( 'str_getcsv', explode( "\n", $content ) );
            $row_index = 0;
            $imported  = 0;

            foreach ( $rows as $data ) {
                if ( empty( $data ) || ! is_array( $data ) ) {
                    continue;
                }

                $row_index++;

                // Handle header row (skip if it contains 'source' and 'meta_keywords').
                if ( 1 === $row_index ) {
                    $header = array_map( 'trim', $data );
                    if ( in_array( 'source', $header, true ) && in_array( 'meta_keywords', $header, true ) ) {
                        continue;
                    }
                }

                $source         = isset( $data[0] ) ? trim( $data[0] ) : '';
                $object_type    = isset( $data[1] ) ? trim( $data[1] ) : '';
                $object_id      = isset( $data[2] ) ? trim( $data[2] ) : '';
                $object_key     = isset( $data[3] ) ? trim( $data[3] ) : '';
                $meta_keywords  = isset( $data[5] ) ? trim( $data[5] ) : '';

                if ( empty( $source ) || empty( $meta_keywords ) ) {
                    continue;
                }

                $meta_keywords_sanitized = sanitize_textarea_field( $meta_keywords );

                if ( 'post' === $source && is_numeric( $object_id ) ) {
                    $post_id = intval( $object_id );
                    update_post_meta( $post_id, 'mkephem_meta_keywords', $meta_keywords_sanitized );
                    $imported++;
                } elseif ( 'term' === $source && is_numeric( $object_id ) ) {
                    $term_id = intval( $object_id );
                    update_term_meta( $term_id, 'mkephem_meta_keywords', $meta_keywords_sanitized );
                    $imported++;
                } elseif ( 'option' === $source ) {
                    if ( ! empty( $object_key ) ) {
                        update_option( $object_key, $meta_keywords_sanitized );
                        $imported++;
                    } elseif ( ! empty( $object_type ) ) {
                        $opt = $object_type . '_meta_keywords';
                        update_option( $opt, $meta_keywords_sanitized );
                        $imported++;
                    }
                }
            }

            // Redirect back with success message.
            wp_safe_redirect( add_query_arg( 'message', 'imported', wp_get_referer() ) );
            exit;
        }

        /**
         * Build preview meta tag string.
         */
        private function mkephem_build_meta_keyword_tag( $keywords ) {
            $content = trim( $keywords );
            if ( '' === $content ) {
                return esc_html__( '<meta name="keywords" content="...">', 'meta-keywords-for-each-page' );
            }

            return '<meta name="keywords" content="' . esc_attr( $content ) . '">';
        }

        /**
         * Output meta keywords in the head tag.
         */
        public function display_meta_keywords() {
            if (is_singular()) {
                global $post;
                $meta_keyword = get_post_meta( $post->ID, 'mkephem_meta_keywords', true );
            }
            else if (is_tax() || is_category() || is_tag()) {
                $term = get_queried_object(); // Get the current term object

                if ($term && isset($term->term_id) && isset($term->taxonomy)) {
                    $term_id = $term->term_id;         // Get the term ID
                    $meta_keyword = get_term_meta($term_id, 'mkephem_meta_keywords', true);
                }
            }
            else if(is_archive()){
                $meta_keyword = get_option(get_post_type() . '_meta_keywords', true);
            }
            
            if (!empty($meta_keyword)) {
                echo '<meta name="keywords" content="'.esc_attr($meta_keyword).'">';
            }
            
        }
    }

    new Meta_Keywords_for_Each_Page();
}
