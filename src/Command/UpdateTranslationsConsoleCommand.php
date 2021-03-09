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

class UpdateTranslationsConsoleCommand extends Command
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
			->setName('mollie:update-translations')
			->setAliases(['m:u:t'])
			->setDescription('Update translation csv');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$missingTranslations = fopen('missing-translations.csv', 'r');
		$data = fgetcsv($missingTranslations);
		$missingTranslationsArray = [];

		while (($missingTranslation = fgetcsv($missingTranslations, 0, ';')) !== false) {
			$missingTranslationsArray[$missingTranslation[0]] = $missingTranslation;
		}

		try {
			$translations = fopen('translation.csv', 'r');
			$translationsArray = [];
			while (($translation = fgetcsv($translations, 0, ';')) !== false) {
				$translationsArray[$translation[0]] = $translation;
			}
		} catch (\Exception $e) {
			$output->writeln("<error>{$e->getMessage()}</error>");

			return 0;
		}

		foreach ($missingTranslationsArray as $position => $value) {
			foreach ($translationsArray as $key => $item) {
				if ($item[1] === $position) {
					$translationsArray[$key][2] = $value[1];
					$translationsArray[$key][3] = $value[2];
					$translationsArray[$key][4] = $value[3];
				}
			}
		}

		$translations = fopen('translation.csv', 'w');
		foreach ($translationsArray as $value) {
			fputcsv($translations, $value, ';', chr(127));
		}
		fclose($translations);
		$output->writeln('<info>Translation export to CSV finished</info>');

		return 0;
	}
}
