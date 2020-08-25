<?php
/**
 * Copyright (c) 2012-2020, Mollie B.V.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
 * DAMAGE.
 *
 * @author     Mollie B.V. <info@mollie.nl>
 * @copyright  Mollie B.V.
 * @license    Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 * @category   Mollie
 * @package    Mollie
 * @link       https://www.mollie.nl
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Command;

use Mollie;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UploadTranslationsFromCsvFileConsoleCommand extends Command
{
    const CSV_POSITION_ID = 0;
    const CSV_POSITION_EN = 1;
    const CSV_POSITION_NL = 2;
    const CSV_POSITION_DE = 3;
    const CSV_POSITION_FR = 4;

    /**
     * @var Mollie
     */
    private $module;

    public function __construct(Mollie $module)
    {
        parent::__construct();
        $this->module = $module;
    }

    protected function configure()
    {
        $this
            ->setName('mollie:upload-translation-csv')
            ->setAliases(['m:g:t:c'])
            ->setDescription('Upload translation csv');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        include_once $this->module->getLocalPath() . '/translations/en.php';

        $csvHeader = "<?php \n\nglobal \$_MODULE;\n\$_MODULE = array();\n";
        try {
            $handle = fopen("translation.csv", "r");
            if ($handle) {
                $en = 'en.php';
                $nl = 'nl.php';
                $de = 'de.php';
                $fr = 'fr.php';
                file_put_contents($en, $csvHeader);
                file_put_contents($nl, $csvHeader);
                file_put_contents($de, $csvHeader);
                file_put_contents($fr, $csvHeader);

                while (($line = fgets($handle)) !== false) {
                    $line = preg_replace("/\r|\n/", "", $line);
                    $values = explode(',', $line);
                    if ($values[self::CSV_POSITION_ID] === 'ID' ||
                        $values[self::CSV_POSITION_ID] === ''
                    ) {
                        continue;
                    }
                    $this->updateTranslation($en, $values, self::CSV_POSITION_EN);
                    $this->updateTranslation($nl, $values, self::CSV_POSITION_NL);
                    $this->updateTranslation($de, $values, self::CSV_POSITION_DE);
                    $this->updateTranslation($fr, $values, self::CSV_POSITION_FR);
                }
            } else {
                $output->writeln("<error>Couldn't find csv file</error>");
            }
        } catch (\Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");

            return 0;
        }
        $output->writeln('<info>Product synchronization finished</info>');

        return 0;
    }

    private function updateTranslation($file, $values, $position)
    {
        if (!isset($values[$position]) || $values[$position] === '') {
            return;
        }

        $translatedText = str_replace("'", "\'", $values[$position]);

        $translationLine =
            '$_MODULE[\'' . $values[self::CSV_POSITION_ID] . '\'] = \'' . $translatedText . "';\n";

        file_put_contents($file, $translationLine, FILE_APPEND);
    }
}
