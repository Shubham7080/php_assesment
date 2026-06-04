# Assessment — Book Authoring and Publishing Platform API

A backend REST API developed as part of a technical assessment for a book authoring and publishing platform. The system enables authors to create, manage, version, and submit books for review while providing reviewers and administrators with tools to moderate, approve, and publish content through a structured workflow.

The platform supports the complete lifecycle of a book, from initial draft creation to final publication. Authors can organize content into chapters and pages, upload manuscript documents for automatic content extraction, maintain historical versions of their work through snapshot-based versioning, and submit books for moderation and review. Reviewers can evaluate submitted books and either approve or reject them, while administrators are responsible for publishing approved content.

The application follows a clean, layered architecture with separation of concerns across controllers, services, policies, resources, and event listeners. Business processes such as moderation, workflow transitions, notifications, and analytics are implemented using an event-driven approach to improve maintainability, scalability, and extensibility.

Key capabilities include:

* JWT-based authentication and role-based authorization
* Book, chapter, and page management
* Snapshot-based versioning with rollback support
* Document upload and manuscript conversion
* Automated content moderation using configurable rules
* Multi-stage review and publishing workflow
* Event-driven notifications and analytics tracking
* Consistent JSON API responses
* Comprehensive automated test coverage

Built with **Laravel 13**, **PHP 8.3**, **MySQL 8 (MariaDB compatible)**, **JWT Authentication**, and modern Laravel development practices to demonstrate clean architecture, maintainable code organization, and robust API design.

---
