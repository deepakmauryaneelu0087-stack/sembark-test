# Linkboard URL Shortener

A small, server-rendered URL shortener built with Laravel 12, PHP 8.2+, and SQLite by default. Accounts belong to a company and have one of five roles: SuperAdmin, Admin, Member, Sales, or Manager.

## Requirements

- PHP 8.2 or newer with `pdo_sqlite`, `mbstring`, and `openssl`
- Composer
- Node.js and npm (only needed to build Laravel's frontend assets; the app's pages use plain HTML/CSS)

## Local setup

```sh
git clone <repository-url>
cd <repository-directory>
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

Open <http://127.0.0.1:8000>. The development SuperAdmin credentials are:

- Email: `superadmin@example.com`
- Password: `ChangeMe123!`

Set `SUPERADMIN_EMAIL`, `SUPERADMIN_NAME`, and `SUPERADMIN_PASSWORD` in `.env` before seeding if you need different credentials. The seeder inserts this account with a parameterized raw SQL statement. Change the sample password before exposing the app anywhere beyond a local test environment.

The seeder also adds an `Example Company`. To try company-specific screens, create users with a company and role using Tinker, for example:

```sh
php artisan tinker
```

```php
$company = App\Models\Company::firstOrCreate(['name' => 'Example Company']);
App\Models\User::create([
    'name' => 'Company Admin',
    'email' => 'admin@example.com',
    'password' => 'ChangeMe123!',
    'role' => 'Admin',
    'company_id' => $company->id,
]);
```

Invitations create a time-limited account setup link (valid for seven days). In this minimal assignment app, the link is shown to the inviter so they can share it; no mail service is configured.

## Access rules

- Only Sales and Managers assigned to a company can create short links.
- Admins can view links from companies other than their own; Members can view links they did not create.
- SuperAdmins have no short-link listing and cannot create links.
- Admins may invite Sales and Managers into their own company. SuperAdmins may invite Members, Sales, and Managers to an existing company. Neither route accepts an Admin invitation.
- The `/s/{slug}` route requires a signed-in user and redirects to the stored HTTP(S) destination after authentication.
- There is no public registration route. New accounts are set up through an invitation.

## Tests

Run the feature and framework tests with:

```sh
php artisan test
```

## AI assistance

Copilot SDK in VS Code was used to scaffold and implement the Laravel application, and to help write and run its tests. The requirements, access rules, and final changes should be reviewed by the submitter before submission.
