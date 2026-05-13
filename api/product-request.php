<?php
  /**
  * Vercel Serverless Function (runtime: vercel-php).
  *
  * Publicly reachable at /forms/product-request.php (via the rewrite in
  * vercel.json). Actually executed from /api/product-request.php.
  *
  * Uses PHPMailer over Zoho SMTP (smtppro.zoho.com:465, SSL).
  */

  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\Exception;

  // --- Load PHPMailer via Composer's autoloader ------------------------------
  $autoload = __DIR__ . '/vendor/autoload.php';
  if ( ! file_exists($autoload) ) {
    http_response_code(200);
    echo 'Server misconfiguration: PHPMailer not installed. Run `composer install` in /api.';
    exit;
  }
  require $autoload;

  // --- Config ----------------------------------------------------------------
  $smtp_username = getenv('SMTP_USERNAME');
  $smtp_password = getenv('SMTP_PASSWORD');

  // Where product requests should land.
  $receiving_email_address = 'sourcing@mcg-global.com';

  // --- Collect + sanitize POST fields ----------------------------------------
  $visitor_name    = trim( (string) ($_POST['name']         ?? '') );
  $visitor_email   = trim( (string) ($_POST['email']        ?? '') );
  $visitor_phone   = trim( (string) ($_POST['phone']        ?? '') );
  $visitor_company = trim( (string) ($_POST['company']      ?? '') );
  $product_name    = trim( (string) ($_POST['product_name'] ?? '') );
  $category        = trim( (string) ($_POST['category']     ?? '') );
  $description     = trim( (string) ($_POST['description']  ?? '') );
  $quantity        = trim( (string) ($_POST['quantity']     ?? '') );
  $budget          = trim( (string) ($_POST['budget']       ?? '') );

  if ( $visitor_name === '' || $visitor_email === '' || $product_name === '' || $description === '' ) {
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
    $mail->isSMTP();
    $mail->Host       = 'smtppro.zoho.com';
    $mail->Port       = 465;
    $mail->SMTPAuth   = true;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // implicit TLS for 465
    $mail->Username   = $smtp_username;
    $mail->Password   = $smtp_password;
    $mail->CharSet    = 'UTF-8';

    // Zoho: From must match the authenticated mailbox.
    $mail->setFrom($smtp_username, $smtp_username);
    $mail->addAddress($receiving_email_address);
    $mail->addReplyTo($visitor_email, $visitor_name);

    $mail->Subject = 'New Product Request - MCG-GLOBAL';

    $lines = [];
    $lines[] = '=== Contact ===';
    $lines[] = 'Name:    ' . $visitor_name;
    $lines[] = 'Email:   ' . $visitor_email;
    if ( $visitor_phone !== '' )   { $lines[] = 'Phone:   ' . $visitor_phone; }
    if ( $visitor_company !== '' ) { $lines[] = 'Company: ' . $visitor_company; }
    $lines[] = '';
    $lines[] = '=== Product Request ===';
    $lines[] = 'Product:     ' . $product_name;
    if ( $category !== '' ) { $lines[] = 'Category:    ' . $category; }
    $lines[] = 'Quantity:    ' . ($quantity !== '' ? $quantity : '(not specified)');
    $lines[] = 'Budget:      ' . ($budget   !== '' ? $budget   : '(not specified)');
    $lines[] = '';
    $lines[] = 'Description:';
    $lines[] = $description;

    $mail->isHTML(false);
    $mail->Body = implode("\n", $lines);

    $mail->send();

    echo 'OK';
  } catch ( Exception $e ) {
    http_response_code(200);
    echo 'Unable to send the message. ' . $mail->ErrorInfo;
  }
?>
