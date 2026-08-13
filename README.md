# LEMS — Library Entrance Monitoring System

> A full-stack library entrance monitoring and attendance tracking system built for the **Cor Jesu College — Library & Information Resource Center (LIRC)**.

---

## Overview

LEMS tracks student and patron attendance at the college library via barcode/QR scanning. It ships as a **self-contained Electron desktop app** with a bundled PHP runtime — no XAMPP or MySQL installation required. A companion **Expo mobile scanner app** extends scanning capability to any phone on the LAN.

### Key Features

- **Kiosk Mode** — Full-screen barcode scanning with real-time occupancy display and library slideshow
- **Admin Panel** — Student CRUD, analytics dashboards, violation tracking, section management, report exports
- **Offline-First** — Both kiosk and mobile apps queue scans locally when the server is unreachable
- **Self-Registration** — Students can register themselves with QR-paired mobile photo capture
- **Multi-Format Reports** — Export attendance data as Excel, Word, or PDF
- **Role-Based Access** — Super Admin, Staff, and Librarian roles with approval-based signup

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 12, PHP 8.2+ |
| Database | SQLite (desktop) / MySQL (web/XAMPP) |
| Frontend | Blade, Alpine.js, Tailwind CSS 3, Chart.js |
| Desktop | Electron 43, bundled PHP runtime |
| Mobile | React Native / Expo with expo-camera |
| Build | Vite (assets), electron-builder (installer) |
| Reports | PhpSpreadsheet, DomPDF |

---

## Project Structure

```
LEMS/
├── app/
│   ├── Http/Controllers/     # 18+ controllers
│   ├── Http/Middleware/       # Admin auth, role check, kiosk token
│   ├── Models/                # 13 Eloquent models
│   └── Services/              # Occupancy, analytics, reports, sections
├── database/
│   ├── migrations/            # 19 migration files
│   ├── seeders/               # Default admin + settings
│   └── lems_database.sql      # Full MySQL setup script (XAMPP)
├── resources/
│   ├── views/                 # Blade templates (admin, kiosk, register)
│   ├── js/kiosk/              # Kiosk scanner + offline queue
│   └── css/                   # Custom styles
├── electron/                  # Electron main process + preload
├── lems-mobile-scanner/       # Expo React Native scanner app
├── php_bundle/                # Bundled PHP runtime (for builds)
└── public/                    # Entry point + compiled assets
```

---

## Getting Started

### Option A: Desktop App (Recommended)

The Electron app bundles everything — no server setup needed.

```bash
# Install dependencies
npm install

# Run in development mode (requires PHP in PATH or Herd/XAMPP)
npm start              # Kiosk mode (default)
npm start -- --admin   # Admin mode

# Build production installer
npm run build
```

The app will:
1. Find or use bundled PHP
2. Run database migrations automatically
3. Start `artisan serve` on port 8000
4. Open the kiosk or admin interface

### Option B: Web Server (XAMPP / Herd)

```bash
# 1. Install PHP dependencies
composer install

# 2. Copy and configure environment
cp .env.example .env
php artisan key:generate

# 3. Setup database
#    For SQLite: touch database/database.sqlite
#    For MySQL:  import database/lems_database.sql into phpMyAdmin

# 4. Run migrations and seed
php artisan migrate --seed

# 5. Build frontend assets
npm install
npm run build

# 6. Start the server
php artisan serve --host=0.0.0.0 --port=8000
```

### Mobile Scanner

```bash
cd lems-mobile-scanner
npm install
npx expo start
```

Configure the server IP in the app's Settings screen.

---

## Default Credentials

| Role | Email | Password |
|------|-------|----------|
| Super Admin | `admin@corjesucollege.edu.ph` | `admin123` |

> ⚠️ **Change the default password immediately after first login.**

---

## Network Configuration

For LAN access (mobile scanners, client kiosks), create `lems.host.json` in the project root:

```json
{
  "host": "192.168.x.x",
  "port": 8000
}
```

The Electron app reads this file to connect kiosks to the host server.

---

## API Endpoints

The mobile scanner uses a single API endpoint:

```
POST /api/kiosk/process
Body: { "student_id": "2024-00123" }
Header: X-Kiosk-Token: <token> (required for non-LAN access)
```

---

## Architecture Notes

- **Attendance Flow**: Scans are processed with atomic cache locks to prevent duplicate check-ins within a configurable cooldown window.
- **Occupancy Tracking**: Net occupancy (students currently inside) is calculated via a subquery that finds each student's latest action today.
- **Settings Cache**: `SystemSetting` model caches all settings for 5 minutes with bust-on-write.
- **Electron Storage**: In packaged builds, SQLite database and Laravel storage are redirected to `%APPDATA%/LEMS/`.

---

## License

This project is developed for Cor Jesu College internal use.
