# COBIT 2019 — Public API Documentation

Dokumentasi lengkap Public API untuk integrasi lintas-aplikasi pada framework COBIT 2019.

---

## Daftar Isi

- [1. Overview](#1-overview)
- [2. RACI Roles Matrix API](#2-raci-roles-matrix-api)
  - [2.1 Endpoint](#21-endpoint)
  - [2.2 Query Parameters](#22-query-parameters)
  - [2.3 Response Structure](#23-response-structure)
  - [2.4 Contoh Request & Response](#24-contoh-request--response)
- [3. GAMO Information Flow API](#3-gamo-information-flow-api)
  - [3.1 Endpoint](#31-endpoint)
  - [3.2 Query Parameters](#32-query-parameters)
  - [3.3 Response Structure](#33-response-structure)
  - [3.4 Contoh Request & Response](#34-contoh-request--response)
- [4. Frontend (FE) GAMO Analysis](#4-frontend-fe-gamo-analysis)
  - [4.1 Halaman & Route](#41-halaman--route)
  - [4.2 Arsitektur FE](#42-arsitektur-fe)
  - [4.3 Alur Data FE](#43-alur-data-fe)
- [5. CORS & Error Handling](#5-cors--error-handling)
- [6. Data Model](#6-data-model)
- [7. File Terkait](#7-file-terkait)

---

## 1. Overview

Proyek ini menyediakan beberapa endpoint API publik yang dapat diakses oleh aplikasi eksternal tanpa autentikasi (Public API) untuk kebutuhan integrasi data COBIT 2019.

| Endpoint | Deskripsi |
|----------|-----------|
| `GET /api/cobit/roles-matrix` | Mengembalikan matriks RACI (Responsible, Accountable, Consulted, Informed) roles untuk setiap management practice. |
| `GET /api/cobit/gamo-infoflow` | Mengembalikan alur informasi detail (Input → Practice → Output) beserta data RACI untuk analysis visual. |

---

## 2. RACI Roles Matrix API

### 2.1 Endpoint

```
GET /api/cobit/roles-matrix
```

- **Autentikasi:** Tidak diperlukan (public route)
- **Method:** `GET`
- **Content-Type Response:** `application/json`

### 2.2 Query Parameters

| Parameter | Tipe | Wajib | Deskripsi |
|-----------|------|-------|-----------|
| `objective_id` | `string` | ❌ | Filter matriks berdasarkan objective tertentu. Contoh: `EDM01`, `APO02` |

### 2.3 Response Structure

```json
{
  "success": true,
  "roles": [
    {
      "role_id": "integer",
      "role_name": "string",
      "description": "string"
    }
  ],
  "matrix": [
    {
      "practice_id": "string",
      "practice_name": "string",
      "practice_description": "string",
      "objective_id": "string",
      "objective_name": "string",
      "role_assignments": {
        "<role_id>": "R | A | C | I"
      }
    }
  ]
}
```

### 2.4 Contoh Request & Response

#### Request
```bash
curl http://https://cobit2019.divusi.co.id//api/cobit/roles-matrix?objective_id=EDM01
```

#### Response
```json
{
  "success": true,
  "roles": [
    { "role_id": 1, "role_name": "Board", "description": "..." },
    { "role_id": 2, "role_name": "Executive Committee", "description": "..." }
  ],
  "matrix": [
    {
      "practice_id": "EDM01.01",
      "practice_name": "Evaluate the governance system.",
      "practice_description": "Continually identify and engage with the enterprise's stakeholders...",
      "objective_id": "EDM01",
      "objective_name": "Ensured Governance Framework Setting and Maintenance",
      "role_assignments": {
        "1": "A",
        "2": "R",
        "3": "R",
        "7": "R",
        "10": "R"
      }
    }
  ]
}
```

---

## 3. GAMO Information Flow API

### 3.1 Endpoint

```
GET /api/cobit/gamo-infoflow
```

- **Autentikasi:** Tidak diperlukan (public route)
- **Method:** `GET`
- **Content-Type Response:** `application/json`

### 3.2 Query Parameters

| Parameter | Tipe | Wajib | Default | Deskripsi |
|-----------|------|-------|---------|-----------|
| `objective_id` | `string` | ❌ | — | Filter satu objective spesifik. Contoh: `EDM01`, `APO02` |
| `domain` | `string` | ❌ | — | Filter semua objective dalam satu domain. Contoh: `EDM`, `APO`, `BAI`, `DSS`, `MEA` |

> Jika kedua parameter diberikan, `objective_id` diprioritaskan.  
> Jika tidak ada parameter, semua 40 objectives dikembalikan.

### 3.3 Response Structure

```json
{
  "success": true,
  "objectives": [
    {
      "objective_id": "string",
      "objective": "string",
      "objective_description": "string",
      "practices": [
        {
          "practice_id": "string",
          "practice_name": "string",
          "practice_description": "string",
          "inputs": [
            {
              "input_id": "integer",
              "from": "string | null",
              "description": "string | null"
            }
          ],
          "outputs": [
            {
              "output_id": "integer",
              "to": "string | null",
              "description": "string | null"
            }
          ],
          "role_assignments": {
            "<role_id>": {
              "role_name": "string",
              "raci": "R | A | C | I"
            }
          }
        }
      ]
    }
  ],
  "roles": [
    {
      "role_id": "integer",
      "role_name": "string",
      "description": "string"
    }
  ]
}
```

### 3.4 Contoh Request & Response

#### Request
```bash
curl http://localhost:8000/api/cobit/gamo-infoflow?objective_id=EDM01
```

#### Response
```json
{
  "success": true,
  "objectives": [
    {
      "objective_id": "EDM01",
      "objective": "Ensured Governance Framework Setting and Maintenance",
      "objective_description": "Analyze and articulate the requirements for the governance of enterprise I&T...",
      "practices": [
        {
          "practice_id": "EDM01.01",
          "practice_name": "Evaluate the governance system.",
          "practice_description": "Continually identify and engage with the enterprise's stakeholders...",
          "inputs": [
            {
              "input_id": 1,
              "from": "MEA03.02",
              "description": "Communications of changed compliance requirements"
            },
            {
              "input_id": 2,
              "from": "Outside COBIT",
              "description": "• Constitution/bylaws/statutes of organization\n• Governance/decisionmaking model..."
            }
          ],
          "outputs": [
            {
              "output_id": 1,
              "to": "All EDM; APO01.01; APO01.03; APO01.04",
              "description": "Enterprise governance guiding principles"
            }
          ],
          "role_assignments": {
            "1": { "role_name": "Board", "raci": "A" },
            "2": { "role_name": "Executive Committee", "raci": "R" }
          }
        }
      ]
    }
  ],
  "roles": [
    { "role_id": 1, "role_name": "Board", "description": "..." }
  ]
}
```

---

## 4. COBIT 2019 Components API

### 4.1 Overview Endpoint Komponen
Terdapat endpoint dinamis dan alias route khusus untuk mengakses 10 komponen COBIT 2019 secara publik:

- **Endpoint Dinamis:** `GET /api/cobit/components/{component}`
- **Endpoint Daftar Komponen:** `GET /api/cobit/components` (Menampilkan daftar semua komponen)
- **Alias Routes:**
  - `GET /api/cobit/overview`
  - `GET /api/cobit/goals`
  - `GET /api/cobit/domains`
  - `GET /api/cobit/practices` atau `/api/cobit/processes`
  - `GET /api/cobit/organizational`
  - `GET /api/cobit/infoflows` atau `/api/cobit/information-flows`
  - `GET /api/cobit/policies`
  - `GET /api/cobit/skills`
  - `GET /api/cobit/culture`
  - `GET /api/cobit/services`

- **Autentikasi:** Tidak diperlukan (public route)
- **Method:** `GET`
- **Content-Type Response:** `application/json`

### 4.2 Query Parameters (Berlaku untuk semua endpoint komponen)

| Parameter | Tipe | Wajib | Deskripsi |
|-----------|------|-------|-----------|
| `objective_id` | `string` | ❌ | Filter data berdasarkan objective tertentu. Contoh: `EDM01` |
| `domain` | `string` | ❌ | Filter data berdasarkan domain tertentu. Contoh: `EDM`, `APO` |

### 4.3 Struktur Response & Contoh Payload per Komponen

Setiap response API komponen memiliki struktur dasar:
```json
{
  "success": true,
  "component": "nama-komponen",
  "data": [ ... ]
}
```

Berikut adalah rincian data schema dan contoh respon untuk masing-masing komponen:

#### 4.3.1 Overview (`/api/cobit/overview`)
Menyediakan deskripsi singkat, tujuan, dan area domain dari objective.
```json
{
  "success": true,
  "component": "overview",
  "data": [
    {
      "objective_id": "EDM01",
      "objective": "Ensured Governance Framework Setting and Maintenance",
      "description": "Analyze and articulate the requirements for the governance of enterprise I&T...",
      "purpose": "Provide a consistent approach and integration with the enterprise governance...",
      "domains": [
        {
          "area": "1",
          "domain": "EDM"
        }
      ]
    }
  ]
}
```

#### 4.3.2 Goals (`/api/cobit/goals`)
Menyediakan pemetaan Enterprise Goals dan Alignment Goals beserta metriknya.
```json
{
  "success": true,
  "component": "goals",
  "data": [
    {
      "objective_id": "EDM01",
      "objective": "Ensured Governance Framework Setting and Maintenance",
      "entergoals": [
        {
          "entergoals_id": "EG01",
          "description": "Portfolio of competitive products and services",
          "metrics": [
            {
              "entergoalsmetr_id": 1,
              "description": "Percent of products and services that meet or exceed target revenue..."
            }
          ]
        }
      ],
      "aligngoals": [
        {
          "aligngoals_id": "AG01",
          "description": "I&T compliance and support for business compliance",
          "metrics": [
            {
              "aligngoalsmetr_id": 1,
              "description": "Number of compliance failures..."
            }
          ]
        }
      ]
    }
  ]
}
```

#### 4.3.3 Domains (`/api/cobit/domains`)
Menyediakan area domain yang terkait dengan objective.
```json
{
  "success": true,
  "component": "domains",
  "data": [
    {
      "objective_id": "EDM01",
      "objective": "Ensured Governance Framework Setting and Maintenance",
      "domains": [
        {
          "area": "1",
          "domain": "EDM"
        }
      ]
    }
  ]
}
```

#### 4.3.4 Practices (`/api/cobit/practices` atau `/api/cobit/processes`)
Menyediakan rincian Management Practices, metrics, activities, guidances (referensi), dan roles.
```json
{
  "success": true,
  "component": "practices",
  "data": [
    {
      "objective_id": "EDM01",
      "objective": "Ensured Governance Framework Setting and Maintenance",
      "practices": [
        {
          "practice_id": "EDM01.01",
          "practice_name": "Evaluate the governance system.",
          "practice_description": "Continually identify and engage with the enterprise's stakeholders...",
          "metrics": [
            {
              "id": 1,
              "description": "Percent of stakeholder feedback incorporated into governance updates."
            }
          ],
          "activities": [
            {
              "activity_id": "EDM01.01.A1",
              "capability_lvl": 2,
              "description": "Define and identify stakeholders..."
            }
          ],
          "guidances": [
            {
              "guidance_id": 1,
              "guidance": "COBIT 5 EDM01.01",
              "reference": "COBIT 5 Framework"
            }
          ],
          "roles": [
            {
              "role_id": 1,
              "role_name": "Board",
              "description": "Board of Directors...",
              "raci": "A"
            }
          ]
        }
      ]
    }
  ]
}
```

#### 4.3.5 Organizational (`/api/cobit/organizational`)
Menyediakan pemetaan struktur organisasi berupa matriks RACI per Management Practice.
```json
{
  "success": true,
  "component": "organizational",
  "data": [
    {
      "objective_id": "EDM01",
      "objective": "Ensured Governance Framework Setting and Maintenance",
      "practices": [
        {
          "practice_id": "EDM01.01",
          "practice_name": "Evaluate the governance system.",
          "practice_description": "...",
          "role_assignments": {
            "1": {
              "role_name": "Board",
              "raci": "A"
            },
            "2": {
              "role_name": "Executive Committee",
              "raci": "R"
            }
          }
        }
      ]
    }
  ]
}
```

#### 4.3.6 Infoflows (`/api/cobit/infoflows` atau `/api/cobit/information-flows`)
Menyediakan alur informasi input/output detail dari setiap practices.
```json
{
  "success": true,
  "component": "infoflows",
  "data": [
    {
      "objective_id": "EDM01",
      "objective": "Ensured Governance Framework Setting and Maintenance",
      "infoflows": [
        {
          "practice_id": "EDM01.01",
          "input": {
            "input_id": 1,
            "practice_id": "EDM01.01",
            "from": "MEA03.02",
            "description": "Communications of changed compliance requirements"
          },
          "connectedoutputs": [
            {
              "output_id": 1,
              "practice_id": "EDM01.01",
              "to": "All EDM; APO01.01; APO01.03; APO01.04",
              "description": "Enterprise governance guiding principles"
            }
          ]
        }
      ]
    }
  ]
}
```

#### 4.3.7 Policies (`/api/cobit/policies`)
Menyediakan kebijakan (policies) dan pedoman regulasi yang disyaratkan oleh objective.
```json
{
  "success": true,
  "component": "policies",
  "data": [
    {
      "objective_id": "EDM01",
      "objective": "Ensured Governance Framework Setting and Maintenance",
      "policies": [
        {
          "policy_id": 1,
          "policy": "IT Governance Policy",
          "description": "Determines the framework for IT strategy...",
          "guidances": [
            {
              "guidance_id": 10,
              "guidance": "ISO/IEC 38500",
              "reference": "Governance of IT for the Organization"
            }
          ]
        }
      ]
    }
  ]
}
```

#### 4.3.8 Skills (`/api/cobit/skills`)
Menyediakan kompetensi dan keahlian personal (skills) yang diperlukan untuk mendukung objective.
```json
{
  "success": true,
  "component": "skills",
  "data": [
    {
      "objective_id": "EDM01",
      "objective": "Ensured Governance Framework Setting and Maintenance",
      "skills": [
        {
          "skill_id": 1,
          "skill": "Business Strategic Planning",
          "guidances": [
            {
              "guidance_id": 20,
              "guidance": "SFIA Skill BSLN",
              "reference": "Skills Framework for the Information Age"
            }
          ]
        }
      ]
    }
  ]
}
```

#### 4.3.9 Culture (`/api/cobit/culture`)
Menyediakan elemen budaya, etika, dan perilaku pendukung governance.
```json
{
  "success": true,
  "component": "culture",
  "data": [
    {
      "objective_id": "EDM01",
      "objective": "Ensured Governance Framework Setting and Maintenance",
      "culture": [
        {
          "keyculture_id": 1,
          "element": "Transparency in decision-making",
          "guidances": [
            {
              "guidance_id": 30,
              "guidance": "King IV Code of Corporate Governance",
              "reference": "Transparency principles"
            }
          ]
        }
      ]
    }
  ]
}
```

#### 4.3.10 Services (`/api/cobit/services`)
Menyediakan deskripsi Services, Infrastructure, and Applications (SIA) yang menjadi dasar infrastruktur data COBIT.
```json
{
  "success": true,
  "component": "services",
  "data": [
    {
      "objective_id": "EDM01",
      "objective": "Ensured Governance Framework Setting and Maintenance",
      "s_i_a": [
        {
          "sia_id": 1,
          "description": "Enterprise Governance Portal"
        }
      ]
    }
  ]
}
```

---

## 5. Focus Areas API

### 5.1 Overview
Focus Areas memungkinkan pengelompokan objectives COBIT 2019 berdasarkan area fokus tertentu (misalnya Security, Digital Transformation, Risk Management).

| Endpoint | Deskripsi |
|----------|-----------|
| `GET /api/cobit/focus-areas` | Mengembalikan daftar semua focus areas beserta objectives mapping-nya. |
| `GET /api/cobit/focus-areas/{id}` | Mengembalikan detail satu focus area beserta objectives dan ringkasan komponennya. |

### 5.2 List Focus Areas

#### Endpoint
```
GET /api/cobit/focus-areas
```

- **Autentikasi:** Tidak diperlukan (public route)
- **Method:** `GET`

#### Response
```json
{
  "success": true,
  "focus_areas": [
    {
      "id": 1,
      "code": "SECURITY",
      "name": "Security",
      "description": "Focus on governance of enterprise security",
      "objectives": [
        {
          "objective_id": "APO13",
          "objective": "Managed Security"
        }
      ]
    }
  ]
}
```

### 5.3 Show Focus Area Detail

#### Endpoint
```
GET /api/cobit/focus-areas/{id}
```

#### Response
```json
{
  "success": true,
  "focus_area": {
    "id": 1,
    "code": "SECURITY",
    "name": "Security",
    "description": "Focus on governance of enterprise security",
    "objectives": [
      {
        "objective_id": "APO13",
        "objective": "Managed Security",
        "description": "...",
        "purpose": "...",
        "domains": [{ "area": "2", "domain": "APO" }],
        "practices_count": 3,
        "policies_count": 2,
        "skills_count": 1,
        "culture_count": 1,
        "sia_count": 1
      }
    ]
  }
}
```

---

## 6. Frontend (FE) GAMO Analysis

### 6.1 Halaman & Route

| Item | Detail |
|------|--------|
| **URL** | `/objectives/analysis/gamo` |
| **Route Name** | `cobit_component.gamoanalysis` |
| **View File** | `resources/views/cobit_component/gamoanalisis.blade.php` |
| **Auth** | Diperlukan (`auth` + `permission:cobit.view`) |
| **Layout** | Extends `layouts.app` |

### 6.2 Arsitektur FE

Halaman ini adalah **Single Page** dengan layout visual 5-kolom per baris:

```
[Input Card] (Biru) ──> [Arrow] ──> [Practice Card + RACI Pills] (Ungu) ──> [Arrow] ──> [Output Card] (Pink)
```

RACI Pills Warna:
- **R**: 🔴 Merah (Responsible)
- **A**: 🟡 Kuning (Accountable)
- **C**: 🟢 Hijau (Consulted)
- **I**: 🔵 Cyan (Informed)

### 6.3 Alur Data FE

1. Halaman load melakukan `fetch('/objectives')` untuk mengambil daftar objectives beserta relasinya.
2. Menyimpan data di cache client `cachedObjectives`.
3. Render flow row per practice:
   - Inputs di-extract dari `practice.infoflowinput`.
   - Outputs digabungkan dari `input.connectedoutputs` (via pivot `trs_infoflowio`) dan `practice.infoflowoutput` (fallback).
   - RACI roles di-extract dari `practice.roles`.

---

## 7. CORS & Error Handling

Semua endpoint API publik mengirimkan header CORS berikut agar dapat diakses dari domain frontend lain:

```
Access-Control-Allow-Origin: *
Access-Control-Allow-Headers: Content-Type, X-Requested-With, Authorization
Access-Control-Allow-Methods: GET, OPTIONS
```

### HTTP Status Codes

| Status Code | Deskripsi |
|-------------|-----------|
| `200 OK` | Berhasil. Jika tidak ada data yang sesuai filter, properti data utama akan berupa array kosong |
| `404 Not Found` | Komponen tidak valid (tidak dikenal) |
| `500 Server Error` | Terjadi kesalahan pada server (contoh: database tidak merespons) |

---

## 8. Data Model

```
mst_objective (1) ──────< mst_practice (N)
                              │
                    ┌─────────┼──────────┐
                    │         │          │
              infoflowinput  roles   infoflowoutput
              (hasMany)    (M:N)     (hasMany)
                    │         │          │
          mst_infoflowinput   │   mst_infoflowoutput
                    │    trs_practroles     │
                    │                      │
                    └──────── M:N ─────────┘
                         trs_infoflowio
```

---

## 9. File Terkait

### Backend
- **Controller:** [MstObjectiveController.php](file:///Users/mac/Desktop/MAGANG/cbioo/app/Http/Controllers/cobit2019/MstObjectiveController.php) (method `getRolesMatrix()`, `getGamoInfoflow()`, `getComponentsList()`, dan `getComponentApi()`)
- **Controller:** [FocusAreaController.php](file:///Users/mac/Desktop/MAGANG/cbioo/app/Http/Controllers/cobit2019/FocusAreaController.php) (Focus Area CRUD, API)
- **Routes:** [web.php](file:///Users/mac/Desktop/MAGANG/cbioo/routes/web.php) (Public routes section, Component APIs & Aliases, Focus Area routes)
- **Models:**
  - [MstObjective.php](file:///Users/mac/Desktop/MAGANG/cbioo/app/Models/MstObjective.php)
  - [MstFocusArea.php](file:///Users/mac/Desktop/MAGANG/cbioo/app/Models/MstFocusArea.php)
  - [MstPractice.php](file:///Users/mac/Desktop/MAGANG/cbioo/app/Models/MstPractice.php)
  - [MstInfoflowInput.php](file:///Users/mac/Desktop/MAGANG/cbioo/app/Models/MstInfoflowInput.php)
  - [MstInfoflowOutput.php](file:///Users/mac/Desktop/MAGANG/cbioo/app/Models/MstInfoflowOutput.php)
  - [MstRoles.php](file:///Users/mac/Desktop/MAGANG/cbioo/app/Models/MstRoles.php)
  - [MstPolicy.php](file:///Users/mac/Desktop/MAGANG/cbioo/app/Models/MstPolicy.php)
  - [MstSkill.php](file:///Users/mac/Desktop/MAGANG/cbioo/app/Models/MstSkill.php)
  - [MstKeyCulture.php](file:///Users/mac/Desktop/MAGANG/cbioo/app/Models/MstKeyCulture.php)
  - [MstSIA.php](file:///Users/mac/Desktop/MAGANG/cbioo/app/Models/MstSIA.php)

### Frontend
- **View:** [gamoanalisis.blade.php](file:///Users/mac/Desktop/MAGANG/cbioo/resources/views/cobit_component/gamoanalisis.blade.php)
- **View:** [focus_area/index.blade.php](file:///Users/mac/Desktop/MAGANG/cbioo/resources/views/focus_area/index.blade.php)
- **View:** [focus_area/show.blade.php](file:///Users/mac/Desktop/MAGANG/cbioo/resources/views/focus_area/show.blade.php)

### Database
- **SQL Setup:** [focus_area_setup.sql](file:///Users/mac/Desktop/MAGANG/cbioo/database/sql/focus_area_setup.sql)
