# AI Kimi Quick Start Guide

## 🚀 Quick Setup (2 minutes)

### Step 1: Add API Key to `.env`

```env
NVIDIA_KIMI_API_KEY=nvapi-YOUR_KEY_HERE
```

### Step 2: Choose Generation Mode

#### Template Mode (Fast ⚡)

```json
{
    "product_id": "uuid-here",
    "tone": "friendly",
    "use_ai": false
}
```

#### AI Mode (Creative 🎨)

```json
{
    "product_id": "uuid-here",
    "tone": "professional",
    "use_ai": true
}
```

## 📝 Available Tones

| Tone             | Best For          | Example Output                        |
| ---------------- | ----------------- | ------------------------------------- |
| **friendly**     | Casual, relatable | "Hey! Try our brownies! 😋"           |
| **professional** | Formal, premium   | "Introducing our premium brownies..." |
| **playful**      | Fun, viral        | "OMG! Our brownies are SO good! 🤤"   |

## 💡 Writing Prompts for AI

### Good Prompt ✅

```
Buatkan caption Instagram yang playful untuk produk makanan.
Gunakan banyak emoji, bahasa santai (Gen Z),
fokus pada rasa unik. Maksimal 4 baris.
Buat orang ingin segera membeli!
```

### Bad Prompt ❌

```
Buatkan caption yang bagus
```

## 🔧 Configuration in Config

File: `config/services.php`

```php
'nvidia_kimi' => [
    'api_key' => env('NVIDIA_KIMI_API_KEY'),
    'api_url' => env('NVIDIA_KIMI_API_URL', 'https://integrate.api.nvidia.com/v1/chat/completions'),
    'model' => env('NVIDIA_KIMI_MODEL', 'moonshotai/kimi-k2.5'),
],
```

## 📚 Caption Templates CRUD

### List Templates

```bash
GET /api/admin/caption-templates
```

### Create Template (with AI)

```bash
POST /api/admin/caption-templates
{
    "tone": "playful",
    "template_text": "Your template with {name}, {flavor}, {price}, {description}",
    "prompt": "AI prompt for playful tone...",
    "use_ai": true,
    "is_active": true
}
```

### Update Template

```bash
PUT /api/admin/caption-templates/{id}
{
    "use_ai": true,
    "prompt": "Updated prompt..."
}
```

### Delete Template

```bash
DELETE /api/admin/caption-templates/{id}
```

## 🎯 Generate Captions

### Template-Based (No API Call)

```bash
POST /api/admin/generated-captions/generate
{
    "product_id": "uuid",
    "tone": "friendly",
    "include_emoji": true,
    "use_ai": false
}
```

Response Time: < 100ms ⚡

### AI-Generated (with API Call)

```bash
POST /api/admin/generated-captions/generate
{
    "product_id": "uuid",
    "tone": "professional",
    "include_emoji": true,
    "use_ai": true
}
```

Response Time: 2-5 seconds 🔄

## 🛠️ Troubleshooting

| Problem                      | Solution                                       |
| ---------------------------- | ---------------------------------------------- |
| "API key not configured"     | Add `NVIDIA_KIMI_API_KEY` to `.env`            |
| "Template missing AI prompt" | Add `prompt` field and set `use_ai: true`      |
| "API timeout"                | Check internet, check NVIDIA status page       |
| "Caption too long"           | Update template prompt with length requirement |

## 📊 Performance Comparison

```
┌─────────────────┬──────────────┬─────────────┐
│ Feature         │ Template     │ AI          │
├─────────────────┼──────────────┼─────────────┤
│ Speed           │ < 50ms ✅    │ 2-5s 🟡     │
│ Cost            │ Free ✅      │ $ 🟡        │
│ Creativity      │ Low 🔴       │ High ✅     │
│ Consistency     │ High ✅      │ Variable 🟡 │
└─────────────────┴──────────────┴─────────────┘
```

## 🎓 Common Prompts

### Friendly Tone

```
Buatkan caption Instagram yang friendly dan casual
untuk produk makanan. Gunakan emoji, bahasa santai,
sebutkan nama dan rasa produk. Maksimal 3-5 baris.
```

### Professional Tone

```
Buatkan caption Instagram yang profesional untuk
produk makanan premium. Fokus pada kualitas bahan
dan value. Gunakan bahasa sopan, tidak berlebihan.
Maksimal 4 baris.
```

### Playful Tone

```
Buatkan caption Instagram yang playful dan fun untuk
Gen Z audience. Banyak emoji, bahasa santai, fokus
pada rasa unik dan keunikan produk. Buat viral!
Maksimal 5 baris.
```

## 🔐 Security Notes

1. **Never commit API key** to git
2. Use `.env` file for local development
3. Use environment variables in production
4. Rotate API key regularly
5. Monitor API usage for suspicious activity

## 📖 Full Documentation

See [AI_KIMI_INTEGRATION.md](./AI_KIMI_INTEGRATION.md) for detailed documentation.

## 🚨 Error Responses

### Missing API Key

```json
{
    "errors": {
        "ai": [
            "Failed to generate caption with AI: NVIDIA Kimi API key not configured"
        ]
    }
}
```

### Invalid Tone

```json
{
    "errors": {
        "tone": ["The tone must be friendly, professional, or playful"]
    }
}
```

### Product Not Found

```json
{
    "errors": {
        "product_id": ["The product_id does not exist"]
    }
}
```

## 🌐 External Links

- [NVIDIA API Documentation](https://docs.api.nvidia.com/)
- [Get API Key](https://integrate.api.nvidia.com/)
- [Kimi AI Model Docs](https://docs.api.nvidia.com/kimi/)

## ✅ Checklist for Implementation

- [ ] Add `NVIDIA_KIMI_API_KEY` to `.env`
- [ ] Run `php artisan migrate`
- [ ] Run `php artisan db:seed --class=CaptionTemplateSeeder`
- [ ] Test template-based generation
- [ ] Get and add real API key
- [ ] Update caption templates with `use_ai: true`
- [ ] Test AI-based generation
- [ ] Monitor API usage

## 🎯 Next Steps

1. Get NVIDIA Kimi API key from [NVIDIA console](https://integrate.api.nvidia.com/)
2. Add it to `.env`
3. Run migrations and seeders
4. Create caption templates with AI prompts
5. Start generating captions!

---

**Last Updated**: April 19, 2026  
**Version**: 1.0
