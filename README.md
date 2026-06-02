# Academic Compliance & Violation Management System (ACVMS)

A comprehensive web-based system designed to track student violations, manage disciplinary actions, and provide data-driven insights through an intuitive, role-based interface. It streamlines the entire violation management lifecycle from reporting and evidence attachment to AI-powered severity assessment and student appeals.

## ✨ Features

- **Role-Based Access Control**: Distinct interfaces and permissions for Administrators, Teachers, and Students.
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
  - Secure "Forgot Password" self-service recovery system using native PHP mail.
- **Reporting & Auditing**:
  - Data Export (CSV) for violation reports and system logs for administrative filing.
  - Comprehensive Audit Logging for immutable tracking of all system events.
- **System-Wide Pagination**: Optimized data fetching and rendering for large datasets across the system.
- **Role-Based Dashboards**: Real-time aggregated statistics, trend analysis, and Chart.js visualizations for actionable data insights.

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
