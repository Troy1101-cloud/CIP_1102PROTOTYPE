# Developer Handoff Documentation: Ralmitrokij Hotel Admin Dashboard

## 1. Project Overview
This prototype serves as the internal management interface for Ralmitrokij Hotel. It is built as a responsive Single Page Application (SPA) using HTML5, CSS3 (Flexbox/Grid), and Vanilla JavaScript for client-side routing and interactivity.

## 2. HTML Structure & Naming Conventions
- **Naming Strategy**: BEM-inspired flat classes (e.g., `nav-link`, `btn-admin`, `status-badge`).
- **Layout**:
  - `header.dashboard-header`: Fixed navigation bar.
  - `main.dashboard-container`: Content wrapper.
  - `section.dashboard-view`: Individual page views toggled via JS.
- **Accessibility**: 
  - Semantic tags: `<nav>`, `<main>`, `<section>`, `<article>`, `<header>`, `<footer>`.
  - Contrast: Follows WCAG 2.1 AA standards using Primary (#333) on Background (#FFF) and Gold (#DAA520) for interactive states.

## 3. Integration Guide (PHP/Backend)
To transition from prototype to full functionality:
1. **Routing**: Current `navigateTo()` function handles client-side switching. For SEO/Deep-linking, consider implementing a PHP-based router that includes view files based on `$_GET['view']`.
2. **Data Population**:
   - Replace static `<tr>` in `reservations-body` with a PHP loop fetching from the `bookings` table.
   - Use the `status` column to dynamically apply classes: `status-paid`, `status-pending`, or `status-cancelled`.
3. **Filtering**: The `applyFilter()` function currently filters visible DOM elements. In production, this should trigger a new SQL query or an AJAX request to fetch filtered data from the server.

## 4. Database Schema Recommendations

### Table: `bookings`
| Column | Type | Description |
|--------|------|-------------|
| id | INT (PK) | Unique booking ID |
| guest_name | VARCHAR | Full name of the guest |
| room_id | INT (FK) | Reference to rooms table |
| check_in | DATE | Arrival date |
| check_out | DATE | Departure date |
| guests_count | INT | Number of adults/children |
| status | ENUM | 'Paid', 'Pending', 'Cancelled' |

### Table: `rooms`
| Column | Type | Description |
|--------|------|-------------|
| id | INT (PK) | Unique room ID |
| room_number | VARCHAR | Display number (e.g., "501") |
| type | VARCHAR | Deluxe, Penthouse, etc. |
| status | ENUM | 'Available', 'Occupied', 'Maintenance' |
| features | TEXT | JSON or comma-separated list |

## 5. API Endpoint Specifications
- `GET /api/reservations.php?status=X`: Returns JSON data for bookings filtered by status.
- `GET /api/reports_data.php?type=revenue`: Returns time-series data for the Reports page charts.
- `POST /api/update_amenity.php`: Updates the operational status of a facility.

## 6. CSS Variables
Styles are centralized using CSS variables in `assets/css/admin-dashboard.css`:
- `--admin-primary`: #333333
- `--admin-accent`: #DAA520
- `--admin-bg`: #FFFFFF
- `--admin-border`: #E0E0E0
