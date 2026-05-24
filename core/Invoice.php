<?php

use Dompdf\Dompdf;
use Dompdf\Options;

class Invoice {
    public static function generate($order, $items, $event) {
        global $CONFIG;
        
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');
        
        $dompdf = new Dompdf($options);
        
        $html = self::buildHtml($order, $items, $event, $CONFIG);
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        return $dompdf->output();
    }

    private static function buildHtml($order, $items, $event, $config) {
        $teal = "#0A9696";
        $pale = "#f1f8f8";
        $grey = "#666666";
        $dark = "#1a1a1a";
        
        $order_id = $order['id'];
        $date = date('d M Y', strtotime($order['created_at']));
        
        // Company info from dashboard settings
        $company_name    = $config['company_name'] ?? "OmniSpace 3D Events Ltd";
        $company_addr    = $config['company_address'] ?? "Eldama Office Park, Nairobi, Kenya";
        $company_pin     = $config['company_pin'] ?? "";
        $company_vat_no  = $config['company_vat_no'] ?? "";
        $company_email   = $config['company_email'] ?? "solarandstorage@omnispace3d.com";
        $company_phone   = $config['company_phone'] ?? "+254 731 001 723";
        $company_website = $config['company_website'] ?? "www.omnispace3d.com";
        $payment_note    = $config['invoice_payment_note'] ?? "Please make payment within 14 days quoting your Invoice Number.";
        $terms           = $config['invoice_terms'] ?? "";
        
        $logo_src = self::logoDataUri();

        $items_rows = "";
        foreach ($items as $item) {
            $color = !empty($item['color_name']) ? "<br><small style='color:{$grey}; font-style:italic;'>Color: " . htmlspecialchars($item['color_name']) . "</small>" : "";
            $items_rows .= "
            <tr>
                <td style='padding:12px 10px; border-bottom:1px solid #eee;'>
                    <div style='font-weight:600; color:{$dark};'>" . htmlspecialchars($item['product_name']) . "</div>
                    <div style='font-size:10px; color:{$grey}; margin-top:2px;'>SKU: " . htmlspecialchars($item['product_code']) . "</div>
                    {$color}
                </td>
                <td style='padding:12px 10px; border-bottom:1px solid #eee; text-align:center; color:{$dark};'>" . $item['quantity'] . "</td>
                <td style='padding:12px 10px; border-bottom:1px solid #eee; text-align:right; color:{$dark};'>$" . number_format($item['unit_price'], 2) . "</td>
                <td style='padding:12px 10px; border-bottom:1px solid #eee; text-align:right; font-weight:700; color:{$teal};'>$" . number_format($item['total_price'], 2) . "</td>
            </tr>";
        }

        $terms_html = "";
        if (!empty($terms)) {
            $lines = explode("\n", $terms);
            $terms_html = "<div style='margin-top:30px; border-top:1px solid #eee; padding-top:15px;'>
                <div style='font-size:11px; font-weight:700; color:{$dark}; text-transform:uppercase; margin-bottom:8px;'>Terms &amp; Conditions</div>
                <div style='font-size:10px; color:{$grey}; line-height:1.5;'>";
            foreach ($lines as $line) {
                if (trim($line)) $terms_html .= "<div style='margin-bottom:4px;'>&bull; " . htmlspecialchars(trim($line)) . "</div>";
            }
            $terms_html .= "</div></div>";
        }

        return "
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: {$dark}; line-height: 1.4; margin: 0; padding: 0; }
        .page { padding: 45px; }
        .header { margin-bottom: 40px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .logo { width: 220px; }
        .invoice-title { text-align: right; }
        .invoice-title h1 { color: {$teal}; font-size: 32px; font-weight: 700; margin: 0; text-transform: uppercase; letter-spacing: 1px; }
        .invoice-title p { margin: 5px 0 0; color: {$grey}; font-size: 14px; font-weight: 600; }

        .info-grid { width: 100%; border-collapse: collapse; margin-bottom: 35px; }
        .info-col { width: 33.33%; vertical-align: top; }
        .info-box { padding: 0 10px 0 0; }
        .info-label { color: {$teal}; font-size: 10px; font-weight: 700; text-transform: uppercase; margin-bottom: 8px; border-bottom: 1px solid {$teal}; display: inline-block; padding-bottom: 2px; }
        .info-content { font-size: 11px; }
        .info-content strong { color: {$dark}; display: block; font-size: 12px; margin-bottom: 4px; }

        .items-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .items-table th { background: {$teal}; color: #ffffff; padding: 10px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .totals-container { margin-top: 20px; width: 100%; }
        .totals-table { width: 280px; float: right; border-collapse: collapse; }
        .totals-table td { padding: 8px 10px; border-bottom: 1px solid #f5f5f5; }
        .total-row { background: {$teal}; color: #ffffff; }
        .total-row td { border-bottom: none; font-size: 14px; font-weight: 700; }

        .payment-box { margin-top: 40px; padding: 20px; background: {$pale}; border-left: 4px solid {$teal}; border-radius: 4px; }
        .payment-box h3 { margin: 0 0 8px; font-size: 12px; color: {$teal}; }
        .payment-box p { margin: 0; font-size: 11px; color: {$dark}; line-height: 1.5; }

        .footer { position: fixed; bottom: 30px; width: 100%; text-align: center; color: {$grey}; font-size: 9px; border-top: 1px solid #eee; padding-top: 15px; left: 0; }
    </style>
</head>
<body>
    <div class='page'>
        <div class='header'>
            <table class='header-table'>
                <tr>
                    <td>
                        <img src='{$logo_src}' class='logo'>
                    </td>
                    <td class='invoice-title'>
                        <h1>Invoice</h1>
                        <p>#{$order_id}</p>
                    </td>
                </tr>
            </table>
        </div>

        <table class='info-grid'>
            <tr>
                <td class='info-col'>
                    <div class='info-box'>
                        <div class='info-label'>From:</div>
                        <div class='info-content'>
                            <strong>{$company_name}</strong>
                            {$company_addr}<br>
                            " . ($company_pin ? "PIN: {$company_pin}<br>" : "") . "
                            " . ($company_vat_no ? "VAT: {$company_vat_no}<br>" : "") . "
                            Email: {$company_email}<br>
                            Tel: {$company_phone}
                        </div>
                    </div>
                </td>
                <td class='info-col'>
                    <div class='info-box'>
                        <div class='info-label'>Bill To:</div>
                        <div class='info-content'>
                            <strong>" . htmlspecialchars($order['company_name']) . "</strong>
                            " . htmlspecialchars($order['contact_name']) . "<br>
                            " . htmlspecialchars($order['email']) . "<br>
                            " . ($order['phone'] ? htmlspecialchars($order['phone']) . "<br>" : "") . "
                            " . htmlspecialchars($order['address']) . "
                            <div style='margin-top:8px;'><strong>Booth:</strong> " . htmlspecialchars($order['booth_number']) . "</div>
                        </div>
                    </div>
                </td>
                <td class='info-col'>
                    <div class='info-box'>
                        <div class='info-label'>Invoice Details:</div>
                        <div class='info-content'>
                            <strong>{$event['name']}</strong>
                            Date: {$date}<br>
                            Status: <span style='color: #f59e0b; font-weight: 700;'>" . strtoupper($order['status']) . "</span><br>
                            Venue: {$event['venue']}
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <table class='items-table'>
            <thead>
                <tr>
                    <th style='width: 50%;'>Item Description</th>
                    <th style='width: 10%; text-align: center;'>Qty</th>
                    <th style='width: 20%; text-align: right;'>Unit Price</th>
                    <th style='width: 20%; text-align: right;'>Total Price</th>
                </tr>
            </thead>
            <tbody>
                {$items_rows}
            </tbody>
        </table>

        <div class='totals-container'>
            <table class='totals-table'>
                <tr>
                    <td style='color: {$grey};'>Subtotal:</td>
                    <td style='text-align: right; font-weight: 600;'>$" . number_format($order['subtotal'], 2) . "</td>
                </tr>
                <tr>
                    <td style='color: {$grey};'>VAT (" . ($config['vat_rate'] ?? 16) . "%):</td>
                    <td style='text-align: right; font-weight: 600;'>$" . number_format($order['vat'], 2) . "</td>
                </tr>
                <tr class='total-row'>
                    <td>Total Amount:</td>
                    <td style='text-align: right;'>$" . number_format($order['total'], 2) . "</td>
                </tr>
            </table>
            <div style='clear: both;'></div>
        </div>

        <div class='payment-box'>
            <h3>Payment Instructions</h3>
            <p>{$payment_note}</p>
        </div>

        {$terms_html}

        <div class='footer'>
            {$company_name} &bull; {$company_website} &bull; Email: {$company_email}<br>
            &copy; " . date('Y') . " OmniSpace 3D Events Ltd. All rights reserved.
        </div>
    </div>
</body>
</html>";
    }

    private static function logoDataUri(): string
    {
        $candidates = [
            STATIC_PATH . '/images/omnispace-logo.jpg',
            STATIC_PATH . '/images/omnispace-invoice-logo.jpg',
        ];

        foreach ($candidates as $path) {
            if (! is_readable($path)) {
                continue;
            }

            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $mime = match ($ext) {
                'png' => 'image/png',
                'webp' => 'image/webp',
                'gif' => 'image/gif',
                default => 'image/jpeg',
            };

            return 'data:' . $mime . ';base64,' . base64_encode((string) file_get_contents($path));
        }

        return '';
    }
}
