# COBIT 2019 Focus Area Implementation Memory

## Tanggal Implementasi
17 Juni 2026

## Ringkasan Fitur
Menambahkan fitur **Focus Area** pada aplikasi COBIT 2019. Fitur ini memungkinkan pengguna untuk mengelompokkan `mst_objective` berdasarkan area fokus tertentu seperti Security, Digital Transformation, Regulatory Compliance, Value Delivery, dan Risk Management. Relasi yang digunakan adalah *Many-to-Many* karena satu objective bisa memiliki banyak focus area, dan satu focus area bisa memiliki banyak objectives.

## Arsitektur dan Naming Convention
- Mengikuti pola yang sudah ada pada *codebase*, yaitu menggunakan prefix `mst_` untuk tabel master dan `trs_` untuk tabel pivot/transaksi.
- Penempatan data *focus area* dihubungkan langsung ke `mst_objective` sebagai *base model* untuk semua komponen COBIT.

## Perubahan yang Dilakukan

### 1. Database (Manual SQL)
- Membuat tabel master `mst_focusarea`.
- Membuat tabel pivot `trs_objectivefocus`.
- *Script* SQL disimpan di `database/sql/focus_area_setup.sql`.
- Menambahkan 5 *seed data* awal: SECURITY, DIGITAL, COMPLIANCE, VALUE, RISK.

### 2. Model
- Membuat model baru `app/Models/MstFocusArea.php`.
- Menambahkan relasi `focusAreas()` (`belongsToMany`) pada model `app/Models/MstObjective.php`.

### 3. Controller
- Membuat `app/Http/Controllers/cobit2019/FocusAreaController.php` untuk menangani operasi CRUD, integrasi ke *view*, *sync* objectives, dan juga menyediakan *endpoint* API publik.
- Mengubah `app/Http/Controllers/cobit2019/MstObjectiveController.php` dengan menambahkan `'focusAreas'` ke dalam *array* `$commonRelations` agar relasi ini selalu dimuat (*eager loaded*).

### 4. Routes (`routes/web.php`)
- Menambahkan *route* untuk halaman *view* (`/focus-areas`, `/focus-areas/{id}`).
- Menambahkan *route* CRUD dan *sync* objectives.
- Menambahkan *route* API publik (`/api/cobit/focus-areas`, `/api/cobit/focus-areas/{id}`).

### 5. Views
- Membuat `resources/views/focus_area/index.blade.php` untuk menampilkan daftar *focus areas* dalam bentuk *card grid* beserta modal CRUD.
- Membuat `resources/views/focus_area/show.blade.php` untuk menampilkan detail dari suatu *focus area*. Halaman ini memuat daftar *objectives* yang terhubung dalam bentuk akordeon (mirip dengan halaman *Kamus Component* utama), lengkap dengan tab untuk memisahkan *Practices*, *Policies*, *Skills*, *Culture*, dan *Services*. Juga terdapat fitur untuk melakukan *assign objectives*.
- Memperbarui `resources/views/cobit_component/show.blade.php` dengan menambahkan tombol "Focus Areas" di bagian *header* sebagai navigasi.

### 6. Dokumentasi
- Memperbarui file `docs/API.md` dengan dokumentasi struktur dan penggunaan *endpoint* API *Focus Areas*. Juga memperbaiki penomoran bab dalam dokumen tersebut dan menambahkan referensi *file*.
