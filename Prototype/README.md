# Ralmitrokij Hotel — Developer Handoff Document

This document provides technical specifications and implementation details for the Ralmitrokij Hotel prototype.

## Project Overview
A plain HTML, CSS, and PHP prototype for a luxury hotel website, including booking flows, checkout, and an admin dashboard.

## File Structure & Screen Mapping

| Screen | File Path | Method | Description |
|--------|-----------|--------|-------------|
| Home | `index.php` | GET | Hero, Search, Content Grids (Rooms, Dining, Amenities) |
| Rooms Listing | `rooms.php` | GET | Full listing of available rooms |
| Room Detail | `room.php?id={id}` | GET | Gallery, Amenities, Price, Booking Form |
| Search Results | `search_rooms.php` | POST | Filtered rooms listing |
| Checkout | `checkout.php` | GET | Review booking and select payment method |
| Payment Success | `payment_success.php` | GET | Success confirmation and receipt |
| Payment Failed | `payment_failed.php` | GET | Error screen with retry option |
| Contact | `contact.php` | GET/POST | Contact form and info |
| Admin Login | `admin/login.php` | GET/POST | Centered login card (admin/password) |
| Admin Bookings | `admin/bookings.php` | GET/POST | Reservations table with status filter |

## Core Components (`includes/`)
- `header.php`: Navigation and sticky header.
- `footer.php`: Contact info and quick links.
- `db.php`: Mock persistence using JSON files in `data/`.
- `functions.php`: Validation, rendering, and payment processing logic.

## Server-Side Handling Snippets

### Form Validation (Booking)
```php
if (isset($_POST['submit'])) {
    $errors = validate_booking($_POST);
    if (empty($errors)) {
        $id = save_booking($_POST);
        header("Location: checkout.php?booking_id=" . $id);
    }
}
```

### Persistence (JSON Mock)
```php
function save_data($filename, $data) {
    $file = 'data/' . $filename . '.json';
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}
```

## Setup Instructions
1. Open the project folder in VS Code.
2. Ensure you have a local PHP server running (e.g., XAMPP, WAMP, or `php -S localhost:8000`).
3. Navigate to `index.php` in your browser.
4. Admin credentials: `admin` / `password`.
