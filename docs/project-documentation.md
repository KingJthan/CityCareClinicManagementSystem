# CityCare Clinic Appointment and Patient Management System

## Project Documentation

Prepared for the Clinic Appointment and Patient Management System project-based exam.

## 1. Project Overview

CityCare Clinic Appointment and Patient Management System is a Laravel web application designed to centralize clinic operations. It supports patient registration, doctor scheduling, appointment booking, consultation records, pharmacy dispensing, radiology orders, cashier billing, reporting, and role-based dashboards.

The system addresses the clinic problems described in the question paper:

- Receptionists can book, update, and cancel appointments while checking doctor availability.
- Doctors can access patient details, previous visits, consultation notes, prescriptions, treatment plans, and radiology requests.
- Cashiers can record and track patient payments without reception staff handling billing.
- Patients can log in to view their profile, appointment requests, medical information, bills, insurance, and treatment details.
- Management can monitor appointments, doctor workloads, payment summaries, attendance trends, and reports from one system.

## 2. Setup Steps

1. Place the project folder in `C:\xampp\htdocs\CityCareClinicManagementSystem`.
2. Start Apache and MySQL from the XAMPP Control Panel.
3. Create the database:

```sql
CREATE DATABASE citycare_clinic CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

4. Configure `.env`:

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

5. Run migrations and seeders:

```bash
php artisan migrate --seed
```

6. Open the application:

```text
http://localhost/CityCareClinicManagementSystem/public
```

7. Run verification tests:

```bash
php artisan test
```

## 3. Demo Accounts

All seeded test accounts use the password `citycare456`.

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

Demo accounts ending in `@citycare.test` bypass OTP for classroom testing. Newly registered patient accounts must verify their email before accessing the dashboard.

## 4. Major Features

- Public homepage with patient login, patient sign up, staff access, services, location, contact, and care shop links.
- Patient registration with email verification.
- Staff and patient login separation.
- OTP login for normal users.
- Role-based access for Administrator, Receptionist, Doctor, Cashier, Pharmacist, Radiology, RN, PCT, House Keeping, Nurse, Dietary, and Patient.
- CRUD management for patients, doctors, departments, appointments, payments, drug categories, and drugs.
- Doctor schedule definition using working days, shift start time, shift end time, and appointment slot length.
- Appointment booking with automatic doctor availability checks to prevent double-booking.
- AJAX appointment slots that load dynamically based on selected doctor and date.
- Patient search, filtering, and pagination.
- Doctor consultation notes, diagnosis, treatment plan, prescriptions, and radiology orders.
- Pharmacy module for pharmacist-only drug category management, drug inventory, and prescription dispensing.
- Radiology module for imaging orders and result tracking.
- Cashier billing with MTN Mobile Money, Airtel Money, Card Payment, Bank Deposit, Insurance, and Stripe Checkout.
- Public care shop with cart and Stripe-hosted card checkout.
- Patient check-in links and QR-code support that become available 30 minutes before appointment time.
- Patient medical information including blood work, lab results, vital signs, insurance, family history, prescriptions, and treatment details.
- Dashboards with role-specific metrics, graphs, and pie charts.
- Reports for appointments, payments, visits, prescriptions, and inventory with role-based visibility and CSV export.

## 5. System Modules and Screen Descriptions

### Public Home

The public home screen introduces CityCare Medical Centre and gives public users links to patient login, patient sign up, staff access, services, location, contact, and care shop. It highlights 24/7 services, ambulance support, and professional clinic operations.

### Services

The services screen describes appointment support, diagnostics, pharmacy, emergency response, ambulance support, and other care services. It links users to the contact form, location directions, and care shop.

### Location

The location screen displays Plot 24 Yusuf Lule Road, Kampala and includes a working Google Maps directions button.

### Contact Us

The contact screen includes clinic contact details and a form for appointment booking or general inquiries.

### Authentication

The authentication module provides patient login, hidden staff login, patient registration, email verification, OTP verification, and password change functionality.

### Administrator Dashboard

The administrator dashboard provides central monitoring for appointments, active patients, doctors on roster, pending payments, departments, monthly revenue, doctor workloads, payment methods, appointment performance, and attendance trends.

### Receptionist Workspace

The receptionist workspace allows reception staff to search patients and book, update, or cancel appointments. The appointment form loads available doctor slots dynamically to reduce double-booking.

### Doctor Workspace

The doctor workspace shows the doctor's own appointment schedule, assigned patients, consultation notes, previous treatment history, prescriptions, radiology orders, and treatment plans.

### Cashier Workspace

The cashier workspace allows cashiers to record and track payments, invoice numbers, payment methods, payment status, receipt references, and Stripe checkout information. Receptionists do not have permission to create or update billing records.

### Patient Portal

The patient portal lets patients view their profile, upcoming appointments, appointment requests, medical reports, blood work, bills, insurance, family history, prescriptions, treatment details, and payment status.

### Pharmacy

The pharmacy module is available to pharmacists only. Pharmacists can manage drug categories, drug stock, and prescription dispensing queues. Doctors prescribe drugs and send them to the pharmacy queue.

### Radiology

The radiology module allows doctors to create imaging orders and radiology staff to update study status and result notes.

### Reports

Each role can open the reports page. Report types are limited according to the role. Administrators can access broad reports, cashiers can access payment reports, doctors can access schedule and visit reports, pharmacists can access prescription and drug inventory reports, and patients can access their own appointment, visit, payment, and prescription reports.

### Care Shop and Checkout

The care shop allows public users and patients to add services to a cart. Checkout supports Stripe-hosted Visa/card payments, MTN Mobile Money references, Airtel Money references, and bank deposit references.

## 6. Database Design Summary

The project uses migrations with primary keys, foreign keys, indexes, soft deletes, and relationship tables. Main tables include:

- `users`
- `departments`
- `doctors`
- `patients`
- `appointments`
- `consultations`
- `payments`
- `billing_products`
- `payment_items`
- `drug_categories`
- `drugs`
- `prescriptions`
- `radiology_orders`
- `patient_insurances`
- `lab_results`
- `vital_signs`
- `family_histories`
- `public_inquiries`
- `sessions`

Important relationships include:

- A department has many doctors and appointments.
- A doctor belongs to a user and a department.
- A patient may belong to a user account.
- A patient has many appointments, consultations, payments, prescriptions, radiology orders, lab results, vital signs, family histories, and insurance records.
- An appointment belongs to a patient, doctor, and department.
- A payment belongs to a patient and may belong to an appointment.
- A prescription belongs to an appointment, patient, doctor, and drug.
- A radiology order belongs to an appointment, patient, and doctor.

## 7. Authentication and Authorization

The application includes authentication, patient registration, email verification, OTP login, password update, and role middleware. Role permissions are enforced in the route file and controllers.

Examples:

- Administrators can manage patients, doctors, departments, appointments, payments, reports, and radiology.
- Receptionists can manage appointments and view patients/doctors but cannot manage payments.
- Doctors can view their own appointments and create consultations, prescriptions, and radiology orders.
- Cashiers can create, update, and track payments.
- Pharmacists can access drug categories, drugs, and prescription queues.
- Patients can view their own records and initiate online or mobile money payment references.

## 8. Search, Filtering, Pagination, and API/AJAX

The patient list supports search, gender filtering, status filtering, and pagination. Appointment and payment lists also include filters appropriate to their workflows.

The system includes JSON API endpoints:

- `/api/doctors/{doctor}/available-slots?date=YYYY-MM-DD`
- `/api/doctors/available?date=YYYY-MM-DD`
- `/api/patients/search?q=search-term`

The appointment form uses AJAX to load doctor slots dynamically when the user selects a doctor and date.

## 9. Reporting

The reports module supports role-based reports with CSV export. Reports include appointments, payments, patient visits, prescriptions, drug inventory, radiology orders, and patient-specific records.

## 10. Stripe and Billing

The system uses Laravel Cashier and Stripe Checkout. Patients and public users can choose Visa/card checkout through Stripe. Mobile money and bank deposit options collect references for cashier verification.

For local webhook testing, Stripe CLI can forward events to:

```text
http://localhost/CityCareClinicManagementSystem/public/api/stripe/webhook
```

## 11. Code Quality and Best Practices

- Laravel PSR-4 autoloading and namespaces are used for controllers, models, services, middleware, rules, and tests.
- Controllers are organized around RESTful resources where appropriate.
- Business logic that would otherwise be duplicated is placed in services such as `AppointmentSlotService` and `BillingService`.
- Validation rules are centralized where useful, including the custom `PhoneNumber` rule.
- Eloquent relationships are defined in model classes.
- Role access is handled through middleware and route groups.
- Blade templates use a master layout, reusable components, partials, and Bootstrap-based responsive design.
- Soft deletes and confirmation prompts are used for safer delete actions.
- Feature tests verify role permissions, registration, OTP behavior, appointment overlap prevention, cart checkout, and reporting access.

## 12. Verification Result

The local verification command is:

```bash
php artisan test
```

Current result:

```text
24 tests passed, 144 assertions.
```

## 13. Important URLs

- Public home: `/`
- About: `/about`
- Services: `/services`
- Care shop: `/shop`
- Cart: `/cart`
- Checkout: `/checkout`
- Location: `/location`
- Contact: `/contact-us`
- Patient login: `/login`
- Staff login: `/staff/login`
- Dashboard: `/dashboard`
- Patients: `/patients`
- Doctors: `/doctors`
- Departments: `/departments`
- Appointments: `/appointments`
- Payments: `/payments`
- Pharmacy categories: `/drug-categories`
- Drugs: `/drugs`
- Prescriptions: `/prescriptions`
- Radiology: `/radiology-orders`
- Reports: `/reports`

