# Timeout Configuration untuk AI Kimi Generation

## Problem

Error "Maximum execution time of 30 seconds exceeded" terjadi ketika generate caption dengan AI.

## Root Cause

1. **HTTP Timeout**: Laravel HTTP client default timeout sangat pendek (tidak set atau ~5 detik)
2. **PHP Execution Time**: PHP default `max_execution_time` hanya 30 detik
3. **API Response Time**: NVIDIA Kimi API bisa membutuhkan waktu lebih dari 30 detik untuk generate caption

## Solution Implemented

### 1. HTTP Timeout Configuration

**File**: `config/services.php`

```php
'nvidia_kimi' => [
    'api_key' => env('NVIDIA_KIMI_API_KEY'),
    'api_url' => env('NVIDIA_KIMI_API_URL', 'https://integrate.api.nvidia.com/v1/chat/completions'),
    'model' => env('NVIDIA_KIMI_MODEL', 'moonshotai/kimi-k2.5'),
    'timeout' => env('NVIDIA_KIMI_TIMEOUT', 120), // Default 120 detik = 2 menit
],
```

**File**: `.env`

```env
NVIDIA_KIMI_TIMEOUT=120
```

**File**: `app/Services/NvidiaKimiService.php`

```php
protected int $timeout = 120; // Load dari config
public function __construct() {
    $this->timeout = config('services.nvidia_kimi.timeout', 120);
}

// Di method generateCaption:
$response = Http::withHeaders([...])->timeout($this->timeout)->post($this->apiUrl, [
```

### 2. PHP Execution Time Limit

**File**: `app/Http/Controllers/Api/GeneratedCaptionController.php`

```php
public function generate(Request $request)
{
    // Set PHP execution time limit untuk AI generation (150 detik)
    // Harus lebih besar dari HTTP timeout (120 detik)
    set_time_limit(150);
    ...
}
```

## Configuration Levels

### Development Environment

- HTTP Timeout: 120 detik (dari `.env`)
- PHP max_execution_time: 150 detik (set di controller)
- ✅ Ready out of the box

### Production Environment

#### Option A: Using .env (Recommended)

```env
NVIDIA_KIMI_TIMEOUT=180  # Adjust sesuai kebutuhan
```

#### Option B: Modify php.ini

```ini
max_execution_time = 300  ; 5 menit untuk AI generation endpoints
```

#### Option C: Using .htaccess (Apache)

```apache
<IfModule mod_php.c>
    php_value max_execution_time 300
</IfModule>
```

#### Option D: Using nginx.conf (Nginx)

```nginx
location ~ \.php$ {
    fastcgi_pass unix:/var/run/php-fpm.sock;
    fastcgi_param PHP_VALUE "max_execution_time=300";
}
```

## Testing

### Test 1: Basic Generation (Template Mode)

```bash
curl -X POST http://localhost:8000/api/admin/generated-captions/generate \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "product_id": "YOUR_PRODUCT_UUID",
    "tone": "friendly",
    "include_emoji": true,
    "use_ai": false
  }'
```

Expected: Response dalam 2-3 detik

### Test 2: AI Generation (Slow)

```bash
curl -X POST http://localhost:8000/api/admin/generated-captions/generate \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "product_id": "YOUR_PRODUCT_UUID",
    "tone": "professional",
    "include_emoji": true,
    "use_ai": true
  }'
```

Expected: Response dalam 30-120 detik (tergantung API NVIDIA)

## Timeout Hierarchy

```
┌─────────────────────────────────────────┐
│ PHP max_execution_time: 150 detik       │
│ (Set di GeneratedCaptionController)     │
└─────────────────────────────────────────┘
           ↓
┌─────────────────────────────────────────┐
│ HTTP Request Timeout: 120 detik         │
│ (Set di NvidiaKimiService)              │
└─────────────────────────────────────────┘
           ↓
┌─────────────────────────────────────────┐
│ NVIDIA Kimi API Response Time: ~30-60s  │
│ (Actual API processing)                 │
└─────────────────────────────────────────┘
```

## Troubleshooting

### Masih Timeout?

1. **Check PHP version**

    ```bash
    php -v
    ```

2. **Verify timeout configuration**

    ```bash
    php -r "echo ini_get('max_execution_time');"
    ```

3. **Check if set_time_limit() is disabled**

    ```bash
    php -r "echo ini_get('disable_functions');"
    ```

4. **Increase timeout values**
    - Ubah `NVIDIA_KIMI_TIMEOUT` di `.env` menjadi 180 atau 240 detik
    - Ubah `set_time_limit()` value di controller menjadi 300 detik

### API Still Timing Out?

1. Check NVIDIA API status: https://status.nvidia.com
2. Verify API key is valid
3. Check network latency
4. Reduce `max_tokens` value di NvidiaKimiService (dari 16384 ke 4096)

## Changes Summary

| File                             | Change                                    | Purpose                                |
| -------------------------------- | ----------------------------------------- | -------------------------------------- |
| `config/services.php`            | Added timeout config                      | Configure HTTP timeout per environment |
| `.env`                           | Added NVIDIA_KIMI_TIMEOUT=120             | Set HTTP timeout value                 |
| `NvidiaKimiService.php`          | Added timeout property + load from config | Use configurable timeout               |
| `NvidiaKimiService.php`          | Added ->timeout($this->timeout)           | Apply timeout to HTTP request          |
| `GeneratedCaptionController.php` | Added set_time_limit(150)                 | Allow longer PHP execution             |

## Notes

- Default timeout values (120/150 detik) safe untuk kebanyakan cases
- Adjust berdasarkan network latency dan API performance
- Production: monitor actual response times dan adjust accordingly
- Jangan set timeout terlalu tinggi untuk menghindari resource abuse
