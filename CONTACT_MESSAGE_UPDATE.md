# Contact Message Feature Update

## Summary of Changes

Replaced email field with phone_number field in contact messages system. WhatsApp validation is now automatic, and admin replies are sent directly to WhatsApp without requiring additional parameters.

---

## Database Changes

### Migration: `2026_04_20_000001_replace_email_with_phone_in_contact_messages`

**Changes:**

- ❌ Removed: `email` column
- ✅ Added: `phone_number` (varchar 20, indexed)

**Table Structure (After Migration):**

```
id (char 36, PK)
name (varchar 255)
phone_number (varchar 20, INDEX) ← NEW
message (text)
is_read (boolean, INDEX)
replied_at (timestamp, nullable)
reply_message (text, nullable)
created_at (timestamp, INDEX)
```

---

## Model Changes

### ContactMessage Model

**Updated Fillable Array:**

```php
protected $fillable = [
    'name',
    'phone_number',        // Changed from 'email'
    'message',
    'is_read',
    'replied_at',
    'reply_message',
    'created_at',
];
```

---

## API Endpoint Changes

### 1. POST /api/public/contact (Create Message)

#### Request Body (BEFORE)

```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "message": "Saya ingin bertanya tentang produk..."
}
```

#### Request Body (AFTER)

```json
{
    "name": "John Doe",
    "phone_number": "6281234567890",
    "message": "Saya ingin bertanya tentang produk..."
}
```

#### Phone Number Validation

- **Format**: International or local format accepted
    - ✅ `6281234567890`
    - ✅ `+6281234567890`
    - ✅ `08123 4567890`
    - ✅ `+62 (812) 3456-7890`
    - ❌ `john@example.com` (invalid)

#### WhatsApp Validation (NEW)

- Before creating message, system validates if phone number is registered on WhatsApp
- Uses Evolution API to check number existence
- **If number NOT found on WhatsApp:**
    - Returns HTTP 422 (Unprocessable Entity)
    - Error message: "Nomor WhatsApp tidak valid atau tidak terdaftar"

#### Response (Success - 201)

```json
{
    "data": {
        "message": "Pesan Anda berhasil dikirim. Kami akan segera menghubungi Anda.",
        "id": "550e8400-e29b-41d4-a716-446655440000"
    }
}
```

#### Response (Validation Error - 422)

```json
{
    "message": "Nomor WhatsApp tidak valid atau tidak terdaftar. Harap gunakan nomor yang terdaftar di WhatsApp.",
    "errors": {
        "phone_number": ["Nomor WhatsApp tidak ditemukan."]
    }
}
```

#### Admin Notification

When message is created, admin receives WhatsApp notification:

```
📩 *Pesan Baru Masuk — Mealjun Website*

*Dari:* John Doe
*WhatsApp:* 6281234567890

*Pesan:*
Saya ingin bertanya tentang produk...

_Balas pesan ini melalui panel admin._
```

---

### 2. POST /api/admin/contact-messages/{id}/reply (Reply to Message)

#### Request Body (BEFORE)

```json
{
    "reply_message": "Terima kasih atas pertanyaan Anda...",
    "send_whatsapp_notif": true,
    "recipient_phone": "6281234567890"
}
```

#### Request Body (AFTER)

```json
{
    "reply_message": "Terima kasih atas pertanyaan Anda..."
}
```

**Changes:**

- ❌ Removed: `send_whatsapp_notif` parameter
- ❌ Removed: `recipient_phone` parameter
- ✅ Auto behavior: Always sends WhatsApp to stored phone_number

#### Auto WhatsApp Reply (NEW)

When admin replies, system automatically sends WhatsApp message to customer using phone_number from the original message:

```
Halo _John Doe_,

Terima kasih telah menghubungi kami. Berikut balasan kami:

Terima kasih atas pertanyaan Anda...

*— Tim Mealjun*
```

#### Error Handling

- If WhatsApp send fails, message is still saved (doesn't fail the request)
- Error logged in application logs
- Frontend receives successful response with saved message

#### Response (Success - 200)

```json
{
    "data": {
        "id": "550e8400-e29b-41d4-a716-446655440000",
        "name": "John Doe",
        "phone_number": "6281234567890",
        "message": "Saya ingin bertanya tentang produk...",
        "reply_message": "Terima kasih atas pertanyaan Anda...",
        "is_read": true,
        "replied_at": "2026-04-20T10:30:00.000Z",
        "created_at": "2026-04-20T10:00:00.000Z"
    }
}
```

---

## Other Endpoints (No Changes)

### GET /api/admin/contact-messages

- Lists all contact messages
- No changes to response format
- `phone_number` appears instead of `email` in response

### GET /api/admin/contact-messages/{id}

- Gets single contact message
- `phone_number` appears instead of `email`

### PATCH /api/admin/contact-messages/{id}/read

- Marks message as read
- No changes

### DELETE /api/admin/contact-messages/{id}

- Deletes message
- No changes

---

## Implementation Details

### Validation Rules

**store() method:**

```php
'name' => 'required|string|max:255',
'phone_number' => 'required|string|max:20|regex:/^([0-9+\-\s()])+$/',
'message' => 'required|string|min:10',
```

**reply() method:**

```php
'reply_message' => 'required|string|min:5',
```

### WhatsApp Number Validation Flow

```
User submits request
    ↓
Phone number regex validation (format check)
    ↓
Evolution API checkWhatsappNumbers() call
    ↓
Does WhatsApp number exist?
    ├─ YES → Create message + Send admin notification + Return 201
    └─ NO → Return 422 error with message
```

### Auto WhatsApp Reply Flow

```
Admin calls reply endpoint
    ↓
Validate reply_message
    ↓
Save message with reply data
    ↓
Auto send WhatsApp using phone_number
    ├─ Success → Return response with message
    └─ Fail → Log error + Still return 200 response
```

---

## Frontend Integration Notes

### Contact Form Update

1. Change email input to phone input
2. Add phone format placeholder: "+62 812 3456 7890"
3. Handle WhatsApp validation error (422 response)
4. Show error message if number not found on WhatsApp

### Admin Panel Reply Form Update

1. Remove "Send WhatsApp" checkbox
2. Remove "Recipient Phone" field
3. Keep only reply_message textarea
4. Display: "WhatsApp will be sent automatically"

### Error Handling Example

```javascript
// When user submits contact form
try {
    const response = await fetch("/api/public/contact", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            name: "John Doe",
            phone_number: "+6281234567890",
            message: "Your message...",
        }),
    });

    if (!response.ok) {
        const error = await response.json();
        // Show error: "Nomor WhatsApp tidak valid atau tidak terdaftar"
        console.error(error.errors.phone_number[0]);
        return;
    }

    // Success
    console.log("Message sent successfully");
} catch (err) {
    console.error("Request failed:", err);
}
```

---

## Migration & Deployment

### Before Running Migration

- No existing contacts should have NULL phone_number values
- Consider data migration if you have existing contacts
- Backup database

### Run Migration

```bash
php artisan migrate
```

### Database Rollback (if needed)

```bash
php artisan migrate:rollback
```

---

## Files Modified

| File                                                                                     | Changes                                         |
| ---------------------------------------------------------------------------------------- | ----------------------------------------------- |
| `database/migrations/2026_04_20_000001_replace_email_with_phone_in_contact_messages.php` | NEW - Migration file                            |
| `app/Models/ContactMessage.php`                                                          | Updated fillable array                          |
| `app/Http/Controllers/Api/ContactMessageController.php`                                  | Updated store() and reply() methods             |
| Database Schema                                                                          | email column removed, phone_number column added |

---

## Testing Checklist

- [ ] Test contact form submission with valid WhatsApp number
- [ ] Test contact form with invalid number format
- [ ] Test contact form with unregistered WhatsApp number
- [ ] Verify admin receives notification message
- [ ] Test admin reply endpoint
- [ ] Verify customer receives WhatsApp reply
- [ ] Test error handling when WhatsApp send fails
- [ ] Verify message still saves even if WhatsApp send fails
- [ ] Check phone number formatting (08xx → 62xx conversion)
- [ ] Verify phone number is indexed in database

---

## Migration Status

✅ **Completed**

- Migration created: `2026_04_20_000001_replace_email_with_phone_in_contact_messages`
- Migration applied: 132.09ms
- Database schema updated
- Models updated
- Controllers updated
- WhatsApp validation implemented
- Auto reply implemented

**Date**: April 20, 2026
**Status**: Ready for testing
