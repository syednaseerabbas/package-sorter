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

- Node.js **v13.2+** (numeric separators: `1_000_000`)

---

## Installation

```bash
git clone https://github.com/syednaseerabbas/package-sorter.git
cd package-sorter && cd JS
```

No dependencies. No `npm install` required.

---

## Usage

```js
const { sortPackage } = require("./sort");

console.log(sortPackage(10, 10, 10, 1));      // STANDARD  — small and light
console.log(sortPackage(100, 100, 100, 1));   // SPECIAL   — bulky (volume = 1,000,000 cm³)
console.log(sortPackage(10, 10, 10, 20));     // SPECIAL   — heavy
console.log(sortPackage(150, 10, 10, 25));    // REJECTED  — both bulky and heavy
```

### Function Signature

```js
sortPackage(width, height, length, mass) → 'STANDARD' | 'SPECIAL' | 'REJECTED'
```

| Parameter | Type | Unit |
|-----------|------|------|
| `width`   | `number` | cm |
| `height`  | `number` | cm |
| `length`  | `number` | cm |
| `mass`    | `number` | kg |

**Returns:** `'STANDARD'` | `'SPECIAL'` | `'REJECTED'`

**Throws:** `Error` if any parameter is not a positive finite number (catches `0`, negatives, `Infinity`, `NaN`, strings, `undefined`).

---

## Running Tests

Tests are bundled in the same file and activate only when run directly from the CLI:

```bash
node sort.js test
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
  ✅ PASS: NaN mass — caught: 'mass' must be a finite number, got: NaN
  ✅ PASS: String width — caught: 'width' must be a finite number, got: big
  ...

══════════════════════════════════════════
  26 passed  |  0 failed  —  ✅ All tests passed
══════════════════════════════════════════
```

The test suite covers:

- All three stack outcomes (`STANDARD`, `SPECIAL`, `REJECTED`)
- Exact boundary values (at threshold, just above, just below)
- Float inputs
- Exception handling for invalid inputs (`0`, negatives, `Infinity`, `NaN`, strings, `undefined`)

---

## File Structure

```
.
├── sort.js      # Core function + bundled test suite
└── README.md
```

---

## License

MIT
