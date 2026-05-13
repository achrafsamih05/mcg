<?php
  /**
  * Vercel Serverless Function (runtime: vercel-php).
  *
  * Publicly reachable at /forms/product-request.php (via the rewrite in
  * vercel.json) so existing HTML forms keep working without edits.
  * Actually executed from /api/product-request.php.
  *
  * Requires the "PHP Email Form" library (pro-only), expected at:
  *   assets/vendor/php-email-form/php-email-form.php
  */

  // ---------------------------------------------------------------------------
  // Zoho SMTP credentials (from Vercel Environment Variables)
  // ---------------------------------------------------------------------------
  //   SMTP_USERNAME = full Zoho mailbox (e.g. sourcing@mcg-global.com)
  //   SMTP_PASSWORD = a Zoho app-specific password (NOT your account password)
  $smtp_username = getenv('SMTP_USERNAME');
  $smtp_password = getenv('SMTP_PASSWORD');

  // Zoho rejects any message whose "From" header does not match the
  // authenticated mailbox ("Relaying disallowed" / 553). We therefore
  // ALWAYS send as the SMTP user and keep the visitor's address in
  // the message body for manual reply.
  $sending_email_address   = $smtp_username;
  $sending_name            = $smtp_username;

  // Where product requests should land in the inbox.
  $receiving_email_address = 'sourcing@mcg-global.com';

  // ---------------------------------------------------------------------------
  // Load the PHP Email Form library
  // ---------------------------------------------------------------------------
  $php_email_form = __DIR__ . '/../assets/vendor/php-email-form/php-email-form.php';
  if ( file_exists($php_email_form) ) {
    require( $php_email_form );
  } else {
    die( 'Unable to load the "PHP Email Form" Library!' );
  }

  $contact = new PHP_Email_Form;
  $contact->ajax = true;

  $contact->to      = $receiving_email_address;

  // Strictly match the SMTP user to avoid Zoho "Relaying disallowed" errors.
  $contact->from_email = $sending_email_address;
  $contact->from_name  = $sending_name;

  $contact->subject = 'New Product Request - MCG-GLOBAL';

  // SMTP (Zoho) - SSL on port 465, authentication enabled
  $contact->smtp = array(
    'host'     => 'smtppro.zoho.com',
    'username' => $smtp_username,
    'password' => $smtp_password,
    'port'     => '465'
  );

  // Contact fields - include the visitor's real email so you can reply manually.
  $contact->add_message( $_POST['name'],  'Name' );
  $contact->add_message( $_POST['email'], 'Reply to this address' );
  isset($_POST['phone'])   && $contact->add_message( $_POST['phone'],   'Phone / WhatsApp' );
  isset($_POST['company']) && $contact->add_message( $_POST['company'], 'Company' );

  // Product request fields
  $contact->add_message( $_POST['product_name'], 'Product Name' );
  isset($_POST['category']) && $contact->add_message( $_POST['category'], 'Category' );
  $contact->add_message( $_POST['description'], 'Description', 10 );
  $contact->add_message( $_POST['quantity'],    'Quantity' );
  $contact->add_message( $_POST['budget'],      'Budget' );

  echo $contact->send();
?>
