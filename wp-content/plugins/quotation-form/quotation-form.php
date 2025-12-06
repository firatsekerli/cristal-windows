<?php
/**
 * Plugin Name: Multi-Step Quotation Form
 * Plugin URI: https://cristalwindows.com
 * Description: A comprehensive multi-step quotation form for Windows, Doors, and Bay Windows with basket functionality
 * Version: 1.0.0
 * Author: Cristal Windows
 * Author URI: https://cristalwindows.com
 * Text Domain: quotation-form
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.2
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('QUOTATION_FORM_VERSION', '1.0.0');
define('QUOTATION_FORM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('QUOTATION_FORM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('QUOTATION_FORM_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Load Composer autoloader for TCPDF
if (file_exists(QUOTATION_FORM_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once QUOTATION_FORM_PLUGIN_DIR . 'vendor/autoload.php';
}

/**
 * Main Plugin Class
 */
class Quotation_Form_Plugin {

    /**
     * Instance of this class
     */
    private static $instance = null;

    /**
     * Get instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
        $this->load_dependencies();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Note: quotation CPT is registered via ACF Pro UI
        add_action('acf/init', array($this, 'register_acf_options_page'));
        add_filter('acf/settings/save_json', array($this, 'acf_json_save_point'));
        add_filter('acf/settings/load_json', array($this, 'acf_json_load_point'));
        add_action('wp_ajax_submit_quotation_form', array($this, 'handle_form_submission'));
        add_action('wp_ajax_nopriv_submit_quotation_form', array($this, 'handle_form_submission'));
        add_action('wp_ajax_download_quote_pdf', array($this, 'handle_pdf_download'));
        add_action('wp_ajax_nopriv_download_quote_pdf', array($this, 'handle_pdf_download'));
        add_shortcode('quotation_form', array($this, 'render_form_shortcode'));

        // Populate select field choices dynamically
        add_filter('acf/load_field/key=field_type_category', array($this, 'populate_category_choices'));
        add_filter('acf/load_field/key=field_material_available_types', array($this, 'populate_type_choices'));
        add_filter('acf/load_field/key=field_style_types', array($this, 'populate_type_choices'));

        // Add admin scripts for auto-slug generation
        add_action('acf/input/admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

        // Auto-calculate quote price when quotation is saved
        add_action('acf/save_post', array($this, 'auto_calculate_quote_price'), 20);

        // Generate PDF when quotation is saved
        add_action('acf/save_post', array($this, 'generate_quote_pdf'), 25);

        // Add custom admin columns
        add_filter('manage_quotation_posts_columns', array($this, 'add_quotation_columns'));
        add_action('manage_quotation_posts_custom_column', array($this, 'populate_quotation_columns'), 10, 2);
        add_filter('manage_edit-quotation_sortable_columns', array($this, 'sortable_quotation_columns'));
    }

    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        // Load additional files if needed
    }

    /**
     * Register ACF options page
     */
    public function register_acf_options_page() {
        if (function_exists('acf_add_options_page')) {
            acf_add_options_page(array(
                'page_title'  => 'Quotation Form Settings',
                'menu_title'  => 'Quote Settings',
                'menu_slug'   => 'quotation-form-settings',
                'capability'  => 'manage_options',
                'icon_url'    => 'dashicons-feedback',
                'position'    => 58,
                'redirect'    => false
            ));
        }
    }

    /**
     * Populate category choices from product categories
     */
    public function populate_category_choices($field) {
        $field['choices'] = array();

        // Get product categories
        if (function_exists('get_field')) {
            $categories = get_field('product_categories', 'option');
            if (!empty($categories) && is_array($categories)) {
                foreach ($categories as $category) {
                    $slug = isset($category['slug']) ? $category['slug'] : '';
                    $name = isset($category['name']) ? $category['name'] : '';

                    if ($slug && $name) {
                        $field['choices'][$slug] = $name;
                    }
                }
            }
        }

        // Fallback to defaults if no categories exist
        if (empty($field['choices'])) {
            $field['choices'] = array(
                'windows' => 'Windows',
                'doors' => 'Doors',
                'bay-windows' => 'Bay Windows'
            );
        }

        return $field;
    }

    /**
     * Populate product type choices for materials and styles
     */
    public function populate_type_choices($field) {
        $field['choices'] = array();

        // Get all product types
        if (function_exists('get_field')) {
            $product_types = get_field('product_types', 'option');
            if (!empty($product_types) && is_array($product_types)) {
                foreach ($product_types as $type) {
                    $slug = isset($type['slug']) ? $type['slug'] : '';
                    $name = isset($type['name']) ? $type['name'] : '';
                    $category = isset($type['category']) ? $type['category'] : '';

                    if ($slug && $name) {
                        // Format: "Casement Windows (Windows)"
                        $display_name = $name;
                        if ($category) {
                            $category_label = ucfirst(str_replace('-', ' ', $category));
                            $display_name .= ' (' . $category_label . ')';
                        }
                        $field['choices'][$slug] = $display_name;
                    }
                }
            }
        }

        return $field;
    }

    /**
     * Enqueue admin scripts for ACF fields
     */
    public function enqueue_admin_scripts() {
        ?>
        <style>
        .acf-field[data-name="quote_price"] input {
            background: #f0f7ff !important;
            font-weight: bold;
            font-size: 16px;
            color: #0066cc !important;
        }
        .acf-field[data-name="quote_pdf_url"] .acf-input-wrap {
            position: relative;
        }
        .acf-field[data-name="quote_pdf_url"] input {
            padding-right: 120px;
        }
        .pdf-download-button {
            position: absolute;
            right: 5px;
            top: 5px;
            background: #0066cc;
            color: white;
            padding: 8px 15px;
            border-radius: 3px;
            text-decoration: none;
            font-weight: bold;
            font-size: 12px;
            display: inline-block;
            transition: background 0.3s;
        }
        .pdf-download-button:hover {
            background: #0052a3;
            color: white;
        }
        .pdf-download-button:before {
            content: "📄 ";
        }
        </style>
        <script type="text/javascript">
        (function($) {
            if (typeof acf === 'undefined') return;

            // Helper function to generate slug
            function generateSlug(text) {
                return text
                    .toLowerCase()
                    .trim()
                    .replace(/[^a-z0-9\s-]/g, '')  // Remove special characters
                    .replace(/\s+/g, '-')            // Replace spaces with hyphens
                    .replace(/-+/g, '-')             // Replace multiple hyphens with single
                    .replace(/^-+|-+$/g, '');        // Remove leading/trailing hyphens
            }

            // Auto-calculate quote price from item prices
            function calculateQuotePrice() {
                var total = 0;
                var $basketItemsRepeater = $('.acf-field[data-name="basket_items"]');

                if ($basketItemsRepeater.length) {
                    $basketItemsRepeater.find('.acf-row:not(.acf-clone)').each(function() {
                        var $row = $(this);
                        var $priceInput = $row.find('[data-name="item_price"] input[type="number"]');

                        if ($priceInput.length) {
                            var price = parseFloat($priceInput.val()) || 0;
                            total += price;
                        }
                    });
                }

                // Update the quote price field
                var $quotePriceField = $('.acf-field[data-name="quote_price"] input[type="number"]');
                if ($quotePriceField.length) {
                    $quotePriceField.val(total.toFixed(2));
                    $quotePriceField.trigger('change');
                }
            }

            // Listen for changes to item prices
            acf.addAction('ready', function() {
                // Calculate on page load
                calculateQuotePrice();

                // Recalculate when item price changes
                $(document).on('change keyup', '.acf-field[data-name="item_price"] input', function() {
                    calculateQuotePrice();
                });

                // Recalculate when rows are added or removed
                acf.addAction('append', function($el) {
                    if ($el.closest('.acf-field[data-name="basket_items"]').length) {
                        calculateQuotePrice();
                    }
                });

                acf.addAction('remove', function($el) {
                    if ($el.closest('.acf-field[data-name="basket_items"]').length) {
                        setTimeout(calculateQuotePrice, 100);
                    }
                });

                // Add download button to PDF URL field
                var $pdfUrlField = $('.acf-field[data-name="quote_pdf_url"]');
                if ($pdfUrlField.length) {
                    var $input = $pdfUrlField.find('input');
                    var pdfUrl = $input.val();

                    if (pdfUrl && !$pdfUrlField.find('.pdf-download-button').length) {
                        var $button = $('<a href="' + pdfUrl + '" target="_blank" class="pdf-download-button">Download PDF</a>');
                        $pdfUrlField.find('.acf-input-wrap').append($button);
                    }

                    // Update button when URL changes
                    $input.on('change', function() {
                        var url = $(this).val();
                        var $existingButton = $pdfUrlField.find('.pdf-download-button');

                        if (url) {
                            if ($existingButton.length) {
                                $existingButton.attr('href', url);
                            } else {
                                var $button = $('<a href="' + url + '" target="_blank" class="pdf-download-button">Download PDF</a>');
                                $pdfUrlField.find('.acf-input-wrap').append($button);
                            }
                        } else {
                            $existingButton.remove();
                        }
                    });
                }
            });

            // Auto-generate slug from name - works for all repeaters
            acf.addAction('ready_field/name=name', function($field) {
                var $nameInput = $field.find('input[type="text"]');
                var $row = $nameInput.closest('.acf-row');

                // Try multiple selectors to find the slug field
                var $slugInput = $row.find('input[data-name="slug"]');
                if (!$slugInput.length) {
                    $slugInput = $row.find('td[data-name="slug"] input');
                }
                if (!$slugInput.length) {
                    $slugInput = $row.find('[data-key*="slug"] input');
                }

                if ($slugInput.length && $nameInput.length) {
                    // Trigger on input (while typing) for better UX
                    $nameInput.on('input keyup', function() {
                        var currentSlug = $slugInput.val().trim();
                        // Only auto-generate if slug is empty
                        if (currentSlug === '') {
                            var name = $(this).val();
                            var slug = generateSlug(name);
                            $slugInput.val(slug);
                            // Trigger change event so ACF knows the field changed
                            $slugInput.trigger('change');
                        }
                    });

                    // Also trigger on blur as a fallback
                    $nameInput.on('blur', function() {
                        var currentSlug = $slugInput.val().trim();
                        if (currentSlug === '') {
                            var name = $(this).val();
                            var slug = generateSlug(name);
                            $slugInput.val(slug);
                            $slugInput.trigger('change');
                        }
                    });
                }
            });

        })(jQuery);
        </script>
        <?php
    }

    /**
     * Set ACF JSON save point
     */
    public function acf_json_save_point($path) {
        return QUOTATION_FORM_PLUGIN_DIR . 'acf-json';
    }

    /**
     * Set ACF JSON load point
     */
    public function acf_json_load_point($paths) {
        unset($paths[0]);
        $paths[] = QUOTATION_FORM_PLUGIN_DIR . 'acf-json';
        return $paths;
    }

    /**
     * Enqueue scripts and styles for the form
     */
    private function enqueue_form_assets() {
        // Enqueue CSS
        wp_enqueue_style(
            'quotation-form-css',
            QUOTATION_FORM_PLUGIN_URL . 'assets/css/quotation-form.css',
            array(),
            QUOTATION_FORM_VERSION
        );

        // Enqueue JavaScript
        wp_enqueue_script(
            'quotation-form-js',
            QUOTATION_FORM_PLUGIN_URL . 'assets/js/quotation-form.js',
            array('jquery'),
            QUOTATION_FORM_VERSION,
            true
        );

        // Get ACF config data (with fallbacks to hardcoded defaults)
        $product_types = $this->get_acf_field_or_default('product_types', 'option');
        $materials = $this->get_acf_field_or_default('materials', 'option');
        $styles = $this->get_acf_field_or_default('styles', 'option');

        // Group types by category for backwards compatibility
        $types_by_category = $this->group_types_by_category($product_types);

        $config = array(
            'categories' => $this->get_acf_field_or_default('product_categories', 'option'),
            'productTypes' => $product_types,
            'windowTypes' => $types_by_category['windows'],
            'doorTypes' => $types_by_category['doors'],
            'bayTypes' => $types_by_category['bay-windows'],
            'materials' => $materials,
            'styles' => $styles,
            'colours' => $this->get_acf_field_or_default('colours', 'option'),
            'useAcfData' => function_exists('get_field') && get_field('product_categories', 'option') ? true : false
        );

        // Localize script for AJAX
        wp_localize_script('quotation-form-js', 'quotationFormAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('quotation_form_nonce'),
            'pluginUrl' => QUOTATION_FORM_PLUGIN_URL,
            'config' => $config
        ));
    }

    /**
     * Get ACF field or return empty array
     */
    private function get_acf_field_or_default($field_name, $post_id) {
        if (function_exists('get_field')) {
            $value = get_field($field_name, $post_id);
            return $value ? $value : array();
        }
        return array();
    }

    /**
     * Group product types by category
     */
    private function group_types_by_category($product_types) {
        $grouped = array(
            'windows' => array(),
            'doors' => array(),
            'bay-windows' => array()
        );

        if (empty($product_types) || !is_array($product_types)) {
            return $grouped;
        }

        foreach ($product_types as $type) {
            $category = isset($type['category']) ? $type['category'] : 'windows';
            if (isset($grouped[$category])) {
                $grouped[$category][] = $type;
            }
        }

        return $grouped;
    }

    /**
     * Render form via shortcode
     * Usage: [quotation_form]
     */
    public function render_form_shortcode($atts) {
        $atts = shortcode_atts(array(
            'title' => '',
        ), $atts);

        // Enqueue assets when shortcode is called
        $this->enqueue_form_assets();

        ob_start();
        include QUOTATION_FORM_PLUGIN_DIR . 'templates/form-template.php';
        return ob_get_clean();
    }

    /**
     * Handle AJAX form submission
     */
    public function handle_form_submission() {
        check_ajax_referer('quotation_form_nonce', 'nonce');

        $basket_items = isset($_POST['basket_items']) ? json_decode(stripslashes($_POST['basket_items']), true) : array();
        $customer_data = isset($_POST['customer_data']) ? $_POST['customer_data'] : array();

        // Sanitize customer data
        $customer_data = array_map('sanitize_text_field', $customer_data);

        // Save to quotation CPT
        $post_id = $this->save_to_quotation_cpt($basket_items, $customer_data);

        // Send email notification
        $this->send_email_notification($basket_items, $customer_data, $post_id);

        wp_send_json_success(array(
            'message' => 'Quotation request submitted successfully!',
            'quotation_id' => $post_id
        ));
    }

    /**
     * Save submission to quotation custom post type
     */
    private function save_to_quotation_cpt($basket_items, $customer_data) {
        // Normalize field names - convert from JS format to ACF format
        $normalized_data = array(
            'customer_name' => isset($customer_data['name']) ? $customer_data['name'] :
                              (isset($customer_data['customer_name']) ? $customer_data['customer_name'] : ''),
            'customer_email' => isset($customer_data['email']) ? $customer_data['email'] :
                               (isset($customer_data['customer_email']) ? $customer_data['customer_email'] : ''),
            'customer_phone' => isset($customer_data['phone']) ? $customer_data['phone'] :
                               (isset($customer_data['customer_phone']) ? $customer_data['customer_phone'] : ''),
            'customer_address' => isset($customer_data['address']) ? $customer_data['address'] :
                                 (isset($customer_data['customer_address']) ? $customer_data['customer_address'] : ''),
            'customer_postcode' => isset($customer_data['postcode']) ? $customer_data['postcode'] :
                                  (isset($customer_data['customer_postcode']) ? $customer_data['customer_postcode'] : ''),
            'preferred_contact' => isset($customer_data['preferred_contact']) ? $customer_data['preferred_contact'] : 'email',
            'additional_notes' => isset($customer_data['additional_notes']) ? $customer_data['additional_notes'] : '',
        );

        // Create post in quotation CPT
        $post_id = wp_insert_post(array(
            'post_title'  => $normalized_data['customer_name'] . ' - ' . date('d M Y'),
            'post_type'   => 'quotation',
            'post_status' => 'publish',
        ));

        if (!$post_id || is_wp_error($post_id)) {
            return false;
        }

        // Normalize basket items - convert from JS camelCase to ACF snake_case
        $normalized_basket_items = $this->normalize_basket_items($basket_items);

        // Save customer data to ACF fields (if ACF is available)
        if (function_exists('update_field')) {
            update_field('customer_name', $normalized_data['customer_name'], $post_id);
            update_field('customer_email', $normalized_data['customer_email'], $post_id);
            update_field('customer_phone', $normalized_data['customer_phone'], $post_id);
            update_field('customer_address', $normalized_data['customer_address'], $post_id);
            update_field('customer_postcode', $normalized_data['customer_postcode'], $post_id);
            update_field('preferred_contact', $normalized_data['preferred_contact'], $post_id);
            update_field('additional_notes', $normalized_data['additional_notes'], $post_id);
            update_field('submission_date', current_time('Y-m-d H:i:s'), $post_id);

            // Save normalized basket items
            update_field('basket_items', $normalized_basket_items, $post_id);

            // Set default quote status
            update_field('quote_status', 'pending', $post_id);
        } else {
            // Fallback: Save as post meta if ACF not available
            update_post_meta($post_id, 'customer_data', $normalized_data);
            update_post_meta($post_id, 'basket_items', $normalized_basket_items);
            update_post_meta($post_id, 'submission_date', current_time('mysql'));
        }

        return $post_id;
    }

    /**
     * Normalize basket items from JS camelCase to ACF snake_case
     */
    private function normalize_basket_items($basket_items) {
        $normalized = array();

        foreach ($basket_items as $item) {
            $normalized[] = array(
                'category' => isset($item['category']) ? $item['category'] : '',
                'type_name' => isset($item['typeName']) ? $item['typeName'] : '',
                'material_name' => isset($item['materialName']) ? $item['materialName'] : '',
                'style_name' => isset($item['styleName']) ? $item['styleName'] : '',
                'width' => isset($item['width']) ? $item['width'] : '',
                'height' => isset($item['height']) ? $item['height'] : '',
                'cill' => isset($item['cill']) ? $item['cill'] : '',
                'inside_colour' => isset($item['insideColour']) ? $item['insideColour'] : '',
                'outside_colour' => isset($item['outsideColour']) ? $item['outsideColour'] : '',
                'glazing_type' => isset($item['glazingType']) ? $item['glazingType'] : '',
                'glazing_features' => isset($item['glazingFeatures']) ? $item['glazingFeatures'] : '',
                'hardware_colour' => isset($item['hardwareColour']) ? $item['hardwareColour'] : '',
                'location' => isset($item['location']) ? $item['location'] : '',
            );
        }

        return $normalized;
    }

    /**
     * Send email notification
     */
    private function send_email_notification($basket_items, $customer_data, $post_id = null) {
        // Normalize field names - convert from JS format to display format
        $normalized_data = array(
            'Name' => isset($customer_data['name']) ? $customer_data['name'] :
                     (isset($customer_data['customer_name']) ? $customer_data['customer_name'] : ''),
            'Email' => isset($customer_data['email']) ? $customer_data['email'] :
                      (isset($customer_data['customer_email']) ? $customer_data['customer_email'] : ''),
            'Phone' => isset($customer_data['phone']) ? $customer_data['phone'] :
                      (isset($customer_data['customer_phone']) ? $customer_data['customer_phone'] : ''),
            'Address' => isset($customer_data['address']) ? $customer_data['address'] :
                        (isset($customer_data['customer_address']) ? $customer_data['customer_address'] : ''),
            'Postcode' => isset($customer_data['postcode']) ? $customer_data['postcode'] :
                         (isset($customer_data['customer_postcode']) ? $customer_data['customer_postcode'] : ''),
            'Preferred Contact' => isset($customer_data['preferred_contact']) ? $customer_data['preferred_contact'] : 'email',
            'Additional Notes' => isset($customer_data['additional_notes']) ? $customer_data['additional_notes'] : '',
        );

        // Get email settings from ACF or use defaults
        $email_recipients = function_exists('get_field') ? get_field('email_recipients', 'option') : '';
        $email_subject = function_exists('get_field') ? get_field('email_subject', 'option') : '';
        $send_customer_confirmation = function_exists('get_field') ? get_field('send_customer_confirmation', 'option') : true;

        // Parse recipients (one per line)
        if (!empty($email_recipients)) {
            $recipients = array_filter(array_map('trim', explode("\n", $email_recipients)));
            $admin_email = $recipients;
        } else {
            $admin_email = get_option('admin_email');
        }

        $subject = !empty($email_subject) ? $email_subject : 'New Quotation Request from ' . $normalized_data['Name'];

        $message = "New quotation request received:\n\n";
        $message .= "=== CUSTOMER DETAILS ===\n\n";

        foreach ($normalized_data as $label => $value) {
            if (!empty($value)) {
                $message .= $label . ": " . $value . "\n";
            }
        }

        $message .= "\n\n=== ITEMS (" . count($basket_items) . ") ===\n\n";

        foreach ($basket_items as $index => $item) {
            $message .= "--- Item " . ($index + 1) . " ---\n";
            $message .= "Category: " . ucfirst($item['category']) . "\n";
            $message .= "Type: " . $item['typeName'] . "\n";

            if (isset($item['materialName']) && $item['materialName'] !== 'N/A') {
                $message .= "Material: " . $item['materialName'] . "\n";
            }

            $message .= "Style: " . $item['styleName'] . "\n";
            $message .= "Dimensions: " . $item['width'] . "mm (W) x " . $item['height'] . "mm (H)\n";
            $message .= "Cill: " . $item['cill'] . "\n";
            $message .= "Inside Colour: " . $item['insideColour'] . "\n";
            $message .= "Outside Colour: " . $item['outsideColour'] . "\n";
            $message .= "Glazing Type: " . ucfirst($item['glazingType']) . "\n";
            $message .= "Glazing Features: " . ucfirst($item['glazingFeatures']) . "\n";
            $message .= "Hardware Colour: " . ucfirst($item['hardwareColour']) . "\n";

            if (!empty($item['location'])) {
                $message .= "Location: " . $item['location'] . "\n";
            }

            $message .= "\n";
        }

        $message .= "\n---\nThis email was sent from the quotation form on " . get_bloginfo('name') . "\n";
        $message .= "Submitted: " . current_time('mysql') . "\n";

        // Add link to view quotation in admin (if post_id provided)
        if ($post_id) {
            $edit_link = admin_url('post.php?post=' . $post_id . '&action=edit');
            $message .= "\nView quotation in admin: " . $edit_link . "\n";
        }

        // Send email
        wp_mail($admin_email, $subject, $message);

        // Send confirmation email to customer (if enabled)
        $customer_email = isset($customer_data['email']) ? $customer_data['email'] :
                         (isset($customer_data['customer_email']) ? $customer_data['customer_email'] : '');
        $customer_name = isset($customer_data['name']) ? $customer_data['name'] :
                        (isset($customer_data['customer_name']) ? $customer_data['customer_name'] : '');

        if ($send_customer_confirmation && !empty($customer_email)) {
            $customer_subject = 'Thank you for your quotation request';
            $customer_message = "Dear " . $customer_name . ",\n\n";
            $customer_message .= "Thank you for requesting a quotation. We have received your request and will get back to you shortly.\n\n";
            $customer_message .= "Items requested: " . count($basket_items) . "\n\n";

            // Add detailed item information
            foreach ($basket_items as $index => $item) {
                $customer_message .= "--- Item " . ($index + 1) . " ---\n";
                $customer_message .= "Category: " . ucfirst($item['category']) . "\n";
                $customer_message .= "Type: " . $item['typeName'] . "\n";

                if (isset($item['materialName']) && $item['materialName'] !== 'N/A') {
                    $customer_message .= "Material: " . $item['materialName'] . "\n";
                }

                $customer_message .= "Style: " . $item['styleName'] . "\n";
                $customer_message .= "Dimensions: " . $item['width'] . "mm (W) x " . $item['height'] . "mm (H)\n";
                $customer_message .= "Cill: " . $item['cill'] . "\n";
                $customer_message .= "Inside Colour: " . $item['insideColour'] . "\n";
                $customer_message .= "Outside Colour: " . $item['outsideColour'] . "\n";
                $customer_message .= "Glazing Type: " . ucfirst($item['glazingType']) . "\n";
                $customer_message .= "Glazing Features: " . ucfirst($item['glazingFeatures']) . "\n";
                $customer_message .= "Hardware Colour: " . ucfirst($item['hardwareColour']) . "\n";

                if (!empty($item['location'])) {
                    $customer_message .= "Location: " . $item['location'] . "\n";
                }

                $customer_message .= "\n";
            }

            $customer_message .= "Best regards,\n";
            $customer_message .= get_bloginfo('name') . " & Doors";

            wp_mail($customer_email, $customer_subject, $customer_message);
        }
    }

    /**
     * Auto-calculate quote price from item prices
     */
    public function auto_calculate_quote_price($post_id) {
        // Only run for quotation post type
        if (get_post_type($post_id) !== 'quotation') {
            return;
        }

        // Avoid infinite loops
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Get basket items
        $basket_items = get_field('basket_items', $post_id);

        if (empty($basket_items) || !is_array($basket_items)) {
            return;
        }

        // Calculate total price
        $total = 0;
        foreach ($basket_items as $item) {
            if (isset($item['item_price']) && is_numeric($item['item_price'])) {
                $total += floatval($item['item_price']);
            }
        }

        // Update quote price field
        update_field('quote_price', $total, $post_id);
    }

    /**
     * Generate quote PDF
     */
    public function generate_quote_pdf($post_id) {
        // Only run for quotation post type
        if (get_post_type($post_id) !== 'quotation') {
            error_log("PDF Generation: Not a quotation post type - " . get_post_type($post_id));
            return;
        }

        // Avoid infinite loops and autosaves
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            error_log("PDF Generation: Skipping autosave for post $post_id");
            return;
        }

        // Don't run on revisions
        if (wp_is_post_revision($post_id)) {
            error_log("PDF Generation: Skipping revision for post $post_id");
            return;
        }

        // Don't run if this is just a draft
        $post_status = get_post_status($post_id);
        if ($post_status === 'auto-draft' || $post_status === 'trash') {
            error_log("PDF Generation: Skipping post with status: $post_status");
            return;
        }

        error_log("PDF Generation: Starting PDF generation check for post $post_id");

        // Get basket items - these should be saved now since we're at priority 25
        $basket_items = get_field('basket_items', $post_id);

        if (empty($basket_items)) {
            error_log("PDF Generation: No basket items found for post $post_id - skipping PDF generation");
            return;
        }

        error_log("PDF Generation: Found " . count($basket_items) . " basket items");

        // Check if at least one item exists (don't require prices - admin might add them later)
        // Generate PDF whenever there are basket items, even if prices aren't set yet
        $has_valid_items = !empty($basket_items) && is_array($basket_items);

        if (!$has_valid_items) {
            error_log("PDF Generation: No valid items found for post $post_id");
            return;
        }

        error_log("PDF Generation: Starting PDF generation for post $post_id");

        // Get customer data
        $customer_name = get_field('customer_name', $post_id);
        $customer_email = get_field('customer_email', $post_id);
        $customer_phone = get_field('customer_phone', $post_id);
        $customer_address = get_field('customer_address', $post_id);
        $customer_postcode = get_field('customer_postcode', $post_id);
        $quote_price = get_field('quote_price', $post_id);

        // Generate and save PDF file
        $data = array(
            'customer_name' => $customer_name,
            'customer_email' => $customer_email,
            'customer_phone' => $customer_phone,
            'customer_address' => $customer_address,
            'customer_postcode' => $customer_postcode,
            'basket_items' => $basket_items,
            'quote_price' => $quote_price
        );

        // Generate PDF file and save it
        $pdf_url = $this->save_pdf_file($post_id, $data);

        // Update the PDF URL field with the actual file URL
        // Use remove_action to prevent infinite loop
        remove_action('acf/save_post', array($this, 'generate_quote_pdf'), 25);

        if ($pdf_url) {
            update_field('quote_pdf_url', $pdf_url, $post_id);
            error_log("PDF Generation: Successfully generated PDF for post $post_id. URL: $pdf_url");
        } else {
            error_log("PDF Generation: Failed to generate PDF for post $post_id");
        }

        // Re-add the action for next time
        add_action('acf/save_post', array($this, 'generate_quote_pdf'), 25);
    }

    /**
     * Generate PDF content
     */
    private function generate_pdf_content($post_id, $data) {
        ob_start();
        include QUOTATION_FORM_PLUGIN_DIR . 'templates/pdf-template.php';
        return ob_get_clean();
    }

    /**
     * Save PDF file to uploads directory
     */
    private function save_pdf_file($post_id, $data) {
        // Check if TCPDF is available
        if (!class_exists('TCPDF')) {
            error_log("PDF Generation: TCPDF class not found for post $post_id");
            return false;
        }

        error_log("PDF Generation: TCPDF class found, creating PDF for post $post_id");

        try {
            // Create new PDF document
            $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('Cristal Windows');
        $pdf->SetAuthor('Cristal Windows, Doors & Conservatories Ltd');
        $pdf->SetTitle('Quotation - ' . $data['customer_name']);
        $pdf->SetSubject('Quotation');

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);

        // Set font
        $pdf->SetFont('helvetica', '', 10);

        // Add a page
        $pdf->AddPage();

        // Get HTML content
        $html = $this->generate_pdf_content($post_id, $data);

        // Output the HTML content
        $pdf->writeHTML($html, true, false, true, false, '');

            // Create uploads directory for quotes if it doesn't exist
            $upload_dir = wp_upload_dir();
            $quotes_dir = $upload_dir['basedir'] . '/quotes';
            $quotes_url = $upload_dir['baseurl'] . '/quotes';

            error_log("PDF Generation: Upload dir basedir: " . $upload_dir['basedir']);
            error_log("PDF Generation: Quotes directory: $quotes_dir");

            if (!file_exists($quotes_dir)) {
                wp_mkdir_p($quotes_dir);
                error_log("PDF Generation: Created quotes directory: $quotes_dir");
            }

            // Generate filename
            $filename = 'quote-' . $post_id . '-' . sanitize_title($data['customer_name']) . '.pdf';
            $file_path = $quotes_dir . '/' . $filename;

            error_log("PDF Generation: Saving PDF to: $file_path");

            // Save PDF to file
            $pdf->Output($file_path, 'F');

            if (file_exists($file_path)) {
                error_log("PDF Generation: PDF file successfully created at: $file_path");
            } else {
                error_log("PDF Generation: ERROR - PDF file not found after Output() call: $file_path");
            }

            // Return the URL to the PDF file
            return $quotes_url . '/' . $filename;
        } catch (Exception $e) {
            error_log("PDF Generation: Exception occurred for post $post_id - " . $e->getMessage());
            error_log("PDF Generation: Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Handle PDF download via AJAX
     */
    public function handle_pdf_download() {
        $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
        $nonce = isset($_GET['nonce']) ? $_GET['nonce'] : '';

        // Verify nonce
        if (!wp_verify_nonce($nonce, 'download_quote_pdf_' . $post_id)) {
            wp_die('Security check failed');
        }

        // Verify post exists and is a quotation
        if (get_post_type($post_id) !== 'quotation') {
            wp_die('Invalid quotation');
        }

        // Get data
        $customer_name = get_field('customer_name', $post_id);
        $customer_email = get_field('customer_email', $post_id);
        $customer_phone = get_field('customer_phone', $post_id);
        $customer_address = get_field('customer_address', $post_id);
        $customer_postcode = get_field('customer_postcode', $post_id);
        $basket_items = get_field('basket_items', $post_id);
        $quote_price = get_field('quote_price', $post_id);

        $data = array(
            'customer_name' => $customer_name,
            'customer_email' => $customer_email,
            'customer_phone' => $customer_phone,
            'customer_address' => $customer_address,
            'customer_postcode' => $customer_postcode,
            'basket_items' => $basket_items,
            'quote_price' => $quote_price
        );

        // Check if TCPDF is available
        if (class_exists('TCPDF')) {
            $this->generate_pdf_with_tcpdf($post_id, $data);
        } else {
            // Fallback: Output HTML that can be printed as PDF
            $this->output_printable_quote($post_id, $data);
        }
    }

    /**
     * Generate PDF using TCPDF library
     */
    private function generate_pdf_with_tcpdf($post_id, $data) {
        // Create new PDF document
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('Cristal Windows');
        $pdf->SetAuthor('Cristal Windows, Doors & Conservatories Ltd');
        $pdf->SetTitle('Quotation - ' . $data['customer_name']);
        $pdf->SetSubject('Quotation');

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);

        // Set font
        $pdf->SetFont('helvetica', '', 10);

        // Add a page
        $pdf->AddPage();

        // Get HTML content
        $html = $this->generate_pdf_content($post_id, $data);

        // Output the HTML content
        $pdf->writeHTML($html, true, false, true, false, '');

        // Close and output PDF document
        $filename = 'quote-' . $post_id . '-' . sanitize_title($data['customer_name']) . '.pdf';
        $pdf->Output($filename, 'D');
        exit;
    }

    /**
     * Output printable HTML quote (fallback when no PDF library available)
     */
    private function output_printable_quote($post_id, $data) {
        header('Content-Type: text/html; charset=utf-8');
        echo $this->generate_pdf_content($post_id, $data);
        echo '<script>window.print();</script>';
        exit;
    }

    /**
     * Add custom columns to quotations list
     */
    public function add_quotation_columns($columns) {
        // Remove the date column, we'll add it back later
        $date = $columns['date'];
        unset($columns['date']);

        // Add custom columns
        $columns['quote_status'] = 'Quote Status';
        $columns['quote_price'] = 'Quote Price (£)';
        $columns['quote_sent_date'] = 'Quote Sent Date';
        $columns['follow_up_date'] = 'Follow-up Date';
        $columns['quote_pdf'] = 'Quote PDF';

        // Add date column back at the end
        $columns['date'] = $date;

        return $columns;
    }

    /**
     * Populate custom columns with data
     */
    public function populate_quotation_columns($column, $post_id) {
        switch ($column) {
            case 'quote_status':
                $status = get_field('quote_status', $post_id);
                if ($status) {
                    // Add color coding for different statuses
                    $status_colors = array(
                        'pending' => '#f0ad4e',
                        'sent' => '#5bc0de',
                        'accepted' => '#5cb85c',
                        'rejected' => '#d9534f'
                    );
                    $color = isset($status_colors[$status]) ? $status_colors[$status] : '#777';
                    echo '<span style="display: inline-block; padding: 4px 10px; background-color: ' . esc_attr($color) . '; color: white; border-radius: 3px; font-size: 11px; font-weight: 600;">' . esc_html(ucfirst($status)) . '</span>';
                } else {
                    echo '—';
                }
                break;

            case 'quote_price':
                $price = get_field('quote_price', $post_id);
                if ($price) {
                    echo '<strong>£' . number_format((float)$price, 2) . '</strong>';
                } else {
                    echo '—';
                }
                break;

            case 'quote_sent_date':
                $sent_date = get_field('quote_sent_date', $post_id);
                if ($sent_date) {
                    echo date('d M Y', strtotime($sent_date));
                } else {
                    echo '—';
                }
                break;

            case 'follow_up_date':
                $follow_up = get_field('follow_up_date', $post_id);
                if ($follow_up) {
                    $follow_up_timestamp = strtotime($follow_up);
                    $today = strtotime(date('Y-m-d'));

                    // Highlight if follow-up date is today or overdue
                    if ($follow_up_timestamp <= $today) {
                        echo '<span style="color: #d9534f; font-weight: bold;">' . date('d M Y', $follow_up_timestamp) . '</span>';
                    } else {
                        echo date('d M Y', $follow_up_timestamp);
                    }
                } else {
                    echo '—';
                }
                break;

            case 'quote_pdf':
                $pdf_url = get_field('quote_pdf_url', $post_id);
                $customer_name = get_field('customer_name', $post_id);

                if ($pdf_url) {
                    $quotation_name = $customer_name ? $customer_name : 'Quote #' . $post_id;
                    echo '<a href="' . esc_url($pdf_url) . '" target="_blank" style="text-decoration: none;">';
                    echo '<span style="display: inline-block; padding: 4px 8px; background-color: #0066cc; color: white; border-radius: 3px; font-size: 11px;">';
                    echo '<span class="dashicons dashicons-pdf" style="font-size: 14px; vertical-align: middle; margin-right: 4px;"></span>';
                    echo esc_html($quotation_name);
                    echo '</span></a>';
                } else {
                    echo '—';
                }
                break;
        }
    }

    /**
     * Make columns sortable
     */
    public function sortable_quotation_columns($columns) {
        $columns['quote_price'] = 'quote_price';
        $columns['quote_sent_date'] = 'quote_sent_date';
        $columns['follow_up_date'] = 'follow_up_date';
        $columns['quote_status'] = 'quote_status';
        return $columns;
    }

}

/**
 * Initialize the plugin
 */
function quotation_form_init() {
    return Quotation_Form_Plugin::get_instance();
}

// Start the plugin
add_action('plugins_loaded', 'quotation_form_init');

/**
 * Activation hook
 */
register_activation_hook(__FILE__, 'quotation_form_activate');
function quotation_form_activate() {
    // Create upload directory for form images if needed
    $upload_dir = wp_upload_dir();
    $quotation_form_dir = $upload_dir['basedir'] . '/quotation-form';

    if (!file_exists($quotation_form_dir)) {
        wp_mkdir_p($quotation_form_dir);
    }

    // Flush rewrite rules
    flush_rewrite_rules();
}

/**
 * Deactivation hook
 */
register_deactivation_hook(__FILE__, 'quotation_form_deactivate');
function quotation_form_deactivate() {
    // Flush rewrite rules
    flush_rewrite_rules();
}
