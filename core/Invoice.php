<?php

use Dompdf\Dompdf;
use Dompdf\Options;

class Invoice {
    public static function generate($order, $items, $event) {
        global $CONFIG;

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');
        $options->set('isFontSubsettingEnabled', true);

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
        $lightGrey = "#999999";
        $borderGrey = "#e0e0e0";

        $order_id = $order['custom_order_id'] ?? $order['id'];
        $date = date('d M Y', strtotime($order['created_at']));

        $company_name    = $config['company_name'] ?? "OmniSpace 3D Events Ltd";
        $company_addr    = $config['company_address'] ?? "Eldama Office Park, Nairobi, Kenya";
        $company_pin     = $config['company_pin'] ?? "";
        $company_vat_no  = $config['company_vat_no'] ?? "";
        $company_email   = $config['company_email'] ?? "solarandstorage@omnispace3d.com";
        $company_phone   = $config['company_phone'] ?? "+254 731 001 723";
        $company_website = $config['company_website'] ?? "www.omnispace3d.com";
        $payment_note    = $config['invoice_payment_note'] ?? "Please make payment within 14 days quoting your Invoice Number.";

        $bank_transfer   = $config['bank_transfer_details'] ?? "Account Name: Omnispace (K) Ltd\nBank: NCBA, Upper Hill Branch\nAcct: 8057990042 (USD)\nSWIFT: CBAFKENX";
        $disclaimer      = $config['invoice_disclaimer_text'] ?? "";
        $bank_warning    = $config['bank_charge_warning_text'] ?? "ALL BANK CHARGES TO BE BORNE BY SENDER";

        $sig_left_title  = $config['invoice_signatory_left_title'] ?? "Customer Approval";
        $sig_right_title = $config['invoice_signatory_right_title'] ?? "Omnispace Verification";
        $event_name      = $event['name'] ?? '';
        $event_venue     = $event['venue'] ?? '';
        $vat_rate        = $config['vat_rate'] ?? 16;

        $logo_src = self::logoDataUri();

        $items_rows = "";
        foreach ($items as $item) {
            $color = !empty($item['color_name']) ? "<br><small style='color:{$grey}; font-style:italic;'>Color: " . htmlspecialchars($item['color_name']) . "</small>" : "";
            $items_rows .= "
            <tr>
                <td style='padding:8px 8px; border-bottom:1px solid {$borderGrey};'>
                    <div style='font-weight:600; color:{$dark}; font-size:9px;'>" . htmlspecialchars($item['product_name']) . "</div>
                    <div style='font-size:8px; color:{$grey}; margin-top:1px;'>SKU: " . htmlspecialchars($item['product_code']) . "</div>
                    {$color}
                </td>
                <td style='padding:8px 8px; border-bottom:1px solid {$borderGrey}; text-align:center; color:{$dark}; font-size:9px;'>" . $item['quantity'] . "</td>
                <td style='padding:8px 8px; border-bottom:1px solid {$borderGrey}; text-align:right; color:{$dark}; font-size:9px;'>$" . number_format($item['unit_price'], 2) . "</td>
                <td style='padding:8px 8px; border-bottom:1px solid {$borderGrey}; text-align:right; font-weight:700; color:{$teal}; font-size:9px;'>$" . number_format($item['total_price'], 2) . "</td>
            </tr>";
        }

        $disclaimer_html = "";
        if (!empty($disclaimer)) {
            $lines = explode("\n", $disclaimer);
            $disclaimer_html = "<div style='margin-top:20px; border-top:1px solid {$borderGrey}; padding-top:10px; page-break-inside: avoid;'>
                <div style='font-size:8px; color:{$dark}; line-height:1.35;'>";
            foreach ($lines as $line) {
                $trimmed = trim($line);
                if ($trimmed === '') continue;
                $disclaimer_html .= "<div style='margin-bottom:2px;'>" . htmlspecialchars($trimmed) . "</div>";
            }
            $disclaimer_html .= "</div></div>";
        }

        $bank_warning_html = "";
        if (!empty($bank_warning)) {
            $lines = explode("\n", $bank_warning);
            $firstLine = array_shift($lines);
            $restLines = implode("\n", $lines);

            $bank_warning_html = "<div style='margin-top:12px; border:2px solid #F59E0B; border-radius:3px; padding:8px 10px; background:#FFFBEB; page-break-inside: avoid;'>
                <div style='font-size:9px; font-weight:700; color:#92400E; margin-bottom:4px;'>IMPORTANT: " . htmlspecialchars($firstLine) . "</div>
                <div style='font-size:7.5px; color:#78350F; line-height:1.35;'>" . htmlspecialchars($restLines) . "</div>
            </div>";
        }

        $bank_lines = explode("\n", $bank_transfer);
        $bank_html = "";
        foreach ($bank_lines as $line) {
            $bank_html .= "<div style='font-size:8px; color:{$dark}; line-height:1.4;'>" . htmlspecialchars(trim($line)) . "</div>";
        }

        $display_id = $order['custom_order_id'] ?? $order['id'];

        return "
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 9px; color: {$dark}; line-height: 1.35; margin: 0; padding: 0; }
        .page { padding: 30px 35px; }
        .header { margin-bottom: 24px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .logo { width: 180px; }
        .invoice-title { text-align: right; }
        .invoice-title h1 { color: {$teal}; font-size: 26px; font-weight: 700; margin: 0; text-transform: uppercase; letter-spacing: 1px; }
        .invoice-title p { margin: 3px 0 0; color: {$grey}; font-size: 11px; font-weight: 600; }
        .invoice-title .display-id { font-size: 10px; color: {$teal}; font-weight: 700; font-family: monospace; }

        .info-grid { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-col { width: 33.33%; vertical-align: top; }
        .info-box { padding: 0 8px 0 0; }
        .info-label { color: {$teal}; font-size: 8px; font-weight: 700; text-transform: uppercase; margin-bottom: 5px; border-bottom: 1px solid {$teal}; display: inline-block; padding-bottom: 1px; }
        .info-content { font-size: 8.5px; }
        .info-content strong { color: {$dark}; display: block; font-size: 9px; margin-bottom: 2px; }

        .from-condensed { font-size: 8px; color: {$dark}; line-height: 1.3; }
        .from-condensed .oneline { white-space: nowrap; }

        .items-table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        .items-table th { background: {$teal}; color: #ffffff; padding: 7px 8px; text-align: left; font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; }

        .totals-container { margin-top: 12px; width: 100%; }
        .totals-table { width: 240px; float: right; border-collapse: collapse; }
        .totals-table td { padding: 5px 8px; border-bottom: 1px solid #f5f5f5; font-size: 9px; }
        .total-row { background: {$teal}; color: #ffffff; }
        .total-row td { border-bottom: none; font-size: 11px; font-weight: 700; }

        .payment-box { margin-top: 24px; padding: 12px 14px; background: {$pale}; border-left: 3px solid {$teal}; border-radius: 3px; page-break-inside: avoid; }
        .payment-box h3 { margin: 0 0 6px; font-size: 10px; color: {$teal}; font-weight: 700; }
        .payment-box p, .payment-box div { margin: 0; font-size: 8px; color: {$dark}; line-height: 1.4; }

        .bank-details { margin-top: 16px; padding: 10px 14px; background: #fff; border: 1px solid {$borderGrey}; border-radius: 3px; page-break-inside: avoid; }
        .bank-details h4 { margin: 0 0 6px; font-size: 9px; color: {$teal}; font-weight: 700; text-transform: uppercase; }

        .signatory-section { margin-top: 24px; width: 100%; border-collapse: collapse; border-top: 1px solid {$borderGrey}; padding-top: 12px; page-break-inside: avoid; }
        .signatory-col { width: 50%; vertical-align: top; padding: 8px 12px; }
        .signatory-title { font-size: 8px; font-weight: 700; color: {$teal}; text-transform: uppercase; margin-bottom: 10px; border-bottom: 1px solid {$teal}; padding-bottom: 3px; display: inline-block; }
        .sig-field { margin-bottom: 6px; }
        .sig-label { font-size: 7px; color: {$grey}; margin-bottom: 1px; }
        .sig-line { border-bottom: 1px solid {$borderGrey}; min-height: 14px; }
        .stamp-box { width: 70px; height: 70px; border: 1px solid {$borderGrey}; border-radius: 3px; margin-top: 8px; }
        .stamp-label { font-size: 7px; color: {$grey}; margin-top: 3px; }

        .footer { position: fixed; bottom: 20px; width: 100%; text-align: center; color: {$grey}; font-size: 7px; border-top: 1px solid {$borderGrey}; padding-top: 10px; left: 0; }
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
                        <p class='display-id'>{$display_id}</p>
                        <p>Ref: {$order['id']}</p>
                    </td>
                </tr>
            </table>
        </div>

        <table class='info-grid'>
            <tr>
                <td class='info-col'>
                    <div class='info-box'>
                        <div class='info-label'>From:</div>
                        <div class='from-condensed'>
                            <strong style='font-size:9px;'>{$company_name}</strong><br>
                            {$company_addr}<br>
                            {$company_email}<br>
                            {$company_phone}<br>
                            " . ($company_pin ? "PIN: {$company_pin}" : "&nbsp;") . "<br>
                            " . ($company_vat_no ? "VAT: {$company_vat_no}" : "&nbsp;") . "
                        </div>
                    </div>
                </td>
                <td class='info-col'>
                    <div class='info-box'>
                        <div class='info-label'>Bill To:</div>
                        <div class='from-condensed'>
                            <strong style='font-size:9px;'>" . htmlspecialchars($order['company_name']) . "</strong><br>
                            " . htmlspecialchars($order['contact_name']) . "<br>
                            " . htmlspecialchars($order['email']) . "<br>
                            " . htmlspecialchars($order['phone'] ?? '') . "<br>
                            " . htmlspecialchars($order['address']) . "<br>
                            <strong>Booth:</strong> " . htmlspecialchars($order['booth_number']) . "
                        </div>
                    </div>
                </td>
                <td class='info-col'>
                    <div class='info-box'>
                        <div class='info-label'>Invoice Details:</div>
                        <div class='from-condensed'>
                            <strong style='font-size:9px;'>{$event_name}</strong><br>
                            Date: {$date}<br>
                            Status: <span style='color: #f59e0b; font-weight: 700;'>" . strtoupper($order['status']) . "</span><br>
                            Venue: {$event_venue}<br>
                            &nbsp;<br>
                            &nbsp;
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
                    <td style='color: {$grey};'>VAT ({$vat_rate}%):</td>
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

        <div class='bank-details'>
            <h4>Bank Transfer Details</h4>
            {$bank_html}
        </div>

        {$bank_warning_html}

        {$disclaimer_html}

        <table class='signatory-section' style='width:100%;margin-top:20px;border-collapse:collapse;'>
            <tr>
                <td style='width:50%;vertical-align:top;padding:8px 12px;border-right:1px solid {$borderGrey};'>
                    <div class='signatory-title'>{$sig_left_title}</div>

                    <div class='sig-field'>
                        <div class='sig-label'>Name of approver (Print):</div>
                        <div class='sig-line'></div>
                    </div>
                    <div class='sig-field'>
                        <div class='sig-label'>Approver Title/Designation:</div>
                        <div class='sig-line'></div>
                    </div>
                    <div class='sig-field'>
                        <div class='sig-label'>Approver Phone Number:</div>
                        <div class='sig-line'></div>
                    </div>
                    <div class='sig-field'>
                        <div class='sig-label'>Approver Email Address:</div>
                        <div class='sig-line'></div>
                    </div>
                    <div class='sig-field'>
                        <div class='sig-label'>Approver Signature:</div>
                        <div class='sig-line'></div>
                    </div>
                    <div class='sig-field'>
                        <div class='sig-label'>Approver Date &amp; Time:</div>
                        <div class='sig-line'></div>
                    </div>
                    <div>
                        <div class='sig-label'>Company Stamp:</div>
                        <div class='stamp-box'></div>
                    </div>
                </td>
                <td style='width:50%;vertical-align:top;padding:8px 12px;'>
                    <div class='signatory-title'>{$sig_right_title}</div>

                    <div class='sig-field'>
                        <div class='sig-label'>Checked by: Omnispace Limited</div>
                        <div class='sig-line'></div>
                    </div>
                    <div class='sig-field'>
                        <div class='sig-label'>Agent Name:</div>
                        <div class='sig-line'></div>
                    </div>
                    <div class='sig-field'>
                        <div class='sig-label'>Agent Signature:</div>
                        <div class='sig-line'></div>
                    </div>
                    <div class='sig-field'>
                        <div class='sig-label'>Approval Date:</div>
                        <div class='sig-line'></div>
                    </div>
                    <div>
                        <div class='sig-label'>Company Stamp:</div>
                        <div class='stamp-box'></div>
                    </div>
                </td>
            </tr>
        </table>

        <div class='footer'>
            {$company_name} &bull; {$company_website} &bull; Email: {$company_email}<br>
            &copy; " . date('Y') . " {$company_name}. All rights reserved.
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