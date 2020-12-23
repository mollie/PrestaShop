<?php

namespace Mollie\Provider;

use Context;
use Mollie;
use Mollie\Utility\TagsUtility;
use SimpleXMLElement;
use Tools;

class UpdateMessageProvider implements UpdateMessageProviderInterface
{
	/**
	 * @var Mollie
	 */
	private $module;

	public function __construct(Mollie $module)
	{
		$this->module = $module;
	}

	/**
	 * @param string $url
	 * @param mixed $addons
	 *
	 * @return string
	 *
	 * @throws \SmartyException
	 */
	public function getUpdateMessageFromOutsideUrl($url, $addons)
	{
		$updateXml = $this->getUpdateXML($url, $addons);

		if (!$updateXml) {
			return $this->module->l('Warning: Could not retrieve update xml file from github.');
		}

		/* @var SimpleXMLElement $tags */
		$tags = new SimpleXMLElement($updateXml);

		if (!$this->xmlFileFollowsExpectedFormat($tags)) {
			return $this->module->l('Warning: Update xml file from github follows an unexpected format.', $this->module->name);
		}

		$title = $tags->entry[0]->id;
		$latestVersion = preg_replace('/[^0-9,.]/', '', Tools::substr($title, strrpos($title, '/')));

		if (version_compare($this->module->version, $latestVersion, '>=')) {
			return '';
		}

		Context::getContext()->smarty->assign(
			[
				'this_version' => $this->module->version,
				'release_version' => $latestVersion,
				'github_url' => TagsUtility::ppTags(
					sprintf(
						$this->module->l('You are currently using version \'%s\' of this plugin. The latest version is \'%s\'. We advice you to [1]update[/1] to enjoy the latest features. '),
						$this->module->version,
						$latestVersion
					),
					[
						$this->module->display($this->module->getPathUri(), 'views/templates/admin/github_redirect.tpl'),
					]
				),
			]
		);

		return Context::getContext()->smarty->fetch(_PS_MODULE_DIR_ . 'mollie/views/templates/admin/new_release.tpl');
	}

	/**
	 * @param string $url
	 * @param mixed $addons
	 *
	 * @return bool|string
	 */
	private function getUpdateXML($url, $addons)
	{
		if ($addons) {
			return '';
		}

		return @Tools::file_get_contents($url . '/releases.atom');
	}

	/**
	 * @param SimpleXMLElement $tags
	 *
	 * @return bool
	 */
	private function xmlFileFollowsExpectedFormat($tags)
	{
		if (empty($tags)) {
			return false;
		}

		if (!isset($tags->entry)) {
			return false;
		}

		if (!isset($tags->entry[0])) {
			return false;
		}

		if (!isset($tags->entry[0]->id)) {
			return false;
		}

		return true;
	}
}
