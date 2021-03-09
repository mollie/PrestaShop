<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 *
 * @see        https://github.com/mollie/PrestaShop
 *
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
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
		$translations = $GLOBALS['_MODULE'];
		try {
			$fp = fopen('translation.csv', 'w');
			$fields = [];
			foreach ($translations as $id => $text) {
				$field = array_map('utf8_decode', [$id, $text]);
				$fields[$field[0]] = $field;
			}
		} catch (\Exception $e) {
			$output->writeln("<error>{$e->getMessage()}</error>");

			return 0;
		}

		$translationFiles = [
			2 => 'nl',
			3 => 'de',
			4 => 'fr',
		];
		fputcsv($fp, $csvHeader);
		foreach ($translationFiles as $position => $file) {
			include_once $this->module->getLocalPath() . "translations/{$file}.php";
			$translations = $GLOBALS['_MODULE'];

			foreach ($translations as $id => $text) {
				$fields[$id][$position] = $text;
			}
		}
		foreach ($fields as $field) {
			if (!isset($field[0])) {
				continue;
			}
			fputcsv($fp, $field, ';', chr(127));
		}

		fclose($fp);
		$output->writeln('<info>Translation export to CSV finished</info>');

		return 0;
	}
}
