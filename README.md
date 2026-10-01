# Galerija Divila

Code-only snapshot of the OpenCart site served from
`/home/aldoniah/divila.aldonia.hr/upload`.

## Intentionally excluded

- live `upload/config.php` and `upload/admin/config.php` credentials;
- the database and all database/backup archives;
- product media in `upload/image/catalog/` and generated image cache;
- logs, sessions, caches, generated modifications and temporary uploads;
- cPanel, ACME, macOS and local editor metadata;
- ad-hoc spreadsheet exports from the admin directory.

The live database and product media must be backed up separately. They do not
belong in this public repository.

## Local/deployment setup

1. Copy `upload/config.example.php` to `upload/config.php`.
2. Copy `upload/admin/config.example.php` to `upload/admin/config.php`.
3. Replace every placeholder with environment-specific paths and credentials.
4. Restore product media to `upload/image/catalog/` and provision a compatible
   OpenCart database outside Git.
5. Ensure the configured storage directory is writable by the web server.

The bundled PayPal helper reads its client credentials from these environment
variables rather than keeping shared credentials in source control:

- `OPENCART_PAYPAL_SANDBOX_CLIENT_ID`
- `OPENCART_PAYPAL_SANDBOX_CLIENT_SECRET`
- `OPENCART_PAYPAL_CLIENT_ID`
- `OPENCART_PAYPAL_CLIENT_SECRET`

The maintenance cron script reads its database connection from these
environment variables:

- `OPENCART_DB_HOST` (defaults to `localhost`)
- `OPENCART_DB_PORT` (defaults to `3306`)
- `OPENCART_DB_USERNAME`
- `OPENCART_DB_PASSWORD`
- `OPENCART_DB_DATABASE`

The source snapshot was prepared from the production server on 2026-10-01.

## Compliance modules

This repository also contains the Galerija Divila OpenCart 3.0.3.8 implementation for:

- configurable anchor/reference prices with audited CSV import and responsive storefront display;
- daily atomic CSV/XML digital price-list publication with checksums and a 30-day archive;
- a two-step unilateral contract-withdrawal form with customer/admin notifications, admin records, GDPR notice and anti-spam controls.

Deployment and activation details are in [Sidrene cijene i digitalni cjenik](docs/sidrene_cijene_i_digitalni_cjenik.md) and [Jednostrani raskid ugovora](docs/raskid_ugovora.md).
