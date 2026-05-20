# ReliaWork2 — Barangay Job Fair Management System

> **SDG 8: Decent Work and Economic Growth**

---

## Project Title

**ReliaWork2** — Barangay Resource & Labor Pool System  
*A web-based Job Fair Management System for Barangay PESO (Public Employment Service Office)*

---

## Overview

ReliaWork2 is a full-stack web application built with **PHP 8+, MySQL, and Bootstrap 5** that digitizes and streamlines the entire job fair lifecycle at the barangay level. It follows a structured **7-process DFD workflow** — from schedule generation and job fair requests, to agency invitations, vacancy processing, resource allocation, and applicant registration.

The system replaces manual, paper-based processes with a secure, role-based digital platform that connects barangay officials, agencies, and job seekers in one unified system.

---

## Problem Statement

Barangay-level job fairs in the Philippines are often managed manually — through paper forms, phone calls, and informal coordination. This leads to:

- **Schedule conflicts** — no centralized calendar to check date availability
- **Disorganized agency coordination** — invitations sent informally with no tracking
- **Incomplete vacancy data** — companies submit incomplete job information
- **No applicant tracking** — registration forms are paper-based and easily lost
- **Lack of accountability** — no audit trail for approvals, rejections, or changes
- **Data privacy issues** — sensitive applicant information stored insecurely

ReliaWork2 solves all of these by providing a structured, role-based digital workflow with full audit logging and data security.

---

## Objectives

1. **Digitize the job fair workflow** following the 7-process DFD model used by PESO offices
2. **Prevent schedule conflicts** through a real-time calendar with booked-date indicators
3. **Streamline agency coordination** — supervising labor can select and bulk-invite companies from a master directory
4. **Standardize vacancy submission** — agencies submit structured vacancy data (company, position, slots, qualifications)
5. **Enable applicant registration** with government ID fields (GSIS/SSS, Pag-IBIG, PhilHealth)
6. **Implement role-based access control (RBAC)** so each user only sees what they need
7. **Ensure data security** through bcrypt password hashing, CSRF protection, prepared statements, and session management
8. **Provide audit logging** for all significant actions in the system

---

## Target Users / Personas

| Role | Name | Description |
|------|------|-------------|
| **Admin** | System Administrator | Manages user accounts, approves registrations, assigns roles |
| **Supervising Labor** | PESO Officer | Manages schedules, validates job fair requests, invites agencies, reviews vacancies, generates registration forms |
| **Barangay Captain** | Barangay Captain | Creates and submits job fair requests, monitors approval status |
| **Secretary** | Barangay Secretary | Manages barangay resources (chairs, tables, tents, equipment) and allocates them to events |
| **Agency** | Company / Recruitment Agency | Confirms participation, submits job vacancies with full company details |
| **Applicant** | Job Seeker / Resident | Registers for job fairs, browses vacancies, applies for jobs, tracks application status |

---

## System Workflow (7-Process DFD)

```
Process 1 — Supervising Labor generates Schedule of Events
     ↓
Process 2 — Barangay Captain creates Job Fair Request (checks calendar)
     ↓
Process 3 — Supervising Labor validates the request (Approve / Reject)
     ↓
Process 4 — Supervising Labor invites agencies from company directory
     ↓
Process 5 — Agency submits Job Vacancies (company info, position, slots)
          → Supervising Labor is notified and adds remarks
     ↓
Process 6 — Secretary confirms barangay resources; Supervising Labor finalizes details
     ↓
Process 7 — Supervising Labor generates Applicant Registration Form (printable)
```

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8.0+ |
| Database | MySQL 8.0+ / MariaDB 10.4+ |
| Frontend | Bootstrap 5.3, Bootstrap Icons |
| Architecture | MVC (Model-View-Controller) |
| Server | Apache (XAMPP compatible) |
| Auth | Session-based + CSRF protection |
| Security | bcrypt, PDO prepared statements, XSS sanitization |

---

## Project Structure

```
ReliaWork2/
├── app/
│   ├── config/
│   │   ├── config.php          # App constants, .env loader
│   │   └── Database.php        # PDO singleton
│   ├── controllers/
│   │   ├── AuthController.php
│   │   ├── AdminController.php
│   │   ├── SupervisingLaborController.php
│   │   ├── BarangayCaptainController.php
│   │   ├── SecretaryController.php
│   │   ├── AgencyController.php
│   │   ├── ApplicantController.php
│   │   └── NotificationController.php
│   ├── models/
│   │   ├── UserModel.php
│   │   ├── ScheduleModel.php
│   │   ├── JobFairRequestModel.php
│   │   ├── AgencyModel.php
│   │   ├── CompanyModel.php
│   │   ├── VacancyModel.php
│   │   ├── ResourceModel.php
│   │   ├── ApplicantModel.php
│   │   ├── ApplicationModel.php
│   │   ├── AnnouncementModel.php
│   │   └── NotificationModel.php
│   ├── views/
│   │   ├── layouts/            # main.php, auth.php
│   │   ├── auth/               # login.php, register.php
│   │   ├── admin/              # dashboard.php, users.php
│   │   ├── supervising_labor/  # dashboard, schedules, requests, agencies, vacancies, companies
│   │   ├── barangay_captain/   # dashboard, create_request, my_requests
│   │   ├── secretary/          # dashboard, resources
│   │   ├── agency/             # dashboard, vacancies
│   │   ├── applicant/          # dashboard, vacancies, register, my_applications
│   │   └── errors/             # 403.php, 404.php
│   └── helpers/
│       ├── auth_helper.php     # Auth, CSRF, session, flash, audit log
│       ├── flash_helper.php    # Flash message rendering
│       └── view_helper.php     # View utilities
├── database/
│   ├── schema.sql              # All 13 table definitions
│   ├── seed.sql                # Default admin + sample data
│   ├── setup.php               # One-click database setup script
│   └── migrations/             # Incremental migration files
├── public/
│   ├── index.php               # Front controller / router
│   ├── .htaccess               # Apache URL rewriting
│   └── assets/
│       ├── css/app.css
│       └── js/app.js
├── .env                        # Environment configuration
└── README.md
```

---

## Database Tables

| Table | Description |
|-------|-------------|
| `users` | All system users with role and status |
| `profiles` | Extended user profile information |
| `schedule_of_events` | Barangay event calendar |
| `job_fair_requests` | Job fair requests by Barangay Captain |
| `participating_agencies` | Agencies invited to job fairs |
| `companies` | Master company/agency directory |
| `job_vacancies` | Vacancies submitted by agencies |
| `barangay_resources` | Equipment inventory (chairs, tables, etc.) |
| `resource_allocations` | Resources allocated to events |
| `applicants` | Registered applicants with government IDs |
| `applications` | Job applications submitted by applicants |
| `announcements` | System announcements |
| `notifications` | In-system notifications |
| `audit_logs` | Full audit trail of all actions |

---

## Installation & Setup

### Requirements
- PHP 8.0+
- MySQL 8.0+ or MariaDB 10.4+
- Apache with `mod_rewrite` enabled
- XAMPP (recommended for local development)

### Steps

**1. Clone the repository**
```bash
git clone https://github.com/YOUR_USERNAME/ReliaWork2.git
```

**2. Place in XAMPP htdocs**
```
C:\xampp\htdocs\ReliaWork2\
```

**3. Configure environment**

Edit `.env`:
```env
APP_URL=http://localhost/ReliaWork2/public
DB_HOST=127.0.0.1
DB_DATABASE=reliawork2_db
DB_USERNAME=root
DB_PASSWORD=
```

**4. Run database setup**

Open in browser:
```
http://localhost/ReliaWork2/database/setup.php
```

Or via CLI:
```bash
php database/setup.php
```

**5. Access the system**
```
http://localhost/ReliaWork2/public
```

---

## Default Login

| Field | Value |
|-------|-------|
| Email | admin@reliawork2.com |
| Password | Admin@123 |

> ⚠️ Change the admin password after first login.

---

## Security Features

- ✅ **bcrypt password hashing** (cost 12)
- ✅ **CSRF protection** on all POST forms
- ✅ **PDO prepared statements** — no SQL injection
- ✅ **XSS sanitization** — all output escaped
- ✅ **Session regeneration** on login
- ✅ **Role-based access control** (6 roles)
- ✅ **Audit logging** for all significant actions
- ✅ **Admin approval workflow** — new users are pending until approved

---

## License

MIT License — ReliaWork2 © 2026  
Developed for SDG 8: Decent Work and Economic Growth
