# MEALJUN API - QUICK REFERENCE

## 📋 Endpoint Summary

### Authentication (3 endpoints)

- ✅ `POST /auth/login` - Login dan dapatkan token
- ✅ `POST /auth/logout` - Logout (protected)
- ✅ `GET /auth/me` - Get user info (protected)

### Products (7 endpoints)

- ✅ `GET /public/products` - List publik dengan search/filter
- ✅ `GET /public/products/{id}` - Detail produk publik
- ✅ `GET /products` - List admin (protected)
- ✅ `POST /products` - Create (protected)
- ✅ `GET /products/{id}` - Detail admin (protected)
- ✅ `PUT /products/{id}` - Update (protected)
- ✅ `DELETE /products/{id}` - Delete (protected)
- ✅ `PATCH /products/{id}/toggle-featured` - Toggle featured
- ✅ `PATCH /products/{id}/stock-status` - Update stock status

### Testimonials (8 endpoints)

- ✅ `GET /public/testimonials` - List publik
- ✅ `GET /testimonials` - List admin (protected)
- ✅ `POST /testimonials` - Create (protected)
- ✅ `GET /testimonials/{id}` - Detail (protected)
- ✅ `PUT /testimonials/{id}` - Update (protected)
- ✅ `DELETE /testimonials/{id}` - Delete (protected)
- ✅ `PATCH /testimonials/{id}/toggle-featured` - Toggle
- ✅ `PATCH /testimonials/{id}/toggle-approved` - Toggle

### Gallery Images (7 endpoints)

- ✅ `GET /public/gallery` - List publik
- ✅ `GET /gallery` - List admin (protected)
- ✅ `POST /gallery` - Create (protected)
- ✅ `GET /gallery/{id}` - Detail (protected)
- ✅ `PUT /gallery/{id}` - Update (protected)
- ✅ `DELETE /gallery/{id}` - Delete (protected)
- ✅ `POST /gallery/reorder` - Reorder images (protected)

### Store Locations (6 endpoints)

- ✅ `GET /public/store-locations` - List publik
- ✅ `GET /store-locations` - List admin (protected)
- ✅ `POST /store-locations` - Create (protected)
- ✅ `GET /store-locations/{id}` - Detail (protected)
- ✅ `PUT /store-locations/{id}` - Update (protected)
- ✅ `DELETE /store-locations/{id}` - Delete (protected)

### Caption Templates (5 endpoints)

- ✅ `GET /caption-templates` - List (protected)
- ✅ `POST /caption-templates` - Create (protected)
- ✅ `GET /caption-templates/{id}` - Detail (protected)
- ✅ `PUT /caption-templates/{id}` - Update (protected)
- ✅ `DELETE /caption-templates/{id}` - Delete (protected)

### Contact Messages (6 endpoints)

- ✅ `POST /public/contact` - Submit contact form
- ✅ `GET /contact-messages` - List (protected)
- ✅ `GET /contact-messages/{id}` - Detail (protected)
- ✅ `POST /contact-messages/{id}/reply` - Reply (protected)
- ✅ `PATCH /contact-messages/{id}/mark-as-read` - Mark read (protected)
- ✅ `DELETE /contact-messages/{id}` - Delete (protected)

### Generated Captions (4 endpoints)

- ✅ `GET /generated-captions` - List (protected)
- ✅ `POST /generated-captions/generate` - Generate (protected)
- ✅ `PATCH /generated-captions/{id}/copied` - Mark copied (protected)
- ✅ `DELETE /generated-captions/{id}` - Delete (protected)

### About Info (2 endpoints)

- ✅ `GET /public/about` - Get about (publik)
- ✅ `PUT /about` - Update about (protected)

### Dashboard & Analytics (3 endpoints)

- ✅ `GET /dashboard` - Dashboard summary (protected)
- ✅ `POST /public/analytics/track` - Track visitor
- ✅ `GET /analytics` - Analytics summary (protected)
- ✅ `GET /city-stats` - City stats (protected)

**Total: 64 Endpoints** ✅

---

## 🔐 Authentication

### Headers untuk Protected Endpoints

```
Authorization: Bearer {token}
Content-Type: application/json
```

### Cara Login

```bash
curl -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password123"
  }'
```

---

## 📝 Response Format

### Success (200/201)

```json
{
  "data": {
    "id": "uuid",
    "field": "value",
    ...
  }
}
```

### List (with pagination)

```json
{
  "data": [...],
  "limit": 20,
  "page": 1,
  "size": 15,
  "pages": 5
}
```

### Error (422)

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "field": ["Error message"]
    }
}
```

---

## 🖼️ Image Upload Format

Semua endpoint yang menerima image menggunakan base64:

```
data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEA...
data:image/png;base64,iVBORw0KGgoAAAANSUhEUg...
data:image/webp;base64,UklGRiQAAABXRUJQ...
data:image/gif;base64,R0lGODlhAQABAAAAACw=...
```

**Supported:** jpeg, png, webp, gif

---

## 🔍 Query Parameters

### Pagination

```
?limit=20&page=1
```

### Search

```
?value=chocolate
```

### Sorting

```
?order=price&sort=1       # ascending
?order=price&sort=-1      # descending
```

### Filtering

```
?stock_status=available
?is_read=true
?is_featured=false
```

### Combined

```
?limit=10&page=1&value=chocolate&order=price&sort=-1
```

---

## 📊 Common Fields

### Product

- `id`, `name`, `flavor`, `description`, `price`
- `image_url`, `stock_status`, `is_featured`
- `view_count`, `created_by`, `created_at`

### Testimonial

- `id`, `customer_name`, `customer_location`, `rating`
- `review_text`, `customer_avatar`, `is_featured`, `is_approved`

### Gallery Image

- `id`, `image_url`, `caption`, `display_order`, `is_published`

### Store Location

- `id`, `store_name`, `store_type`, `address`, `city`
- `phone`, `latitude`, `longitude`, `is_active`

### Contact Message

- `id`, `name`, `email`, `message`
- `reply_message`, `is_read`, `replied_at`

---

## 🧪 Contoh Request JavaScript

### Login

```javascript
const res = await fetch("http://127.0.0.1:8000/api/auth/login", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
        email: "admin@example.com",
        password: "password123",
    }),
});
const data = await res.json();
const token = data.token;
```

### Get Products

```javascript
const res = await fetch("http://127.0.0.1:8000/api/public/products?limit=10");
const { data } = await res.json();
```

### Create Product

```javascript
const res = await fetch("http://127.0.0.1:8000/api/products", {
    method: "POST",
    headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
    },
    body: JSON.stringify({
        name: "New Product",
        flavor: "Chocolate",
        description: "Delicious",
        price: 25000,
        image_base64: "data:image/png;base64,...",
        stock_status: "available",
    }),
});
const { data: product } = await res.json();
```

### Update Product

```javascript
const res = await fetch(`http://127.0.0.1:8000/api/products/${id}`, {
    method: "PUT",
    headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
    },
    body: JSON.stringify({
        name: "Updated Name",
        price: 30000,
    }),
});
```

### Delete Product

```javascript
await fetch(`http://127.0.0.1:8000/api/products/${id}`, {
    method: "DELETE",
    headers: { Authorization: `Bearer ${token}` },
});
```

---

## ⚠️ HTTP Status Codes

| Code | Arti                                  |
| ---- | ------------------------------------- |
| 200  | ✅ Berhasil (GET, PUT, PATCH, DELETE) |
| 201  | ✅ Dibuat (POST berhasil)             |
| 400  | ❌ Request salah                      |
| 401  | ❌ Belum login                        |
| 403  | ❌ Tidak diizinkan                    |
| 404  | ❌ Tidak ditemukan                    |
| 422  | ❌ Validasi gagal                     |
| 500  | ❌ Error server                       |

---

## 🎯 Validasi Rules

### Required Fields (wajib)

- Berisi value tidak boleh kosong

### String

- Tipe: text
- Max length: ditunjukkan dalam docs

### Integer

- Tipe: angka bulat
- Range: min-max jika ada

### UUID

- Format: `xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx`
- Harus ada di database (exists rule)

### Email

- Format valid email: `user@example.com`

### URL

- Format valid URL: `https://example.com`

### Base64 Image

- Harus: `data:image/{type};base64,{data}`
- Type: jpeg, png, webp, gif

### Boolean

- Value: true atau false

### In (enum)

- Harus salah satu dari pilihan yang diberikan

---

## 📌 Tips Penting

1. **Selalu include token** di header untuk protected endpoints
2. **Base64 image** harus lengkap dengan prefix `data:image/`
3. **UUID** harus dalam format benar
4. **Pagination default**: limit=20, page=1
5. **Sorting**: sort=1 (asc), sort=-1 (desc)
6. **Error response** akan memberikan detail field mana yang error
7. **Search** hanya di field yang ditunjukkan dalam docs
8. **Filter** hanya di field yang disediakan

---

## 🚀 Environment

```
Base URL: http://127.0.0.1:8000/api
Protocol: HTTP/HTTPS
Content-Type: application/json
Charset: UTF-8
```

---

**Dokumentasi Lengkap:** Lihat file `API_DOCUMENTATION.md`
