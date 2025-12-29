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
            padding-bottom: 10px;
            margin-bottom: 20px;
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
            font-size: 12px;
            color: #333;
            line-height: 1.8;
        }

        .header:after {
            content: "";
            display: table;
            clear: both;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #1a5490;
            margin-bottom: 10px;
        }

        .document-title {
            font-size: 28px;
            font-weight: bold;
            color: #1a5490;
            text-align: center;
            margin: 30px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .info-grid {
            width: 100%;
            margin-bottom: 30px;
            overflow: hidden;
        }

        .info-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            width: 48%;
            float: left;
            box-sizing: border-box;
        }

        .info-section:first-child {
            margin-right: 4%;
        }

        .info-grid:after {
            content: "";
            display: table;
            clear: both;
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
            border-radius: 8px;
            margin: 30px 0;
            text-align: center;
        }

        .quote-price {
            font-size: 32px;
            font-weight: bold;
            margin: 10px 0;
        }

        .price-breakdown {
            font-size: 14px;
            margin-top: 10px;
            opacity: 0.95;
        }

        .terms-section {
            background: #fff9e6;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
        }

        .terms-section h3 {
            color: #1a5490;
            margin-top: 0;
            margin-bottom: 10px;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e0e0e0;
            text-align: center;
            color: #666;
            font-size: 14px;
        }

        .highlight {
            background: #ffeb3b;
            padding: 2px 5px;
            border-radius: 3px;
        }

        .page-break {
            page-break-before: always;
            margin-top: 0;
            padding-top: 40px;
        }

        .terms-page {
            padding: 20px;
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
            text-align: center;
            color: #666;
            font-size: 13px;
            line-height: 1.6;
        }

        .company-footer strong {
            color: #1a5490;
            font-size: 14px;
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
    <div class="header">
        <div class="header-logo">
            <img src="https://cristalwindows.co.uk/wp-content/uploads/2025/02/Cristal-Windows-LOGO-01.png" alt="Cristal Windows Logo">
        </div>
        <div class="header-contact">
            www.cristalwindows.co.uk<br>
            01252 810777 | enquiries@cristalwindows.co.uk
        </div>
    </div>

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
                <span class="info-label">Date:</span> <?php echo date('j F Y'); ?>
            </div>
            <div class="info-item">
                <span class="info-label">Quote Reference:</span> Q-<?php echo date('Y-m-d'); ?>-<?php echo str_pad($post_id, 3, '0', STR_PAD_LEFT); ?>
            </div>
            <div class="info-item">
                <span class="info-label">Prepared by:</span> Steve Cornish
            </div>
            <div class="info-item">
                <span class="info-label">Email:</span> steve@cristalwindows.co.uk
            </div>
            <div class="info-item">
                <span class="info-label">Lead Time:</span> 4-6 weeks on standard range products
            </div>
            <div class="info-item">
                <span class="info-label">Guarantee:</span> 10 years Parts & Labour
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
