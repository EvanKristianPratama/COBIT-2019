# Use Cases - COBIT 2019 Design Toolkit

**Versi Dokumen:** 1.0  
**Tanggal:** Desember 2024

---

## 1. Deskripsi Aktor

### 1.1 Daftar Aktor

| Aktor | Deskripsi |
|-------|-----------|
| **Guest** | Pengguna yang belum terdaftar atau login sebagai guest. Memiliki akses terbatas. |
| **User** | Pengguna terdaftar yang dapat melakukan assessment dan evaluasi. |
| **PIC** | Person In Charge yang memiliki akses lebih luas untuk melihat assessment organisasi. |
| **Admin** | Administrator sistem dengan akses penuh untuk manajemen user dan assessment. |
| **System** | Sistem COBIT 2019 Design Toolkit yang memproses request dan menyimpan data. |
| **Google OAuth** | Layanan autentikasi eksternal Google. |

---

## 2. Use Case Diagram

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                     COBIT 2019 Design Toolkit System                        │
│                                                                             │
│  ┌──────────────────────────────────────────────────────────────────────┐  │
│  │                        Authentication Module                          │  │
│  │                                                                       │  │
│  │    ┌─────────────┐  ┌─────────────┐  ┌─────────────┐                 │  │
│  │    │  Register   │  │    Login    │  │   Logout    │                 │  │
│  │    └─────────────┘  └──────┬──────┘  └─────────────┘                 │  │
│  │                            │                                          │  │
│  │                     ┌──────┴──────┐                                   │  │
│  │                     │Login Google │                                   │  │
│  │                     └─────────────┘                                   │  │
│  └──────────────────────────────────────────────────────────────────────┘  │
│                                                                             │
│  ┌──────────────────────────────────────────────────────────────────────┐  │
│  │                    Design Factor Assessment Module                    │  │
│  │                                                                       │  │
│  │    ┌─────────────┐  ┌─────────────┐  ┌─────────────┐                 │  │
│  │    │ Input DF1-10│  │Calculate    │  │ View Output │                 │  │
│  │    │             │──│   Score     │──│             │                 │  │
│  │    └─────────────┘  └─────────────┘  └─────────────┘                 │  │
│  └──────────────────────────────────────────────────────────────────────┘  │
│                                                                             │
│  ┌──────────────────────────────────────────────────────────────────────┐  │
│  │                    Assessment Management Module                       │  │
│  │                                                                       │  │
│  │    ┌─────────────┐  ┌─────────────┐  ┌─────────────┐                 │  │
│  │    │   Create    │  │    Join     │  │    View     │                 │  │
│  │    │ Assessment  │  │ Assessment  │  │ Assessment  │                 │  │
│  │    └─────────────┘  └─────────────┘  └─────────────┘                 │  │
│  └──────────────────────────────────────────────────────────────────────┘  │
│                                                                             │
│  ┌──────────────────────────────────────────────────────────────────────┐  │
│  │                    Assessment Evaluation Module                       │  │
│  │                                                                       │  │
│  │    ┌─────────────┐  ┌─────────────┐  ┌─────────────┐                 │  │
│  │    │   Create    │  │   Input     │  │   Finish    │                 │  │
│  │    │ Evaluation  │──│ Activity    │──│ Evaluation  │                 │  │
│  │    └─────────────┘  │ Evaluation  │  └─────────────┘                 │  │
│  │                     └─────────────┘                                   │  │
│  └──────────────────────────────────────────────────────────────────────┘  │
│                                                                             │
│  ┌──────────────────────────────────────────────────────────────────────┐  │
│  │                         Admin Module                                  │  │
│  │                                                                       │  │
│  │    ┌─────────────┐  ┌─────────────┐  ┌─────────────┐                 │  │
│  │    │    View     │  │   Manage    │  │   Approve   │                 │  │
│  │    │  Dashboard  │  │    Users    │  │  Requests   │                 │  │
│  │    └─────────────┘  └─────────────┘  └─────────────┘                 │  │
│  └──────────────────────────────────────────────────────────────────────┘  │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘

      ┌─────┐     ┌─────┐     ┌─────┐     ┌─────┐
      │Guest│     │User │     │ PIC │     │Admin│
      └──┬──┘     └──┬──┘     └──┬──┘     └──┬──┘
         │           │           │           │
    Register    All Modules  All Modules  All Modules
    Login      (restricted)  (extended)   (full access)
    Login Google
```

---

## 3. Use Case Specifications

### 3.1 Authentication Module

---

#### UC-AUTH-001: Register (Registrasi Pengguna)

| Attribute | Description |
|-----------|-------------|
| **Use Case ID** | UC-AUTH-001 |
| **Use Case Name** | Register |
| **Actor** | Guest |
| **Description** | Pengguna baru mendaftarkan akun |
| **Pre-condition** | Pengguna belum memiliki akun |
| **Post-condition** | Akun pengguna baru terbuat dan tersimpan di database |

**Main Flow:**
1. Guest mengakses halaman registrasi (/register)
2. Sistem menampilkan form registrasi
3. Guest mengisi data: nama, email, password, organisasi, jabatan
4. Guest menekan tombol "Register"
5. Sistem memvalidasi data
6. Sistem membuat akun baru
7. Sistem redirect ke halaman login dengan pesan sukses

**Alternative Flow:**
- **5a. Validasi gagal:**
  - Sistem menampilkan pesan error
  - Kembali ke langkah 3

- **6a. Email sudah terdaftar:**
  - Sistem menampilkan pesan error "Email sudah terdaftar"
  - Kembali ke langkah 3

---

#### UC-AUTH-002: Login

| Attribute | Description |
|-----------|-------------|
| **Use Case ID** | UC-AUTH-002 |
| **Use Case Name** | Login |
| **Actor** | Guest |
| **Description** | Pengguna masuk ke sistem |
| **Pre-condition** | Pengguna memiliki akun yang aktif |
| **Post-condition** | Session pengguna aktif, pengguna ter-redirect ke home |

**Main Flow:**
1. Guest mengakses halaman login (/login)
2. Sistem menampilkan form login
3. Guest memasukkan email dan password
4. Guest menekan tombol "Login"
5. Sistem memvalidasi kredensial
6. Sistem membuat session
7. Sistem redirect ke halaman home

**Alternative Flow:**
- **5a. Kredensial tidak valid:**
  - Sistem menampilkan pesan error
  - Kembali ke langkah 3

- **5b. Akun dinonaktifkan:**
  - Sistem menampilkan pesan "Akun Anda telah dinonaktifkan"
  - Logout dan kembali ke halaman login

---

#### UC-AUTH-003: Login dengan Google

| Attribute | Description |
|-----------|-------------|
| **Use Case ID** | UC-AUTH-003 |
| **Use Case Name** | Login Google |
| **Actor** | Guest, Google OAuth |
| **Description** | Pengguna masuk menggunakan akun Google |
| **Pre-condition** | Pengguna memiliki akun Google |
| **Post-condition** | Session pengguna aktif |

**Main Flow:**
1. Guest mengklik tombol "Login with Google"
2. Sistem redirect ke halaman autentikasi Google
3. Google menampilkan form consent
4. Guest menyetujui dan memasukkan kredensial Google
5. Google mengirim callback ke sistem
6. Sistem memeriksa apakah email sudah terdaftar
7. Jika sudah terdaftar, sistem login pengguna
8. Sistem redirect ke home

**Alternative Flow:**
- **6a. Email belum terdaftar:**
  - Sistem menampilkan form registrasi dengan data dari Google
  - Pengguna melengkapi data dan submit
  - Sistem membuat akun dan login

- **7a. Akun dinonaktifkan:**
  - Sistem menampilkan pesan error
  - Redirect ke halaman login

---

#### UC-AUTH-004: Logout

| Attribute | Description |
|-----------|-------------|
| **Use Case ID** | UC-AUTH-004 |
| **Use Case Name** | Logout |
| **Actor** | User, PIC, Admin |
| **Description** | Pengguna keluar dari sistem |
| **Pre-condition** | Pengguna sudah login |
| **Post-condition** | Session dihapus, pengguna ter-redirect ke login |

**Main Flow:**
1. User mengklik tombol "Logout"
2. Sistem menghapus session
3. Sistem redirect ke halaman login

---

### 3.2 Design Factor Assessment Module

---

#### UC-DF-001: Input Design Factor

| Attribute | Description |
|-----------|-------------|
| **Use Case ID** | UC-DF-001 |
| **Use Case Name** | Input Design Factor |
| **Actor** | User, PIC, Admin |
| **Description** | Pengguna mengisi penilaian Design Factor (1-10) |
| **Pre-condition** | Pengguna sudah login dan memiliki assessment aktif |
| **Post-condition** | Data Design Factor tersimpan |

**Main Flow:**
1. User mengakses form Design Factor (df1/form/{id} s/d df10/form/{id})
2. Sistem menampilkan form dengan kriteria yang harus dinilai
3. User mengisi nilai untuk setiap kriteria
4. User menekan tombol "Submit"
5. Sistem menyimpan data
6. Sistem redirect ke halaman output

**Alternative Flow:**
- **1a. User tidak memiliki akses (middleware jabatan.df):**
  - Sistem menampilkan pesan error akses ditolak

- **4a. Validasi gagal:**
  - Sistem menampilkan pesan error
  - Kembali ke langkah 3

---

#### UC-DF-002: View Design Factor Output

| Attribute | Description |
|-----------|-------------|
| **Use Case ID** | UC-DF-002 |
| **Use Case Name** | View Output |
| **Actor** | User, PIC, Admin |
| **Description** | Pengguna melihat hasil penilaian Design Factor |
| **Pre-condition** | Design Factor sudah diisi |
| **Post-condition** | Halaman output ditampilkan |

**Main Flow:**
1. User mengakses halaman output (df1/output/{id} s/d df10/output/{id})
2. Sistem mengambil data dari database
3. Sistem menghitung score dan relative importance
4. Sistem menampilkan hasil dalam format visual

---

### 3.3 Assessment Management Module

---

#### UC-ASSESS-001: Create Assessment

| Attribute | Description |
|-----------|-------------|
| **Use Case ID** | UC-ASSESS-001 |
| **Use Case Name** | Create Assessment |
| **Actor** | User, PIC, Admin |
| **Description** | Pengguna membuat assessment baru |
| **Pre-condition** | Pengguna sudah login |
| **Post-condition** | Assessment baru terbuat dengan kode unik |

**Main Flow:**
1. User mengakses halaman COBIT home
2. User menekan tombol "Buat Design Factor"
3. Sistem membuat assessment dengan kode otomatis (AUTO-XXXXXX)
4. Sistem menyimpan assessment ke database
5. Sistem set session dengan assessment_id baru
6. Sistem redirect ke form Design Factor 1

**Alternative Flow:**
- **Admin Flow:**
  - Admin dapat membuat assessment dengan kode manual
  - Admin mengisi kode_assessment dan instansi
  - Sistem memvalidasi kode unik
  - Sistem menyimpan assessment

---

#### UC-ASSESS-002: Join Assessment

| Attribute | Description |
|-----------|-------------|
| **Use Case ID** | UC-ASSESS-002 |
| **Use Case Name** | Join Assessment |
| **Actor** | User, PIC, Admin |
| **Description** | Pengguna bergabung ke assessment yang sudah ada |
| **Pre-condition** | Pengguna sudah login, assessment dengan kode tersebut ada |
| **Post-condition** | Session pengguna terhubung ke assessment |

**Main Flow:**
1. User mengakses halaman COBIT home
2. User menekan tombol "Join Design Factor"
3. Sistem menampilkan form input kode
4. User memasukkan kode assessment
5. User menekan tombol "Join"
6. Sistem memvalidasi kode
7. Sistem set session dengan assessment yang ditemukan
8. Sistem redirect ke form Design Factor 1

**Alternative Flow:**
- **6a. Kode tidak valid:**
  - Sistem menampilkan pesan error
  - Kembali ke langkah 4

---

#### UC-ASSESS-003: View Assessment List

| Attribute | Description |
|-----------|-------------|
| **Use Case ID** | UC-ASSESS-003 |
| **Use Case Name** | View Assessment List |
| **Actor** | User, PIC, Admin |
| **Description** | Pengguna melihat daftar assessment |
| **Pre-condition** | Pengguna sudah login |
| **Post-condition** | Daftar assessment ditampilkan |

**Main Flow:**
1. User mengakses halaman COBIT home
2. Sistem mengambil assessment sesuai role:
   - **User**: Assessment milik sendiri atau sesuai organisasi
   - **PIC**: Semua assessment
   - **Admin**: Assessment dibagi per organisasi dan lainnya
3. Sistem menampilkan daftar assessment

---

### 3.4 Assessment Evaluation Module

---

#### UC-EVAL-001: Create Evaluation

| Attribute | Description |
|-----------|-------------|
| **Use Case ID** | UC-EVAL-001 |
| **Use Case Name** | Create Evaluation |
| **Actor** | User, PIC, Admin |
| **Description** | Pengguna membuat evaluasi assessment baru |
| **Pre-condition** | Pengguna sudah login |
| **Post-condition** | Evaluasi baru terbuat |

**Main Flow:**
1. User mengakses halaman assessment evaluation list
2. User menekan tombol "Create New Evaluation"
3. User memilih GAMO/domain yang akan dievaluasi (opsional)
4. User menekan tombol "Create"
5. Sistem membuat evaluasi baru
6. Sistem redirect ke halaman evaluasi

---

#### UC-EVAL-002: Input Activity Evaluation

| Attribute | Description |
|-----------|-------------|
| **Use Case ID** | UC-EVAL-002 |
| **Use Case Name** | Input Activity Evaluation |
| **Actor** | User, PIC, Admin |
| **Description** | Pengguna menginput evaluasi untuk setiap aktivitas |
| **Pre-condition** | Evaluasi sudah dibuat, status 'in_progress' |
| **Post-condition** | Data evaluasi tersimpan |

**Main Flow:**
1. User mengakses halaman evaluasi (assessment-eval/{evalId})
2. Sistem menampilkan daftar objectives, practices, dan activities
3. User memilih level achieved (F/L/P) untuk setiap aktivitas
4. User mengisi notes dan evidence (opsional)
5. Sistem auto-save secara periodik atau user menekan "Save"
6. Sistem menyimpan data evaluasi

---

#### UC-EVAL-003: Finish Evaluation

| Attribute | Description |
|-----------|-------------|
| **Use Case ID** | UC-EVAL-003 |
| **Use Case Name** | Finish Evaluation |
| **Actor** | User (owner) |
| **Description** | Pengguna menyelesaikan dan mengunci evaluasi |
| **Pre-condition** | Evaluasi sudah dibuat, user adalah owner |
| **Post-condition** | Status evaluasi berubah ke 'finished' |

**Main Flow:**
1. User berada di halaman evaluasi
2. User menekan tombol "Finish"
3. Sistem mengubah status evaluasi menjadi 'finished'
4. Evaluasi terkunci dan tidak bisa diubah

---

#### UC-EVAL-004: Unlock Evaluation

| Attribute | Description |
|-----------|-------------|
| **Use Case ID** | UC-EVAL-004 |
| **Use Case Name** | Unlock Evaluation |
| **Actor** | User (owner) |
| **Description** | Pengguna membuka kunci evaluasi yang sudah selesai |
| **Pre-condition** | Evaluasi status 'finished', user adalah owner |
| **Post-condition** | Status evaluasi berubah ke 'in_progress' |

**Main Flow:**
1. User berada di halaman evaluasi yang terkunci
2. User menekan tombol "Unlock"
3. Sistem mengubah status menjadi 'in_progress'
4. Evaluasi dapat diubah kembali

---

### 3.5 Admin Module

---

#### UC-ADMIN-001: View Dashboard

| Attribute | Description |
|-----------|-------------|
| **Use Case ID** | UC-ADMIN-001 |
| **Use Case Name** | View Dashboard |
| **Actor** | Admin, PIC |
| **Description** | Admin melihat dashboard overview |
| **Pre-condition** | Pengguna login sebagai Admin/PIC |
| **Post-condition** | Dashboard ditampilkan |

**Main Flow:**
1. Admin mengakses halaman admin dashboard (/admin/dashboard)
2. Sistem memeriksa role pengguna
3. Sistem mengambil data assessment
4. Sistem menampilkan dashboard dengan daftar assessment dan filter

---

#### UC-ADMIN-002: Manage Users

| Attribute | Description |
|-----------|-------------|
| **Use Case ID** | UC-ADMIN-002 |
| **Use Case Name** | Manage Users |
| **Actor** | Admin |
| **Description** | Admin mengelola pengguna (aktivasi/deaktivasi) |
| **Pre-condition** | Pengguna login sebagai Admin |
| **Post-condition** | Status user diperbarui |

**Main Flow:**
1. Admin mengakses halaman users (/admin/users)
2. Sistem menampilkan daftar pengguna
3. Admin memilih user yang akan dikelola
4. Admin memilih aksi (aktivasi/deaktivasi)
5. Sistem memperbarui status user
6. Sistem menampilkan pesan konfirmasi

---

#### UC-ADMIN-003: Approve Request

| Attribute | Description |
|-----------|-------------|
| **Use Case ID** | UC-ADMIN-003 |
| **Use Case Name** | Approve Request |
| **Actor** | Admin, PIC |
| **Description** | Admin menyetujui request join assessment |
| **Pre-condition** | Ada request dengan status 'pending' |
| **Post-condition** | Status request berubah ke 'approved' |

**Main Flow:**
1. Admin mengakses halaman requests (/admin/requests)
2. Sistem menampilkan daftar request pending
3. Admin memilih request yang akan di-approve
4. Admin menekan tombol "Approve"
5. Sistem memperbarui status request
6. Sistem menampilkan pesan konfirmasi

---

## 4. Use Case Matrix (Actor vs Use Case)

| Use Case | Guest | User | PIC | Admin |
|----------|-------|------|-----|-------|
| UC-AUTH-001: Register | ✓ | - | - | - |
| UC-AUTH-002: Login | ✓ | - | - | - |
| UC-AUTH-003: Login Google | ✓ | - | - | - |
| UC-AUTH-004: Logout | - | ✓ | ✓ | ✓ |
| UC-DF-001: Input Design Factor | - | ✓ | ✓ | ✓ |
| UC-DF-002: View Output | - | ✓ | ✓ | ✓ |
| UC-ASSESS-001: Create Assessment | - | ✓ | ✓ | ✓ |
| UC-ASSESS-002: Join Assessment | - | ✓ | ✓ | ✓ |
| UC-ASSESS-003: View Assessment List | - | ✓* | ✓** | ✓** |
| UC-EVAL-001: Create Evaluation | - | ✓ | ✓ | ✓ |
| UC-EVAL-002: Input Activity Evaluation | - | ✓ | ✓ | ✓ |
| UC-EVAL-003: Finish Evaluation | - | ✓ | ✓ | ✓ |
| UC-EVAL-004: Unlock Evaluation | - | ✓ | ✓ | ✓ |
| UC-ADMIN-001: View Dashboard | - | - | ✓ | ✓ |
| UC-ADMIN-002: Manage Users | - | - | - | ✓ |
| UC-ADMIN-003: Approve Request | - | - | ✓ | ✓ |

**Keterangan:**
- ✓* = Akses terbatas sesuai organisasi
- ✓** = Akses penuh atau extended

---

## 5. Appendix: Use Case Traceability

| Use Case ID | Functional Requirement |
|-------------|------------------------|
| UC-AUTH-001 | FR-AUTH-001 |
| UC-AUTH-002 | FR-AUTH-002 |
| UC-AUTH-003 | FR-AUTH-003 |
| UC-AUTH-004 | FR-AUTH-004 |
| UC-DF-001 | FR-DF-001, FR-DF-002 |
| UC-DF-002 | FR-DF-003 |
| UC-ASSESS-001 | FR-ASSESS-001 |
| UC-ASSESS-002 | FR-ASSESS-002 |
| UC-ASSESS-003 | FR-ASSESS-003 |
| UC-EVAL-001 | FR-EVAL-001 |
| UC-EVAL-002 | FR-EVAL-002, FR-EVAL-003 |
| UC-EVAL-003 | FR-EVAL-004 |
| UC-EVAL-004 | FR-EVAL-005 |
| UC-ADMIN-001 | FR-ADMIN-001 |
| UC-ADMIN-002 | FR-ADMIN-002 |
| UC-ADMIN-003 | FR-ADMIN-003 |
