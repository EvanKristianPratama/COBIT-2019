# 🎨 AGENT FRONTEND GUIDE - COBIT 2019 Assessment System

> Dokumentasi khusus untuk AI Agent agar tidak keluar konteks saat bekerja dengan frontend.

---

## 📦 Tech Stack Frontend

| Technology | Version | Purpose |
|------------|---------|---------|
| Vue 3 | ^3.x | UI Framework (Composition API + `<script setup>`) |
| Inertia.js | ^1.x | SPA bridge Laravel ↔ Vue |
| Vite | ^5.x | Build tool & dev server |
| Tailwind CSS | ^3.x | Utility-first CSS |
| Laravel Socialite | ^5.x | Google OAuth Authentication |
| Headless UI | ^1.x | Unstyled accessible components |
| Heroicons | ^2.x | SVG icons |
| Jspreadsheet | v5 | Spreadsheet Data Editor |

---

## 📁 Struktur Folder Frontend

```
resources/
├── js/
│   ├── app.js                    # Entry point - createInertiaApp
│   ├── bootstrap.js              # Axios setup
│   ├── Layouts/
│   │   └── AuthenticatedLayout.vue # Main Layout (Navbar + Sidebar + Dark Mode)
│   ├── Pages/
│   │   ├── Auth/
│   │   │   └── Login.vue         # Dual Auth (Manual + Google Socialite)
│   │   ├── Dashboard/
│   │   │   └── Home.vue          # User Dashboard
│   │   ├── Spreadsheet/
│   │   │   ├── Index.vue         # List Spreadsheets
│   │   │   ├── Create.vue        # Create/Import
│   │   │   └── Show.vue          # Editor (Jspreadsheet)
│   │   └── Profile/
│   │       └── Index.vue         # User Profile
│   ├── Components/
│   │   ├── Breadcrumbs.vue       # Navigation Trail
│   │   ├── PageHeader.vue        # Standard Page Header
│   │   └── ConfirmModal.vue      # Reusable Modal
├── css/
│   └── app.css                   # Tailwind imports
└── views/
    └── app.blade.php             # ROOT TEMPLATE INERTIA (DO NOT DELETE!)
```

---

## 🎨 Design System

### Color Palette (Light Theme - Modern)
- **Primary**: Emerald/Teal gradient (`from-emerald-500 to-teal-600`)
- **Background**: Gray-50 (`bg-gray-50`)
- **Cards**: White dengan border gray-100 (`bg-white border border-gray-100`)
- **Text Primary**: Gray-900 (`text-gray-900`)
- **Text Secondary**: Gray-500 (`text-gray-500`)
- **Accent Colors**:
  - COBIT: Emerald (`emerald-500`) - *Core Identity*
  - Auth/Admin: Indigo (`indigo-600`) - *Professional Accent*

### Color Palette (Dark Theme - Enterprise)
- **Background Main**: `#0f0f0f` (Ultra Dark)
- **Card Surface**: `#1a1a1a` (Dark Gray)
- **Border**: `dark:border-white/5` (Subtle)
- **Text Primary**: `dark:text-white`
- **Text Secondary**: `dark:text-gray-400`
- **Hover**: `dark:hover:bg-white/5`

### Component Patterns
- **Cards**: `bg-white dark:bg-[#1a1a1a] rounded-2xl p-6 border border-gray-200/80 dark:border-white/5 shadow-sm`
- **Inputs**: `rounded-xl border-gray-200 dark:border-white/10 dark:bg-white/5 focus:ring-2 focus:ring-emerald-500`
- **Buttons**:
  - Primary: `bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl shadow-lg`
  - Secondary: `bg-white dark:bg-white/10 border border-gray-200`
- **Page Header**:
  Gunakan `Components/PageHeader.vue` untuk konsistensi judul dan breadcrumbs.

---

## 🔑 Pola & Konvensi

### 1. Inertia Page Props
Setiap page menerima props dari Laravel controller/route (`HandleInertiaRequests`):

```vue
<script setup>
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const user = page.props.auth.user;
const flash = page.props.flash;
</script>
```

### 2. Layout Pattern
Page HARUS wrap content dengan layout:

```vue
<template>
    <AuthenticatedLayout title="Page Title">
        <template #header>
            <PageHeader title="Judul" :breadcrumbs="breadcrumbs" />
        </template>

        <!-- Content Area -->
        <div class="space-y-6">
            <!-- Cards/Table here -->
        </div>
    </AuthenticatedLayout>
</template>
```

### 3. Authentication Pattern (Dual Mode)
- **Manual**: Menggunakan `useForm` Inertia ke route `POST /login`.
- **Socialite**: Direct link ke `/login/google` -> Laravel handle callback.
- **Pending State**: Cek `user.isActivated` atau flash status 'pending'.

### 4. Navigation (Breadcrumbs)
Setiap halaman dalam harus menyertakan breadcrumbs untuk navigasi user.
```javascript
const breadcrumbs = [
    { label: 'Dashboard', href: '/dashboard' },
    { label: 'Spreadsheets', href: '/spreadsheet' },
    { label: 'Edit' }
];
```

---

## 🏗️ Architecture Patterns

### 1. Smart vs Dumb Components
- **Pages (`resources/js/Pages/`)**: Menangani logic, fetch data, dan state halaman.
- **Components (`resources/js/Components/`)**: Reusable UI, menerima props, emit events. (Contoh: `PageHeader`, `Breadcrumbs`).

### 2. Formatting & Assets
- **Logo**: 
  - Navbar: `/images/cobitColour.png` (Resized `h-8`)
  - Carousel: `/images/logo-divusi.png` (White/Inverted `h-16`)
- **Images**: Gunakan absolute path dari `/public/images/` atau URL eksternal (Unsplash).

### 🛑 Architecture Don'ts
1. ❌ **JANGAN** gunakan `GuestLayout` (sudah deprecated/unused). Login page adalah standalone.
2. ❌ **JANGAN** hardcode warna hex sembarangan. Gunakan Tailwind classes atau variable CSS yang sudah ada.
3. ❌ **JANGAN** hapus `app.blade.php`.
4. ❌ **JANGAN** akses `auth()->user()` di template. Gunakan `page.props.auth.user`.

### ✅ Architecture Do's
1. ✅ **SELALU** support Dark Mode (`dark:` classes).
2. ✅ **SELALU** gunakan `TransitionRoot` untuk modal/dropdown.
3. ✅ **SELALU** run `npm run build` setelah perubahan besar.
4. ✅ **SELALU** cek responsivitas mobile (terutama tabel dan layout split).

---

*Last updated: 3 Februari 2026*
