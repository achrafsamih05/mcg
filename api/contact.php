<?php
  /**
  * Vercel Serverless Function (runtime: vercel-php).
  *
  * Publicly reachable at /forms/contact.php (via the rewrite in vercel.json)
  * so existing HTML forms keep working without edits. Actually executed
  * from /api/contact.php.
  *
  * Uses PHPMailer over Zoho SMTP (smtppro.zoho.com:465, SSL).
  */

  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\Exception;

  // --- Load PHPMailer via Composer's autoloader ------------------------------
  $autoload = __DIR__ . '/vendor/autoload.php';
  if ( ! file_exists($autoload) ) {
    http_response_code(200); // validate.js swallows non-2xx responses
    echo 'Server misconfiguration: PHPMailer not installed. Run `composer install` in /api.';
    exit;
  }
  require $autoload;

  // --- Config ----------------------------------------------------------------
  // Zoho SMTP credentials come from Vercel env vars.
  $smtp_username = getenv('SMTP_USERNAME');
  $smtp_password = getenv('SMTP_PASSWORD');

  // Where submissions should land.
  $receiving_email_address = 'contact@mcg-global.com';

  // --- Collect + sanitize POST fields ----------------------------------------
  $visitor_name    = trim( (string) ($_POST['name']    ?? '') );
  $visitor_email   = trim( (string) ($_POST['email']   ?? '') );
  $visitor_phone   = trim( (string) ($_POST['phone']   ?? '') );
  $visitor_subject = trim( (string) ($_POST['subject'] ?? '') );
  $visitor_message = trim( (string) ($_POST['message'] ?? '') );

  // Minimal validation matching the BootstrapMade template expectations.
  if ( $visitor_name === '' || $visitor_email === '' || $visitor_message === '' ) {
    echo 'Please fill in all required fields.';
    exit;
  }
  if ( ! filter_var($visitor_email, FILTER_VALIDATE_EMAIL) ) {
    echo 'Please provide a valid email address.';
    exit;
  }
  if ( ! $smtp_username || ! $smtp_password ) {
    echo 'Server misconfiguration: SMTP credentials are not set.';
    exit;
  }

  // --- Send ------------------------------------------------------------------
  $mail = new PHPMailer(true);

  try {
    // SMTP (Zoho) - SSL on port 465, authentication enabled
    $mail->isSMTP();
    $mail->Host       = 'smtppro.zoho.com';
    $mail->Port       = 465;
    $mail->SMTPAuth   = true;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // implicit TLS for 465
    $mail->Username   = $smtp_username;
    $mail->Password   = $smtp_password;
    $mail->CharSet    = 'UTF-8';

    // Zoho requires the From address to equal the authenticated mailbox.
    // Anything else triggers "Relaying disallowed" / 553. We therefore
    // ALWAYS send as the SMTP user and preserve the visitor's identity
    // via Reply-To and inside the message body.
    $mail->setFrom($smtp_username, $smtp_username);
    $mail->addAddress($receiving_email_address);
    $mail->addReplyTo($visitor_email, $visitor_name);

    $mail->Subject = $visitor_subject !== ''
      ? $visitor_subject
      : 'New contact form submission';

    // Plain-text body with the visitor's real email captured for replies.
    $lines = [];
    $lines[] = 'From:    ' . $visitor_name;
    $lines[] = 'Email:   ' . $visitor_email;
    if ( $visitor_phone !== '' ) {
      $lines[] = 'Phone:   ' . $visitor_phone;
    }
    if ( $visitor_subject !== '' ) {
      $lines[] = 'Subject: ' . $visitor_subject;
    }
    $lines[] = '';
    $lines[] = 'Message:';
    $lines[] = $visitor_message;

    $mail->isHTML(false);
    $mail->Body = implode("\n", $lines);

    $mail->send();

    // BootstrapMade validate.js expects a 200 with body exactly "OK".
    echo 'OK';
  } catch ( Exception $e ) {
    // Return a 200 with a human-readable message so validate.js surfaces it.
    http_response_code(200);
    echo 'Unable to send the message. ' . $mail->ErrorInfo;
  }
?>
