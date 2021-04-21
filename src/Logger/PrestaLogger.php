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

namespace Mollie\Logger;

use Mollie\Exception\NotImplementedException;
use Psr\Log\LoggerInterface;

class PrestaLogger implements LoggerInterface
{
	public function emergency($message, array $context = [])
	{
		throw new NotImplementedException('not implemented method');
	}

	public function alert($message, array $context = [])
	{
		throw new NotImplementedException('not implemented method');
	}

	public function critical($message, array $context = [])
	{
		throw new NotImplementedException('not implemented method');
	}

	public function error($message, array $context = [])
	{
		$uniqueMessage = sprintf('Log ID (%s) | %s', uniqid(), $message);

		\PrestaShopLogger::addLog(
			$this->getMessageWithContext($uniqueMessage, $context),
			2
		);
	}

	public function warning($message, array $context = [])
	{
		throw new NotImplementedException('not implemented method');
	}

	public function notice($message, array $context = [])
	{
		throw new NotImplementedException('not implemented method');
	}

	public function info($message, array $context = [])
	{
		$uniqueMessage = sprintf('Log ID (%s) | %s', uniqid(), $message);

		\PrestaShopLogger::addLog(
			$this->getMessageWithContext($uniqueMessage, $context)
		);
	}

	public function debug($message, array $context = [])
	{
		throw new NotImplementedException('not implemented method');
	}

	public function log($level, $message, array $context = [])
	{
		throw new NotImplementedException('not implemented method');
	}

	private function getMessageWithContext($message, array $context = [])
	{
		$content = json_encode($context);

		return "{$message} . context: {$content}";
	}
}
