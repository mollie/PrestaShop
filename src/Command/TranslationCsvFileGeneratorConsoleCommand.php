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

class TranslationCsvFileGeneratorConsoleCommand extends Command
{

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
            ->setName('mollie:generate-translation-csv')
            ->setAliases(['m:g:t:c'])
            ->setDescription('Generate translation csv');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        include_once $this->module->getLocalPath() . '/translations/en.php';

        $csvHeader = [
            'ID',
            'EN',
            'NL',
            'DE',
            'FR',
        ];
        $translations = $GLOBALS["_MODULE"];
        try {
            $fp = fopen('translation.csv', 'w');
            fputcsv($fp, $csvHeader);
            foreach ($translations as $id => $text) {
                $field = array_map("utf8_decode", [$id, $text]);
                fputcsv($fp, $field);
            }
            fclose($fp);
        } catch (\Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");

            return 0;
        }
        $output->writeln('<info>Translation export to CSV finished</info>');

        return 0;
    }
}
