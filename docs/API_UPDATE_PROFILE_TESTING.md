# Update Profile API Testing Guide

## Endpoint
- **URL**: `PUT /api/v1/auth` atau `PATCH /api/v1/auth`
- **Method**: PUT/PATCH
- **Auth**: Required (Bearer Token)
- **Content-Type**: `multipart/form-data` (jika upload avatar) atau `application/json` (jika tanpa avatar)

## Features
✅ **Full Update** - Update semua field sekaligus  
✅ **Partial Update** - Update field tertentu saja  
✅ **Avatar Upload** - Upload foto profil user  
✅ **Auto Delete Old Avatar** - Otomatis menghapus avatar lama saat upload baru  
✅ **Avatar URL** - Otomatis menambahkan `avatar_url` di response  

## Request Fields

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| `name` | string | Optional | max:255 | Nama user |
| `email` | string | Optional | email, unique | Email user |
| `password` | string | Optional | min:8, confirmed | Password baru |
| `password_confirmation` | string | Required if password | min:8 | Konfirmasi password |
| `avatar` | file | Optional | image (jpeg,jpg,png,gif), max:2MB | Foto profil |

## Example Requests

### 1. Update Name Only (Partial Update)
```bash
curl -X PATCH http://localhost:8000/api/v1/auth \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe Updated"
  }'
```

### 2. Update Email Only
```bash
curl -X PATCH http://localhost:8000/api/v1/auth \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "newemail@example.com"
  }'
```

### 3. Update Password
```bash
curl -X PATCH http://localhost:8000/api/v1/auth \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
  }'
```

### 4. Upload Avatar Only
```bash
curl -X PATCH http://localhost:8000/api/v1/auth \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "avatar=@/path/to/photo.jpg"
```

### 5. Full Update (All Fields)
```bash
curl -X PUT http://localhost:8000/api/v1/auth \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "name=John Doe" \
  -F "email=john@example.com" \
  -F "password=newpassword123" \
  -F "password_confirmation=newpassword123" \
  -F "avatar=@/path/to/photo.jpg"
```

### 6. Update Name + Avatar
```bash
curl -X PATCH http://localhost:8000/api/v1/auth \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "name=John Updated" \
  -F "avatar=@/path/to/new-photo.jpg"
```

## Success Response
```json
{
  "success": true,
  "message": "Profile updated successfully",
  "data": {
    "id": 1,
    "name": "John Doe Updated",
    "email": "john@example.com",
    "avatar": "avatars/xyz123.jpg",
    "avatar_url": "https://api-minio.muaraenimkab.go.id/egovreportingmuaraenim/avatars/xyz123.jpg",
    "email_verified_at": null,
    "created_at": "2024-01-15T10:00:00.000000Z",
    "updated_at": "2024-01-15T10:30:00.000000Z",
    "unit_id": null,
    "deleted_at": null
  }
}
```

## Error Responses

### Validation Error
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email has already been taken."],
    "password": ["The password confirmation does not match."],
    "avatar": ["The avatar must be an image.", "The avatar must not be greater than 2048 kilobytes."]
  }
}
```

### Unauthenticated
```json
{
  "message": "Unauthenticated."
}
```

## Testing dengan Postman

### Setup
1. Buat request baru dengan method `PATCH` atau `PUT`
2. URL: `http://localhost:8000/api/v1/auth`
3. Headers:
   - `Authorization`: `Bearer YOUR_ACCESS_TOKEN`
4. Body:
   - Pilih `form-data` jika upload avatar
   - Pilih `raw` + `JSON` jika tidak upload avatar

### Test Cases

#### ✅ Test 1: Partial Update - Update Name
- Body (JSON):
  ```json
  {
    "name": "New Name"
  }
  ```
- Expected: Success, name berubah, field lain tidak berubah

#### ✅ Test 2: Partial Update - Upload Avatar
- Body (form-data):
  - Key: `avatar`, Type: File, Value: pilih file image
- Expected: Success, avatar terupload, avatar lama terhapus (jika ada)

#### ✅ Test 3: Partial Update - Update Password
- Body (JSON):
  ```json
  {
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
  }
  ```
- Expected: Success, password berubah

#### ✅ Test 4: Full Update
- Body (form-data):
  - `name`: "John Doe"
  - `email`: "john@example.com"
  - `password`: "password123"
  - `password_confirmation`: "password123"
  - `avatar`: [pilih file]
- Expected: Success, semua field terupdate

#### ❌ Test 5: Validation Error - Invalid Email
- Body (JSON):
  ```json
  {
    "email": "invalid-email"
  }
  ```
- Expected: Error 422, validation message

#### ❌ Test 6: Validation Error - Password Mismatch
- Body (JSON):
  ```json
  {
    "password": "password123",
    "password_confirmation": "different"
  }
  ```
- Expected: Error 422, password confirmation error

#### ❌ Test 7: Validation Error - Avatar Too Large
- Body (form-data):
  - `avatar`: [pilih file > 2MB]
- Expected: Error 422, file size error

## Implementation Details

### Files Modified/Created:
1. ✅ `app/Http/Requests/UpdateProfileRequest.php` - Validation request
2. ✅ `src/Domain/User/Actions/Auth/UpdateAuthAction.php` - Business logic with avatar upload
3. ✅ `app/Http/Controllers/API/V1/User/Auth/EditProfileController.php` - Controller
4. ✅ `src/Infra/User/Models/User.php` - Model with avatar_url accessor
5. ✅ `database/migrations/2024_05_21_020650_add_user_avatar.php` - Migration (already exists)

### Storage Configuration:
- Disk: `s3` (MinIO)
- Avatar path: `avatars/`
- Old avatars automatically deleted on update
- Public URL automatically generated

### Security:
- ✅ Authentication required
- ✅ Email uniqueness validated (except current user)
- ✅ Password hashed with bcrypt
- ✅ File type validation (only images)
- ✅ File size limit (2MB)
- ✅ Password confirmation required

## Notes
- Semua field bersifat **optional** - bisa update satu, beberapa, atau semua field sekaligus
- Avatar otomatis dapat URL publik melalui accessor `avatar_url`
- Old avatar otomatis dihapus saat upload avatar baru untuk menghemat storage
- Support both `PUT` dan `PATCH` methods untuk flexibility
