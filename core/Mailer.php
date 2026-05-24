<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private static $TEAL = "#0A9696";
    private static $LT_TEAL = "#19AFAC";
    private static $PALE = "#D6F0EF";
    private static $CHARCOAL = "#333333";
    private static $GREY = "#6E6E6E";

    public static function send($to, $subject, $body, $attachments = [], $cc = null) {
        $mail = new PHPMailer(true);
        global $CONFIG;

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = $CONFIG['smtp_host'] ?? 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $CONFIG['gmail_address'] ?? '';
            $mail->Password   = $CONFIG['gmail_app_password'] ?? '';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = $CONFIG['smtp_port'] ?? 465;

            // Recipients
            $mail->setFrom($CONFIG['gmail_address'] ?? 'noreply@omnispace3d.com', 'OmniShop Orders');
            $mail->addAddress($to);
            if ($cc) $mail->addCC($cc);

            // Attachments
            foreach ($attachments as $filename => $data) {
                if (is_numeric($filename)) {
                    // Simple path attachment
                    $mail->addAttachment($data);
                } else {
                    // String attachment (e.g. PDF data)
                    $mail->addStringAttachment($data, $filename);
                }
            }

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
            return false;
        }
    }

    public static function buildBaseHtml($title, $body_html, $event_name = "Solar and Storage Live Kenya 2026") {
        $teal = self::$TEAL;
        $lt_teal = self::$LT_TEAL;
        $pale = self::$PALE;
        $charcoal = self::$CHARCOAL;
        $grey = self::$GREY;

        global $CONFIG;
        $c_name = $CONFIG['company_name'] ?? "OmniSpace 3D Events Ltd";
        $c_web  = $CONFIG['company_website'] ?? "www.omnispace3d.com";
        $c_wa   = $CONFIG['company_whatsapp'] ?? "+254 731 001 723";
        $c_ph   = $CONFIG['company_phone'] ?? "+254 204 489 504";

        return "
<!DOCTYPE html>
<html>
<head>
  <meta charset='utf-8'>
  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
</head>
<body style='margin:0;padding:0;background:#f4f4f4;font-family:Arial,sans-serif;'>
  <table width='100%' cellpadding='0' cellspacing='0' style='background:#f4f4f4;padding:30px 0;'>
    <tr><td align='center'>
      <table width='620' cellpadding='0' cellspacing='0'
             style='background:#ffffff;border-radius:8px;overflow:hidden;max-width:620px;width:100%;'>
        <!-- HEADER -->
        <tr>
          <td style='background:{$teal};padding:28px 32px;text-align:center;'>
            <div style='font-size:22px;font-weight:bold;color:#ffffff;letter-spacing:1px;'>
              {$c_name}
            </div>
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
          <td style='background:#f9f9f9;padding:18px 32px;border-top:1px solid #eeeeee;
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
        $teal = self::$TEAL;
        $pale = self::$PALE;
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
        $teal = self::$TEAL;
        $pale = self::$PALE;
        $grey = self::$GREY;
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
}
