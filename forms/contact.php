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
  //   SMTP_USERNAME = the full Zoho mailbox address (e.g. contact@mcg-global.com)
  //   SMTP_PASSWORD = a Zoho app-specific password (NOT your regular account password)
  $smtp_username = getenv('SMTP_USERNAME');
  $smtp_password = getenv('SMTP_PASSWORD');

  // Zoho strictly requires the "From" header to match the authenticated mailbox.
  // Using anything else (e.g. the visitor's email) will cause Zoho to reject the
  // message with a 553 / relay-denied error. We therefore send AS the SMTP user
  // and keep the visitor's address in the message body (and as Reply-To below).
  $sending_email_address   = $smtp_username;

  // Where the submission should ultimately land.
  $receiving_email_address = 'contact@mcg-global.com';

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
  $contact->from_name  = isset($_POST['name'])    ? $_POST['name']    : 'Website Contact Form';
  $contact->subject    = isset($_POST['subject']) ? $_POST['subject'] : 'New contact form submission';

  // SMTP (Zoho) - SSL on port 465, authentication enabled
  $contact->smtp = array(
    'host'     => 'smtppro.zoho.com',
    'username' => $smtp_username,
    'password' => $smtp_password,
    'port'     => '465'
  );

  // Message body - include the visitor's real email so you can reply manually.
  $contact->add_message( $_POST['name'],    'From' );
  $contact->add_message( $_POST['email'],   'Email (reply to this address)' );
  isset($_POST['phone']) && $contact->add_message( $_POST['phone'], 'Phone' );
  $contact->add_message( $_POST['message'], 'Message', 10 );

  echo $contact->send();
?>
