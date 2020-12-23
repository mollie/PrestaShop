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

namespace Mollie\Adapter;

use Context;

class LegacyContext
{
	public function getContext()
	{
		return Context::getContext();
	}

	public function getCookieValue($key)
	{
		return $this->getContext()->cookie->__get($key);
	}

	public function getCurrencyIsoCode()
	{
		return $this->getContext()->currency->iso_code;
	}

	public function getCurrencySign()
	{
		return $this->getContext()->currency->sign;
	}

	public function getCountryIsoCode()
	{
		return $this->getContext()->country->iso_code;
	}

	public function getCountryId()
	{
		return $this->getContext()->country->id;
	}

	public function getCurrencyId()
	{
		return $this->getContext()->currency->id;
	}

	public function getCart()
	{
		return $this->getContext()->cart;
	}

	public function getMobileDetect()
	{
		return $this->getContext()->getMobileDetect();
	}

	public function getLink()
	{
		return $this->getContext()->link;
	}

	public function getSmarty()
	{
		return $this->getContext()->smarty;
	}
}
