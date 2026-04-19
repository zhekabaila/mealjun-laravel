# AI Kimi Integration Guide

## Overview

Mealjun now supports AI-powered caption generation using NVIDIA's Kimi AI model. Users can choose to generate captions using either pre-defined templates or AI-generated content.

## Setup

### 1. Environment Configuration

Add the following to your `.env` file:

```env
NVIDIA_KIMI_API_KEY=nvapi-YOUR_API_KEY_HERE
NVIDIA_KIMI_API_URL=https://integrate.api.nvidia.com/v1/chat/completions
NVIDIA_KIMI_MODEL=moonshotai/kimi-k2.5
```

### 2. Get NVIDIA Kimi API Key

1. Visit [NVIDIA API Platform](https://integrate.api.nvidia.com/)
2. Sign up or login to your account
3. Navigate to API Keys section
4. Create a new API key for Chat API
5. Copy the API key and add it to your `.env` file

## Features

### Caption Generation Modes

#### 1. Template Mode (Default)

- Uses predefined caption templates
- Replaces placeholders: `{name}`, `{flavor}`, `{price}`, `{description}`
- Fast and consistent results
- No API calls required

#### 2. AI Mode

- Uses NVIDIA Kimi AI to generate creative captions
- More varied and creative results
- Requires API key and internet connection
- Slightly slower than template mode

## API Usage

### Create Caption Template

**POST** `/api/admin/caption-templates`

#### Request Body:

```json
{
    "tone": "professional",
    "template_text": "Kami mempersembahkan {name}. {description}. Harga: {price}",
    "prompt": "Buatkan caption Instagram yang profesional dan menarik untuk produk makanan premium. Fokus pada kualitas bahan dan nilai uang. Gunakan bahasa yang sopan namun tetap engaging. Maksimal 3-4 baris dan boleh pakai emoji.",
    "is_active": true
}
```

**Response:**

```json
{
    "data": {
        "id": "019da042-abcd-efgh-ijkl-mnopqrstuvwx",
        "tone": "professional",
        "template_text": "Kami mempersembahkan {name}. {description}. Harga: {price}",
        "prompt": "Buatkan caption Instagram yang profesional dan menarik untuk produk makanan premium...",
        "is_active": true,
        "created_at": "2026-04-18T11:50:00.000000Z",
        "updated_at": "2026-04-18T11:50:00.000000Z"
    }
}
```

**Notes:**

- Template without `prompt` can still be used for Template Mode generation
- Template with `prompt` can be used for both Template Mode and AI Mode generation
- `use_ai` is NOT a database field - it's a request parameter only

### Generate Caption - Template Mode

**POST** `/api/admin/generated-captions/generate`

```json
{
    "product_id": "019da009-1234-5678-abcd-ef1234567890",
    "tone": "friendly",
    "include_emoji": true,
    "use_ai": false
}
```

**Response:**

```json
{
    "data": {
        "id": "019da042-9999-5555-aaaa-bbbbccccdddd",
        "product_id": "019da009-1234-5678-abcd-ef1234567890",
        "product_name": "Chocolate Brownies",
        "flavor": "Chocolate",
        "tone": "friendly",
        "include_emoji": true,
        "generated_text": "😍 Hei teman! Coba Chocolate Brownies rasa Chocolate kami! Brownies lezat dengan cokelat premium. Harga: 25000",
        "was_copied": false,
        "created_by": "019d9526-132c-722b-b98c-bec7c1e40387",
        "created_at": "2026-04-19T10:30:00.000000Z"
    }
}
```

### Generate Caption - AI Mode

**POST** `/api/admin/generated-captions/generate`

```json
{
    "product_id": "019da009-1234-5678-abcd-ef1234567890",
    "tone": "professional",
    "include_emoji": true,
    "use_ai": true
}
```

**Response:**

```json
{
    "data": {
        "id": "019da042-9999-5555-aaaa-bbbbccccdddd",
        "product_id": "019da009-1234-5678-abcd-ef1234567890",
        "product_name": "Chocolate Brownies",
        "flavor": "Chocolate",
        "tone": "professional",
        "include_emoji": true,
        "generated_text": "🎉 Kami dengan bangga mempersembahkan Chocolate Brownies kami yang istimewa! Dibuat dengan bahan cokelat premium berkualitas tinggi, memberikan rasa yang kaya dan memanjakan lidah. Nikmati kelezatan autentik dengan harga yang sangat terjangkau. Pesan sekarang dan rasakan perbedaannya! ✨",
        "was_copied": false,
        "created_by": "019d9526-132c-722b-b98c-bec7c1e40387",
        "created_at": "2026-04-19T10:32:00.000000Z"
    }
}
```

## Error Handling

### When Using AI Mode

#### 1. Template Not Configured for AI

```json
{
    "errors": {
        "template": ["Template does not have AI prompt configured."]
    }
}
```

**Solution:** Create or update template with `use_ai: true` and a valid prompt.

#### 2. API Key Not Set

```json
{
    "errors": {
        "ai": [
            "Failed to generate caption with AI: NVIDIA Kimi API key not configured..."
        ]
    }
}
```

**Solution:** Add `NVIDIA_KIMI_API_KEY` to `.env` file.

#### 3. API Connection Error

```json
{
    "errors": {
        "ai": ["Failed to generate caption with AI: Connection timeout..."]
    }
}
```

**Solution:** Check internet connection and NVIDIA API status.

## Best Practices

### Writing Effective AI Prompts

1. **Be Specific**: Clearly describe the tone and style you want

    ```
    ✅ Buatkan caption yang fun dan playful dengan banyak emoji, fokus pada rasa unik produk
    ❌ Buatkan caption yang bagus
    ```

2. **Set Output Length**: Specify how many lines or words

    ```
    Maksimal 3-5 baris, jangan lebih dari 10 kata per baris
    ```

3. **Include Guidelines**: Tell AI what to include/exclude

    ```
    Gunakan hashtag #Mealjun, jangan sebutkan harga terlalu banyak
    ```

4. **Use Examples**: Provide sample caption if helpful
    ```
    Contoh: "Hey! Coba brownies kami yang enak! 😋"
    ```

## Seeded Caption Templates

The system comes with 3 pre-configured templates:

### 1. Friendly Tone

- **use_ai**: false
- **Purpose**: Casual, relatable captions
- **Prompt**: For future AI generation

### 2. Professional Tone

- **use_ai**: false
- **Purpose**: Formal, business-like captions
- **Prompt**: For future AI generation

### 3. Playful Tone

- **use_ai**: false
- **Purpose**: Fun, attention-grabbing captions
- **Prompt**: For future AI generation

### Updating Templates for AI

To enable AI for any template:

**PUT** `/api/admin/caption-templates/{id}`

```json
{
    "use_ai": true,
    "prompt": "Your AI prompt here describing the tone and style..."
}
```

## Performance Considerations

### Template Mode

- **Speed**: Instant (< 50ms)
- **Cost**: Free
- **Consistency**: High - same template produces same result format
- **Creativity**: Low - limited by template

### AI Mode

- **Speed**: ~2-5 seconds (depends on API response)
- **Cost**: Depends on NVIDIA pricing (check their docs)
- **Consistency**: Low - each generation is unique
- **Creativity**: High - AI generates varied creative content

### Recommendations

- Use **Template Mode** for:
    - High-volume caption generation
    - Budget constraints
    - Consistent branding
- Use **AI Mode** for:
    - Marketing campaigns
    - Featured products
    - Special occasions
    - When maximum creativity is needed

## Troubleshooting

### Issue: "API Key not configured"

**Solution**:

1. Check `.env` file for `NVIDIA_KIMI_API_KEY`
2. Ensure key is valid and not expired
3. Restart application if `.env` was just updated

### Issue: API timeouts

**Solution**:

1. Check internet connection
2. Check NVIDIA API status page
3. Reduce max_tokens in config if needed

### Issue: Generated caption is too long/short

**Solution**: Update the prompt in your template to specify exact length requirements

## API Reference

### NvidiaKimiService

```php
// Generate caption using AI
public function generateCaption(
    string $prompt,
    string $productName,
    string $flavor,
    string $price,
    string $description,
    bool $includeEmoji = true
): string

// Test connection to NVIDIA Kimi API
public function testConnection(): bool
```

## Examples

### Example 1: Generate Friendly Caption (Template)

```bash
curl -X POST http://localhost:8000/api/admin/generated-captions/generate \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "product_id": "019da009-1234-5678-abcd-ef1234567890",
    "tone": "friendly",
    "include_emoji": true,
    "use_ai": false
  }'
```

### Example 2: Generate Professional Caption (AI)

```bash
curl -X POST http://localhost:8000/api/admin/generated-captions/generate \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "product_id": "019da009-1234-5678-abcd-ef1234567890",
    "tone": "professional",
    "include_emoji": true,
    "use_ai": true
  }'
```

### Example 3: Create Template with AI

```bash
curl -X POST http://localhost:8000/api/admin/caption-templates \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "tone": "playful",
    "template_text": "🎉 {name}? Pasti enak! Rasa {flavor} yang bikin ketagihan. {description}. Hanya {price}! #Mealjun",
    "prompt": "Buatkan caption yang playful dan fun dengan banyak emoji. Target audience adalah millennials dan Gen Z. Buat caption yang viral dan engaging!",
    "is_active": true,
    "use_ai": true
  }'
```

## Database Schema

### caption_templates table

```
- id (UUID)
- tone (string: friendly, professional, playful)
- template_text (text) - Template with placeholders {name}, {flavor}, {price}, {description}
- prompt (text, nullable) - AI prompt for this tone (used when use_ai=true in request)
- is_active (boolean)
- created_at (timestamp)
- updated_at (timestamp)
```

**Note:** `use_ai` is a **request parameter only**, NOT a database field. This allows flexibility:

- Same template can be used for both Template Mode and AI Mode generation
- No need to create separate templates for AI vs Template generation
- Users decide per-request whether to use AI or template

## Future Enhancements

- [ ] Support for multiple AI providers (OpenAI, Claude, etc.)
- [ ] A/B testing for template vs AI generated captions
- [ ] Caption history and analytics
- [ ] Bulk caption generation
- [ ] Custom prompt templates per tone
- [ ] Caption quality metrics
- [ ] Integration with social media APIs
