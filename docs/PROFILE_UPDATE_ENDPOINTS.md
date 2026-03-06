# Profile Update API - Separated Endpoints

## Endpoints

### 1. Update Profile Data (JSON Only)
```
PUT/PATCH /api/v1/auth
Content-Type: application/json

{
  "name": "New Name",
  "email": "new@email.com",
  "password": "newpass123",
  "password_confirmation": "newpass123"
}
```

### 2. Upload Avatar (Multipart)
```
POST /api/v1/auth/avatar
Content-Type: multipart/form-data

avatar: [file]
```

## Files Created
1. `app/Http/Controllers/API/V1/User/Auth/UploadAvatarController.php`
2. `src/Domain/User/Actions/Auth/UploadAvatarAction.php`

## Files Modified
1. `app/Http/Controllers/API/V1/User/Auth/EditProfileController.php` - Simplified, JSON only
2. `src/Domain/User/Actions/Auth/UpdateAuthAction.php` - Removed avatar handling
3. `app/Http/Requests/UpdateProfileRequest.php` - Removed avatar validation
4. `routes/api/v1.php` - Added POST /auth/avatar route

## Testing

### Update Name
```bash
curl -X PATCH http://localhost:8000/api/v1/auth \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"New Name"}'
```

### Upload Avatar
```bash
curl -X POST http://localhost:8000/api/v1/auth/avatar \
  -H "Authorization: Bearer TOKEN" \
  -F "avatar=@photo.jpg"
```

Response will include `avatar` (S3 path) and `avatar_url` (public URL).
