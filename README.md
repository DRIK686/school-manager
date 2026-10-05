# School Manager

Multi-role school management system (Laravel): students, staff, fees, attendance,
exams and report cards, timetables, role-based portals and a public website CMS.

## Install

Requirements: the PHP version in `composer.json`, MySQL/MariaDB, Composer, and a web
server whose document root is the `public/` folder.

1. `composer install --no-dev --optimize-autoloader`
2. `cp .env.example .env` then `php artisan key:generate`
3. Edit `.env`: database, `APP_URL`, mail
4. `php artisan migrate --force`
5. `php artisan school:install` (school details, colours, first academic year, Super Admin)
6. `php artisan storage:link`
7. Make `storage/` and `bootstrap/cache/` writable by the web server user
8. Log in, upload the logo under Settings, and replace the placeholder text in Website CMS

`school:install` refuses to run on a database that already has users or school settings.

## Updating

`git pull`, `composer install --no-dev --optimize-autoloader`, `php artisan migrate --force`,
then `php artisan optimize:clear`.
