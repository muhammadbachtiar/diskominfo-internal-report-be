# Debug Update Profile - Comprehensive Testing Guide

## 🔍 Issue
Data tidak berubah ketika UPDATE dengan multipart form data dari Swagger UI atau Frontend.

## ✅ Solution Implemented

Saya sudah menambahkan **comprehensive logging** di:
1. **Controller** - Track request masuk
2. **Action** - Track update process

---

## 🧪 How to Debug

### **Step 1: Start Log Monitoring**

Open terminal dan jalankan:

```powershell
# Watch Laravel logs in real-time
docker exec internal_reporting tail -f storage/logs/laravel.log
```

Biarkan terminal ini tetap running.

---

### **Step 2: Test Update**

Di terminal lain, test dengan salah satu cara dibawah:

#### **Option A: Swagger UI**
1. Buka browser: `http://localhost:8080` (jika sudah setup swagger-ui)
2. Atau import `docs/openapi.yaml` ke Swagger Editor
3. Authorize dengan token
4. Cari endpoint **PATCH /api/v1/auth**
5. Click "Try it out"
6. **Content-Type:** pilih `multipart/form-data`
7. Fill fields:
   - `name`: "Test Name from Swagger"
   - `avatar`: [Choose File] (jika mau upload)
8. Click "Execute"

#### **Option B: Postman**
1. Method: **PATCH**
2. URL: `http://localhost:8000/api/v1/auth`
3. Headers:
   - `Authorization`: `Bearer YOUR_TOKEN`
4. Body: **form-data** (PENTING!)
   - Key: `name` | Value: "Test Name from Postman"
   - Key: `avatar` | Type: **File** | Value: [Choose file]
5. Send

#### **Option C: cURL**
```bash
curl -X PATCH http://localhost:8000/api/v1/auth \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "name=Test Name from cURL"
```

---

### **Step 3: Check Logs**

Di terminal pertama (yang running `tail -f`), Anda akan melihat log seperti ini:

#### ✅ **SUCCESS Pattern (Data Updated)**

```
[2026-01-15 12:20:00] local.INFO: UpdateProfile - Request received 
{
  "content_type":"multipart/form-data; boundary=...",
  "has_file_avatar":false,
  "input_keys":["name"]
}

[2026-01-15 12:20:00] local.INFO: UpdateProfile - Data to be processed 
{
  "keys":["name"],
  "name":"Test Name from Postman",
  "email":null,
  "has_avatar":false
}

[2026-01-15 12:20:00] local.INFO: UpdateAuthAction - Start 
{
  "user_id":1,
  "data_keys":["name"]
}

[2026-01-15 12:20:00] local.INFO: UpdateAuthAction - Name will be updated 
{
  "name":"Test Name from Postman"
}

[2026-01-15 12:20:00] local.INFO: UpdateAuthAction - Fields to be updated 
{
  "fields":["name"],
  "count":1
}

[2026-01-15 12:20:00] local.INFO: UpdateAuthAction - Database updated

[2026-01-15 12:20:00] local.INFO: UpdateAuthAction - Complete 
{
  "name":"Test Name from Postman",  ← UPDATED!
  "email":"user@example.com",
  "avatar":null
}

[2026-01-15 12:20:00] local.INFO: UpdateProfile - Success 
{
  "user_id":1,
  "name":"Test Name from Postman",  ← SUCCESS!
  "email":"user@example.com",
  "avatar":null
}
```

#### ❌ **PROBLEM Patterns**

**Pattern 1: Data tidak sampai ke request**
```
UpdateProfile - Request received 
{
  "input_keys":[]  ← EMPTY! No data dari form
}
```
**Problem:** Form data tidak dikirim dengan benar
**Fix:** Pastikan Content-Type adalah `multipart/form-data`

---

**Pattern 2: Validation gagal**
```
UpdateProfile - Request received 
{
  "input_keys":["name"]  ← Data ada
}

[ERROR] Validation Error
```
**Problem:** Data tidak pass validation
**Fix:** Check validation rules atau format data

---

**Pattern 3: Data ada tapi tidak masuk ke validated()**
```
UpdateProfile - Request received 
{
  "input_keys":["name"]  ← Data ada di request
}

UpdateProfile - Data to be processed 
{
  "keys":[],  ← KOSONG! Data hilang setelah validated()
  "name":null
}
```
**Problem:** `validated()` tidak return data
**Fix:** Check UpdateProfileRequest validation rules

---

**Pattern 4: Data sampai ke Action tapi tidak update**
```
UpdateAuthAction - Fields to be updated 
{
  "fields":["name"],
  "count":1
}

UpdateAuthAction - Database updated

UpdateAuthAction - Complete 
{
  "name":"Old Name",  ← TIDAK BERUBAH!
}
```
**Problem:** Database tidak ter-update
**Fix:** Check user model fillable atau database connection

---

**Pattern 5: No data to update**
```
UpdateAuthAction - Fields to be updated 
{
  "fields":[],  ← KOSONG!
  "count":0
}

[WARNING] UpdateAuthAction - No data to update!
```
**Problem:** Data tidak sampai ke Action
**Fix:** Check controller data passing

---

## 🔎 **What Each Log Tells You**

| Log Entry | What It Means | If Empty/Missing |
|-----------|---------------|------------------|
| `UpdateProfile - Request received` | Request masuk ke controller | Server issue |
| `input_keys` | Field apa saja yang dikirim | Form tidak submit data |
| `has_file_avatar` | Apakah ada file upload | File tidak terdetect |
| `UpdateProfile - Data to be processed` | Data setelah validasi | Validation gagal |
| `UpdateAuthAction - Start` | Action mulai execute | Controller error |
| `UpdateAuthAction - Name will be updated` | Field name akan diupdate | Field tidak ada di data |
| `UpdateAuthAction - Fields to be updated` | Field apa yang masuk update | Tidak ada data untuk update |
| `UpdateAuthAction - Database updated` | Database berhasil update | Update gagal/skip |
| `UpdateAuthAction - Complete` | Hasil akhir user data | - |
| `UpdateProfile - Success` | Response success ke client | - |

---

## 📊 **Common Issues & Solutions**

### **Issue 1: Content-Type Wrong**

**Symptoms:**
- `input_keys: []` (empty)
- Data tidak sampai ke server

**Fix:**
```
Di Postman:
Body → form-data (BUKAN raw atau x-www-form-urlencoded!)

Di Swagger:
Content-Type → multipart/form-data (dari dropdown)

Di cURL:
Gunakan -F flag, BUKAN -d
curl -F "name=Test" (CORRECT)
curl -d '{"name":"Test"}' (WRONG untuk multipart)
```

---

### **Issue 2: Validation Using 'nullable' Instead of 'sometimes'**

**Symptoms:**
- `input_keys` ada data
- `validated_keys` empty

**Check:**
File: `app/Http/Requests/UpdateProfileRequest.php`

Should be:
```php
'name' => ['sometimes', 'string', 'max:255'],  // ← 'sometimes' untuk partial update
```

NOT:
```php
'name' => ['nullable', 'string', 'max:255'],  // ← 'nullable' akan require field
```

---

### **Issue 3: Model Not Fillable**

**Symptoms:**
- Log shows "Database updated"
- But data tidak berubah di database

**Check:**
File: `src/Infra/User/Models/User.php` atau `src/Infra/Shared/Models/AuthModel.php`

Should have:
```php
protected $guarded = ['id'];  // Allow all except id
```

Or:
```php
protected $fillable = ['name', 'email', 'password', 'avatar'];  // Explicit allow
```

---

### **Issue 4: File Upload Not Detected**

**Symptoms:**
- `has_file_avatar: false` even though file selected

**Fix:**
```
Postman:
- Key: avatar
- Type: File (change dropdown dari Text ke File!)
- Value: [Choose File button]

cURL:
curl -F "avatar=@/path/to/file.jpg"  # @ symbol penting!
```

---

## 🎯 **Quick Debug Checklist**

Jalankan test dan check di logs:

- [ ] `input_keys` shows your fields? → Form sending data ✅
- [ ] `validated_keys` shows your fields? → Validation passed ✅
- [ ] `data_keys` in Action shows fields? → Data reached Action ✅
- [ ] `Fields to be updated` not empty? → Update will happen ✅
- [ ] `Database updated` log appears? → DB write successful ✅
- [ ] Final `name` value changed? → Update successful! ✅

---

## 🚀 **Testing Steps**

### **Test 1: Update Name Only**
```bash
curl -X PATCH http://localhost:8000/api/v1/auth \
  -H "Authorization: Bearer $TOKEN" \
  -F "name=New Name $(date +%H:%M:%S)"
```

**Expected Logs:**
```
input_keys: ["name"]
validated_keys: ["name"]
Name will be updated: "New Name ..."
Database updated
Complete: name="New Name ..."
```

### **Test 2: Upload Avatar**
```bash
curl -X PATCH http://localhost:8000/api/v1/auth \
  -H "Authorization: Bearer $TOKEN" \
  -F "avatar=@photo.jpg"
```

**Expected Logs:**
```
has_file_avatar: true
Avatar file detected
Processing avatar upload
Avatar uploaded: "avatars/xyz.jpg"
Database updated
```

### **Test 3: Update Name + Avatar**
```bash
curl -X PATCH http://localhost:8000/api/v1/auth \
  -H "Authorization: Bearer $TOKEN" \
  -F "name=With Avatar" \
  -F "avatar=@photo.jpg"
```

**Expected Logs:**
```
input_keys: ["name", "avatar"]
Name will be updated
Processing avatar upload
Database updated
```

---

## 📝 **How to Share Debug Info**

If still having issues, copy the relevant logs:

```bash
# Get last 100 lines of logs
docker exec internal_reporting tail -n 100 storage/logs/laravel.log > debug.log
```

Then share:
1. Request you sent (method, headers, body)
2. Log output dari `debug.log`
3. Expected vs actual result

---

## ✅ **What Logging Will Reveal**

Dengan logging ini, kita bisa pinpoint exactly:

1. ✅ Apakah data sampai ke server?
2. ✅ Apakah validation pass?
3. ✅ Apakah data sampai ke Action?
4. ✅ Field mana yang akan di-update?
5. ✅ Apakah database write berhasil?
6. ✅ Apa hasil akhir update?

**No more guessing!** Logs akan tell you exactly mana step yang gagal. 🎯
