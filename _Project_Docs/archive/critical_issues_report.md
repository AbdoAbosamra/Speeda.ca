# Critical Issues Report

### Priority: HIGH
- **Performance**: Subqueries in the main search `index()` will cause DB bottlenecks as the provider count increases.
  - *Impact*: slow page loads, higher server costs.
  - *Fix*: implement cached rating column on the `service_providers` table.
- **UI/Layout**: Hardcoded LTR logic in CSS (e.g. `margin-left: 280px`) breaks RTL functionality for Arabic users.
  - *Impact*: broken dashboard UX for a primary target audience.
  - *Fix*: replace hardcoded directions with CSS Logical Properties.

### Priority: MEDIUM
- **Image System**: Split logic between manual columns and Spatie MediaLibrary makes maintenance complex.
  - *Impact*: higher risk of broken images and harder data migrations.
  - *Fix*: unify all image handling under Spatie MediaLibrary.
- **Analytics**: Cleartext-derived session hashes are vulnerable to reverse-engineering.
  - *Impact*: privacy risk for visitors.
  - *Fix*: add a secret salt to the hash generation logic.

### Priority: LOW
- **SEO**: Missing dynamic meta descriptions on provider profiles.
  - *Impact*: lower CTR from search engines.
  - *Fix*: implement a `getMetaDescription()` method on the `ServiceProvider` model.
