PHP form handlers for MCG-GLOBAL
================================

This folder contains the server-side handlers for the two forms on the site:

- contact.php         handles the Contact Us form (contact.html)
- product-request.php handles the Request a Quote form (appointment.html)

Both scripts use the "PHP Email Form" library, which ships only with the pro
version of the original template. The library is expected at:

    assets/vendor/php-email-form/php-email-form.php

Configuration
-------------

1. Update the $receiving_email_address variable at the top of each file with
   the address that should receive form submissions.
2. If you want to send through SMTP, uncomment and fill in the $contact->smtp
   block with your SMTP host, username, password and port.

Reference
---------

Template originally based on: https://bootstrapmade.com/clinic-bootstrap-template/
