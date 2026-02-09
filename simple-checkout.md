# Simple Checkout Plan for Honey Template Landing Page

## Overview

This document outlines the guest checkout implementation for the honey landing page template. The checkout system handles dynamic shipping charges, payment methods, and creates orders for guest users without requiring authentication.

## Analysis of Existing Order System

### Model: `Order`
- **Relationships:**
    - `details()`: Has many `OrderDetails` (products/variations).
    - `payments()`: Has many `OrderPayment`.
    - `user()`: Belongs to `User` (customer).
    - `assign()`: Belongs to `User` (worker).
    - `courier()`: Belongs to `Courier`.
    - `delivery_charge()`: Belongs to `DeliveryCharge` (via `delivery_charge_id`).

### Migration: `orders` table
- **Required Fields:**
    - `user_id` (BigInteger) - Customer user ID (created for guest users).
    - `invoice_no` (string) - Generated automatically.
    - `status` (string) - Defaults to 'pending'.
    - `amount`, `final_amount` (decimal) - Calculated.
    - `delivery_charge_id` (BigInteger) - References delivery_charges table.
    - `assign_user_id` (BigInteger) - Worker assigned to order.
- **Nullable Fields (Can be skipped or defaulted):**
    - `shipping_address`, `city`, `state`, `zip_code`
    - `first_name`, `last_name`, `mobile`, `date`
    - `payment_status` (Defaults to 'due' for COD, '{method}_pending' for online)
    - `shipping_charge`, `tax`, `discount`
    - `delivery_type`

### Controller: `CheckoutController@storeData`
- **Validation**:
    - `first_name`: required
    - `mobile`: required, 11 digits
    - `shipping_address`: required
    - `delivery_charge_id`: required|numeric
    - `product_id` / `variation_id`: Required for order details.

## Honey Template Checkout Strategy

The honey template allows users to checkout with just **Name**, **Phone**, and **Address** as guest users (no authentication required).

### A. Guest User Strategy

**No Authentication Required**: Orders are created for guest users without requiring login.

**User Creation/Update Logic**:
- Uses mobile number as unique identifier
- If user with mobile exists, updates user info; otherwise creates new user
- Pattern from `CheckoutController@storeData` (lines 608-620):
  ```php
  if(empty(auth()->user()->id)){
      $user = User::create([
          'first_name' => $request->first_name,
          'mobile' => $request->mobile,
          'shipping_address' => $request->shipping_address,
          'note' => $request->note
      ]);
      $data['user_id'] = $user->id;
  } else {
      $data['user_id'] = auth()->user()->id;
  }
  ```
- For honey template, always use guest creation path (no auth check needed)

### B. Product Information Handling

**Product Source**: Product information comes from `HoneyLandingPage->content['product']` structure:
- `product_id` (nullable - can be null for static products)
- `title`, `image`, `quantity`, `regular_price`, `offer_price`
- `short_description`

**Two Scenarios**:

1. **Dynamic Product** (when `product_id` exists in content):
   - Fetch actual `Product` model using `Product::find($product_id)`
   - Get first variation: `Variation::where('product_id', $product->id)->first()`
   - Use product's `sell_price`, `after_discount`, `discount` fields
   - Use variation's `price` or `discount_price` if available
   - Create `OrderDetails` with actual product and variation IDs

2. **Static Product** (when `product_id` is null):
   - Use static pricing from `content['product']['offer_price']` or `regular_price`
   - No variation_id (set to null)
   - Create `OrderDetails` with static price, no product_id reference
   - Note: This scenario may require special handling or product creation

**Price Priority**:
1. If variation exists and has `discount_price` > 0: use `variation->discount_price`
2. Else if variation has `price` > 0: use `variation->price`
3. Else if product has `after_discount` > 0: use `product->after_discount`
4. Else: use `product->sell_price` or static price from content

### C. Dynamic Shipping Charges

**Model**: `DeliveryCharge`
- Fields: `id`, `title`, `amount`, `status`
- Only active charges (`status = 1`) are displayed in frontend

**Frontend Implementation**:
- Radio buttons populated from `$delivery_charges` collection
- Each radio button has `value="{{ $charge->id }}"` (sends `delivery_charge_id`, not amount)
- User selects shipping option (e.g., "Inside Dhaka - ৳60", "Outside Dhaka - ৳120")

**Backend Handling**:
- Receives `delivery_charge_id` from form
- Looks up charge: `DeliveryCharge::find($delivery_charge_id)`
- Gets amount: `$charge = $charge ? $charge->amount : 0`
- Stores both `delivery_charge_id` and `shipping_charge` (amount) in Order

**Example**:
```php
$charge = DeliveryCharge::find($data['delivery_charge_id']);
$charge = $charge ? $charge->amount : 0;
$data['shipping_charge'] = $charge;
$data['delivery_charge_id'] = $request->delivery_charge_id; // Store ID for relationship
```

### D. Dynamic Payment Methods

**Model**: `PaymentMethod`
- Fields: `id`, `name`, `number` (agent mobile), `instruction`, `type`, `status`
- Only active methods (`status = 1`) are displayed

**Payment Method Options**:

1. **Cash on Delivery (COD)**:
   - Value: `'cod'` (hardcoded, not from database)
   - No additional fields required
   - Sets `payment_status` to `'due'`
   - No `OrderPayment` record created initially

2. **Online Payment Methods** (bkash, nogod, rocket, etc.):
   - Value: Payment method ID (e.g., `$method->id`)
   - Requires additional fields:
     - `fromNumber`: Customer's mobile number (11 digits)
     - `transactionId`: Transaction ID from payment gateway
     - `screenshot`: Image file of payment proof
   - Creates `OrderPayment` record:
     ```php
     $order->payments()->create([
         'amount' => $order->final_amount,
         'account_no' => $request->fromNumber, // Customer mobile
         'tnx_id' => $request->transactionId,
         'method' => $paymentMethod->name, // e.g., 'bkash', 'nogod'
         'date' => date('Y-m-d'),
         'note' => ''
     ]);
     ```
   - Sets `payment_status` to `{method}_pending` (e.g., `'bkash_pending'`, `'nogod_pending'`)

**Frontend Payment Details**:
- Hidden by default, shown when non-COD payment selected
- Displays agent number and instructions from selected payment method
- Collects customer mobile, transaction ID, and screenshot

### E. Order Creation Flow

Complete order creation process:

1. **Validate Form Inputs**:
   - `name`: required, string
   - `phone`: required, 11 digits (Bangladesh format)
   - `address`: required, min 10 characters
   - `delivery_charge_id`: required, exists in delivery_charges table
   - `payment_method`: required, either 'cod' or valid payment_method ID
   - Conditional: `fromNumber`, `transactionId`, `screenshot` if payment_method != 'cod'

2. **Create/Update Guest User**:
   - Check if user exists by mobile number
   - Create or update user record
   - Set `$data['user_id'] = $user->id`

3. **Resolve Product and Variation**:
   - Get `HoneyLandingPage` active page
   - Extract product info from `content['product']`
   - If `product_id` exists: fetch Product and first Variation
   - If `product_id` is null: use static pricing
   - Calculate unit price based on priority (variation > product after_discount > sell_price)

4. **Calculate Totals**:
   - Product price: from product/variation or static
   - Shipping charge: from `DeliveryCharge::find($delivery_charge_id)->amount`
   - Subtotal: product price × quantity
   - Final amount: subtotal + shipping charge
   - Discount: calculated if applicable

5. **Assign Worker**:
   - Get users with role_id 8 (worker role)
   - Filter by status = 1 (active)
   - Randomly assign or round-robin: `array_rand($verified_users)`
   - Set `$data['assign_user_id'] = $worker_id`
   - Fallback to user_id = 1 if no workers available

6. **Generate Invoice Number**:
   - Use `generateUniqueInvoice()` method (from `OrderController`)
   - Generates random 6-digit number, checks for uniqueness
   - Alternative: `rand(111111, 999999)` (less safe, may have collisions)

7. **Create Order Record**:
   ```php
   $order = Order::create([
       'user_id' => $user->id,
       'first_name' => $request->name,
       'mobile' => $request->phone,
       'shipping_address' => $request->address,
       'delivery_charge_id' => $request->delivery_charge_id,
       'shipping_charge' => $charge,
       'amount' => $subtotal + $discount,
       'final_amount' => $subtotal + $charge,
       'discount' => $discount,
       'status' => 'pending',
       'date' => date('Y-m-d'),
       'payment_status' => 'due', // or '{method}_pending'
       'invoice_no' => $invoiceNo,
       'assign_user_id' => $workerId
   ]);
   ```

8. **Create OrderDetails Record**:
   ```php
   $order->details()->create([
       'product_id' => $product->id, // or null for static
       'variation_id' => $variation->id, // or null
       'quantity' => 1, // or from request
       'unit_price' => $finalPrice,
       'discount' => $discountPerUnit,
       'is_stock' => $product->is_stock ?? 0
   ]);
   ```

9. **Handle Payment**:
   - **COD**: Set `payment_status = 'due'` (already set in step 7)
   - **Online Payment**: 
     - Create `OrderPayment` record (see section D)
     - Update `payment_status = '{method}_pending'`
     - Handle screenshot upload to public directory

10. **Update Order Status**:
    - Call `ModulUtil::orderstatus($order)` to recalculate payment status
    - Updates based on total payments vs final amount

11. **Return Success Response**:
    ```json
    {
      "success": true,
      "msg": "Order Create successfully!",
      "url": "/confirm-order/{order_id}"
    }
    ```

### F. Required Form Fields

**Customer Information**:
- `name` (string, required) → maps to `Order.first_name`
- `phone` (string, required, 11 digits) → maps to `Order.mobile`
- `address` (string, required, min 10 chars) → maps to `Order.shipping_address`

**Shipping**:
- `delivery_charge_id` (integer, required) → from radio button value
- Note: Frontend sends ID, not amount; backend looks up amount

**Payment**:
- `payment_method` (string, required) → value: `'cod'` or payment method ID (integer)
- Conditional fields (if `payment_method != 'cod'`):
  - `fromNumber` (string, required, 11 digits) → customer's mobile
  - `transactionId` (string, required) → transaction ID from gateway
  - `screenshot` (file, required, image) → payment proof image

**Product Information** (hidden fields or from context):
- `product_id` (integer, nullable) → from `HoneyLandingPage->content['product']['product_id']`
- `variation_id` (integer, nullable) → first variation of product, or null for static
- `quantity` (integer, default 1) → usually 1 for honey template

### G. Route & Controller

**Route** (to be created):
```php
Route::post('/honey/checkout', [CheckoutController::class, 'honeyCheckout'])
    ->name('honey.checkout');
```

**Controller Method**: `CheckoutController@honeyCheckout`

**Reference Implementation**: 
- Pattern from `CheckoutController@storeData` (lines 594-735)
- Pattern from `LandingPageController@storeData` (lines 496-606)
- Adapt for honey template specifics:
  - Get product from `HoneyLandingPage` content
  - Handle static vs dynamic product
  - Support multiple payment methods dynamically

**Method Structure**:
```php
public function honeyCheckout(Request $request)
{
    // 1. Validation
    // 2. Get HoneyLandingPage active page
    // 3. Create/update guest user
    // 4. Resolve product and variation
    // 5. Calculate totals
    // 6. Assign worker
    // 7. Generate invoice
    // 8. Create order (DB transaction)
    // 9. Create order details
    // 10. Handle payment
    // 11. Update order status
    // 12. Return response
}
```

### H. Database Fields Mapping

**Form Field → Order Model Field**:

| Form Field | Order Field | Notes |
|------------|-------------|-------|
| `name` | `first_name` | Customer name |
| `phone` | `mobile` | 11-digit mobile number |
| `address` | `shipping_address` | Full address text |
| `delivery_charge_id` | `delivery_charge_id` | Foreign key to delivery_charges |
| - | `shipping_charge` | Calculated from delivery_charge_id |
| - | `amount` | Calculated: subtotal + discount |
| - | `final_amount` | Calculated: subtotal + shipping |
| - | `discount` | Calculated from product/variation |
| - | `status` | Default: `'pending'` |
| - | `date` | Default: `date('Y-m-d')` |
| - | `payment_status` | `'due'` (COD) or `'{method}_pending'` (online) |
| - | `invoice_no` | Generated unique 6-digit number |
| - | `user_id` | Created/updated guest user ID |
| - | `assign_user_id` | Assigned worker ID (role_id 8) |

**OrderDetails Fields**:
- `product_id`: From product or null for static
- `variation_id`: First variation or null
- `quantity`: Usually 1
- `unit_price`: Calculated final price per unit
- `discount`: Discount per unit
- `is_stock`: From product model

**OrderPayment Fields** (for online payments):
- `order_id`: Order ID
- `method`: Payment method name (e.g., 'bkash')
- `amount`: Order final_amount
- `account_no`: Customer mobile (fromNumber)
- `tnx_id`: Transaction ID
- `date`: Current date

## Validation Requirements

### Required Validations

1. **Customer Information**:
   - `name`: required, string, max 200
   - `phone`: required, digits, exactly 11 characters (Bangladesh format)
   - `address`: required, string, min 10 characters

2. **Shipping**:
   - `delivery_charge_id`: required, integer, exists in `delivery_charges` table where `status = 1`

3. **Payment**:
   - `payment_method`: required, string, must be either `'cod'` or valid `payment_methods.id` where `status = 1`

4. **Online Payment Fields** (conditional):
   - `fromNumber`: required if `payment_method != 'cod'`, digits, exactly 11 characters
   - `transactionId`: required if `payment_method != 'cod'`, string, max 100
   - `screenshot`: required if `payment_method != 'cod'`, file, image type (jpg, png, jpeg, webp), max size (e.g., 5MB)

5. **Product** (from context):
   - `product_id`: if exists in honeyPage content, must exist in `products` table
   - Verify product is active (`status = 1`)

### Validation Example

```php
$data = $request->validate([
    'name' => 'required|string|max:200',
    'phone' => 'required|digits:11',
    'address' => 'required|string|min:10',
    'delivery_charge_id' => 'required|integer|exists:delivery_charges,id',
    'payment_method' => 'required|string',
    'fromNumber' => 'required_if:payment_method,!=,cod|digits:11',
    'transactionId' => 'required_if:payment_method,!=,cod|string|max:100',
    'screenshot' => 'required_if:payment_method,!=,cod|image|mimes:jpg,png,jpeg,webp|max:5120'
]);

// Additional validation for payment_method
if ($data['payment_method'] !== 'cod') {
    $paymentMethod = PaymentMethod::where('id', $data['payment_method'])
        ->where('status', 1)
        ->first();
    if (!$paymentMethod) {
        return response()->json([
            'success' => false,
            'msg' => 'Invalid payment method selected.'
        ], 422);
    }
}
```

## Error Handling

### Error Scenarios

1. **Invalid Delivery Charge**:
   - Delivery charge ID doesn't exist
   - Delivery charge is inactive (`status != 1`)
   - Response: `{"success": false, "msg": "Invalid shipping option selected."}`

2. **Invalid Payment Method**:
   - Payment method ID doesn't exist
   - Payment method is inactive
   - Response: `{"success": false, "msg": "Invalid payment method selected."}`

3. **Missing Online Payment Details**:
   - Payment method is not COD but required fields missing
   - Response: `{"success": false, "msg": "Please provide all payment details."}`

4. **Product Not Found**:
   - `product_id` exists in content but product doesn't exist in database
   - Product is inactive
   - Response: `{"success": false, "msg": "Product not available."}`

5. **Stock Validation**:
   - Product has stock management enabled
   - Insufficient stock available
   - Response: `{"success": false, "msg": "Stock not available!"}`

6. **Worker Assignment Failure**:
   - No active workers available
   - Response: `{"success": false, "msg": "No worker available to assign."}`

7. **File Upload Failure**:
   - Screenshot upload fails
   - Invalid file type or size
   - Response: `{"success": false, "msg": "Failed to upload payment screenshot."}`

### Error Response Format

```json
{
  "success": false,
  "msg": "Error message describing what went wrong"
}
```

All errors should be returned with appropriate HTTP status codes (400, 422, 500, etc.).

## Success Response

### Response Format

```json
{
  "success": true,
  "msg": "Order Create successfully!",
  "url": "/confirm-order/{order_id}"
}
```

### Response Details

- `success`: Always `true` for successful orders
- `msg`: Success message (can be customized)
- `url`: Redirect URL to order confirmation page
  - Route: `route('front.confirmOrder', $order->id)`
  - Or: `route('front.confirmOrderlanding', $order->id)` for landing pages

### Frontend Handling

The frontend JavaScript should:
1. Handle the JSON response
2. Show success message to user
3. Redirect to confirmation URL
4. Clear any form data or cart data if applicable

## Key Differences from Existing Flow

1. **No Authentication**: Always creates guest user (no auth check)
2. **Single Product**: Honey template is for one specific product (from honeyPage content)
3. **Dynamic Shipping**: Uses `delivery_charge_id` lookup instead of hardcoded values
4. **Payment Method Handling**: Supports both COD and multiple online payment methods with transaction details
5. **Product Source**: Product info comes from `HoneyLandingPage` content, not direct product selection
6. **Static Product Support**: Can handle products without database entry (static pricing)

## Files to Reference

- `app/Http/Controllers/Frontend/CheckoutController.php` - Existing checkout patterns
- `app/Http/Controllers/Backend/LandingPageController.php` - Landing page checkout example
- `app/Models/Order.php` - Order model structure and relationships
- `app/Models/OrderDetails.php` - Order details structure
- `app/Models/OrderPayment.php` - Payment record structure
- `app/Models/DeliveryCharge.php` - Shipping charge model
- `app/Models/PaymentMethod.php` - Payment method model
- `app/Models/HoneyLandingPage.php` - Landing page model with content structure
- `app/Utils/ModulUtil.php` - Utility methods for order status and payment
- `app/Utils/Util.php` - Stock management utilities
- `resources/views/templates/honey.blade.php` - Frontend template

## Implementation Notes

1. **Database Columns**:
   - `delivery_charge_id` column exists in orders table (confirmed via Order model relationship)
   - `assign_user_id` column exists in Order model (used for worker assignment)

2. **Invoice Number Generation**:
   - Preferred: Use `generateUniqueInvoice()` method from `OrderController` (ensures uniqueness)
   - Alternative: `rand(111111, 999999)` (may have collisions)

3. **Stock Management**:
   - May need to decrease product stock when order is created
   - Use `Util::decreaseProductStock($product_id, $variation_id, $quantity)`
   - Check stock before creating order: `Util::checkProductStock($product_id, $variation_id)`

4. **Screenshot Upload**:
   - Store in public directory (e.g., `public/payment_screenshots/`)
   - Generate unique filename: `time() . '_' . $fileName`
   - Store path in OrderPayment or as separate field

5. **Transaction Safety**:
   - Wrap order creation in `DB::beginTransaction()` / `DB::commit()` / `DB::rollback()`
   - Ensures atomicity: if any step fails, rollback all changes

6. **Worker Assignment**:
   - Get workers with role_id 8 (worker role)
   - Filter by status = 1 (active only)
   - Random assignment or round-robin distribution
   - Fallback to user_id = 1 if no workers available

## Conclusion

This checkout flow adapts the existing order system for the honey landing page template, supporting guest users, dynamic shipping charges, and multiple payment methods. The implementation should follow the patterns established in `CheckoutController@storeData` while adapting for the honey template's specific requirements.
