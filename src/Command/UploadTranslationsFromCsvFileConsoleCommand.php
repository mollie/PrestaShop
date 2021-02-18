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
			$handle = fopen('translation.csv', 'r');
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
					$line = preg_replace("/\r|\n/", '', $line);
					$values = explode(';', $line);
					if ('ID' === $values[self::CSV_POSITION_ID] ||
						'' === $values[self::CSV_POSITION_ID]
					) {
						continue;
					}
					if ($values['0'] === '<{mollie}prestashop>smarty_error_9d1fbbe0d150b89f068ba72a20366659') {
						$a = 1;
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
		if (!isset($values[$position]) || '' === $values[$position]) {
			return;
		}

		$translatedText = str_replace("'", "\'", $values[$position]);

		$translationLine =
			'$_MODULE[\'' . $values[self::CSV_POSITION_ID] . '\'] = \'' . $translatedText . "';\n";

		file_put_contents($file, $translationLine, FILE_APPEND);
	}
}
