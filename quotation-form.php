<?php
/**
 * Plugin Name: Multi-Step Quotation Form
 * Plugin URI: https://asparagents.com/plugins/quotation-form/
 * Description: A comprehensive multi-step quotation form for Windows, Doors, and Bay Windows with basket functionality
 * Version: 1.0.0
 * Requires at least: 5.6
 * Tested up to: 6.9
 * Requires PHP: 7.4
 * Author: asparagents
 * Author URI: https://asparagents.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: quotation-form
 * Domain Path: /languages
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

// Load Composer autoloader (includes mPDF)
$composer_autoload = QUOTATION_FORM_PLUGIN_DIR . 'vendor/autoload.php';
if (file_exists($composer_autoload)) {
    require_once $composer_autoload;
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
        add_filter('acf/load_field/key=field_pattern_available_glazing_types', array($this, 'populate_glazing_type_choices'));
        add_filter('acf/load_field/key=field_brand_available_types', array($this, 'populate_type_choices'));
        add_filter('acf/load_field/key=field_colour_available_materials', array($this, 'populate_material_choices'));
        add_filter('acf/load_field/key=field_exclusion_product_type', array($this, 'populate_type_choices'));
        add_filter('acf/load_field/key=field_opening_available_types', array($this, 'populate_type_choices'));
        add_filter('acf/load_field/key=field_panel_available_types', array($this, 'populate_type_choices'));
        add_filter('acf/load_field/key=field_panel_available_materials', array($this, 'populate_material_choices'));
        add_filter('acf/load_field/key=field_item_brand', array($this, 'populate_brand_choices'));
        add_filter('acf/load_field/key=field_centralized_brand', array($this, 'populate_brand_choices'));
        add_filter('acf/load_field/key=field_item_services', array($this, 'populate_service_choices'));
        add_filter('acf/load_field/key=field_centralized_services', array($this, 'populate_service_choices'));

        // Populate basket item dropdowns dynamically from settings
        add_filter('acf/load_field/key=field_item_category', array($this, 'populate_basket_item_category_choices'));
        add_filter('acf/load_field/key=field_item_type', array($this, 'populate_basket_item_type_choices'));
        add_filter('acf/load_field/key=field_item_material', array($this, 'populate_basket_item_material_choices'));
        add_filter('acf/load_field/key=field_item_cill', array($this, 'populate_basket_item_cill_choices'));
        add_filter('acf/load_field/key=field_item_glazing_type', array($this, 'populate_basket_item_glazing_type_choices'));
        add_filter('acf/load_field/key=field_item_glazing_patterns', array($this, 'populate_basket_item_glazing_pattern_choices'));
        add_filter('acf/load_field/key=field_item_glazing_features', array($this, 'populate_basket_item_glazing_feature_choices'));
        add_filter('acf/load_field/key=field_item_hardware_colour', array($this, 'populate_basket_item_hardware_colour_choices'));

        // Add admin scripts for auto-slug generation
        add_action('acf/input/admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

        // Localize settings data for admin JavaScript
        add_action('admin_enqueue_scripts', array($this, 'enqueue_settings_data'));

        // Auto-calculate quote price when quotation is saved
        add_action('acf/save_post', array($this, 'auto_calculate_quote_price'), 20);

        // Generate PDF when quotation is saved
        add_action('acf/save_post', array($this, 'generate_quote_pdf'), 25);

        // Add custom admin columns
        add_filter('manage_quotation_posts_columns', array($this, 'add_quotation_columns'));
        add_action('manage_quotation_posts_custom_column', array($this, 'populate_quotation_columns'), 10, 2);
        add_filter('manage_edit-quotation_sortable_columns', array($this, 'sortable_quotation_columns'));

        // Add manual PDF generation button in admin
        add_action('admin_notices', array($this, 'show_pdf_generation_notices'));
        add_action('admin_post_generate_quote_pdf', array($this, 'handle_manual_pdf_generation'));
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
     * Populate glazing type choices for patterns
     */
    public function populate_glazing_type_choices($field) {
        $field['choices'] = array();

        // Get all glazing types
        if (function_exists('get_field')) {
            $glazing_types = get_field('glazing_types', 'option');
            if (!empty($glazing_types) && is_array($glazing_types)) {
                foreach ($glazing_types as $type) {
                    $value = isset($type['value']) ? $type['value'] : '';
                    $label = isset($type['label']) ? $type['label'] : '';

                    if ($value && $label) {
                        $field['choices'][$value] = $label;
                    }
                }
            }
        }

        return $field;
    }

    /**
     * Populate material choices for colour availability
     */
    public function populate_material_choices($field) {
        $field['choices'] = array();

        // Get all materials from settings
        if (function_exists('get_field')) {
            $materials = get_field('materials', 'option');
            if (!empty($materials) && is_array($materials)) {
                foreach ($materials as $material) {
                    $slug = isset($material['slug']) ? $material['slug'] : '';
                    $name = isset($material['name']) ? $material['name'] : '';

                    if ($slug && $name) {
                        $field['choices'][$slug] = $name;
                    }
                }
            }
        }

        return $field;
    }

    /**
     * Populate brand choices for quotation items and centralized brand
     */
    public function populate_brand_choices($field) {
        $field['choices'] = array();

        // Get all brands from settings
        if (function_exists('get_field')) {
            $brands = get_field('brands', 'option');
            if (!empty($brands) && is_array($brands)) {
                foreach ($brands as $brand) {
                    $slug = isset($brand['slug']) ? $brand['slug'] : '';
                    $name = isset($brand['name']) ? $brand['name'] : '';

                    if ($slug && $name) {
                        $field['choices'][$slug] = $name;
                    }
                }
            }
        }

        return $field;
    }

    /**
     * Populate service choices for quotation items and centralized services
     */
    public function populate_service_choices($field) {
        $field['choices'] = array();

        // Get all services from settings
        if (function_exists('get_field')) {
            $services = get_field('services', 'option');
            if (!empty($services) && is_array($services)) {
                foreach ($services as $service) {
                    $slug = isset($service['slug']) ? $service['slug'] : '';
                    $name = isset($service['name']) ? $service['name'] : '';

                    if ($slug && $name) {
                        $field['choices'][$slug] = $name;
                    }
                }
            }
        }

        return $field;
    }

    /**
     * Populate basket item category dropdown with values from settings
     */
    public function populate_basket_item_category_choices($field) {
        $field['choices'] = array();

        // Get product categories from settings
        if (function_exists('get_field')) {
            $categories = get_field('product_categories', 'option');
            if (!empty($categories) && is_array($categories)) {
                foreach ($categories as $category) {
                    $name = isset($category['name']) ? $category['name'] : '';

                    if ($name) {
                        // Use the name as both key and value for display consistency
                        $field['choices'][$name] = $name;
                    }
                }
            }
        }

        return $field;
    }

    /**
     * Populate basket item type dropdown with values from settings
     */
    public function populate_basket_item_type_choices($field) {
        $field['choices'] = array();

        // Get product types from settings
        if (function_exists('get_field')) {
            $types = get_field('product_types', 'option');
            if (!empty($types) && is_array($types)) {
                foreach ($types as $type) {
                    $name = isset($type['name']) ? $type['name'] : '';

                    if ($name) {
                        // Use the name as both key and value for display consistency
                        $field['choices'][$name] = $name;
                    }
                }
            }
        }

        return $field;
    }

    /**
     * Populate basket item material dropdown with values from settings
     */
    public function populate_basket_item_material_choices($field) {
        $field['choices'] = array();

        // Get materials from settings
        if (function_exists('get_field')) {
            $materials = get_field('materials', 'option');
            if (!empty($materials) && is_array($materials)) {
                foreach ($materials as $material) {
                    $name = isset($material['name']) ? $material['name'] : '';

                    if ($name) {
                        // Use the name as both key and value for display consistency
                        $field['choices'][$name] = $name;
                    }
                }
            }
        }

        return $field;
    }

    /**
     * Populate basket item cill dropdown with values from settings
     */
    public function populate_basket_item_cill_choices($field) {
        $field['choices'] = array();

        // Get cill options from settings
        if (function_exists('get_field')) {
            $cill_options = get_field('cill_options', 'option');
            if (!empty($cill_options) && is_array($cill_options)) {
                foreach ($cill_options as $cill) {
                    $label = isset($cill['label']) ? $cill['label'] : '';

                    if ($label) {
                        // Use the label as both key and value for display consistency
                        $field['choices'][$label] = $label;
                    }
                }
            }
        }

        return $field;
    }

    /**
     * Populate basket item glazing type dropdown with values from settings
     */
    public function populate_basket_item_glazing_type_choices($field) {
        $field['choices'] = array();

        // Get glazing types from settings
        if (function_exists('get_field')) {
            $glazing_types = get_field('glazing_types', 'option');
            if (!empty($glazing_types) && is_array($glazing_types)) {
                foreach ($glazing_types as $type) {
                    $label = isset($type['label']) ? $type['label'] : '';

                    if ($label) {
                        // Use the label as both key and value for display consistency
                        $field['choices'][$label] = $label;
                    }
                }
            }
        }

        return $field;
    }

    /**
     * Populate basket item glazing pattern dropdown with values from settings
     */
    public function populate_basket_item_glazing_pattern_choices($field) {
        $field['choices'] = array();

        // Get glazing patterns from settings
        if (function_exists('get_field')) {
            $patterns = get_field('patterns', 'option');
            if (!empty($patterns) && is_array($patterns)) {
                foreach ($patterns as $pattern) {
                    $name = isset($pattern['name']) ? $pattern['name'] : '';

                    if ($name) {
                        // Use the name as both key and value for display consistency
                        $field['choices'][$name] = $name;
                    }
                }
            }
        }

        return $field;
    }

    /**
     * Populate basket item glazing feature dropdown with values from settings
     */
    public function populate_basket_item_glazing_feature_choices($field) {
        $field['choices'] = array();

        // Get glazing features from settings
        if (function_exists('get_field')) {
            $glazing_features = get_field('glazing_features', 'option');
            if (!empty($glazing_features) && is_array($glazing_features)) {
                foreach ($glazing_features as $feature) {
                    $name = isset($feature['name']) ? $feature['name'] : '';

                    if ($name) {
                        // Use the name as both key and value for display consistency
                        $field['choices'][$name] = $name;
                    }
                }
            }
        }

        return $field;
    }

    /**
     * Populate basket item hardware colour dropdown with values from settings
     */
    public function populate_basket_item_hardware_colour_choices($field) {
        $field['choices'] = array();

        // Get hardware colours from settings
        if (function_exists('get_field')) {
            $hardware_colours = get_field('hardware_colours', 'option');
            if (!empty($hardware_colours) && is_array($hardware_colours)) {
                foreach ($hardware_colours as $colour) {
                    $label = isset($colour['label']) ? $colour['label'] : '';

                    if ($label) {
                        // Use the label as both key and value for display consistency
                        $field['choices'][$label] = $label;
                    }
                }
            }
        }

        return $field;
    }

    /**
     * Enqueue settings data for JavaScript
     * Passes styles data to admin JavaScript for style image lookup
     */
    public function enqueue_settings_data() {
        // Only on quotation edit pages
        $screen = get_current_screen();
        if ($screen && $screen->post_type === 'quotation') {
            // Get all styles from settings
            $styles = array();
            if (function_exists('get_field')) {
                $styles_data = get_field('styles', 'option');
                if (!empty($styles_data) && is_array($styles_data)) {
                    foreach ($styles_data as $style) {
                        $styles[] = array(
                            'name' => isset($style['name']) ? $style['name'] : '',
                            'slug' => isset($style['slug']) ? $style['slug'] : '',
                            'image_url' => isset($style['image']['url']) ? $style['image']['url'] : ''
                        );
                    }
                }
            }

            // Pass to JavaScript
            wp_localize_script('jquery', 'quotationSettings', array(
                'styles' => $styles
            ));
        }
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
        /* Style all images in Quotation Form Settings repeaters to 75x75px */
        .acf-options-page .acf-repeater img,
        body[class*="quotation-form-settings"] .acf-repeater img {
            width: 75px !important;
            height: 75px !important;
            object-fit: cover !important;
            display: block;
        }
        /* Hide style_image text input and show custom image preview */
        .acf-field[data-name="style_image"] input[type="text"] {
            display: none !important;
        }
        .acf-field[data-name="style_image"] .acf-input {
            min-height: auto;
        }
        .style-image-preview-container {
            margin-top: 5px;
        }
        .style-image-preview-container img {
            max-width: 150px;
            height: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
            background: white;
            display: block;
        }
        .style-image-preview-container .no-image {
            color: #999;
            font-style: italic;
            font-size: 13px;
        }
        /* Move publish box lower on settings pages to give more room for columns */
        /* Only apply to quotation form settings page */
        body.toplevel_page_quotation-form-settings #poststuff #post-body.columns-2 {
            margin-right: 0 !important;
            display: flex;
            flex-direction: column-reverse;
        }

        body.toplevel_page_quotation-form-settings #post-body.columns-2 #postbox-container-1 {
            float: none !important;
            margin-right: 0 !important;
            width: 280px;
            margin-left: auto;
            min-height: unset !important;
        }
        </style>
        <script type="text/javascript">
        // Wait for jQuery to be ready
        (function() {
            // Check if jQuery is loaded
            if (typeof jQuery === 'undefined') {
                console.error('jQuery is not loaded yet, waiting...');
                // Try again in 100ms
                setTimeout(arguments.callee, 100);
                return;
            }

            // jQuery is loaded, proceed
            (function($) {
                console.log('jQuery loaded, initializing view image buttons script');

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

                // Add installation price
                var installationPrice = parseFloat($('.acf-field[data-name="installation_price"] input').val()) || 0;
                total += installationPrice;

                // Add rubbish removal cost
                var rubbishCost = parseFloat($('.acf-field[data-name="rubbish_removal_cost"] input').val()) || 0;
                total += rubbishCost;

                // Add trims & accessories price
                var trimsPrice = parseFloat($('.acf-field[data-name="trims_accessories_price"] input').val()) || 0;
                total += trimsPrice;

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

                // Recalculate when additional price fields change (use input event for real-time updates)
                $(document).on('input change', '.acf-field[data-name="installation_price"] input', calculateQuotePrice);
                $(document).on('input change', '.acf-field[data-name="rubbish_removal_cost"] input', calculateQuotePrice);
                $(document).on('input change', '.acf-field[data-name="trims_accessories_price"] input', calculateQuotePrice);

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

            // Display style images in basket items
            function displayStyleImages() {
                $('.acf-field[data-name="style_image"]').each(function() {
                    var $field = $(this);
                    var $input = $field.find('input[type="text"]');
                    var $acfInput = $field.find('.acf-input');

                    // Check if preview container already exists
                    if ($field.find('.style-image-preview-container').length > 0) {
                        return; // Already processed
                    }

                    // Get the saved style_image URL from the input
                    var styleImageUrl = $input.val();

                    // If no URL is saved, try to look it up from settings based on style_name
                    if (!styleImageUrl) {
                        var $row = $field.closest('.acf-row');
                        var $styleNameField = $row.find('.acf-field[data-name="style_name"] input');
                        var styleName = $styleNameField.val();

                        if (styleName && typeof quotationSettings !== 'undefined' && quotationSettings.styles) {
                            // Look up the style by name
                            for (var i = 0; i < quotationSettings.styles.length; i++) {
                                if (quotationSettings.styles[i].name === styleName) {
                                    styleImageUrl = quotationSettings.styles[i].image_url;
                                    break;
                                }
                            }
                        }
                    }

                    // Create preview container
                    var $previewContainer = $('<div class="style-image-preview-container"></div>');

                    if (styleImageUrl) {
                        $previewContainer.html('<img src="' + styleImageUrl + '" alt="Style Image">');
                    } else {
                        $previewContainer.html('<p class="no-image">No style image available</p>');
                    }

                    // Append preview after the input
                    $acfInput.append($previewContainer);
                });
            }

            // Add "View Image" button to attached_image fields
            function addViewImageButtons() {
                console.log('Adding view image buttons...');

                // Target all attached_image fields (including those in repeaters)
                var $fields = $('.acf-field[data-name="attached_image"]');
                console.log('Found ' + $fields.length + ' attached_image fields');

                $fields.each(function() {
                    var $field = $(this);

                    // Skip if button already exists
                    if ($field.find('.view-image-button').length) {
                        console.log('Button already exists, skipping');
                        return;
                    }

                    // Try to find the image - check multiple possible structures
                    var $img = $field.find('img').first();

                    if ($img.length && $img.attr('src')) {
                        var imageUrl = $img.attr('src');
                        console.log('Found image:', imageUrl);

                        // Get the full size URL (remove any size suffixes)
                        var fullImageUrl = imageUrl.replace(/-\d+x\d+(\.\w+)$/, '$1');

                        // Create button
                        var $button = $('<a href="' + fullImageUrl + '" target="_blank" class="button view-image-button" style="margin-top: 10px; display: inline-block;">🖼️ View Full Image</a>');

                        // Try multiple insertion points
                        var $input = $field.find('.acf-input');
                        if ($input.length) {
                            $input.append($button);
                            console.log('Button added to .acf-input');
                        } else {
                            $field.append($button);
                            console.log('Button added to field');
                        }
                    } else {
                        console.log('No image found in field');
                        // Remove button if it exists but no image
                        $field.find('.view-image-button').remove();
                    }
                });
            }

            // Run on multiple events to ensure button is added
            $(document).ready(function() {
                console.log('Document ready, scheduling button addition');
                setTimeout(addViewImageButtons, 500);
                setTimeout(addViewImageButtons, 1000);
                setTimeout(addViewImageButtons, 2000);
                // Also display style images
                setTimeout(displayStyleImages, 500);
                setTimeout(displayStyleImages, 1000);
                setTimeout(displayStyleImages, 2000);
            });

            // Run when ACF is ready
            if (typeof acf !== 'undefined') {
                console.log('ACF detected, adding action handlers');

                acf.addAction('ready', function() {
                    console.log('ACF ready event');
                    addViewImageButtons();
                    displayStyleImages();
                });

                acf.addAction('load', function() {
                    console.log('ACF load event');
                    addViewImageButtons();
                    displayStyleImages();
                });

                // Handle repeater row additions
                acf.addAction('append', function($el) {
                    console.log('ACF append event');
                    setTimeout(addViewImageButtons, 100);
                    setTimeout(displayStyleImages, 100);
                });

                // Handle when images are added/changed
                acf.addAction('change', function($el) {
                    setTimeout(addViewImageButtons, 100);
                });
            }

            })(jQuery); // End of jQuery wrapper

        })(); // End of jQuery check wrapper
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
        // Add this plugin's JSON directory to load paths
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

        // Get colours and ensure image data is included
        $colours = $this->get_acf_field_or_default('colours', 'option');

        // Process colours to ensure image data is properly formatted for JavaScript
        $colours = $this->process_colour_data($colours);

        // Debug: Log the colours data to see what we're getting
        if (!empty($colours)) {
            error_log('Processed colours data: ' . print_r($colours, true));
        }

        // Get glazing features and ensure image data is included
        $glazing_features = $this->get_acf_field_or_default('glazing_features', 'option');

        // Process glazing features to ensure image data is properly formatted for JavaScript
        $glazing_features = $this->process_glazing_feature_data($glazing_features);

        // Get hardware colours
        $hardware_colours = $this->get_acf_field_or_default('hardware_colours', 'option');

        // Get cill options
        $cill_options = $this->get_acf_field_or_default('cill_options', 'option');

        // Get openings
        $openings = $this->get_acf_field_or_default('openings', 'option');
        $openings = $this->process_opening_data($openings);

        // Get panels
        $panels = $this->get_acf_field_or_default('panels', 'option');
        $panels = $this->process_panel_data($panels);

        $config = array(
            'categories' => $this->get_acf_field_or_default('product_categories', 'option'),
            'productTypes' => $product_types,
            'windowTypes' => $types_by_category['windows'],
            'doorTypes' => $types_by_category['doors'],
            'bayTypes' => $types_by_category['bay-windows'],
            'materials' => $materials,
            'styles' => $styles,
            'colours' => $colours,
            'glazingFeatures' => $glazing_features,
            'hardwareColours' => $hardware_colours,
            'cillOptions' => $cill_options,
            'openings' => $openings,
            'panels' => $panels,
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
     * Process colour data to ensure image URLs are properly formatted
     */
    private function process_colour_data($colours) {
        if (empty($colours) || !is_array($colours)) {
            return array();
        }

        $processed = array();
        foreach ($colours as $colour) {
            // Ensure the colour has the basic fields
            if (!isset($colour['name'])) {
                continue;
            }

            $processed_colour = array(
                'name' => $colour['name'],
                'category' => isset($colour['category']) ? $colour['category'] : 'Colour',
                'hex' => isset($colour['hex']) ? $colour['hex'] : '#FFFFFF',
                'finish_type' => isset($colour['finish_type']) ? $colour['finish_type'] : null,
                'available_materials' => isset($colour['available_materials']) ? $colour['available_materials'] : array(),
                'finish_exclusions' => isset($colour['finish_exclusions']) ? $colour['finish_exclusions'] : array()
            );

            // Handle colour_image field - ensure it's in the correct format
            if (!empty($colour['colour_image'])) {
                // If it's an array (ACF return format 'array'), extract the URL
                if (is_array($colour['colour_image'])) {
                    $processed_colour['colour_image'] = array(
                        'url' => isset($colour['colour_image']['url']) ? $colour['colour_image']['url'] : '',
                        'id' => isset($colour['colour_image']['id']) ? $colour['colour_image']['id'] : '',
                        'alt' => isset($colour['colour_image']['alt']) ? $colour['colour_image']['alt'] : $colour['name']
                    );
                }
                // If it's a numeric ID (ACF return format 'id'), get the URL
                elseif (is_numeric($colour['colour_image'])) {
                    $image_url = wp_get_attachment_image_url($colour['colour_image'], 'thumbnail');
                    if ($image_url) {
                        $processed_colour['colour_image'] = array(
                            'url' => $image_url,
                            'id' => $colour['colour_image'],
                            'alt' => $colour['name']
                        );
                    }
                }
                // If it's a URL string (ACF return format 'url')
                elseif (is_string($colour['colour_image']) && filter_var($colour['colour_image'], FILTER_VALIDATE_URL)) {
                    $processed_colour['colour_image'] = array(
                        'url' => $colour['colour_image'],
                        'alt' => $colour['name']
                    );
                }
            }

            // For Aluminium Special Colours with multiple finish types, create separate entries
            if ($processed_colour['category'] === 'Aluminium Special Colours' &&
                !empty($processed_colour['finish_type']) &&
                is_array($processed_colour['finish_type'])) {

                // Create one entry for each finish type
                foreach ($processed_colour['finish_type'] as $finish_type) {
                    $colour_variant = $processed_colour;
                    $colour_variant['finish_type'] = $finish_type; // Single value, not array
                    $processed[] = $colour_variant;
                }
            } else {
                // Normal processing - add as is
                $processed[] = $processed_colour;
            }
        }

        return $processed;
    }

    /**
     * Process glazing feature data to ensure image URLs are properly formatted
     */
    private function process_glazing_feature_data($features) {
        if (empty($features) || !is_array($features)) {
            return array();
        }

        $processed = array();
        foreach ($features as $feature) {
            // Ensure the feature has the basic fields
            if (!isset($feature['name'])) {
                continue;
            }

            $processed_feature = array(
                'name' => $feature['name'],
                'category' => isset($feature['category']) ? $feature['category'] : 'Georgians',
                'value' => isset($feature['value']) ? $feature['value'] : strtolower(str_replace(' ', '-', $feature['name']))
            );

            // Handle feature_image field - ensure it's in the correct format
            if (!empty($feature['feature_image'])) {
                // If it's an array (ACF return format 'array'), extract the URL
                if (is_array($feature['feature_image'])) {
                    $processed_feature['feature_image'] = array(
                        'url' => isset($feature['feature_image']['url']) ? $feature['feature_image']['url'] : '',
                        'id' => isset($feature['feature_image']['id']) ? $feature['feature_image']['id'] : '',
                        'alt' => isset($feature['feature_image']['alt']) ? $feature['feature_image']['alt'] : $feature['name']
                    );
                }
                // If it's a numeric ID (ACF return format 'id'), get the URL
                elseif (is_numeric($feature['feature_image'])) {
                    $image_url = wp_get_attachment_image_url($feature['feature_image'], 'thumbnail');
                    if ($image_url) {
                        $processed_feature['feature_image'] = array(
                            'url' => $image_url,
                            'id' => $feature['feature_image'],
                            'alt' => $feature['name']
                        );
                    }
                }
                // If it's a URL string (ACF return format 'url')
                elseif (is_string($feature['feature_image']) && filter_var($feature['feature_image'], FILTER_VALIDATE_URL)) {
                    $processed_feature['feature_image'] = array(
                        'url' => $feature['feature_image'],
                        'alt' => $feature['name']
                    );
                }
            }

            $processed[] = $processed_feature;
        }

        return $processed;
    }

    /**
     * Process opening data to ensure image URLs are properly formatted
     */
    private function process_opening_data($openings) {
        if (empty($openings) || !is_array($openings)) {
            return array();
        }

        $processed = array();
        foreach ($openings as $opening) {
            // Ensure the opening has the basic fields
            if (!isset($opening['name'])) {
                continue;
            }

            $processed_opening = array(
                'name' => $opening['name'],
                'slug' => isset($opening['slug']) ? $opening['slug'] : '',
                'available_types' => isset($opening['available_types']) ? $opening['available_types'] : array()
            );

            // Handle image field - ensure it's in the correct format
            if (!empty($opening['image'])) {
                // If it's an array (ACF return format 'array'), extract the URL
                if (is_array($opening['image'])) {
                    $processed_opening['image'] = array(
                        'url' => isset($opening['image']['url']) ? $opening['image']['url'] : '',
                        'id' => isset($opening['image']['id']) ? $opening['image']['id'] : '',
                        'alt' => isset($opening['image']['alt']) ? $opening['image']['alt'] : $opening['name']
                    );
                }
                // If it's a numeric ID (ACF return format 'id'), get the URL
                elseif (is_numeric($opening['image'])) {
                    $image_url = wp_get_attachment_image_url($opening['image'], 'medium');
                    if ($image_url) {
                        $processed_opening['image'] = array(
                            'url' => $image_url,
                            'id' => $opening['image'],
                            'alt' => $opening['name']
                        );
                    }
                }
                // If it's a URL string (ACF return format 'url')
                elseif (is_string($opening['image']) && filter_var($opening['image'], FILTER_VALIDATE_URL)) {
                    $processed_opening['image'] = array(
                        'url' => $opening['image'],
                        'alt' => $opening['name']
                    );
                }
            }

            $processed[] = $processed_opening;
        }

        return $processed;
    }

    /**
     * Process panel data to ensure image URLs are properly formatted
     */
    private function process_panel_data($panels) {
        if (empty($panels) || !is_array($panels)) {
            return array();
        }

        $processed = array();
        foreach ($panels as $panel) {
            // Ensure the panel has the basic fields
            if (!isset($panel['name'])) {
                continue;
            }

            $processed_panel = array(
                'name' => $panel['name'],
                'slug' => isset($panel['slug']) ? $panel['slug'] : '',
                'available_types' => isset($panel['available_types']) ? $panel['available_types'] : array(),
                'available_materials' => isset($panel['available_materials']) ? $panel['available_materials'] : array()
            );

            // Handle image field - ensure it's in the correct format
            if (!empty($panel['image'])) {
                // If it's an array (ACF return format 'array'), extract the URL
                if (is_array($panel['image'])) {
                    $processed_panel['image'] = array(
                        'url' => isset($panel['image']['url']) ? $panel['image']['url'] : '',
                        'id' => isset($panel['image']['id']) ? $panel['image']['id'] : '',
                        'alt' => isset($panel['image']['alt']) ? $panel['image']['alt'] : $panel['name']
                    );
                }
                // If it's a numeric ID (ACF return format 'id'), get the URL
                elseif (is_numeric($panel['image'])) {
                    $image_url = wp_get_attachment_image_url($panel['image'], 'medium');
                    if ($image_url) {
                        $processed_panel['image'] = array(
                            'url' => $image_url,
                            'id' => $panel['image'],
                            'alt' => $panel['name']
                        );
                    }
                }
                // If it's a URL string (ACF return format 'url')
                elseif (is_string($panel['image']) && filter_var($panel['image'], FILTER_VALIDATE_URL)) {
                    $processed_panel['image'] = array(
                        'url' => $panel['image'],
                        'alt' => $panel['name']
                    );
                }
            }

            $processed[] = $processed_panel;
        }

        return $processed;
    }

    /**
     * Process glazing types and map patterns based on available_glazing_types field
     */
    public function process_glazing_types_with_patterns($glazing_types, $patterns_library) {
        if (empty($glazing_types) || !is_array($glazing_types)) {
            return array();
        }

        // Initialize processed glazing types
        $processed = array();
        foreach ($glazing_types as $type) {
            $type_value = isset($type['value']) ? $type['value'] : '';
            // Extract icon URL directly from ACF image array
            $icon_url = '';
            if (isset($type['icon'])) {
                if (is_array($type['icon']) && isset($type['icon']['url'])) {
                    $icon_url = $type['icon']['url'];
                } elseif (is_string($type['icon'])) {
                    $icon_url = $type['icon'];
                }
            }
            $processed[$type_value] = array(
                'label' => isset($type['label']) ? $type['label'] : '',
                'value' => $type_value,
                'icon' => $icon_url,
                'patterns' => array()
            );
        }

        // Map patterns to glazing types based on available_glazing_types field
        if (!empty($patterns_library) && is_array($patterns_library)) {
            foreach ($patterns_library as $pattern) {
                $pattern_data = array(
                    'name' => isset($pattern['name']) ? $pattern['name'] : '',
                    'value' => isset($pattern['value']) ? $pattern['value'] : '',
                    'image' => isset($pattern['image']) ? $pattern['image'] : array()
                );

                // Get the available glazing types for this pattern
                $available_types = isset($pattern['available_glazing_types']) ? $pattern['available_glazing_types'] : array();

                if (!empty($available_types) && is_array($available_types)) {
                    foreach ($available_types as $type_value) {
                        if (isset($processed[$type_value])) {
                            $processed[$type_value]['patterns'][] = $pattern_data;
                        }
                    }
                }
            }
        }

        // Return as indexed array instead of associative
        return array_values($processed);
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
            'customer_street' => isset($customer_data['street']) ? $customer_data['street'] :
                                (isset($customer_data['customer_street']) ? $customer_data['customer_street'] : ''),
            'customer_town' => isset($customer_data['town']) ? $customer_data['town'] :
                              (isset($customer_data['customer_town']) ? $customer_data['customer_town'] : ''),
            'customer_county' => isset($customer_data['county']) ? $customer_data['county'] :
                                (isset($customer_data['customer_county']) ? $customer_data['customer_county'] : ''),
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
        $normalized_basket_items = $this->normalize_basket_items($basket_items, $post_id);

        // Save customer data to ACF fields (if ACF is available)
        if (function_exists('update_field')) {
            update_field('customer_name', $normalized_data['customer_name'], $post_id);
            update_field('customer_email', $normalized_data['customer_email'], $post_id);
            update_field('customer_phone', $normalized_data['customer_phone'], $post_id);
            update_field('customer_street', $normalized_data['customer_street'], $post_id);
            update_field('customer_town', $normalized_data['customer_town'], $post_id);
            update_field('customer_county', $normalized_data['customer_county'], $post_id);
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
    /**
     * Capitalize text values for display (converts "low-e-double" to "Low-E Double")
     */
    private function capitalize_value($text) {
        if (empty($text)) {
            return $text;
        }

        // Replace hyphens and underscores with spaces
        $text = str_replace(['-', '_'], ' ', $text);

        // Capitalize each word
        $text = ucwords(strtolower($text));

        // Fix specific uppercase patterns (like "E" in "Low-E")
        $text = preg_replace_callback('/\bE\b/', function($matches) {
            return 'E';
        }, $text);

        return $text;
    }

    /**
     * Upload base64 image to WordPress media library
     */
    private function upload_base64_image($base64_data, $filename, $post_id = 0) {
        // Check if base64 string is valid
        if (empty($base64_data) || strpos($base64_data, 'data:image') !== 0) {
            return false;
        }

        // Extract the base64 encoded binary data
        $base64_parts = explode(',', $base64_data);
        if (count($base64_parts) < 2) {
            return false;
        }

        $encoded_data = $base64_parts[1];
        $decoded_data = base64_decode($encoded_data);

        if ($decoded_data === false) {
            return false;
        }

        // Get file extension from mime type
        preg_match('/data:image\/([a-zA-Z0-9]+);/', $base64_data, $matches);
        $file_ext = isset($matches[1]) ? $matches[1] : 'png';

        // Sanitize filename
        $filename = sanitize_file_name($filename);
        if (empty($filename)) {
            $filename = 'quote-image-' . time() . '.' . $file_ext;
        } else {
            // Ensure correct extension
            $filename = preg_replace('/\.[^.]+$/', '', $filename) . '.' . $file_ext;
        }

        // Create uploads/quotes directory if it doesn't exist
        $upload_dir = wp_upload_dir();
        $quotes_dir = $upload_dir['basedir'] . '/quotes';

        if (!file_exists($quotes_dir)) {
            wp_mkdir_p($quotes_dir);
        }

        // Save file to uploads/quotes
        $file_path = $quotes_dir . '/' . $filename;
        $file_url = $upload_dir['baseurl'] . '/quotes/' . $filename;

        // Make filename unique if it exists
        $counter = 1;
        $original_filename = $filename;
        while (file_exists($file_path)) {
            $filename = preg_replace('/\.[^.]+$/', '', $original_filename) . '-' . $counter . '.' . $file_ext;
            $file_path = $quotes_dir . '/' . $filename;
            $file_url = $upload_dir['baseurl'] . '/quotes/' . $filename;
            $counter++;
        }

        // Write file
        if (file_put_contents($file_path, $decoded_data) === false) {
            return false;
        }

        // Create WordPress attachment
        $attachment = array(
            'guid'           => $file_url,
            'post_mime_type' => 'image/' . $file_ext,
            'post_title'     => preg_replace('/\.[^.]+$/', '', basename($filename)),
            'post_content'   => '',
            'post_status'    => 'inherit'
        );

        $attach_id = wp_insert_attachment($attachment, $file_path, $post_id);

        if (!is_wp_error($attach_id)) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attach_data = wp_generate_attachment_metadata($attach_id, $file_path);
            wp_update_attachment_metadata($attach_id, $attach_data);
            return $attach_id;
        }

        return false;
    }

    private function normalize_basket_items($basket_items, $post_id = 0) {
        $normalized = array();

        foreach ($basket_items as $item) {
            $normalized_item = array(
                'category' => isset($item['category']) ? $this->capitalize_value($item['category']) : '',
                'type_name' => isset($item['typeName']) ? $this->capitalize_value($item['typeName']) : '',
                'material_name' => isset($item['materialName']) ? $this->capitalize_value($item['materialName']) : '',
                'style_image' => isset($item['styleImage']) ? $item['styleImage'] : '',
                'style_name' => isset($item['styleName']) ? $this->capitalize_value($item['styleName']) : '',
                'width' => isset($item['width']) ? $item['width'] : '',
                'height' => isset($item['height']) ? $item['height'] : '',
                'cill' => isset($item['cill']) ? $this->capitalize_value($item['cill']) : '',
                'inside_colour' => isset($item['insideColour']) ? $this->capitalize_value($item['insideColour']) : '',
                'outside_colour' => isset($item['outsideColour']) ? $this->capitalize_value($item['outsideColour']) : '',
                'glazing_type' => isset($item['glazingType']) ? $this->capitalize_value($item['glazingType']) : '',
                'glazing_features' => isset($item['glazingFeatures']) ? $this->capitalize_value($item['glazingFeatures']) : '',
                'glazing_patterns' => isset($item['glazingPattern']) ? $this->capitalize_value($item['glazingPattern']) : '',
                'hardware_colour' => isset($item['hardwareColour']) ? $this->capitalize_value($item['hardwareColour']) : '',
                'location' => isset($item['location']) ? $this->capitalize_value($item['location']) : '',
            );

            // Handle attached image - upload base64 image to media library
            $normalized_item['attached_image'] = '';
            if (isset($item['attachedFiles']) && is_array($item['attachedFiles']) && !empty($item['attachedFiles'])) {
                // Take only the first file (we only allow 1 image now)
                $file = $item['attachedFiles'][0];
                $file_name = isset($file['name']) ? sanitize_text_field($file['name']) : '';
                $file_data = isset($file['data']) ? $file['data'] : '';

                // Upload base64 image and get attachment ID (unattached for public access)
                $attachment_id = $this->upload_base64_image($file_data, $file_name, 0);

                if ($attachment_id) {
                    $normalized_item['attached_image'] = $attachment_id;
                }
            }

            $normalized[] = $normalized_item;
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
            $message .= "Glazing Feature: " . ucfirst($item['glazingFeatures']) . "\n";
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
                $customer_message .= "Glazing Feature: " . ucfirst($item['glazingFeatures']) . "\n";
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

        // Add installation price
        $installation_price = get_field('installation_price', $post_id);
        if ($installation_price && is_numeric($installation_price)) {
            $total += floatval($installation_price);
        }

        // Add rubbish removal cost
        $rubbish_removal_cost = get_field('rubbish_removal_cost', $post_id);
        if ($rubbish_removal_cost && is_numeric($rubbish_removal_cost)) {
            $total += floatval($rubbish_removal_cost);
        }

        // Add trims & accessories price
        $trims_accessories_price = get_field('trims_accessories_price', $post_id);
        if ($trims_accessories_price && is_numeric($trims_accessories_price)) {
            $total += floatval($trims_accessories_price);
        }

        // Update quote price field
        update_field('quote_price', $total, $post_id);
    }

    /**
     * Generate quote PDF
     */
    public function generate_quote_pdf($post_id) {
        // Initialize debug log array to capture all steps
        $debug_log = array();

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

        $debug_log[] = "=== PDF GENERATION DEBUG LOG ===";
        $debug_log[] = "Quotation ID: #$post_id";
        $debug_log[] = "Timestamp: " . date('Y-m-d H:i:s');
        error_log("PDF Generation: Starting PDF generation check for post $post_id");

        // Get basket items - these should be saved now since we're at priority 25
        $basket_items = get_field('basket_items', $post_id);

        if (empty($basket_items)) {
            $debug_log[] = "❌ ERROR: No basket items found";
            error_log("PDF Generation: No basket items found for post $post_id - skipping PDF generation");
            set_transient('pdf_debug_log_' . $post_id, $debug_log, 300);
            return;
        }

        $debug_log[] = "✓ Found " . count($basket_items) . " basket items";
        error_log("PDF Generation: Found " . count($basket_items) . " basket items");

        // Check if at least one item has a price (only generate PDF when prices are set)
        // This prevents PDF generation when quotation is first submitted without prices
        $has_priced_items = false;
        $debug_log[] = "\n--- PRICE VALIDATION ---";
        foreach ($basket_items as $index => $item) {
            // Debug: Log the entire item structure
            $debug_log[] = "\nItem #" . ($index + 1) . ":";
            $debug_log[] = "  Data: " . json_encode($item, JSON_PRETTY_PRINT);
            error_log("PDF Generation: Checking item #$index: " . print_r($item, true));

            // Debug: Log each validation step
            $isset_check = isset($item['item_price']);
            $empty_check = !empty($item['item_price']);
            $numeric_check = is_numeric($item['item_price'] ?? '');
            $value_check = floatval($item['item_price'] ?? 0) > 0;

            $debug_log[] = "  Validation:";
            $debug_log[] = "    - isset(item_price): " . ($isset_check ? 'YES' : 'NO');
            $debug_log[] = "    - !empty(item_price): " . ($empty_check ? 'YES' : 'NO');
            $debug_log[] = "    - is_numeric(item_price): " . ($numeric_check ? 'YES' : 'NO');
            $debug_log[] = "    - value > 0: " . ($value_check ? 'YES' : 'NO');
            $debug_log[] = "    - item_price value: " . ($isset_check ? var_export($item['item_price'], true) : 'NOT SET');

            error_log("PDF Generation: Item #$index validation - isset: " . ($isset_check ? 'true' : 'false') .
                     ", !empty: " . ($empty_check ? 'true' : 'false') .
                     ", is_numeric: " . ($numeric_check ? 'true' : 'false') .
                     ", value>0: " . ($value_check ? 'true' : 'false'));

            // Use the same simple check as the notice display for consistency
            if (isset($item['item_price']) && floatval($item['item_price']) > 0) {
                $has_priced_items = true;
                $debug_log[] = "  ✓ VALID: Item has price £" . $item['item_price'];
                error_log("PDF Generation: Found priced item with price: " . $item['item_price']);
                break;
            } else {
                $debug_log[] = "  ✗ INVALID: Item does not meet pricing criteria";
            }
        }

        if (!$has_priced_items) {
            $debug_log[] = "\n❌ VALIDATION FAILED: No priced items found";
            $debug_log[] = "Requirement: At least one item must have item_price > 0";
            $debug_log[] = "Result: PDF generation skipped";
            error_log("PDF Generation: No priced items found for post $post_id - skipping PDF generation (PDFs are only generated when items have prices)");
            set_transient('pdf_debug_log_' . $post_id, $debug_log, 300);
            return;
        }

        $debug_log[] = "\n✓ VALIDATION PASSED: Priced items found";
        $debug_log[] = "\n--- PDF FILE GENERATION ---";
        error_log("PDF Generation: Starting PDF generation for post $post_id");

        // Get customer data
        $customer_name = get_field('customer_name', $post_id);
        $customer_email = get_field('customer_email', $post_id);
        $customer_phone = get_field('customer_phone', $post_id);
        $customer_street = get_field('customer_street', $post_id);
        $customer_town = get_field('customer_town', $post_id);
        $customer_county = get_field('customer_county', $post_id);
        $customer_postcode = get_field('customer_postcode', $post_id);
        $quote_price = get_field('quote_price', $post_id);

        // Get brand information
        $centralized_brand = get_field('centralized_brand', $post_id);
        $brands = get_field('brands', 'option'); // Get all brands from settings

        // Create a brand lookup array (slug => name)
        $brand_lookup = array();
        if (!empty($brands)) {
            foreach ($brands as $brand) {
                if (isset($brand['slug']) && isset($brand['name'])) {
                    $brand_lookup[$brand['slug']] = $brand['name'];
                }
            }
        }

        // Get service information
        $centralized_services = get_field('centralized_services', $post_id);
        $services = get_field('services', 'option'); // Get all services from settings

        // Create a service lookup array (slug => name)
        $service_lookup = array();
        if (!empty($services)) {
            foreach ($services as $service) {
                if (isset($service['slug']) && isset($service['name'])) {
                    $service_lookup[$service['slug']] = $service['name'];
                }
            }
        }

        $debug_log[] = "Customer: " . $customer_name;
        $debug_log[] = "Total quote price: £" . $quote_price;

        // Generate and save PDF file
        $data = array(
            'customer_name' => $customer_name,
            'customer_email' => $customer_email,
            'customer_phone' => $customer_phone,
            'customer_street' => $customer_street,
            'customer_town' => $customer_town,
            'customer_county' => $customer_county,
            'customer_postcode' => $customer_postcode,
            'basket_items' => $basket_items,
            'quote_price' => $quote_price,
            'centralized_brand' => $centralized_brand,
            'brand_lookup' => $brand_lookup,
            'centralized_services' => $centralized_services,
            'service_lookup' => $service_lookup
        );

        // Generate PDF file and save it
        $result = $this->save_pdf_file($post_id, $data, $debug_log);
        $pdf_url = $result['url'];
        $debug_log = $result['debug_log'];

        // Update the PDF URL field with the actual file URL
        // Use remove_action to prevent infinite loop
        remove_action('acf/save_post', array($this, 'generate_quote_pdf'), 25);

        if ($pdf_url) {
            update_field('quote_pdf_url', $pdf_url, $post_id);
            $debug_log[] = "\n✓ SUCCESS: PDF generated and saved";
            $debug_log[] = "PDF URL: " . $pdf_url;
            error_log("PDF Generation: Successfully generated PDF for post $post_id. URL: $pdf_url");
        } else {
            $debug_log[] = "\n❌ FAILED: PDF file was not created";
            $debug_log[] = "Check the error details above";
            error_log("PDF Generation: Failed to generate PDF for post $post_id");
        }

        // Store debug log in transient for display in admin (expires after 5 minutes)
        set_transient('pdf_debug_log_' . $post_id, $debug_log, 300);

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
     * Save PDF file to uploads directory using wkhtmltopdf (with mPDF fallback)
     */
    private function save_pdf_file($post_id, $data, $debug_log = array()) {
        // Get HTML content first
        $html = $this->generate_pdf_content($post_id, $data);
        $debug_log[] = "✓ HTML content generated";

        // Create uploads directory structure
        $upload_dir = wp_upload_dir();
        $quotes_dir = $upload_dir['basedir'] . '/quotes';
        $quotes_url = $upload_dir['baseurl'] . '/quotes';

        if (!file_exists($quotes_dir)) {
            wp_mkdir_p($quotes_dir);
            $debug_log[] = "✓ Created quotes directory: $quotes_dir";
        }

        // Generate filename
        $filename = 'quote-' . $post_id . '-' . sanitize_title($data['customer_name']) . '.pdf';
        $file_path = $quotes_dir . '/' . $filename;
        $debug_log[] = "Output path: $file_path";

        // Try wkhtmltopdf first
        $wkhtmltopdf_paths = [
            '/usr/local/bin/wkhtmltopdf',
            '/usr/bin/wkhtmltopdf',
            'wkhtmltopdf'  // Try from PATH
        ];

        $wkhtmltopdf_binary = null;
        foreach ($wkhtmltopdf_paths as $path) {
            if (@is_executable($path) || $path === 'wkhtmltopdf') {
                // Test if it works
                $test_output = shell_exec($path . ' --version 2>&1');
                if ($test_output && strpos($test_output, 'wkhtmltopdf') !== false) {
                    $wkhtmltopdf_binary = $path;
                    break;
                }
            }
        }

        if ($wkhtmltopdf_binary) {
            try {
                $debug_log[] = "✓ Found wkhtmltopdf: $wkhtmltopdf_binary";
                error_log("PDF Generation: Using wkhtmltopdf for post $post_id");

                // Create temporary HTML file
                $temp_html = $quotes_dir . '/temp-' . $post_id . '.html';
                file_put_contents($temp_html, $html);

                // Build wkhtmltopdf command
                $command = sprintf(
                    '%s --page-size A4 --margin-top 15mm --margin-right 15mm --margin-bottom 15mm --margin-left 15mm --encoding UTF-8 --enable-local-file-access --quiet %s %s 2>&1',
                    escapeshellarg($wkhtmltopdf_binary),
                    escapeshellarg($temp_html),
                    escapeshellarg($file_path)
                );

                // Execute command
                $output = shell_exec($command);

                // Clean up temp file
                @unlink($temp_html);

                // Check if PDF was created
                if (file_exists($file_path) && filesize($file_path) > 0) {
                    $file_size = filesize($file_path);
                    $debug_log[] = "✓ wkhtmltopdf: PDF created successfully: " . round($file_size / 1024, 2) . " KB";
                    error_log("PDF Generation: Success - wkhtmltopdf created PDF at $file_path");

                    $pdf_url = $quotes_url . '/' . $filename;
                    return array('url' => $pdf_url, 'debug_log' => $debug_log);
                } else {
                    $debug_log[] = "⚠ wkhtmltopdf failed, falling back to mPDF...";
                    if ($output) {
                        $debug_log[] = "wkhtmltopdf output: " . substr($output, 0, 200);
                    }
                }
            } catch (Exception $e) {
                $debug_log[] = "⚠ wkhtmltopdf exception: " . $e->getMessage();
                $debug_log[] = "Falling back to mPDF...";
            }
        } else {
            $debug_log[] = "wkhtmltopdf not found, using mPDF";
        }

        // Fallback to mPDF
        if (!class_exists('Mpdf\Mpdf')) {
            $debug_log[] = "❌ ERROR: mPDF library not found";
            error_log("PDF Generation: mPDF not available for post $post_id");
            return array('url' => false, 'debug_log' => $debug_log);
        }

        try {
            $debug_log[] = "Using mPDF for PDF generation...";
            error_log("PDF Generation: Using mPDF for post $post_id");

            // Configure mPDF
            $config = [
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_left' => 15,
                'margin_right' => 15,
                'margin_top' => 15,
                'margin_bottom' => 15,
                'margin_header' => 0,
                'margin_footer' => 0,
                'tempDir' => $quotes_dir . '/tmp'
            ];

            $mpdf = new \Mpdf\Mpdf($config);

            // Set document metadata
            $mpdf->SetCreator('Cristal Windows');
            $mpdf->SetAuthor('Cristal Windows, Doors & Conservatories Ltd');
            $mpdf->SetTitle('Quotation - ' . $data['customer_name']);
            $mpdf->SetSubject('Quotation');

            // Write HTML and output
            $mpdf->WriteHTML($html);
            $mpdf->Output($file_path, \Mpdf\Output\Destination::FILE);

            $debug_log[] = "✓ mPDF: Document created successfully";

            // Verify file was created
            if (file_exists($file_path)) {
                $file_size = filesize($file_path);
                $debug_log[] = "✓ PDF file saved: " . round($file_size / 1024, 2) . " KB";
                error_log("PDF Generation: Success - mPDF created PDF at $file_path");
            } else {
                $debug_log[] = "❌ ERROR: PDF file not created";
                error_log("PDF Generation: ERROR - File not found after generation");
                return array('url' => false, 'debug_log' => $debug_log);
            }

            $pdf_url = $quotes_url . '/' . $filename;
            return array('url' => $pdf_url, 'debug_log' => $debug_log);

        } catch (Exception $e) {
            $debug_log[] = "❌ EXCEPTION: " . $e->getMessage();
            $debug_log[] = "Location: " . $e->getFile() . ':' . $e->getLine();
            error_log("PDF Generation: Exception - " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return array('url' => false, 'debug_log' => $debug_log);
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
        $customer_street = get_field('customer_street', $post_id);
        $customer_town = get_field('customer_town', $post_id);
        $customer_county = get_field('customer_county', $post_id);
        $customer_postcode = get_field('customer_postcode', $post_id);
        $basket_items = get_field('basket_items', $post_id);
        $quote_price = get_field('quote_price', $post_id);

        // Get brand information
        $centralized_brand = get_field('centralized_brand', $post_id);
        $brands = get_field('brands', 'option'); // Get all brands from settings

        // Create a brand lookup array (slug => name)
        $brand_lookup = array();
        if (!empty($brands)) {
            foreach ($brands as $brand) {
                if (isset($brand['slug']) && isset($brand['name'])) {
                    $brand_lookup[$brand['slug']] = $brand['name'];
                }
            }
        }

        // Get service information
        $centralized_services = get_field('centralized_services', $post_id);
        $services = get_field('services', 'option'); // Get all services from settings

        // Create a service lookup array (slug => name)
        $service_lookup = array();
        if (!empty($services)) {
            foreach ($services as $service) {
                if (isset($service['slug']) && isset($service['name'])) {
                    $service_lookup[$service['slug']] = $service['name'];
                }
            }
        }

        $data = array(
            'customer_name' => $customer_name,
            'customer_email' => $customer_email,
            'customer_phone' => $customer_phone,
            'customer_street' => $customer_street,
            'customer_town' => $customer_town,
            'customer_county' => $customer_county,
            'customer_postcode' => $customer_postcode,
            'basket_items' => $basket_items,
            'quote_price' => $quote_price,
            'centralized_brand' => $centralized_brand,
            'brand_lookup' => $brand_lookup,
            'centralized_services' => $centralized_services,
            'service_lookup' => $service_lookup
        );

        // Generate PDF using wkhtmltopdf (with mPDF fallback)
        $filename = 'quote-' . $post_id . '-' . sanitize_title($data['customer_name']) . '.pdf';
        $html = $this->generate_pdf_content($post_id, $data);

        // Try wkhtmltopdf first
        $wkhtmltopdf_paths = [
            '/usr/local/bin/wkhtmltopdf',
            '/usr/bin/wkhtmltopdf',
            'wkhtmltopdf'
        ];

        $wkhtmltopdf_binary = null;
        foreach ($wkhtmltopdf_paths as $path) {
            if (@is_executable($path) || $path === 'wkhtmltopdf') {
                $test_output = shell_exec($path . ' --version 2>&1');
                if ($test_output && strpos($test_output, 'wkhtmltopdf') !== false) {
                    $wkhtmltopdf_binary = $path;
                    break;
                }
            }
        }

        if ($wkhtmltopdf_binary) {
            try {
                // Create temporary file for output
                $temp_pdf = tempnam(sys_get_temp_dir(), 'pdf_');
                $temp_html = tempnam(sys_get_temp_dir(), 'html_') . '.html';
                file_put_contents($temp_html, $html);

                // Build command
                $command = sprintf(
                    '%s --page-size A4 --margin-top 15mm --margin-right 15mm --margin-bottom 15mm --margin-left 15mm --encoding UTF-8 --enable-local-file-access --quiet %s %s 2>&1',
                    escapeshellarg($wkhtmltopdf_binary),
                    escapeshellarg($temp_html),
                    escapeshellarg($temp_pdf)
                );

                // Execute
                shell_exec($command);

                // Check if PDF was created
                if (file_exists($temp_pdf) && filesize($temp_pdf) > 0) {
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename="' . $filename . '"');
                    header('Content-Length: ' . filesize($temp_pdf));
                    readfile($temp_pdf);

                    // Clean up
                    @unlink($temp_pdf);
                    @unlink($temp_html);
                    exit;
                }

                // Clean up failed attempt
                @unlink($temp_pdf);
                @unlink($temp_html);
            } catch (Exception $e) {
                error_log('wkhtmltopdf failed for download: ' . $e->getMessage());
            }
        }

        // Fallback to mPDF
        if (!class_exists('Mpdf\Mpdf')) {
            wp_die('PDF library not available');
        }

        try {
            $upload_dir = wp_upload_dir();
            $quotes_dir = $upload_dir['basedir'] . '/quotes';

            $config = [
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_left' => 15,
                'margin_right' => 15,
                'margin_top' => 15,
                'margin_bottom' => 15,
                'margin_header' => 0,
                'margin_footer' => 0,
                'tempDir' => $quotes_dir . '/tmp'
            ];

            $mpdf = new \Mpdf\Mpdf($config);
            $mpdf->SetCreator('Cristal Windows');
            $mpdf->SetAuthor('Cristal Windows, Doors & Conservatories Ltd');
            $mpdf->SetTitle('Quotation - ' . $data['customer_name']);
            $mpdf->SetSubject('Quotation');
            $mpdf->WriteHTML($html);
            $mpdf->Output($filename, \Mpdf\Output\Destination::DOWNLOAD);
            exit;
        } catch (Exception $e) {
            error_log('PDF Generation Error: ' . $e->getMessage());
            wp_die('Error generating PDF: ' . $e->getMessage());
        }
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

    /**
     * Show PDF generation notices in admin
     */
    public function show_pdf_generation_notices() {
        global $pagenow, $post;

        // Only show on quotation edit pages
        if ($pagenow === 'post.php' && isset($_GET['post'])) {
            $post_id = intval($_GET['post']);
            if (get_post_type($post_id) === 'quotation') {
                $basket_items = get_field('basket_items', $post_id);
                $pdf_url = get_field('quote_pdf_url', $post_id);

                // Check if we need to show PDF generation status
                if (!empty($basket_items) && empty($pdf_url)) {
                    // Check if items have prices
                    $has_priced_items = false;
                    foreach ($basket_items as $item) {
                        if (isset($item['item_price']) && floatval($item['item_price']) > 0) {
                            $has_priced_items = true;
                            break;
                        }
                    }

                    if ($has_priced_items) {
                        $generate_url = admin_url('admin-post.php?action=generate_quote_pdf&post_id=' . $post_id);
                        $generate_url = wp_nonce_url($generate_url, 'generate_pdf_' . $post_id);

                        echo '<div class="notice notice-warning is-dismissible">';
                        echo '<p><strong>PDF not generated yet.</strong> This quotation has priced items but no PDF. ';
                        echo '<a href="' . esc_url($generate_url) . '" class="button button-primary">Generate PDF Now</a></p>';
                        echo '</div>';
                    } else {
                        echo '<div class="notice notice-info is-dismissible">';
                        echo '<p><strong>PDF will be generated automatically</strong> once you add prices to the items and save this quotation.</p>';
                        echo '</div>';
                    }
                }
            }
        }

        // Show success/error messages from manual PDF generation
        if (isset($_GET['pdf_generated'])) {
            if ($_GET['pdf_generated'] === 'success') {
                echo '<div class="notice notice-success is-dismissible">';
                echo '<p><strong>Success!</strong> PDF has been generated successfully.</p>';
                echo '</div>';
            } else if ($_GET['pdf_generated'] === 'error') {
                $error_msg = isset($_GET['error_msg']) ? urldecode($_GET['error_msg']) : 'Unknown error';
                echo '<div class="notice notice-error is-dismissible">';
                echo '<p><strong>Error generating PDF:</strong> ' . esc_html($error_msg) . '</p>';
                echo '</div>';
            }
        }

        // Show debug log if available (after PDF generation attempt)
        if ($pagenow === 'post.php' && isset($_GET['post'])) {
            $post_id = intval($_GET['post']);
            if (get_post_type($post_id) === 'quotation') {
                $debug_log = get_transient('pdf_debug_log_' . $post_id);
                if ($debug_log && is_array($debug_log)) {
                    echo '<div class="notice notice-info">';
                    echo '<p><strong>PDF Generation Debug Information:</strong></p>';
                    echo '<div style="background: #f5f5f5; padding: 15px; border-left: 4px solid #2271b1; font-family: monospace; font-size: 12px; max-height: 500px; overflow-y: auto;">';
                    foreach ($debug_log as $log_entry) {
                        echo '<div style="margin: 2px 0;">' . esc_html($log_entry) . '</div>';
                    }
                    echo '</div>';
                    echo '<p><em>This debug information will automatically disappear after 5 minutes.</em></p>';
                    echo '</div>';
                }
            }
        }
    }

    /**
     * Handle manual PDF generation from admin
     */
    public function handle_manual_pdf_generation() {
        if (!isset($_GET['post_id'])) {
            wp_die('No quotation specified');
        }

        $post_id = intval($_GET['post_id']);

        // Verify nonce
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'generate_pdf_' . $post_id)) {
            wp_die('Security check failed');
        }

        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            wp_die('You do not have permission to generate PDFs for this quotation');
        }

        // Verify it's a quotation
        if (get_post_type($post_id) !== 'quotation') {
            wp_die('Invalid quotation');
        }

        // Attempt to generate PDF
        try {
            $this->generate_quote_pdf($post_id);

            // Check if PDF was created
            $pdf_url = get_field('quote_pdf_url', $post_id);

            if ($pdf_url) {
                wp_redirect(admin_url('post.php?post=' . $post_id . '&action=edit&pdf_generated=success'));
            } else {
                wp_redirect(admin_url('post.php?post=' . $post_id . '&action=edit&pdf_generated=error&error_msg=' . urlencode('PDF was not created. Check that items have prices.')));
            }
        } catch (Exception $e) {
            wp_redirect(admin_url('post.php?post=' . $post_id . '&action=edit&pdf_generated=error&error_msg=' . urlencode($e->getMessage())));
        }

        exit;
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
