<?php
/**
 * Quotation Form Theme Functions
 */

// Enqueue scripts and styles
function quotation_form_enqueue_scripts() {
    wp_enqueue_style('quotation-form-css', get_template_directory_uri() . '/quotation-form.css', array(), '1.0');
    wp_enqueue_script('quotation-form-js', get_template_directory_uri() . '/quotation-form.js', array('jquery'), '1.0', true);

    // Localize script for AJAX
    wp_localize_script('quotation-form-js', 'quotationFormAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('quotation_form_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'quotation_form_enqueue_scripts');

// AJAX handler for form submission
add_action('wp_ajax_submit_quotation_form', 'handle_quotation_form_submission');
add_action('wp_ajax_nopriv_submit_quotation_form', 'handle_quotation_form_submission');

function handle_quotation_form_submission() {
    check_ajax_referer('quotation_form_nonce', 'nonce');

    $basket_items = isset($_POST['basket_items']) ? json_decode(stripslashes($_POST['basket_items']), true) : array();
    $customer_data = isset($_POST['customer_data']) ? $_POST['customer_data'] : array();

    // Process the submission
    // You can save to database, send email, etc.

    // Example: Send email notification
    $admin_email = get_option('admin_email');
    $subject = 'New Quotation Request';

    $message = "New quotation request received:\n\n";
    $message .= "Customer Details:\n";
    foreach ($customer_data as $key => $value) {
        $message .= ucfirst(str_replace('_', ' ', $key)) . ": " . sanitize_text_field($value) . "\n";
    }

    $message .= "\n\nItems (" . count($basket_items) . "):\n";
    foreach ($basket_items as $index => $item) {
        $message .= "\nItem " . ($index + 1) . ":\n";
        foreach ($item as $key => $value) {
            if (!in_array($key, ['id', 'timestamp'])) {
                $message .= "  " . ucfirst(str_replace('_', ' ', $key)) . ": " . $value . "\n";
            }
        }
    }

    wp_mail($admin_email, $subject, $message);

    wp_send_json_success(array(
        'message' => 'Quotation request submitted successfully!'
    ));
}

// Register ACF options page for form settings (optional)
if (function_exists('acf_add_options_page')) {
    acf_add_options_page(array(
        'page_title' => 'Quotation Form Settings',
        'menu_title' => 'Quotation Form',
        'menu_slug' => 'quotation-form-settings',
        'capability' => 'edit_posts',
        'redirect' => false
    ));
}
