# ✅ Implementasi Update Profile dengan Upload Avatar

## 📌 Ringkasan
Implementasi **Update Profile API** sudah selesai dengan fitur:
- ✅ **Full Update** - Update semua field sekaligus
- ✅ **Partial Update** - Update hanya field tertentu (name, email, password, atau avatar)
- ✅ **Avatar Upload** - Upload foto profil dengan validasi gambar
- ✅ **Auto Delete Old Avatar** - Menghapus avatar lama otomatis saat upload baru
- ✅ **Avatar URL Accessor** - Setiap response user otomatis include `avatar_url`

---

## 📁 File yang Dibuat/Dimodifikasi

### 1. **Validation Request**
📄 `app/Http/Requests/UpdateProfileRequest.php`
- Validasi untuk semua field (name, email, password, avatar)
- Semua field optional (mendukung partial update)
- Email unique validation (kecuali untuk user sendiri)
- Password confirmation required
- Avatar validation: image, max 2MB, format jpeg/jpg/png/gif
- Custom error messages dalam bahasa Indonesia

### 2. **Action Layer**
📄 `src/Domain/User/Actions/Auth/UpdateAuthAction.php`
**Fitur:**
- Hanya update field yang ada di request (partial update support)
- Upload avatar ke S3/MinIO dengan path `avatars/`
- Auto delete old avatar sebelum upload baru
- Hash password dengan bcrypt
- Refresh user data setelah update

### 3. **Controller**
📄 `app/Http/Controllers/API/V1/User/Auth/EditProfileController.php`
- Menggunakan `UpdateProfileRequest` untuk validasi
- Handle file upload dengan proper
- Error handling yang baik

### 4. **User Model**
📄 `src/Infra/User/Models/User.php`
**Penambahan:**
- `avatar_url` accessor untuk generate URL avatar otomatis
- `appends` property untuk include `avatar_url` di response

### 5. **Migration**
📄 `database/migrations/2024_05_21_020650_add_user_avatar.php`
- ✅ **Sudah ada dan sudah dijalankan**
- Menambahkan kolom `avatar` (nullable) di tabel `users`

---

## 🔌 API Endpoints

### **GET** `/api/v1/auth`
Mendapatkan data user yang sedang login
```json
{
  "success": true,
  "message": "Data success to data",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "avatar": "avatars/xyz123.jpg",
    "avatar_url": "https://api-minio.muaraenimkab.go.id/egovreportingmuaraenim/avatars/xyz123.jpg",
    ...
  }
}
```

### **PUT/PATCH** `/api/v1/auth`
Update profile user (full atau partial)

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json (tanpa avatar)
Content-Type: multipart/form-data (dengan avatar)
```

**Request Body (JSON - tanpa avatar):**
```json
{
  "name": "John Doe Updated",
  "email": "john.updated@example.com",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

**Request Body (Form Data - dengan avatar):**
```
name: John Doe
email: john@example.com  
avatar: [FILE]
password: newpassword123
password_confirmation: newpassword123
```

---

## 🧪 Testing

### **Opsi 1: Manual dengan Postman/Thunder Client**

#### Test 1: Partial Update - Update Name Only
```
Method: PATCH
URL: http://localhost:8000/api/v1/auth
Headers:
  Authorization: Bearer {your_token}
  Content-Type: application/json
Body (raw JSON):
{
  "name": "New Name"
}
```

#### Test 2: Upload Avatar Only
```
Method: PATCH
URL: http://localhost:8000/api/v1/auth
Headers:
  Authorization: Bearer {your_token}
Body (form-data):
  avatar: [pilih file gambar]
```

#### Test 3: Full Update
```
Method: PUT
URL: http://localhost:8000/api/v1/auth
Headers:
  Authorization: Bearer {your_token}
Body (form-data):
  name: John Doe
  email: john@example.com
  password: password123
  password_confirmation: password123
  avatar: [pilih file gambar]
```

### **Opsi 2: Script yang Sudah Disediakan**

#### PowerShell (Windows):
```powershell
cd tests/api
$TOKEN = "your_actual_token"
.\test-update-profile.ps1
```

#### Bash (Linux/Mac):
```bash
cd tests/api
TOKEN="your_actual_token" ./test-update-profile.sh
```

### **Opsi 3: Curl Manual**

```bash
# Get current user
curl -X GET http://localhost:8000/api/v1/auth \
  -H "Authorization: Bearer YOUR_TOKEN"

# Update name only
curl -X PATCH http://localhost:8000/api/v1/auth \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name": "New Name"}'

# Upload avatar
curl -X PATCH http://localhost:8000/api/v1/auth \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "avatar=@/path/to/image.jpg"

# Update name + avatar
curl -X PATCH http://localhost:8000/api/v1/auth \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "name=John Doe" \
  -F "avatar=@/path/to/image.jpg"
```

---

## ✅ Validation Rules

| Field | Rules | Description |
|-------|-------|-------------|
| `name` | optional, string, max:255 | Nama user |
| `email` | optional, email, unique (except current user) | Email user |
| `password` | optional, min:8, confirmed | Password baru |
| `password_confirmation` | required_with:password, min:8 | Konfirmasi password |
| `avatar` | optional, file, image, mimes:jpeg,jpg,png,gif, max:2MB | Foto profil |

---

## 🔒 Security Features

1. ✅ **Authentication Required** - Endpoint dilindungi dengan `auth:api` middleware
2. ✅ **Email Uniqueness** - Validasi email unique kecuali untuk user sendiri
3. ✅ **Password Hashing** - Password di-hash dengan bcrypt
4. ✅ **Password Confirmation** - Wajib confirm password saat update
5. ✅ **File Type Validation** - Hanya accept image files
6. ✅ **File Size Limit** - Maximum 2MB untuk avatar
7. ✅ **Old File Cleanup** - Avatar lama otomatis dihapus untuk prevent storage bloat

---

## 📂 Storage Configuration

- **Disk**: `s3` (MinIO)
- **Bucket**: `egovreportingmuaraenim`
- **Avatar Path**: `avatars/`
- **Public URL**: `https://api-minio.muaraenimkab.go.id/egovreportingmuaraenim/avatars/{filename}`

---

## 🎯 Use Cases

### 1. User ingin update nama saja
```json
PATCH /api/v1/auth
{
  "name": "Nama Baru"
}
```
✅ Hanya field `name` yang berubah, field lain tetap

### 2. User ingin ganti password
```json
PATCH /api/v1/auth
{
  "password": "password_baru123",
  "password_confirmation": "password_baru123"
}
```
✅ Password di-hash dan tersimpan, field lain tidak terpengaruh

### 3. User ingin upload/ganti foto profil
```
PATCH /api/v1/auth
Form-data:
  avatar: [file]
```
✅ Avatar lama otomatis dihapus, avatar baru terupload, `avatar_url` otomatis tersedia

### 4. User ingin update semua data sekaligus
```
PUT /api/v1/auth
Form-data:
  name: "John Doe"
  email: "john@example.com"
  password: "newpass123"
  password_confirmation: "newpass123"
  avatar: [file]
```
✅ Semua field terupdate dalam 1 request

### 5. User hanya ingin lihat data profile
```
GET /api/v1/auth
```
✅ Response include `avatar_url` otomatis

---

## 📊 Response Examples

### Success Response
```json
{
  "success": true,
  "message": "Profile updated successfully",
  "data": {
    "id": 1,
    "name": "John Doe Updated",
    "email": "john.updated@example.com",
    "avatar": "avatars/abc123def456.jpg",
    "avatar_url": "https://api-minio.muaraenimkab.go.id/egovreportingmuaraenim/avatars/abc123def456.jpg",
    "email_verified_at": null,
    "created_at": "2024-01-15T10:00:00.000000Z",
    "updated_at": "2024-01-15T10:30:00.000000Z",
    "unit_id": 1,
    "deleted_at": null
  }
}
```

### Validation Error Response
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email has already been taken."],
    "password": ["The password confirmation does not match."],
    "avatar": ["The avatar must not be greater than 2048 kilobytes."]
  }
}
```

---

## 🚀 Cara Menggunakan di Frontend

```javascript
// Update name only
const updateName = async (name) => {
  const response = await fetch('http://localhost:8000/api/v1/auth', {
    method: 'PATCH',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ name })
  });
  return response.json();
};

// Upload avatar
const uploadAvatar = async (file) => {
  const formData = new FormData();
  formData.append('avatar', file);
  
  const response = await fetch('http://localhost:8000/api/v1/auth', {
    method: 'PATCH',
    headers: {
      'Authorization': `Bearer ${token}`
    },
    body: formData
  });
  return response.json();
};

// Update profile dengan avatar
const updateProfile = async (name, email, avatarFile) => {
  const formData = new FormData();
  formData.append('name', name);
  formData.append('email', email);
  if (avatarFile) {
    formData.append('avatar', avatarFile);
  }
  
  const response = await fetch('http://localhost:8000/api/v1/auth', {
    method: 'PATCH',
    headers: {
      'Authorization': `Bearer ${token}`
    },
    body: formData
  });
  return response.json();
};
```

---

## ✅ Checklist Implementasi

- [x] Migration untuk kolom `avatar` (sudah ada dan running)
- [x] Validation Request dengan rules lengkap
- [x] Action untuk handle upload avatar
- [x] Action untuk handle partial update
- [x] Auto delete old avatar
- [x] Model accessor untuk `avatar_url`
- [x] Controller yang proper
- [x] Route sudah terdaftar (PUT dan PATCH)
- [x] Error handling yang baik
- [x] Dokumentasi lengkap
- [x] Test scripts (PowerShell & Bash)

---

## 📚 Dokumentasi Tambahan

- **Testing Guide**: `docs/API_UPDATE_PROFILE_TESTING.md`
- **PowerShell Test Script**: `tests/api/test-update-profile.ps1`
- **Bash Test Script**: `tests/api/test-update-profile.sh`

---

## 🎉 Kesimpulan

Implementasi **Update Profile dengan Upload Avatar** sudah **100% selesai** dan siap digunakan. Fitur ini mendukung:

✅ **Partial Update** - Bisa update field apapun secara terpisah  
✅ **Full Update** - Bisa update semua field sekaligus  
✅ **Avatar Upload** - Upload gambar dengan validasi proper  
✅ **Auto Cleanup** - Avatar lama otomatis terhapus  
✅ **Public URL** - Avatar URL otomatis tersedia di setiap response  

Silakan test menggunakan salah satu metode testing yang sudah disediakan! 🚀
