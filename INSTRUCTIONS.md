# Instruksi Pembangunan API Laravel 12 — Mealjun

Dokumen ini mendefinisikan pola, konvensi, dan standar yang wajib diikuti saat mengembangkan API backend Mealjun. Setiap fitur baru, controller, model, atau service harus mengikuti aturan yang tercantum di sini.

---

## 1. Identitas & Database

### Identitas Database

- **Tipe Database**: PostgreSQL
- **UUID Extension**: Wajib diaktifkan (`uuid-ossp`)
- **Primary Key**: UUID (bukan auto-increment)
    - Semua model gunakan trait `HasUuids`
    - Format: `$table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'))`

### Timestamp

- **Format**: TIMESTAMP dengan timezone support
- **Konvensi**:
    - `created_at` dengan `useCurrent()`
    - `updated_at` dengan `useCurrent()->useCurrentOnUpdate()`
    - Beberapa model tidak punya `timestamps` di Migration jika dikelola manual di Model (set `public $timestamps = false`)

---

## 2. Model & Relasi

### Trait Wajib

Setiap model yang merepresentasikan data utama **harus** menggunakan:

```php
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class YourModel extends Model
{
    use HasUuids;
}
```

### Fillable & Casting

- Definisikan `protected $fillable[]` untuk mass assignment
- Gunakan `protected $casts[]` untuk type conversion otomatis:
    - Boolean: `'is_active' => 'boolean'`
    - Integer: `'view_count' => 'integer'`
    - DateTime: `'created_at' => 'datetime'`
    - Date: `'visit_date' => 'date'`
    - Float: `'latitude' => 'float'`

### Hidden Attributes

- Field sensitif seperti `password_hash` wajib di `protected $hidden[]`

### Relasi

- Gunakan `belongsTo()` untuk relasi dengan user (creator/updater)
- Gunakan `hasMany()` untuk relasi satu ke banyak
- Beri nama relasi secara deskriptif (`creator`, `analytics`, `generatedCaptions`)

---

## 3. Service Classes

### CloudinaryService

**Tujuan**: Handle semua upload gambar ke Cloudinary

**Aturan Wajib**:

- Input gambar **HARUS** dalam format base64 lengkap: `data:image/jpeg;base64,...`
- Service validate format di awal method
- Return array dengan keys: `['url' => string, 'public_id' => string]`
- Folder Cloudinary berbeda per use case:
    - Products: `mealjun/products`
    - Avatars: `mealjun/avatars`
    - Gallery: `mealjun/gallery`
    - About: `mealjun/about`

**Method Utama**:

- `uploadBase64(string $base64Image, string $folder, ?string $publicId): array`
- `delete(string $publicId): bool`

### EvolutionApiService

**Tujuan**: Integrasi WhatsApp API untuk notifikasi

**Aturan Wajib**:

- Semua nomor WhatsApp otomatis diformat ke format internasional: `08xx` → `628xx`
- Gunakan method `notifyAdmin()` yang sudah include pengecekan validitas nomor
- Jangan langsung kirim pesan tanpa cek validitas nomor sebelumnya
- Error di WhatsApp API **tidak boleh** menghentikan proses utama (gunakan try-catch dan log)

**Method Utama**:

- `checkWhatsappNumbers(array $numbers): array`
- `sendText(string $number, string $text): array`
- `notifyAdmin(string $adminNumber, string $message): bool`
- `formatNumber(string $number): string` (internal)

---

## 4. Routes & Structure

### Route Groups

Semua routes harus diorganisir dalam 3 group:

1. **AUTH Routes** (no token required)
    - `POST /api/auth/login`
    - `POST /api/auth/register` (jika ada)

2. **PUBLIC Routes** (no token required, untuk frontend)
    - Prefix: `/api/public/`
    - Endpoint baca saja (GET, POST untuk form)
    - Hanya menampilkan data yang `is_published=true`, `is_approved=true`, `is_active=true`
    - Contoh: `/api/public/products`, `/api/public/contact`

3. **PROTECTED Routes** (require `auth:sanctum`)
    - Prefix: `/api/`
    - CRUD lengkap untuk admin
    - Endpoint analytics, dashboard, management

### Rate Limiting (Public Endpoints)

- Contact form: max 3 req/menit per IP
- Analytics tracking: max 30 req/menit per IP
- Gunakan middleware: `Route::post(...)->middleware('throttle:contact')`

---

## 5. Controllers

### Naming Convention

- File: `{Resource}Controller.php` (contoh: `ProductController.php`)
- Class: `class {Resource}Controller extends Controller`
- Location: `app/Http/Controllers/Api/`

### Method Convention (RESTful)

- `index()` — GET, list dengan pagination
- `store()` — POST, buat record baru
- `show(id)` — GET, tampil detail
- `update(id)` — PUT, update full
- `destroy(id)` — DELETE
- Custom methods dengan deskripsi jelas: `toggleFeatured()`, `markAsRead()`, `reorder()`

### Public vs Protected Methods

Gunakan prefix method:

```php
// Public endpoint
public function publicIndex(Request $request) { ... }
public function publicShow(string $id) { ... }

// Protected endpoint (di dalam middleware auth:sanctum)
public function index(Request $request) { ... }
public function store(Request $request) { ... }
```

### Dependency Injection

- Inject service classes di `__construct()`
- Gunakan protected property: `protected CloudinaryService $cloudinary`
- Akses via `$this->cloudinary->methodName()`

### Response Format

**Success**:

```php
return response()->json($data);                // Default 200
return response()->json($data, 201);           // Created
return response()->json(['message' => 'text'], 204); // No content
```

**Error** (biarkan Laravel handle validation error otomatis):

```php
$request->validate([...]);  // Throws ValidationException
```

---

## 6. Validasi (Form Requests / Request Validation)

### Rules

- **Email**: `email|max:255`
- **URL**: `url` atau `nullable|url`
- **UUID**: `uuid|exists:table,column`
- **Enum**: `in:value1,value2,value3`
- **File/Image**: Wajib base64 → `string` bukan `file` type
- **DateTime**: Laravel auto-cast, validasi saat input

### Messages

Selalu provide custom message dalam bahasa Indonesia:

```php
public function messages(): array
{
    return [
        'email.required'    => 'Email wajib diisi.',
        'email.email'       => 'Format email tidak valid.',
    ];
}
```

---

## 7. Image Upload

### Format Wajib: Base64 (BUKAN multipart/form-data)

**Di Frontend** — Convert file ke base64:

```javascript
const fileToBase64 = (file) => {
    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = () => resolve(reader.result);
    });
};

const base64 = await fileToBase64(inputFile);
// base64 = "data:image/jpeg;base64,/9j/4AAQ..."
```

**Di Request**:

```php
$validated['image_base64'] = 'data:image/jpeg;base64,...';
$this->cloudinary->uploadBase64($validated['image_base64'], 'folder');
```

**Validasi Base64**:

```php
'image_base64' => 'required|string|regex:/^data:image\/(jpeg|png|webp|gif);base64,/'
```

---

## 8. Authentication (Sanctum)

### Token Management

- User create token via: `$user->createToken('api-token')->plainTextToken`
- Token disimpan di client, dikirim setiap request di header:
    ```
    Authorization: Bearer {token}
    ```
- Delete token (logout): `$request->user()->currentAccessToken()->delete()`

### Protect Routes

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
});
```

---

## 9. WhatsApp Integration (Evolution API)

### Notifikasi Otomatis Trigger Points

1. **Pesan Contact Form Diterima**
    - Ambil nomor admin dari `about_info.whatsapp_number`
    - Format nomor otomatis via `EvolutionApiService::formatNumber()`
    - Cek validitas nomor via `checkWhatsappNumbers()`
    - Kirim notifikasi dengan format: `📩 *Pesan Baru Masuk*` + data

2. **Admin Balas Pesan** (opsional)
    - Gunakan endpoint `POST /api/contact-messages/{id}/reply`
    - Query param `send_whatsapp_notif=true` dan `recipient_phone=...`
    - Kirim balas ke nomor pengirim

### Format Pesan WhatsApp

- Gunakan emoji untuk visual yang menarik
- Format bold dengan asterisk: `*text*`
- Gunakan line breaks untuk readability
- Contoh:

    ```
    📩 *Pesan Baru Masuk — Mealjun Website*

    *Dari:* John Doe
    *Email:* john@example.com

    *Pesan:*
    Halo, saya ingin...
    ```

### Error Handling

- Gagal kirim WA **tidak boleh** stop proses utama
- Log error: `\Log::error('Error message')`
- Return false/null ke function yang memanggil
- Frontend tidak perlu tahu ada error WhatsApp

---

## 10. Data Visibility & Filtering

### Tampilan Data di Public Endpoints

- **Products**: Hanya `stock_status IN ('available', 'limited')`
- **Testimonials**: `is_approved = true` dan sort by featured
- **Gallery**: `is_published = true` dengan sort by `display_order`
- **Store Locations**: `is_active = true`
- **About**: Always available (single record)

### Filtering di Admin Endpoints

- Gunakan query parameter untuk filter optional
- Contoh: `GET /api/products?stock_status=out_of_stock&flavor=original`
- Always provide pagination: `->paginate(15)` atau `->paginate(20)`

---

## 11. Seeding & Testing

### UserSeeder

Create default admin user dengan kredensial test:

- Email: `admin@mealjun.com`
- Password: `password123` (hashed)
- Role: `super_admin`
- is_active: `true`

### Template Seeding

Seed minimal 3 caption template untuk tone: `friendly`, `professional`, `playful`

### Testing dengan cURL

```bash
# 1. Login
TOKEN=$(curl -s -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@mealjun.com","password":"password123"}' \
  | jq -r '.token')

# 2. Use token
curl -X GET http://localhost:8000/api/dashboard \
  -H "Authorization: Bearer $TOKEN"
```

---

## 12. Code Style & Conventions

### Naming

- **Variables**: camelCase
- **Methods**: camelCase
- **Classes**: PascalCase
- **Constants**: UPPER_SNAKE_CASE
- **Database columns**: snake_case

### Spacing

- 4 spaces indentation
- Empty line antara method di class
- Trailing comma di array multiline:
    ```php
    $array = [
        'key1' => 'value1',
        'key2' => 'value2',  // <-- trailing comma
    ];
    ```

### Comments

- Gunakan PHPDoc untuk method public:
    ```php
    /**
     * Deskripsi singkat.
     *
     * @param  string  $parameter  Deskripsi parameter
     * @return array   Deskripsi return
     * @throws Exception
     */
    ```
- Inline comment hanya untuk logika kompleks

### String Formatting

- Gunakan double quotes untuk string biasa
- Gunakan single quotes untuk string tanpa interpolasi
- Gunakan curly braces untuk interpolasi kompleks:
    ```php
    "Halo {$user->name}!"  // String interpolation
    'Halo ' . $user->name  // Concatenation
    ```

---

## 13. Service Injection & Dependency

### Constructor Injection

```php
public function __construct(
    protected CloudinaryService $cloudinary,
    protected EvolutionApiService $evolutionApi
) {}
```

### Usage

```php
$uploadResult = $this->cloudinary->uploadBase64($base64, 'folder');
$this->evolutionApi->notifyAdmin($phone, $message);
```

---

## 14. Folder Structure

```
app/
├── Http/
│   ├── Controllers/Api/
│   │   ├── AuthController.php
│   │   ├── ProductController.php
│   │   ├── ContactMessageController.php
│   │   ├── ...
│   ├── Requests/
│   │   ├── StoreContactRequest.php
│   │   └── ...
│   └── Resources/
│       ├── ProductResource.php
│       └── ...
├── Models/
│   ├── User.php
│   ├── Product.php
│   ├── ContactMessage.php
│   └── ...
├── Services/
│   ├── CloudinaryService.php
│   └── EvolutionApiService.php
└── Providers/
    └── AppServiceProvider.php

routes/
└── api.php  (bukan web.php)

database/
├── migrations/
│   ├── enable_uuid_extension.php
│   ├── create_users_table.php
│   ├── create_products_table.php
│   └── ...
└── seeders/
    ├── UserSeeder.php
    ├── CaptionTemplateSeeder.php
    └── DatabaseSeeder.php
```

---

## 15. Environment Variables

```dotenv
APP_NAME=MealjunAPI
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=mealjun_db
DB_USERNAME=postgres
DB_PASSWORD=...

# Cloudinary
CLOUDINARY_CLOUD_NAME=...
CLOUDINARY_API_KEY=...
CLOUDINARY_API_SECRET=...
CLOUDINARY_UPLOAD_PRESET=mealjun_preset

# Evolution API (WhatsApp)
EVOLUTION_API_URL=https://evolution.coreapps.web.id
EVOLUTION_API_KEY=...
EVOLUTION_INSTANCE_NAME=...

# Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1
SESSION_DRIVER=cookie
```

---

## 16. Ambiguous Areas — Klarifikasi Lebih Lanjut

Sebelum membuat fitur baru, jelaskan:

1. **Scope Endpoint** — Apakah public (frontend) atau protected (admin)?
2. **Data Visibility** — Apakah ada filter untuk status/approval?
3. **Upload Requirements** — Apakah perlu upload gambar (base64 required)?
4. **Notifikasi** — Apakah perlu kirim WhatsApp notification?
5. **Timestamp** — Apakah perlu `created_by` / `updated_by`?

---

## Checklist Sebelum Deploy ke Production

- [ ] Ubah `APP_DEBUG=false`
- [ ] Generate production `APP_KEY`
- [ ] Setup `.env` dengan kredensial production (DB, Cloudinary, Evolution API)
- [ ] Run migration: `php artisan migrate --env=production`
- [ ] Run seeder: `php artisan db:seed`
- [ ] Test semua endpoint dengan token
- [ ] Setup CORS jika frontend di domain berbeda
- [ ] Enable HTTPS
- [ ] Monitor logs di `storage/logs/`

---

**Terakhir diupdate**: April 16, 2026  
**Versi**: 1.0 — Laravel 12, PostgreSQL, Sanctum, Cloudinary, Evolution API
