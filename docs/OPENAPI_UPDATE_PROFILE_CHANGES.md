# OpenAPI Documentation Update - User Profile Management

## 📋 Summary of Changes

This document outlines the updates made to `docs/openapi.yaml` to document the User Profile Update API with Avatar Upload functionality.

---

## 🔄 Changes Made

### 1. **Updated `User` Schema**
**Location:** `components/schemas/User`

**Added Fields:**
```yaml
avatar: 
  type: string
  nullable: true
  description: S3 object key for user avatar (e.g., "avatars/abc123.jpg")
avatar_url:
  type: string
  format: uri
  nullable: true
  description: Public URL to access the avatar image
```

**Impact:** All User objects in API responses will now include `avatar` and `avatar_url` fields.

---

### 2. **Added `UpdateProfileRequest` Schema**
**Location:** `components/schemas/UpdateProfileRequest`

**New Schema:**
```yaml
UpdateProfileRequest:
  type: object
  description: Request schema for updating user profile. All fields are optional for partial updates.
  properties:
    name:
      type: string
      maxLength: 255
      description: User's full name
    email:
      type: string
      format: email
      maxLength: 255
      description: User's email address (must be unique)
    password:
      type: string
      format: password
      minLength: 8
      description: New password (requires password_confirmation)
    password_confirmation:
      type: string
      format: password
      minLength: 8
      description: Password confirmation (required when updating password)
    avatar:
      type: string
      format: binary
      description: Avatar image file (JPEG, JPG, PNG, GIF) - Max 2MB
```

---

### 3. **Enhanced `PUT /api/v1/auth` Endpoint**
**Tag:** Auth  
**Summary:** Update current user profile (full update)

**Key Updates:**
- ✅ Comprehensive description with usage instructions
- ✅ Support for both `application/json` and `multipart/form-data`
- ✅ Detailed request examples for different use cases
- ✅ Complete response schemas with examples
- ✅ Error response documentation (422, 401)
- ✅ Avatar upload documentation with validation rules

**Request Body Examples:**
1. **JSON** - Update name and email
2. **JSON** - Update password only
3. **Form Data** - Update with avatar

**Response Examples:**
- Success (200) with avatar_url
- Validation Error (422)
- Unauthenticated (401)

---

### 4. **Enhanced `PATCH /api/v1/auth` Endpoint**
**Tag:** Auth  
**Summary:** Partially update current user profile

**Key Updates:**
- ✅ Detailed description with validation rules
- ✅ Support for both `application/json` and `multipart/form-data`
- ✅ Multiple request examples (name only, email only, avatar only)
- ✅ Complete response schemas with examples
- ✅ Error response documentation with specific scenarios

**Request Body Examples:**
1. **JSON** - Update name only
2. **JSON** - Update email only
3. **Form Data** - Upload avatar only

**Response Examples:**
- Success (200)
- Password Mismatch Error (422)
- Avatar Too Large Error (422)
- Unauthenticated (401)

---

## 📝 Validation Rules Documented

| Field | Rule | Description |
|-------|------|-------------|
| `name` | max: 255 | User's full name |
| `email` | email, unique, max: 255 | Must be valid email and unique (except current user) |
| `password` | min: 8, confirmed | Requires password_confirmation |
| `password_confirmation` | min: 8, required_with:password | Must match password |
| `avatar` | image, mimes: jpeg,jpg,png,gif, max: 2MB | Avatar image file |

---

## 🎯 Endpoint Examples in OpenAPI

### Example 1: Update Name (PATCH)
```yaml
examples:
  updateNameOnly:
    summary: Update name only
    value:
      name: "New Name"
```

### Example 2: Upload Avatar (PATCH)
```yaml
examples:
  uploadAvatarOnly:
    summary: Upload avatar only
    value:
      avatar: "[binary file data]"
```

### Example 3: Full Update with Avatar (PUT)
```yaml
examples:
  updateWithAvatar:
    summary: Update name and avatar
    value:
      name: "John Doe"
      avatar: "[binary file data]"
```

---

## 📤 Response Schema Example

```yaml
success:
  summary: Successful update
  value:
    success: true
    message: "Profile updated successfully"
    data:
      id: 1
      name: "John Doe"
      email: "john.doe@example.com"
      avatar: "avatars/abc123xyz.jpg"
      avatar_url: "https://api-minio.muaraenimkab.go.id/egovreportingmuaraenim/avatars/abc123xyz.jpg"
      unit_id: null
      created_at: "2024-01-15T10:00:00.000000Z"
      updated_at: "2024-01-15T10:30:00.000000Z"
```

---

## 🚨 Error Response Examples

### Validation Error (422)
```yaml
validationError:
  summary: Validation failed
  value:
    message: "The given data was invalid."
    errors:
      email: ["The email has already been taken."]
      password: ["The password confirmation does not match."]
      avatar: ["The avatar must not be greater than 2048 kilobytes."]
```

### Password Mismatch (422)
```yaml
passwordMismatch:
  summary: Password confirmation mismatch
  value:
    message: "The given data was invalid."
    errors:
      password: ["The password confirmation does not match."]
```

### Avatar Too Large (422)
```yaml
avatarTooLarge:
  summary: Avatar file too large
  value:
    message: "The given data was invalid."
    errors:
      avatar: ["The avatar must not be greater than 2048 kilobytes."]
```

---

## 🔑 Key Features Documented

1. ✅ **Partial Update Support** - All fields are optional
2. ✅ **Avatar Upload** - Support for multipart/form-data
3. ✅ **Auto Delete Old Avatar** - Documented in description
4. ✅ **Avatar URL Generation** - Automatic URL in response
5. ✅ **Comprehensive Validation** - All rules documented
6. ✅ **Multiple Content Types** - JSON and multipart/form-data
7. ✅ **Rich Examples** - Multiple use cases covered
8. ✅ **Error Scenarios** - All error cases documented

---

## 📊 Impact on Other Endpoints

Since the `User` schema is used in multiple places, the following endpoints will now automatically include `avatar` and `avatar_url` in their responses:

- `GET /api/v1/auth` - Get current user
- `GET /api/v1/users` - List users (if applicable)
- `GET /api/v1/users/{id}` - Get user detail (if applicable)
- Any endpoint that returns User objects

---

## 🧪 Testing with OpenAPI Tools

You can now test the API using:

1. **Swagger UI** - Import `docs/openapi.yaml`
2. **Postman** - Import OpenAPI spec
3. **Insomnia** - Import OpenAPI spec
4. **Redoc** - For beautiful documentation

### Swagger UI Example:
```bash
# Serve with swagger-ui
npx swagger-ui-watcher docs/openapi.yaml
```

---

## 📚 Related Documentation

- **Implementation Guide:** `docs/IMPLEMENTATION_UPDATE_PROFILE.md`
- **Testing Guide:** `docs/API_UPDATE_PROFILE_TESTING.md`
- **OpenAPI Spec:** `docs/openapi.yaml`
- **PowerShell Test:** `tests/api/test-update-profile.ps1`
- **Bash Test:** `tests/api/test-update-profile.sh`

---

## ✅ Verification Checklist

- [x] User schema updated with avatar fields
- [x] UpdateProfileRequest schema added
- [x] PUT /auth endpoint fully documented
- [x] PATCH /auth endpoint fully documented
- [x] Request examples for all use cases
- [x] Response examples with avatar_url
- [x] Error response examples
- [x] Validation rules documented
- [x] Content-Type variations documented
- [x] Description and summaries added

---

## 🎉 Conclusion

The OpenAPI documentation has been **comprehensively updated** to reflect the new User Profile Update functionality with Avatar Upload support. The documentation now provides:

- Clear request/response schemas
- Multiple examples for different use cases
- Comprehensive validation rules
- Error handling documentation
- Support for both JSON and multipart/form-data

Developers can now use this documentation to:
- Understand the API capabilities
- Generate client SDKs
- Test the API endpoints
- Integrate with frontend applications
