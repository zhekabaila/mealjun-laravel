# Mealjun API Documentation

## Base URL

```
http://127.0.0.1:8000/api
```

## Authentication

- **Type**: Bearer Token (Sanctum)
- **Header**: `Authorization: Bearer {token}`
- **How to Get Token**: POST /api/auth/login

### Public vs Protected Endpoints

- ✅ **Public**: No authentication required
- 🔐 **Protected**: Requires valid Bearer token
- 👨‍💼 **Admin Only**: Requires token with admin role

---

# 1. AUTHENTICATION ENDPOINTS

## 1.1 Login

**POST** `/auth/login`

- **Type**: Public
- **Description**: Authenticate user and get API token

### Request

```json
{
    "email": "admin@example.com",
    "password": "password123"
}
```

### Response (200 OK)

```json
{
    "message": "Login berhasil",
    "token": "019da01c-7707-7165-87f5-1f0acd47b9f1|NTcQy2oiuce5t7FMOTKQ5hYCihBo9CRVthi14YC543af3268",
    "user": {
        "id": "019d9526-132c-722b-b98c-bec7c1e40387",
        "email": "admin@example.com",
        "full_name": "Admin User",
        "role": "admin"
    }
}
```

### Response (422 Unprocessable Entity)

```json
{
    "message": "Email atau password salah.",
    "errors": {
        "email": ["Email atau password salah."]
    }
}
```

### Validation Rules

- `email`: required, valid email format
- `password`: required, string

---

## 1.2 Logout

**POST** `/auth/logout`

- **Type**: Protected 🔐
- **Description**: Logout user by deleting current token

### Headers

```
Authorization: Bearer {token}
```

### Response (200 OK)

```json
{
    "message": "Logout berhasil"
}
```

---

## 1.3 Get Current User

**GET** `/auth/me`

- **Type**: Protected 🔐
- **Description**: Get authenticated user details

### Headers

```
Authorization: Bearer {token}
```

### Response (200 OK)

```json
{
    "id": "019d9526-132c-722b-b98c-bec7c1e40387",
    "full_name": "Admin User",
    "email": "admin@example.com",
    "email_verified_at": "2026-04-10T10:00:00.000000Z",
    "is_active": true,
    "role": "admin",
    "last_login": "2026-04-18T11:00:00.000000Z",
    "created_at": "2026-03-01T10:00:00.000000Z",
    "updated_at": "2026-04-18T11:00:00.000000Z"
}
```

---

# 2. PRODUCT ENDPOINTS

## 2.1 List Products (Public)

**GET** `/public/products`

- **Type**: Public ✅
- **Description**: Get list of available products with pagination and filtering

### Query Parameters

| Parameter | Type    | Default | Description                                      |
| --------- | ------- | ------- | ------------------------------------------------ |
| `limit`   | integer | 12      | Items per page                                   |
| `page`    | integer | 1       | Page number                                      |
| `flavor`  | string  | -       | Filter by flavor                                 |
| `order`   | string  | -       | Sort field (name, price, created_at, view_count) |
| `sort`    | integer | -       | Sort direction (1 = asc, -1 = desc)              |
| `value`   | string  | -       | Search in name, flavor, description              |

### Response (200 OK)

```json
{
    "data": [
        {
            "id": "019da009-1234-5678-abcd-ef1234567890",
            "name": "Chocolate Brownies",
            "flavor": "Chocolate",
            "description": "Rich and fudgy chocolate brownies",
            "price": 25000,
            "image_url": "https://res.cloudinary.com/...",
            "shopee_link": "https://shopee.co.id/...",
            "tiktok_link": "https://tiktok.com/...",
            "whatsapp_link": "https://wa.me/...",
            "stock_status": "available",
            "is_featured": true,
            "view_count": 150,
            "created_at": "2026-04-10T10:00:00.000000Z",
            "updated_at": "2026-04-18T11:00:00.000000Z"
        }
    ],
    "limit": 12,
    "page": 1,
    "size": 1,
    "pages": 1
}
```

### Example Requests

```bash
# Get first 10 products
curl "http://127.0.0.1:8000/api/public/products?limit=10&page=1"

# Search products
curl "http://127.0.0.1:8000/api/public/products?value=chocolate"

# Filter by flavor and sort by price descending
curl "http://127.0.0.1:8000/api/public/products?flavor=Vanilla&order=price&sort=-1"
```

---

## 2.2 Get Product Detail (Public)

**GET** `/public/products/{id}`

- **Type**: Public ✅
- **Description**: Get single product detail (increments view count)

### Path Parameters

| Parameter | Type | Description |
| --------- | ---- | ----------- |
| `id`      | UUID | Product ID  |

### Response (200 OK)

```json
{
    "data": {
        "id": "019da009-1234-5678-abcd-ef1234567890",
        "name": "Chocolate Brownies",
        "flavor": "Chocolate",
        "description": "Rich and fudgy chocolate brownies",
        "price": 25000,
        "image_url": "https://res.cloudinary.com/...",
        "shopee_link": "https://shopee.co.id/...",
        "tiktok_link": "https://tiktok.com/...",
        "whatsapp_link": "https://wa.me/...",
        "stock_status": "available",
        "is_featured": true,
        "view_count": 151,
        "created_at": "2026-04-10T10:00:00.000000Z",
        "updated_at": "2026-04-18T11:00:00.000000Z"
    }
}
```

### Response (404 Not Found)

```json
{
    "message": "No query results for model [App\\Models\\Product] 019da009-invalid"
}
```

---

## 2.3 List Products (Admin)

**GET** `/products`

- **Type**: Protected 🔐
- **Description**: Get all products with admin filtering options

### Query Parameters

| Parameter      | Type    | Default | Description                               |
| -------------- | ------- | ------- | ----------------------------------------- |
| `limit`        | integer | 15      | Items per page                            |
| `page`         | integer | 1       | Page number                               |
| `stock_status` | string  | -       | Filter (available, limited, out_of_stock) |
| `order`        | string  | -       | Sort field                                |
| `sort`         | integer | -       | Sort direction                            |
| `value`        | string  | -       | Search query                              |

### Headers

```
Authorization: Bearer {token}
```

### Response (200 OK)

```json
{
    "data": [
        {
            "id": "019da009-1234-5678-abcd-ef1234567890",
            "name": "Chocolate Brownies",
            "flavor": "Chocolate",
            "description": "Rich and fudgy chocolate brownies",
            "price": 25000,
            "image_url": "https://res.cloudinary.com/...",
            "shopee_link": "https://shopee.co.id/...",
            "tiktok_link": "https://tiktok.com/...",
            "whatsapp_link": "https://wa.me/...",
            "stock_status": "available",
            "is_featured": true,
            "view_count": 150,
            "created_by": "019d9526-132c-722b-b98c-bec7c1e40387",
            "created_at": "2026-04-10T10:00:00.000000Z",
            "updated_at": "2026-04-18T11:00:00.000000Z"
        }
    ],
    "limit": 15,
    "page": 1,
    "size": 1,
    "pages": 1
}
```

---

## 2.4 Create Product

**POST** `/products`

- **Type**: Protected 🔐
- **Description**: Create new product with image

### Headers

```
Authorization: Bearer {token}
Content-Type: application/json
```

### Request Body

```json
{
    "name": "Strawberry Cheesecake",
    "flavor": "Strawberry",
    "description": "Creamy cheesecake with fresh strawberry topping",
    "price": 35000,
    "image_base64": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==",
    "shopee_link": "https://shopee.co.id/product/123456",
    "tiktok_link": "https://tiktok.com/@mealjun/video/123456",
    "whatsapp_link": "https://wa.me/6281234567890",
    "stock_status": "available",
    "is_featured": false
}
```

### Response (201 Created)

```json
{
    "data": {
        "name": "Strawberry Cheesecake",
        "flavor": "Strawberry",
        "description": "Creamy cheesecake with fresh strawberry topping",
        "price": 35000,
        "image_url": "https://res.cloudinary.com/dctqloe37/image/upload/v1776511358/mealjun/products/abc123def.png",
        "shopee_link": "https://shopee.co.id/product/123456",
        "tiktok_link": "https://tiktok.com/@mealjun/video/123456",
        "whatsapp_link": "https://wa.me/6281234567890",
        "stock_status": "available",
        "is_featured": false,
        "view_count": 0,
        "created_by": "019d9526-132c-722b-b98c-bec7c1e40387",
        "id": "019da042-1234-5678-abcd-ef1234567890",
        "created_at": "2026-04-18T11:05:00.000000Z",
        "updated_at": "2026-04-18T11:05:00.000000Z"
    }
}
```

### Validation Rules

- `name`: required, string, max 255 chars
- `flavor`: required, string, max 100 chars
- `description`: required, string
- `price`: required, numeric, min 0
- `image_base64`: required, base64 (jpeg, png, webp, gif)
- `shopee_link`: nullable, valid URL
- `tiktok_link`: nullable, valid URL
- `whatsapp_link`: nullable, valid URL
- `stock_status`: required, in (available, limited, out_of_stock)
- `is_featured`: boolean

### Response (422 Unprocessable Entity)

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "name": ["The name field is required."],
        "image_base64": [
            "The image_base64 must be a valid base64 image (jpeg, png, webp, or gif)."
        ]
    }
}
```

---

## 2.5 Get Product Detail (Admin)

**GET** `/products/{id}`

- **Type**: Protected 🔐
- **Description**: Get single product with creator info

### Headers

```
Authorization: Bearer {token}
```

### Response (200 OK)

```json
{
    "data": {
        "id": "019da009-1234-5678-abcd-ef1234567890",
        "name": "Chocolate Brownies",
        "flavor": "Chocolate",
        "description": "Rich and fudgy chocolate brownies",
        "price": 25000,
        "image_url": "https://res.cloudinary.com/...",
        "shopee_link": "https://shopee.co.id/...",
        "tiktok_link": "https://tiktok.com/...",
        "whatsapp_link": "https://wa.me/...",
        "stock_status": "available",
        "is_featured": true,
        "view_count": 150,
        "created_by": "019d9526-132c-722b-b98c-bec7c1e40387",
        "creator": {
            "id": "019d9526-132c-722b-b98c-bec7c1e40387",
            "full_name": "Admin User",
            "email": "admin@example.com"
        },
        "created_at": "2026-04-10T10:00:00.000000Z",
        "updated_at": "2026-04-18T11:00:00.000000Z"
    }
}
```

---

## 2.6 Update Product

**PUT** `/products/{id}`

- **Type**: Protected 🔐
- **Description**: Update product (all fields optional)

### Headers

```
Authorization: Bearer {token}
Content-Type: application/json
```

### Request Body (All Optional)

```json
{
    "name": "Updated Product Name",
    "flavor": "New Flavor",
    "description": "Updated description",
    "price": 30000,
    "image_base64": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==",
    "shopee_link": "https://shopee.co.id/product/updated",
    "tiktok_link": "https://tiktok.com/@mealjun/video/updated",
    "whatsapp_link": "https://wa.me/6289876543210",
    "stock_status": "limited",
    "is_featured": true
}
```

### Response (200 OK)

```json
{
    "data": {
        "id": "019da009-1234-5678-abcd-ef1234567890",
        "name": "Updated Product Name",
        "flavor": "New Flavor",
        "description": "Updated description",
        "price": 30000,
        "image_url": "https://res.cloudinary.com/...",
        "shopee_link": "https://shopee.co.id/product/updated",
        "tiktok_link": "https://tiktok.com/@mealjun/video/updated",
        "whatsapp_link": "https://wa.me/6289876543210",
        "stock_status": "limited",
        "is_featured": true,
        "view_count": 150,
        "created_by": "019d9526-132c-722b-b98c-bec7c1e40387",
        "created_at": "2026-04-10T10:00:00.000000Z",
        "updated_at": "2026-04-18T11:15:00.000000Z"
    }
}
```

---

## 2.7 Delete Product

**DELETE** `/products/{id}`

- **Type**: Protected 🔐
- **Description**: Delete product

### Headers

```
Authorization: Bearer {token}
```

### Response (200 OK)

```json
{
    "message": "Produk berhasil dihapus"
}
```

---

## 2.8 Toggle Product Featured Status

**PATCH** `/products/{id}/toggle-featured`

- **Type**: Protected 🔐
- **Description**: Toggle product featured status

### Headers

```
Authorization: Bearer {token}
```

### Response (200 OK)

```json
{
    "id": "019da009-1234-5678-abcd-ef1234567890",
    "is_featured": false,
    "updated_at": "2026-04-18T11:20:00.000000Z"
}
```

---

## 2.9 Update Product Stock Status

**PATCH** `/products/{id}/stock-status`

- **Type**: Protected 🔐
- **Description**: Update product stock status

### Headers

```
Authorization: Bearer {token}
Content-Type: application/json
```

### Request Body

```json
{
    "stock_status": "limited"
}
```

### Response (200 OK)

```json
{
    "id": "019da009-1234-5678-abcd-ef1234567890",
    "stock_status": "limited",
    "updated_at": "2026-04-18T11:25:00.000000Z"
}
```

### Validation Rules

- `stock_status`: required, in (available, limited, out_of_stock)

---

# 3. TESTIMONIAL ENDPOINTS

## 3.1 List Testimonials (Public)

**GET** `/public/testimonials`

- **Type**: Public ✅
- **Description**: Get approved testimonials ordered by featured and date

### Response (200 OK)

```json
[
    {
        "id": "019da034-b8b1-730c-895b-a605e23e5673",
        "customer_name": "John Doe",
        "customer_location": "Jakarta",
        "rating": 5,
        "review_text": "Produk sangat bagus dan memuaskan!",
        "customer_avatar": "https://res.cloudinary.com/...",
        "is_featured": true,
        "is_approved": true,
        "created_at": "2026-04-18T10:48:22.000000Z",
        "updated_at": "2026-04-18T10:48:22.000000Z"
    }
]
```

---

## 3.2 List Testimonials (Admin)

**GET** `/testimonials`

- **Type**: Protected 🔐
- **Description**: Get all testimonials with pagination and filtering

### Query Parameters

| Parameter | Type    | Default | Description                                   |
| --------- | ------- | ------- | --------------------------------------------- |
| `limit`   | integer | 20      | Items per page                                |
| `page`    | integer | 1       | Page number                                   |
| `order`   | string  | -       | Sort field                                    |
| `sort`    | integer | -       | Sort direction                                |
| `value`   | string  | -       | Search (customer_name, location, review_text) |

### Response (200 OK)

```json
{
    "data": [
        {
            "id": "019da034-b8b1-730c-895b-a605e23e5673",
            "customer_name": "John Doe",
            "customer_location": "Jakarta",
            "rating": 5,
            "review_text": "Produk sangat bagus dan memuaskan!",
            "customer_avatar": "https://res.cloudinary.com/...",
            "is_featured": true,
            "is_approved": true,
            "created_at": "2026-04-18T10:48:22.000000Z",
            "updated_at": "2026-04-18T10:48:22.000000Z"
        }
    ],
    "limit": 20,
    "page": 1,
    "size": 1,
    "pages": 1
}
```

---

## 3.3 Create Testimonial

**POST** `/testimonials`

- **Type**: Protected 🔐
- **Description**: Create new testimonial with optional avatar

### Headers

```
Authorization: Bearer {token}
Content-Type: application/json
```

### Request Body

```json
{
    "customer_name": "Jane Smith",
    "customer_location": "Bandung",
    "rating": 4,
    "review_text": "Makanannya enak dan berkualitas tinggi!",
    "avatar_base64": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==",
    "is_featured": false,
    "is_approved": true
}
```

### Response (201 Created)

```json
{
    "data": {
        "customer_name": "Jane Smith",
        "customer_location": "Bandung",
        "rating": 4,
        "review_text": "Makanannya enak dan berkualitas tinggi!",
        "customer_avatar": "https://res.cloudinary.com/dctqloe37/image/upload/v1776511358/mealjun/avatars/q8d9vbdx9vdwrshag4r9.png",
        "is_featured": false,
        "is_approved": true,
        "id": "019da042-1c5f-7340-a8b3-743ec90faf1e",
        "created_at": "2026-04-18T11:03:00.000000Z",
        "updated_at": "2026-04-18T11:03:00.000000Z"
    }
}
```

### Validation Rules

- `customer_name`: required, string, max 255 chars
- `customer_location`: required, string, max 255 chars
- `rating`: required, integer, 1-5
- `review_text`: required, string
- `avatar_base64`: nullable, base64 (jpeg, png, webp, gif)
- `is_featured`: boolean
- `is_approved`: boolean

---

## 3.4 Get Testimonial Detail (Admin)

**GET** `/testimonials/{id}`

- **Type**: Protected 🔐

### Response (200 OK)

```json
{
    "data": {
        "id": "019da042-1c5f-7340-a8b3-743ec90faf1e",
        "customer_name": "Jane Smith",
        "customer_location": "Bandung",
        "rating": 4,
        "review_text": "Makanannya enak dan berkualitas tinggi!",
        "customer_avatar": "https://res.cloudinary.com/...",
        "is_featured": false,
        "is_approved": true,
        "created_at": "2026-04-18T11:03:00.000000Z",
        "updated_at": "2026-04-18T11:03:00.000000Z"
    }
}
```

---

## 3.5 Update Testimonial

**PUT** `/testimonials/{id}`

- **Type**: Protected 🔐

### Request Body (All Optional)

```json
{
    "customer_name": "Jane Updated",
    "customer_location": "Surabaya",
    "rating": 5,
    "review_text": "Updated review text",
    "avatar_base64": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==",
    "is_featured": true,
    "is_approved": true
}
```

### Response (200 OK)

```json
{
    "data": {
        "id": "019da042-1c5f-7340-a8b3-743ec90faf1e",
        "customer_name": "Jane Updated",
        "customer_location": "Surabaya",
        "rating": 5,
        "review_text": "Updated review text",
        "customer_avatar": "https://res.cloudinary.com/...",
        "is_featured": true,
        "is_approved": true,
        "created_at": "2026-04-18T11:03:00.000000Z",
        "updated_at": "2026-04-18T11:30:00.000000Z"
    }
}
```

---

## 3.6 Delete Testimonial

**DELETE** `/testimonials/{id}`

- **Type**: Protected 🔐

### Response (200 OK)

```json
{
    "message": "Testimonial berhasil dihapus"
}
```

---

## 3.7 Toggle Testimonial Featured

**PATCH** `/testimonials/{id}/toggle-featured`

- **Type**: Protected 🔐

### Response (200 OK)

```json
{
    "id": "019da042-1c5f-7340-a8b3-743ec90faf1e",
    "is_featured": false
}
```

---

## 3.8 Toggle Testimonial Approved

**PATCH** `/testimonials/{id}/toggle-approved`

- **Type**: Protected 🔐

### Response (200 OK)

```json
{
    "id": "019da042-1c5f-7340-a8b3-743ec90faf1e",
    "is_approved": false
}
```

---

# 4. GALLERY IMAGE ENDPOINTS

## 4.1 List Gallery Images (Public)

**GET** `/public/gallery`

- **Type**: Public ✅
- **Description**: Get published gallery images ordered by display order

### Response (200 OK)

```json
[
    {
        "id": "019da054-82ad-7250-944e-c050bfcc4922",
        "image_url": "https://res.cloudinary.com/...",
        "caption": "Gallery Image 1",
        "display_order": 0,
        "is_published": true,
        "created_at": "2026-04-18T11:10:00.000000Z",
        "updated_at": "2026-04-18T11:30:00.000000Z"
    }
]
```

---

## 4.2 List Gallery Images (Admin)

**GET** `/gallery`

- **Type**: Protected 🔐
- **Description**: Get all gallery images with pagination

### Query Parameters

| Parameter | Type    | Default | Description       |
| --------- | ------- | ------- | ----------------- |
| `limit`   | integer | 20      | Items per page    |
| `page`    | integer | 1       | Page number       |
| `order`   | string  | -       | Sort field        |
| `sort`    | integer | -       | Sort direction    |
| `value`   | string  | -       | Search in caption |

### Response (200 OK)

```json
{
    "data": [
        {
            "id": "019da054-82ad-7250-944e-c050bfcc4922",
            "image_url": "https://res.cloudinary.com/...",
            "caption": "Gallery Image 1",
            "display_order": 0,
            "is_published": true,
            "created_by": "019d9526-132c-722b-b98c-bec7c1e40387",
            "creator": {
                "id": "019d9526-132c-722b-b98c-bec7c1e40387",
                "full_name": "Admin User"
            },
            "created_at": "2026-04-18T11:10:00.000000Z",
            "updated_at": "2026-04-18T11:30:00.000000Z"
        }
    ],
    "limit": 20,
    "page": 1,
    "size": 1,
    "pages": 1
}
```

---

## 4.3 Create Gallery Image

**POST** `/gallery`

- **Type**: Protected 🔐

### Request Body

```json
{
    "image_base64": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==",
    "caption": "Test gallery image",
    "display_order": 0,
    "is_published": true
}
```

### Response (201 Created)

```json
{
    "data": {
        "image_url": "https://res.cloudinary.com/dctqloe37/image/upload/v1776511385/mealjun/gallery/jk6pjnimdv1lzxp5dopb.png",
        "caption": "Test gallery image",
        "display_order": 0,
        "is_published": true,
        "created_by": "019d9526-132c-722b-b98c-bec7c1e40387",
        "id": "019da054-82ad-7250-944e-c050bfcc4922",
        "created_at": "2026-04-18T11:10:00.000000Z",
        "updated_at": "2026-04-18T11:10:00.000000Z"
    }
}
```

### Validation Rules

- `image_base64`: required, base64 (jpeg, png, webp, gif)
- `caption`: required, string, max 255 chars
- `display_order`: integer, min 0
- `is_published`: boolean

---

## 4.4 Get Gallery Image Detail (Admin)

**GET** `/gallery/{id}`

- **Type**: Protected 🔐

### Response (200 OK)

```json
{
    "data": {
        "id": "019da054-82ad-7250-944e-c050bfcc4922",
        "image_url": "https://res.cloudinary.com/...",
        "caption": "Test gallery image",
        "display_order": 0,
        "is_published": true,
        "created_by": "019d9526-132c-722b-b98c-bec7c1e40387",
        "creator": {
            "id": "019d9526-132c-722b-b98c-bec7c1e40387",
            "full_name": "Admin User"
        },
        "created_at": "2026-04-18T11:10:00.000000Z",
        "updated_at": "2026-04-18T11:10:00.000000Z"
    }
}
```

---

## 4.5 Update Gallery Image

**PUT** `/gallery/{id}`

- **Type**: Protected 🔐

### Request Body (All Optional)

```json
{
    "image_base64": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==",
    "caption": "Updated caption",
    "display_order": 1,
    "is_published": false
}
```

### Response (200 OK)

```json
{
    "data": {
        "id": "019da054-82ad-7250-944e-c050bfcc4922",
        "image_url": "https://res.cloudinary.com/dctqloe37/image/upload/v1776511659/mealjun/gallery/kqtp4j0dkmqe4u6crh1n.png",
        "caption": "Updated caption",
        "display_order": 1,
        "is_published": false,
        "created_by": "019d9526-132c-722b-b98c-bec7c1e40387",
        "created_at": "2026-04-18T11:10:00.000000Z",
        "updated_at": "2026-04-18T11:35:00.000000Z"
    }
}
```

---

## 4.6 Delete Gallery Image

**DELETE** `/gallery/{id}`

- **Type**: Protected 🔐

### Response (200 OK)

```json
{
    "message": "Gambar galeri berhasil dihapus"
}
```

---

## 4.7 Reorder Gallery Images

**POST** `/gallery/reorder`

- **Type**: Protected 🔐
- **Description**: Update display order for multiple gallery images

### Request Body

```json
{
    "order": [
        {
            "id": "019da054-82ad-7250-944e-c050bfcc4922",
            "display_order": 0
        },
        {
            "id": "019da054-82ad-7250-944e-c050bfcc4923",
            "display_order": 1
        }
    ]
}
```

### Response (200 OK)

```json
{
    "message": "Urutan galeri berhasil diperbarui"
}
```

### Validation Rules

- `order`: required, array
- `order.*.id`: required, uuid, exists in gallery_images
- `order.*.display_order`: required, integer, min 0

---

# 5. STORE LOCATION ENDPOINTS

## 5.1 List Store Locations (Public)

**GET** `/public/store-locations`

- **Type**: Public ✅
- **Description**: Get active store locations with filtering

### Query Parameters

| Parameter    | Type   | Description                            |
| ------------ | ------ | -------------------------------------- |
| `city`       | string | Filter by city name (case insensitive) |
| `store_type` | string | Filter by type (retail, reseller)      |

### Response (200 OK)

```json
[
    {
        "id": "019da035-399b-7153-b150-9fac7c04cf79",
        "store_name": "Test Store 123",
        "store_type": "retail",
        "address": "Jl Test",
        "city": "Jakarta",
        "province": null,
        "postal_code": null,
        "phone": "081234567890",
        "latitude": null,
        "longitude": null,
        "is_active": true,
        "created_at": "2026-04-18T10:48:55.000000Z",
        "updated_at": "2026-04-18T10:48:55.000000Z"
    }
]
```

---

## 5.2 List Store Locations (Admin)

**GET** `/store-locations`

- **Type**: Protected 🔐

### Query Parameters

| Parameter | Type    | Default | Description    |
| --------- | ------- | ------- | -------------- |
| `limit`   | integer | 20      | Items per page |
| `page`    | integer | 1       | Page number    |
| `order`   | string  | -       | Sort field     |
| `sort`    | integer | -       | Sort direction |
| `value`   | string  | -       | Search         |

### Response (200 OK)

```json
{
    "data": [
        {
            "id": "019da035-399b-7153-b150-9fac7c04cf79",
            "store_name": "Test Store 123",
            "store_type": "retail",
            "address": "Jl Test",
            "city": "Jakarta",
            "province": null,
            "postal_code": null,
            "phone": "081234567890",
            "latitude": null,
            "longitude": null,
            "is_active": true,
            "created_by": "019d9526-132c-722b-b98c-bec7c1e40387",
            "created_at": "2026-04-18T10:48:55.000000Z",
            "updated_at": "2026-04-18T10:48:55.000000Z"
        }
    ],
    "limit": 20,
    "page": 1,
    "size": 1,
    "pages": 1
}
```

---

## 5.3 Create Store Location

**POST** `/store-locations`

- **Type**: Protected 🔐

### Request Body

```json
{
    "store_name": "Mealjun Downtown",
    "store_type": "retail",
    "address": "Jl Merdeka No. 123",
    "city": "Jakarta",
    "province": "DKI Jakarta",
    "postal_code": "12345",
    "phone": "+62812345678",
    "latitude": -6.2088,
    "longitude": 106.8456,
    "is_active": true
}
```

### Response (201 Created)

```json
{
    "data": {
        "store_name": "Mealjun Downtown",
        "store_type": "retail",
        "address": "Jl Merdeka No. 123",
        "city": "Jakarta",
        "province": "DKI Jakarta",
        "postal_code": "12345",
        "phone": "+62812345678",
        "latitude": -6.2088,
        "longitude": 106.8456,
        "is_active": true,
        "created_by": "019d9526-132c-722b-b98c-bec7c1e40387",
        "id": "019da035-9999-7153-b150-9fac7c04cf99",
        "created_at": "2026-04-18T11:40:00.000000Z",
        "updated_at": "2026-04-18T11:40:00.000000Z"
    }
}
```

### Validation Rules

- `store_name`: required, string, max 255 chars
- `store_type`: required, in (retail, reseller)
- `address`: required, string
- `city`: required, string, max 100 chars
- `province`: nullable, string, max 100 chars
- `postal_code`: nullable, string, max 20 chars
- `phone`: required, string, max 20 chars
- `latitude`: nullable, numeric, -90 to 90
- `longitude`: nullable, numeric, -180 to 180
- `is_active`: boolean

---

## 5.4 Get Store Location Detail (Admin)

**GET** `/store-locations/{id}`

- **Type**: Protected 🔐

### Response (200 OK)

```json
{
    "data": {
        "id": "019da035-9999-7153-b150-9fac7c04cf99",
        "store_name": "Mealjun Downtown",
        "store_type": "retail",
        "address": "Jl Merdeka No. 123",
        "city": "Jakarta",
        "province": "DKI Jakarta",
        "postal_code": "12345",
        "phone": "+62812345678",
        "latitude": -6.2088,
        "longitude": 106.8456,
        "is_active": true,
        "created_by": "019d9526-132c-722b-b98c-bec7c1e40387",
        "created_at": "2026-04-18T11:40:00.000000Z",
        "updated_at": "2026-04-18T11:40:00.000000Z"
    }
}
```

---

## 5.5 Update Store Location

**PUT** `/store-locations/{id}`

- **Type**: Protected 🔐

### Request Body (All Optional)

```json
{
    "store_name": "Mealjun Updated",
    "store_type": "reseller",
    "address": "Jl Baru No. 456",
    "city": "Bandung",
    "phone": "+62823456789",
    "is_active": false
}
```

### Response (200 OK)

```json
{
    "data": {
        "id": "019da035-9999-7153-b150-9fac7c04cf99",
        "store_name": "Mealjun Updated",
        "store_type": "reseller",
        "address": "Jl Baru No. 456",
        "city": "Bandung",
        "province": "DKI Jakarta",
        "postal_code": "12345",
        "phone": "+62823456789",
        "latitude": -6.2088,
        "longitude": 106.8456,
        "is_active": false,
        "created_by": "019d9526-132c-722b-b98c-bec7c1e40387",
        "created_at": "2026-04-18T11:40:00.000000Z",
        "updated_at": "2026-04-18T11:45:00.000000Z"
    }
}
```

---

## 5.6 Delete Store Location

**DELETE** `/store-locations/{id}`

- **Type**: Protected 🔐

### Response (200 OK)

```json
{
    "message": "Lokasi toko berhasil dihapus"
}
```

---

# 6. CAPTION TEMPLATE ENDPOINTS

## 6.1 List Caption Templates

**GET** `/caption-templates`

- **Type**: Protected 🔐
- **Description**: Get all caption templates ordered by tone

### Response (200 OK)

```json
[
    {
        "id": "019da009-1b5c-7dde-8b40-5fa2e8bd0c81",
        "tone": "friendly",
        "template_text": "Hey! Try our amazing {name} - it's so delicious!",
        "is_active": true,
        "created_at": "2026-04-10T10:00:00.000000Z",
        "updated_at": "2026-04-18T11:00:00.000000Z"
    }
]
```

---

## 6.2 Create Caption Template

**POST** `/caption-templates`

- **Type**: Protected 🔐

### Request Body

```json
{
    "tone": "professional",
    "template_text": "Introducing our premium {name} - {description}. Available now at {price}!",
    "prompt": "Buatkan caption Instagram yang profesional untuk produk makanan. Fokus pada kualitas dan value for money.",
    "is_active": true
}
```

### Response (201 Created)

```json
{
    "data": {
        "id": "019da042-abcd-efgh-ijkl-mnopqrstuvwx",
        "tone": "professional",
        "template_text": "Introducing our premium {name} - {description}. Available now at {price}!",
        "prompt": "Buatkan caption Instagram yang profesional untuk produk makanan. Fokus pada kualitas dan value for money.",
        "is_active": true,
        "created_at": "2026-04-18T11:50:00.000000Z",
        "updated_at": "2026-04-18T11:50:00.000000Z"
    }
}
```

### Validation Rules

- `tone`: required, in (friendly, professional, playful)
- `template_text`: required, string
- `prompt`: nullable, string
- `is_active`: boolean

---

## 6.3 Get Caption Template Detail

**GET** `/caption-templates/{id}`

- **Type**: Protected 🔐

### Response (200 OK)

```json
{
    "data": {
        "id": "019da009-1b5c-7dde-8b40-5fa2e8bd0c81",
        "tone": "friendly",
        "template_text": "Hey! Try our amazing {name} - it's so delicious!",
        "is_active": true,
        "created_at": "2026-04-10T10:00:00.000000Z",
        "updated_at": "2026-04-18T11:00:00.000000Z"
    }
}
```

---

## 6.4 Update Caption Template

**PUT** `/caption-templates/{id}`

- **Type**: Protected 🔐

### Request Body (All Optional)

```json
{
    "tone": "playful",
    "template_text": "OMG! {name} is so good! 🤤 Don't miss out!",
    "prompt": "Buatkan caption yang fun dan playful dengan banyak emoji.",
    "is_active": false
}
```

### Response (200 OK)

```json
{
    "data": {
        "id": "019da009-1b5c-7dde-8b40-5fa2e8bd0c81",
        "tone": "playful",
        "template_text": "OMG! {name} is so good! 🤤 Don't miss out!",
        "prompt": "Buatkan caption yang fun dan playful dengan banyak emoji.",
        "is_active": false,
        "created_at": "2026-04-10T10:00:00.000000Z",
        "updated_at": "2026-04-18T11:55:00.000000Z"
    }
}
```

---

## 6.5 Delete Caption Template

**DELETE** `/caption-templates/{id}`

- **Type**: Protected 🔐

### Response (200 OK)

```json
{
    "message": "Template berhasil dihapus"
}
```

---

# 7. CONTACT MESSAGE ENDPOINTS

## 7.1 Create Contact Message (Public)

**POST** `/public/contact`

- **Type**: Public ✅
- **Description**: Submit contact form message

### Request Body

```json
{
    "name": "Ahmad Wijaya",
    "email": "ahmad@example.com",
    "message": "Saya tertarik untuk menjadi reseller Mealjun. Bagaimana cara bergabung?"
}
```

### Response (201 Created)

```json
{
    "data": {
        "message": "Pesan Anda berhasil dikirim. Kami akan segera menghubungi Anda.",
        "id": "019da034-d98f-7063-b06b-cc74e3550878"
    }
}
```

### Validation Rules

- `name`: required, string, max 255 chars
- `email`: required, valid email
- `message`: required, string, min 10 chars

---

## 7.2 List Contact Messages (Admin)

**GET** `/contact-messages`

- **Type**: Protected 🔐

### Query Parameters

| Parameter | Type    | Description           |
| --------- | ------- | --------------------- |
| `limit`   | integer | Items per page        |
| `page`    | integer | Page number           |
| `is_read` | boolean | Filter by read status |
| `order`   | string  | Sort field            |
| `sort`    | integer | Sort direction        |
| `value`   | string  | Search                |

### Response (200 OK)

```json
{
    "data": [
        {
            "id": "019da034-d98f-7063-b06b-cc74e3550878",
            "name": "Ahmad Wijaya",
            "email": "ahmad@example.com",
            "message": "Saya tertarik untuk menjadi reseller Mealjun...",
            "reply_message": null,
            "is_read": false,
            "replied_at": null,
            "created_at": "2026-04-18T10:00:00.000000Z",
            "updated_at": "2026-04-18T10:00:00.000000Z"
        }
    ],
    "limit": 20,
    "page": 1,
    "size": 1,
    "pages": 1
}
```

---

## 7.3 Get Contact Message Detail (Admin)

**GET** `/contact-messages/{id}`

- **Type**: Protected 🔐
- **Note**: Auto marks message as read

### Response (200 OK)

```json
{
    "data": {
        "id": "019da034-d98f-7063-b06b-cc74e3550878",
        "name": "Ahmad Wijaya",
        "email": "ahmad@example.com",
        "message": "Saya tertarik untuk menjadi reseller Mealjun...",
        "reply_message": null,
        "is_read": true,
        "replied_at": null,
        "created_at": "2026-04-18T10:00:00.000000Z",
        "updated_at": "2026-04-18T10:05:00.000000Z"
    }
}
```

---

## 7.4 Reply to Contact Message (Admin)

**POST** `/contact-messages/{id}/reply`

- **Type**: Protected 🔐
- **Description**: Send reply via email or WhatsApp

### Request Body

```json
{
    "reply_message": "Terima kasih atas minat Anda. Kami akan menghubungi Anda segera.",
    "send_whatsapp_notif": true,
    "recipient_phone": "+628123456789"
}
```

### Response (200 OK)

```json
{
    "data": {
        "id": "019da034-d98f-7063-b06b-cc74e3550878",
        "name": "Ahmad Wijaya",
        "email": "ahmad@example.com",
        "message": "Saya tertarik untuk menjadi reseller Mealjun...",
        "reply_message": "Terima kasih atas minat Anda. Kami akan menghubungi Anda segera.",
        "is_read": true,
        "replied_at": "2026-04-18T10:10:00.000000Z",
        "created_at": "2026-04-18T10:00:00.000000Z",
        "updated_at": "2026-04-18T10:10:00.000000Z"
    }
}
```

### Validation Rules

- `reply_message`: required, string, min 5 chars
- `send_whatsapp_notif`: boolean
- `recipient_phone`: nullable, string, max 20 chars

---

## 7.5 Mark Message as Read (Admin)

**PATCH** `/contact-messages/{id}/mark-as-read`

- **Type**: Protected 🔐

### Response (200 OK)

```json
{
    "message": "Pesan ditandai sudah dibaca"
}
```

---

## 7.6 Delete Contact Message (Admin)

**DELETE** `/contact-messages/{id}`

- **Type**: Protected 🔐

### Response (200 OK)

```json
{
    "message": "Pesan berhasil dihapus"
}
```

---

# 8. GENERATED CAPTION ENDPOINTS

## 8.1 List Generated Captions

**GET** `/generated-captions`

- **Type**: Protected 🔐
- **Description**: Get captions generated by current user

### Query Parameters

| Parameter | Type    | Default | Description    |
| --------- | ------- | ------- | -------------- |
| `limit`   | integer | 20      | Items per page |
| `page`    | integer | 1       | Page number    |
| `order`   | string  | -       | Sort field     |
| `sort`    | integer | -       | Sort direction |
| `value`   | string  | -       | Search         |

### Response (200 OK)

```json
{
    "data": [
        {
            "id": "019da042-9999-5555-aaaa-bbbbccccdddd",
            "product_id": "019da009-1234-5678-abcd-ef1234567890",
            "product_name": "Chocolate Brownies",
            "flavor": "Chocolate",
            "tone": "friendly",
            "include_emoji": true,
            "generated_text": "Hey! Try our amazing Chocolate Brownies - it's so delicious! 😋",
            "was_copied": false,
            "created_by": "019d9526-132c-722b-b98c-bec7c1e40387",
            "product": {
                "id": "019da009-1234-5678-abcd-ef1234567890",
                "name": "Chocolate Brownies"
            },
            "created_at": "2026-04-18T12:00:00.000000Z",
            "updated_at": "2026-04-18T12:00:00.000000Z"
        }
    ],
    "limit": 20,
    "page": 1,
    "size": 1,
    "pages": 1
}
```

---

## 8.2 Generate Caption

**POST** `/generated-captions/generate`

- **Type**: Protected 🔐
- **Description**: Generate new caption from template or AI Kimi

### Request Body (Template Mode)

```json
{
    "product_id": "019da009-1234-5678-abcd-ef1234567890",
    "tone": "friendly",
    "include_emoji": true,
    "use_ai": false
}
```

### Request Body (AI Mode)

```json
{
    "product_id": "019da009-1234-5678-abcd-ef1234567890",
    "tone": "professional",
    "include_emoji": true,
    "use_ai": true
}
```

### Response (201 Created) - Template Mode

```json
{
    "data": {
        "id": "019da042-9999-5555-aaaa-bbbbccccdddd",
        "product_id": "019da009-1234-5678-abcd-ef1234567890",
        "product_name": "Chocolate Brownies",
        "flavor": "Chocolate",
        "tone": "friendly",
        "include_emoji": true,
        "generated_text": "Hey! Try our amazing Chocolate Brownies - it's so delicious! 😋",
        "was_copied": false,
        "created_by": "019d9526-132c-722b-b98c-bec7c1e40387",
        "created_at": "2026-04-18T12:00:00.000000Z",
        "updated_at": "2026-04-18T12:00:00.000000Z"
    }
}
```

### Response (201 Created) - AI Mode

```json
{
    "data": {
        "id": "019da042-9999-5555-aaaa-bbbbccccdddd",
        "product_id": "019da009-1234-5678-abcd-ef1234567890",
        "product_name": "Chocolate Brownies",
        "flavor": "Chocolate",
        "tone": "professional",
        "include_emoji": true,
        "generated_text": "🎉 Kami dengan bangga mempersembahkan Chocolate Brownies yang lezat! Dibuat dengan bahan premium pilihan. Harga terjangkau, kualitas terjamin. Pesan sekarang! 🍫✨",
        "was_copied": false,
        "created_by": "019d9526-132c-722b-b98c-bec7c1e40387",
        "created_at": "2026-04-18T12:00:00.000000Z",
        "updated_at": "2026-04-18T12:00:00.000000Z"
    }
}
```

### Validation Rules

- `product_id`: required, UUID, must exist
- `tone`: required, in (friendly, professional, playful)
- `include_emoji`: boolean
- `use_ai`: boolean (default: false)

### Generation Modes

#### Template Mode (use_ai: false)

- Uses predefined template with placeholder substitution
- Fast and consistent results
- Replaces `{name}`, `{flavor}`, `{price}`, `{description}` placeholders

#### AI Mode (use_ai: true)

- Uses NVIDIA Kimi AI to generate creative captions
- Template must have `prompt` field configured
- More creative and varied results (2-5 seconds generation time)
- Includes product context (name, flavor, price, description) in the prompt
- Requires NVIDIA Kimi API key configured

---

## 8.3 Mark Caption as Copied

**PATCH** `/generated-captions/{id}/copied`

- **Type**: Protected 🔐

### Response (200 OK)

```json
{
    "data": {
        "id": "019da042-9999-5555-aaaa-bbbbccccdddd",
        "product_name": "Chocolate Brownies",
        "generated_text": "Hey! Try our amazing Chocolate Brownies - it's so delicious! 😋",
        "was_copied": true,
        "created_at": "2026-04-18T12:00:00.000000Z",
        "updated_at": "2026-04-18T12:05:00.000000Z"
    }
}
```

---

## 8.4 Delete Generated Caption

**DELETE** `/generated-captions/{id}`

- **Type**: Protected 🔐

### Response (200 OK)

```json
{
    "message": "Caption berhasil dihapus"
}
```

---

# 9. ABOUT INFO ENDPOINTS

## 9.1 Get About Information (Public)

**GET** `/public/about`

- **Type**: Public ✅
- **Description**: Get website about information

### Response (200 OK)

```json
{
    "data": {
        "id": "019d8888-1111-2222-3333-444444444444",
        "title": "Tentang Mealjun",
        "description": "Mealjun adalah toko kue online terpercaya...",
        "vision": "Menjadi pilihan utama kue berkualitas di Indonesia",
        "mission": "Memberikan kue terbaik dengan harga terjangkau",
        "image_url": "https://res.cloudinary.com/...",
        "whatsapp_number": "62812345678",
        "email": "info@mealjun.com",
        "address": "Jl Raya No. 123, Jakarta",
        "created_at": "2026-03-01T10:00:00.000000Z",
        "updated_at": "2026-04-18T12:00:00.000000Z"
    }
}
```

### Response (404 Not Found)

```json
{
    "message": "Data about belum tersedia"
}
```

---

## 9.2 Update About Information (Admin)

**PUT** `/about`

- **Type**: Protected 🔐

### Request Body (All Optional)

```json
{
    "title": "Tentang Kami",
    "description": "Updated description...",
    "vision": "Updated vision...",
    "mission": "Updated mission...",
    "image_base64": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==",
    "whatsapp_number": "+6281234567890",
    "email": "contact@mealjun.com",
    "address": "Jl Baru No. 456"
}
```

### Response (200 OK)

```json
{
    "data": {
        "id": "019d8888-1111-2222-3333-444444444444",
        "title": "Tentang Kami",
        "description": "Updated description...",
        "vision": "Updated vision...",
        "mission": "Updated mission...",
        "image_url": "https://res.cloudinary.com/dctqloe37/image/upload/v1776512000/mealjun/about/xyz789.png",
        "whatsapp_number": "+6281234567890",
        "email": "contact@mealjun.com",
        "address": "Jl Baru No. 456",
        "updated_by": "019d9526-132c-722b-b98c-bec7c1e40387",
        "created_at": "2026-03-01T10:00:00.000000Z",
        "updated_at": "2026-04-18T12:10:00.000000Z"
    }
}
```

### Validation Rules

- `title`: sometimes, string, max 255 chars
- `description`: sometimes, string
- `vision`: sometimes, string
- `mission`: sometimes, string
- `image_base64`: nullable, base64 (jpeg, png, webp, gif)
- `whatsapp_number`: sometimes, string, max 20 chars
- `email`: sometimes, valid email
- `address`: sometimes, string

---

# 10. DASHBOARD ENDPOINTS

## 10.1 Get Dashboard Summary (Admin)

**GET** `/dashboard`

- **Type**: Protected 🔐
- **Description**: Get admin dashboard summary

### Response (200 OK)

```json
{
    "summary": {
        "total_products": 25,
        "featured_products": 5,
        "out_of_stock_products": 2,
        "total_testimonials": 48,
        "pending_testimonials": 3,
        "total_store_locations": 8,
        "unread_messages": 12,
        "total_captions_generated": 156
    },
    "recent_messages": [
        {
            "id": "019da034-d98f-7063-b06b-cc74e3550878",
            "name": "Ahmad Wijaya",
            "email": "ahmad@example.com",
            "is_read": false,
            "created_at": "2026-04-18T12:15:00.000000Z"
        }
    ],
    "visitor_today": 342,
    "visitor_week": 2150,
    "top_products": [
        {
            "id": "019da009-1234-5678-abcd-ef1234567890",
            "name": "Chocolate Brownies",
            "view_count": 485
        }
    ]
}
```

---

# 11. VISITOR ANALYTICS ENDPOINTS

## 11.1 Track Visitor (Public)

**POST** `/public/analytics/track`

- **Type**: Public ✅
- **Description**: Track website visitor analytics

### Request Body

```json
{
    "page_viewed": "product_detail",
    "product_id": "019da009-1234-5678-abcd-ef1234567890",
    "session_id": "session_abc123def456",
    "visitor_city": "Jakarta",
    "visitor_province": "DKI Jakarta",
    "referrer_url": "https://google.com"
}
```

### Response (201 Created)

```json
{
    "message": "Kunjungan berhasil dicatat"
}
```

### Validation Rules

- `page_viewed`: required, string, max 255 chars
- `product_id`: nullable, UUID, must exist
- `session_id`: nullable, string, max 255 chars
- `visitor_city`: nullable, string, max 255 chars
- `visitor_province`: nullable, string, max 255 chars
- `referrer_url`: nullable, valid URL

---

## 11.2 Get Analytics Summary (Admin)

**GET** `/analytics`

- **Type**: Protected 🔐

### Query Parameters

| Parameter | Type    | Default | Description               |
| --------- | ------- | ------- | ------------------------- |
| `days`    | integer | 30      | Number of days to analyze |

### Response (200 OK)

```json
{
    "total_visits_period": 5432,
    "unique_cities": 156,
    "top_pages": [
        {
            "page_viewed": "homepage",
            "visits": 2145
        },
        {
            "page_viewed": "product_detail",
            "visits": 1823
        }
    ],
    "top_cities": [
        {
            "visitor_city": "Jakarta",
            "visits": 1245
        },
        {
            "visitor_city": "Bandung",
            "visits": 892
        }
    ],
    "daily_visits": [
        {
            "visit_date": "2026-03-18",
            "visits": 145
        }
    ]
}
```

---

# 12. CITY STATISTICS ENDPOINTS

## 12.1 Get City Statistics (Admin)

**GET** `/city-stats`

- **Type**: Protected 🔐
- **Description**: Get city visitor statistics

### Response (200 OK)

```json
[
    {
        "id": "019da042-1111-2222-3333-444444444444",
        "city": "Jakarta",
        "province": "DKI Jakarta",
        "total_visitors": 1245,
        "last_visit_date": "2026-04-18",
        "created_at": "2026-04-10T10:00:00.000000Z",
        "updated_at": "2026-04-18T12:30:00.000000Z"
    }
]
```

---

# PAGINATION FORMAT

All list endpoints return paginated results with the following format:

```json
{
  "data": [...],
  "limit": 20,
  "page": 1,
  "size": 15,
  "pages": 5
}
```

**Fields:**

- `data`: Array of items
- `limit`: Items requested per page
- `page`: Current page number
- `size`: Actual items returned in this page
- `pages`: Total number of pages

---

# ERROR RESPONSES

## 401 Unauthorized

```json
{
    "message": "Unauthenticated."
}
```

## 403 Forbidden

```json
{
    "message": "This action is unauthorized."
}
```

## 404 Not Found

```json
{
    "message": "No query results for model [ClassName]"
}
```

## 422 Unprocessable Entity (Validation Error)

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "field_name": ["Error message 1", "Error message 2"],
        "another_field": ["Error message"]
    }
}
```

## 500 Internal Server Error

```json
{
    "message": "Server error message"
}
```

---

# HTTP STATUS CODES

| Code | Meaning               | Usage                              |
| ---- | --------------------- | ---------------------------------- |
| 200  | OK                    | Successful GET, PUT, PATCH, DELETE |
| 201  | Created               | Successful POST (resource created) |
| 400  | Bad Request           | Invalid request format             |
| 401  | Unauthorized          | Missing or invalid authentication  |
| 403  | Forbidden             | Authenticated but not authorized   |
| 404  | Not Found             | Resource not found                 |
| 422  | Unprocessable Entity  | Validation failed                  |
| 500  | Internal Server Error | Server-side error                  |

---

# SORTING & FILTERING

## Sort Direction

- `sort: 1` → Ascending (A-Z, 0-9, oldest to newest)
- `sort: -1` → Descending (Z-A, 9-0, newest to oldest)

## Example Queries

**Sort by price ascending:**

```
GET /api/public/products?order=price&sort=1
```

**Sort by creation date descending:**

```
GET /api/public/products?order=created_at&sort=-1
```

**Search and filter:**

```
GET /api/products?value=chocolate&stock_status=available&order=name&sort=1
```

---

# AUTHENTICATION FLOW

1. **Login** → POST `/auth/login` → Get token
2. **Use Token** → Include `Authorization: Bearer {token}` in headers
3. **Logout** → POST `/auth/logout` → Token is invalidated
4. **Get User** → GET `/auth/me` → Get current user info

---

# BASE64 IMAGE FORMAT

All image uploads expect base64 format starting with `data:image/`:

```
data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEA...
data:image/png;base64,iVBORw0KGgoAAAANSUhEUg...
data:image/webp;base64,UklGRiQAAABXRUJQ...
data:image/gif;base64,R0lGODlhAQABAAAAACw=...
```

**Supported formats:** jpeg, png, webp, gif

---

# ENVIRONMENT

- **Base URL**: `http://127.0.0.1:8000/api`
- **Protocol**: HTTP/HTTPS
- **Content-Type**: `application/json`
- **Character Encoding**: UTF-8

---

# QUICK START EXAMPLES

## JavaScript/Fetch

```javascript
// Login
const loginResponse = await fetch("http://127.0.0.1:8000/api/auth/login", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
        email: "admin@example.com",
        password: "password123",
    }),
});
const loginData = await loginResponse.json();
const token = loginData.token;

// Get products
const productsResponse = await fetch(
    "http://127.0.0.1:8000/api/public/products?limit=10",
    {
        method: "GET",
        headers: { "Content-Type": "application/json" },
    },
);
const products = await productsResponse.json();

// Create product (with authentication)
const createResponse = await fetch("http://127.0.0.1:8000/api/products", {
    method: "POST",
    headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
    },
    body: JSON.stringify({
        name: "New Product",
        flavor: "Chocolate",
        description: "Description here",
        price: 25000,
        image_base64: "data:image/png;base64,...",
        stock_status: "available",
    }),
});
const newProduct = await createResponse.json();
```

---

# END OF DOCUMENTATION

**Version**: 1.0  
**Last Updated**: April 18, 2026  
**API Status**: Production Ready
