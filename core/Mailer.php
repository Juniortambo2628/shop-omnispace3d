<?php

require_once __DIR__ . '/Branding.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    // Colors are sourced from Branding class (single source of truth)

    public static function send($to, $subject, $body, $attachments = [], $cc = null, $embeddedImages = []) {
        $mail = new PHPMailer(true);
        global $CONFIG;

        try {
            $mail->isSMTP();
            $mail->Host       = $CONFIG['smtp_host'] ?? 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $CONFIG['gmail_address'] ?? '';
            $mail->Password   = $CONFIG['gmail_app_password'] ?? '';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = $CONFIG['smtp_port'] ?? 465;

            $mail->setFrom($CONFIG['gmail_address'] ?? 'noreply@omnispace3d.com', 'OmniShop Orders');
            $mail->addAddress($to);
            if ($cc) $mail->addCC($cc);

            foreach ($attachments as $filename => $data) {
                if (is_numeric($filename)) {
                    $mail->addAttachment($data);
                } else {
                    $mail->addStringAttachment($data, $filename);
                }
            }

            foreach ($embeddedImages as $cid => $path) {
                $mail->addEmbeddedImage($path, $cid);
            }

            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
            return false;
        }
    }

    public static function logoPath(): ?string
    {
        return Branding::emailLogoPath();
    }

    public static function buildBaseHtml($title, $body_html, $event_name = "Solar and Storage Live Kenya 2026") {
        $teal = Branding::TEAL;
        $lt_teal = Branding::LT_TEAL;
        $pale = Branding::PALE;
        $charcoal = Branding::CHARCOAL;
        $grey = Branding::GREY;

        global $CONFIG;
        $c_name = $CONFIG['company_name'] ?? "OmniSpace 3D Events Ltd";
        $c_web  = $CONFIG['company_website'] ?? "www.omnispace3d.com";
        $c_wa   = $CONFIG['company_whatsapp'] ?? "+254 731 001 723";
        $c_ph   = $CONFIG['company_phone'] ?? "+254 204 489 504";

        $logoPath = self::logoPath();
        $logoHtml = $logoPath ? "<img src='cid:omnispace-logo' style='height:40px;margin-bottom:8px;' alt='{$c_name}'>" : "<div style='font-size:22px;font-weight:bold;color:#ffffff;letter-spacing:1px;'>{$c_name}</div>";

        return "
<!DOCTYPE html>
<html>
<head>
  <meta charset='utf-8'>
  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
</head>
<body style='margin:0;padding:0;background:#f0f0f0;font-family:Arial,sans-serif;'>
  <table width='100%' cellpadding='0' cellspacing='0' style='background:#f0f0f0;padding:30px 0;'>
    <tr><td align='center'>
      <table width='620' cellpadding='0' cellspacing='0'
             style='background:#ffffff;border-radius:8px;overflow:hidden;max-width:620px;width:100%;'>
        <!-- HEADER with Logo -->
        <tr>
          <td style='background:{$teal};padding:20px 32px;text-align:center;'>
            {$logoHtml}
            <div style='font-size:13px;color:{$pale};margin-top:4px;'>{$event_name}</div>
          </td>
        </tr>
        <!-- TITLE BAR -->
        <tr>
          <td style='background:{$lt_teal};padding:12px 32px;'>
            <span style='font-size:16px;font-weight:bold;color:#ffffff;'>{$title}</span>
          </td>
        </tr>
        <!-- BODY -->
        <tr>
          <td style='padding:28px 32px;color:{$charcoal};font-size:14px;line-height:1.7;'>
            {$body_html}
          </td>
        </tr>
        <!-- FOOTER -->
        <tr>
          <td style='background:#f9fffe;padding:18px 32px;border-top:1px solid #f0f0f0;
                     text-align:center;color:{$grey};font-size:12px;'>
            {$c_name} &nbsp;|&nbsp; {$c_web}<br>
            WhatsApp: {$c_wa} &nbsp;|&nbsp; Tel: {$c_ph}
          </td>
        </tr>
      </table>
    </td></tr>
  </table>
</body>
</html>";
    }

    public static function buildItemsTable($items) {
        $teal = Branding::TEAL;
        $pale = Branding::PALE;
        $rows = "";
        foreach ($items as $idx => $item) {
            $bg = ($idx % 2 == 0) ? $pale : "#ffffff";
            $color = !empty($item['color_name']) ? " (" . $item['color_name'] . ")" : "";
            $rows .= "
            <tr style='background:{$bg};'>
              <td style='padding:8px 10px;'>" . htmlspecialchars($item['product_code']) . "</td>
              <td style='padding:8px 10px;'>" . htmlspecialchars($item['product_name']) . "{$color}</td>
              <td style='padding:8px 10px;text-align:center;'>" . $item['quantity'] . "</td>
              <td style='padding:8px 10px;text-align:right;'>$" . number_format($item['unit_price'], 2) . "</td>
              <td style='padding:8px 10px;text-align:right;font-weight:bold;'>$" . number_format($item['total_price'], 2) . "</td>
            </tr>";
        }

        return "
        <table width='100%' cellpadding='0' cellspacing='0'
               style='border-collapse:collapse;margin:16px 0;font-size:13px;'>
          <thead>
            <tr style='background:{$teal};color:#ffffff;'>
              <th style='padding:9px 10px;text-align:left;'>Code</th>
              <th style='padding:9px 10px;text-align:left;'>Product</th>
              <th style='padding:9px 10px;text-align:center;'>Qty</th>
              <th style='padding:9px 10px;text-align:right;'>Unit Price</th>
              <th style='padding:9px 10px;text-align:right;'>Total</th>
            </tr>
          </thead>
          <tbody>{$rows}</tbody>
        </table>";
    }

    public static function buildTotalsBlock($order) {
        $teal = Branding::TEAL;
        $pale = Branding::PALE;
        $grey = Branding::GREY;
        return "
        <table width='100%' cellpadding='0' cellspacing='0'
               style='font-size:14px;margin-top:8px;'>
          <tr>
            <td style='padding:4px 10px;text-align:right;width:75%;color:{$grey};'>Subtotal:</td>
            <td style='padding:4px 10px;text-align:right;'>$" . number_format($order['subtotal'], 2) . "</td>
          </tr>
          <tr>
            <td style='padding:4px 10px;text-align:right;color:{$grey};'>VAT (16%):</td>
            <td style='padding:4px 10px;text-align:right;'>$" . number_format($order['vat'], 2) . "</td>
          </tr>
          <tr style='background:{$pale};'>
            <td style='padding:8px 10px;text-align:right;font-weight:bold;'>TOTAL (incl. VAT):</td>
            <td style='padding:8px 10px;text-align:right;font-weight:bold;color:{$teal};font-size:16px;'>$" . number_format($order['total'], 2) . "</td>
          </tr>
        </table>";
    }

    public static function getEmbeddedImages(): array
    {
        $path = self::logoPath();
        return $path ? ['omnispace-logo' => $path] : [];
    }

    public static function replaceTemplateVars($template, $order, $items, $config, $event) {
        $customId = $order['custom_order_id'] ?? $order['id'];
        $paypalLink = $config['paypal_payment_link'] ?? '#';
        $portalUrl = $config['payment_portal_url'] ?? '#';
        $companyEmail = $config['company_email'] ?? 'solarandstorage@omnispace3d.com';
        $companyPhone = $config['company_phone'] ?? '+254 731 001 723';

        $itemsTable = self::buildItemsTable($items);
        $totalsBlock = self::buildTotalsBlock($order);

        $replacements = [
            '{contact_name}' => htmlspecialchars($order['contact_name'] ?? ''),
            '{order_id}' => htmlspecialchars($customId),
            '{order_status}' => htmlspecialchars($order['status'] ?? 'Pending'),
            '{payment_verification_status}' => htmlspecialchars(ucfirst($order['payment_verification_status'] ?? 'unverified')),
            '{booth_number}' => htmlspecialchars($order['booth_number'] ?? ''),
            '{company_email}' => htmlspecialchars($companyEmail),
            '{company_phone}' => htmlspecialchars($companyPhone),
            '{paypal_link}' => htmlspecialchars($paypalLink),
            '{payment_portal_url}' => htmlspecialchars($portalUrl),
            '{items_table}' => $itemsTable,
            '{totals_block}' => $totalsBlock,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    public static function sendNewOrderEmail($order, $items, $config, $event) {
        $template = $config['email_template_new_order'] ?? '';
        if (empty($template)) {
            $template = '<p>Dear {contact_name},</p><p>Thank you for your order <strong>{order_id}</strong>. We have received it and our team will review it shortly.</p><p>Your order is currently <span style="color:#F59E0B;font-weight:bold;">Pending Review</span>.</p><p>Our team will check availability and confirm your order within 24 hours.</p><p>{items_table}<br>{totals_block}</p>';
        }

        $body = self::replaceTemplateVars($template, $order, $items, $config, $event);
        $bodyHtml = self::buildBaseHtml('Order Received', $body, $event['name'] ?? '');

        $customId = $order['custom_order_id'] ?? $order['id'];
        $subject = "Order Confirmation -- {$customId}";

        return self::send($order['email'], $subject, $bodyHtml, [], null, self::getEmbeddedImages());
    }

    public static function sendAvailabilityConfirmedEmail($order, $items, $config, $event, $invoicePdf = null) {
        $template = $config['email_template_availability_confirmed'] ?? '';
        if (empty($template)) {
            $template = '<p>Hi {contact_name},</p><p>Great news! Availability has been confirmed for your order <strong>{order_id}</strong>.</p><p>Please proceed to payment using one of the following methods:</p><ul><li><strong>PayPal:</strong> <a href="{paypal_link}">{paypal_link}</a></li><li><strong>Bank Transfer:</strong> See invoice attached for full bank details</li><li><strong>Payment Portal:</strong> <a href="{payment_portal_url}">{payment_portal_url}</a></li></ul><p><strong>Important:</strong> Payment must be made within 10 days to guarantee availability.</p><p>{items_table}<br>{totals_block}</p><p>Your tax invoice is attached. Please reference <strong>{order_id}</strong> on all payments.</p>';
        }

        $body = self::replaceTemplateVars($template, $order, $items, $config, $event);
        $bodyHtml = self::buildBaseHtml('Availability Confirmed -- Proceed to Payment', $body, $event['name'] ?? '');

        $customId = $order['custom_order_id'] ?? $order['id'];
        $subject = "Availability Confirmed -- {$customId} -- Proceed to Payment";

        $attachments = [];
        if ($invoicePdf) {
            $attachments["Invoice-{$customId}.pdf"] = $invoicePdf;
        }

        return self::send($order['email'], $subject, $bodyHtml, $attachments, null, self::getEmbeddedImages());
    }

    public static function sendPaymentProcessedEmail($order, $items, $config, $event, $invoicePdf = null) {
        $template = $config['email_template_payment_processed'] ?? '';
        if (empty($template)) {
            $template = '<p>Dear {contact_name},</p><p>Thank you! Your payment for order <strong>{order_id}</strong> has been received and processed.</p><p><strong>Order Complete</strong> — Your booth <strong>{booth_number}</strong> is now confirmed.</p><p><strong>Billing Summary:</strong><br>{items_table}<br>{totals_block}</p><p>Your tax invoice is attached for your records.</p><p>We look forward to serving you at the event.</p>';
        }

        $body = self::replaceTemplateVars($template, $order, $items, $config, $event);
        $bodyHtml = self::buildBaseHtml('Payment Processed -- Order Complete', $body, $event['name'] ?? '');

        $customId = $order['custom_order_id'] ?? $order['id'];
        $subject = "Payment Processed -- {$customId} -- Order Complete";

        $attachments = [];
        if ($invoicePdf) {
            $attachments["Invoice-{$customId}.pdf"] = $invoicePdf;
        }

        return self::send($order['email'], $subject, $bodyHtml, $attachments, null, self::getEmbeddedImages());
    }
}