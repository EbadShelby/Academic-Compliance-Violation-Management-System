---
trigger: always_on
---

==============================
TECHXP EXECUTION CONTRACT
==============================

This project must be fully compatible with TechXP shared hosting environment.

STRICT RULES:
- No terminal access exists.
- No Node.js, npm, Vite, Webpack, or frontend build tools.
- No Composer or external PHP package managers.
- No Laravel, Symfony, or any frameworks.
- Use only native PHP (MVC style), MySQL, HTML, CSS (Bootstrap CDN allowed), and vanilla JavaScript.
- Code must run immediately after upload (no build step).
- Apache is preconfigured and cannot be modified.
- Use PDO (preferred) or mysqli for database access.
- No CLI-based setup or installation steps.

ARCHITECTURE RULES:
- Use lightweight custom MVC structure.
- No autoloading or Composer dependency injection.
- Manual include/require for files.

DEPLOYMENT RULE:
- Project is uploaded directly to hosting root (e.g., public_html).
- All paths must be relative.

TECHXP LIMITATIONS:
- No SSH or terminal access.
- No background workers or queues.
- No Node-based tooling.
- No Redis or external service dependencies.

SIMPLICITY RULE:
- Prefer simple, readable PHP code over complex architecture.
- Avoid overengineering.