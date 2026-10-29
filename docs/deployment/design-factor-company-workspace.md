# Design Factor per perusahaan dan Roadmap Core Model

Perubahan ini memakai PHP dan Blade. Tidak ada entry Vite baru untuk fitur ini.

## Perilaku

- Landing `/design_factor/cobit_home` menampilkan empat langkah desain.
- `/design_factor/list` hanya mengambil Design Factor perusahaan yang sedang dipilih pada akun, termasuk saat akun memiliki beberapa perusahaan.
- Tabel berurutan Perusahaan, Kode Design Factor, Progress, Last Update, Created At, Aksi. Progress memiliki subkolom DF1–DF4, Step 2, DF5–DF10, Step 3, dan Step 4.
- Progress DF dihitung dari jawaban pada kiriman terakhir yang tersimpan untuk kode tersebut (bukan jumlah baris kiriman). Jumlah pertanyaan DF1–DF10: 4, 13, 19, 20, 2, 3, 4, 3, 3, 3. Nol pada isian persentase adalah jawaban yang sah. DF3 memakai 19 nilai risk rating hasil impact × likelihood.
- Status Step 2–4 membaca hasil terakhir per responden pada kode perusahaan; 40 GAMO lengkap ditandai Tersimpan. Hasil parsial antarresponden tidak dijumlah menjadi status lengkap. Last Update mengikuti perubahan kode, input, dan hasil step terakhir.
- Aksi Hapus tersedia bagi pengguna dengan akses input pada perusahaan dipilih. Konfirmasi menampilkan kode dan perusahaan, lalu mewajibkan mengetik `saya yakin` (juga divalidasi di server). Penghapusan kode, seluruh input/score/relative importance DF1–DF10, bobot, hasil Step 2–4, dan assignment kode dijalankan dalam satu transaksi.
- Hasil Step 2–4 tersimpan di `trs_step2`, `trs_step3`, `trs_step4`, dengan kunci assessment, pengguna, dan objective. Bobot tersimpan di `df_step2` dan `df_step3`.
- Roadmap editor, laporan, sumber Step 4, dan scope memakai objective Core Model COBIT 2019 dari database, bukan semua model/focus area.
- Target Roadmap baru disimpan menurut `organization_id`, `objective_id`, dan `year`. Ringkasan Assessment dan PDF membaca target perusahaan milik assessment.
- Data Roadmap lama tanpa perusahaan tetap disimpan dengan `organization_id = NULL`. Data tersebut tidak otomatis disalin ke perusahaan karena tabel lama tidak menyimpan perusahaan/assessment pemiliknya.

## File aplikasi yang perlu ikut diunggah ke cPanel

Unggah ke path proyek Laravel yang sama:

```text
app/Http/Controllers/cobit2019/DesignToolkitController.php
app/Http/Controllers/cobit2019/RoadmapController.php
app/Http/Controllers/cobit2019/Step2Controller.php
app/Http/Controllers/cobit2019/Step3Controller.php
app/Http/Controllers/cobit2019/Step4Controller.php
app/Http/Controllers/cobit2019/FocusAreaController.php
app/Http/Controllers/Assessment/AssessmentSummaryController.php
app/Http/Controllers/Assessment/AssessmentReportController.php
app/Http/Middleware/EnsureCobitAssessmentAccess.php
app/Models/Assessment.php
app/Models/TrsRoadmap.php
app/Services/Cobit/CobitAssessmentAccessService.php
app/Services/Cobit/CobitCoreModelService.php
app/Services/Cobit/DesignFactorDeletionService.php
app/Services/Cobit/DesignFactorProgressService.php
app/Services/Assessment/Summary/AssessmentSummaryService.php
app/Services/Assessment/Report/AssessmentReportService.php
resources/views/assessment/report/export/all-pdf.blade.php
resources/views/design_factor/cobit_home.blade.php
resources/views/design_factor/list.blade.php
resources/views/design_factor/partials/  (seluruh isi folder)
resources/views/design_factor/cobitTools.blade.php
resources/views/design_factor/step2/step2sumaryblade.blade.php
resources/views/design_factor/step3/step3sumaryblade.blade.php
resources/views/design_factor/step4/step4sumaryblade.blade.php
resources/views/design_factor/roadmap/index.blade.php
resources/views/design_factor/roadmap/report.blade.php
routes/web.php
database/migrations/2026_10_29_000001_add_organization_to_trs_roadmap_table.php
```

File di atas merupakan daftar perubahan fitur ini. Gunakan versi kode dari checkout yang sama agar dependensinya konsisten.

## Migrasi

Unggah file migrasi terlebih dahulu. Jalankan dari root proyek Laravel, dengan koneksi database aplikasi cPanel yang benar, sebelum mengunggah file aplikasi yang memakai kolom baru:

```bash
php artisan migrate --path=database/migrations/2026_10_29_000001_add_organization_to_trs_roadmap_table.php --force
```

Setelah migrasi selesai, unggah file aplikasi pada daftar di atas, kemudian jalankan `php artisan optimize:clear`.

Migrasi menambah kolom perusahaan dan unique index. Baris target lama dipertahankan. Jangan menjalankan migrasi lain atau mengimpor ulang dump seluruh database untuk perubahan ini.

## Pemeriksaan setelah unggah

1. Pilih satu perusahaan pada akun; landing harus menampilkan empat langkah.
2. Buka Design Factor List; perusahaan lain tidak tampil.
3. Jika akun berhak pada beberapa perusahaan, pilih dua perusahaan dan periksa daftar keduanya.
4. Simpan Step 2, Step 3, dan Step 4; buka ulang hasilnya dan periksa progres tersimpan.
5. Roadmap harus berisi 40 baris Core Model tanpa objective seperti `AI01.M27` atau clone Security/DevOps.
6. Simpan target tahun yang sama pada dua perusahaan; target dan penghapusan tahun harus tetap terpisah.
7. Ringkasan Assessment harus mengambil target dari perusahaan assessment tersebut.
