# Letter Analysis AI - Dynamic Prompt Based on Letter Type

## 📋 Overview
API analyze letter sekarang sudah mendukung **dynamic prompting** berdasarkan tipe surat (masuk/keluar) untuk ekstraksi data yang lebih akurat.

---

## 🎯 Changes Made

### **1. Controller Update**
File: `app/Http/Controllers/API/V1/LetterController.php`

**Added:**
- Validation untuk `type` parameter (required: `incoming` atau `outgoing`)
- Pass `type` parameter ke Action

```php
$request->validate([
    'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
    'type' => 'required|string|in:incoming,outgoing', // NEW!
]);

$result = $action->execute(
    $request->file('file'),
    $request->input('type')  // NEW!
);
```

---

### **2. Action Update**
File: `src/Domain/Letter/Actions/AnalyzeLetterAction.php`

**Updated:**
```php
public function execute(UploadedFile $file, string $letterType)
{
    // ... 
    return $this->geminiService->analyze($content, $mimeType, $letterType);
}
```

---

### **3. Service Update - Dynamic Prompting**
File: `src/Domain/Letter/Services/GeminiLetterAnalysisService.php`

**Key Changes:**

#### **For Incoming Letters (`type=incoming`):**
```
sender_receiver: Surat ini adalah SURAT MASUK. 
Cari nama instansi/organisasi PENGIRIM di Kop Surat 
(bagian paling atas dengan logo dan nama instansi).
```

**Contoh Surat Masuk:**
```
┌─────────────────────────────────────┐
│ [LOGO] PEMERINTAH KOTA ABC          │ ← AI akan ambil ini sebagai PENGIRIM
│        DINAS PENDIDIKAN             │
├─────────────────────────────────────┤
│                                     │
│ Nomor  : 123/ABC/2024               │
│ Hal    : Undangan Rapat             │
│ Lampiran: -                         │
│                                     │
│ Yth. Kepala Dinas Kominfo           │
│ Di Tempat                           │
└─────────────────────────────────────┘
```

**AI Result:**
```json
{
  "sender_receiver": "PEMERINTAH KOTA ABC DINAS PENDIDIKAN",
  ...
}
```

#### **For Outgoing Letters (`type=outgoing`):**
```
sender_receiver: Surat ini adalah SURAT KELUAR. 
Cari PENERIMA di bagian 'Yth.' (Yth: [Nama Penerima]) 
yang biasanya terletak di kiri atas setelah bagian Nomor, 
Hal, Perihal, dll. Jika ada beberapa baris setelah 'Yth.', 
ambil semuanya.
```

**Contoh Surat Keluar:**
```
┌─────────────────────────────────────┐
│ [LOGO] DINAS KOMINFO                │
│        PEMERINTAH KOTA XYZ          │
├─────────────────────────────────────┤
│                                     │
│ Nomor     : 456/XYZ/2024            │
│ Hal       : Undangan Rapat          │
│ Lampiran  : -                       │
│ Tanggal   : 15 Januari 2026         │
│                                     │
│ Yth. Kepala Dinas Pendidikan        │ ← AI akan ambil ini sebagai PENERIMA
│      Kota ABC                       │ ← (bisa multi-baris)
│ Di Tempat                           │
└─────────────────────────────────────┘
```

**AI Result:**
```json
{
  "sender_receiver": "Kepala Dinas Pendidikan Kota ABC",
  ...
}
```

---

## 🧪 API Usage

### **Endpoint**
```
POST /api/v1/letters/analyze
```

### **Request**
```
Content-Type: multipart/form-data

file: [FILE] (PDF, JPG, JPEG, PNG - max 10MB)
type: "incoming" atau "outgoing" (REQUIRED!)
```

### **Example - Incoming Letter**
```bash
curl -X POST http://localhost:8000/api/v1/letters/analyze \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@surat-masuk.pdf" \
  -F "type=incoming"
```

**Response:**
```json
{
  "success": true,
  "message": "Letter analysis completed",
  "data": {
    "sender_receiver": "PEMERINTAH KOTA ABC DINAS PENDIDIKAN",
    "date_of_letter": "2024-01-13",
    "letter_number": "123/ABC/2024",
    "year": "2024",
    "subject": "Undangan Rapat Koordinasi",
    "classification": "Biasa",
    "classification_id": 1,
    "description": "Surat undangan rapat koordinasi untuk membahas program kerja tahun 2024."
  }
}
```

### **Example - Outgoing Letter**
```bash
curl -X POST http://localhost:8000/api/v1/letters/analyze \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@surat-keluar.pdf" \
  -F "type=outgoing"
```

**Response:**
```json
{
  "success": true,
  "message": "Letter analysis completed",
  "data": {
    "sender_receiver": "Kepala Dinas Pendidikan Kota ABC",
    "date_of_letter": "2026-01-15",
    "letter_number": "456/XYZ/2026",
    "year": "2026",
    "subject": "Undangan Rapat",
    "classification": "Penting",
    "classification_id": 2,
    "description": "Undangan rapat untuk membahas koordinasi program pendidikan."
  }
}
```

---

## 📊 Comparison Table

| Tipe Surat | `sender_receiver` Diambil dari | Contoh |
|------------|-------------------------------|---------|
| **Incoming** | Kop Surat (bagian atas) | "PEMERINTAH KOTA ABC DINAS PENDIDIKAN" |
| **Outgoing** | Bagian "Yth." (setelah detail surat) | "Kepala Dinas Pendidikan Kota ABC" |

---

## ✅ Validation Rules

| Field | Type | Rules | Example |
|-------|------|-------|---------|
| `file` | File | required, mimes:pdf,jpg,jpeg,png, max:10240 | surat.pdf |
| `type` | String | required, in:incoming,outgoing | "incoming" |

---

## 🚨 Error Handling

### **Missing Type Parameter**
**Request:**
```bash
curl -F "file=@surat.pdf"  # Missing type!
```

**Response:**
```json
{
  "success": false,
  "message": "Validation Error",
  "data": {
    "type": ["The type field is required."]
  },
  "code": 422
}
```

### **Invalid Type Value**
**Request:**
```bash
curl -F "file=@surat.pdf" -F "type=unknown"
```

**Response:**
```json
{
  "success": false,
  "message": "Validation Error",
  "data": {
    "type": ["The selected type is invalid."]
  },
  "code": 422
}
```

---

## 🎯 Use Cases

### **Use Case 1: Analyze Incoming Letter**
Surat diterima dari instansi lain, perlu tahu pengirim dari kop surat:

```bash
POST /api/v1/letters/analyze
file: surat-masuk-dari-bappeda.pdf
type: incoming

→ AI akan extract PENGIRIM dari kop surat
```

### **Use Case 2: Analyze Outgoing Letter**
Surat yang dibuat sendiri, perlu tahu penerima dari bagian "Yth.":

```bash
POST /api/v1/letters/analyze
file: surat-keluar-ke-kepala-dinas.pdf
type: outgoing

→ AI akan extract PENERIMA dari bagian "Yth."
```

---

## 📝 Frontend Integration

### **React/Next.js Example**
```javascript
const analyzeIncomingLetter = async (file) => {
  const formData = new FormData();
  formData.append('file', file);
  formData.append('type', 'incoming');  // ← Specify type!
  
  const response = await fetch('/api/v1/letters/analyze', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`
    },
    body: formData
  });
  
  const result = await response.json();
  console.log('Pengirim:', result.data.sender_receiver);
};

const analyzeOutgoingLetter = async (file) => {
  const formData = new FormData();
  formData.append('file', file);
  formData.append('type', 'outgoing');  // ← Specify type!
  
  const response = await fetch('/api/v1/letters/analyze', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`
    },
    body: formData
  });
  
  const result = await response.json();
  console.log('Penerima:', result.data.sender_receiver);
};
```

---

## 🔍 Testing

### **Test 1: Incoming Letter**
```bash
curl -X POST http://localhost:8000/api/v1/letters/analyze \
  -H "Authorization: Bearer eyJ..." \
  -F "file=@tests/fixtures/surat-masuk-sample.pdf" \
  -F "type=incoming"
```

Expected: `sender_receiver` contains sender from letterhead

### **Test 2: Outgoing Letter**
```bash
curl -X POST http://localhost:8000/api/v1/letters/analyze \
  -H "Authorization: Bearer eyJ..." \
  -F "file=@tests/fixtures/surat-keluar-sample.pdf" \
  -F "type=outgoing"
```

Expected: `sender_receiver` contains recipient from "Yth." section

---

## 📚 Related Files

1. ✅ `app/Http/Controllers/API/V1/LetterController.php` - Added type validation
2. ✅ `src/Domain/Letter/Actions/AnalyzeLetterAction.php` - Accept letterType parameter
3. ✅ `src/Domain/Letter/Services/GeminiLetterAnalysisService.php` - Dynamic prompting logic

---

## ✨ Benefits

1. **Akurat**: AI tahu persis di mana mencari data berdasarkan tipe surat
2. **Fleksibel**: Mendukung 2 tipe surat dengan extraction logic berbeda
3. **User-Friendly**: User tinggal pilih type, AI akan adjust otomatis
4. **Maintainable**: Prompt logic terpusat di service layer

---

## 🎉 Summary

**Before:**
- AI selalu ambil `sender_receiver` dari kop surat
- Tidak cocok untuk surat keluar (kop = pembuat surat, bukan penerima)

**After:**
- **Surat Masuk** (`type=incoming`): AI ambil PENGIRIM dari kop surat ✅
- **Surat Keluar** (`type=outgoing`): AI ambil PENERIMA dari "Yth." ✅

AI sekarang lebih pintar dan sesuai dengan konteks surat! 🚀
