{**
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
* @category   Mollie
* @package    Mollie
* @link       https://www.mollie.nl
*}
<tr style="background-color: {$color|escape:'htmlall':'UTF-8'}">
    <td style="padding: 0.6em 0.4em;width: 15%;">{$product.reference|escape:'htmlall':'UTF-8'}</td>
    <td style="padding: 0.6em 0.4em;width: 30%;"><strong>{$product.name|escape:'htmlall':'UTF-8'}{$product.attributes|escape:'htmlall':'UTF-8'} - {$customizationText|escape:'htmlall':'UTF-8'}</strong></td>
    <td style="padding: 0.6em 0.4em; width: 20%;">{$price|escape:'htmlall':'UTF-8'}</td>
    <td style="padding: 0.6em 0.4em; width: 15%;">{$customizationQuantity|escape:'htmlall':'UTF-8'}</td>
    <td style="padding: 0.6em 0.4em; width: 20%;">{$fullPrice|escape:'htmlall':'UTF-8'}</td>
</tr>