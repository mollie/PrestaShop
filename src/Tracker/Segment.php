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
 *
 * @category   Mollie
 *
 * @see       https://www.mollie.nl
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Tracker;

use Context;
use Module;
use Mollie\Config\Config;

class Segment implements TrackerInterface
{
	/**
	 * @var string
	 */
	private $message = '';

	/**
	 * @var array
	 */
	private $options = [];

	/**
	 * @var Context
	 */
	private $context;

	/**
	 * Segment constructor.
	 *
	 * @param Context $context
	 */
	public function __construct(Context $context)
	{
		$this->context = $context;
		$this->init();
	}

	/**
	 * Init segment client with the api key
	 */
	private function init()
	{
		\Segment::init(Config::SEGMENT_KEY);
	}

	/**
	 * Track event on segment
	 *
	 * @return bool
	 *
	 * @throws \PrestaShopException
	 */
	public function track()
	{
		if (empty($this->message)) {
			throw new \PrestaShopException('Message cannot be empty. Need to set it with setMessage() method.');
		}

		// Dispatch track depending on context shop
		$this->dispatchTrack();

		return true;
	}

	private function segmentTrack($userId)
	{
		if (!$userId) {
			$userId = 'MissingUserId';
		}

		$userAgent = array_key_exists('HTTP_USER_AGENT', $_SERVER) === true ? $_SERVER['HTTP_USER_AGENT'] : '';
		$ip = array_key_exists('REMOTE_ADDR', $_SERVER) === true ? $_SERVER['REMOTE_ADDR'] : '';
		$referer = array_key_exists('HTTP_REFERER', $_SERVER) === true ? $_SERVER['HTTP_REFERER'] : '';
		$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$module = Module::getInstanceByName('mollie');

		\Segment::track([
			'userId' => $userId,
			'event' => $this->message,
			'channel' => 'browser',
			'context' => [
				'ip' => $ip,
				'userAgent' => $userAgent,
				'locale' => $this->context->language->iso_code,
				'page' => [
					'referrer' => $referer,
					'url' => $url,
				],
			],
			'properties' => array_merge([
				'module' => 'mollie',
				'version' => $module->version,
			], $this->options),
		]);

		\Segment::flush();
	}

	/**
	 * Handle tracking differently depending on the shop context
	 *
	 * @return mixed
	 */
	private function dispatchTrack()
	{
		$dictionary = [
			\Shop::CONTEXT_SHOP => function () {
				return $this->trackShop();
			},
			\Shop::CONTEXT_GROUP => function () {
				return $this->trackShopGroup();
			},
			\Shop::CONTEXT_ALL => function () {
				return $this->trackAllShops();
			},
		];

		return call_user_func($dictionary[$this->context->shop->getContext()]);
	}

	/**
	 * Send track segment only for the current shop
	 */
	private function trackShop()
	{
		$userId = $this->context->shop->domain;

		$this->segmentTrack($userId);
	}

	/**
	 * Send track segment for each shop in the current shop group
	 */
	private function trackShopGroup()
	{
		$shops = $this->context->shop->getShops(true, $this->context->shop->getContextShopGroupID());
		foreach ($shops as $shop) {
			$this->segmentTrack($shop['domain']);
		}
	}

	/**
	 * Send track segment for all shops
	 */
	private function trackAllShops()
	{
		$shops = $this->context->shop->getShops();
		foreach ($shops as $shop) {
			$this->segmentTrack($shop['domain']);
		}
	}

	/**
	 * @return string
	 */
	public function getMessage()
	{
		return $this->message;
	}

	/**
	 * @param string $message
	 */
	public function setMessage($message)
	{
		$this->message = $message;
	}

	/**
	 * @return array
	 */
	public function getOptions()
	{
		return $this->options;
	}

	/**
	 * @param array $options
	 */
	public function setOptions($options)
	{
		$this->options = $options;
	}
}
