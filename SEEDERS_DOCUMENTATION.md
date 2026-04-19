# Database Seeders Documentation

## Overview

Seeder adalah fitur Laravel yang mengisi database dengan data dummy untuk keperluan testing dan development. Semua seeder telah dibuat dengan data yang realistis untuk aplikasi Mealjun.

## Seeder Statistics

| Seeder                    | Model           | Jumlah Data | Keterangan                                         |
| ------------------------- | --------------- | ----------- | -------------------------------------------------- |
| **UserSeeder**            | User            | 1 user      | Admin super_admin dengan email admin@mealjun.com   |
| **ProductSeeder**         | Product         | 8 produk    | Berbagai jenis kue (1 out_of_stock)                |
| **TestimonialSeeder**     | Testimonial     | 8           | Review dari berbagai pelanggan                     |
| **GalleryImageSeeder**    | GalleryImage    | 8           | Foto produk dengan caption                         |
| **StoreLocationSeeder**   | StoreLocation   | 8 lokasi    | Toko di berbagai kota Indonesia                    |
| **AboutInfoSeeder**       | AboutInfo       | 1 record    | Informasi tentang Mealjun                          |
| **ContactMessageSeeder**  | ContactMessage  | 7 pesan     | Pertanyaan dari pengunjung                         |
| **VisitorAnalyticSeeder** | VisitorAnalytic | 200 records | Data analytics pengunjung website                  |
| **CaptionTemplateSeeder** | CaptionTemplate | 3 template  | Template caption (friendly, professional, playful) |

**Total Data: 238 records**

---

## Detail Seeder

### 1. UserSeeder

**File:** `database/seeders/UserSeeder.php`

**Data:**

- Email: `admin@mealjun.com`
- Password: `password123`
- Full Name: `Admin Mealjun`
- Role: `super_admin`
- Status: `active`

**Usage:**

```bash
php artisan db:seed --class=UserSeeder
```

---

### 2. ProductSeeder

**File:** `database/seeders/ProductSeeder.php`

**Data (8 products):**

1. Chocolate Brownies - Rp 25.000 (available, featured)
2. Strawberry Cheesecake - Rp 35.000 (available, featured)
3. Vanilla Cupcake - Rp 15.000 (available)
4. Red Velvet Cake - Rp 45.000 (available, featured)
5. Matcha Latte Cake - Rp 38.000 (limited)
6. Cookies & Cream Donut - Rp 12.000 (available)
7. Carrot Cake - Rp 30.000 (available)
8. Tiramisu Cake - Rp 40.000 (out_of_stock)

**Fields Included:**

- name, flavor, description, price
- image_url (dari Unsplash)
- shopee_link, tiktok_link, whatsapp_link
- stock_status, is_featured, view_count
- created_by (referensi ke User)

---

### 3. TestimonialSeeder

**File:** `database/seeders/TestimonialSeeder.php`

**Data (8 testimonials):**

- Siti Nurhaliza - 5 stars (featured)
- Budi Santoso - 5 stars (featured)
- Rina Wijaya - 4 stars (featured)
- Ahmad Wijaya - 5 stars
- Dewi Putri - 5 stars
- Roni Saputra - 4 stars
- Nurul Aini - 5 stars
- Hendra Gunawan - 4 stars

**Fields:**

- customer_name, customer_location, rating
- review_text, customer_avatar
- is_featured, is_approved

---

### 4. GalleryImageSeeder

**File:** `database/seeders/GalleryImageSeeder.php`

**Data (8 gallery images):**

- Captions untuk setiap produk
- display_order dari 0-7
- is_published: true untuk semua
- image_url (dari Unsplash)
- created_by (referensi ke User)

---

### 5. StoreLocationSeeder

**File:** `database/seeders/StoreLocationSeeder.php`

**Data (8 lokasi toko):**

| No  | Toko                    | Tipe     | Kota       |
| --- | ----------------------- | -------- | ---------- |
| 1   | Mealjun Jakarta Pusat   | retail   | Jakarta    |
| 2   | Mealjun Jakarta Selatan | retail   | Jakarta    |
| 3   | Mealjun Bandung         | retail   | Bandung    |
| 4   | Mealjun Surabaya        | retail   | Surabaya   |
| 5   | Mealjun Medan           | reseller | Medan      |
| 6   | Mealjun Yogyakarta      | retail   | Yogyakarta |
| 7   | Mealjun Makassar        | reseller | Makassar   |
| 8   | Mealjun Bali            | retail   | Denpasar   |

**Fields:**

- store_name, store_type (retail/reseller)
- address, city, province, postal_code
- phone, latitude, longitude (untuk maps)
- is_active, created_by

---

### 6. AboutInfoSeeder

**File:** `database/seeders/AboutInfoSeeder.php`

**Data:**

- Title: `Tentang Mealjun`
- Description: Deskripsi lengkap tentang Mealjun
- Vision: Visi perusahaan
- Mission: Misi perusahaan
- Image: Dari Unsplash
- WhatsApp: `628123456789`
- Email: `info@mealjun.com`
- Address: Jakarta Pusat
- updated_by: Referensi ke User

---

### 7. ContactMessageSeeder

**File:** `database/seeders/ContactMessageSeeder.php`

**Data (7 pesan kontak):**

1. Ahmad Wijaya - Menjadi reseller (replied) ✅
2. Siti Nurhaliza - Custom cake (replied) ✅
3. Budi Santoso - Estimasi pengiriman (replied) ✅
4. Rina Wijaya - Diskon untuk bulk (pending)
5. Dewi Putri - Layanan katering (pending)
6. Roni Saputra - Subscription bulanan (replied) ✅
7. Nurul Aini - Cicilan (pending)

**Fields:**

- name, email, message
- is_read (true/false)
- replied_at, reply_message

---

### 8. VisitorAnalyticSeeder

**File:** `database/seeders/VisitorAnalyticSeeder.php`

**Data (200 records):**

- Random visitors dari 8 kota: Jakarta, Bandung, Surabaya, Medan, Yogyakarta, Semarang, Makassar, Denpasar
- Random pages: homepage, products, product_detail, testimonials, gallery, about, contact, store_locations
- Random dates: 0-30 hari yang lalu
- Random product_id: jika page adalah product_detail

**Fields:**

- visit_date, visitor_ip
- visitor_city, visitor_province, visitor_country
- page_viewed, product_id
- referrer_url, user_agent, session_id

---

### 9. CaptionTemplateSeeder

**File:** `database/seeders/CaptionTemplateSeeder.php`

**Data (3 templates):**

1. **Friendly Tone**

    ```
    😍 Hei teman-teman! Kenalin nih produk favorit kita: _{name}_ rasa {flavor}!
    ...
    ```

2. **Professional Tone**

    ```
    Kami dengan bangga mempersembahkan _{name}_ — varian {flavor} dari Mealjun.
    ...
    ```

3. **Playful Tone**
    ```
    🎊 STOP SCROLLING! 🛑
    Kamu belum coba {name} rasa {flavor}?!
    ...
    ```

**Placeholders:**

- {name} → Product name
- {flavor} → Product flavor
- {price} → Product price
- {description} → Product description

---

## Running Seeders

### Run Semua Seeder (Fresh Database)

```bash
php artisan migrate:fresh --seed
```

Ini akan:

1. Drop semua tables
2. Re-create semua tables dari migrations
3. Jalankan semua seeders

### Run Seeder Spesifik

```bash
# Run user seeder saja
php artisan db:seed --class=UserSeeder

# Run product seeder saja
php artisan db:seed --class=ProductSeeder

# Run multiple seeders
php artisan db:seed --class=ProductSeeder --class=TestimonialSeeder
```

### Append Seeder tanpa Reset Database

```bash
php artisan db:seed
```

---

## Development Tips

### 1. Menggunakan Seeder untuk Testing

```php
// Dalam test file
class ProductTest extends TestCase
{
    public function test_get_products()
    {
        $this->seed(ProductSeeder::class);
        $response = $this->getJson('/api/products');
        $this->assertCount(8, $response['data']);
    }
}
```

### 2. Membuat Data Berbeda dengan Faker

```php
use Faker\Factory as Faker;

$faker = Faker::create('id_ID');

Product::create([
    'name' => $faker->name(),
    'description' => $faker->paragraph(),
    'price' => $faker->numberBetween(10000, 100000),
]);
```

### 3. Seeding dengan Factory

```php
// Membuat seeder yang lebih fleksibel
Product::factory()
    ->count(50)
    ->create();
```

---

## Default Image

Semua seeder menggunakan default image dari Unsplash:

```
https://images.unsplash.com/photo-1599490659213-e2b9527bd087?w=600&h=600&fit=crop
```

Jika ingin mengubah image, ubah di setiap seeder:

```php
$imageUrl = 'https://your-image-url-here.jpg';
```

---

## Database Structure

```
├── users (1 admin)
├── products (8 items)
├── testimonials (8 reviews)
├── gallery_images (8 images)
├── store_locations (8 stores)
├── about_info (1 info)
├── contact_messages (7 messages)
├── visitor_analytics (200 records)
├── caption_templates (3 templates)
└── generated_captions (empty)
```

---

## Cleanup

Untuk menghapus semua data seeders:

```bash
# Rollback semua migrations (menghapus semua tables)
php artisan migrate:rollback

# Atau reset dengan fresh
php artisan migrate:fresh
```

---

## Notes

- ✅ Semua foreign keys sudah dikonfigurasi
- ✅ Data timestamps (created_at, updated_at) sudah diset
- ✅ Seeder berjalan dalam urutan yang benar (dependencies dihandle)
- ✅ Image URLs valid dan accessible
- ✅ Total 238 data records siap untuk development
- ✅ Support untuk public dan admin endpoints

---

**Last Updated:** April 18, 2026  
**Status:** Production Ready ✅
