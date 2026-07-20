<?php
/**
 * PDF Quotation Letter Template
 * Beautiful modern design for quotation PDFs
 */

// Calculate VAT (20%)
$quote_price = floatval($data['quote_price']);
$vat_amount = $quote_price - ($quote_price / 1.2);

// Format currency
function format_currency($amount) {
    return '£' . number_format($amount, 2);
}

// ---- Proposal content (Phase 3): editable settings + per-quote fields ----
// Every value below is optional. Sections only render when their content
// exists, so an un-synced or empty site produces the same PDF as before.
$qf_get_opt = function($name) {
    return function_exists('get_field') ? get_field($name, 'option') : '';
};
$qf_lines = function($text) {
    $lines = preg_split('/\r\n|\r|\n/', (string) $text);
    $lines = array_map('trim', $lines);
    return array_values(array_filter($lines, function($l) { return $l !== ''; }));
};

$customer_name = isset($data['customer_name']) ? $data['customer_name'] : '';

$intro_letter        = $qf_get_opt('proposal_intro_letter');
$five_reasons        = $qf_get_opt('proposal_five_reasons');
$whats_included      = $qf_get_opt('proposal_whats_included');
$why_choose          = $qf_get_opt('proposal_why_choose');
$what_happens_next   = $qf_get_opt('proposal_what_happens_next');
$vat_comparison_note = $qf_get_opt('proposal_vat_comparison_note');
$lead_time_setting   = $qf_get_opt('proposal_lead_time');
$validity_days       = $qf_get_opt('proposal_validity_days');
$additional_spec     = $qf_get_opt('proposal_additional_specification');
$customer_checklist  = $qf_get_opt('proposal_customer_checklist');
$payment_conditions  = $qf_get_opt('proposal_payment_conditions');
$final_thought       = $qf_get_opt('proposal_final_thought');
$acceptance_wording  = $qf_get_opt('proposal_acceptance_wording');

$brief_description = function_exists('get_field') ? get_field('brief_project_description', $post_id) : '';
$install_days     = function_exists('get_field') ? get_field('estimated_installation_days', $post_id) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation - Cristal Windows, Doors & Conservatories Ltd</title>
    <style>
        @page {
            size: A4;
            margin: 20mm;
        }

        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 10px;
            background: white;
        }

        .header {
            border-bottom: 3px solid #1a5490;
            padding-top: 0px;
            padding-bottom: 20px;
            margin-bottom: 40px;
            overflow: hidden;
            display: table;
            width: 100%;
        }

        .header-logo {
            display: table-cell;
            vertical-align: bottom;
            max-width: 120px;
            width: 120px;
        }

        .header-logo img {
            max-width: 100%;
            height: auto;
            display: block;
        }

        .header-contact {
            display: table-cell;
            vertical-align: bottom;
            text-align: right;
            font-size: 14px;
            color: #333;
            line-height: 1.8;
        }

        .header:after {
            content: "";
            display: table;
            clear: both;
        }

        .info-grid-wrapper {
            margin-left: -7.5px;
            margin-right: -7.5px;
            margin-bottom: 30px;
        }

        .info-grid {
            width: 100%;
            display: table;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 10px 0;
        }

        .info-section {
            display: table-cell;
            vertical-align: top;
            width: 50%;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .info-section h3 {
            color: #1a5490;
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 16px;
            text-transform: uppercase;
            border-bottom: 2px solid #1a5490;
            padding-bottom: 5px;
        }

        .info-item {
            margin-bottom: 8px;
            font-size: 14px;
        }

        .info-label {
            font-weight: bold;
            color: #555;
        }

        .message-section {
            background: white;
            padding: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            margin: 30px 0;
        }

        .message-section p {
            margin: 10px 0;
            line-height: 1.8;
        }

        .price-section {
            background: #1a5490;
            background: -webkit-linear-gradient(135deg, #1a5490 0%, #2a6ab0 100%);
            background: linear-gradient(135deg, #1a5490 0%, #2a6ab0 100%);
            color: white;
            padding: 25px;
            border-radius: 5px;
            margin: 30px 0;
            text-align: center;
        }

        .quote-price {
            font-size: 32px;
            font-weight: bold;
            margin: 10px 0;
        }

        .price-breakdown {
            font-size: 16px;
            margin-top: 10px;
            opacity: 1;
        }

        .terms-section {
            background: #fff9e6;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }

        .terms-section h3 {
            color: #1a5490;
            margin-top: 0;
            margin-bottom: 10px;
        }

        .page-break {
            page-break-before: always;
            margin-top: 0;
        }

        .terms-page {
            padding: 0;
        }

        .terms-page h2 {
            color: #333;
            font-size: 20px;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .terms-page p {
            margin: 10px 0;
            line-height: 1.8;
        }

        .terms-page strong {
            font-weight: bold;
        }

        .company-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #ccc;
            text-align: center;
            color: #666;
            font-size: 13px;
            line-height: 1.6;
        }

        .company-footer strong {
            color: #1a5490;
            font-size: 14px;
        }

        /* Item Pages */
        .item-page {
            padding: 0;
        }

        .item-page.page-break-item {
            page-break-before: always;
        }

        .item-technical-detail {
            font-size: 14px;
            color: #555;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .item-image {
            text-align: left;
            margin: 30px 0;
        }

        .item-image img {
            max-width: 325px;
            max-height: 375px;
            width: auto;
            height: auto;
            display: block;
            margin: 0;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
        }

        .item-product-name {
            font-size: 20px;
            font-weight: bold;
            color: #1a5490;
            margin: 20px 0 15px 0;
            text-align: left;
        }

        .item-details-list {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
        }

        .item-detail-row {
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
            font-size: 14px;
        }

        .item-detail-row:last-child {
            border-bottom: none;
        }

        .item-detail-label {
            font-weight: bold;
            color: #555;
            display: inline-block;
            min-width: 150px;
        }

        .item-detail-value {
            color: #333;
        }

        /* Proposal content pages (Phase 3) */
        .proposal-page {
            page-break-before: always;
            padding: 0;
        }

        .proposal-page h2 {
            color: #1a5490;
            font-size: 20px;
            margin: 0 0 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #1a5490;
            padding-bottom: 8px;
        }

        .proposal-page h3 {
            color: #1a5490;
            font-size: 16px;
            margin: 25px 0 10px;
        }

        .proposal-body p {
            margin: 10px 0;
            line-height: 1.8;
            font-size: 14px;
        }

        .proposal-list {
            margin: 10px 0;
            padding-left: 22px;
            font-size: 14px;
            line-height: 1.9;
        }

        .proposal-list li {
            margin-bottom: 6px;
        }

        .checklist {
            list-style: none;
            margin: 15px 0;
            padding: 0;
            font-size: 14px;
        }

        .checklist li {
            padding: 10px 0 10px 30px;
            border-bottom: 1px solid #e0e0e0;
            position: relative;
        }

        .checklist li:before {
            content: "";
            position: absolute;
            left: 0;
            top: 11px;
            width: 12px;
            height: 12px;
            border: 2px solid #1a5490;
        }

        .brief-description {
            background: #f8f9fa;
            padding: 15px 20px;
            border-radius: 5px;
            margin: 30px 0;
        }

        .brief-description h3 {
            color: #1a5490;
            margin: 0 0 8px;
            font-size: 15px;
        }

        .brief-description p {
            margin: 0;
            font-size: 14px;
            line-height: 1.7;
        }

        .duration-box {
            background: #1a5490;
            color: white;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            margin: 20px 0;
            font-size: 18px;
        }

        .duration-box strong {
            font-size: 24px;
        }

        .final-thought {
            background: #f8f9fa;
            border-left: 4px solid #1a5490;
            padding: 20px 25px;
            margin: 25px 0;
            font-style: italic;
            font-size: 15px;
            line-height: 1.8;
        }

        .final-thought .attribution {
            display: block;
            margin-top: 12px;
            font-style: normal;
            font-weight: bold;
            text-align: right;
        }

        .acceptance-fields {
            margin-top: 45px;
        }

        .acceptance-field {
            margin: 30px 0;
            font-size: 14px;
        }

        .acceptance-line {
            display: inline-block;
            border-bottom: 1px solid #333;
            min-width: 320px;
        }

        @media print {
            body {
                padding: 0;
            }
            .info-section {
                break-inside: avoid;
            }
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>
    <!-- QUOTATION PAGE -->
    <div class="header">
        <div class="header-logo">
            <img src="https://cristalwindows.co.uk/wp-content/uploads/2025/02/Cristal-Windows-LOGO-01.png" alt="Cristal Windows Logo">
        </div>
        <div class="header-contact">
            www.cristalwindows.co.uk<br>
            01252 810777 | sales@cristalwindows.co.uk
        </div>
    </div>

    <div class="info-grid-wrapper">
        <div class="info-grid">
            <div class="info-section">
                <h3>Client Details</h3>
                <div class="info-item">
                    <span class="info-label">Name:</span> <?php echo esc_html($data['customer_name']); ?>
                </div>
                <div class="info-item">
                    <span class="info-label">Address:</span><br>
                    <?php echo esc_html($data['customer_street']); ?><br>
                    <?php echo esc_html($data['customer_town']); ?><?php echo !empty($data['customer_county']) ? ', ' . esc_html($data['customer_county']) : ''; ?><br>
                    <?php echo esc_html($data['customer_postcode']); ?>
                </div>
                <div class="info-item">
                    <span class="info-label">Phone:</span> <?php echo esc_html($data['customer_phone']); ?>
                </div>
                <div class="info-item">
                    <span class="info-label">Email:</span> <?php echo esc_html($data['customer_email']); ?>
                </div>
            </div>

            <div class="info-section">
                <h3>Quote Information</h3>
                <div class="info-item">
                    <span class="info-label">Date:</span> <?php echo get_the_date('j F Y', $post_id); ?>
                </div>
                <div class="info-item">
                    <span class="info-label">Quote Reference:</span> Q-<?php echo get_the_date('Y-m-d', $post_id); ?>-<?php echo str_pad($post_id, 3, '0', STR_PAD_LEFT); ?>
                </div>
                <div class="info-item">
                    <span class="info-label">Prepared by:</span> Steve Cornish
                </div>
                <div class="info-item">
                    <span class="info-label">Email:</span> sales@cristalwindows.co.uk
                </div>
                <div class="info-item">
                    <span class="info-label">Lead Time:</span> <?php echo !empty($lead_time_setting) ? esc_html($lead_time_setting) : '4-6 weeks on standard range products'; ?>
                </div>
                <div class="info-item">
                    <span class="info-label">Guarantee:</span> 10 years Parts & Labour
                </div>
            </div>
        </div>
    </div>

    <div class="message-section">
        <?php if (!empty($intro_letter)): ?>
            <?php echo str_replace('[Customer Name]', esc_html($customer_name), $intro_letter); ?>
        <?php else: ?>
        <p>Dear <?php echo esc_html($data['customer_name']); ?>,</p>

        <p>Thank you for your recent enquiry regarding windows, doors and conservatories. We are pleased to provide you with the following quotation based on your requirements.</p>

        <p>This quotation is based on a supply and installation service. All prices are given in good faith and are subject to a signed company contract and final survey. <strong>Prices are inclusive of VAT at 20%.</strong></p>
        <?php endif; ?>
    </div>

    <?php
    $reasons_list = !empty($five_reasons) ? $qf_lines($five_reasons) : array();
    if (!empty($reasons_list)):
    ?>
    <div class="message-section">
        <h3 style="color:#1a5490; margin-top:0;">Five great reasons to choose us</h3>
        <ol class="proposal-list">
            <?php foreach ($reasons_list as $reason): ?>
            <li><?php echo esc_html($reason); ?></li>
            <?php endforeach; ?>
        </ol>
    </div>
    <?php endif; ?>

    <?php if (!empty($brief_description)): ?>
    <div class="brief-description">
        <h3>Brief Project Description</h3>
        <p><?php echo nl2br(esc_html($brief_description)); ?></p>
    </div>
    <?php endif; ?>

    <div class="price-section">
        <div style="font-size: 18px; margin-bottom: 10px;">TOTAL QUOTATION VALUE</div>
        <div class="quote-price"><?php echo format_currency($quote_price); ?></div>
        <div class="price-breakdown">
            Final Price (inc. VAT): <?php echo format_currency($quote_price); ?><br>
            Of which VAT (20%): <?php echo format_currency($vat_amount); ?>
        </div>
    </div>

    <div class="terms-section">
        <h3>Payment Terms</h3>
        <ul style="margin: 10px 0; padding-left: 20px;">
            <li><strong>25% Deposit</strong> required upon order confirmation</li>
            <li><strong>75% Staged payment</strong> as work progresses</li>
            <li><strong>Final balance</strong> due upon completion of installation</li>
        </ul>
    </div>

    <div class="message-section">
        <p>We look forward to working with you on this project. Should you have any questions or require any clarification, please do not hesitate to contact us.</p>

        <p>Best regards,<br>
        <strong>Steve Cornish</strong><br>
        Cristal Windows, Doors & Conservatories Ltd</p>
    </div>

    <!-- Item Pages -->
    <?php
    if (!empty($data['basket_items']) && is_array($data['basket_items'])):
        $item_number = 1;
        foreach ($data['basket_items'] as $item):
            // Determine which brand to use (per-item brand overrides centralized brand)
            $brand_slug = '';
            if (!empty($item['brand'])) {
                $brand_slug = $item['brand'];
            } elseif (!empty($data['centralized_brand'])) {
                $brand_slug = $data['centralized_brand'];
            }

            // Look up brand name from slug
            $brand_name = '';
            if (!empty($brand_slug) && !empty($data['brand_lookup'][$brand_slug])) {
                $brand_name = $data['brand_lookup'][$brand_slug];
            }

            // Determine which services to use (per-item services override centralized services)
            $service_slugs = array();
            if (!empty($item['services']) && is_array($item['services'])) {
                $service_slugs = $item['services'];
            } elseif (!empty($data['centralized_services']) && is_array($data['centralized_services'])) {
                $service_slugs = $data['centralized_services'];
            }

            // Look up service names from slugs
            $service_names = array();
            if (!empty($service_slugs) && !empty($data['service_lookup'])) {
                foreach ($service_slugs as $service_slug) {
                    if (!empty($data['service_lookup'][$service_slug])) {
                        $service_names[] = $data['service_lookup'][$service_slug];
                    }
                }
            }

            // Check if glazing fields should be hidden for this style
            $hide_glazing_styles = array('5001', '5004', '5015', '5025', '5030', '5033', '5401');
            $style_name = isset($item['style_name']) ? $item['style_name'] : '';
            $hide_glazing = false;
            foreach ($hide_glazing_styles as $style_num) {
                if (strpos($style_name, $style_num) !== false) {
                    $hide_glazing = true;
                    break;
                }
            }

            // Build technical detail string
            $tech_details = array();
            $tech_details[] = $item_number;
            if (!empty($item['location'])) {
                $tech_details[] = esc_html($item['location']);
            }
            foreach ($service_names as $service_name) {
                $tech_details[] = esc_html($service_name);
            }
            $technical_detail = implode(' - ', $tech_details);

            // Build product name: Brand Material Type
            $product_name_parts = array();
            if (!empty($brand_name)) {
                $product_name_parts[] = $brand_name;
            }
            if (!empty($item['material_name']) && $item['material_name'] !== 'N/A') {
                // Convert material slug to display name
                $material_display_name = !empty($data['material_lookup'][$item['material_name']])
                    ? $data['material_lookup'][$item['material_name']]
                    : $item['material_name'];
                $product_name_parts[] = $material_display_name;
            }
            if (!empty($item['type_name'])) {
                // Convert type slug to display name
                $type_display_name = !empty($data['type_lookup'][$item['type_name']])
                    ? $data['type_lookup'][$item['type_name']]
                    : $item['type_name'];
                $product_name_parts[] = $type_display_name;
            }
            $product_name = implode(' ', $product_name_parts);
    ?>
    <div class="item-page<?php echo ($item_number > 1) ? ' page-break-item' : ''; ?>">
        <div class="item-technical-detail">
            Technical Detail: <?php echo $technical_detail; ?>
        </div>

        <?php if (!empty($item['style_image'])): ?>
        <div class="item-image">
            <img src="<?php echo esc_url($item['style_image']); ?>" alt="<?php echo esc_attr($item['style_name']); ?>">
        </div>
        <?php endif; ?>

        <div class="item-product-name">
            <?php echo esc_html($product_name); ?>
        </div>

        <div class="item-details-list">
            <?php if (!empty($item['category'])): ?>
            <div class="item-detail-row">
                <span class="item-detail-label">Category:</span>
                <span class="item-detail-value"><?php
                    // Convert category slug to display name
                    $category_display_name = !empty($data['category_lookup'][$item['category']])
                        ? $data['category_lookup'][$item['category']]
                        : ucfirst($item['category']);
                    echo esc_html($category_display_name);
                ?></span>
            </div>
            <?php endif; ?>

            <?php if (!empty($item['style_name'])): ?>
            <div class="item-detail-row">
                <span class="item-detail-label">Style:</span>
                <span class="item-detail-value"><?php echo esc_html($item['style_name']); ?></span>
            </div>
            <?php endif; ?>

            <?php if (!empty($item['width']) && !empty($item['height'])): ?>
            <div class="item-detail-row">
                <span class="item-detail-label">Dimensions:</span>
                <span class="item-detail-value"><?php echo esc_html($item['width']); ?>mm (W) x <?php echo esc_html($item['height']); ?>mm (H)</span>
            </div>
            <?php endif; ?>

            <?php if (!empty($item['segment_widths'])): ?>
            <div class="item-detail-row">
                <span class="item-detail-label">Segment Widths:</span>
                <span class="item-detail-value"><?php
                    $widths = array_map('trim', explode(',', $item['segment_widths']));
                    $parts = array();
                    foreach ($widths as $idx => $w) {
                        $parts[] = 'S' . ($idx + 1) . ': ' . esc_html($w) . 'mm';
                    }
                    echo implode(', ', $parts);
                ?></span>
            </div>
            <?php endif; ?>

            <?php if (!empty($item['cill'])): ?>
            <div class="item-detail-row">
                <span class="item-detail-label">External Sub Cill:</span>
                <span class="item-detail-value"><?php echo esc_html($item['cill']); ?></span>
            </div>
            <?php endif; ?>

            <?php
            // Only show Side Panels for Composite Doors
            $type_name_lower = strtolower($item['type_name'] ?? '');
            if (!empty($item['side_panels']) && (strpos($type_name_lower, 'composite') !== false || strpos($type_name_lower, 'door-co') !== false || strpos($type_name_lower, 'doorco') !== false)):
            ?>
            <div class="item-detail-row">
                <span class="item-detail-label">Side Panels:</span>
                <span class="item-detail-value"><?php echo esc_html($item['side_panels']); ?></span>
            </div>
            <?php endif; ?>

            <?php
            // Only show Infill Panel for Glazed Doors or French Doors with midrail styles
            $show_infill_panel_styles = array('1803', '1804', '1811', '1812', '1813', '1814', '1815', '1816', '1827', '1828', '1829', '1830', '1831', '1832', '2101', '2104', '2105', '2202', '2205', '2206');
            $style_name_pdf = strtolower($item['style_name'] ?? '');
            $has_midrail_style_pdf = false;
            foreach ($show_infill_panel_styles as $style_num) {
                if (strpos($style_name_pdf, $style_num) !== false) {
                    $has_midrail_style_pdf = true;
                    break;
                }
            }
            if (!empty($item['infill_panel']) && (strpos($type_name_lower, 'glazed') !== false || strpos($type_name_lower, 'french') !== false) && $has_midrail_style_pdf):
            ?>
            <div class="item-detail-row">
                <span class="item-detail-label">Infill Panel:</span>
                <span class="item-detail-value"><?php echo esc_html($item['infill_panel']); ?></span>
            </div>
            <?php endif; ?>

            <?php if (!empty($item['outside_colour'])): ?>
            <div class="item-detail-row">
                <span class="item-detail-label">Outside Colour:</span>
                <span class="item-detail-value"><?php echo esc_html($item['outside_colour']); ?></span>
            </div>
            <?php endif; ?>

            <?php if (!empty($item['inside_colour'])): ?>
            <div class="item-detail-row">
                <span class="item-detail-label">Inside Colour:</span>
                <span class="item-detail-value"><?php echo esc_html($item['inside_colour']); ?></span>
            </div>
            <?php endif; ?>

            <?php if (!empty($item['glazing_type']) && !$hide_glazing): ?>
            <div class="item-detail-row">
                <span class="item-detail-label">Glazing Type:</span>
                <span class="item-detail-value"><?php echo esc_html(ucfirst($item['glazing_type'])); ?></span>
            </div>
            <?php endif; ?>

            <?php if (!empty($item['glazing_patterns']) && !$hide_glazing): ?>
            <div class="item-detail-row">
                <span class="item-detail-label">Glazing Pattern:</span>
                <span class="item-detail-value"><?php echo esc_html($item['glazing_patterns']); ?></span>
            </div>
            <?php endif; ?>

            <?php if (!empty($item['glazing_features']) && !$hide_glazing): ?>
            <div class="item-detail-row">
                <span class="item-detail-label">Glazing Features:</span>
                <span class="item-detail-value"><?php echo esc_html(ucfirst($item['glazing_features'])); ?></span>
            </div>
            <?php endif; ?>

            <?php if (!empty($item['hardware_colour'])): ?>
            <div class="item-detail-row">
                <span class="item-detail-label">Hardware Colour:</span>
                <span class="item-detail-value"><?php echo esc_html(ucfirst($item['hardware_colour'])); ?></span>
            </div>
            <?php endif; ?>

            <?php
            // Only show Opening for French Doors, Glazed Doors, and Composite Doors
            $show_opening = strpos($type_name_lower, 'french') !== false || strpos($type_name_lower, 'glazed') !== false || strpos($type_name_lower, 'composite') !== false || strpos($type_name_lower, 'door-co') !== false || strpos($type_name_lower, 'doorco') !== false;
            if ($show_opening && !empty($item['opening'])):
            ?>
            <div class="item-detail-row">
                <span class="item-detail-label">Opening:</span>
                <span class="item-detail-value"><?php echo esc_html($item['opening']); ?></span>
            </div>
            <?php endif; ?>

        </div>
    </div>
    <?php
            $item_number++;
        endforeach;
    endif;
    ?>

    <!-- Proposal content pages (Phase 3). Payment schedule (Phase 4) and
         additional documents (Phase 5) will slot in around these. -->

    <?php if (!empty($payment_conditions)): ?>
    <div class="proposal-page">
        <h2>Payment Conditions</h2>
        <div class="proposal-body"><?php echo $payment_conditions; ?></div>
    </div>
    <?php endif; ?>

    <?php
    $included_list = !empty($whats_included) ? $qf_lines($whats_included) : array();
    if (!empty($included_list)):
    ?>
    <div class="proposal-page">
        <h2>What's Included In Our Price</h2>
        <ul class="checklist">
            <?php foreach ($included_list as $inc): ?>
            <li><?php echo esc_html($inc); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <?php
    $next_steps = !empty($what_happens_next) ? $qf_lines($what_happens_next) : array();
    $has_project_info = !empty($why_choose) || !empty($next_steps) || !empty($lead_time_setting) || !empty($validity_days) || !empty($vat_comparison_note);
    if ($has_project_info):
    ?>
    <div class="proposal-page">
        <h2>Why Choose Cristal</h2>
        <?php if (!empty($why_choose)): ?>
        <div class="proposal-body"><?php echo $why_choose; ?></div>
        <?php endif; ?>

        <?php if (!empty($next_steps)): ?>
        <h3>What Happens Next</h3>
        <ol class="proposal-list">
            <?php foreach ($next_steps as $step): ?>
            <li><?php echo esc_html($step); ?></li>
            <?php endforeach; ?>
        </ol>
        <?php endif; ?>

        <?php if (!empty($lead_time_setting) || !empty($validity_days)): ?>
        <h3>Project Information</h3>
        <div class="proposal-body">
            <?php if (!empty($lead_time_setting)): ?><p><strong>Estimated lead time:</strong> <?php echo esc_html($lead_time_setting); ?></p><?php endif; ?>
            <?php if (!empty($validity_days)): ?><p><strong>Quotation validity:</strong> <?php echo esc_html($validity_days); ?> days.</p><?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($vat_comparison_note)): ?>
        <h3>VAT &amp; Price Comparison</h3>
        <div class="proposal-body"><p><?php echo esc_html($vat_comparison_note); ?></p></div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($additional_spec)): ?>
    <div class="proposal-page">
        <h2>Additional Project Information &amp; Specification</h2>
        <div class="proposal-body"><?php echo $additional_spec; ?></div>
    </div>
    <?php endif; ?>

    <?php
    $checklist_items = !empty($customer_checklist) ? $qf_lines($customer_checklist) : array();
    if (!empty($checklist_items)):
    ?>
    <div class="proposal-page">
        <h2>Customer Checklist</h2>
        <div class="proposal-body"><p>Please check that the following details are correct and let us know of any amendments required.</p></div>
        <ul class="checklist">
            <?php foreach ($checklist_items as $ci): ?>
            <li><?php echo esc_html($ci); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <?php if (!empty($install_days) || !empty($final_thought)): ?>
    <div class="proposal-page">
        <h2>Installation &amp; A Final Thought</h2>
        <?php if (!empty($install_days)): ?>
        <div class="duration-box">Estimated Installation Duration<br><strong><?php echo esc_html($install_days); ?></strong> Working Day(s)</div>
        <?php endif; ?>
        <?php
        if (!empty($final_thought)):
            $ft_lines = $qf_lines($final_thought);
            $attribution = '';
            if (!empty($ft_lines)) {
                $last = end($ft_lines);
                if (preg_match('/^[-\x{2014}]/u', $last)) {
                    $attribution = ltrim($last, "-\xe2\x80\x94 ");
                    array_pop($ft_lines);
                }
            }
            $ft_body = implode(' ', $ft_lines);
        ?>
        <div class="final-thought">
            <?php echo esc_html($ft_body); ?>
            <?php if ($attribution !== ''): ?><span class="attribution">&mdash; <?php echo esc_html($attribution); ?></span><?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($acceptance_wording)): ?>
    <div class="proposal-page">
        <h2>Customer Acceptance</h2>
        <div class="proposal-body"><?php echo $acceptance_wording; ?></div>
        <div class="acceptance-fields">
            <div class="acceptance-field">Customer Name: <span class="acceptance-line"></span></div>
            <div class="acceptance-field">Signature: <span class="acceptance-line"></span></div>
            <div class="acceptance-field">Date: <span class="acceptance-line"></span></div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Terms and Conditions Page -->
    <div class="page-break terms-page">
        <h2>Terms and Conditions</h2>

        <p><strong>All prices are given in good faith. Subject to signed company contract and final survey.</strong></p>

        <p>Based on Supply & Install, make good to immediate fitting area. Generated debris removal from site.</p>

        <p><strong>Prices are inclusive of VAT@20%</strong></p>

        <p><strong>PRICES ARE HELD FOR A PERIOD OF 14 DAYS.</strong></p>

        <p>This quotation is valid for 14 days from the date shown above. Payment terms and schedule will be confirmed upon contract signing. Installation timescales will be confirmed following the final survey.</p>

        <div class="company-footer">
            <strong>Cristal Windows, Doors & Conservatories Ltd</strong><br>
            Registered in England No. 5829993 | Registered address as above | VAT Registration No. 890 4307 21
        </div>
    </div>
</body>
</html>
