# Academic Compliance & Violation Management System (ACVMS)

A comprehensive web-based system designed to track student violations, manage disciplinary actions, and provide data-driven insights through an intuitive, role-based interface. It streamlines the entire violation management lifecycle from reporting and evidence attachment to AI-powered severity assessment and student appeals.

## 🎯 Problem Statement

Educational institutions frequently struggle with the manual, paper-based tracking of academic and behavioral violations, which often leads to lost records, inconsistent disciplinary actions, and a lack of transparency for students. Without a centralized digital system, administrators spend excessive time processing reports, while students lack a clear, accessible avenue to view their disciplinary standing or submit formal appeals. The Academic Compliance & Violation Management System (ACVMS) addresses this by providing a secure, centralized, and AI-assisted platform to streamline the reporting, review, and resolution of student violations, ensuring fairness, maintaining immutable records, and upholding academic integrity.

## 👥 Team Members

- **Ebad, Yasser C.**
- **Manial. Mohammed, A.**
- **Mentang, Rayyan A.**
- **Singsing, Adnan M.**

## ✨ Features

- **Role-Based Access Control**: Distinct interfaces and permissions for Administrators, Registrar, Teachers, and Students.
- **Violation Management**: Full CRUD operations for disciplinary reports with strict role-based access control and file attachment support for evidence.
- **AI Severity Assessment**: Integrated with Google Gemini AI to analyze violation descriptions and automatically suggest severity levels and potential actions.
- **Student Appeals Workflow**: Allows students to formally submit defenses or appeals for recorded violations.
- **Robust Security**:
  - CSRF Token Protection on all state-changing forms.
  - IP-based Login Rate Limiting to prevent brute-force attacks.
  - Secure `bcrypt` password hashing.
- **User Management**:
  - Bulk User Import via CSV file upload.
  - Self-Service User Profiles (update personal details, change passwords).
  - Secure "Forgot Password" self-service recovery system using php mailer.
- **Reporting & Auditing**:
  - Data Export (CSV) for violation reports and system logs for administrative filing.
  - Comprehensive Audit Logging for immutable tracking of all system events.
- **System-Wide Pagination**: Optimized data fetching and rendering for large datasets across the system.
- **Role-Based Dashboards**: Real-time aggregated statistics, trend analysis, and Chart.js visualizations for actionable data insights.
- **In-App Notification System**: Role-specific real-time alerts for case updates, appeals, and administrative actions.
- **Dark & Light Theme Support**: Integrated system-wide theme toggling for enhanced accessibility and user preference.

## ⚖️ Ethics & Compliance Statement

This Academic Compliance & Violation Management System (ACVMS) is designed with a strong commitment to ethical principles, data privacy, and equitable academic management.

**1. Data Privacy Compliance (RA 10173 — Data Privacy Act of 2012)**
We strictly adhere to the provisions of the Philippine Data Privacy Act of 2012. All student records, violation reports, and personal information collected within this system are treated with the utmost confidentiality. Data collection is limited strictly to what is necessary for maintaining academic compliance. User consent is implied for administrative purposes within the educational institution, and data is never shared with unauthorized third parties.

**2. Security Measures to Protect User Data**
To safeguard sensitive academic and personal data, the system implements robust security mechanisms. All user passwords are encrypted using modern `bcrypt` hashing algorithms. The application enforces CSRF (Cross-Site Request Forgery) token protection on all state-changing forms to prevent unauthorized actions. Additionally, IP-based login rate limiting is in place to protect against brute-force attacks, and strict role-based access control (RBAC) ensures that users can only access data relevant to their specific clearance level, preventing unauthorized data exposure.

**3. Responsible Use of AI-Generated Output & User-Submitted Data**
Our integration of the Google Gemini API is designed strictly as an _assistive_ tool, not an authoritative one. The AI analyzes user-submitted violation descriptions to suggest severity levels; however, all AI-generated outputs require human review and final approval by the Registrar or Administrator. We mandate that AI is used responsibly to ensure fairness and prevent algorithmic bias from independently determining a student's academic standing. User-submitted data is only processed for the explicit purpose of incident resolution.

---

## 🛠️ Technology Stack

- **Backend**: Native PHP 8.x (MVC)
- **Database**: MySQL (using PDO)
- **Frontend**: HTML5, Vanilla JavaScript, CSS3
- **UI Framework**: Bootstrap 5 (via CDN)
- **AI Integration**: Google Gemini API

---

## 💻 Requirements (Windows)

To run this project locally on a Windows machine, you need a local web server stack. We recommend **XAMPP** or **Laragon**.

- **PHP**: Version 8.0 or higher
- **MySQL / MariaDB**: Version 5.7+ / 10.x+
- **Web Server**: Apache
- **Browser**: Modern web browser (Chrome, Edge, Firefox)

## 🚀 Installation & Setup Guide

### 1. Set Up Your Local Server

1. Download and install [XAMPP](https://www.apachefriends.org/) or [Laragon](https://laragon.org/).
2. Start the **Apache** and **MySQL** services from your server's control panel.

### 2. Add the Project

1. Extract or clone this project folder into your server's public directory:
   - For XAMPP: `C:\xampp\htdocs\Academic-Compliance-Violation-Management-System`
   - For Laragon: `C:\laragon\www\Academic-Compliance-Violation-Management-System`

### 3. Database Configuration

1. Open your web browser and go to `http://localhost/phpmyadmin`
2. Create a new database named `acvms` (or any name you prefer).
3. Import the database schema:
   - Click on your newly created database.
   - Go to the **Import** tab.
   - Click **Choose File** and select the `database/schema.sql` file located inside the project folder.
   - Click **Import** at the bottom to create the tables and default accounts.

### 4. Environment Variables

1. Navigate to the project folder.
2. Find the file named `.env.example` and copy/rename it to `.env`.
3. Open the `.env` file in a text editor (like Notepad or VS Code) and update the database settings:

```env
APP_URL=http://localhost/Academic-Compliance-Violation-Management-System
DB_HOST=localhost
DB_PORT=3306
DB_NAME=acvms
DB_USER=root
DB_PASS=
```

_(Note: By default in XAMPP/Laragon, the MySQL user is `root` and the password is blank)._

4. **(Optional)** Add your Gemini API key and SMTP credentials to enable AI classification and email notifications.

### 5. Access the System

Open your web browser and navigate to:

```text
http://localhost/Academic-Compliance-Violation-Management-System
```

_(The system will automatically route you to the public directory.)_

---

## 🔑 Default Accounts

You can log in immediately using the accounts created during the database import:

**Administrator Account**

- **Email**: `admin@acvms.edu`
- **Password**: `Admin@1234`

**Registrar Account**

- **Email**: `registrar@acvms.edu`
- **Password**: `Registrar@1234`

> **Note:** It is highly recommended to change these default passwords after your first login!

## 📁 Directory Structure

- `/app` - Core MVC application (Controllers, Models, Middleware, Services).
- `/database` - Contains the `schema.sql` needed to initialize the database.
- `/public` - The web root containing CSS, JS, and image assets.
- `/routes` - Application URL routing logic.
- `/views` - HTML/PHP templates for the user interface.
- `/storage` - Log files and other generated data.
- `/uploads` - Directory for storing uploaded evidence files.

## 🤝 Contributing

For information on maintaining and adding features to the system, please refer to the internal documentation and ensure all code adheres to the native PHP MVC standards established in this repository.

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.
