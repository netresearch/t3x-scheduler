<?php

/**
 * This file is part of the package netresearch/nr-scheduler.
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 * SPDX-FileCopyrightText: Netresearch DTT GmbH
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

$EM_CONF['nr_scheduler'] = [
    'title'          => 'Scheduler Extensions',
    'description'    => 'Extends the TYPO3 scheduler extension with additional functions.',
    'category'       => 'plugin',
    'author'         => 'Rico Sonntag',
    'author_email'   => 'rico.sonntag@netresearch.de',
    'author_company' => 'Netresearch DTT GmbH',
    'state'          => 'stable',
    'version'        => '2.0.0',
    'constraints'    => [
        'depends' => [
            'php'   => '8.2.0-8.99.99',
            'typo3' => '13.4.0-14.3.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];
