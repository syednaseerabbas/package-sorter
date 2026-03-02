/**
 * Smarter Technology — Robotic Arm Package Sorter
 *
 * Dispatches packages to the correct stack based on volume and mass.
 *
 * Stacks:
 *   STANDARD — not bulky and not heavy
 *   SPECIAL  — bulky OR heavy (but not both)
 *   REJECTED — bulky AND heavy
 *
 * Bulky: volume >= 1,000,000 cm³  OR  any dimension >= 150 cm
 * Heavy: mass >= 20 kg
 */

// ─────────────────────────────────────────────
//  Core Function
// ─────────────────────────────────────────────

/**
 * @param {number} width   Width in cm
 * @param {number} height  Height in cm
 * @param {number} length  Length in cm
 * @param {number} mass    Mass in kg
 * @returns {'STANDARD' | 'SPECIAL' | 'REJECTED'}
 * @throws {Error} on invalid input
 */
function sortPackage(width, height, length, mass) {
  const params = { width, height, length, mass };

  for (const [name, value] of Object.entries(params)) {
    if (typeof value !== "number" || isNaN(value) || !isFinite(value)) {
      throw new Error(`'${name}' must be a finite number, got: ${value}`);
    }
    if (value <= 0) {
      throw new Error(`'${name}' must be a positive number, got: ${value}`);
    }
  }

  const volume  = width * height * length;
  const isBulky = volume >= 1_000_000 || width >= 150 || height >= 150 || length >= 150;
  const isHeavy = mass >= 20;

  if (isBulky && isHeavy) return "REJECTED";
  if (isBulky || isHeavy) return "SPECIAL";
  return "STANDARD";
}

module.exports = { sortPackage };


// ─────────────────────────────────────────────
//  Tests  (only runs via: node sort.js test)
// ─────────────────────────────────────────────

if (require.main === module && process.argv[2] === "test") {
  let passed = 0;
  let failed = 0;

  function check(label, expected, actual) {
    if (expected === actual) {
      console.log(`  ✅ PASS: ${label}`);
      passed++;
    } else {
      console.log(`  ❌ FAIL: ${label} — expected ${expected}, got ${actual}`);
      failed++;
    }
  }

  function throws(label, fn) {
    try {
      fn();
      console.log(`  ❌ FAIL: ${label} — expected an Error to be thrown`);
      failed++;
    } catch (e) {
      console.log(`  ✅ PASS: ${label} — caught: ${e.message}`);
      passed++;
    }
  }

  console.log("\n╔══════════════════════════════════════╗");
  console.log("║    Package Sorter — Test Suite       ║");
  console.log("╚══════════════════════════════════════╝");

  console.log("\n[ STANDARD — not bulky, not heavy ]");
  check("Small package (10×10×10, 1 kg)",           "STANDARD", sortPackage(10, 10, 10, 1));
  check("Compact and light (50×50×30, 5 kg)",        "STANDARD", sortPackage(50, 50, 30, 5));
  check("Volume just under threshold (99×100×100)",  "STANDARD", sortPackage(99, 100, 100, 10));
  check("Mass just under threshold (19.99 kg)",      "STANDARD", sortPackage(10, 10, 10, 19.99));
  check("Dimension just under 150 (149.9 cm)",       "STANDARD", sortPackage(149.9, 10, 10, 10));

  console.log("\n[ SPECIAL — bulky only ]");
  check("Volume exactly 1,000,000 cm³",              "SPECIAL",  sortPackage(100, 100, 100, 1));
  check("Volume over 1,000,000 cm³ (200×10×10)",     "SPECIAL",  sortPackage(200, 10, 10, 1));
  check("Width exactly 150 cm",                      "SPECIAL",  sortPackage(150, 10, 10, 1));
  check("Height exactly 150 cm",                     "SPECIAL",  sortPackage(10, 150, 10, 1));
  check("Length exactly 150 cm",                     "SPECIAL",  sortPackage(10, 10, 150, 1));

  console.log("\n[ SPECIAL — heavy only ]");
  check("Mass exactly 20 kg",                        "SPECIAL",  sortPackage(10, 10, 10, 20));
  check("Mass well over 20 kg (50 kg)",              "SPECIAL",  sortPackage(10, 10, 10, 50));

  console.log("\n[ REJECTED — bulky AND heavy ]");
  check("Volume at threshold + heavy",               "REJECTED", sortPackage(100, 100, 100, 20));
  check("Dimension >= 150 + heavy",                  "REJECTED", sortPackage(150, 10, 10, 25));
  check("All extreme values (200×200×200, 100 kg)",  "REJECTED", sortPackage(200, 200, 200, 100));

  console.log("\n[ Edge cases ]");
  check("Float dimensions (10.5×10.5×10.5, 1.5 kg)","STANDARD", sortPackage(10.5, 10.5, 10.5, 1.5));
  check("Mass exactly 20.0 (float)",                 "SPECIAL",  sortPackage(10, 10, 10, 20.0));
  check("Volume exactly 1,000,000.0 (float)",        "SPECIAL",  sortPackage(100.0, 100.0, 100.0, 1.0));

  console.log("\n[ Input validation — must throw ]");
  throws("Zero width",          () => sortPackage(0, 10, 10, 5));
  throws("Negative height",     () => sortPackage(10, -5, 10, 5));
  throws("Zero mass",           () => sortPackage(10, 10, 10, 0));
  throws("Negative length",     () => sortPackage(10, 10, -1, 5));
  throws("Infinity width",      () => sortPackage(Infinity, 10, 10, 5));
  throws("NaN mass",            () => sortPackage(10, 10, 10, NaN));
  throws("String width",        () => sortPackage("big", 10, 10, 5));
  throws("Undefined mass",      () => sortPackage(10, 10, 10, undefined));

  const status = failed === 0 ? "✅ All tests passed" : `❌ ${failed} test(s) failed`;
  console.log("\n══════════════════════════════════════════");
  console.log(`  ${passed} passed  |  ${failed} failed  —  ${status}`);
  console.log("══════════════════════════════════════════\n");

  process.exit(failed > 0 ? 1 : 0);
}
