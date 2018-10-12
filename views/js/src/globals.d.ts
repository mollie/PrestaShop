/**
 * Copyright (c) 2012-2018, Mollie B.V.
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
 */
export {}
declare global {
  interface ITranslations {
    [key: string]: string,
  }

  interface IBankImage {
    size1x: string,
    size2x: string,
  }

  interface IBank {
    id: string,
    name: string,
    image: IBankImage,
    href: string,
  }

  interface IBanks {
    [key: string]: IBank,
  }

  interface IMollieAmount {
    value: string,
    currency: string,
  }

  interface IBankOptions {
    [key: string]: any,
  }

  interface IMollieOrderConfig {
    [key: string]: any,
  }

  interface ICurrencies {
    [iso: string]: ICurrency,
  }

  interface ICurrency {
    format: number,
    sign: string,
    blank: string,
  }

  interface IMollieApiOrder {
    resource: string,
    id: string,
    mode: string,
    amount: IMollieAmount,
    amountCaptured: IMollieAmount,
    status: string,
    method: string,
    metadata: any,
    isCancelable?: boolean,
    createdAt: string,
    lines: Array<IMollieOrderLine>,
    refunds: Array<IMollieApiRefund>,
  }

  interface IMollieOrderLine {
    resource: string,
    id: string,
    orderId: string,
    name: string,
    sku?: string,
    type: string,
    status: string,
    isCancelable: boolean,
    quantity: number,
    quantityShipped: number,
    amountShipped: IMollieAmount,
    quantityRefunded: number,
    amountRefunded: IMollieAmount,
    quantityCanceled: number,
    amountCanceled: IMollieAmount,
    shippableQuantity: number,
    refundableQuantity: number,
    cancelableQuantity: number,
    unitPrice: IMollieAmount,
    vatRate: string,
    vatAmount: IMollieAmount,
    totalAmount: IMollieAmount,
    createdAt: string,
  }

  interface IMollieApiPayment {
    resource: string,
    id: string,
    mode: string,
    amount: IMollieAmount,
    settlementAmount: IMollieAmount,
    amountRefunded: IMollieAmount,
    amountRemaining: IMollieAmount,
    description: string,
    method: string,
    status: string,
    createdAt: string,
    paidAt: string,
    canceledAt: string,
    expiresAt: string,
    failedAt: string,
    metaData: any,
    isCancelable: boolean,
    refunds: Array<IMollieApiRefund>,
  }

  interface IMollieApiRefund {
    resource: string,
    id: string,
    amount: IMollieAmount,
    createdAt: string,
    description: string,
    paymentId: string,
    orderId: string,
    lines: string,
    settlementAmount: string,
    status: string,
  }
}
