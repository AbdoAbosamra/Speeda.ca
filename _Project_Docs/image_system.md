# Image System

### 1. Storage Logic
- **Profile Image**: Manual upload to `public` disk under `profile-images/`. Stores the path directly in DB.
- **Gallery**: Managed by `Spatie\MediaLibrary`. Supports conversions (`gallery_thumb`, `gallery_large`) and WebP optimization.

### 2. Root Causes of Broken Images
- **URL Normalization**: `ServiceProvider::normalizePublicUrl` fails if the `APP_URL` in `.env` doesn't match the access URL (e.g., `localhost` vs `127.0.0.1`).
- **Disk Mismatch**: Profile images check for file existence on the `public` disk, but if the local `storage/app/public` symlink isn't linked, images fail while DB paths look correct.

### 3. Recommended Fix
- **Unification**: Migrate profile images to MediaLibrary. This ensures all images benefit from the same conversion, optimization, and WebP logic.
