# My Graduates API

## Quick Start

**Base URL**: `https://yoursite.com/wp-json/my-graduates/graduates`

**Authentication**: Add header `X-API-KEY: your_api_key_here`

## Get Graduates

**Request**:
```bash
curl -H "X-API-KEY: your_api_key" \
     https://yoursite.com/wp-json/my-graduates/graduates
```

**Response**:
```json
[
  {
    "id": 123,
    "first_name": "Jan",
    "last_name": "Kowalski", 
    "description": "Graduate description",
    "photo": "https://yoursite.com/photo.jpg"
  }
]
```

## Setup

Add to your `.env` file:
```bash
MY_GRADUATES_API_KEY=your_secure_api_key_here
MY_GRADUATES_ALLOWED_IPS=127.0.0.1,192.0.0.1 (optional if variable not set no IP restriction)
```

Generate API key:
```bash
openssl rand -hex 32
```

## Errors

- `401` - Invalid API key
- `403` - IP not allowed

That's it! 🎉
