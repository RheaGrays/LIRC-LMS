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

## 📌 Unsa ang LIRC-LMS System? (About The System)

Ang **LIRC-LMS** (*Library Information & Resource Center - Library Management & Entrance Monitoring System*) ay usa ka automated, real-time digital entrance monitoring ug library administration system nga gihimo alang sa **Cor Jesu College (CJC)**.

Ang panguna nga katuyoan niining sistema ay ang **pag-ganti sa karaan nga manwal nga logbook** pinaagi sa usa ka paspas, contactless, ug digital Kiosk system. Naga-record kini sa matag pagsulod ug paggawas sa mga estudyante ug empleyado sa librarya, samtang naga-hatag sa mga librarian og real-time dashboard para sa occupancy tracking, patron management, ug statistical analytics.

---

## 🏛️ Duha ka Sumpay nga Component sa Sistema (System Architecture)

Ang LIRC-LMS naga-operate pinaagi sa duha ka importanteng bahin:

```
┌──────────────────────────────────────────┐      ┌──────────────────────────────────────────┐
│        🚪 PUBLIC ENTRANCE KIOSK          │      │       🛡️ ADMIN CONTROL DASHBOARD          │
├──────────────────────────────────────────┤      ├──────────────────────────────────────────┤
│ • Barcode / QR Scanner Engine            │      │ • Live Occupancy & Headcount Tracker     │
│ • Real-Time 60FPS Webcam Scanner         │  ──► │ • Patron Directory & Student Records     │
│ • Manual Name / ID Lookup                │  ◄── │ • Violation & Penalty Enforcement        │
│ • Offline Attendance Queue (IndexedDB)   │      │ • Department & Academic Program Setup    │
│ • Audio-Visual Status Verification Cards │      │ • Attendance Analytics & Audit Logs      │
└──────────────────────────────────────────┘      └──────────────────────────────────────────┘
```

---

## ✨ Mga Main Feature sa Kiosk (Entrance Terminal)

### 1. ⚡ Fast Barcode & QR Code ID Scanning
* Naga-support sa handheld laser barcode scanners ug QR readers (via high-speed keyboard-wedge listener).
* Mupagawas dayon og **Instant Visual Status Card** ug **Audio Beep Alert** pag scan sa ID.

### 2. 📷 High-Speed 60 FPS Webcam Scanner
* Built-in camera scanning interface nga gipaspasan gamit ang `@zxing/browser` engine.
* Paspas nga muread og ID barcode diretso gikan sa phone screen o physical Student ID nga walay lag.

### 3. 💾 Offline Attendance Queue Manager
* Kung maputol man gani ang internet o server connection sa campus, dili ma-interupt ang kiosk.
* Naga-save kini sa check-ins sulod sa browser's local database (**IndexedDB**) ug **automatic nga mag-sync pabalik sa database** sa moment nga mubalik ang connection.

### 4. 🔍 Manual Lookup & Autocomplete Search
* Para sa mga patron nga naguba o nakalimtan ang ID card, pwedeng i-type ang pangalan o Student ID.
* Naay real-time autocomplete dropdown nga mupakita sa ilang litrato ug impormasyon para dali nga ma-verify.

### 5. 🖼️ Facilities Slideshow & Live Campus Clock
* Nagapakita sa mga pasilidad sa CJC Library (Discussion Rooms, Quiet Study Zone, E-Library Digital Station) ug sa live campus time and date.

---

## 🛡️ Mga Main Feature sa Admin Dashboard (Management Suite)

### 1. 📊 Live Occupancy & Capacity Monitor
* Nagapakita sa eksaktong gidaghanon sa mga tawo nga **kasamtangang naa sa sulod sa librarya** kumpara sa Maximum Seating Capacity limit.
* Paspas nga mualerto sa librarian kung hapit na mapuno ang seating capacity.

### 2. 👥 Complete Patron Directory
* Centralized nga rekord sa tanang patrons ubos sa lain-laing categories:
  * **Students** (Undergraduate, Senior High, College)
  * **Employees / Faculty**
  * **Post Graduate Students**
  * **Alumni**
  * **Campus Visitors**

### 3. 🏫 Academic Departments & Programs Manager
* Dito gina-manage ang tanang departamento (e.g., Computer Studies, Engineering, Business & Governance, Arts & Sciences, Nursing) ug mga degree programs (gikan 1st Year hangtod 5th Year engineering levels).

### 4. 🚨 Violation System & Account Suspension
* Gina-record ang mga supak sa patakaran sa librarya (e.g., noise violations, unreturned books, dress code).
* Automatic nga mag-flag ug **"Access Denied"** sa Kiosk kung ang usa ka patron suspended o inactive ang account.

### 5. 📈 Analytics & Statistical Reports
* Nagahatag og visual charts ug datos bahin sa peak library usage hours, pinaka-busy nga adlaw sa semana, ug department attendance distribution.

---

## 🔄 Unsaon Pag-Function sa Entrance Process (System Workflow)

```
[1. Student approaches Kiosk] ──► [2. Scans Barcode / QR ID] 
                                            │
                                            ▼
                             [3. System Verifies Account Status]
                                            │
               ┌────────────────────────────┴────────────────────────────┐
               ▼                                                         ▼
     【 Account Active 】                                      【 Account Suspended / Inactive 】
  • Screen shows Student Card                               • Screen shows Red Warning Card
  • Plays Success Beep Alert                                • Plays Denied Alert Sound
  • Adds 1 to Live Occupancy Count                          • Directs student to Librarian Desk
               │
               ▼
[4. Instant Sync to Admin Dashboard]
```

---

## ⚙️ System Requirements & Architecture Specs

* **Core Platform**: Web Application + Desktop Electron Wrapper
* **Framework**: Laravel 11 (PHP 8.2+)
* **Database**: TiDB Cloud Serverless / MySQL (SSL Secured)
* **Frontend Engine**: TailwindCSS, Alpine.js, Blade Templates
* **Target Users**: Cor Jesu College Students, Faculty, Staff, and LIRC Librarians

---

## 🔒 Copyright & Rights

Copyright © 2026 **Cor Jesu College — Library Information & Resource Center (LIRC)**.  
Designed and developed for Cor Jesu College Library Operations.
