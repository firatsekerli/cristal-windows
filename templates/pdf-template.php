<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
body {
    font-family: dejavusans, sans-serif;
    font-size: 10pt;
    line-height: 1.3;
    color: #000000;
}
.header {
    width: 100%;
    margin: 0 0 10px 0;  /* more space under the header */
    padding: 0 0 6px 0;
    border-bottom: 3px solid #0066cc;
}
.company-info {
    text-align: right;
    font-size: 8pt;
    line-height: 1.2;
}
.company-info h3 {
    color: #0066cc;
    margin: 0 0 4px 0;
    font-size: 9pt;
    font-weight: bold;
}
.company-info p {
    margin: 0;
    font-size: 8pt;
    line-height: 1.2;
}
.customer-section {
    margin-bottom: 15px;
}
.customer-section h2 {
    font-size: 11pt;
    margin-bottom: 6px;
    color: #333333;
    font-weight: bold;
}
.customer-details {
    line-height: 1.3;
}
.customer-details p {
    margin: 2px 0;
}
.items-section {
    margin-bottom: 15px;
    page-break-before: always;
}
.items-section h2 {
    font-size: 12pt;
    margin-top: 15px;
    margin-bottom: 8px;
    color: #0066cc;
    border-bottom: 3px solid #0066cc;
    padding-bottom: 6px;
    font-weight: bold;
}
.item {
    background-color: #f9f9f9;
    padding: 12px;
    margin-top: 12px;
    margin-bottom: 10px;
    page-break-inside: avoid;
}
.item:first-of-type {
    margin-top: 25px;
}
.item-header {
    font-weight: bold;
    font-size: 11pt;
    margin-bottom: 6px;
    color: #0066cc;
}
.item-details {
    margin-top: 6px;
}
.item-detail {
    padding: 3px 0;
    border-bottom: 1px solid #eeeeee;
    line-height: 1.2;
}
.item-detail:last-child {
    border-bottom: none;
}
.item-detail strong {
    color: #555555;
    font-weight: bold;
}
.item-price {
    font-size: 11pt;
    font-weight: bold;
    color: #0066cc;
    margin-top: 6px;
    text-align: right;
}
.total-section {
    margin-top: 12px;
    text-align: right;
    font-size: 13pt;
    font-weight: bold;
    padding: 10px;
    background-color: #f0f7ff;
    border: 2px solid #0066cc;
}
.total-section .total-label {
    color: #333333;
}
.total-section .total-amount {
    color: #0066cc;
    font-size: 16pt;
}
.terms {
    margin-top: 15px;
    padding-top: 12px;
    border-top: 1px solid #cccccc;
    font-size: 8pt;
    color: #666666;
    page-break-before: always;
}
.terms h3 {
    font-size: 10pt;
    margin-bottom: 6px;
    color: #333333;
    font-weight: bold;
}
.terms p {
    margin: 4px 0;
    line-height: 1.2;
}
.footer {
    margin-top: 15px;
    text-align: center;
    font-size: 8pt;
    color: #666666;
    border-top: 1px solid #cccccc;
    padding-top: 10px;
}
.footer p {
    margin: 2px 0;
}
.cover-letter {
    margin-bottom: 20px;
    margin-top: 30px;
    padding: 0;
    background-color: #ffffff;
}
.cover-letter p {
    margin: 8px 0;
    line-height: 1.4;
}
.cover-letter p:first-child {
    margin-top: 0;
}
.customer-info-inline {
    margin: 12px 0 15px 0;
    padding: 10px 12px;
    background-color: #f9f9f9;
}
.customer-info-inline p {
    margin: 3px 0;
    font-size: 9pt;
    line-height: 1.3;
}
.info-columns {
    width: 100%;
    margin-bottom: 20px;
    border-collapse: separate;
    border-spacing: 15px 0;
}
.info-columns td {
    vertical-align: top;
    padding: 0;
    width: 50%;
    background-color: #F8F9FA;
}
.info-column {
    padding: 15px;
}
.info-column-title {
    font-size: 9pt;
    color: #1e3a8a;
    margin-bottom: 10px;
    padding: 10px 15px;
    background-color: #F8F9FA;
    border-bottom: 2px solid #333;
    font-weight: bold;
    text-transform: uppercase;
}
.info-columns p {
    margin: 5px 0;
    font-size: 8pt;
    line-height: 1.4;
}
.info-columns .label {
    font-weight: bold;
    color: #333;
}
</style>
</head>
<body>
<!-- Header -->
<div class="header">
<table style="width: 100%; border: none; padding: 0; margin: 0;" cellpadding="0" cellspacing="0">
    <tr>
        <td style="width: 35%; vertical-align: top;">
            <?php
            $logo_url = '';
            if (function_exists('get_field')):
                $logo = get_field('company_logo', 'option');
                if ($logo && isset($logo['url'])) {
                    $logo_url = $logo['url'];
                }
            endif;

            // Fallback to default logo if not set in ACF
            if (empty($logo_url)) {
                $logo_url = 'https://cristalwindows.co.uk/wp-content/uploads/2025/02/Cristal-Windows-LOGO-01.png';
            }

            if ($logo_url): ?>
                <img src="<?php echo esc_url($logo_url); ?>" alt="Company Logo" style="max-width: 120px;">
            <?php endif; ?>
        </td>
        <td style="width: 65%; vertical-align: top; text-align: right;">
            <div class="company-info">
                <h3>Cristal Windows, Doors &amp; Conservatories Ltd</h3>
                <p>23 Cedar Drive, Fleet, Hampshire GU51 3HD</p>
                <p>www.cristalwindows.co.uk</p>
                <p>01252 810777</p>
                <p>enquiries@cristalwindows.co.uk</p>
            </div>
        </td>
    </tr>
</table>
</div>

<!-- Cover Letter -->
<div class="cover-letter">
<p><strong>Date:</strong> <?php echo date('d F Y'); ?></p>

<!-- Two Column Layout: Client Details & Quote Information -->
<table class="info-columns" cellpadding="0" cellspacing="0">
    <tr>
        <td class="info-column">
            <h3 class="info-column-title">CLIENT DETAILS</h3>
            <div class="content">
                <p><span class="label">Name:</span> <?php echo esc_html($data['customer_name']); ?></p>
                <p><span class="label">Address:</span><br>
                    <?php
                    $address_parts = array_filter([
                        $data['customer_street'],
                        $data['customer_town'],
                        $data['customer_county'],
                        $data['customer_postcode']
                    ]);
                    echo esc_html(implode('<br>', $address_parts));
                    ?>
                </p>
                <p><span class="label">Phone:</span> <?php echo esc_html($data['customer_phone']); ?></p>
                <p><span class="label">Email:</span> <?php echo esc_html($data['customer_email']); ?></p>
            </div>
        </td>
        <td class="info-column">
            <h3 class="info-column-title">QUOTE INFORMATION</h3>
            <div class="content">
                <p><span class="label">Date:</span> <?php echo date('d F Y'); ?></p>
                <p><span class="label">Quote Reference:</span> Q-<?php echo date('Y-m-d'); ?>-001</p>
                <p><span class="label">Prepared by:</span> Steve Cornish</p>
                <p><span class="label">Email:</span> steve@cristalwindows.co.uk</p>
                <p><span class="label">Lead Time:</span> 4-6 weeks on standard range products</p>
                <p><span class="label">Guarantee:</span> 10 years Parts & Labour</p>
            </div>
        </td>
    </tr>
</table>

<p><strong>Dear <?php echo esc_html($data['customer_name']); ?>,</strong></p>

<p>Thank you for your recent enquiry regarding windows, doors and conservatories. We are pleased to provide you with the following quotation based on your requirements.</p>

<p>This quotation is based on a supply and installation service. All prices are given in good faith and are subject to a signed company contract and final survey. Prices are inclusive of VAT at 20%.</p>

<?php if (!empty($data['quote_price'])): ?>
<p><strong>TOTAL QUOTE PRICE: £<?php echo number_format((float)$data['quote_price'], 2); ?></strong></p>
<?php endif; ?>

<p>We look forward to working with you on this project. Should you have any questions or require any clarification, please do not hesitate to contact us.</p>

<p>Best regards,<br><strong>Cristal Windows, Doors &amp; Conservatories Ltd</strong></p>
</div>

<!-- Items -->
<div class="items-section">
<h2>QUOTATION DETAILS</h2>

<?php
$item_number = 1;
foreach ($data['basket_items'] as $item):
    $item_letter = chr(64 + $item_number); // A, B, C, etc.

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
?>
<?php
    // Convert type slug to display name
    $type_display_name = !empty($data['type_lookup'][$item['type_name']])
        ? $data['type_lookup'][$item['type_name']]
        : $item['type_name'];
?>
<div class="item">
    <div class="item-header">
        <?php echo $item_letter; ?>)
        <?php if (!empty($brand_name)): ?>
            <?php echo esc_html($brand_name); ?>
        <?php endif; ?>
        <?php echo esc_html($type_display_name); ?>
        <?php if (!empty($item['material_name']) && $item['material_name'] !== 'N/A'): ?>
            <?php echo esc_html($item['material_name']); ?>
        <?php endif; ?>
    </div>

    <div class="item-details">
        <?php if (!empty($item['category'])): ?>
        <div class="item-detail">
            <strong>Category:</strong> <?php echo esc_html(ucfirst($item['category'])); ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($item['type_name'])): ?>
        <div class="item-detail">
            <strong>Type:</strong> <?php echo esc_html($type_display_name); ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($item['material_name']) && $item['material_name'] !== 'N/A'): ?>
        <div class="item-detail">
            <strong>Material:</strong> <?php echo esc_html($item['material_name']); ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($item['style_name'])): ?>
        <div class="item-detail">
            <strong>Style:</strong> <?php echo esc_html($item['style_name']); ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($item['width']) && !empty($item['height'])): ?>
        <div class="item-detail">
            <strong>Dimensions:</strong> <?php echo esc_html($item['width']); ?>mm (W) x <?php echo esc_html($item['height']); ?>mm (H)
        </div>
        <?php endif; ?>

        <?php if (!empty($item['segment_widths'])): ?>
        <div class="item-detail">
            <strong>Segment Widths:</strong> <?php
                $widths = array_map('trim', explode(',', $item['segment_widths']));
                $parts = array();
                foreach ($widths as $idx => $w) {
                    $parts[] = 'S' . ($idx + 1) . ': ' . esc_html($w) . 'mm';
                }
                echo implode(', ', $parts);
            ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($item['cill'])): ?>
        <div class="item-detail">
            <strong>Cill:</strong> <?php echo esc_html($item['cill']); ?>
        </div>
        <?php endif; ?>

        <?php
        // Only show Side Panels for Composite Doors
        $type_name_lower = strtolower($item['type_name'] ?? '');
        if (!empty($item['side_panels']) && (strpos($type_name_lower, 'composite') !== false || strpos($type_name_lower, 'door-co') !== false || strpos($type_name_lower, 'doorco') !== false)):
        ?>
        <div class="item-detail">
            <strong>Side Panels:</strong> <?php echo esc_html($item['side_panels']); ?>
        </div>
        <?php endif; ?>

        <?php
        // Only show Infill Panel for Glazed Doors with midrail styles
        $show_infill_panel_styles = array('1803', '1804', '1811', '1812', '1813', '1814', '1815', '1816', '1827', '1828', '1829', '1830', '1831', '1832');
        $style_name_pdf = strtolower($item['style_name'] ?? '');
        $has_midrail_style_pdf = false;
        foreach ($show_infill_panel_styles as $style_num) {
            if (strpos($style_name_pdf, $style_num) !== false) {
                $has_midrail_style_pdf = true;
                break;
            }
        }
        if (!empty($item['infill_panel']) && strpos($type_name_lower, 'glazed') !== false && $has_midrail_style_pdf):
        ?>
        <div class="item-detail">
            <strong>Infill Panel:</strong> <?php echo esc_html($item['infill_panel']); ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($item['inside_colour'])): ?>
        <div class="item-detail">
            <strong>Inside Colour:</strong> <?php echo esc_html($item['inside_colour']); ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($item['outside_colour'])): ?>
        <div class="item-detail">
            <strong>Outside Colour:</strong> <?php echo esc_html($item['outside_colour']); ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($item['glazing_type']) && !$hide_glazing): ?>
        <div class="item-detail">
            <strong>Glazing Type:</strong> <?php echo esc_html(ucfirst($item['glazing_type'])); ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($item['glazing_patterns']) && !$hide_glazing): ?>
        <div class="item-detail">
            <strong>Glazing Pattern:</strong> <?php echo esc_html(ucfirst($item['glazing_patterns'])); ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($item['glazing_features']) && !$hide_glazing): ?>
        <div class="item-detail">
            <strong>Glazing Features:</strong> <?php echo esc_html(ucfirst($item['glazing_features'])); ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($item['hardware_colour'])): ?>
        <div class="item-detail">
            <strong>Hardware Colour:</strong> <?php echo esc_html(ucfirst($item['hardware_colour'])); ?>
        </div>
        <?php endif; ?>

        <?php
        // Only show Opening for French Doors, Glazed Doors, and Composite Doors
        $show_opening = strpos($type_name_lower, 'french') !== false || strpos($type_name_lower, 'glazed') !== false || strpos($type_name_lower, 'composite') !== false || strpos($type_name_lower, 'door-co') !== false || strpos($type_name_lower, 'doorco') !== false;
        if ($show_opening && !empty($item['opening'])):
        ?>
        <div class="item-detail">
            <strong>Opening:</strong> <?php echo esc_html($item['opening']); ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($item['location'])): ?>
        <div class="item-detail">
            <strong>Location:</strong> <?php echo esc_html($item['location']); ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($service_names) || !empty($item['location'])): ?>
        <div class="item-detail">
            <strong>Technical detail:</strong>
            <?php
            $tech_details = array();
            $tech_details[] = $item_number; // Item number
            if (!empty($item['location'])) {
                $tech_details[] = esc_html($item['location']);
            }
            foreach ($service_names as $service_name) {
                $tech_details[] = esc_html($service_name);
            }
            echo implode(' - ', $tech_details);
            ?>
        </div>
        <?php endif; ?>
    </div>

    <?php if (isset($item['item_price']) && !empty($item['item_price'])): ?>
    <div class="item-price">
        Item Price: £<?php echo number_format((float)$item['item_price'], 2); ?>
    </div>
    <?php endif; ?>
</div>
<?php
    $item_number++;
endforeach;
?>
</div>

<!-- Terms and Conditions -->
<div class="terms">
<h3>TERMS AND CONDITIONS</h3>

<p><strong>All prices are given in good faith. Subject to signed company contract and final survey.</strong></p>
<p>Based on Supply &amp; Install, make good to immediate fitting area. Generated debris removal from site.</p>
<p><strong>Prices are inclusive of VAT@20%</strong></p>
<p><strong>PRICES ARE HELD FOR A PERIOD OF 14 DAYS.</strong></p>

<p style="margin-top: 15px;">This quotation is valid for 14 days from the date shown above. Payment terms and schedule will be confirmed upon contract signing. Installation timescales will be confirmed following the final survey.</p>
</div>

<!-- Footer -->
<div class="footer">
<p><strong>Cristal Windows, Doors &amp; Conservatories Ltd</strong></p>
<p>Registered in England No. 5829993 | Registered address as above | VAT Registration No. 890 4307 21</p>
</div>
</body>
</html>
