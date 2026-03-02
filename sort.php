<?php

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
 * @param int|float $width   Width in cm
 * @param int|float $height  Height in cm
 * @param int|float $length  Length in cm
 * @param int|float $mass    Mass in kg
 *
 * @return string 'STANDARD' | 'SPECIAL' | 'REJECTED'
 *
 * @throws InvalidArgumentException on invalid input
 */
function sort_package(int|float $width, int|float $height, int|float $length, int|float $mass): string
{
    foreach (compact('width', 'height', 'length', 'mass') as $name => $value) {
        if (!is_finite((float) $value)) {
            throw new InvalidArgumentException("'$name' must be a finite number.");
        }
        if ($value <= 0) {
            throw new InvalidArgumentException("'$name' must be a positive number, got: $value");
        }
    }

    $volume  = $width * $height * $length;
    $isBulky = $volume >= 1_000_000 || $width >= 150 || $height >= 150 || $length >= 150;
    $isHeavy = $mass >= 20;

    return match (true) {
        $isBulky && $isHeavy  => 'REJECTED',
        $isBulky || $isHeavy  => 'SPECIAL',
        default               => 'STANDARD',
    };
}


// ─────────────────────────────────────────────
//  Tests  (only runs via CLI: php sort.php test)
// ─────────────────────────────────────────────

if (PHP_SAPI === 'cli' && ($argv[1] ?? '') === 'test') {
    $passed = 0;
    $failed = 0;

    $check = function (string $label, string $expected, string $actual) use (&$passed, &$failed): void {
        if ($expected === $actual) {
            echo "  ✅ PASS: $label\n";
            $passed++;
        } else {
            echo "  ❌ FAIL: $label — expected $expected, got $actual\n";
            $failed++;
        }
    };

    $throws = function (string $label, callable $fn) use (&$passed, &$failed): void {
        try {
            $fn();
            echo "  ❌ FAIL: $label — expected InvalidArgumentException\n";
            $failed++;
        } catch (InvalidArgumentException $e) {
            echo "  ✅ PASS: $label — caught: {$e->getMessage()}\n";
            $passed++;
        }
    };

    echo "\n╔══════════════════════════════════════╗\n";
    echo "║    Package Sorter — Test Suite       ║\n";
    echo "╚══════════════════════════════════════╝\n";

    echo "\n[ STANDARD — not bulky, not heavy ]\n";
    $check('Small package (10×10×10, 1 kg)',          'STANDARD', sort_package(10, 10, 10, 1));
    $check('Compact and light (50×50×30, 5 kg)',       'STANDARD', sort_package(50, 50, 30, 5));
    $check('Volume just under threshold (99×100×100)', 'STANDARD', sort_package(99, 100, 100, 10));
    $check('Mass just under threshold (19.99 kg)',     'STANDARD', sort_package(10, 10, 10, 19.99));
    $check('Dimension just under 150 (149.9 cm)',      'STANDARD', sort_package(149.9, 10, 10, 10));

    echo "\n[ SPECIAL — bulky only ]\n";
    $check('Volume exactly 1,000,000 cm³',             'SPECIAL',  sort_package(100, 100, 100, 1));
    $check('Volume over 1,000,000 cm³ (200×10×10)',    'SPECIAL',  sort_package(200, 10, 10, 1));
    $check('Width exactly 150 cm',                     'SPECIAL',  sort_package(150, 10, 10, 1));
    $check('Height exactly 150 cm',                    'SPECIAL',  sort_package(10, 150, 10, 1));
    $check('Length exactly 150 cm',                    'SPECIAL',  sort_package(10, 10, 150, 1));

    echo "\n[ SPECIAL — heavy only ]\n";
    $check('Mass exactly 20 kg',                       'SPECIAL',  sort_package(10, 10, 10, 20));
    $check('Mass well over 20 kg (50 kg)',             'SPECIAL',  sort_package(10, 10, 10, 50));

    echo "\n[ REJECTED — bulky AND heavy ]\n";
    $check('Volume at threshold + heavy',              'REJECTED', sort_package(100, 100, 100, 20));
    $check('Dimension >= 150 + heavy',                 'REJECTED', sort_package(150, 10, 10, 25));
    $check('All extreme values (200×200×200, 100 kg)', 'REJECTED', sort_package(200, 200, 200, 100));

    echo "\n[ Edge cases ]\n";
    $check('Float dimensions (10.5×10.5×10.5, 1.5 kg)', 'STANDARD', sort_package(10.5, 10.5, 10.5, 1.5));
    $check('Mass exactly 20.0 (float)',                  'SPECIAL',  sort_package(10, 10, 10, 20.0));
    $check('Volume exactly 1,000,000.0 (float)',         'SPECIAL',  sort_package(100.0, 100.0, 100.0, 1.0));

    echo "\n[ Input validation — must throw ]\n";
    $throws('Zero width',              fn() => sort_package(0, 10, 10, 5));
    $throws('Negative height',         fn() => sort_package(10, -5, 10, 5));
    $throws('Zero mass',               fn() => sort_package(10, 10, 10, 0));
    $throws('Negative length',         fn() => sort_package(10, 10, -1, 5));
    $throws('INF width',               fn() => sort_package(INF, 10, 10, 5));
    $throws('NAN mass',                fn() => sort_package(10, 10, 10, NAN));

    $status = $failed === 0 ? '✅ All tests passed' : "❌ $failed test(s) failed";
    echo "\n══════════════════════════════════════════\n";
    echo "  $passed passed  |  $failed failed  —  $status\n";
    echo "══════════════════════════════════════════\n\n";

    exit($failed > 0 ? 1 : 0);
}
