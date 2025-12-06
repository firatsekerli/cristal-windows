<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: helvetica, arial, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #000000;
        }
        .header {
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 3px solid #0066cc;
            padding-bottom: 10px;
        }
        .company-info {
            text-align: right;
        }
        .company-info h1 {
            color: #0066cc;
            margin: 0 0 8px 0;
            font-size: 18pt;
            font-weight: bold;
        }
        .company-info p {
            margin: 1px 0;
            font-size: 9pt;
        }
        .customer-section {
            margin-bottom: 20px;
        }
        .customer-section h2 {
            font-size: 11pt;
            margin-bottom: 8px;
            color: #333333;
            font-weight: bold;
        }
        .customer-details {
            line-height: 1.6;
        }
        .customer-details p {
            margin: 3px 0;
        }
        .items-section {
            margin-bottom: 20px;
        }
        .items-section h2 {
            font-size: 12pt;
            margin-bottom: 10px;
            color: #0066cc;
            border-bottom: 2px solid #0066cc;
            padding-bottom: 5px;
            font-weight: bold;
        }
        .item {
            background-color: #f9f9f9;
            border: 1px solid #cccccc;
            padding: 12px;
            margin-bottom: 12px;
            page-break-inside: avoid;
        }
        .item-header {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 8px;
            color: #0066cc;
        }
        .item-details {
            margin-top: 8px;
        }
        .item-detail {
            padding: 4px 0;
            border-bottom: 1px solid #eeeeee;
            line-height: 1.4;
        }
        .item-detail:last-child {
            border-bottom: none;
        }
        .item-detail strong {
            color: #555555;
            font-weight: bold;
        }
        .item-price {
            font-size: 12pt;
            font-weight: bold;
            color: #0066cc;
            margin-top: 8px;
            text-align: right;
        }
        .total-section {
            margin-top: 20px;
            text-align: right;
            font-size: 14pt;
            font-weight: bold;
            padding: 12px;
            background-color: #f0f7ff;
            border: 2px solid #0066cc;
        }
        .total-section .total-label {
            color: #333333;
        }
        .total-section .total-amount {
            color: #0066cc;
            font-size: 18pt;
        }
        .terms {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #cccccc;
            font-size: 8pt;
            color: #666666;
        }
        .terms h3 {
            font-size: 10pt;
            margin-bottom: 8px;
            color: #333333;
            font-weight: bold;
        }
        .terms p {
            margin: 5px 0;
            line-height: 1.4;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8pt;
            color: #666666;
            border-top: 1px solid #cccccc;
            padding-top: 12px;
        }
        .footer p {
            margin: 3px 0;
        }
        .cover-letter {
            margin-bottom: 25px;
            padding: 15px;
            background-color: #ffffff;
            border-left: 4px solid #0066cc;
        }
        .cover-letter p {
            margin: 8px 0;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div>
            <?php if (function_exists('get_field')):
                $logo = get_field('company_logo', 'option');
                if ($logo): ?>
                    <img src="<?php echo esc_url($logo['url']); ?>" alt="Company Logo" style="max-width: 200px;">
                <?php endif;
            endif; ?>
        </div>
        <div class="company-info">
            <h1>Cristal Windows, Doors &amp; Conservatories Ltd</h1>
            <p>23 Cedar Drive, Fleet, Hampshire GU51 3HD</p>
            <p>www.cristalwindows.co.uk</p>
            <p>tel: 01252 810777</p>
            <p>enquiries@cristalwindows.co.uk</p>
        </div>
    </div>

    <!-- Cover Letter -->
    <div class="cover-letter">
        <p><strong>Date:</strong> <?php echo date('d F Y'); ?></p>

        <p>Dear <?php echo esc_html($data['customer_name']); ?>,</p>

        <p>Thank you for your recent enquiry regarding windows, doors and conservatories. We are pleased to provide you with the following quotation based on your requirements.</p>

        <p>This quotation is based on a supply and installation service. All prices are given in good faith and are subject to a signed company contract and final survey. Prices are inclusive of VAT at 20%.</p>

        <p>We look forward to working with you on this project. Should you have any questions or require any clarification, please do not hesitate to contact us.</p>

        <p>Best regards,<br>
        <strong>Cristal Windows, Doors &amp; Conservatories Ltd</strong></p>
    </div>

    <!-- Customer Details -->
    <div class="customer-section">
        <h2>Quotation For:</h2>
        <div class="customer-details">
            <p><strong><?php echo esc_html($data['customer_name']); ?></strong></p>
            <?php if (!empty($data['customer_address'])): ?>
                <p><?php echo nl2br(esc_html($data['customer_address'])); ?></p>
            <?php endif; ?>
            <?php if (!empty($data['customer_postcode'])): ?>
                <p><?php echo esc_html($data['customer_postcode']); ?></p>
            <?php endif; ?>
            <p><?php echo esc_html($data['customer_phone']); ?></p>
            <p><a href="mailto:<?php echo esc_attr($data['customer_email']); ?>"><?php echo esc_html($data['customer_email']); ?></a></p>
        </div>
    </div>

    <!-- Items -->
    <div class="items-section">
        <h2>QUOTATION DETAILS</h2>

        <?php
        $item_number = 1;
        foreach ($data['basket_items'] as $item):
            $item_letter = chr(64 + $item_number); // A, B, C, etc.
        ?>
        <div class="item">
            <div class="item-header">
                <?php echo $item_letter; ?>) <?php echo esc_html($item['type_name']); ?>
                <?php if (!empty($item['material_name']) && $item['material_name'] !== 'N/A'): ?>
                    - <?php echo esc_html($item['material_name']); ?>
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
                    <strong>Type:</strong> <?php echo esc_html($item['type_name']); ?>
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

                <?php if (!empty($item['cill'])): ?>
                <div class="item-detail">
                    <strong>Cill:</strong> <?php echo esc_html($item['cill']); ?>
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

                <?php if (!empty($item['glazing_type'])): ?>
                <div class="item-detail">
                    <strong>Glazing Type:</strong> <?php echo esc_html(ucfirst($item['glazing_type'])); ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($item['glazing_features'])): ?>
                <div class="item-detail">
                    <strong>Glazing Features:</strong> <?php echo esc_html(ucfirst($item['glazing_features'])); ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($item['hardware_colour'])): ?>
                <div class="item-detail">
                    <strong>Hardware Colour:</strong> <?php echo esc_html(ucfirst($item['hardware_colour'])); ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($item['location'])): ?>
                <div class="item-detail">
                    <strong>Location:</strong> <?php echo esc_html($item['location']); ?>
                </div>
                <?php endif; ?>
            </div>

            <?php if (isset($item['item_price']) && !empty($item['item_price'])): ?>
            <div class="item-price">
                £<?php echo number_format((float)$item['item_price'], 2); ?>
            </div>
            <?php endif; ?>
        </div>
        <?php
            $item_number++;
        endforeach;
        ?>
    </div>

    <!-- Total -->
    <?php if (!empty($data['quote_price'])): ?>
    <div class="total-section">
        <span class="total-label">TOTAL QUOTE PRICE:</span>
        <span class="total-amount">£<?php echo number_format((float)$data['quote_price'], 2); ?></span>
    </div>
    <?php endif; ?>

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
