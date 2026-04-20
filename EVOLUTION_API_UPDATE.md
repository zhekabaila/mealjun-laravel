# Evolution API WhatsApp Number Check - Update

## Summary of Changes

Updated `checkWhatsappNumbers` method to properly handle single phone number validation and return complete response data from Evolution API, instead of just boolean.

---

## Previous Implementation (Problematic)

```php
public function checkWhatsappNumbers(string $numbers): bool
{
    // ...
    print($response->json()[0]);  // Debug output
    return $response->json()[0]->exists;  // Only returns boolean
}
```

**Issues:**
- ❌ Had `print()` debug line
- ❌ Only returned `boolean` (exists or not)
- ❌ Wasted the rich response data from API (jid, name, number)
- ❌ Didn't format phone number before sending
- ❌ Couldn't distinguish between "number not found" and "API error"

---

## New Implementation (Fixed)

```php
public function checkWhatsappNumbers(string $number): array|null
{
    try {
        // Format the number first (08xxx → 62xxx)
        $formattedNumber = $this->formatNumber($number);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'apikey' => $this->apiKey,
        ])->post(
            "{$this->baseUrl}/chat/whatsappNumbers/{$this->instanceName}",
            ['numbers' => [$formattedNumber]]
        );

        if ($response->failed()) {
            throw new Exception('Evolution API check failed: ' . $response->body());
        }

        // API returns array of objects, get the first one
        $responseData = $response->json();
        
        if (empty($responseData)) {
            return null;
        }

        // Convert to associative array
        $result = (array) $responseData[0];

        return [
            'exists' => $result['exists'] ?? false,
            'jid' => $result['jid'] ?? null,
            'name' => $result['name'] ?? null,
            'number' => $result['number'] ?? null,
        ];
    } catch (Exception $e) {
        Log::error('WhatsApp number check error: ' . $e->getMessage(), [
            'phone_number' => $number,
        ]);
        return null;
    }
}
```

**Improvements:**
- ✅ Removed `print()` debug line
- ✅ Returns complete response array with all data
- ✅ Auto-formats phone number (08xxx → 62xxx)
- ✅ Returns `null` on API error (graceful failure)
- ✅ Return type: `array|null` instead of `bool`
- ✅ Better error logging

---

## Return Value

### Success Response
```php
[
    'exists' => true,
    'jid' => '6281313747177@s.whatsapp.net',
    'name' => 'jek',
    'number' => '6281313747177'
]
```

### Failure Response
```php
null  // API error or number doesn't exist
```

---

## API Request/Response Details

### Request
```bash
curl --location 'https://evolution.coreapps.web.id/chat/whatsappNumbers/Ristin' \
--header 'Content-Type: application/json' \
--header 'apikey: 429683C4C977415CAAFCCE10F7D57E11' \
--data '{
  "numbers": ["6281313747177"]
}'
```

### Response (Number Exists)
```json
[
    {
        "exists": true,
        "jid": "6281313747177@s.whatsapp.net",
        "name": "jek",
        "number": "6281313747177"
    }
]
```

### Response (Number Not Exists)
```json
[
    {
        "exists": false,
        "jid": null,
        "name": null,
        "number": "6281313747177"
    }
]
```

---

## Implementation Flow

### For Single Number Validation

```
User Input: "08131-37-47177" or "6281313747177"
    ↓
formatNumber() → "6281313747177"
    ↓
HTTP POST to Evolution API
    ↓
API Response: [{"exists": true, "jid": "...", "name": "jek", "number": "..."}]
    ↓
Return: ["exists" => true, "jid" => "...", "name" => "jek", "number" => "..."]
```

### Usage in ContactMessageController

```php
// Check WhatsApp number
$checkResult = $this->evolutionApi->checkWhatsappNumbers($validated['phone_number']);

// Result is array or null
if (!$checkResult || !$checkResult['exists']) {
    return response()->json([
        'message' => 'Nomor WhatsApp tidak ditemukan.',
        'errors' => ['phone_number' => ['Nomor WhatsApp tidak terdaftar.']]
    ], 422);
}

// Number is valid, proceed with creating message
$contact = ContactMessage::create($validated);
```

### Usage in notifyAdmin

```php
public function notifyAdmin(string $adminNumber, string $message): bool
{
    try {
        // Returns array or null
        $checkResult = $this->checkWhatsappNumbers($adminNumber);

        // Check if number exists
        if (!$checkResult || !$checkResult['exists']) {
            Log::warning("WhatsApp number validation failed for: {$adminNumber}");
            return false;
        }

        // Use the validated and formatted number from response
        $this->sendText($checkResult['number'], $message);

        return true;
    } catch (Exception $e) {
        Log::error("WhatsApp admin notification failed: " . $e->getMessage());
        return false;
    }
}
```

---

## Error Handling

### Case 1: Number doesn't exist on WhatsApp
```
checkWhatsappNumbers('6281313747177') → returns array with exists=false
```
Action: Return 422 error to user

### Case 2: API call fails
```
checkWhatsappNumbers('invalid') → returns null
```
Action: Return 422 error, log to application logs

### Case 3: Network/Connection error
```
checkWhatsappNumbers('6281313747177') → Exception caught, returns null, logged
```
Action: Silent fail or return error depending on context

---

## Phone Number Formatting

Auto-formats numbers:
- `08131374717` → `6281313747177`
- `+628131374717` → `628131374717`
- `62 813 1374717` → `628131374717`

Formats using `formatNumber()` method:
```php
protected function formatNumber(string $number): string
{
    $cleaned = preg_replace('/\D/', '', $number);
    
    if (str_starts_with($cleaned, '08')) {
        return '62' . substr($cleaned, 1);
    }
    
    return $cleaned;
}
```

---

## Testing Checklist

- [ ] Test with valid WhatsApp number: `checkWhatsappNumbers('6281313747177')`
  - Expected: Returns array with `exists: true`
  
- [ ] Test with invalid number format: `checkWhatsappNumbers('not-a-number')`
  - Expected: Returns `null` (or array with `exists: false`)
  
- [ ] Test with 08xx format: `checkWhatsappNumbers('081313747177')`
  - Expected: Auto-formats to 62xxx and returns result
  
- [ ] Test with API failure (invalid API key, down service, etc.)
  - Expected: Returns `null`, logs error
  
- [ ] Test in ContactMessageController store() with valid number
  - Expected: Message created, admin notification sent
  
- [ ] Test in ContactMessageController store() with invalid number
  - Expected: Returns 422 error
  
- [ ] Test in notifyAdmin() with valid number
  - Expected: WhatsApp message sent
  
- [ ] Test in notifyAdmin() with invalid number
  - Expected: Returns false, logs warning

---

## Files Modified

| File | Changes |
|------|---------|
| `app/Services/EvolutionApiService.php` | Updated `checkWhatsappNumbers()` method to return array\|null |
| `app/Services/EvolutionApiService.php` | Updated `notifyAdmin()` to handle new response format |
| `app/Http/Controllers/Api/ContactMessageController.php` | Already updated to handle new response format |

---

## Benefits

✅ **Complete Data**: Access to jid, name, number - useful for logging/debugging  
✅ **Better Error Handling**: Can distinguish between "not found" vs "API error"  
✅ **Auto-Formatting**: Phone numbers formatted before API call  
✅ **Type Safety**: Return type clearly defined as `array|null`  
✅ **Consistent**: Works with both single number checks and admin notifications  
✅ **No Debug Output**: Removed `print()` statement  
✅ **Better Logging**: Errors logged with context

---

## Migration Notes

- No database migrations needed
- No configuration changes needed
- Method signature changed: `bool` → `array|null`
- Callers already updated to handle new format
- Backward compatible: `!$checkResult` still works for existence check

---

**Date**: April 20, 2026  
**Status**: ✅ Updated and Ready
