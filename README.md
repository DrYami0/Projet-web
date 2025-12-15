# PHP OOP CRUD - Events & Gifts

Simple PHP project demonstrating CRUD operations for events and gifts (cadeaux) with OOP and PDO.

## Requirements

- PHP 7.4+ (or 8+)
- MySQL / MariaDB
- Web server (Apache, Nginx) or PHP built-in server

## Installation

1. Import the database:

   - Open your MySQL client (phpMyAdmin, MySQL Workbench, etc.)
   - Run the `schema.sql` file located at the project root.

2. Configure database connection:

   - Open `config/Database.php`
   - Adjust `$host`, `$db`, `$user`, `$pass` to match your local MySQL configuration.

3. Run the project (example using PHP built-in server):

   ```bash
   cd php-crud-events-gifts
   php -S localhost:8000 -t public
   ```

4. Open in browser:

   - Go to `http://localhost:8000/index.php`

## Features

- Events
  - List events
  - Create, update, delete event

- Gifts
  - List gifts
  - Each gift belongs to one event (foreign key)
  - Create, update, delete gift

## Notes

- Foreign key from `gifts.event_id` to `events.id` with `ON DELETE CASCADE`.
- This is a minimal educational example meant to illustrate OOP + PDO + CRUD.

-------------------------------------------------------------------------------------------
# PerFran Web Project

This repository implements a web platform for game-related services (user accounts, face-based login, QR login, admin BackOffice, events/gifts management, reports and punishments). The application follows a light MVC pattern and uses PHP for the web application, JavaScript/HTML/CSS for the front-end, Node.js for auxiliary AI services, and Python for the face-recognition microservice.

## Quick summary
- Language stack: PHP (app), JavaScript/HTML/CSS (front-end), Node.js (AI helpers), Python (face recognition service).
- Architecture: MVC (models hold data definitions/getters/setters, controllers perform DB requests and business logic, views render frontoffice/backoffice UIs).
- Login methods: email/password, OAuth providers (Google, Facebook, GitHub), face recognition, QR-code login.
- Main integrations: OAuth libraries (League OAuth providers), PHPMailer for email, a local Python face service for recognition, and an optional Node AI server for password suggestions and other helpers.

## Project purpose
PerFran is a small multiplayer/game-related platform that centralizes user accounts, matchmaking/game stats, and administrative tools. The BackOffice allows admins to manage events, gifts, reports, and user punishments while the FrontOffice provides public pages and user dashboards.

## MVC layout (how this repo organizes code)
- `PerFranMVC/Model/` - PHP model classes: data structures, getters/setters, simple persistence helpers. Models do not render UI.
- `PerFranMVC/Controller/` - Controllers: handle HTTP requests, interact with models and the database, perform operations such as login flows, social OAuth callbacks, report/punishment CRUD, and API endpoints.
- `PerFranMVC/View/FrontOffice/` - Public-facing views and assets (accessible to regular users).
- `PerFranMVC/View/BackOffice/` - Admin-facing views (BackOffice) used by administrators.
- Top-level scripts (e.g., `index.php`, small helpers) wire routing and bootstrap sessions.

File-level conventions: controllers prepare data then include a view. Views should not access DB directly. Sessions carry logged-in user info (`uid`, `role`, `email`, etc.).

## Login & Authentication flows
- Email / password: standard form handled by `PerFranMVC/Controller/auth.php`.
- OAuth (social login): implemented using the PHP League OAuth client and provider packages. Providers present in the codebase include:
	- Google (`oauth_google.php` / `league/oauth2-google`)
	- Facebook (`oauth_facebook.php` / `league/oauth2-facebook`)
	- GitHub (`oauth_github.php` / `league/oauth2-github`)

	Notes: Social login controllers request scopes appropriate to each provider. If a provider rejects a permission (for example `email` when the Facebook app is in dev mode), the controller contains fallback handling to retry with reduced scopes or show a friendly error message.

- Face recognition: a local Python microservice (see `face_service.py`) handles image matching; the front-end captures camera frames and posts them to the service for real-time recognition and login.
- QR login: a short-lived token flow where the server issues a QR token and the mobile/session flow authorizes it (`qr-generate.php`, `qr-check.php`, etc.).

## Admin / BackOffice features
- Manage events and gifts (create/edit/delete) — forms in `PerFranMVC/View/BackOffice/` with Flatpickr date pickers where applicable.
- Display and handle user reports; create punishments from reports. When banning a user the controllers update `users.bannedUntil` and notify users via email.
- Pending user approvals and banned user lists are provided; controllers ensure banned users do not appear in pending lists.

## Important dependencies
- PHP packages (via Composer):
	- `league/oauth2-client` and provider adapters (`league/oauth2-google`, `league/oauth2-facebook`, `league/oauth2-github`)
	- `phpmailer/phpmailer` for SMTP/email operations
- Node.js: optional AI helper server (used for password suggestions and other assistant features). See `package-ai.json` / `package.json`.
- Python: face recognition microservice and utilities (see `requirements.txt` / `face_service.py`).

## Database
- MySQL / MariaDB (PDO connections configured in `config.php`). The schema includes `users`, `reports`, `punishments`, `events`, `gifts`, and auxiliary tables for QR tokens and embeddings.

## Running & environment notes (local dev)
1. Configure `config.php` constants (`BASE_URL`, OAuth client IDs/secrets, SMTP credentials).
2. Install PHP dependencies via Composer: `composer install`.
3. Install Node deps if using the AI server: `npm install` (or follow `package-ai.json`).
4. Install Python deps for face service: `pip install -r requirements.txt` (recommended in a virtualenv).
5. Start services as needed: PHP via your local web server (XAMPP), Python face service (`python face_service.py`), and Node AI server if used.

## Notes on OAuth and Facebook scope errors
- If Facebook is in Developer mode, unapproved permissions like `email` may be shown as "Invalid Scopes" to non-admin users. To allow real users to grant such permissions, either add the users as testers/developers in the Facebook App dashboard or submit the `email` permission for review.

## Where to look for key logic
- Authentication: `PerFranMVC/Controller/auth.php`, `PerFranMVC/Controller/oauth_facebook.php`, `oauth_google.php`, `oauth_github.php`.
- Face login: front-end code in `View/FrontOffice/login.php` + `face_service.py`.
- Reports / punishments: `PerFranMVC/Controller/ReportC.php`, `PerFranMVC/Controller/delete_report.php`, BackOffice views in `PerFranMVC/View/BackOffice/`.
- Email helpers and SMTP config: `config.php` (functions `envoyerMail*`).
