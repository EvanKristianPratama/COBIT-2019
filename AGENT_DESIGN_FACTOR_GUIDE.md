# 🧠 AI GUIDE: COBIT 2019 Design Factors

> Panduan khusus untuk AI Agent agar tidak keluar konteks saat bekerja dengan Design Factor (DF1-DF10).

---

## 📋 Overview

Design Toolkit adalah fitur utama COBIT 2019 Assessment System yang menghitung **Relative Importance** dari 40 governance objectives berdasarkan 10 Design Factors. Setiap DF memiliki input berbeda yang mempengaruhi prioritas objectives.

---

## 🎯 Design Factors Summary

| DF | Name | Input Count | Input Type | Constraint |
|----|------|-------------|------------|------------|
| DF1 | Enterprise Strategy | 4 | Radio 1-5 | Max 1 value of 5, max 1 value of 4 |
| DF2 | Enterprise Goals | 13 | Radio 1-5 | 3-5 goals rated 5, 8-10 goals rated 1-4 |
| DF3 | Risk Profile | 19 | **Dropdown** Impact(1-5) + Likelihood(1-5) | Risk = Impact × Likelihood |
| DF4 | I&T-Related Issues | 20 | **Dropdown 1-3** | 1=No Issue, 2=Issue, 3=Serious Issue |
| DF5 | IT Investment Portfolio | 2 | **Percentage** | Sum = 100% (High/Normal) |
| DF6 | Adoption of Cloud Computing | 3 | **Percentage** | Sum = 100% |
| DF7 | IT Sourcing Model | 4 | Radio 1-5 (or %) | USE_AVERAGE_CALCULATION |
| DF8 | IT Implementation Methods | 3 | **Percentage** | Sum = 100% |
| DF9 | Technology Adoption Strategy | 3 | **Percentage** | Sum = 100% |
| DF10 | Threat Landscape | 3 | **Percentage** | Sum = 100% |

> [!IMPORTANT]
> - **DF3**: Menggunakan 2 dropdown (Impact & Likelihood) per row, hasil = perkalian keduanya
> - **DF4**: Menggunakan dropdown dengan 3 opsi, bukan 5
> - **DF5, DF6, DF8, DF9, DF10**: Input persentase yang HARUS berjumlah 100%

---

## 📁 File Locations

### Data Configuration
```
app/Data/Cobit/
├── Df1Data.php      # 4 inputs × 40 objectives
├── Df2Data.php      # 13 inputs × 40 objectives
├── Df3Data.php      # 19 inputs × 40 objectives (SPECIAL: 3 models)
├── Df4Data.php      # 12 inputs × 40 objectives
├── Df5Data.php      # 2 inputs × 40 objectives (PERCENTAGE)
├── Df6Data.php      # 3 inputs × 40 objectives (PERCENTAGE)
├── Df7Data.php      # 4 inputs × 40 objectives
├── Df8Data.php      # 3 inputs × 40 objectives (PERCENTAGE)
├── Df9Data.php      # 3 inputs × 40 objectives (PERCENTAGE)
└── Df10Data.php     # 3 inputs × 40 objectives (PERCENTAGE)
```

### Data File Structure
Setiap `DfNData.php` HARUS memiliki:

```php
final class DfNData
{
    public const INPUT_COUNT = N;           // Jumlah input
    public const OBJECTIVE_COUNT = 40;      // Selalu 40
    public const BASELINE_INPUTS = [...];   // Nilai default baseline
    public const BASELINE_SCORES = [...];   // 40 nilai baseline score
    public const MAP = [                    // Matrix 40 × N
        [coef1, coef2, ...],  // EDM01
        [coef1, coef2, ...],  // EDM02
        // ... 40 rows total
    ];
}
```

### Blade Views (Legacy)
```
resources/views/cobit2019/
├── df1/
│   ├── design_factor.blade.php    # Input form + charts
│   └── df1_output.blade.php       # Output/hasil (read-only)
├── df2/ ... df10/                  # Same pattern
```

### Vue Components (New - To Be Created)
```
resources/js/Pages/DesignToolkit/
├── Index.vue                       # Home listing
├── Components/                     # Shared
│   ├── InputTable.vue
│   ├── SpiderChart.vue
│   ├── BarChart.vue
│   └── RelativeImportanceTable.vue
└── DF1/ ... DF10/                  # Per-DF pages
```

---

## 🔢 Core Calculation Formula

### Step 1: Calculate Score
```javascript
// Score = SUM(MAP[i][j] × INPUT[j]) for each objective i
const scores = MAP.map(row => 
    row.reduce((sum, coef, j) => sum + coef * inputs[j], 0)
);
```

### Step 2: Calculate Average Ratio (E14)
```javascript
const avgInput = inputs.reduce((a, b) => a + b, 0) / inputs.length;
const avgBaseline = BASELINE_INPUTS.reduce((a, b) => a + b, 0) / BASELINE_INPUTS.length;
const E14 = avgBaseline / avgInput;  // Baseline ratio
```

### Step 3: Calculate Relative Importance
```javascript
// RI = round((E14 × 100 × score / baselineScore) / 5) × 5 - 100
const relativeImportance = scores.map((score, i) => {
    const baselineScore = BASELINE_SCORES[i];
    return Math.round((E14 * 100 * score / baselineScore) / 5) * 5 - 100;
});
```

> [!IMPORTANT]
> Hasil RI dibulatkan ke kelipatan 5 (0, 5, 10, 15, dst).  
> Range valid: -100 hingga +100.

---

## 🎨 UI Components Required

### 1. InputTable (Radio - DF1, DF2, DF7)
- Radio buttons 1-5 per row
- Columns: Value | Explanation | Importance (radio) | Baseline
- Optional: Suggestion toggle (constraint mode)

### 2. DropdownInput (DF3, DF4)
**DF3 (Risk Profile):**
- 2 dropdown per row: Impact (1-5) + Likelihood (1-5)
- Auto-calculate Risk Rating = Impact × Likelihood
- Color coding: Green (≤6), Yellow (7-12), Red (>12)

**DF4 (I&T-Related Issues):**
- 1 dropdown per row: 1-3 scale
- Labels: 1=No Issue, 2=Issue, 3=Serious Issue
- Color coding: Green (1), Yellow (2), Red (3)

### 3. PercentageInput (DF5, DF6, DF8, DF9, DF10)
- Number input fields (0-100%)
- Auto-adjust when one value changes (enforce sum = 100%)
- Pie chart visualization
- Labels vary per DF (e.g., DF5: High/Normal, DF6: Full Cloud/Hybrid/On-Premise)


### 2. SpiderChart (Radar)
- 40 labels (EDM01-MEA04)
- Dynamic color: Blue for positive, Red for negative
- Scale: -100 to +100

### 3. BarChart (Horizontal)
- 40 bars representing objectives
- Color coding: Blue (+), Red (-), Gray (0)
- Y-axis: Objective labels
- X-axis: -100 to +100

### 4. RelativeImportanceTable
- 3 columns: Objective | Score | Relative Importance
- Row coloring based on RI value

---

## ⚠️ Common Pitfalls

### ❌ DON'T: Hardcode MAP values in Vue
```javascript
// WRONG - Data will become stale
const MAP = [[1.0, 1.0, 1.5, 1.5], ...]; 
```

### ✅ DO: Pass MAP from server via Inertia props
```php
// Controller
return Inertia::render('DesignToolkit/DF1/Index', [
    'map' => Df1Data::MAP,
    'baselineScores' => Df1Data::BASELINE_SCORES,
    // ...
]);
```

---

### ❌ DON'T: Mix DF data structures
```php
// WRONG - DF3 has different input models!
$inputs = DesignFactor3::pluck('input1df3'); // Only partial data
```

### ✅ DO: Handle DF3 specially
```php
// DF3 uses 3 separate models
$categoryInputs = DesignFactor3a::pluck(...);  // 19 category values
$impactInputs = DesignFactor3b::pluck(...);    // 19 impact values  
$likelihoodInputs = DesignFactor3c::pluck(...); // 19 likelihood values
```

---

### ❌ DON'T: Use different rounding methods
```javascript
// WRONG - inconsistent with original
const ri = Math.round(raw / 5) * 5;  // Missing -100 offset
```

### ✅ DO: Follow exact formula
```javascript
// CORRECT
const ri = Math.round((E14 * 100 * score / baselineScore) / 5) * 5 - 100;
```

---

### ❌ DON'T: Forget percentage constraint (DF5, DF6, DF8, DF9, DF10)
```javascript
// WRONG - inputs can exceed 100%
const inputs = [50, 60, 40]; // Total = 150%
```

### ✅ DO: Validate percentage totals to 100%
```javascript
// DF5, DF6, DF8, DF9, DF10 use percentages that must sum to 100
if (inputs.reduce((a, b) => a + b, 0) !== 100) {
    throw new Error('Percentage inputs must total 100%');
}
```

### ✅ DO: Auto-adjust complementary values
```javascript
// Example DF5: When input1 changes, auto-update input2
function handleInput1Change(newValue) {
    input2.value = 100 - newValue; // Enforce sum = 100
}
```

---

### ❌ DON'T: Use same calculation for percentage DFs
```javascript
// WRONG - Percentage DFs don't use E14 average ratio
const E14 = avgBaseline / avgInput; // Not applicable for DF5-DF10
```

### ✅ DO: Use direct percentage formula for DF5-DF10
```javascript
// For percentage DFs, inputs are already 0-100, divide by 100 first
const DF_INPUT = inputs.map(v => v / 100); // e.g., [0.33, 0.67]
const scores = MAP.map(row => 
    row.reduce((sum, coef, j) => sum + coef * DF_INPUT[j], 0)
);
// Then: RI = mround(100 * score / baselineScore, 5) - 100
```

---

## 🏷️ Objective Labels

All 40 objectives follow this order:

```javascript
const OBJECTIVE_LABELS = [
    // EDM Domain (5)
    'EDM01', 'EDM02', 'EDM03', 'EDM04', 'EDM05',
    // APO Domain (14)
    'APO01', 'APO02', 'APO03', 'APO04', 'APO05',
    'APO06', 'APO07', 'APO08', 'APO09', 'APO10',
    'APO11', 'APO12', 'APO13', 'APO14',
    // BAI Domain (11)
    'BAI01', 'BAI02', 'BAI03', 'BAI04', 'BAI05',
    'BAI06', 'BAI07', 'BAI08', 'BAI09', 'BAI10', 'BAI11',
    // DSS Domain (6)
    'DSS01', 'DSS02', 'DSS03', 'DSS04', 'DSS05', 'DSS06',
    // MEA Domain (4)
    'MEA01', 'MEA02', 'MEA03', 'MEA04',
];
```

---

## 🔗 Related Files

When working with Design Factors, always consider these related files:

- **Controllers**: `app/Http/Controllers/cobit2019/Df*Controller.php`
- **Models**: `app/Models/DesignFactor*.php`
- **Routes**: `routes/web.php` (search for `df1`, `df2`, etc.)
- **Existing Vue**: `resources/js/Pages/CobitComponents/` (reference for patterns)

---

## ✅ Checklist Before Implementation

- [ ] Verify which DF you're working on (DF1-DF10)
- [ ] Check INPUT_COUNT in corresponding `DfNData.php`
- [ ] Confirm calculation formula matches this guide
- [ ] Handle special cases:
  - DF3: 3 separate models (a, b, c)
  - DF5, DF6, DF8, DF9, DF10: Percentage inputs (sum = 100%)
- [ ] Test with known baseline values
- [ ] Ensure charts update reactively

---

*Last updated: 3 Februari 2026*
