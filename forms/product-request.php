<?php
  /**
  * Requires the "PHP Email Form" library
  * The "PHP Email Form" library is available only in the pro version of the template
  * The library should be uploaded to: vendor/php-email-form/php-email-form.php
  * For more info and help: https://bootstrapmade.com/php-email-form/
  */

  // Replace with your real receiving email address for product requests
  $receiving_email_address = 'sourcing@mcg-global.com';

  if( file_exists($php_email_form = '../assets/vendor/php-email-form/php-email-form.php' )) {
    include( $php_email_form );
  } else {
    die( 'Unable to load the "PHP Email Form" Library!');
  }

  $contact = new PHP_Email_Form;
  $contact->ajax = true;

  $contact->to = $receiving_email_address;
  $contact->from_name = $_POST['name'];
  $contact->from_email = $_POST['email'];
  $contact->subject = 'New Product Request - MCG-GLOBAL';

  // Uncomment below code if you want to use SMTP to send emails. You need to enter your correct SMTP credentials
  /*
  $contact->smtp = array(
    'host' => 'example.com',
    'username' => 'example',
    'password' => 'pass',
    'port' => '587'
  );
  */

  // Contact fields
  $contact->add_message( $_POST['name'], 'Name');
  $contact->add_message( $_POST['email'], 'Email');
  isset($_POST['phone']) && $contact->add_message($_POST['phone'], 'Phone / WhatsApp');
  isset($_POST['company']) && $contact->add_message($_POST['company'], 'Company');

  // Product request fields
  $contact->add_message( $_POST['product_name'], 'Product Name');
  isset($_POST['category']) && $contact->add_message($_POST['category'], 'Category');
  $contact->add_message( $_POST['description'], 'Description', 10);
  $contact->add_message( $_POST['quantity'], 'Quantity');
  $contact->add_message( $_POST['budget'], 'Budget');

  echo $contact->send();
?>
