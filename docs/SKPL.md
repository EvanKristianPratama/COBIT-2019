# SKPL - Spesifikasi Kebutuhan Perangkat Lunak

## COBIT 2019 Design Toolkit

**Versi Dokumen:** 1.0  
**Tanggal:** Desember 2024

---

## 1. Pendahuluan

### 1.1 Tujuan Dokumen

Dokumen SKPL ini bertujuan untuk mendefinisikan kebutuhan perangkat lunak sistem COBIT 2019 Design Toolkit secara lengkap dan terstruktur. Dokumen ini menjadi acuan bagi tim pengembang, stakeholder, dan pengguna dalam memahami fungsi dan batasan sistem.

### 1.2 Lingkup Produk

COBIT 2019 Design Toolkit adalah aplikasi web yang membantu organisasi dalam:
- Melakukan assessment Design Factor berdasarkan COBIT 2019
- Mengevaluasi objectives dan practices COBIT
- Menghasilkan rekomendasi governance IT yang sesuai dengan kebutuhan organisasi

### 1.3 Definisi dan Singkatan

| Istilah | Definisi |
|---------|----------|
| COBIT | Control Objectives for Information and Related Technologies |
| DF | Design Factor |
| IT | Information Technology |
| PIC | Person In Charge |
| GAMO | Governance and Management Objectives |

### 1.4 Referensi

- COBIT 2019 Framework by ISACA
- Laravel Framework Documentation
- Bootstrap 5 Documentation

---

## 2. Deskripsi Umum

### 2.1 Perspektif Produk

Sistem COBIT 2019 Design Toolkit merupakan aplikasi standalone berbasis web yang dapat diakses melalui browser. Sistem ini tidak bergantung pada sistem lain namun dapat diintegrasikan dengan layanan autentikasi Google OAuth.

### 2.2 Fungsi Produk

Fungsi utama sistem meliputi:

1. **Manajemen Pengguna**
   - Registrasi pengguna baru
   - Autentikasi (login/logout)
   - Manajemen profil pengguna
   - Manajemen role (Admin, PIC, User, Guest)

2. **Design Factor Assessment**
   - Input penilaian 10 Design Factor
   - Perhitungan score dan relative importance
   - Visualisasi hasil assessment

3. **Assessment Management**
   - Pembuatan assessment baru
   - Join assessment dengan kode
   - Manajemen assessment (view, edit, delete)

4. **Assessment Evaluation**
   - Evaluasi aktivitas COBIT
   - Input notes dan evidence
   - Pelacakan progress evaluasi

5. **Admin Dashboard**
   - Manajemen user
   - Approval request
   - Monitoring assessment

### 2.3 Karakteristik Pengguna

| Tipe Pengguna | Deskripsi | Kemampuan Teknis |
|---------------|-----------|------------------|
| Admin | Administrator sistem | Menengah - Tinggi |
| PIC | Penanggungjawab assessment | Menengah |
| User | Pengguna umum/responden | Dasar - Menengah |
| Guest | Pengguna tanpa akun | Dasar |

### 2.4 Batasan Sistem

- Memerlukan koneksi internet untuk akses
- Browser yang didukung: Chrome, Firefox, Safari, Edge (versi terbaru)
- Memerlukan PHP >= 8.0 untuk deployment
- Database menggunakan MySQL

### 2.5 Asumsi dan Ketergantungan

- Pengguna memiliki pemahaman dasar tentang framework COBIT
- Server hosting memenuhi kebutuhan minimum Laravel
- Database MySQL tersedia dan dapat diakses

---

## 3. Kebutuhan Fungsional

### 3.1 Modul Autentikasi

#### FR-AUTH-001: Registrasi Pengguna
- **Deskripsi**: Sistem harus memungkinkan pengguna baru untuk mendaftar
- **Input**: Nama, email, password, organisasi, jabatan
- **Output**: Akun pengguna baru terbuat
- **Prioritas**: Tinggi

#### FR-AUTH-002: Login Pengguna
- **Deskripsi**: Sistem harus memungkinkan pengguna untuk login
- **Input**: Email, password
- **Output**: Session pengguna aktif, redirect ke home
- **Prioritas**: Tinggi

#### FR-AUTH-003: Login Google OAuth
- **Deskripsi**: Sistem harus memungkinkan login menggunakan akun Google
- **Input**: Kredensial Google
- **Output**: Session pengguna aktif
- **Prioritas**: Menengah

#### FR-AUTH-004: Logout
- **Deskripsi**: Sistem harus memungkinkan pengguna untuk logout
- **Input**: Request logout
- **Output**: Session dihapus, redirect ke login
- **Prioritas**: Tinggi

### 3.2 Modul Design Factor Assessment

#### FR-DF-001: Input Design Factor 1-10
- **Deskripsi**: Sistem harus memungkinkan input penilaian untuk setiap Design Factor (1-10)
- **Input**: Nilai assessment untuk setiap kriteria
- **Output**: Data tersimpan di database
- **Prioritas**: Tinggi

#### FR-DF-002: Hitung Score
- **Deskripsi**: Sistem harus menghitung score berdasarkan input Design Factor
- **Input**: Data Design Factor
- **Output**: Score dan relative importance
- **Prioritas**: Tinggi

#### FR-DF-003: Tampilkan Output
- **Deskripsi**: Sistem harus menampilkan hasil assessment dalam format yang mudah dipahami
- **Input**: Assessment ID
- **Output**: Halaman output dengan visualisasi
- **Prioritas**: Tinggi

### 3.3 Modul Assessment Management

#### FR-ASSESS-001: Buat Assessment Baru
- **Deskripsi**: Sistem harus memungkinkan pembuatan assessment baru
- **Input**: Kode assessment (auto/manual), instansi
- **Output**: Assessment baru dengan kode unik
- **Prioritas**: Tinggi

#### FR-ASSESS-002: Join Assessment
- **Deskripsi**: Sistem harus memungkinkan pengguna join assessment yang sudah ada
- **Input**: Kode assessment
- **Output**: Akses ke assessment
- **Prioritas**: Tinggi

#### FR-ASSESS-003: Lihat Daftar Assessment
- **Deskripsi**: Sistem harus menampilkan daftar assessment sesuai role pengguna
- **Input**: User ID, organisasi
- **Output**: Daftar assessment yang relevan
- **Prioritas**: Tinggi

### 3.4 Modul Assessment Evaluation

#### FR-EVAL-001: Buat Evaluasi Baru
- **Deskripsi**: Sistem harus memungkinkan pembuatan evaluasi assessment baru
- **Input**: User ID, selected GAMO (opsional)
- **Output**: Evaluasi baru terbuat
- **Prioritas**: Tinggi

#### FR-EVAL-002: Input Evaluasi Aktivitas
- **Deskripsi**: Sistem harus memungkinkan input evaluasi untuk setiap aktivitas
- **Input**: Level achieved (F/L/P), notes, evidence
- **Output**: Data evaluasi tersimpan
- **Prioritas**: Tinggi

#### FR-EVAL-003: Simpan Progress
- **Deskripsi**: Sistem harus menyimpan progress evaluasi secara otomatis
- **Input**: Data evaluasi
- **Output**: Data tersimpan di database
- **Prioritas**: Tinggi

#### FR-EVAL-004: Selesaikan Evaluasi
- **Deskripsi**: Sistem harus memungkinkan penyelesaian evaluasi (lock)
- **Input**: Eval ID
- **Output**: Status berubah menjadi 'finished'
- **Prioritas**: Menengah

#### FR-EVAL-005: Buka Kunci Evaluasi
- **Deskripsi**: Sistem harus memungkinkan pembukaan kunci evaluasi yang sudah selesai
- **Input**: Eval ID
- **Output**: Status berubah menjadi 'in_progress'
- **Prioritas**: Menengah

### 3.5 Modul Admin

#### FR-ADMIN-001: Lihat Dashboard
- **Deskripsi**: Admin dapat melihat dashboard dengan overview assessment
- **Input**: Admin session
- **Output**: Halaman dashboard dengan statistik
- **Prioritas**: Tinggi

#### FR-ADMIN-002: Manajemen User
- **Deskripsi**: Admin dapat mengelola pengguna (aktivasi/deaktivasi)
- **Input**: User ID, action
- **Output**: Status user diperbarui
- **Prioritas**: Tinggi

#### FR-ADMIN-003: Approve Request
- **Deskripsi**: Admin dapat menyetujui request join assessment
- **Input**: Request index
- **Output**: Status request berubah ke 'approved'
- **Prioritas**: Menengah

---

## 4. Kebutuhan Non-Fungsional

### 4.1 Keamanan

#### NFR-SEC-001: Enkripsi Password
- Password harus dienkripsi menggunakan bcrypt
- **Prioritas**: Tinggi

#### NFR-SEC-002: Proteksi CSRF
- Semua form harus memiliki proteksi CSRF token
- **Prioritas**: Tinggi

#### NFR-SEC-003: Session Security
- Session harus memiliki timeout dan secure flags
- **Prioritas**: Tinggi

### 4.2 Performa

#### NFR-PERF-001: Response Time
- Halaman harus dimuat dalam waktu < 3 detik dalam kondisi normal
- **Prioritas**: Menengah

#### NFR-PERF-002: Concurrent Users
- Sistem harus mendukung minimal 50 pengguna bersamaan
- **Prioritas**: Menengah

### 4.3 Usability

#### NFR-USE-001: Responsive Design
- Antarmuka harus responsif untuk desktop dan tablet
- **Prioritas**: Tinggi

#### NFR-USE-002: User Feedback
- Sistem harus memberikan feedback yang jelas untuk setiap aksi
- **Prioritas**: Menengah

### 4.4 Reliability

#### NFR-REL-001: Data Backup
- Data harus di-backup secara berkala
- **Prioritas**: Tinggi

#### NFR-REL-002: Error Handling
- Sistem harus menangani error dengan graceful dan informatif
- **Prioritas**: Tinggi

### 4.5 Maintainability

#### NFR-MAIN-001: Logging
- Sistem harus mencatat log untuk debugging dan audit
- **Prioritas**: Menengah

#### NFR-MAIN-002: Documentation
- Code harus didokumentasikan dengan baik
- **Prioritas**: Menengah

---

## 5. Data Requirements

### 5.1 Entity Relationship

Entitas utama dalam sistem:

1. **User**
   - id, name, email, password, organisasi, jabatan, role, isActivated

2. **Assessment**
   - assessment_id, kode_assessment, instansi, user_id

3. **Design Factor (1-10)**
   - id, assessment_id, [kriteria spesifik]

4. **Design Factor Score (1-10)**
   - id, assessment_id, score, user_id

5. **Design Factor Relative Importance (1-10)**
   - id, assessment_id, importance_value

6. **MstObjective**
   - objective_id, objective_name, description

7. **MstPractice**
   - practice_id, objective_id, practice_name

8. **MstActivities**
   - activity_id, practice_id, activity_name

9. **MstEval**
   - eval_id, user_id, status, created_at

10. **TrsActivityeval**
    - id, eval_id, activity_id, level_achieved, notes, evidence

---

## 6. Interface Requirements

### 6.1 User Interface

- Framework: Bootstrap 5
- Style: Clean, professional, responsive
- Color scheme: Blue primary (#0d6efd)

### 6.2 API Interface

Tidak ada external API yang diexpose. Semua komunikasi melalui web routes.

### 6.3 Hardware Interface

Tidak ada kebutuhan hardware khusus.

### 6.4 Software Interface

- Web Server: Apache/Nginx
- PHP: >= 8.0
- MySQL: >= 5.7
- Composer: Package manager

---

## 7. Appendix

### 7.1 Glossary

| Term | Definition |
|------|------------|
| Assessment | Proses penilaian governance IT menggunakan framework COBIT |
| Design Factor | Faktor yang mempengaruhi desain governance system |
| Governance | Tata kelola yang memastikan IT mendukung tujuan bisnis |
| GAMO | Governance and Management Objectives dalam COBIT |

### 7.2 Change Log

| Versi | Tanggal | Perubahan | Author |
|-------|---------|-----------|--------|
| 1.0 | Des 2024 | Dokumen awal | Development Team |
