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
