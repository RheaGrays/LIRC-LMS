<p align="center">
  <img src="public/CorJesu Logo.png" width="130" alt="Cor Jesu College Logo">
</p>

<h1 align="center">LIRC-LMS</h1>
<p align="center">
  <b>Library Entrance Monitoring & Management System</b><br>
  <i>Cor Jesu College — Library Information & Resource Center</i>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Status-Active-brightgreen?style=for-the-badge" alt="Status">
  <img src="https://img.shields.io/badge/Institution-Cor_Jesu_College-c41e2a?style=for-the-badge" alt="Institution">
  <img src="https://img.shields.io/badge/System-Kiosk_%26_Admin_Suite-0f2744?style=for-the-badge" alt="System">
</p>

---

## 📌 About The System

**LIRC-LMS** (*Library Information & Resource Center - Library Management & Entrance Monitoring System*) is an automated, real-time digital entrance monitoring and library management solution built specifically for **Cor Jesu College (CJC)**.

The primary objective of LIRC-LMS is to replace manual paper logbooks with a seamless, contactless, and intelligent digital kiosk system. It registers student and staff attendance upon entering or leaving the library, while providing library administrators with real-time occupancy monitoring, patron record management, and comprehensive attendance analytics.

---

## 🏛️ System Architecture

The LIRC-LMS ecosystem operates across two primary interconnected modules:

```
┌──────────────────────────────────────────┐      ┌──────────────────────────────────────────┐
│        🚪 PUBLIC ENTRANCE KIOSK          │      │       🛡️ ADMIN CONTROL DASHBOARD          │
├──────────────────────────────────────────┤      ├──────────────────────────────────────────┤
│ • Barcode / QR Scanner Engine            │      │ • Live Occupancy & Headcount Tracker     │
│ • Real-Time 60 FPS Webcam Scanner        │  ──► │ • Patron Directory & Student Records     │
│ • Manual Name / ID Lookup Autocomplete   │  ◄── │ • Violation & Penalty Enforcement        │
│ • Offline Attendance Queue (IndexedDB)   │      │ • Department & Academic Program Setup    │
│ • Audio-Visual Status Verification Cards │      │ • Attendance Analytics & Audit Logs      │
└──────────────────────────────────────────┘      └──────────────────────────────────────────┘
```

---

## ✨ Key Features

### 🚪 Public Entrance Kiosk
1. **⚡ Multi-Format Barcode & QR ID Scanning**: Compatible with handheld laser barcode scanners and QR readers via rapid keyboard-wedge detection.
2. **📷 High-Speed 60 FPS Webcam Scanner**: Integrated camera scanning interface powered by the `@zxing/browser` engine with zero input lag for decoding barcodes directly from smartphones or physical IDs.
3. **💾 Offline Attendance Queue Manager**: In the event of campus network or server downtime, attendance check-ins are recorded locally in the browser's **IndexedDB** storage and automatically synchronized to the cloud database once connection is restored.
4. **🔍 Manual Lookup & Autocomplete Search**: Allows patrons with damaged or missing ID cards to search by Name or Student ID, complete with real-time autocomplete suggestions and photo verification.
5. **🖼️ Facilities Slideshow & Live Campus Clock**: Features a dynamic slideshow highlighting CJC Library facilities (Discussion Rooms, Quiet Study Zones, E-Library Digital Stations) alongside a real-time campus clock.

### 🛡️ Admin Management Suite
1. **📊 Live Occupancy & Capacity Monitor**: Displays real-time headcounts of patrons currently inside the library against maximum seating capacity limits, alerting staff before overcapacity occurs.
2. **👥 Complete Patron Directory**: Centralized database managing patrons across various categories:
   - **Students** (Undergraduate, Senior High, College)
   - **Faculty & Employees**
   - **Post-Graduate Researchers**
   - **Alumni**
   - **Campus Visitors**
3. **🏫 Academic Departments & Programs Manager**: Configurable management of college departments and degree programs, supporting 1st Year to 5th Year engineering levels.
4. **🚨 Violation System & Penalty Enforcement**: Logs patron infractions (e.g., noise violations, unreturned materials, dress code infractions) and automatically triggers **"Access Denied"** warnings at the kiosk for suspended accounts.
5. **📈 Analytics & Attendance Reports**: Generates graphical data visualizers highlighting peak library usage hours, busiest days of the week, and department attendance distribution.

---

## 🔄 Entrance Workflow

```
[1. Patron Approaches Kiosk] ──► [2. Scans Barcode / QR ID] 
                                            │
                                            ▼
                             [3. System Verifies Account Status]
                                            │
               ┌────────────────────────────┴────────────────────────────┐
               ▼                                                         ▼
     【 Account Active 】                                      【 Account Suspended / Inactive 】
  • Displays Student Profile Card                            • Displays Red Warning Card
  • Plays Verification Sound Alert                           • Plays Access Denied Sound Alert
  • Increments Live Occupancy Count                          • Directs Patron to Librarian Desk
               │
               ▼
[4. Real-Time Sync to Admin Dashboard]
```

---

## ⚙️ System Specifications

* **Platform**: Web Application & Desktop Electron Wrapper
* **Backend Framework**: Laravel 11 (PHP 8.2+)
* **Database**: Local MySQL / MariaDB / SQLite
* **Frontend Technologies**: TailwindCSS, Alpine.js, Blade Templates
* **Target Audience**: Cor Jesu College Students, Faculty, Staff, and LIRC Librarians

---

## 🔒 Copyright & Rights

Copyright © 2026 **Cor Jesu College — Library Information & Resource Center (LIRC)**.  
All rights reserved. Designed and developed for CJC Library Operations.
