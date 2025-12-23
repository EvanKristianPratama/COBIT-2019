# SOP Alur Kerja Git & Kolaborasi Development (CBIOO Project)
**Team:** 2 Full Stack Developers (Evan & Jetro)  
**Stack:** Laravel (Blade/Controller/Model)

---

## 1. Aturan Dasar (Golden Rules)
1.  **JANGAN PERNAH** push langsung ke branch `main`.
2.  Branch `main` harus selalu dalam kondisi **siap deploy (Production Ready)**.
3.  Setiap pekerjaan baru = **Branch Baru**.
4.  Sebelum merge ke `main`, wajib melakukan **Pull Request (PR)** dan direview oleh partner (Evan review kode Jetro, Jetro review kode Evan).

---

## 2. Struktur Modul & Pembagian Kerja
Untuk menghindari *Merge Conflict* (tabrakan kode), usahakan membagi tugas berdasarkan modul ini:

| Modul | Deskripsi | Risiko Konflik |
| :--- | :--- | :--- |
| **A. COBIT Components** | Kamus Data, GAMO, Penjelasan COBIT. | **Rendah**. Biasanya hanya Model dan Seeder. |
| **B. Design Toolkit** | 10 Design Factors, Step 2, 3, 4. | **Tinggi**. Banyak logika di Controller. Komunikasikan jika menyentuh `DfController`. |
| **C. Target Capability/Maturity** | Input & Kalkulasi Target. | **Sedang**. Hati-hati di file View (`blade`). |
| **D. I&T Maturity Assessment** | Form Assessment & Reporting. | **Tinggi**. Sering update `EvidenceController` & `ReportController`. |

> **Tips:** Jika Anda sedang mengerjakan **Modul B (Design Toolkit)**, minta Jetro mengerjakan **Modul D (Assessment)** agar tidak mengedit file yang sama.

---

## 3. Workflow Langkah-demi-Langkah

### Langkah 1: Persiapan (Sebelum Coding)
Pastikan Anda berada di branch `main` terbaru sebelum mulai.
```bash
git checkout main
git pull origin main
```

### Langkah 2: Membuat Branch (Mulai Kerja)
Beri nama branch seseifik mungkin dengan format: `tipe/nama-modul-fitur`.
*   `feat/` = Fitur baru
*   `fix/` = Perbaikan bug
*   `refactor/` = Merapikan kode

**Contoh Kasus:** Anda ingin mengerjakan Design Factor 2 pada Design Toolkit.
```bash
git checkout -b feat/design-toolkit-df2
```

### Langkah 3: Coding & Commit
Lakukan pekerjaan Anda (Backend & Frontend). Commit secara berkala dengan pesan jelas.
```bash
git add .
git commit -m "feat: setup controller and view for DF2"
git commit -m "fix: calculation formula for risk factor"
```

### Langkah 4: Sinkronisasi (Cegah Konflik)
Sebelum push, cek apakah Jetro sudah update `main` duluan?
```bash
# Ambil info terbaru dari server (tanpa merge dulu)
git fetch origin

# Tarik perubahan 'main' terbaru ke branch Anda
git merge origin/main
```
*Jika ada konflik, selesaikan di VS Code, lalu commit lagi.*

### Langkah 5: Push ke GitHub
Upload branch fitur Anda ke server.
```bash
git push -u origin feat/design-toolkit-df2
```

### Langkah 6: Pull Request (PR) & Code Review
1.  Buka GitHub repo.
2.  Klik **"Compare & pull request"**.
3.  Pilih base: `main` <- compare: `feat/design-toolkit-df2`.
4.  **Tugas Jetro:** Review kode Anda. Cek logika Controller, cek tampilan Blade.
    *   Jika oke ➝ Approve & Merge.
    *   Jika bug ➝ Request Changes.

---

## 4. Penanganan Konflik (Panic Button)
Konflik terjadi jika Anda dan Jetro mengedit **baris yang sama** di file yang sama (sering terjadi di `routes/web.php`).

**Cara Mengatasi:**
1.  Saat `git merge origin/main`, terminal akan bilang "CONFLICT".
2.  Buka file yang merah di VS Code.
3.  Anda akan melihat:
    ```php
    <<<<<<< HEAD
    // Kodingan Anda
    Route::get('/df2', ...);
    =======
    // Kodingan Jetro (yang baru masuk main)
    Route::get('/assessment-report', ...);
    >>>>>>> origin/main
    ```
4.  Pilih "Accept Both Changes" (jika dua-duanya butuh) atau pilih salah satu.
5.  Simpan file.
6.  Jalankan:
    ```bash
    git add .
    git commit -m "fix: resolve merge conflict in routes"
    ```

---

## 5. Cara Membuat GitHub Issues (Task Tracking)

**GitHub Issues** adalah fitur untuk mencatat:
- 🐛 **Bug** yang perlu diperbaiki
- ✨ **Fitur baru** yang akan dikerjakan
- 📝 **To-do list** untuk tim

### Langkah Membuat Issue:

#### 1. Buka Tab Issues di GitHub
1. Buka repository di browser: `https://github.com/username/cbioo`
2. Klik tab **"Issues"** (di samping Code)
3. Klik tombol hijau **"New issue"**

#### 2. Isi Form Issue
**Title (Judul):**
- Harus jelas dan spesifik
- Contoh bagus: ✅ `[Bug] Spiderweb chart tidak muncul di report-all`
- Contoh buruk: ❌ `Chart error`

**Description (Deskripsi):**
Gunakan template ini:
```markdown
## Deskripsi
[Jelaskan masalah atau fitur yang diminta]

## Langkah Reproduksi (untuk bug)
1. Buka halaman `/assessment-eval/report-all`
2. Pilih 3 scope
3. Chart tidak muncul

## Expected Behavior (Harapan)
Chart seharusnya muncul dengan 3 layer berbeda

## Screenshot
[Lampirkan screenshot jika ada]

## File Terkait
- `resources/views/assessment-eval/report-spiderweb.blade.php`
- `app/Http/Controllers/AssessmentEval/AssessmentReportController.php`
```

#### 3. Assign & Label
- **Assignees:** Pilih siapa yang akan mengerjakan (Evan atau Jetro)
- **Labels:** Pilih kategori:
  - `bug` = Ada error
  - `enhancement` = Fitur baru
  - `documentation` = Dokumentasi
  - `help wanted` = Butuh bantuan

#### 4. Link ke Project/Milestone (Opsional)
Jika ada, hubungkan ke milestone seperti "Sprint 1" atau "Release v1.0"

### Cara Menutup Issue
Setelah selesai dikerjakan:
1. **Via Commit Message:**
   ```bash
   git commit -m "fix: resolve spiderweb chart rendering issue

   Closes #12"
   ```
   Issue #12 akan otomatis tertutup saat PR di-merge.

2. **Manual di GitHub:**
   - Buka issue yang sudah selesai
   - Klik "Close issue"

### Template Issue untuk Tim CBIOO

**Contoh Issue 1: Bug**
```
Title: [Bug] Target Maturity tidak muncul di tabel list

## Deskripsi
Kolom "I&T Target Maturity" di halaman list assessment menampilkan "-" 
padahal data sudah ada di database.

## File Terkait
- AssessmentListController.php (line 89-93)
- list.blade.php (line 119)

## Assigned to: @jetro
## Labels: bug, priority-high
```

**Contoh Issue 2: Feature**
```
Title: [Feature] Tambah export PDF untuk spiderweb chart

## Deskripsi
User ingin bisa download spiderweb chart sebagai PDF untuk laporan.

## Acceptance Criteria
- [ ] Tombol "Export PDF" di halaman spiderweb
- [ ] PDF berisi chart + tabel scope yang dipilih
- [ ] Filename: "Spiderweb_Report_YYYY-MM-DD.pdf"

## Assigned to: @evan
## Labels: enhancement
```

---

## 6. Cheat Sheet Command

| Aksi | Command |
| :--- | :--- |
| **Pindah ke main** | `git checkout main` |
| **Update main** | `git pull origin main` |
| **Bikin branch** | `git checkout -b nama-branch` |
| **Cek status** | `git status` |
| **Simpan perubahan** | `git add .` lalu `git commit -m "pesan"` |
| **Upload** | `git push origin nama-branch` |
| **Hapus branch (setelah merge)** | `git branch -d nama-branch` |

---
*Dibuat otomatis oleh AI Assistant untuk Tim CBIOO.*
