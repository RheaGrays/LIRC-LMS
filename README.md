<p align="center">
  <img src="public/CorJesu Logo.png" width="120" alt="Cor Jesu College Logo">
</p>

<h1 align="center">LIRC-LMS</h1>
<p align="center">
  <b>Library Entrance Monitoring & Management System</b><br>
  <i>Cor Jesu College — Library Information & Resource Center</i>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 11">
  <img src="https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?style=for-the-badge&logo=alpine.js&logoColor=black" alt="Alpine.js">
  <img src="https://img.shields.io/badge/TiDB_Cloud-Serverless-0052CC?style=for-the-badge&logo=mysql&logoColor=white" alt="TiDB Cloud">
  <img src="https://img.shields.io/badge/Electron-Desktop-47848F?style=for-the-badge&logo=electron&logoColor=white" alt="Electron">
</p>

---

## 📌 Overview

**LIRC-LMS** is a state-of-the-art, high-performance Library Management and Entrance Monitoring System built specifically for **Cor Jesu College (CJC)**. 

The system provides a **Self-Service Kiosk Scanner** for students and patrons entering the library, paired with an **Admin Control Panel** for library administrators to monitor live occupancy, manage patrons, track violations, and analyze seating statistics.

---

## ✨ Key Features

### 🏢 Public Entrance Kiosk
* ⚡ **Multi-Format ID Scanning**: Supports hardware Barcode/QR scanners via fast keyboard-wedge buffer detection.
* 📷 **High-Speed Webcam Scanner**: 60 FPS hardware-accelerated camera decoder using `@zxing/browser` with zero input lag.
* 💾 **Offline Resilience**: Built-in IndexedDB offline queue manager that saves student check-ins when network connectivity drops and auto-syncs when online.
* 🔔 **Instant Verification Feedback**: Real-time visual status cards and audio beep indicators for check-in confirmation or access denial.
* 🖼️ **Cinematic Slideshow & Live Campus Clock**: Full-screen facility slideshow showcasing quiet zones, study hubs, and live library hours.

### 🛡️ Admin Dashboard & Management
* 📊 **Live Occupancy Analytics**: Real-time tracker for students currently inside the library against max seating capacity.
* 👥 **Patron Directory**: Complete patron management supporting Students, Employees, Alumni, Post Graduates, and Visitors.
* 🎓 **Academic Programs & Departments**: Dynamic management of college departments and degree programs (with 1st to 5th Year engineering support).
* 🚨 **Violation & Penalty Tracking**: Log patron infractions, track penalty statuses, and flag suspended accounts automatically at the kiosk.
* 📜 **Audit Logs & Reports**: Comprehensive action logging for security auditing and exportable attendance statistics.

### 🖥️ Desktop Runtime & Launchers
* 🚀 **Electron Wrapper & One-Click VBS Launchers**: Packaged with desktop shortcuts (`Launch LEMS Kiosk.vbs` and `Launch LEMS Admin.vbs`) for kiosk terminals.

---

## 🛠️ Technology Stack

| Layer | Technology |
|---|---|
| **Backend Framework** | Laravel 11 (PHP 8.2+) |
| **Database** | TiDB Cloud Serverless / MySQL (SSL Encrypted) |
| **Frontend Framework** | Blade Templates, TailwindCSS, Alpine.js |
| **Barcode/QR Decoder** | `@zxing/browser` Multi-Format Engine |
| **Desktop Runtime** | Electron JS |
| **Asset Bundler** | Vite 6 |

---

## 🚀 Quick Start Guide

### 1. Prerequisites
Ensure you have the following installed on your environment:
* **PHP** >= 8.2
* **Composer** >= 2.0
* **Node.js** >= 18.x & **NPM**
* **MySQL** or **TiDB Cloud** instance

### 2. Installation Steps

1. **Clone the Repository**
   ```bash
   git clone https://github.com/RheaGrays/LIRC-LMS.git
   cd LIRC-LMS
   ```

2. **Install PHP & Node Dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Configure Environment File**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Update your `.env` database connection credentials (`DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `MYSQL_ATTR_SSL_CA`).*

4. **Run Migrations & Seeders**
   ```bash
   php artisan migrate --seed
   php artisan storage:link
   ```

5. **Build Assets & Start Server**
   ```bash
   npm run build
   php artisan serve
   ```

6. **Access Application**
   * **Kiosk Terminal**: `http://localhost:8000/kiosk`
   * **Admin Panel**: `http://localhost:8000/admin/login`

---

## 📁 Project Structure

```
LEMS/
├── app/
│   ├── Http/Controllers/    # Kiosk, Attendance, Admin & Student Controllers
│   ├── Models/              # Student, AttendanceLog, Violation, Academic models
│   └── Services/            # Occupancy & Queue management services
├── database/
│   ├── migrations/          # DB schema migrations
│   └── seeders/             # Initial system settings & default admin account
├── electron/                # Desktop app electron main & preload scripts
├── resources/
│   ├── js/kiosk/            # Alpine.js Kiosk state machine & camera scanner logic
│   └── views/               # Blade templates (Kiosk, Admin, Components)
├── public/                  # Static assets & compiled Vite output
├── Launch LEMS Kiosk.vbs    # One-click desktop launcher for Kiosk mode
└── Launch LEMS Admin.vbs    # One-click desktop launcher for Admin mode
```

---

## 🔒 License & Copyright

Copyright © 2026 **Cor Jesu College — Library Information & Resource Center**.  
All rights reserved. Developed for CJC Library Entrance Monitoring & Management.
