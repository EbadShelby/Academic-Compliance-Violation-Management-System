# Academic Compliance & Violation Management System

## Overview

A comprehensive web-based system designed to track student violations, manage disciplinary actions, and provide data-driven insights through an intuitive, role-based interface. It streamlines the entire violation management lifecycle from reporting and evidence attachment to AI-powered severity assessment and student appeals.

## Features

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

## Tech Stack

- **Backend**: PHP.
- **Database**: MySQL / MariaDB (via PDO).
- **Frontend**: HTML5, JavaScript, Bootstrap 5.3 (CDN), Chart.js (CDN).

## License

See the `LICENSE` file for details.
