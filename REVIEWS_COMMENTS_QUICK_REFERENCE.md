# Reviews & Comments System - Quick Reference

## Deployment Checklist

Before going live, execute these commands:

```bash
# Run the new migrations
php artisan migrate

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
```

---

## API Endpoints

### Reviews (Client)

```http
# Get active reviews for a service provider
GET /service-providers/{serviceProviderProfile}/reviews

# View a single review
GET /reviews/{review}

# Create a new review (Authenticated, CLIENT role)
POST /reviews
Content-Type: application/json
{
  "service_provider_profile_id": 1,
  "booking_id": 5,              // Optional - auto-verifies
  "rating": 5,                  // 1-5 required
  "review_text": "Great service!", // Optional, min 10 chars
  "rating_breakdown": {}        // Optional, custom ratings
}

# Update own review (before approval)
PUT /reviews/{review}
{
  "rating": 4,                  // Optional
  "review_text": "Updated...",  // Optional
  "rating_breakdown": {}        // Optional
}

# Delete own review
DELETE /reviews/{review}
```

### Reviews (Admin)

```http
# List all reviews (with filters)
GET /admin/reviews?status=pending&rating=5&provider_id=1

# View review details
GET /admin/reviews/{review}

# Approve a review
POST /admin/reviews/{review}/approve

# Reject a review
POST /admin/reviews/{review}/reject
{
  "reason": "Inappropriate content"
}

# Feature a review
POST /admin/reviews/{review}/feature

# Unfeature a review
POST /admin/reviews/{review}/unfeature

# Delete a review
DELETE /admin/reviews/{review}
```

### Comments (Client)

```http
# Get active comments
GET /comments?commentable_type=App\Models\Review&commentable_id=5

# Create a comment (Authenticated)
POST /comments
{
  "commentable_type": "App\Models\Review",
  "commentable_id": 5,
  "content": "This is a comment"  // Min 5, max 500 chars
}

# Update own comment (before approval)
PUT /comments/{comment}
{
  "content": "Updated comment"
}

# Delete own comment
DELETE /comments/{comment}

# Flag comment (report inappropriate)
POST /comments/{comment}/flag
```

### Comments (Admin)

```http
# List all comments (with filters)
GET /admin/comments?status=pending&commentable_type=App\Models\Review

# View comment details
GET /admin/comments/{comment}

# Approve a comment
POST /admin/comments/{comment}/approve

# Reject a comment
POST /admin/comments/{comment}/reject
{
  "reason": "Spam"
}

# Flag a comment
POST /admin/comments/{comment}/flag

# Unflag a comment
POST /admin/comments/{comment}/unflag

# Delete a comment
DELETE /admin/comments/{comment}

# Restore a deleted comment
POST /admin/comments/{comment}/restore
```

---

## Database Schema

### service_provider_reviews table
```sql
- id (PK)
- service_provider_profile_id (FK)
- client_id (FK → users)
- booking_id (FK, nullable)
- rating (int 1-5)
- review_text (text)
- rating_breakdown (json)
- is_verified (boolean, default false)
- is_featured (boolean, default false)
- is_active (boolean, default false) ← CRITICAL: false = hidden
- admin_approved_by (FK → users, nullable)
- admin_approved_at (timestamp, nullable)
- created_at, updated_at
```

### comments table
```sql
- id (PK)
- commentable_type (string)
- commentable_id (bigint)
- user_id (FK → users)
- content (text)
- is_active (boolean, default false) ← CRITICAL: false = hidden
- is_flagged (boolean, default false)
- approved_by (FK → users, nullable)
- approved_at (timestamp, nullable)
- rejection_reason (string, nullable)
- created_at, updated_at, deleted_at (soft delete)
```

---

## Translation Keys

### Reviews
```php
// Client-facing
trans('reviews.write_first_review')              // "Be the first to review..."
trans('reviews.review_submitted_pending_approval') // "Thank you! Your review..."
trans('reviews.my_reviews')                       // "My Reviews"
trans('reviews.no_reviews')                       // "No reviews yet"

// Admin
trans('admin.review_approved_successfully')       // "Review approved successfully"
trans('admin.review_rejected_successfully')       // "Review rejected successfully"
trans('admin.review_featured_successfully')       // "Review featured successfully"
trans('admin.review_deleted_successfully')        // "Review deleted successfully"
```

### Comments
```php
// Client-facing
trans('comments.comment_submitted_pending_approval') // "Thank you! Your comment..."
trans('comments.my_comments')                       // "My Comments"
trans('comments.no_comments')                       // "No comments yet"

// Admin
trans('admin.comment_approved_successfully')        // "Comment approved successfully"
trans('admin.comment_rejected_successfully')        // "Comment rejected successfully"
trans('admin.comment_flagged_successfully')         // "Comment flagged successfully"
trans('admin.comment_deleted_successfully')         // "Comment deleted successfully"
```

---

## Query Examples

### Get active reviews for a provider
```php
$reviews = $provider->activeReviews()
    ->with(['client', 'approvedBy'])
    ->orderByDesc('created_at')
    ->get();
```

### Get pending reviews (admin)
```php
$pending = Review::where('is_active', false)
    ->whereNull('admin_approved_by')
    ->with(['client', 'serviceProviderProfile'])
    ->orderByDesc('created_at')
    ->get();
```

### Get active comments on a review
```php
$comments = $review->activeComments()
    ->with(['user'])
    ->get();
```

### Get flagged comments for review
```php
$flagged = Comment::where('commentable_type', Review::class)
    ->flagged()
    ->with(['user', 'commentable'])
    ->get();
```

---

## Validation Rules

### Review Validation
```php
'service_provider_profile_id' => 'required|integer|exists:service_provider_profiles,id',
'booking_id' => 'nullable|integer|exists:bookings,id',
'rating' => 'required|integer|min:1|max:5',
'review_text' => 'nullable|string|min:10|max:1000',
'rating_breakdown' => 'nullable|array',
```

### Comment Validation
```php
'commentable_type' => 'required|string|in:App\Models\Review',
'commentable_id' => 'required|integer',
'content' => 'required|string|min:5|max:500',
```

---

## Error Responses

### Unauthorized (403)
```json
{
  "message": "You can only edit your own reviews"
}
```

### Validation Failed (422)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "rating": ["The rating must be between 1 and 5"]
  }
}
```

### Not Found (404)
```json
{
  "message": "This review is not available for viewing"
}
```

---

## Admin Panel Features

### Review Management
- [x] View all reviews (pending + approved)
- [x] Filter by status (pending, active)
- [x] Filter by rating (1-5)
- [x] Filter by provider
- [x] Approve with one click
- [x] Reject with reason
- [x] Feature/unfeature reviews
- [x] Delete reviews
- [x] View full review details

### Comment Management
- [x] View all comments (pending, approved, rejected, flagged)
- [x] Filter by status
- [x] Filter by type (which model)
- [x] Filter by author
- [x] Approve with one click
- [x] Reject with reason
- [x] Flag for review
- [x] Delete/restore comments
- [x] View full comment details

---

## Audit Logging

All actions logged to Laravel logs:

```
[2025-12-20 15:30:45] local.INFO: Review created by client {"review_id":1,"client_id":5,"provider_id":3,"rating":5,"is_verified":false}

[2025-12-20 15:31:20] local.INFO: Review approved by admin {"review_id":1,"admin_id":1,"client_id":5,"provider_id":3}

[2025-12-20 15:35:10] local.INFO: Comment created by user {"comment_id":12,"user_id":5,"commentable_type":"App\\Models\\Review","commentable_id":1}
```

---

## Performance Considerations

### Indexed Fields
- `reviews.is_active` - Frequent filtering
- `reviews.created_at` - Sorting  
- `reviews.rating` - Filtering
- `comments.is_active` - Frequent filtering
- `comments.user_id` - Filtering by author
- `comments.commentable_type + commentable_id` - Polymorphic lookup

### Query Optimization
- Use `activeReviews()` scope instead of `where('is_active', true)`
- Eager load relationships: `->with(['client', 'approvedBy'])`
- Paginate large result sets: `->paginate(10)`

---

## Security Reminders

1. **Only CLIENT users can create reviews** - Enforced in controller
2. **Cannot review yourself** - Checked in store method
3. **One review per client per provider** - Unique constraint + code check
4. **Pending reviews hidden from website** - `is_active = false` default
5. **All admin actions logged** - Audit trail for compliance
6. **Soft delete comments** - Preserves history, reversible
7. **Authorization checks** - User can only edit own pending content

---

## Troubleshooting

### "You cannot write a review for yourself"
- Ensure `$provider->user_id !== $user->id` check is passing
- Verify service provider profile is correctly associated with user

### "You have already written a review"
- Check unique constraint: `[(service_provider_profile_id, client_id)]`
- User can only write ONE review per provider

### Reviews not showing on website
- Verify `is_active = true` (admin approved)
- Check `->activeReviews()` scope is being used
- Review may still be pending admin approval

### Comments not showing on website
- Verify `is_active = true` (admin approved)
- Check `->activeComments()` relationship
- Ensure `commentable_type` and `commentable_id` match

---

## Future Enhancements

Suggested features for future releases:

1. **Rating aggregation** - Calculate average rating per provider
2. **Response to reviews** - Providers can respond to reviews
3. **Helpful votes** - Users vote if review is helpful
4. **Review edit history** - Track all edits to reviews
5. **Comment threading** - Nested replies to comments
6. **Review reports** - More detailed flagging categories
7. **Auto-moderation** - AI/ML for spam detection
8. **Email notifications** - Notify on review approval/response

