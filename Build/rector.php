<?php

/*
 * This file is part of the package netresearch/nr-scheduler.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Ssch\TYPO3Rector\Set\Typo3LevelSetList;

$configure = require_once __DIR__ . '/../.build/vendor/netresearch/typo3-ci-workflows/config/rector/rector.php';

return static function (RectorConfig $rectorConfig) use ($configure): void {
    // Shared org base config: paths, code-quality sets, rule skips,
    // and the package's ergebnis-free phpstan-rector.neon.
    $configure($rectorConfig, __DIR__ . '/..');

    $rectorConfig->disableParallel();

    // This repo has always scanned Tests/ as well; the shared default path
    // list does not include it and paths() replaces, so restate the full list.
    $rectorConfig->paths(array_merge(
        [
            __DIR__ . '/../Classes',
            __DIR__ . '/../Configuration',
            __DIR__ . '/../Resources',
            __DIR__ . '/../Tests',
        ],
        glob(__DIR__ . '/../ext_*.php') ?: [],
    ));

    // TYPO3 migration level: v13, the lowest still-supported major
    // (typo3-ci-workflows#155 fleet convention) — UP_TO_TYPO3_14 belongs to
    // the release that drops ^13.4 support.
    $rectorConfig->sets([
        Typo3LevelSetList::UP_TO_TYPO3_13,
    ]);

    $rectorConfig->skip([
        __DIR__ . '/../ext_*.sql',
    ]);
};
