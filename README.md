# Basic Data Capturing App

This is a simple PHP web application for managing clients and contacts, with a many-to-many relationship between them. It is designed to run on XAMPP (or any LAMP/WAMP stack) and uses MySQL as the database backend.

## Features
- Add, edit, and list clients
- Add, edit, and list contacts
- Link multiple contacts to a client and multiple clients to a contact (many-to-many)
- Unlink contacts from clients and vice versa
- AJAX-powered linking/unlinking for a smooth user experience
- Auto-generated, unique client codes
- Tabbed forms for easy navigation between general info and relationships
- Simple, modern navigation bar for quick access to all main pages

## How to Use
1. **Setup:**
   - Import the provided `database_schema.sql` into your MySQL database.
   - Configure your database connection in `app/models/Database.php` if needed.
   - Place the project folder in your XAMPP `htdocs` directory.
2. **Run:**
   - Start Apache and MySQL in XAMPP.
   - Visit `http://localhost/basic_data_capturing_app/` in your browser.
3. **Functionality:**
   - Use the navigation bar to access the client and contact lists, and to add new entries.
   - Edit a client or contact to manage their relationships via the tabbed interface.
   - Use the link/unlink buttons to manage associations instantly.

## Project Structure
- `public/` — Publicly accessible files (entry point, assets)
- `app/models/` — Database and business logic
- `app/controllers/` — Application controllers
- `app/views/` — HTML/PHP views
- `database_schema.sql` — MySQL schema

## Requirements
- PHP 7.4+
- MySQL
- XAMPP, LAMP, or WAMP stack

## Notes
- All linking/unlinking is handled via AJAX for a seamless experience.
- The application is intended as a learning/demo project and can be extended for more advanced use cases.

---

**Author:**
- Saya Mubiana

