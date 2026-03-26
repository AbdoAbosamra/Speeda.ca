# Service Provider System - Manual QA Testing Checklist

**Project:** Speeda Service Provider Platform  
**Test Date:** _____________  
**Tester Name:** _____________  
**Environment:** [ ] Development [ ] Staging [ ] Production

---

## Pre-Testing Setup

### Test Accounts Required:
1. **Service Provider Account 1**
   - Email: provider1@test.com
   - Password: ________________
   - Has service provider profile: Yes
   - Provider ID: ________________

2. **Service Provider Account 2**
   - Email: provider2@test.com
   - Password: ________________
   - Has service provider profile: Yes
   - Provider ID: ________________

3. **Client Account** (optional, for views counter testing)
   - Email: client@test.com
   - Password: ________________
   - Has service provider profile: No

### Browser Compatibility Testing:
Test on at least 2 browsers from this list:
- [ ] Chrome/Edge
- [ ] Firefox
- [ ] Safari
- [ ] Mobile Chrome (iOS/Android)

---

## SECTION 1: Profile Access Control

### Test 1.1: Owner Can Access Own Profile
**Steps:**
1. Login as Provider 1
2. Navigate to your service provider profile
   - URL: `/service-providers/{your-provider-id}`
3. Page should load successfully

**Expected Result:**
- ✅ Profile page loads without errors
- ✅ All profile information visible
- ✅ Edit form is visible (owner-only section)
- ✅ No "Access Denied" message

**Actual Result:** [ ] Pass [ ] Fail  
**Notes:** ________________________________

---

### Test 1.2: Non-Owner Cannot Access Other's Profile
**Steps:**
1. Login as Provider 1
2. Try to access Provider 2's profile URL
   - URL: `/service-providers/{provider-2-id}`

**Expected Result:**
- ✅ Unauthorized error page shown (NOT Laravel exception page)
- ✅ Message: "Access Denied" or similar
- ✅ Button to return to provider listing
- ✅ No profile information visible

**Actual Result:** [ ] Pass [ ] Fail  
**Notes:** ________________________________

---

### Test 1.3: Guest Redirected to Login
**Steps:**
1. Logout completely
2. Try to access any service provider profile URL
   - URL: `/service-providers/{any-provider-id}`

**Expected Result:**
- ✅ Redirected to login page
- ✅ Flash message: "Please login to access this page" or similar
- ✅ After login, should redirect back to intended profile (if owner)

**Actual Result:** [ ] Pass [ ] Fail  
**Notes:** ________________________________

---

## SECTION 2: Profile Editing & Save Persistence

### Test 2.1: Business Name Update
**Steps:**
1. Login as Provider 1
2. Go to your profile page
3. Locate edit form section
4. Change "Business Name" field
   - Original: ________________
   - New: ________________
5. Click "Save changes"
6. Refresh page or navigate away and back

**Expected Result:**
- ✅ Success notification shown (green card, top-right)
- ✅ Page redirects to profile view
- ✅ New business name is displayed
- ✅ After refresh, new name still shows (persisted)

**Actual Result:** [ ] Pass [ ] Fail  
**Notes:** ________________________________

---

### Test 2.2: Profession/Category Dropdown
**Steps:**
1. Open edit form
2. Locate "Profession" dropdown
3. Click dropdown to expand options
4. Verify only parent categories shown (e.g., Plumbing, Electrical, NOT sub-categories)
5. Select a different profession
6. Save changes

**Expected Result:**
- ✅ Dropdown shows only parent categories (parent_id = 1)
- ✅ No child/subcategories visible
- ✅ Current profession is pre-selected
- ✅ After save, new profession persists
- ✅ Profile displays updated profession

**Actual Result:** [ ] Pass [ ] Fail  
**Categories Shown:** ________________________________  
**Notes:** ________________________________

---

### Test 2.3: Phone Number Update
**Steps:**
1. Update "Phone" field
   - New value: ________________
2. Save changes

**Expected Result:**
- ✅ Phone number persists after save
- ✅ Displayed correctly on profile
- ✅ No validation error (if format valid)

**Actual Result:** [ ] Pass [ ] Fail  
**Notes:** ________________________________

---

### Test 2.4: WhatsApp Number Field (NEW)
**Steps:**
1. Locate "WhatsApp Number" field (should be below Phone)
2. Enter WhatsApp number with country code
   - Example: +1234567890
3. Read hint text below field
4. Save changes
5. Verify WhatsApp button/link on profile

**Expected Result:**
- ✅ WhatsApp field is visible and optional
- ✅ Hint text explains format
- ✅ Number saves successfully
- ✅ WhatsApp quick action button uses this number
- ✅ If empty, button falls back to phone number

**Test Variations:**
- [ ] Valid format: +1234567890 → Saves successfully
- [ ] Without +: 1234567890 → Saves successfully
- [ ] With spaces: +1 234 567 8900 → Saves successfully
- [ ] With hyphens: +1-234-567-8900 → Saves successfully
- [ ] Invalid chars: abc123!@# → Validation error shown

**Actual Result:** [ ] Pass [ ] Fail  
**Notes:** ________________________________

---

### Test 2.5: Location Dropdown
**Steps:**
1. Open "Location" dropdown
2. Select a different location
3. Save changes

**Expected Result:**
- ✅ All available locations shown in dropdown
- ✅ Current location pre-selected
- ✅ New location persists after save

**Actual Result:** [ ] Pass [ ] Fail  
**Notes:** ________________________________

---

### Test 2.6: Description/Bio Update
**Steps:**
1. Update "Description" textarea
   - Add at least 100 characters
2. Save changes

**Expected Result:**
- ✅ Description persists
- ✅ Displayed in profile view section
- ✅ Line breaks preserved

**Actual Result:** [ ] Pass [ ] Fail  
**Notes:** ________________________________

---

### Test 2.7: Services Offered (Comma-Separated)
**Steps:**
1. Enter services in "Services Offered" field
   - Example: "Plumbing, Repairs, Installation, Emergency Services"
2. Save changes
3. Check profile view

**Expected Result:**
- ✅ Services saved successfully
- ✅ Displayed as individual badges/pills on profile
- ✅ Each service is a separate item (not one long string)

**Actual Result:** [ ] Pass [ ] Fail  
**Displayed as:** ________________________________  
**Notes:** ________________________________

---

### Test 2.8: Experience Years & Hourly Rate
**Steps:**
1. Update "Years of Experience" (integer)
   - Value: ________________
2. Update "Hourly Rate" (decimal)
   - Value: ________________
3. Save changes

**Expected Result:**
- ✅ Both fields persist correctly
- ✅ Displayed with proper formatting
- ✅ Hourly rate shows currency symbol and decimals

**Actual Result:** [ ] Pass [ ] Fail  
**Notes:** ________________________________

---

## SECTION 3: File Uploads

### Test 3.1: Profile Image Upload
**Steps:**
1. Click "Profile Image" file input
2. Select an image file
   - Format: JPG, PNG, or WebP
   - Size: Under 5MB
3. Submit form

**Expected Result:**
- ✅ Image uploads successfully
- ✅ Success notification shown
- ✅ Profile image updated in header
- ✅ Image resized to 400x400px
- ✅ Old image deleted from storage (if existed)

**Test Variations:**
- [ ] JPG file → Uploads successfully
- [ ] PNG file → Uploads successfully
- [ ] Large file (>5MB) → Validation error shown
- [ ] Non-image file (PDF) → Validation error shown

**Actual Result:** [ ] Pass [ ] Fail  
**Notes:** ________________________________

---

### Test 3.2: Certification Upload (PDF)
**Steps:**
1. Locate "Certification" file input
2. Select a PDF file (under 10MB)
3. Save changes
4. Check profile for certification status

**Expected Result:**
- ✅ PDF uploads successfully
- ✅ Badge shows "Certification Uploaded"
- ✅ Download link appears (not image preview)
- ✅ Link text: "Download Certificate PDF" or similar
- ✅ Clicking link downloads the PDF
- ✅ `is_certified` flag set to true

**Actual Result:** [ ] Pass [ ] Fail  
**Notes:** ________________________________

---

### Test 3.3: Certification Upload (Image)
**Steps:**
1. Upload image certification (JPG/PNG)
2. Save changes
3. Check profile

**Expected Result:**
- ✅ Image uploads successfully
- ✅ Image preview displayed (not download link)
- ✅ Badge shows "Certification Uploaded"
- ✅ Image stored in certifications folder

**Actual Result:** [ ] Pass [ ] Fail  
**Notes:** ________________________________

---

### Test 3.4: Certification Re-upload
**Steps:**
1. Already have certification uploaded
2. Upload a new certification file (different file)
3. Save changes
4. Check server storage folder

**Expected Result:**
- ✅ New file uploads successfully
- ✅ Old certification file deleted from storage
- ✅ New file path stored in database
- ✅ Only ONE certification file exists per provider

**Actual Result:** [ ] Pass [ ] Fail  
**Notes:** ________________________________

---

## SECTION 4: Error Handling & Validation

### Test 4.1: Validation Errors Display
**Steps:**
1. Open edit form
2. Clear "Business Name" field (required field)
3. Submit form

**Expected Result:**
- ✅ Notification card appears (top-right)
- ✅ Red/error color scheme
- ✅ Message: "Business name is required" or similar
- ✅ NOT a Laravel exception page
- ✅ Form input values preserved (not lost)
- ✅ Notification auto-dismisses after 5 seconds OR has close button

**Actual Result:** [ ] Pass [ ] Fail  
**Notes:** ________________________________

---

### Test 4.2: Invalid WhatsApp Format
**Steps:**
1. Enter invalid WhatsApp number
   - Example: "abcd1234!@#"
2. Submit form

**Expected Result:**
- ✅ Validation error shown in notification card
- ✅ Message: "Please enter a valid WhatsApp number format" or similar
- ✅ Input highlighted or indicated as invalid

**Actual Result:** [ ] Pass [ ] Fail  
**Notes:** ________________________________

---

### Test 4.3: File Size Validation
**Steps:**
1. Try to upload profile image > 5MB
2. Submit form

**Expected Result:**
- ✅ Validation error: "Profile image cannot exceed 5MB"
- ✅ File not uploaded
- ✅ Notification card displayed

**Actual Result:** [ ] Pass [ ] Fail  
**Notes:** ________________________________

---

### Test 4.4: CSRF Token Expiration (Session Expired)
**Steps:**
1. Open edit form
2. Wait for session to expire (or manually delete session cookie)
3. Fill form and submit

**Expected Result:**
- ✅ Error notification shown
- ✅ Message: "Your session has expired. Please refresh the page and try again."
- ✅ NOT a 419 Laravel error page
- ✅ User-friendly message

**Actual Result:** [ ] Pass [ ] Fail  
**Notes:** ________________________________

---

### Test 4.5: Database Error Handling
**Steps:**
1. Try to create duplicate phone number (if unique constraint exists)
   OR
2. Simulate database error (disconnect DB during save)

**Expected Result:**
- ✅ Generic user-friendly error shown
- ✅ NOT SQL error or Laravel stack trace
- ✅ Notification card with error icon
- ✅ Message: "An error occurred" or similar
- ✅ Error logged to Laravel log file

**Actual Result:** [ ] Pass [ ] Fail  
**Notes:** ________________________________

---

### Test 4.6: Success Notifications
**Steps:**
1. Successfully save profile changes

**Expected Result:**
- ✅ Green success notification appears
- ✅ Message: "Profile updated successfully!"
- ✅ Notification positioned top-right
- ✅ Auto-dismiss after 5 seconds
- ✅ Has close button
- ✅ Smooth fade-in/fade-out animation

**Actual Result:** [ ] Pass [ ] Fail  
**Notes:** ________________________________

---

## SECTION 5: Views Counter

### Test 5.1: Owner Views Own Profile (No Increment)
**Steps:**
1. Login as Provider 1
2. Note current views count: ________________
3. View your own profile
4. Refresh page multiple times (3-5 times)
5. Check views count again

**Expected Result:**
- ✅ Views count does NOT increase
- ✅ Counter remains the same after multiple refreshes

**Actual Result:** [ ] Pass [ ] Fail  
**Initial Views:** ________ **Final Views:** ________  
**Notes:** ________________________________

---

### Test 5.2: Other User Views Profile (Increment)
**Steps:**
1. Login as Provider 2 (or client account)
2. Note Provider 1's views count: ________________
3. View Provider 1's profile (will see unauthorized page, but view should count)

**Note:** This test may need adjustment based on access control. If non-owners can't view at all, views won't increment. Test with client role if available.

**Expected Result:**
- ✅ Views count increases by 1
- ✅ Counter updates after each unique view

**Actual Result:** [ ] Pass [ ] Fail [ ] N/A  
**Notes:** ________________________________

---

## SECTION 6: UI/UX Quality

### Test 6.1: Notification Card Design
**Steps:**
1. Trigger a validation error
2. Inspect notification card

**Expected Result:**
- ✅ Card positioned at top-right
- ✅ Has drop shadow/elevation
- ✅ Color-coded (red=error, green=success, yellow=warning)
- ✅ Icon appropriate for type (checkmark, exclamation, etc.)
- ✅ Text readable and properly sized
- ✅ Close button works
- ✅ Auto-dismiss animation smooth
- ✅ Does not block important content

**Actual Result:** [ ] Pass [ ] Fail  
**Notes:** ________________________________

---

### Test 6.2: Form Layout & Usability
**Steps:**
1. Review entire edit form

**Expected Result:**
- ✅ All labels clearly visible
- ✅ Input fields properly sized
- ✅ Dropdowns show all options
- ✅ File inputs have proper accept attributes
- ✅ Hint text helpful and not intrusive
- ✅ Required fields marked (if any)
- ✅ Save/Cancel buttons easy to find
- ✅ No overlapping elements

**Actual Result:** [ ] Pass [ ] Fail  
**Notes:** ________________________________

---

### Test 6.3: Mobile Responsiveness
**Steps:**
1. Open profile page on mobile device or resize browser to mobile width (375px)

**Expected Result:**
- ✅ Page layout adjusts to screen size
- ✅ Notification card visible and properly positioned
- ✅ Form inputs stack vertically
- ✅ Buttons accessible
- ✅ No horizontal scrolling
- ✅ Text readable without zooming

**Actual Result:** [ ] Pass [ ] Fail  
**Notes:** ________________________________

---

## SECTION 7: Security & Authorization

### Test 7.1: Authorization Enforcement
**Steps:**
1. Login as Provider 2
2. Try to manually submit form to update Provider 1's profile
   - Use browser dev tools to modify form action URL
   - OR directly POST to `/service-providers/profile/{provider-1-id}`

**Expected Result:**
- ✅ Request rejected (403 Forbidden)
- ✅ No changes applied to Provider 1's profile
- ✅ Error notification or page shown

**Actual Result:** [ ] Pass [ ] Fail  
**Notes:** ________________________________

---

### Test 7.2: CSRF Protection
**Steps:**
1. Open profile edit form
2. Open browser dev tools
3. Remove or modify the `_token` field in form
4. Submit form

**Expected Result:**
- ✅ Request rejected
- ✅ Session expired message OR CSRF error
- ✅ No data saved

**Actual Result:** [ ] Pass [ ] Fail  
**Notes:** ________________________________

---

### Test 7.3: File Upload Security
**Steps:**
1. Try to upload PHP file as profile image (rename .php to .jpg)
2. Submit form

**Expected Result:**
- ✅ File rejected (MIME type validation)
- ✅ Error message shown
- ✅ File not stored on server

**Actual Result:** [ ] Pass [ ] Fail  
**Notes:** ________________________________

---

## SECTION 8: Performance & Reliability

### Test 8.1: Page Load Time
**Steps:**
1. Open profile page
2. Check browser dev tools Network tab
3. Note page load time

**Expected Result:**
- ✅ Page loads in under 3 seconds (on decent connection)
- ✅ No excessive database queries (check Laravel Debugbar if available)
- ✅ Images load efficiently

**Actual Result:** [ ] Pass [ ] Fail  
**Load Time:** ________ seconds  
**Notes:** ________________________________

---

### Test 8.2: Concurrent Edits (Race Condition)
**Steps:**
1. Open profile edit form in 2 browser tabs
2. Make different changes in each tab
3. Submit Tab 1
4. Immediately submit Tab 2

**Expected Result:**
- ✅ Both saves complete without error
- ✅ Last save wins (Tab 2's data persists)
- ✅ No database deadlock or crash

**Actual Result:** [ ] Pass [ ] Fail  
**Notes:** ________________________________

---

## SECTION 9: Data Integrity

### Test 9.1: Transaction Rollback
**Steps:**
1. Simulate error during save (disconnect DB mid-save if possible)
   OR
2. Provide invalid category_id that passes validation but fails on foreign key constraint

**Expected Result:**
- ✅ All changes rolled back (nothing saved)
- ✅ Profile data unchanged
- ✅ User-friendly error message

**Actual Result:** [ ] Pass [ ] Fail  
**Notes:** ________________________________

---

### Test 9.2: Old Files Deletion
**Steps:**
1. Upload profile image
2. Note filename: ________________
3. Upload different profile image
4. Check server storage folder

**Expected Result:**
- ✅ Old image file deleted
- ✅ New image file exists
- ✅ Only ONE profile image per provider in storage

**Actual Result:** [ ] Pass [ ] Fail  
**Notes:** ________________________________

---

## SECTION 10: Cross-Browser Compatibility

### Test 10.1: Chrome/Edge
- [ ] All features work
- [ ] Notifications display correctly
- [ ] File uploads work
- [ ] No console errors

**Issues Found:** ________________________________

---

### Test 10.2: Firefox
- [ ] All features work
- [ ] Notifications display correctly
- [ ] File uploads work
- [ ] No console errors

**Issues Found:** ________________________________

---

### Test 10.3: Safari (if available)
- [ ] All features work
- [ ] Notifications display correctly
- [ ] File uploads work
- [ ] No console errors

**Issues Found:** ________________________________

---

### Test 10.4: Mobile Browser
- [ ] All features work
- [ ] Notifications display correctly
- [ ] File uploads work
- [ ] Touch interactions smooth

**Issues Found:** ________________________________

---

## SUMMARY

### Overall Test Results

**Total Tests:** 50+  
**Passed:** ________  
**Failed:** ________  
**Not Applicable:** ________  

### Critical Issues Found (Priority 1):
1. ________________________________
2. ________________________________
3. ________________________________

### Major Issues Found (Priority 2):
1. ________________________________
2. ________________________________

### Minor Issues Found (Priority 3):
1. ________________________________
2. ________________________________

### Recommendations:
________________________________
________________________________
________________________________

### Sign-Off

**Tester Name:** ____________________  
**Date:** ____________________  
**Signature:** ____________________  

**Approved for Production:** [ ] Yes [ ] No (see critical issues)

**Notes:**
________________________________
________________________________
________________________________
