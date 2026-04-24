# CityCare Clinic Appointment and Patient Management System

CityCare is a Laravel 12 clinic management system built for the project-based exam brief. It centralizes patient registration, email verification, OTP login, doctor scheduling, appointment booking, consultation notes, pharmacy, radiology, cashier payments, secure document uploads, dark-mode capable dashboards, public feature pages, search/filtering, pagination, and CSV reporting.

## Documentation Checklist

This README is written to satisfy the project documentation requirement in the marking guide:

- Setup steps are provided in the **Installation Steps**, **Stripe Billing Setup**, and **Laravel Cloud Notes** sections.
- Major features are explained in the **Features** section.
- Brief descriptions of the screens and system modules are provided in the **System Modules** and **Screen Descriptions** sections.
- Demo accounts, important URLs, and verification commands are included for quick examiner testing.
- The final formal documentation PDF is available at `docs/CityCare_Project_Documentation_Updated.pdf`.
- The editable documentation source is `docs/project-documentation.md`, and the PDF generator script is `docs/generate_project_documentation.py`.

## Technologies Used

- PHP 8.2+
- Laravel 12
- Laravel Blade templating
- Laravel Eloquent ORM
- Laravel validation, middleware, migrations, seeders, and feature tests
- Laravel Cashier
- Stripe Checkout
- MySQL for local XAMPP development
- PostgreSQL or MySQL for Laravel Cloud deployment
- Bootstrap 5
- Custom CSS
- JavaScript Fetch API for AJAX doctor availability
- PHPUnit through `php artisan test`
- Git and GitHub
- Laravel Cloud

## Features

- Secure patient login, hidden staff login, patient self-registration, email verification after registration, and OTP verification on normal non-demo login.
- Role-based access for Administrator, Receptionist, Doctor, Cashier, Pharmacist, Radiology, RN, PCT, House Keeping, Nurse, Dietary, and Patient.
- CRUD modules for departments, doctors, patients, appointments, payments, drug categories, and drugs.
- Appointment conflict prevention so a doctor cannot be double-booked for the same active time slot.
- AJAX/JSON doctor availability endpoint used by the appointment booking form.
- Doctor consultation notes with symptoms, diagnosis, treatment plan, prescriptions sent to pharmacy, radiology orders, and next visit date.
- Pharmacy queue for pharmacist-only drug categories, drug inventory, and prescription dispensing.
- Radiology queue for imaging requests, study status, and result notes.
- Dashboard graphs and pie charts for performance visibility across operational roles.
- Cashier-only billing service for invoices, MTN Mobile Money, Airtel Money, card payments, bank deposits, insurance, and cashier-controlled receipt tracking.
- Public care service catalog with cart checkout, payment line items, Stripe-hosted Visa/card checkout, and cashier verification for MTN, Airtel, and bank references.
- Laravel Cashier and Stripe Checkout integration for live patient card and Stripe-supported online payments.
- Public home page with separate patient access, visible staff portal access, services, location, and contact inquiry form.
- Dedicated public feature pages for role-based access, live doctor slot checks, 24/7 services, and ambulance support, linked directly from the home page.
- Patient appointment requests that use live doctor availability and await clinic approval.
- Patient check-in links and QR codes that open 30 minutes before appointment time.
- Patient insurance, blood work/lab results, vital signs, prescriptions, family history, and treatment history views.
- Secure document uploads for patient IDs, insurance cards, clinical files, payment proof, pharmacy files, radiology reports, and role-specific staff documents.
- Patient portal for profile, appointments, visit history, and payment status.
- Reports for appointments, payments, and visits with CSV export.
- Light and dark display mode across public pages, authentication screens, and workspaces.
- Auto-dismissing flash messages for success and error notifications after create, update, delete, login, and logout actions.
- Search, filtering, pagination, soft deletes, and responsive Bootstrap UI.

## System Modules

- Administrator dashboard: monitors daily appointments, doctor workloads, payment summaries, patient attendance trends, departments, doctors, and patient records.
- Receptionist workspace: searches patients and books, updates, or cancels appointments using dynamic doctor slot availability to prevent double-booking.
- Doctor workspace: shows the doctor's own appointment schedule, assigned patient details, consultation notes, treatment history, prescriptions, radiology orders, and visit reports.
- Cashier workspace: records and tracks patient payments, invoice status, payment methods, receipt references, and payment reports. Receptionists cannot receive or update patient billing records.
- Pharmacist workspace: manages drug categories, drug stock, prescription review, and dispensing status.
- Radiology workspace: manages imaging orders, urgent study queues, and result updates.
- RN, PCT, House Keeping, Nurse, and Dietary workspaces: provide role-specific operational dashboards for support duties.
- Patient portal: allows patients to view their personal profile, upcoming appointments, medical reports, blood work, bills, insurance, and appointment requests.
- Nurse/RN/PCT workspace: can review patient information, provider, vitals, blood work, treatment history, and family history.
- Reports module: produces appointment, payment, and visit summaries with CSV export.
- API/AJAX module: provides JSON doctor availability and patient search endpoints for faster appointment workflows.
- Documents module: allows authorized users to upload and download role-appropriate documents from the Documents page or from patient profiles.

## Screen Descriptions

These descriptions can be used in place of screenshots when submitting the project documentation:

- Public home screen: presents CityCare Medical Centre, patient login, patient sign up, staff access, 24/7 service messaging, ambulance support, and quick links to services, location, contact, and the care shop.
- Public feature detail pages: explain role-based access, doctor slot checks, 24/7 services, and ambulance support from dedicated public information screens linked from the homepage.
- Services screen: explains clinic services, appointment support, diagnostics, pharmacy, emergency response, and directs users to book inquiries or choose paid care services.
- Location screen: shows Plot 24 Yusuf Lule Road, Kampala and provides a working Google Maps directions link.
- Contact screen: provides clinic contact details and a form for appointment booking or general inquiries.
- Authentication screens: separate patient and staff login flows, patient registration, email verification, OTP verification for normal users, and password change for authenticated users.
- Administrator dashboard: displays operational metrics, appointment performance charts, payment method summaries, doctor workload, attendance trends, links to core management modules, and the administrator portrait in the workspace profile areas.
- Receptionist workspace: supports patient search and appointment booking, editing, and cancellation while loading available doctor slots dynamically.
- Doctor workspace: shows the doctor's schedule, patient records, previous visits, consultation notes, prescriptions, radiology orders, and treatment plans.
- Cashier workspace: records and tracks payments, invoice status, payment methods, receipt references, Stripe checkout status, and payment reports.
- Patient portal: lets patients view their profile, appointment requests, upcoming visits, medical reports, blood work, bills, insurance, family history, prescriptions, and treatment information.
- Pharmacy workspace: allows pharmacists to manage drug categories, drug inventory, and prescription dispensing queues sent by doctors.
- Radiology workspace: allows radiology staff to view imaging orders, update study status, and record result notes.
- Reports screen: gives every role a report area, with report types filtered by role and CSV export available for the selected report.
- Documents screen: lets patients and staff upload secure role-specific files such as ID cards, insurance cards, payment proof, clinical attachments, pharmacy files, radiology reports, and support documents.
- Care shop and checkout screens: allow public visitors or patients to add services to a cart, submit mobile money or bank references for cashier verification, or proceed to Stripe-hosted card checkout.

## Installation Steps

1. Put the folder in `C:\xampp\htdocs\CityCareClinicManagementSystem`.
2. Open a terminal in the project folder:

```bash
cd C:\xampp\htdocs\CityCareClinicManagementSystem
```

3. Install PHP dependencies:

```bash
composer install
```

4. Create the environment file if it does not already exist:

```bash
copy .env.example .env
```

5. Generate the Laravel application key:

```bash
php artisan key:generate
```

6. Start Apache and MySQL in XAMPP.
7. Create the database if it does not already exist:

```sql
CREATE DATABASE citycare_clinic CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

8. Confirm `.env` contains:

```env
APP_NAME="CityCare Clinic"
APP_URL=http://localhost/CityCareClinicManagementSystem/public
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=citycare_clinic
DB_USERNAME=root
DB_PASSWORD=
STRIPE_KEY=pk_live_or_test_key_here
STRIPE_SECRET=sk_live_or_test_secret_here
STRIPE_WEBHOOK_SECRET=whsec_from_stripe_cli_or_dashboard
CASHIER_CURRENCY=ugx
CASHIER_CURRENCY_LOCALE=en_UG
```

9. Run migrations and seed sample data:

```bash
php artisan migrate --seed
```

10. Clear cached configuration:

```bash
php artisan optimize:clear
```

11. Open the system:

```text
http://localhost/CityCareClinicManagementSystem/public
```

## Demo Accounts

All demo accounts use the password:

```text
citycare456
```

| Role | Email |
| --- | --- |
| Administrator | admin@citycare.test |
| Receptionist | reception@citycare.test |
| Doctor | doctor.grace@citycare.test |
| Cashier | cashier@citycare.test |
| Pharmacist | pharmacist@citycare.test |
| Radiology | radiology@citycare.test |
| RN | rn@citycare.test |
| PCT | pct@citycare.test |
| House Keeping | housekeeping@citycare.test |
| Nurse | nurse@citycare.test |
| Dietary | dietary@citycare.test |
| Patient | patient@citycare.test |

Newly registered patient accounts must verify their email before entering the dashboard. Demo accounts ending in `@citycare.test` are already verified and bypass OTP for classroom testing, while normal non-demo login still sends a one-time OTP code. The local `.env` uses Laravel's log mailer, so email and OTP messages can be reviewed in `storage/logs/laravel.log` during XAMPP testing.

## Stripe Billing Setup

The project uses Laravel Cashier with Stripe Checkout for live online patient payments. Add real test or live Stripe keys to `.env`, then clear the cache:

```bash
php artisan optimize:clear
```

Patients can open a pending bill and choose **Pay securely with Stripe**. Public visitors can also open `/shop`, add care services to the cart, choose checkout, and select **Visa card through Stripe**. Stripe Checkout redirects to Stripe's hosted card page where the patient enters Visa or other supported card details. MTN Mobile Money, Airtel Money, and Bank Deposit remain available as reference submissions for cashier verification.

Set the Stripe webhook endpoint to:

```text
http://localhost/CityCareClinicManagementSystem/public/api/stripe/webhook
```

For production hosting, replace the domain with the public domain and use HTTPS.

For local XAMPP webhook testing, the Stripe Dashboard cannot send events directly to private `localhost`. Install the Stripe CLI, sign in, and forward events to the local route:

```bash
stripe listen --forward-to http://localhost/CityCareClinicManagementSystem/public/api/stripe/webhook
```

The command prints a `whsec_...` webhook signing secret. Put that value in `.env` as `STRIPE_WEBHOOK_SECRET`, then run `php artisan optimize:clear`.

## Laravel Cloud Notes

The application uses database-backed sessions, queues, and cache. Make sure Laravel Cloud runs migrations after each deployment:

```bash
php artisan migrate --force
```

For a classroom/demo deployment, seed the default role accounts once after the database is attached:

```bash
php artisan db:seed --force
```

If every role login returns a 500 error on Cloud, first check that migrations have created the `users`, `sessions`, and clinic tables, then confirm the demo accounts were seeded. The default demo password is `citycare456`.

## Important URLs

- Public home: `/`
- About: `/about`
- Services: `/services`
- Care service shop: `/shop`
- Care cart and checkout: `/cart`, `/checkout`
- Location and directions for Plot 24 Yusuf Lule Road, Kampala: `/location`
- Contact and public appointment inquiry form: `/contact-us`
- Role-based access feature page: `/features/role-based-access`
- Live doctor slot checks feature page: `/features/live-doctor-slot-checks`
- 24/7 services feature page: `/features/24-7-services`
- Ambulance support feature page: `/features/ambulance-support`
- Patient login: `/login`
- Staff login: `/staff/login`
- Dashboard: `/dashboard`
- Patients: `/patients`
- Doctors: `/doctors`
- Departments: `/departments`
- Appointments: `/appointments`
- Payments: `/payments`
- Documents: `/documents`
- Drug Categories: `/drug-categories`
- Drugs: `/drugs`
- Prescription Queue: `/prescriptions`
- Radiology Orders: `/radiology-orders`
- Reports: `/reports`
- Availability API: `/api/doctors/{doctor}/available-slots?date=YYYY-MM-DD`
- Available doctors API: `/api/doctors/available?date=YYYY-MM-DD`

## Verification

The included test suite covers public pages, patient registration, role checking, the availability API, and appointment overlap rejection:

```bash
php artisan test
```

Latest local verification result:

```text
29 tests passed, 185 assertions.
```

## Author

Jonathan Mugume  
VU-BBC-2411-1587-DAY  
2026
