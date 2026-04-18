# Custom Pagination Format - Usage Guide

## Format Response

Semua endpoint dengan pagination sekarang mengembalikan format berikut:

```json
{
    "data": [
        {
            /* item data */
        },
        {
            /* item data */
        }
    ],
    "limit": 10,
    "page": 1,
    "size": 2,
    "pages": 5
}
```

**Field Description:**

- `data`: Array berisi items dari halaman saat ini
- `limit`: Jumlah item per halaman
- `page`: Halaman saat ini (mulai dari 1)
- `size`: Total item di halaman saat ini
- `pages`: Total halaman yang tersedia

---

## Query Parameters

### Basic Parameters

- `limit`: Jumlah item per page (default: 10-20 tergantung endpoint)
- `page`: Halaman yang ditampilkan (default: 1)

### Sorting Parameters

- `order`: Field name untuk sorting
    - Contoh: `order=name`, `order=created_at`, `order=price`
    - Available fields tergantung resource
- `sort`: Arah sorting
    - `-1` = Descending (Terbaru/Terbesar)
    - `1` = Ascending (Terlama/Terkecil)

### Search Parameter

- `value`: Nilai pencarian (akan search di multiple columns)

---

## Contoh Request

### 1. Basic Pagination

```bash
GET /api/testimonials?limit=10&page=1
```

### 2. Dengan Sorting

```bash
# Sorting by customer_name descending (terbaru)
GET /api/testimonials?limit=10&page=1&order=customer_name&sort=-1

# Sorting by created_at ascending (terlama)
GET /api/testimonials?limit=10&page=1&order=created_at&sort=1
```

### 3. Dengan Search

```bash
# Search value "Budi" di multiple columns
GET /api/testimonials?limit=10&page=1&value=Budi
```

### 4. Kombinasi Semua Parameter

```bash
# Cari "Budi", sort by name descending, limit 5, page 1
GET /api/testimonials?limit=5&page=1&order=customer_name&sort=-1&value=Budi
```

---

## Endpoints yang Sudah Diupdate

### Admin Endpoints (memerlukan authentication)

1. **Products**
    - `GET /api/products` - List all products
    - Searchable: name, flavor, description
2. **Testimonials**
    - `GET /api/testimonials` - List all testimonials
    - Searchable: customer_name, customer_location, review_text

3. **Gallery**
    - `GET /api/gallery` - List all gallery images
    - Searchable: caption

4. **Contact Messages**
    - `GET /api/contact-messages` - List all messages
    - Searchable: name, email, message

5. **Generated Captions**
    - `GET /api/generated-captions` - List user's captions
    - Searchable: product_name, generated_text

6. **Store Locations**
    - `GET /api/store-locations` - List all locations
    - Searchable: store_name, city, address, phone

### Public Endpoints (tanpa authentication)

1. **Products**
    - `GET /api/public/products` - List available products
    - Searchable: name, flavor, description

---

## Catatan

- Semua endpoint yang sudah diupdate sudah support semua 4 parameter sekaligus
- Search akan otomatis di-combine dengan filter lainnya (misal: `is_read`, `stock_status`)
- Default sorting bisa di-override dengan parameter `order` dan `sort`
- Jika tidak ada parameter sorting, akan menggunakan sorting default masing-masing endpoint
