# 📦 Package Sorter — Smarter Technology

> Robotic arm dispatch logic that routes packages to the correct stack based on their dimensions and mass.

---

## How It Works

Each package is evaluated against two criteria:

| Criteria | Rule |
|----------|------|
| **Bulky** | Volume (`W × H × L`) **≥ 1,000,000 cm³** _or_ any single dimension **≥ 150 cm** |
| **Heavy** | Mass **≥ 20 kg** |

The result determines which stack the package is sent to:

| Bulky | Heavy | Stack |
|:-----:|:-----:|-------|
| ❌ | ❌ | **STANDARD** — handled automatically |
| ✅ | ❌ | **SPECIAL** — requires manual handling |
| ❌ | ✅ | **SPECIAL** — requires manual handling |
| ✅ | ✅ | **REJECTED** — cannot be processed |

---

## Requirements

- PHP **8.0+**

---

## Installation

```bash
git clone https://github.com/syednaseerabbas/package-sorter.git
cd package-sorter
```

No dependencies. No Composer required.

---

## Usage

```php
<?php
require_once 'sort.php';

echo sort_package(10, 10, 10, 1);      // STANDARD  — small and light
echo sort_package(100, 100, 100, 1);   // SPECIAL   — bulky (volume = 1,000,000 cm³)
echo sort_package(10, 10, 10, 20);     // SPECIAL   — heavy
echo sort_package(150, 10, 10, 25);    // REJECTED  — both bulky and heavy
```

### Function Signature

```php
sort_package(int|float $width, int|float $height, int|float $length, int|float $mass): string
```

| Parameter | Type | Unit |
|-----------|------|------|
| `$width`  | `int\|float` | cm |
| `$height` | `int\|float` | cm |
| `$length` | `int\|float` | cm |
| `$mass`   | `int\|float` | kg |

**Returns:** `'STANDARD'` | `'SPECIAL'` | `'REJECTED'`

**Throws:** `InvalidArgumentException` if any parameter is zero, negative, or non-finite (`INF`, `NAN`).

---

## Running Tests

Tests are bundled in the same file and activated via a CLI flag:

```bash
php sort.php test
```

Expected output:

```
╔══════════════════════════════════════╗
║    Package Sorter — Test Suite       ║
╚══════════════════════════════════════╝

[ STANDARD — not bulky, not heavy ]
  ✅ PASS: Small package (10×10×10, 1 kg)
  ✅ PASS: Compact and light (50×50×30, 5 kg)
  ...

[ REJECTED — bulky AND heavy ]
  ✅ PASS: Volume at threshold + heavy
  ...

[ Input validation — must throw ]
  ✅ PASS: Zero width — caught: 'width' must be a positive number, got: 0
  ...

══════════════════════════════════════════
  21 passed  |  0 failed  —  ✅ All tests passed
══════════════════════════════════════════
```

The test suite covers:

- All three stack outcomes (`STANDARD`, `SPECIAL`, `REJECTED`)
- Exact boundary values (at threshold, just above, just below)
- Float inputs
- Exception handling for invalid inputs (`0`, negative, `INF`, `NAN`)

---

## File Structure

```
.
├── sort.php     # Core function + bundled test suite
└── README.md
```

---

## License

MIT
