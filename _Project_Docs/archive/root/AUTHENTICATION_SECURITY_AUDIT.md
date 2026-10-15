# 🔐 SPEEDA.CA - COMPLETE AUTHENTICATION SYSTEM SECURITY AUDIT

**Report Date:** May 12, 2026  
**System:** Speeda.ca (Laravel 12 + Blade Multilingual Marketplace)  
**Scope:** Full authentication architecture review  
**Status:** PRODUCTION ENVIRONMENT ANALYSIS

---

## EXECUTIVE SUMMARY

The Speeda.ca authentication system has **MEDIUM SECURITY POSTURE** with several critical gaps that must be addressed before system maturity.

### Critical Findings
| # | Issue | Severity | Status |
|---|-------|----------|--------|
| 1 | Phone Number Duplication Across Tables | 🔴 HIGH | ⚠️ UNFIXED |
| 2 | Missing Database Constraint on users.mobile | 🔴 HIGH | ⚠️ UNFIXED |
| 3 | No Cross-Table Phone Uniqueness | 🟡 MEDIUM | ⚠️ UNFIXED |
| 4 | Phone Normalization Inconsistency | 🟡 MEDIUM | ✓ LOW RISK |
| 5 | WhatsApp Number Has No Constraint | 🟡 MEDIUM | ⚠️ UNFIXED |
| 6 | Race Condition in Registration | 🟡 MEDIUM | ⚠️ POSSIBLE |
| 7 | No Phone Update Audit Trail | 🟠 LOW | ⚠️ UNFIXED |

---

## PART 1: PHONE NUMBER UNIQUENESS ANALYSIS

### ✅ What's Configured Correctly

#### 1.1 Database Constraints - Service Providers
```
Table: service_providers
Column: phone
Constraint: UNIQUE + NULLABLE
Migration: 2025_10_08_000003_create_service_providers_table.php

Code:
  $table->string('phone')->unique()->nullable();
```
✅ **Status**: Properly configured  
✅ **Protection Level**: Database enforced  

#### 1.2 Registration Validation
```
File: app/Http/Requests/Auth/RegisterRequest.php
Rule: 'unique:service_providers,phone'
Custom Rule: CanadianPhoneNumber
Normalization: Yes (prepareForValidation)
```
✅ **Status**: Comprehensive validation  
✅ **Protection Level**: Application + Database  

#### 1.3 Phone Number Normalization
```
Location: RegisterRequest::normalizePhoneNumber()
Process: Happens in prepareForValidation() BEFORE validation
Formats Handled:
  • +1 514 555 1234 → +15145551234
  • (514) 555-1234  → +15145551234
  • 514-555-1234    → +15145551234
  • 5145551234      → +15145551234
  • 15145551234     → +15145551234

Deterministic: YES ✅
Consistent: YES ✅
```
✅ **Status**: Solid normalization logic  

#### 1.4 Profile Update Validation  
```
File: app/Http/Requests/UpdateServiceProviderProfileRequest.php
Rule: Rule::unique('service_providers', 'phone')->ignore($serviceProviderId)
Authorization: Policy-based (via middleware)
Rate Limiting: 10 requests/minute
```
✅ **Status**: Update validation is safe  

---

### ❌ CRITICAL GAPS - Phone Number Duplication Risks

#### ISSUE #1: `users.mobile` Column NOT Protected
```
Table: users
Column: mobile
Type: string, nullable
Constraint: INDEX only (NO UNIQUE)
Duplication Risk: MULTIPLE users can have same mobile
```

**Risk Scenario**:
```
User 1 (client):   email: john@example.com,  mobile: +15145551234
User 2 (client):   email: jane@example.com,  mobile: +15145551234  ✅ ALLOWED (no constraint)
User 3 (provider): email: bob@example.com,   mobile: +15145551234  ✅ ALLOWED (different table)
```

**Impact**:
- Multiple clients can register with same phone
- Phone login is disabled for clients (LOW RISK)
- BUT: Confusion, support calls, account recovery issues
- Database integrity concerns

**Current Code Path**:
- RegisterRequest doesn't validate client phone (TASK-2 removed it)
- Clients register with: name, email, password ONLY
- Phone field is hidden in client registration flow
- ✅ Unintended mitigation: clients don't enter phone during registration

**Verdict**: 🟡 MEDIUM RISK (low immediate threat, high long-term concern)

---

#### ISSUE #2: No Cross-Table Phone Uniqueness
```
Scenario:
  Provider A:  service_providers.phone = +15145551234  ✅
  Client X:    users.mobile = +15145551234             ✅ (allowed - different table)

Problem: No database-level constraint preventing this
Solution: Composite unique constraint across both tables OR application-level validation
```

**Current Safeguard**: 
- Clients don't enter phone at registration ✅ TASK-2
- Phone login only for providers ✅
- **But**: If later you enable client phone registration, this gap becomes critical

**Verdict**: 🟡 MEDIUM RISK (dormant, but dangerous if client phone features added)

---

#### ISSUE #3: WhatsApp Number Not Unique
```
Table: service_providers
Column: whatsapp_number
Type: string, nullable
Constraint: NONE
Duplication Risk: Multiple providers can have same WhatsApp
```

**Code**:
```php
$table->string('whatsapp_number')->nullable();
// NO unique constraint
```

**Validation** (UpdateServiceProviderProfileRequest):
```php
'whatsapp_number' => ['nullable', 'string', 'regex:/^[+]?[0-9\-\s]{10,20}$/']
// No unique validation rule
```

**Risk Level**: 🟠 LOW-MEDIUM (WhatsApp isn't used for authentication, only contact)  
**Verdict**: Acceptable but inconsistent with phone design

---

### 🔄 Phone Normalization Consistency Issues

#### ISSUE #4: Login vs Registration Normalization Mismatch

**Registration** (`RegisterRequest::prepareForValidation()`):
```php
protected function prepareForValidation(): void
{
    if ($this->has('mobile')) {
        $this->merge([
            'mobile' => $this->normalizePhoneNumber($this->input('mobile'))
        ]);
    }
}

private function normalizePhoneNumber(?string $phone): ?string
{
    // Removes all non-digits except +
    $cleaned = preg_replace('/[^0-9+]/', '', $phone);
    
    // Remove leading 1 if not +1
    $digits = preg_replace('/\D/', '', $cleaned);
    if (strlen($digits) === 11 && $digits[0] === '1') {
        $digits = substr($digits, 1);
    }
    
    // Return +1 + 10 digits
    if (strlen($digits) === 10) {
        return '+1' . $digits;
    }
    return $phone;
}
```

**Login** (`LoginRequest::authenticate()`):
```php
public function authenticate(): void
{
    $loginField = $this->input('login');  // ⚠️ NO NORMALIZATION
    
    if ($loginType === 'mobile') {
        $serviceProvider = \App\Models\ServiceProvider::where('phone', $loginField)->first();
        // Looks up with RAW input, not normalized
    }
}
```

**Risk Scenario**:
```
User registers with: "514-555-1234"
  → Normalized to: +15145551234 (stored in DB)

User tries to login with: "514-555-1234"
  → NOT normalized in login request
  → Query: WHERE phone = '514-555-1234' (raw)
  → Result: NOT FOUND ❌
  → Login fails even though phone is valid
```

**Current Status**: ⚠️ UNFIXED - This is a BUG waiting to happen

**Verdict**: 🟡 MEDIUM RISK - Login by phone will fail with formatted numbers

---

### ⏱️ Race Condition Analysis

#### ISSUE #5: Concurrent Registration Race Condition

**Window**: Between validation and database insert

**Scenario**:
```
Thread 1: POST /register with phone=+15145551234
Thread 2: POST /register with phone=514-555-1234 (different format, same number)

Both normalize to: +15145551234
Both pass validation: unique check ✅
Thread 1: Insert → SUCCESS ✅
Thread 2: Insert → DUPLICATE KEY ERROR ❌
```

**Laravel Protection**:
```php
// In AuthService::registerUser()
return DB::transaction(function () use ($validatedData) {
    $user = $this->createUser($validatedData);
    if ($user->role === 'service_provider') {
        $this->setupServiceProvider($user, $validatedData);
    }
    return $user;
});
```

✅ **Transaction Used**: Prevents partial inserts  
⚠️ **But**: Unique constraint violation will still throw exception

**Current Behavior**:
- Validation passes (unique check runs before insert)
- If race condition occurs, database constraint catches it
- Exception bubbles up, unhandled
- User sees generic error message

**Verdict**: 🟡 MEDIUM RISK - Not likely but possible under high load

---

## PART 2: AUTHENTICATION FLOW SECURITY

### 📋 Registration Flow

#### Flow Diagram
```
1. GET /register
   ↓ Show form with role selector
   
2. POST /register with data
   ↓ RegisterRequest::validate()
     - Phone normalization
     - Canadian format check
     - Unique constraint check
   
3. AuthService::registerUser()
   ↓ DB transaction
     - createUser() → users table
     - setupServiceProvider() → service_providers table if provider role
   
4. Meta Pixel tracking (async, silent failure)
5. redirect → /service-provider/profile or /locations
```

#### Phone Entry Points
| Role | Phone Required | Field | Validation |
|------|---------------|-------|-----------|
| Client | NO | Hidden | None |
| Provider | YES | mobile | CanadianPhoneNumber + unique |

**Security Assessment**:
- ✅ Proper validation rules
- ✅ Normalization before validation
- ✅ Transaction-based consistency
- ⚠️ Login normalization missing
- ⚠️ No audit logging of registrations

---

### 🔐 Login Flow

```
1. GET /login
   ↓ Show role selector + login field
   
2. POST /login with login (email or phone) + role
   ↓ LoginRequest::authenticate()
   
3. Check login field type:
   IF email:
     - Normal Auth::attempt(['email' => $email, 'password' => $password])
   IF phone:
     - ServiceProvider::where('phone', $phone)->first()  ⚠️ NO NORMALIZATION
     - Auth::attempt(['email' => $user->email, 'password' => $password])
   
4. Role verification:
   - Check Auth::user()->role matches selected role
   - If mismatch: logout and reject
   
5. Redirect to role-specific dashboard
```

#### Phone Login Issues
- ⚠️ NO NORMALIZATION in LoginRequest
- ⚠️ User enters "514-555-1234", stored as "+15145551234"
- ❌ Query fails: `WHERE phone = '514-555-1234'`

**Test Case That Would Fail**:
```php
// Register
POST /register
  mobile: "514-555-1234"
  role: "service_provider"
  // Normalized to: +15145551234 in DB

// Try to login
POST /login
  login: "514-555-1234"
  role: "service_provider"
  // NOT normalized, query fails
  // Error: "No account found with this mobile number"
```

---

### 👤 Admin Backdoor

Found in `LoginRequest::authenticate()`:
```php
if ($selectedRole === 'client' && 
    strtolower(trim($loginField)) === 'admin' && 
    $password === 'admin12345678910') {
    
    // Find or create admin user
    // Login as admin
}
```

**Security Assessment**:
- ⚠️ Hardcoded credentials in code
- ⚠️ Accessed via "client" role selection
- ⚠️ No 2FA or verification
- ⚠️ Password in plaintext in source
- ⚠️ No audit trail

**Risk**: 🔴 HIGH - Development backdoor in production

**Recommendation**: Remove before production deployment

---

## PART 3: MULTILINGUAL & UX SECURITY

### Language Support Status
- ✅ English (en)
- ✅ Arabic (ar)
- ✅ French (fr)

### Validated in All Languages
- ✅ Phone validation error messages
- ✅ Unique constraint errors
- ✅ Canadian format requirements
- ✅ Login/registration flows

### Potential Issues
- ⚠️ Mobile normalization happens before i18n strings loaded
- ⚠️ If phone is normalized in a non-Latin locale, could cause confusion
- ✅ Normalization is locale-agnostic (just removes non-digits)

---

## PART 4: ROLE-BASED SECURITY

### Client Role
```
Phone: Optional during registration (hidden)
       Client can't login with phone
       Client can't call provider phone reveal API
```

### Service Provider Role
```
Phone: Required during registration (validated)
       Provider CAN login with phone
       Provider phone shown on public profile
       Phone used for WhatsApp contact reveal
```

### Admin Role
```
Phone: Not used in auth
       Has full database access
       Can edit ANY provider's phone without constraints
```

---

## PART 5: SESSION & RATE LIMITING

### Rate Limiting
```
Login: Unlimited (⚠️ NO THROTTLE)
Registration: Unlimited (⚠️ NO THROTTLE)
Profile Update: 10 requests/minute ✅
Contact Reveal: 5 requests/minute ✅
```

**Risk**: 🔴 HIGH - Brute force attacks possible

**Recommendations**:
```php
// Add to auth routes
->middleware('throttle:5,1')  // 5 attempts per minute
```

---

### Session Configuration
```php
// config/auth.php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
]

// Session secure attributes
Session lifetime: Default (2 hours) ✅
CSRF protection: Enabled ✅
```

**Assessment**: ✅ Secure defaults

---

### Middleware Protection
```
auth Middleware: ✅ Guards authenticated routes
RoleMiddleware: ✅ Role-based access control
CheckUserStatus: ✅ Validates user is active (is_active=true)
```

---

## PART 6: DATABASE INTEGRITY ANALYSIS

### Foreign Keys
```
users.id → PK
service_providers.user_id → FK (UNIQUE)

Users can have:
  - 0 service provider records (clients) ✅
  - 1 service provider record (providers) ✅
  - Multiple roles manually set (⚠️ should be enum-strict)
```

### Soft Deletes
```
Users: ✅ Soft deletes enabled (deleted_at)
ServiceProviders: ✅ Soft deletes enabled
```

**Risk**: When user is soft-deleted, serviceProvider is cascade-deleted  
**Assessment**: ✅ Cascading deletion is appropriate

---

## PART 7: SOCIAL LOGIN STATUS

### Current Implementation
```
Google Login: "Coming Soon" (placeholder only)
Facebook Login: "Coming Soon" (placeholder only)
```

**Code Location**:
```
Button HTML: resources/views/auth/register.blade.php
JavaScript: Shows toast message "Coming Soon"
No actual OAuth implementation
```

**Security Assessment**: ✅ No vulnerabilities (not implemented)

---

## PART 8: PASSWORD SECURITY

### Password Hashing
```
Algorithm: Bcrypt (via Password::defaults())
Cost: Default (10) ✅
Validation: Min 8 chars, mixed case, symbols required ✅
```

### Password Reset Flow
```
Route: password.request → form
       password.update → process
DB: password_reset_tokens table ✅
Token TTL: 60 minutes ✅
```

**Assessment**: ✅ Industry standard

---

## PART 9: EMAIL VERIFICATION

### Current Flow
```
Registration: ✅ Verified email generated (Registered event)
Verification Email: Sent via configured mailer
Verification Link: Has token, expires
```

**Code**:
```php
'email_verified_at' => $data['role'] === 'service_provider' ? now() : null,
```

**Assessment**: 
- ✅ Providers verified immediately (TASK-2)
- ✅ Clients can verify email (optional)

---

## SECURITY ISSUES SUMMARY TABLE

| # | Issue | Component | Severity | Impact | Fix Time |
|---|-------|-----------|----------|--------|----------|
| 1 | users.mobile not unique | Database | HIGH | Duplicates allowed | 30 min |
| 2 | No cross-table uniqueness | Architecture | HIGH | Potential conflicts | 1 hour |
| 3 | WhatsApp no constraint | Database | MEDIUM | Duplicates allowed | 15 min |
| 4 | Phone normalization in login | LoginRequest | MEDIUM | Login fails | 20 min |
| 5 | No rate limiting on auth | Middleware | HIGH | Brute force possible | 10 min |
| 6 | Admin backdoor in code | LoginRequest | HIGH | Dev credentials in prod | 5 min |
| 7 | Race condition possible | AuthService | MEDIUM | Concurrent registrations | 30 min |
| 8 | No phone update audit | Logging | LOW | No accountability | 1 hour |
| 9 | WhatsApp format flexible | Validation | LOW | Format inconsistency | 20 min |

---

## PRIORITY FIXES (PRODUCTION-SAFE)

### 🔴 CRITICAL - Do Immediately

#### Fix 1: Remove Admin Backdoor (5 minutes)
```php
// File: app/Http/Requests/Auth/LoginRequest.php
// DELETE Lines 60-93 (admin check)
```

#### Fix 2: Add Rate Limiting (10 minutes)
```php
// File: routes/auth.php
Route::post('/register', [...])
    ->middleware('throttle:5,1');  // 5 per minute

Route::post('/login', [...])
    ->middleware('throttle:5,1');  // 5 per minute
```

#### Fix 3: Add Phone Normalization to Login (20 minutes)
```php
// File: app/Http/Requests/Auth/LoginRequest.php
// Add normalizePhoneNumber() method (copy from RegisterRequest)
// Apply in authenticate() method before lookup
```

### 🟡 IMPORTANT - Do This Sprint

#### Fix 4: Add Unique Constraint on users.mobile (30 minutes)
```php
// File: database/migrations/XXXX_add_unique_to_users_mobile.php
Schema::table('users', function (Blueprint $table) {
    $table->unique('mobile');
});
```

#### Fix 5: Add Unique Constraint on WhatsApp (15 minutes)
```php
// File: database/migrations/XXXX_add_unique_to_whatsapp.php
Schema::table('service_providers', function (Blueprint $table) {
    $table->unique('whatsapp_number');
});
```

#### Fix 6: Add Phone Update Validation Enhancement (20 minutes)
```php
// File: app/Http/Requests/UpdateServiceProviderProfileRequest.php
// Verify phone normalization happens before validation
'phone' => [
    'required',
    'string',
    'min:10',
    'max:20',
    new CanadianPhoneNumber(),  // Add rule
    Rule::unique('service_providers', 'phone')->ignore($serviceProviderId)
],
```

### 🟠 NICE TO HAVE - Future Improvements

- Implement phone verification (SMS OTP)
- Add phone change email notifications
- Implement 2FA for admin accounts
- Add audit logging for phone changes
- Create phone login throttle history
- Implement cross-table phone validation

---

## TESTING SCENARIOS

### Test 1: Duplicate Phone Registration ❌ WILL FAIL
```
Step 1: Provider A registers with "514-555-1234"
  → Stored as "+15145551234"

Step 2: Provider B tries to register with "(514) 555-1234"
  → Normalized to "+15145551234"
  → Validation finds duplicate
  → ERROR: "Phone already exists" ✅ WORKS

Step 3: Try concurrent registrations with same phone
  → Both normalize successfully
  → Both pass validation (both check same instant)
  → One insert succeeds
  → One gets UNIQUE constraint violation ⚠️ UNHANDLED
```

### Test 2: Phone Login Failure ❌ CURRENTLY BROKEN
```
Provider registers: "514-555-1234" → stored as "+15145551234"
Provider tries login: "514-555-1234"
  → Not normalized in LoginRequest
  → Query: WHERE phone = '514-555-1234'
  → Result: NULL (doesn't match "+15145551234")
  → Error: "No account found" ❌ LOGIN FAILS
```

### Test 3: Client Phone Registration 🟢 WORKS AS INTENDED
```
Client registers with email/password only
  → Phone field hidden/ignored
  → No phone stored in users.mobile
  → No conflicts possible ✅
```

---

## ARCHITECTURE RECOMMENDATIONS

### Short Term (This Sprint)
1. Remove admin backdoor
2. Add rate limiting to auth routes
3. Fix phone normalization in login
4. Add unique constraint to users.mobile
5. Add unique constraint to whatsapp_number

### Medium Term (Next Sprint)
1. Add phone verification via SMS OTP
2. Implement phone change notifications
3. Add audit logging for phone updates
4. Create admin phone change restrictions
5. Add comprehensive authentication tests

### Long Term (Future Roadmap)
1. Implement 2FA for providers
2. Add phone-based account recovery
3. Implement account linking (multiple roles)
4. Add API authentication (Sanctum)
5. Implement OAuth2 for social login

---

## COMPLIANCE NOTES

### Data Privacy
- ✅ Phone hashing in Facebook CAPI
- ✅ Email verification required
- ⚠️ No GDPR data export implemented
- ⚠️ No right-to-be-forgotten implemented

### Security Best Practices
- ✅ CSRF protection enabled
- ✅ HTTPS recommended
- ✅ Password requirements enforced
- ✅ Session timeout implemented
- ⚠️ No 2FA available
- ⚠️ No rate limiting on auth
- ⚠️ No login audit trail

---

## RISK MATRIX

```
Risk = Likelihood × Impact

HIGH RISK (Fix Now):
  - Admin backdoor: Likelihood: MEDIUM, Impact: HIGH → Do First
  - No rate limiting: Likelihood: HIGH, Impact: MEDIUM → Do Now
  
MEDIUM RISK (Fix This Sprint):
  - Phone duplication: Likelihood: LOW, Impact: MEDIUM
  - Login normalization: Likelihood: MEDIUM, Impact: MEDIUM
  
LOW RISK (Fix Later):
  - WhatsApp constraint: Likelihood: LOW, Impact: LOW
  - Race conditions: Likelihood: LOW, Impact: MEDIUM
```

---

## CONCLUSION

**Overall Assessment**: 🟡 MEDIUM SECURITY

**Current State**: Safe for MVP, needs hardening for production.

**Timeline to Production-Ready**:
- Critical fixes: 1 hour
- Important fixes: 3 hours
- Full recommendations: 2 sprints

**Recommendation**: Address all fixes in "Fix Now" and "Do This Sprint" sections before full production release. Current system is suitable for beta testing with known limitations.

---

**Report Prepared By**: GitHub Copilot  
**Analysis Date**: May 12, 2026  
**Next Review**: After fixes implemented  
**Document Version**: 1.0
