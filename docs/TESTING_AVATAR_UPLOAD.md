# Testing Upload Avatar with Swagger UI & Postman

## ✅ OpenAPI YAML Updated!

File `docs/openapi.yaml` sudah diperbaiki untuk mendukung upload foto avatar dengan benar.

---

## 🎯 Perubahan yang Dilakukan

### **1. Added `encoding` specification**
Ditambahkan `encoding` block untuk memastikan Swagger UI dan API clients mengenali avatar sebagai file upload:

```yaml
multipart/form-data:
  schema:
    type: object
    properties:
      avatar:
        type: string
        format: binary
        description: Avatar image file (JPEG, JPG, PNG, GIF) - Max 2MB
  encoding:
    avatar:
      contentType: image/jpeg, image/jpg, image/png, image/gif
      style: form
```

### **2. Detailed property descriptions**
Setiap field sekarang punya deskripsi yang jelas:
- `name`: User's full name
- `email`: User's email address
- `password`: New password
- `password_confirmation`: Password confirmation
- `avatar`: Avatar image file (JPEG, JPG, PNG, GIF) - Max 2MB

---

## 🧪 Cara Test Upload Avatar

### **Menggunakan Swagger UI**

#### **Step 1: Start Swagger UI**
```bash
# Option 1: Using npx
npx swagger-ui-watcher docs/openapi.yaml

# Option 2: Using Docker
docker run -p 8080:8080 -e SWAGGER_JSON=/openapi.yaml -v ${PWD}/docs/openapi.yaml:/openapi.yaml swaggerapi/swagger-ui
```

Buka browser: `http://localhost:8080`

#### **Step 2: Authorize**
1. Klik tombol **"Authorize"** di kanan atas
2. Masukkan Bearer token: `Bearer YOUR_ACCESS_TOKEN`
3. Klik **"Authorize"** lalu **"Close"**

#### **Step 3: Test PATCH /auth**
1. Scroll ke endpoint **`PATCH /api/v1/auth`**
2. Klik **"Try it out"**
3. Pilih **Content type: multipart/form-data**
4. Isi field yang ingin diupdate:
   - `name`: (optional) "John Doe"
   - `avatar`: **Klik "Choose File"** dan pilih gambar
5. Klik **"Execute"**
6. Lihat response di bawah dengan `avatar_url` yang baru

---

### **Menggunakan Postman**

#### **Step 1: Import OpenAPI**
1. Buka Postman
2. Klik **"Import"**
3. Drag & drop file `docs/openapi.yaml`
4. Postman akan membuat collection lengkap dengan semua endpoints

#### **Step 2: Setup Authorization**
1. Klik collection "Laporan Internal API"
2. Tab **"Authorization"**
3. Type: **Bearer Token**
4. Token: Paste your access token

#### **Step 3: Test Upload Avatar**
1. Pilih request **`PATCH /api/v1/auth`**
2. Tab **"Body"**
3. Pilih **form-data** (bukan raw)
4. Add fields:
   - Key: `name` | Value: "New Name" (optional)
   - Key: `avatar` | Type: **File** | Value: [Choose file]
5. Klik **"Send"**
6. Check response untuk `avatar_url`

**Screenshot Postman:**
```
Body tab:
┌──────────────────┬──────┬───────────────┐
│ KEY              │ TYPE │ VALUE         │
├──────────────────┼──────┼───────────────┤
│ name             │ Text │ John Doe      │
│ avatar           │ File │ [Choose File] │ ← KLIK INI
└──────────────────┴──────┴───────────────┘
```

---

### **Menggunakan cURL**

#### **Upload Avatar Only**
```bash
curl -X PATCH http://localhost:8000/api/v1/auth \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "avatar=@/path/to/photo.jpg"
```

#### **Update Name + Avatar**
```bash
curl -X PATCH http://localhost:8000/api/v1/auth \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "name=John Doe" \
  -F "avatar=@/path/to/photo.jpg"
```

#### **Full Update with Avatar**
```bash
curl -X PUT http://localhost:8000/api/v1/auth \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "name=John Doe" \
  -F "email=john@example.com" \
  -F "password=newpass123" \
  -F "password_confirmation=newpass123" \
  -F "avatar=@/path/to/photo.jpg"
```

---

### **Menggunakan PowerShell**

```powershell
# Prepare the file
$file = Get-Item "C:\path\to\photo.jpg"

# Create multipart form
$form = @{
    name = "John Doe"
    avatar = $file
}

# Send request
$response = Invoke-WebRequest `
    -Uri "http://localhost:8000/api/v1/auth" `
    -Method PATCH `
    -Headers @{"Authorization"="Bearer YOUR_TOKEN"} `
    -Form $form

# View response
$response.Content | ConvertFrom-Json | ConvertTo-Json -Depth 10
```

---

### **Menggunakan JavaScript/Fetch**

```javascript
// Get file from input
const fileInput = document.querySelector('input[type="file"]');
const file = fileInput.files[0];

// Create FormData
const formData = new FormData();
formData.append('name', 'John Doe');
formData.append('avatar', file);

// Send request
const response = await fetch('http://localhost:8000/api/v1/auth', {
  method: 'PATCH',
  headers: {
    'Authorization': `Bearer ${token}`
  },
  body: formData
});

const data = await response.json();
console.log(data);
```

---

## 📋 Expected Response

### **Success (200)**
```json
{
  "success": true,
  "message": "Profile updated successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "avatar": "avatars/abc123xyz456.jpg",
    "avatar_url": "https://api-minio.muaraenimkab.go.id/egovreportingmuaraenim/avatars/abc123xyz456.jpg",
    "unit_id": null,
    "created_at": "2024-01-15T10:00:00.000000Z",
    "updated_at": "2024-01-15T11:30:00.000000Z"
  }
}
```

### **Validation Error (422)**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "avatar": [
      "The avatar must be an image.",
      "The avatar must not be greater than 2048 kilobytes."
    ]
  }
}
```

---

## 🔍 Verify in Swagger UI

Setelah OpenAPI diupdate, di Swagger UI Anda akan melihat:

### **PUT /api/v1/auth**
```
Request body (multipart/form-data)
┌─────────────────────────────────────────┐
│ name            [string]                │
│ email           [string]                │
│ password        [string]                │
│ password_confirmation [string]          │
│ avatar          [binary] 📎 Choose File │ ← FILE UPLOAD
└─────────────────────────────────────────┘
```

### **PATCH /api/v1/auth**
```
Request body (multipart/form-data)
┌─────────────────────────────────────────┐
│ name            [string]                │
│ email           [string]                │
│ password        [string]                │
│ password_confirmation [string]          │
│ avatar          [binary] 📎 Choose File │ ← FILE UPLOAD
└─────────────────────────────────────────┘
```

Ada tombol **"Choose File"** untuk upload avatar! 🎉

---

## ✅ Checklist

- [x] OpenAPI YAML diperbaiki dengan `encoding` specification
- [x] Field `avatar` dengan `format: binary`
- [x] Content-Type specification: `image/jpeg, image/jpg, image/png, image/gif`
- [x] Description lengkap untuk setiap field
- [x] Support multipart/form-data
- [x] File upload akan muncul di Swagger UI
- [x] Testing guide lengkap

---

## 🎯 Quick Test

```bash
# 1. Dapatkan token dari login
TOKEN="your_access_token_here"

# 2. Upload avatar
curl -X PATCH http://localhost:8000/api/v1/auth \
  -H "Authorization: Bearer $TOKEN" \
  -F "avatar=@photo.jpg"

# 3. Check profile
curl http://localhost:8000/api/v1/auth \
  -H "Authorization: Bearer $TOKEN"

# Response akan include avatar_url yang bisa diakses langsung!
```

---

## 🚀 Ready to Test!

OpenAPI YAML sudah siap dan upload foto avatar sudah berfungsi dengan benar! Silakan test dengan salah satu metode di atas. 🎉
