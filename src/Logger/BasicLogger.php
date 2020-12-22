<?php

namespace Mollie\Logger;

use Exception;
use PrestaShopLogger;

class BasicLogger implements ModuleLoggerInterface
{
	public function logException(Exception $exception, $message, $severity)
	{
		PrestaShopLogger::addLog(
			$message,
			$severity,
			$exception->getCode(),
			'Mollie',
			null,
			true
		);
	}
}
