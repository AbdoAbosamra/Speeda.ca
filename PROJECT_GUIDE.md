# Speeda Project - Complete Guide for AI Prompt Engineer

## 📋 Project Overview

**Project Name:** Speeda  
**Type:** Service Provider Marketplace Platform  
**Language:** Laravel (PHP) + Blade Templates  
**Languages Supported:** Arabic (RTL), English (LTR), French (LTR)  
**Target Users:** Service Providers & Customers

---

## 🛠️ Tech Stack

### Backend
- **Framework:** Laravel (PHP)
- **Database:** MySQL
- **Authentication:** Laravel Auth
- **Caching:** Redis/File Cache

### Frontend
- **Template Engine:** Blade
- **CSS Framework:** Bootstrap 5.3.3
- **Icons:** Font Awesome 6.5.0
- **JavaScript:** Alpine.js 3.x
- **Build Tool:** Vite

### Key Packages
- AdminLTE-style admin panel
- Multi-language support
- Image handling
- Rating system
- Reviews & endorsements

---

## 📁 Project Structure

```
Speeda/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/          # Admin controllers
│   │   │   ├── Auth/           # Authentication
│   │   │   ├── HomeController.php
│   │   │   └── NotificationController.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── ServiceProvider.php
│   │   ├── AdminNotification.php
│   │   ├── Review.php
│   │   ├── Category.php
│   │   └── Location.php
│   ├── Providers/
│   │   └── AppServiceProvider.php
│   └── Observers/               # Model observers
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   │   ├── app.blade.php    # Main layout
│   │   │   ├── navigation.blade.php
│   │   │   └── footer.blade.php
│   │   ├── components/
│   │   │   ├── main-nav.blade.php
│   │   │   ├── admin-top-bar.blade.php
│   │   │   └── language-switcher.blade.php
│   │   ├── admin/
│   │   │   └── notifications/
│   │   ├── notifications.blade.php
│   │   ├── home.blade.php
│   │   └── blog/
│   ├── css/
│   └── js/
├── routes/
│   └── web.php
└── database/
    └── migrations/
```

---

## 🌐 Multi-Language System

### Supported Languages
- **Arabic (ar)** - RTL direction
- **English (en)** - LTR direction
- **French (fr)** - LTR direction

### Implementation
- Language stored in session
- `app()->getLocale()` returns current language
- RTL/LTR set in HTML `dir` attribute
- Translations in `lang/{locale}` directories

### RTL Handling
```php
dir="{{ in_array(app()->getLocale(), ['ar', 'he', 'ur', 'fa']) ? 'rtl' : 'ltr' }}"
```

---

## 🔔 Notification System (Recently Refactored)

### Architecture
- **Admin-created notifications** targeting service providers only
- **30-day expiration** from creation
- **Multilingual** (Arabic, English, French)
- **Read/unread tracking** via pivot table

### Database Tables
1. `admin_notifications` - Stores notifications
   - `title_ar`, `title_en`, `title_fr`
   - `message_ar`, `message_en`, `message_fr`
   - `target_type` (always 'provider_only')
   - `created_by` (admin user ID)
   - `expires_at` (timestamp)

2. `admin_notification_user` - Pivot table for read status
   - `user_id`
   - `admin_notification_id`
   - `read_at` (nullable timestamp)

### Backend Controllers

#### NotificationController (User-facing)
- `index()` - Lists notifications with filters (all/unread/read) and pagination
- `markAsRead()` - Marks individual or all as read via AJAX
- `unreadCount()` - Returns unread count for polling

#### AdminNotificationController (Admin)
- `index()` - Lists all notifications with search & status filters
- `create()` - Show create form
- `store()` - Create notification with 30-day expiration
- `destroy()` - Delete notification

### Frontend Components

#### Main Navigation Dropdown
- Shows last 10 notifications
- Unread badge with count
- Mark all read button
- "View All" link to notifications page
- Responsive (desktop & mobile)

#### Notifications Page
- Filter tabs: All, Unread, Read
- Count badges on each filter
- Card-based layout
- Visual distinction for read/unread
- Detail modal for full content
- Pagination (15 per page)

#### Admin Notification Management
- Stats cards (total, active, expired)
- Search across all languages
- Status filter (active/expired)
- Preview modal with language tabs
- Flag icons for language sections

### Performance Optimizations
- **Caching:** 5-minute TTL per user (`nav_notifications_{user_id}`)
- **Dropdown limit:** 10 notifications max
- **Cache clearing:** On create/delete/mark-read operations
- **Pagination:** 15 items per page on notifications page

### Key Code Locations
- **Model:** `app/Models/AdminNotification.php`
- **User Controller:** `app/Http/Controllers/NotificationController.php`
- **Admin Controller:** `app/Http/Controllers/Admin/AdminNotificationController.php`
- **View Composer:** `app/Providers/AppServiceProvider.php` (lines 84-124)
- **Dropdown:** `resources/views/components/main-nav.blade.php`
- **Page:** `resources/views/notifications.blade.php`
- **Admin Index:** `resources/views/admin/notifications/index.blade.php`
- **Admin Create:** `resources/views/admin/notifications/create.blade.php`

---

## 👥 User Types

### 1. Admin
- Full access to admin panel
- Can create/delete notifications
- Manages service providers
- Manages categories, locations

### 2. Service Provider
- Has profile with completion tracking
- Receives admin notifications
- Can be reviewed by customers
- Has dashboard

### 3. Customer
- Browses service providers
- Can leave reviews
- No admin notifications

---

## 🎯 Key Features

### Service Provider Features
- Profile completion tracking (80% threshold for top providers)
- Reviews & endorsements system
- Rating calculation (stored in `calculated_rating`)
- Media gallery
- Location-based search

### Admin Features
- Dashboard with stats
- Notification management
- Provider management
- Category management
- Content management (blog)

### User Features
- Search by category & location
- View provider profiles
- Leave reviews
- Multi-language interface

---

## 📝 Coding Standards

### PHP/Laravel
- Follow PSR-12 coding standards
- Use type hints where possible
- Use Laravel conventions
- Comments in English

### Blade
- Component-based architecture
- Use `@once` for CSS/JS to avoid duplication
- Use `@php` for complex logic
- Escape output with `{{ }}` (not `{!! !!}`)

### CSS
- CSS variables for theming
- Responsive design (mobile-first)
- RTL support via logical properties (`margin-inline-start`)
- Bootstrap 5 + custom styles

### JavaScript
- Alpine.js for interactivity
- Bootstrap for modals
- Fetch API for AJAX
- No jQuery dependency

---

## 🔐 Security

- CSRF protection on all forms
- Authentication middleware on protected routes
- Input validation
- SQL injection prevention via Eloquent
- XSS prevention via Blade escaping

---

## 🚀 Performance

- Caching for expensive queries
- Eager loading to prevent N+1
- Pagination for large datasets
- Image optimization
- Lazy loading

---

## 📊 Database Relationships

### Key Relationships
- **User** hasOne **ServiceProvider**
- **ServiceProvider** belongsTo **User**
- **ServiceProvider** belongsTo **Category**
- **ServiceProvider** belongsTo **Location**
- **ServiceProvider** hasMany **Reviews**
- **AdminNotification** belongsTo **User** (created_by)
- **AdminNotification** belongsToMany **User** (readByUsers)

---

## 🎨 UI/UX Guidelines

### Design System
- Primary color: Blue (#3b82f6)
- Border radius: 8-16px
- Shadows: Subtle, layered
- Transitions: 0.2-0.3s ease
- Font: Inter (Google Fonts)

### Accessibility
- ARIA labels on interactive elements
- Keyboard navigation support
- Focus states
- Screen reader friendly
- Color contrast compliance

### Responsive Breakpoints
- Mobile: < 768px
- Tablet: 768px - 992px
- Desktop: > 992px

---

## 🔄 Recent Changes (Notification System Refactor)

### Files Modified
1. `app/Providers/AppServiceProvider.php` - Added caching
2. `app/Http/Controllers/NotificationController.php` - Added pagination, filters, cache clearing
3. `app/Http/Controllers/Admin/AdminNotificationController.php` - Added search, filters, stats
4. `routes/web.php` - Added unread-count route
5. `resources/views/components/main-nav.blade.php` - Added dropdown HTML
6. `resources/views/notifications.blade.php` - Complete redesign
7. `resources/views/admin/notifications/index.blade.php` - Added search, stats
8. `resources/views/admin/notifications/create.blade.php` - Added preview modal

### Production Safety
- No database migrations required
- No breaking changes
- Backward compatible
- Existing data preserved

---

## 📌 Important Notes for AI Prompts

1. **Always check existing code** before suggesting changes
2. **Maintain multilingual support** in all new features
3. **RTL support is critical** for Arabic
4. **Use caching** for performance
5. **Follow existing patterns** in the codebase
6. **Test on mobile** for responsive design
7. **No destructive changes** to existing data
8. **Use View::share** for global data
9. **Use observers** for model events
10. **Keep Blade components reusable**

---

## 🚦 Getting Started

### Development Setup
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

### Testing
- Run tests: `php artisan test`
- Check logs: `storage/logs/laravel.log`

---

## 📞 Contact

For questions about this project, refer to the codebase documentation or existing team members.

---

**Last Updated:** May 2026  
**Version:** Current Production Build
