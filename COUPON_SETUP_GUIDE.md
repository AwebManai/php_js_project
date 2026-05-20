# Coupon System Setup Guide

## Overview
This guide explains how to set up and use the new coupon system that's been added to your StyleStore e-commerce platform.

## Database Setup

1. **Run the SQL setup script** to create the necessary tables:
   - Open `coupon_setup.sql` in phpMyAdmin or your MySQL client
   - Execute the SQL queries to create the `coupon` and `coupon_uses` tables
   - These queries will also add `coupon_id` and `coupon_discount` columns to the `purchase_order` table

## Key Components Added

### 1. Database Tables

**coupon table:**
- `id` - Primary key
- `code` - Unique coupon code (e.g., "SUMMER20")
- `discount_type` - "fixed" (dollar amount) or "percent" (percentage)
- `discount_value` - The discount amount (e.g., 20 for $20 off or 20% off)
- `max_uses` - Maximum number of times this coupon can be used (e.g., 5)
- `times_used` - Current usage count (auto-incremented)
- `expiration_date` - Optional expiration date/time
- `active` - Boolean flag (1 = active, 0 = inactive)
- `created_at`, `updated_at` - Timestamps

**coupon_uses table:**
- Tracks which user used which coupon (one-to-one relationship per coupon per user)
- Prevents duplicate usage per user
- Linked to purchase orders for reference

### 2. API Endpoints

#### **api/admin_coupons.php** - Admin Coupon Management
- **GET ?action=list** - List all coupons (admin only)
- **POST ?action=create** - Create new coupon
- **POST ?action=update** - Update coupon details
- **POST ?action=delete** - Delete a coupon

#### **api/validate_coupon.php** - User Coupon Validation
- **POST ?action=validate** - Validate and calculate coupon discount
  - Checks: code existence, active status, expiration, usage limit, per-user usage
  - Returns: discount amount, final total

#### **api/checkout.php** - Enhanced Checkout
- Now accepts optional `coupon_id` in request
- Applies discount to order
- Records coupon usage automatically

### 3. Admin Interface (admin_manage.php)

New "Manage Coupons" tab with:
- **Create Coupon** form
  - Coupon code
  - Discount type (fixed or percent)
  - Discount value
  - Max uses
  - Expiration date
- **Coupons List** displaying:
  - Code, type, discount, usage count
  - Expiration date
  - Status (Active/Inactive/Expired)
  - Edit and Delete buttons

### 4. Customer Interface (index.php)

Enhanced shopping cart with:
- **Coupon code input field** in cart modal
- **Apply/Remove Coupon** button
- **Coupon validation** with real-time feedback
- **Discount display** showing:
  - Discount amount
  - Final total after discount
- **Automatic coupon pass** to checkout

## Usage Instructions

### For Admins

1. **Navigate to Admin Panel**
   - Go to `admin_manage.php`
   - Click "Manage Coupons" tab

2. **Create a Coupon**
   - Enter coupon code (e.g., "SUMMER20", "WELCOME10")
   - Select discount type:
     - Fixed: Dollar amount (e.g., $10 off)
     - Percent: Percentage off (e.g., 20% off)
   - Enter discount value
   - Set max uses (e.g., 5 means only 5 customers can use it)
   - Optionally set expiration date
   - Click "Create Coupon"

3. **Manage Existing Coupons**
   - View all coupons with usage statistics
   - Edit: Update discount, max uses, or expiration date
   - Delete: Remove a coupon permanently
   - Status shows: Active, Inactive, or Expired

### For Customers

1. **Add Products to Cart**
   - Browse and add items to cart as normal

2. **Apply Coupon**
   - Open cart by clicking "View Cart"
   - Scroll to "Apply Coupon Code" section
   - Enter valid coupon code
   - Click "Apply"
   - System validates and shows discount

3. **Coupon Validation**
   - Code must be active
   - Must not be expired
   - Usage must not exceed max uses
   - User must not have used this code before
   - If any validation fails, error message displays

4. **Complete Purchase**
   - Discount automatically applied to order
   - Click "Proceed to Checkout"
   - System records coupon usage (one time per user per code)

## Coupon Examples

### Example 1: Welcome Discount
- Code: `WELCOME10`
- Type: Fixed
- Value: $10
- Max Uses: 100
- Expiration: 30 days from today
- Use: New customers get $10 off first purchase

### Example 2: Flash Sale
- Code: `FLASH50`
- Type: Percent
- Value: 50
- Max Uses: 5
- Expiration: 24 hours
- Use: Limited time 50% off for first 5 users

### Example 3: VIP Discount
- Code: `VIP20`
- Type: Percent
- Value: 20
- Max Uses: No limit (very high number)
- Expiration: 1 year out
- Use: VIP members get permanent 20% discount

## Validation Rules

### Coupon Validity Checks:
1. ✓ Code must exist in database
2. ✓ Coupon must be active (active=1)
3. ✓ Must not be expired (current_time < expiration_date)
4. ✓ Usage count must not exceed max uses
5. ✓ User must not have used this coupon before (checked in coupon_uses table)
6. ✓ Discount must not exceed cart total (for fixed discounts)

## Database Queries

### View All Coupons:
```sql
SELECT * FROM coupon ORDER BY created_at DESC;
```

### Check Coupon Usage by User:
```sql
SELECT * FROM coupon_uses WHERE user_id = X;
```

### See Order with Coupon:
```sql
SELECT * FROM purchase_order WHERE coupon_id IS NOT NULL;
```

### Get Coupon Statistics:
```sql
SELECT code, times_used, max_uses, active, 
       ROUND((times_used/max_uses)*100, 2) as usage_percent
FROM coupon
ORDER BY times_used DESC;
```

## Important Notes

1. **One Coupon Per Order**: Each customer can only use one coupon per purchase
2. **One-Time Per User Per Code**: A user can only use the same coupon code once
3. **Unlimited Global Usage**: Multiple users can use the same code (up to max_uses limit)
4. **No Code Stacking**: Only one coupon per checkout
5. **Discount Application**: Fixed discounts are capped at cart total
6. **Automatic Recording**: Coupon usage is recorded on successful purchase

## Troubleshooting

### Coupon Not Applying?
- Check if code is correct (case-sensitive)
- Verify coupon is active
- Check expiration date
- Ensure usage hasn't exceeded limit
- Verify you haven't used this code before

### Database Tables Not Created?
- Run `coupon_setup.sql` through phpMyAdmin
- Check for SQL errors
- Verify database permissions

### API Errors?
- Check that admin is logged in (for admin_coupons.php)
- Check that user is logged in (for validate_coupon.php and checkout.php)
- Review error messages in browser console
- Check database connection

## File Locations

- **Database Setup**: `/coupon_setup.sql`
- **Admin API**: `/api/admin_coupons.php`
- **User Validation API**: `/api/validate_coupon.php`
- **Updated Checkout**: `/api/checkout.php`
- **Admin Interface**: `/admin_manage.php`
- **Customer Interface**: `/index.php`
