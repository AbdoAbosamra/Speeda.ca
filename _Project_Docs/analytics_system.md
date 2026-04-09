# Analytics System

### 1. Hybrid Tracking
- **Internal**: `TrackProviderViewAction` records provider views. It uses a `session_hash` (Privacy-compliant) to prevent duplicate views from the same user within 24 hours.
- **External**: Meta Pixel / CAPI. Tracks `ViewContent` (on profile open) and `Lead` (on contact reveal).

### 2. Accuracy & Privacy Issues
- **Accuracy**: Session hashes alone are weak against VPNs or session-clearing scripts, leading to inflated view counts.
- **Privacy**: No IP is stored, but the `session_hash` logic is cleartext-derived; a salt should be added to the hash to prevent reverse-engineering of user sessions.
