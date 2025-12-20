<?php
/**
 * Test PDF Generation
 *
 * This script tests if PDF generation works independently of WordPress hooks.
 * Run this from command line: php test-pdf-generation.php
 */

// Load WordPress
require_once(__DIR__ . '/../../../wp-load.php');

// Check if TCPDF is loaded
if (!class_exists('TCPDF')) {
    echo "ERROR: TCPDF class not found\n";
    echo "Attempting to load autoloader...\n";
    require_once(__DIR__ . '/vendor/autoload.php');

    if (class_exists('TCPDF')) {
        echo "SUCCESS: TCPDF loaded via autoloader\n";
    } else {
        echo "FAILED: TCPDF still not found\n";
        exit(1);
    }
} else {
    echo "SUCCESS: TCPDF class already loaded\n";
}

// Get the first quotation post
$args = array(
    'post_type' => 'quotation',
    'posts_per_page' => 1,
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'DESC'
);

$quotations = get_posts($args);

if (empty($quotations)) {
    echo "ERROR: No quotation posts found\n";
    echo "Creating a test quotation...\n";

    // Create a test quotation
    $post_id = wp_insert_post(array(
        'post_title' => 'Test Quotation - ' . date('Y-m-d H:i:s'),
        'post_type' => 'quotation',
        'post_status' => 'publish'
    ));

    if (is_wp_error($post_id)) {
        echo "ERROR: Failed to create test quotation\n";
        exit(1);
    }

    echo "Created test quotation with ID: $post_id\n";

    // Add test data
    if (function_exists('update_field')) {
        update_field('customer_name', 'Test Customer', $post_id);
        update_field('customer_email', 'test@example.com', $post_id);
        update_field('customer_phone', '01234567890', $post_id);
        update_field('customer_address', '123 Test Street', $post_id);
        update_field('customer_postcode', 'TE5T 1NG', $post_id);

        // Add test basket items with prices
        $basket_items = array(
            array(
                'category' => 'windows',
                'type_name' => 'Casement Window',
                'material_name' => 'uPVC',
                'style_name' => 'Side Hung',
                'width' => '1200',
                'height' => '1500',
                'cill' => 'White uPVC',
                'inside_colour' => 'White',
                'outside_colour' => 'White',
                'glazing_type' => 'double',
                'glazing_features' => 'clear',
                'hardware_colour' => 'white',
                'location' => 'Living Room',
                'item_price' => '450.00'
            )
        );

        update_field('basket_items', $basket_items, $post_id);
        update_field('quote_price', 450.00, $post_id);

        echo "Added test data to quotation\n";
    }
} else {
    $post_id = $quotations[0]->ID;
    echo "Using existing quotation ID: $post_id\n";
}

// Get quotation data
$customer_name = get_field('customer_name', $post_id);
$customer_email = get_field('customer_email', $post_id);
$customer_phone = get_field('customer_phone', $post_id);
$customer_address = get_field('customer_address', $post_id);
$customer_postcode = get_field('customer_postcode', $post_id);
$basket_items = get_field('basket_items', $post_id);
$quote_price = get_field('quote_price', $post_id);

echo "\nQuotation Data:\n";
echo "- Customer: $customer_name\n";
echo "- Email: $customer_email\n";
echo "- Basket items: " . (is_array($basket_items) ? count($basket_items) : '0') . "\n";
echo "- Quote price: £$quote_price\n";

// Check if items have prices
if (is_array($basket_items) && !empty($basket_items)) {
    $has_priced_items = false;
    foreach ($basket_items as $item) {
        if (isset($item['item_price']) && !empty($item['item_price']) && floatval($item['item_price']) > 0) {
            $has_priced_items = true;
            echo "- Found priced item: £" . $item['item_price'] . "\n";
            break;
        }
    }

    if (!$has_priced_items) {
        echo "\nWARNING: No priced items found. PDF will not be generated.\n";
        echo "Add prices to items and try again.\n";
        exit(1);
    }
} else {
    echo "\nERROR: No basket items found\n";
    exit(1);
}

// Attempt to generate PDF
echo "\nAttempting to generate PDF...\n";

try {
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    echo "- TCPDF object created\n";

    $pdf->SetCreator('Cristal Windows');
    $pdf->SetAuthor('Cristal Windows, Doors & Conservatories Ltd');
    $pdf->SetTitle('Quotation - ' . $customer_name);
    $pdf->SetSubject('Quotation');
    echo "- PDF metadata set\n";

    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(TRUE, 15);
    $pdf->SetFont('helvetica', '', 10);
    echo "- PDF settings configured\n";

    $pdf->AddPage();
    echo "- Page added\n";

    // Get upload directory
    $upload_dir = wp_upload_dir();
    $quotes_dir = $upload_dir['basedir'] . '/quotes';
    $quotes_url = $upload_dir['baseurl'] . '/quotes';

    echo "- Upload dir: {$upload_dir['basedir']}\n";
    echo "- Quotes dir: $quotes_dir\n";

    if (!file_exists($quotes_dir)) {
        if (!wp_mkdir_p($quotes_dir)) {
            echo "ERROR: Failed to create quotes directory\n";
            exit(1);
        }
        echo "- Created quotes directory\n";
    } else {
        echo "- Quotes directory exists\n";
    }

    // Check directory permissions
    if (!is_writable($quotes_dir)) {
        echo "ERROR: Quotes directory is not writable\n";
        echo "Directory permissions: " . substr(sprintf('%o', fileperms($quotes_dir)), -4) . "\n";
        exit(1);
    }
    echo "- Quotes directory is writable\n";

    // Generate simple HTML content for testing
    $html = '<h1>Test PDF</h1><p>Testing PDF generation for quotation #' . $post_id . '</p>';
    $html .= '<p>Customer: ' . htmlspecialchars($customer_name) . '</p>';

    $pdf->writeHTML($html, true, false, true, false, '');
    echo "- HTML content added to PDF\n";

    // Generate filename
    $filename = 'quote-' . $post_id . '-' . sanitize_title($customer_name) . '.pdf';
    $file_path = $quotes_dir . '/' . $filename;

    echo "- Attempting to save to: $file_path\n";

    // Save PDF
    $pdf->Output($file_path, 'F');

    if (file_exists($file_path)) {
        $filesize = filesize($file_path);
        echo "\nSUCCESS! PDF generated successfully\n";
        echo "- File: $file_path\n";
        echo "- Size: $filesize bytes\n";
        echo "- URL: $quotes_url/$filename\n";

        // Update the ACF field
        if (function_exists('update_field')) {
            update_field('quote_pdf_url', $quotes_url . '/' . $filename, $post_id);
            echo "- PDF URL updated in quotation\n";
        }
    } else {
        echo "\nERROR: PDF file was not created\n";
        exit(1);
    }

} catch (Exception $e) {
    echo "\nERROR: Exception occurred\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\nTest completed successfully!\n";
