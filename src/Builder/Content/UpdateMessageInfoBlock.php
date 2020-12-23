<?php

namespace Mollie\Builder\Content;

use Mollie;
use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Builder\TemplateBuilderInterface;
use Mollie\Provider\UpdateMessageProviderInterface;
use Mollie\Service\UpgradeNoticeService;

class UpdateMessageInfoBlock implements TemplateBuilderInterface
{
	/**
	 * @var Mollie
	 */
	private $module;

	/**
	 * @var UpgradeNoticeService
	 */
	private $upgradeNoticeService;

	/**
	 * @var ConfigurationAdapter
	 */
	private $configurationAdapter;

	/**
	 * @var mixed
	 */
	private $addons;
	/**
	 * @var UpdateMessageProviderInterface
	 */
	private $updateMessageProvider;

	public function __construct(
		Mollie $module,
		UpgradeNoticeService $upgradeNoticeService,
		ConfigurationAdapter $configurationAdapter,
		UpdateMessageProviderInterface $updateMessageProvider
	) {
		$this->module = $module;
		$this->upgradeNoticeService = $upgradeNoticeService;
		$this->configurationAdapter = $configurationAdapter;
		$this->updateMessageProvider = $updateMessageProvider;
	}

	/**
	 * @param mixed $addons
	 *
	 * @return $this
	 */
	public function setAddons($addons)
	{
		$this->addons = $addons;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function buildParams()
	{
		$updateMessage = '';

		if ($this->canBeUpdatedFromGithub()) {
			$updateMessage = defined('_TB_VERSION_')
				? $this->updateMessageProvider->getUpdateMessageFromOutsideUrl('https://github.com/mollie/thirtybees', $this->addons)
				: $this->updateMessageProvider->getUpdateMessageFromOutsideUrl('https://github.com/mollie/PrestaShop', $this->addons);
		}

		return [
			'updateMessage' => $updateMessage,
		];
	}

	private function canBeUpdatedFromGithub()
	{
		if ($this->addons) {
			return false;
		}

		if ($this->upgradeNoticeService->isUpgradeNoticeClosed(
			Mollie\Utility\TimeUtility::getNowTs(),
			(int) $this->configurationAdapter->get(Mollie\Config\Config::MOLLIE_MODULE_UPGRADE_NOTICE_CLOSE_DATE)
		)) {
			return false;
		}

		return true;
	}
}
