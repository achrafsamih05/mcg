# MCG API (Vercel Serverless PHP)

These serverless functions are reachable at:

- `/forms/contact.php`          -> rewritten to `/api/contact.php`
- `/forms/product-request.php`  -> rewritten to `/api/product-request.php`

The rewrite lives in the root `vercel.json`, so the HTML forms keep
posting to `forms/*.php` without changes.

## Dependencies

PHPMailer is pulled in via Composer and its `vendor/` directory is
committed to the repo so the `vercel-php` runtime can autoload it
without a build step on Vercel.

If you ever need to refresh dependencies:

```bash
cd api
composer install --no-dev -o
cd ..
git add api/composer.json api/composer.lock api/vendor
git commit -m "Update PHPMailer"
git push
```

## Environment variables

Set these in the Vercel Dashboard under
**Project -> Settings -> Environment Variables**:

| Name            | Value                                                         |
| --------------- | ------------------------------------------------------------- |
| `SMTP_USERNAME` | Full Zoho mailbox, e.g. `contact@mcg-global.com`              |
| `SMTP_PASSWORD` | A Zoho **app-specific password** (not your account password). |

Generate the app password at **Zoho Mail -> Settings -> Security ->
App Passwords**.

## Zoho SMTP constraint

Zoho rejects any message whose `From` address does not match the
authenticated SMTP user (the "Relaying disallowed" / 553 error).
Both handlers force `setFrom($smtp_username)` and preserve the
visitor's real address via `addReplyTo(...)` and inside the message
body, so you can hit Reply in Zoho to respond directly.

## Response format (for BootstrapMade `validate.js`)

The template's JS expects:

- **Success:** HTTP 200 with body exactly `OK`.
- **Failure:** HTTP 200 with a human-readable error string, which is
  displayed to the user verbatim. (Non-2xx responses are swallowed
  into a generic status-line error, so both handlers return 200 even
  on failure and put the error in the body.)
