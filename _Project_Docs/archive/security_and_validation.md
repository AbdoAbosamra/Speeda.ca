# Security and Validation

### 1. Authorization
- The system uses `FormRequest` authorization to verify that a provider can only edit their own resource.
- **Critical Risk**: Admin routes rely on a generic `admin` middleware. If this is not strictly checked for `is_active` status, deactivated admins might still access the panel.

### 2. Validation
- **Strong**: `business_name` has strict regex to prevent XSS and script injection.
- **Missing**: The `certification` upload does not check for PHP file headers inside PDF/Images (MIME-type spoofing).
- **Throttling**: Public endpoints like `revealContact` and `trackClick` are throttled (`throttle:5,1` and `throttle:20,1`), which is a good baseline against automated spam.
