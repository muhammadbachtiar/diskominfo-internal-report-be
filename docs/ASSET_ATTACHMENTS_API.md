# Asset Attachments API Documentation

## Overview

The Asset Attachments system provides complete CRUD functionality for managing file attachments associated with assets. It supports cloud storage (S3), automatic file validation, virus scanning integration, and comprehensive metadata tracking.

## Features

- ✅ Cloud storage integration (S3-compatible)
- ✅ Multiple file type support (images, PDFs, documents)
- ✅ File size validation (max 16MB per file, 100MB per asset)
- ✅ Automatic image dimension detection
- ✅ Checksum-based duplicate detection
- ✅ Virus scanning integration points
- ✅ Flexible tagging system
- ✅ Secure temporary download URLs
- ✅ Soft delete support
- ✅ Comprehensive metadata tracking

## Database Schema

### Table: `asset_attachments`

```sql
CREATE TABLE asset_attachments (
    id UUID PRIMARY KEY,
    asset_id UUID NOT NULL,
    uploaded_by BIGINT NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_category VARCHAR(100) NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_size BIGINT NOT NULL,
    object_key VARCHAR(255) UNIQUE NOT NULL,
    checksum VARCHAR(64) NOT NULL,
    storage_path VARCHAR(500) NULL,
    width INTEGER NULL,
    height INTEGER NULL,
    is_compressed BOOLEAN DEFAULT FALSE,
    original_size BIGINT NULL,
    is_scanned BOOLEAN DEFAULT FALSE,
    scan_status VARCHAR(50) NULL,
    scan_result JSON NULL,
    tags JSON NULL,
    created_at TIMESTAMP WITH TIME ZONE NOT NULL,
    updated_at TIMESTAMP WITH TIME ZONE NOT NULL,
    deleted_at TIMESTAMP WITH TIME ZONE NULL,
    
    FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE RESTRICT
);
```

## API Endpoints

### 1. Get Presigned Upload URL

**Endpoint:** `POST /api/v1/assets/{asset}/attachments/presign`

**Description:** Generate a presigned URL for direct file upload to S3.

**Request:**
```json
{
    "original_name": "warranty-certificate.pdf",
    "mime_type": "application/pdf",
    "file_size": 2457600
}
```

**Response:**
```json
{
    "success": true,
    "message": "Presign URL generated successfully",
    "data": {
        "url": "https://s3.amazonaws.com/bucket/assets/uuid/attachments/uuid.pdf?...",
        "object_key": "assets/uuid/attachments/uuid.pdf",
        "expires_at": "2025-12-31T10:35:00Z"
    }
}
```

**Supported MIME Types:**
- Images: `image/jpeg`, `image/png`, `image/webp`
- Documents: `application/pdf`, `application/msword`, `application/vnd.openxmlformats-officedocument.wordprocessingml.document`

### 2. Finalize Upload

**Endpoint:** `POST /api/v1/assets/{asset}/attachments/finalize`

**Description:** Complete the upload process and create attachment record.

**Request:**
```json
{
    "object_key": "assets/uuid/attachments/uuid.pdf",
    "original_name": "warranty-certificate.pdf",
    "mime_type": "application/pdf",
    "file_size": 2457600,
    "file_category": "warranty",
    "tags": ["important", "2024"]
}
```

**Response:**
```json
{
    "success": true,
    "message": "Attachment uploaded successfully",
    "data": {
        "id": "01JGXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX",
        "asset_id": "01JGYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY",
        "original_name": "warranty-certificate.pdf",
        "file_category": "warranty",
        "mime_type": "application/pdf",
        "file_size": 2457600,
        "human_file_size": "2.34 MB",
        "width": null,
        "height": null,
        "is_compressed": false,
        "is_scanned": false,
        "scan_status": null,
        "is_clean": false,
        "has_threats": false,
        "tags": ["important", "2024"],
        "is_image": false,
        "is_pdf": true,
        "is_document": true,
        "uploaded_by": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com"
        },
        "created_at": "2025-12-31T10:30:00Z",
        "updated_at": "2025-12-31T10:30:00Z"
    }
}
```

### 3. List Attachments

**Endpoint:** `GET /api/v1/assets/{asset}/attachments`

**Description:** Get all attachments for an asset with filtering and pagination.

**Query Parameters:**
- `file_category` (optional): Filter by category
- `tags` (optional): Filter by tags (comma-separated)
- `scan_status` (optional): Filter by scan status (`clean`, `infected`, `failed`, `pending`)
- `order_by` (optional): Sort field (default: `created_at`)
- `order_direction` (optional): Sort direction (`asc`, `desc`, default: `desc`)
- `per_page` (optional): Items per page (default: 15)
- `page` (optional): Page number

**Example:**
```
GET /api/v1/assets/{asset}/attachments?file_category=warranty&per_page=10&page=1
```

**Response:**
```json
{
    "success": true,
    "message": "Attachments retrieved successfully",
    "data": [
        {
            "id": "01JGXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX",
            "asset_id": "01JGYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY",
            "original_name": "warranty-certificate.pdf",
            "file_category": "warranty",
            "mime_type": "application/pdf",
            "file_size": 2457600,
            "human_file_size": "2.34 MB",
            "tags": ["important", "2024"],
            "is_pdf": true,
            "uploaded_by": {
                "id": 1,
                "name": "John Doe",
                "email": "john@example.com"
            },
            "created_at": "2025-12-31T10:30:00Z"
        }
    ],
    "meta": {
        "current_page": 1,
        "per_page": 10,
        "total": 5
    }
}
```

### 4. Get Attachment Details

**Endpoint:** `GET /api/v1/attachments/{attachment}`

**Description:** Get detailed information about a specific attachment.

**Query Parameters:**
- `include_download_url` (optional): Include temporary download URL (default: false)

**Response:**
```json
{
    "success": true,
    "message": "Attachment retrieved successfully",
    "data": {
        "id": "01JGXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX",
        "asset_id": "01JGYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY",
        "original_name": "warranty-certificate.pdf",
        "file_category": "warranty",
        "mime_type": "application/pdf",
        "file_size": 2457600,
        "human_file_size": "2.34 MB",
        "checksum": "abc123...",
        "width": null,
        "height": null,
        "is_compressed": false,
        "is_scanned": true,
        "scan_status": "clean",
        "is_clean": true,
        "has_threats": false,
        "tags": ["important", "2024"],
        "is_image": false,
        "is_pdf": true,
        "is_document": true,
        "uploaded_by": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com"
        },
        "created_at": "2025-12-31T10:30:00Z",
        "updated_at": "2025-12-31T10:30:00Z",
        "download_url": "https://s3.amazonaws.com/bucket/...?X-Amz-Expires=300"
    }
}
```

### 5. Update Attachment Metadata

**Endpoint:** `PUT /api/v1/attachments/{attachment}`

**Description:** Update attachment category and tags (file itself cannot be modified).

**Request:**
```json
{
    "file_category": "warranty",
    "tags": ["important", "2024", "renewed"]
}
```

**Response:**
```json
{
    "success": true,
    "message": "Attachment updated successfully",
    "data": {
        "id": "01JGXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX",
        "file_category": "warranty",
        "tags": ["important", "2024", "renewed"],
        "updated_at": "2025-12-31T10:35:00Z"
    }
}
```

### 6. Download Attachment

**Endpoint:** `GET /api/v1/attachments/{attachment}/download`

**Description:** Get a temporary signed URL for downloading the file.

**Response:**
```json
{
    "success": true,
    "message": "Download URL generated successfully",
    "data": {
        "download_url": "https://s3.amazonaws.com/bucket/...?X-Amz-Expires=300",
        "filename": "warranty-certificate.pdf",
        "mime_type": "application/pdf",
        "file_size": 2457600,
        "expires_at": "2025-12-31T10:35:00Z"
    }
}
```

### 7. Delete Attachment

**Endpoint:** `DELETE /api/v1/attachments/{attachment}`

**Description:** Soft delete an attachment (removes from database and optionally from storage).

**Response:**
```json
{
    "success": true,
    "message": "Attachment deleted successfully"
}
```

## Upload Workflow

### Step 1: Request Presigned URL
```javascript
const presignResponse = await fetch('/api/v1/assets/{assetId}/attachments/presign', {
    method: 'POST',
    headers: {
        'Authorization': 'Bearer YOUR_TOKEN',
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        original_name: file.name,
        mime_type: file.type,
        file_size: file.size
    })
});

const { url, object_key } = await presignResponse.json();
```

### Step 2: Upload File to S3
```javascript
await fetch(url, {
    method: 'PUT',
    headers: {
        'Content-Type': file.type
    },
    body: file
});
```

### Step 3: Finalize Upload
```javascript
const finalizeResponse = await fetch('/api/v1/assets/{assetId}/attachments/finalize', {
    method: 'POST',
    headers: {
        'Authorization': 'Bearer YOUR_TOKEN',
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        object_key: object_key,
        original_name: file.name,
        mime_type: file.type,
        file_size: file.size,
        file_category: 'warranty',
        tags: ['important']
    })
});

const attachment = await finalizeResponse.json();
```

## File Validation Rules

### Size Limits
- **Per File:** Maximum 16MB (16,777,216 bytes)
- **Per Asset:** Maximum 100MB (104,857,600 bytes) total

### Supported File Types

**Images:**
- JPEG (`.jpg`, `.jpeg`)
- PNG (`.png`)
- WebP (`.webp`)

**Documents:**
- PDF (`.pdf`)
- Microsoft Word (`.doc`, `.docx`)
- Microsoft Excel (`.xls`, `.xlsx`)

### Security Features

1. **Checksum Validation:** SHA-256 hash prevents duplicate uploads
2. **Virus Scanning:** Integration points for ClamAV or cloud scanners
3. **MIME Type Verification:** Server-side validation of file types
4. **Signed URLs:** Time-limited access (5 minutes) for downloads
5. **Permission-Based Access:** Reuses existing `view-asset` permission

## Error Handling

### Common Error Responses

**File Too Large:**
```json
{
    "success": false,
    "message": "File size exceeds maximum limit of 16MB"
}
```

**Storage Limit Exceeded:**
```json
{
    "success": false,
    "message": "Total attachment size would exceed 100MB limit for this asset"
}
```

**Duplicate File:**
```json
{
    "success": false,
    "message": "This file has already been uploaded"
}
```

**Invalid File Type:**
```json
{
    "success": false,
    "message": "Validation Error",
    "errors": {
        "mime_type": ["The selected mime type is invalid."]
    }
}
```

**Retired Asset:**
```json
{
    "success": false,
    "message": "Cannot upload attachments to retired assets"
}
```

## Integration Examples

### React/TypeScript Example

```typescript
interface UploadAttachmentParams {
    assetId: string;
    file: File;
    category?: string;
    tags?: string[];
}

async function uploadAttachment({
    assetId,
    file,
    category,
    tags
}: UploadAttachmentParams) {
    // Step 1: Get presigned URL
    const presignRes = await api.post(
        `/assets/${assetId}/attachments/presign`,
        {
            original_name: file.name,
            mime_type: file.type,
            file_size: file.size
        }
    );
    
    const { url, object_key } = presignRes.data;
    
    // Step 2: Upload to S3
    await fetch(url, {
        method: 'PUT',
        headers: { 'Content-Type': file.type },
        body: file
    });
    
    // Step 3: Finalize
    const finalizeRes = await api.post(
        `/assets/${assetId}/attachments/finalize`,
        {
            object_key,
            original_name: file.name,
            mime_type: file.type,
            file_size: file.size,
            file_category: category,
            tags
        }
    );
    
    return finalizeRes.data;
}
```

### Vue.js Example

```vue
<template>
  <div>
    <input type="file" @change="handleFileSelect" />
    <button @click="uploadFile" :disabled="!selectedFile || uploading">
      {{ uploading ? 'Uploading...' : 'Upload' }}
    </button>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useAssetAttachments } from '@/composables/useAssetAttachments';

const props = defineProps(['assetId']);
const { uploadAttachment } = useAssetAttachments();

const selectedFile = ref(null);
const uploading = ref(false);

const handleFileSelect = (event) => {
  selectedFile.value = event.target.files[0];
};

const uploadFile = async () => {
  if (!selectedFile.value) return;
  
  uploading.value = true;
  try {
    await uploadAttachment({
      assetId: props.assetId,
      file: selectedFile.value,
      category: 'documentation',
      tags: ['uploaded-from-ui']
    });
    alert('File uploaded successfully!');
  } catch (error) {
    alert('Upload failed: ' + error.message);
  } finally {
    uploading.value = false;
  }
};
</script>
```

## Best Practices

1. **Always validate file size on client-side** before requesting presigned URL
2. **Show upload progress** to users for better UX
3. **Handle network errors** gracefully with retry logic
4. **Use appropriate file categories** for better organization
5. **Add meaningful tags** for easier searching and filtering
6. **Check scan status** before allowing downloads of uploaded files
7. **Implement proper error handling** for all API calls
8. **Use pagination** when listing attachments for assets with many files

## Performance Considerations

- Presigned URLs expire after 5 minutes
- Download URLs expire after 5 minutes
- Attachments are indexed by `asset_id`, `file_category`, and `created_at`
- Soft deletes allow for data recovery if needed
- Use `include_download_url=false` when listing to reduce API response time

## Security Notes

- All endpoints require authentication (`auth:api` middleware)
- File access follows asset permissions (`view-asset`)
- Virus scanning should be implemented via background jobs
- Checksums prevent file tampering
- Signed URLs prevent unauthorized access
- File uploads go directly to S3 (not through application server)