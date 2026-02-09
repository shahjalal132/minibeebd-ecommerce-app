# Incompl

ete Order Management Implementation Plan

## Overview

Create a system to track incomplete orders from the honey landing page checkout form. The system will save form data as users type (with debouncing), and automatically remove incomplete orders when a successful order is placed.

## Implementation Steps

### 1. Database Migration

Create migration file: `database/migrations/YYYY_MM_DD_HHMMSS_create_incomplete_orders_table.php`**Fields:**

- `id` (bigint, primary key, auto-increment)
- `session_id` (string, nullable) - Laravel session ID
- `ip_address` (string, nullable) - User IP address
- `name` (string, nullable)
- `phone` (string, nullable)
- `address` (text, nullable)
- `delivery_charge_id` (bigint, nullable, foreign key to delivery_charges)
- `payment_method` (string, nullable) - 'cod' or payment method ID
- `from_number` (string, nullable) - For online payments
- `transaction_id` (string, nullable) - For online payments
- `screenshot_path` (string, nullable) - For online payments
- `created_at` (timestamp)
- `updated_at` (timestamp)
- `deleted_at` (timestamp, nullable) - Soft deletes

**Indexes:**

- Index on `session_id`
- Index on `ip_address`
- Index on `phone`
- Composite index on `session_id` and `ip_address`

### 2. Model Creation

Create model: `app/Models/IncompleteOrder.php`**Features:**

- Use SoftDeletes trait
- Fillable fields: session_id, ip_address, name, phone, address, delivery_charge_id, payment_method, from_number, transaction_id, screenshot_path
- Relationship: `belongsTo(DeliveryCharge::class, 'delivery_charge_id')`
- Scope methods for querying incomplete orders

### 3. API Route

Add route in `routes/web.php`:

```php
Route::post('/honey/incomplete-order', [CheckoutController::class, 'saveIncompleteOrder'])
    ->name('front.honey.incomplete-order');
```



### 4. Controller Method

Add method `saveIncompleteOrder()` in `app/Http/Controllers/Frontend/CheckoutController.php`:**Functionality:**

- Accept name, phone, address, delivery_charge_id, payment_method, from_number, transaction_id, screenshot (file)
- Get session_id from `session()->getId()`
- Get ip_address from `$request->ip()`
- Handle screenshot upload if provided (store in storage, save path)
- Use `updateOrCreate` with session_id and ip_address as unique identifiers
- Return JSON response with success status

### 5. Frontend Implementation

Update `resources/views/templates/honey.blade.php`:**Changes:**

1. Add IDs to form inputs:

- Name input: `id="checkoutName"`
- Phone input: `id="checkoutPhone"`
- Address textarea: `id="checkoutAddress"`
- Shipping radio buttons: add `data-delivery-charge-id` attribute
- Payment radio buttons: keep existing structure

2. Add JavaScript debounce utility function (1.5 second delay)
3. Add keyup/change event listeners:

- Name, phone, address: debounced save on keyup
- Shipping selection: save on change
- Payment selection: save on change
- Payment details (fromNumber, transactionId, screenshot): debounced save on change

4. Update `placeOrder()` function:

- After successful order creation, call API to delete incomplete order
- Use session_id and ip_address to identify and delete

5. Add function to save incomplete order via AJAX:

- Collect all form data
- Send FormData to `/honey/incomplete-order` endpoint
- Handle errors gracefully (don't interrupt user experience)

### 6. Cleanup Mechanism (Optional)

Add scheduled task or manual cleanup method to remove incomplete orders older than 30 days.

## Data Flow

```javascript
User types in form
    ↓
Debounce (1.5s delay)
    ↓
AJAX call to saveIncompleteOrder()
    ↓
Update/Create IncompleteOrder record
    ↓
User completes order
    ↓
placeOrder() → honeyCheckout()
    ↓
On success: Delete IncompleteOrder
```



## Files to Modify/Create

1. **New Files:**

- `database/migrations/YYYY_MM_DD_HHMMSS_create_incomplete_orders_table.php`
- `app/Models/IncompleteOrder.php`

2. **Modified Files:**

- `routes/web.php` - Add incomplete order route
- `app/Http/Controllers/Frontend/CheckoutController.php` - Add `saveIncompleteOrder()` method, update `honeyCheckout()` to delete incomplete order
- `resources/views/templates/honey.blade.php` - Add IDs, event listeners, and AJAX save functionality

## Technical Considerations

- **Debouncing:** Use 1.5 second delay to prevent excessive database writes
- **Session Management:** Track by both session_id and ip_address for better identification
- **File Uploads:** Handle screenshot uploads for incomplete orders (store temporarily, cleanup on order completion)
- **Error Handling:** Fail silently on incomplete order save errors to not disrupt user experience