# System Overview

### 1. System Intent
Speeda is a high-performance Service Marketplace platform designed to connect clients with local professional service providers (e.g., Construction, Maintenance, Professional Services). It operates as a localized directory with discovery, lead generation (contact reveal), and reputation management (reviews/endorsements) systems.

### 2. Core Modules
- **Discovery Engine**: A hierarchical category and location-based search system.
- **Provider Ecosystem**: Comprehensive profile management including experience, portfolio, and certification.
- **Reputation System**: Multi-layered trust system (Reviews, Star Ratings, and "Endorsements").
- **Analytics Engine**: Internal visitor tracking + Facebook CAPI (Conversion API) for lead event measurement.
- **Admin Control Plane**: Moderation of content, categories, and audit logging.

### 3. Architecture Type: Monolith (Laravel 12)
- **Framework**: Laravel 12.x (Modern Blade-based Monolith).
- **Frontend**: Blade Templates, Bootstrap 5.3, Alpine.js, and Vanilla CSS.
- **Data Persistence**: MySQL with complex subqueries for live reputation calculation.
- **External Integration**: Facebook Pixel / Meta API (CAPI) for marketing event tracking.

### 4. Key Flows
- **Providers**: Registration → Profile Completion (tracked via gamification percent) → Portfolio Upload → Activity Monitoring via Dashboard.
- **Clients**: Search (via Filters Engine) → Profile Discovery → Contact Reveal (Lead Action) → Post-service Review/Rating.
- **Admin**: Review Approval → User Moderation → Activity Log Monitoring.
