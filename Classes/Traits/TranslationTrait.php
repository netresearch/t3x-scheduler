<?php

/**
 * This file is part of the package netresearch/nr-scheduler.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Netresearch\NrScheduler\Traits;

use TYPO3\CMS\Core\Localization\LanguageService;

/**
 * Translation trait.
 *
 * @author  Axel Seemann <axel.seemann@netresearch.de>
 * @author  Rico Sonntag <rico.sonntag@netresearch.de>
 * @license Netresearch https://www.netresearch.de
 * @link    https://www.netresearch.de
 */
trait TranslationTrait
{
    protected string $defaultLanguageFile = 'LLL:EXT:nr_scheduler/Resources/Private/Language/locallang.xlf';

    /**
     * Returns an instance of the language service.
     *
     * @return LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

    /**
     * Return the translated label.
     *
     * @param string   $name         Name of label or field
     * @param string   $languageFile Path of the language file
     * @param string[] $data         array with data to replace in the message
     *
     * @return string
     */
    protected function getLabel(string $name, string $languageFile = '', array $data = []): string
    {
        if ($languageFile === '') {
            $languageFile = $this->defaultLanguageFile;
        }

        $translation = $this->getLanguageService()->sL(
            $languageFile . ':' . $name
        );

        if ($translation === '') {
            return $name;
        }

        if ($data === []) {
            return $translation;
        }

        return str_replace(array_keys($data), $data, $translation);
    }
}
