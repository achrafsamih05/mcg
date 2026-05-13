<?php
  /**
  * Requires the "PHP Email Form" library
  * The "PHP Email Form" library is available only in the pro version of the template
  * Expected at: assets/vendor/php-email-form/php-email-form.php
  * For more info and help: https://bootstrapmade.com/php-email-form/
  *
  * Deployed on Vercel via the `vercel-php` community runtime.
  * SMTP credentials are pulled from Vercel Environment Variables so nothing
  * sensitive is committed to the repository.
  */

  // ---------------------------------------------------------------------------
  // Zoho SMTP configuration
  // ---------------------------------------------------------------------------
  // Set these in the Vercel dashboard -> Project -> Settings -> Environment Variables:
  //   SMTP_USERNAME = the full Zoho mailbox address (e.g. sourcing@mcg-global.com)
  //   SMTP_PASSWORD = a Zoho app-specific password (NOT your regular account password)
  $smtp_username = getenv('SMTP_USERNAME');
  $smtp_password = getenv('SMTP_PASSWORD');

  // Zoho strictly requires the "From" header to match the authenticated mailbox.
  // Using anything else (e.g. the visitor's email) will cause Zoho to reject the
  // message with a 553 / relay-denied error. We therefore send AS the SMTP user
  // and keep the visitor's address in the message body.
  $sending_email_address   = $smtp_username;

  // Where product requests should ultimately land.
  $receiving_email_address = 'sourcing@mcg-global.com';

  // ---------------------------------------------------------------------------
  // Load the PHP Email Form library
  // ---------------------------------------------------------------------------
  $php_email_form = __DIR__ . '/../assets/vendor/php-email-form/php-email-form.php';
  if ( file_exists($php_email_form) ) {
    include( $php_email_form );
  } else {
    die( 'Unable to load the "PHP Email Form" Library!' );
  }

  $contact = new PHP_Email_Form;
  $contact->ajax = true;

  $contact->to         = $receiving_email_address;
  // IMPORTANT for Zoho: from_email must equal the SMTP username.
  $contact->from_email = $sending_email_address;
  $contact->from_name  = isset($_POST['name']) ? $_POST['name'] : 'Website Product Request';
  $contact->subject    = 'New Product Request - MCG-GLOBAL';

  // SMTP (Zoho) - SSL on port 465, authentication enabled
  $contact->smtp = array(
    'host'     => 'smtppro.zoho.com',
    'username' => $smtp_username,
    'password' => $smtp_password,
    'port'     => '465'
  );

  // Contact fields
  $contact->add_message( $_POST['name'],    'Name' );
  $contact->add_message( $_POST['email'],   'Email (reply to this address)' );
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
