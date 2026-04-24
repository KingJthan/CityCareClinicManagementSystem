# CityCare Clinic Appointment and Patient Management System

## Project Documentation

Prepared for the Clinic Appointment and Patient Management System project-based exam.

## 1. Project Overview

CityCare Clinic Appointment and Patient Management System is a Laravel web application designed to centralize clinic operations. It supports patient registration, doctor scheduling, appointment booking, consultation records, pharmacy dispensing, radiology orders, cashier billing, reporting, role-based dashboards, secure document uploads, public feature pages, light and dark display modes, and polished feedback alerts.

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
- Dedicated public feature pages for role-based access, live doctor slot checks, 24/7 services, and ambulance support, linked directly from the homepage.
- Patient registration with email verification.
- Staff and patient login separation.
- OTP login for normal users.
- Role-based access for Administrator, Receptionist, Doctor, Cashier, Pharmacist, Radiology, RN, PCT, House Keeping, Nurse, Dietary, and Patient.
- Same-browser multi-login support using workspace sessions so different roles can stay active in separate tabs without logging each other out.
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
- Secure document upload for patient IDs, insurance cards, clinical attachments, payment proof, pharmacy documents, radiology reports, and role-specific staff documents.
- Dashboards with role-specific metrics, graphs, and pie charts.
- Workspace switching in the top bar when more than one role session is active.
- Light and dark display mode across public pages, authentication pages, and staff workspaces.
- Auto-dismissing success and error flash messages after login, logout, create, update, and delete actions.
- Administrator portrait branding on the public homepage and administrator workspace profile areas.
- Reports for appointments, payments, visits, prescriptions, and inventory with role-based visibility and CSV export.

## 5. System Modules and Screen Descriptions

### Public Home

The public home screen introduces CityCare Medical Centre and gives public users links to patient login, patient sign up, staff access, services, location, contact, and care shop. It highlights 24/7 services, ambulance support, professional clinic operations, and direct navigation to dedicated feature pages such as role-based access and doctor slot checks. It also includes an administrator leadership portrait for branding.

### Public Feature Pages

The public feature pages give a clearer explanation of how the system handles role-based access, live doctor slot checks, 24/7 services, and ambulance support. These pages are linked from the homepage and support the professional public-facing design requirement.

### Services

The services screen describes appointment support, diagnostics, pharmacy, emergency response, ambulance support, and other care services. It links users to the contact form, location directions, and care shop.

### Location

The location screen displays Plot 24 Yusuf Lule Road, Kampala and includes a working Google Maps directions button.

### Contact Us

The contact screen includes clinic contact details and a form for appointment booking or general inquiries.

### Authentication

The authentication module provides patient login, hidden staff login, patient registration, email verification, OTP verification, password change functionality, and same-browser multi-login workspaces. Staff users enter through the homepage staff-access menu, select the intended staff role, and then continue into a role-specific workspace URL after authentication.

### Administrator Dashboard

The administrator dashboard provides central monitoring for appointments, active patients, doctors on roster, pending payments, departments, monthly revenue, doctor workloads, payment methods, appointment performance, and attendance trends. The administrator workspace also uses the customized administrator portrait in the top workspace profile and sidebar profile card.

### Receptionist Workspace

The receptionist workspace allows reception staff to search patients and book, update, or cancel appointments. The appointment form loads available doctor slots dynamically to reduce double-booking.

### Doctor Workspace

The doctor workspace shows the doctor's own appointment schedule, assigned patients, consultation notes, previous treatment history, prescriptions, radiology orders, and treatment plans.

### Cashier Workspace

The cashier workspace allows cashiers to record and track payments, invoice numbers, payment methods, payment status, receipt references, and Stripe checkout information. Receptionists do not have permission to create or update billing records.

### Patient Portal

The patient portal lets patients view their profile, upcoming appointments, appointment requests, medical reports, blood work, bills, insurance, family history, prescriptions, treatment details, payment status, and uploaded documents such as National ID and insurance card files.

### Pharmacy

The pharmacy module is available to pharmacists only. Pharmacists can manage drug categories, drug stock, and prescription dispensing queues. Doctors prescribe drugs and send them to the pharmacy queue, while the pharmacist dashboard highlights pending prescriptions, active drugs, and low-stock items.

### Radiology

The radiology module allows doctors to create imaging orders and radiology staff to update study status and result notes.

### Reports

Each role can open the reports page. Report types are limited according to the role. Administrators can access broad reports, cashiers can access payment reports, doctors can access schedule and visit reports, pharmacists can access prescription and drug inventory reports, and patients can access their own appointment, visit, payment, and prescription reports.

### Documents

The documents module allows authorized users to upload and download files through authenticated routes. Patients can upload National ID, insurance cards, referral letters, and previous medical reports. Staff users receive role-specific document types such as consent forms, payment proof, prescriptions, imaging reports, nursing notes, housekeeping checklists, and dietary plans. Documents can also be attached directly to a patient profile where appropriate.

### Care Shop and Checkout

The care shop allows public users and patients to add services to a cart. Checkout supports Stripe-hosted Visa/card payments, MTN Mobile Money references, Airtel Money references, and bank deposit references.

## 6. Screenshots and Page Purposes

The screenshots below reflect the current CityCare interface and explain what each page is responsible for in the final submitted project.

### Public Homepage

![Public Homepage](screenshots/homepage.png)

Purpose: introduces CityCare Medical Centre to public visitors and gives quick access to services, patient login, patient sign up, staff access, care shop, contact, and location information. It also highlights the clickable feature links for role-based access, live doctor slot checks, 24/7 services, and ambulance support.

### Public Access and Staff Entry

![Public Access and Staff Entry](screenshots/authentication.png)

Purpose: shows how staff access stays separate from the patient portal on the public navigation bar. From this entry point, staff users open their own login path while patients continue through the patient login and patient sign-up options.

### Staff Role Selection

![Staff Role Selection](screenshots/authenticate.png)

Purpose: demonstrates the role selector used before staff login. This helps prevent staff from entering the wrong portal and supports the role-based authorization requirement for administrator, receptionist, doctor, cashier, pharmacist, radiology, RN, PCT, house keeping, nurse, and dietary roles.

### Staff Login Page

![Staff Login Page](screenshots/login.png)

Purpose: presents the styled staff portal login form with role selection, demo password guidance, and secure entry into the CityCare workspace. This screen supports the authentication requirement together with email verification, OTP, and role-specific access.

### Care Shop

![Care Shop](screenshots/careshop.png)

Purpose: allows public users and patients to choose clinic services as billable products. The page supports the billing requirement by connecting services to cart checkout and payment processing.

### Cashier Dashboard

![Cashier Dashboard](screenshots/cashier.png)

Purpose: supports cashier duties such as payment recording, pending invoice review, receipt tracking, daily collection summaries, and monthly revenue visibility. It confirms that revenue is available to cashier and administrator roles only.

### Stripe Checkout

![Stripe Checkout](screenshots/complete-checkout.png)

Purpose: shows the hosted Stripe payment page used for secure card processing. This fulfills the requirement for live-style billing workflow where patients or public users can proceed from CityCare checkout into Stripe card payment.

### Contact Page

![Contact Page](screenshots/contactus.png)

Purpose: provides a public inquiry and appointment-booking form. Visitors can submit names, phone numbers, request types, preferred appointment dates, and messages for clinic follow-up.

### Checkout Details

![Checkout Details](screenshots/detailforcheckout.png)

Purpose: captures patient details and payment route selection before checkout. Users can choose Visa card through Stripe, MTN Mobile Money, Airtel Money, or bank deposit so the system can route payment correctly.

### Doctor Dashboard

![Doctor Dashboard](screenshots/doctor.png)

Purpose: gives doctors a role-specific workspace for appointment schedules, patient access, consultation tracking, completed visits, and patient counts. It supports the doctor requirement to view patient history, notes, and treatment-related activity from one dashboard.

### Services Page

![Services Page](screenshots/services.png)

Purpose: presents CityCare's public services offering, including consultation-based care, diagnostics, 24/7 support, and ambulance coordination. It helps visitors move toward service selection, booking, or directions.

### Location Page

![Location Page](screenshots/location.png)

Purpose: displays Plot 24 Yusuf Lule Road, Kampala and gives a working directions button so patients and visitors can open a route in Google Maps.

### Patient Dashboard

![Patient Dashboard](screenshots/patient.png)

Purpose: gives patients their own role-specific portal where they can see their profile, appointments, visit activity, pending payments, paid invoices, reports, documents, care shop, and cart links.

### Pharmacy Dashboard

![Pharmacy Dashboard](screenshots/pharmacy.png)

Purpose: shows the pharmacist-only workspace for handling prescriptions, managing drug inventory, reviewing drug categories, and monitoring low-stock items. This supports the pharmacy-only access rule and doctor-to-pharmacist prescription handoff.

### Receptionist Dashboard

![Receptionist Dashboard](screenshots/reception.png)

Purpose: supports reception staff with patient lookup, appointment booking, appointment updates, and doctor availability checks while keeping revenue and cashier billing tasks outside reception access.

### Reports Page

![Reports Page](screenshots/report.png)

Purpose: provides role-based reporting with date filters, report-type selection, and CSV export. The screenshot demonstrates that the reports page can generate work summaries according to the signed-in role.

### Cart Review

![Cart Review](screenshots/reviewcart.png)

Purpose: allows the user to review selected clinic services, edit quantity, remove items, and confirm the total before continuing to checkout.

### Administrator Dashboard

![Administrator Dashboard](screenshots/administrator-dashboard.png)

Purpose: gives the administrator a central monitoring workspace for appointments, active patients, doctor roster, pending payments, revenue-aware oversight, and operational shortcuts such as reports and documents.

## 7. Database Design Summary

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
- `clinic_documents`
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
- A clinic document may belong to a patient, belongs to the uploading user, and may belong to an owner user.

## 8. Authentication and Authorization

The application includes authentication, patient registration, email verification, OTP login, password update, and role middleware. Role permissions are enforced in the route file and controllers.

Examples:

- Administrators can manage patients, doctors, departments, appointments, payments, reports, and radiology.
- Receptionists can manage appointments and view patients/doctors but cannot manage payments.
- Doctors can view their own appointments and create consultations, prescriptions, and radiology orders.
- Cashiers can create, update, and track payments.
- Pharmacists can access drug categories, drugs, and prescription queues.
- Patients can view their own records and initiate online or mobile money payment references.
- Patients can upload and view their own documents, while staff document access is limited by role and patient access rules.
- Multiple roles can remain logged in within the same browser by using separate workspace session URLs such as `workspace/{workspace}/dashboard`.

## 9. Search, Filtering, Pagination, and API/AJAX

The patient list supports search, gender filtering, status filtering, and pagination. Appointment and payment lists also include filters appropriate to their workflows.

The system includes JSON API endpoints:

- `/api/doctors/{doctor}/available-slots?date=YYYY-MM-DD`
- `/api/doctors/available?date=YYYY-MM-DD`
- `/api/patients/search?q=search-term`

The appointment form uses AJAX to load doctor slots dynamically when the user selects a doctor and date.

## 10. Reporting

The reports module supports role-based reports with CSV export. Reports include appointments, payments, patient visits, prescriptions, drug inventory, radiology orders, and patient-specific records.

## 11. Stripe and Billing

The system uses Laravel Cashier and Stripe Checkout. Patients and public users can choose Visa/card checkout through Stripe. Mobile money and bank deposit options collect references for cashier verification.

For local webhook testing, Stripe CLI can forward events to:

```text
http://localhost/CityCareClinicManagementSystem/public/api/stripe/webhook
```

## 12. Code Quality and Best Practices

- Laravel PSR-4 autoloading and namespaces are used for controllers, models, services, middleware, rules, and tests.
- Controllers are organized around RESTful resources where appropriate.
- Business logic that would otherwise be duplicated is placed in services such as `AppointmentSlotService` and `BillingService`.
- Validation rules are centralized where useful, including the custom `PhoneNumber` rule.
- Document upload and access rules are centralized in `DocumentService`.
- Eloquent relationships are defined in model classes.
- Role access is handled through middleware and route groups.
- Blade templates use a master layout, reusable components, partials, and Bootstrap-based responsive design.
- Soft deletes and confirmation prompts are used for safer delete actions.
- Feature tests verify role permissions, registration, OTP behavior, appointment overlap prevention, cart checkout, and reporting access.

## 13. Verification Result

The local verification command is:

```bash
php artisan test
```

Current result:

```text
31 tests passed, 207 assertions.
```

## 14. Important URLs

- Public home: `/`
- About: `/about`
- Services: `/services`
- Role-based access feature page: `/features/role-based-access`
- Live doctor slot checks feature page: `/features/live-doctor-slot-checks`
- 24/7 services feature page: `/features/24-7-services`
- Ambulance support feature page: `/features/ambulance-support`
- Care shop: `/shop`
- Cart: `/cart`
- Checkout: `/checkout`
- Location: `/location`
- Contact: `/contact-us`
- Patient login: `/login`
- Staff login: `/staff/login`
- Dashboard: `/dashboard`
- Workspace dashboard: `/workspace/{workspace}/dashboard`
- Patients: `/patients`
- Doctors: `/doctors`
- Departments: `/departments`
- Appointments: `/appointments`
- Payments: `/payments`
- Documents: `/documents`
- Pharmacy categories: `/drug-categories`
- Drugs: `/drugs`
- Prescriptions: `/prescriptions`
- Radiology: `/radiology-orders`
- Reports: `/reports`
