# Production operations runbook

This application is designed for IIS on Windows with MySQL, the database queue, and the Laravel scheduler. The scheduler drains queued email every minute, so a separate permanently open `queue:work` console is not required for this programme's expected workload.

## Required production configuration

Keep secrets only in the server `.env` file. Confirm these values before deployment:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://mentoring.isc2chapter-zimbabwe.org
APP_TIMEZONE=Africa/Harare
LOG_LEVEL=warning
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
CACHE_STORE=database
QUEUE_CONNECTION=database
MAIL_MAILER=smtp
MAIL_SCHEME=smtps
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=your-mailbox
MAIL_PASSWORD=your-password
MAIL_FROM_ADDRESS=your-approved-sender
MAIL_FROM_NAME="ISC2 Zimbabwe Mentoring"
```

`MAIL_SCHEME=smtps` with port `465` is the expected Hostinger implicit-TLS combination. Never commit the production `.env` file.

## Deploy

From `C:\inetpub\wwwroot\Other\isc2chapter-zimbabwe.org`:

```powershell
git pull origin main; composer install --no-dev --optimize-autoloader --no-interaction; npm ci; npm run build; php artisan migrate --force; php artisan optimize:clear; php artisan config:cache; php artisan route:cache; php artisan view:cache; php artisan queue:restart; php artisan schedule:run; php artisan app:operations-check
```

If `app:operations-check` reports a scheduler failure immediately after creating the scheduled task, run `php artisan schedule:run` once and repeat the check.

## Required Windows scheduled task

From an elevated PowerShell window, install or refresh the task with:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\install-scheduler-task.ps1
```

Create one task named `ISC2 Mentoring Scheduler` that runs every minute as `SYSTEM`, whether or not a user is logged in:

- Program: the full path returned by `(Get-Command php).Source`
- Arguments: `artisan schedule:run`
- Start in: `C:\inetpub\wwwroot\Other\isc2chapter-zimbabwe.org`
- Run with highest privileges
- Stop the task if it runs longer than 10 minutes
- Do not start a second instance if one is already running

The Laravel schedule performs four small jobs:

- records a health heartbeat every minute;
- drains the database notification queue every minute and exits when empty;
- sends mentoring reminders at 08:00 Zimbabwe time;
- removes failed-job records older than 30 days.

## Routine checks

After each deployment:

```powershell
php artisan migrate:status; php artisan schedule:run; php artisan app:operations-check
```

Monitor:

- `/health` — returns HTTP 200 only when all operational checks pass;
- Admin → Overview → Service readiness;
- queued and failed email counts on the admin overview;
- `storage\logs\laravel.log` for application failures.

## Failed email recovery

Inspect failures before retrying:

```powershell
php artisan queue:failed
```

After correcting the mail or network problem:

```powershell
php artisan queue:retry all; php artisan schedule:run
```

Do not repeatedly retry failures until the underlying cause has been corrected.

## Database backup and recovery

Use the hosting provider's automated MySQL backup facility and retain at least one off-server copy. Test restoration to a non-production database periodically. Before a risky deployment, take an on-demand database backup and copy the `.env` and user-uploaded `storage\app\public` directory to protected storage.

Application rollback does not automatically reverse database migrations. Restore the matching database backup when a rollback involves incompatible schema changes.

## Maintenance and rollback

To show the maintenance page:

```powershell
php artisan down --retry=60
```

After recovery:

```powershell
php artisan up; php artisan optimize:clear; php artisan config:cache; php artisan route:cache; php artisan view:cache; php artisan schedule:run; php artisan app:operations-check
```

Keep the previous known-good Git commit identifier in deployment records. Do not use destructive Git reset commands on the server without a verified database and file backup.
