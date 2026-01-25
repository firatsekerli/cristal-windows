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
                    <span class="info-label">Lead Time:</span> 4-6 weeks on standard range products
                </div>
                <div class="info-item">
                    <span class="info-label">Guarantee:</span> 10 years Parts & Labour
                </div>
            </div>
        </div>
    </div>

    <div class="message-section">
        <p>Dear <?php echo esc_html($data['customer_name']); ?>,</p>

        <p>Thank you for your recent enquiry regarding windows, doors and conservatories. We are pleased to provide you with the following quotation based on your requirements.</p>

        <p>This quotation is based on a supply and installation service. All prices are given in good faith and are subject to a signed company contract and final survey. <strong>Prices are inclusive of VAT at 20%.</strong></p>
    </div>

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

            <?php if (!empty($item['cill'])): ?>
            <div class="item-detail-row">
                <span class="item-detail-label">Cill:</span>
                <span class="item-detail-value"><?php echo esc_html($item['cill']); ?></span>
            </div>
            <?php endif; ?>

            <?php if (!empty($item['side_panels'])): ?>
            <div class="item-detail-row">
                <span class="item-detail-label">Number of Side Panels:</span>
                <span class="item-detail-value"><?php echo esc_html($item['side_panels']); ?></span>
            </div>
            <?php endif; ?>

            <?php if (!empty($item['inside_colour'])): ?>
            <div class="item-detail-row">
                <span class="item-detail-label">Inside Colour:</span>
                <span class="item-detail-value"><?php echo esc_html($item['inside_colour']); ?></span>
            </div>
            <?php endif; ?>

            <?php if (!empty($item['outside_colour'])): ?>
            <div class="item-detail-row">
                <span class="item-detail-label">Outside Colour:</span>
                <span class="item-detail-value"><?php echo esc_html($item['outside_colour']); ?></span>
            </div>
            <?php endif; ?>

            <?php if (!empty($item['glazing_type'])): ?>
            <div class="item-detail-row">
                <span class="item-detail-label">Glazing Type:</span>
                <span class="item-detail-value"><?php echo esc_html(ucfirst($item['glazing_type'])); ?></span>
            </div>
            <?php endif; ?>

            <?php if (!empty($item['glazing_patterns'])): ?>
            <div class="item-detail-row">
                <span class="item-detail-label">Glazing Pattern:</span>
                <span class="item-detail-value"><?php echo esc_html($item['glazing_patterns']); ?></span>
            </div>
            <?php endif; ?>

            <?php if (!empty($item['glazing_features'])): ?>
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

            <?php if (!empty($item['opening'])): ?>
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
