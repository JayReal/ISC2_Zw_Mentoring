# ISC2 Zimbabwe Mentorship and Professional Growth

A Laravel application supporting the mentorship-specific work that is not handled by the chapter CMMS: participant intake, human-reviewed matching, shared mentoring plans, meetings, check-ins, programme oversight, responsible closure, and outcome records.

## Local setup

Requirements: PHP 8.3+, Composer, Node.js, npm, and MySQL or SQLite with the matching PDO extension.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan serve
```

Run checks with:

```bash
php artisan test
npm run build
vendor/bin/pint --test
```

## Production

The application expects IIS, MySQL, database-backed cache/session/queues, SMTP mail, and one Windows scheduled task that runs `php artisan schedule:run` every minute.

See [Production operations runbook](docs/production-runbook.md) for deployment, scheduled-task, health-check, backup, failed-email, and recovery guidance.

After deploying, run:

```bash
php artisan schedule:run
php artisan app:operations-check
```

On Windows, install the required scheduler task from an elevated PowerShell window with `powershell -ExecutionPolicy Bypass -File .\scripts\install-scheduler-task.ps1`.

The public `/health` endpoint returns HTTP 200 only when the database, cache, writable storage, public storage link, database queue, and scheduler heartbeat are healthy.

## Programme boundary

This application focuses on mentoring workflows. Chapter events, general membership administration, and other chapter operations remain in the ISC2 Chapter Management System.
