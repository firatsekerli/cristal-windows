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
        add_shortcode('quotation_form', array($this, 'render_form_shortcode'));

        // Populate select field choices dynamically
        add_filter('acf/load_field/key=field_type_category', array($this, 'populate_category_choices'));
        add_filter('acf/load_field/key=field_material_available_types', array($this, 'populate_type_choices'));
        add_filter('acf/load_field/key=field_style_types', array($this, 'populate_type_choices'));

        // Add admin scripts for auto-slug generation
        add_action('acf/input/admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
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
