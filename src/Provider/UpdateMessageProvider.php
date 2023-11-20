<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Provider;

use Context;
use Mollie;
use SimpleXMLElement;
use Tools;

if (!defined('_PS_VERSION_')) {
    exit;
}

class UpdateMessageProvider implements UpdateMessageProviderInterface
{
    const FILE_NAME = 'UpdateMessageProvider';

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
            return $this->module->l('Warning: Could not retrieve update xml file from github.', self::FILE_NAME);
        }

        /* @var SimpleXMLElement $tags */
        $tags = new SimpleXMLElement($updateXml);

        if (!$this->xmlFileFollowsExpectedFormat($tags)) {
            return $this->module->l('Warning: Update xml file from github follows an unexpected format.', self::FILE_NAME);
        }

        $title = '';
        foreach ($tags->entry as $entity) {
            if (strpos($entity->id, 'beta')) {
                continue;
            }

            $title = $entity->id;
            break;
        }
        $latestVersion = preg_replace('/[^0-9,.]/', '', Tools::substr($title, strrpos($title, '/')));

        if (version_compare($this->module->version, $latestVersion, '>=')) {
            return '';
        }

        Context::getContext()->smarty->assign(
            [
                'this_version' => $this->module->version,
                'release_version' => $latestVersion,
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
