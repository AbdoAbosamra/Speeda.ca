# Frontend Architecture

### 1. Design System
- **Engine**: Blade + Bootstrap 5.3 + Alpine.js.
- **Aesthetics**: "Premium Light Mode" using HSL colors, Inter typography, and glassmorphism headers for the Admin area.

### 2. RTL Handling
- The system achieves RTL via `dir` attribute in the main layout based on `app()->getLocale()`.
- **Alert**: Many Blade components use `ms-auto` or `me-auto`. While Bootstrap 5 supports RTL with these, manual CSS pixel values (e.g., `margin-left: 280px`) in layouts break the layout in Arabic.

### 3. UI Challenges
- **Overflow**: Long profession names or multi-language descriptions in cards often overflow their containers without truncation.
- **Responsiveness**: The `admin-top-bar` has hardcoded pixel margins that likely overlap content on tablets/small screens.
