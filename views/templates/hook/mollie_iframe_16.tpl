{*
 *
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
 *
 *}

<div id="mollie-iframe-container" style="display: none">
    <div class="mollie-iframe-container">
        <div class="container">
            <article class="alert alert-danger" role="alert" data-alert="danger"
                     style="display: none">
                <li class="js-mollie-alert"></li>
            </article>
        </div>
        <label class="mollie-information-label">{l s='Enter your card information' mod='mollie'}</label>
        <div class="form-group form-group-card-holder">
            <label class="mollie-label" for="card-holder">{l s='Card holder' mod='mollie'}</label>
            <div id="card-holder" class="mollie-input card-holder">
            </div>
        </div>
        <div class="inline-form-group">
            <div class="form-group form-group-card-number">
                <label class="mollie-label" for="card-number">{l s='Card number' mod='mollie'}</label>
                <div id="card-number" class="mollie-input card-number">
                </div>
            </div>
            <div class="form-group form-group-expiry-date">
                <div id="expiry-date" class="mollie-input expiry-date">
                </div>
            </div>
            <div class="form-group form-group-verification-code">
                <label class="mollie-label" for="verification-code">{l s='CVC' mod='mollie'}</label>
                <div id="verification-code"
                     class="mollie-input verification-code">
                </div>
            </div>
        </div>
        <div role="alert" class="error mollie-field-error">
            <label class="mollie-input-error"></label>
        </div>
        <div class="row">
            <div class="mollie-signature col-lg-9">
                <svg
                        xmlns="http://www.w3.org/2000/svg"
                        xmlns:xlink="http://www.w3.org/1999/xlink"
                        width="9"
                        height="12"
                >
                    <path
                            d="M 0 6 C 0 5.448 0.448 5 1 5 L 8 5 C 8.552 5 9 5.448 9 6 L 9 11 C 9 11.552 8.552 12 8 12 L 1 12 C 0.448 12 0 11.552 0 11 Z"
                            fill="#000"
                    ></path>
                    <g>
                        <defs>
                            <path
                                    d="M 1 3.5 C 1 1.567 2.567 0 4.5 0 L 4.5 0 C 6.433 0 8 1.567 8 3.5 L 8 5.5 C 8 7.433 6.433 9 4.5 9 L 4.5 9 C 2.567 9 1 7.433 1 5.5 Z"
                                    id="a1342z"
                            ></path>
                            <clipPath id="a1343z">
                                <use xlink:href="#a1342z"></use>
                            </clipPath>
                        </defs>
                        <use
                                xlink:href="#a1342z"
                                fill="transparent"
                                clip-path="url(#a1343z)"
                                stroke-width="2"
                                stroke="#000"
                        ></use>
                    </g>
                </svg>
                <span>{l s='Secure payments provided by' mod='mollie'}</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="41" height="12">
                    <g>
                        <path
                                d="M 17.341 3.919 C 15.09 3.919 13.262 5.733 13.262 7.959 C 13.262 10.185 15.094 12 17.341 12 C 19.588 12 21.42 10.185 21.42 7.959 C 21.42 5.733 19.593 3.919 17.341 3.919 Z M 17.341 10.089 C 16.158 10.089 15.196 9.135 15.196 7.964 C 15.196 6.792 16.158 5.838 17.341 5.838 C 18.524 5.838 19.487 6.792 19.487 7.964 C 19.487 9.135 18.524 10.089 17.341 10.089 Z"
                                fill="rgb(0,0,0)"
                        ></path>
                        <path
                                d="M 30.909 2.52 C 31.611 2.52 32.181 1.956 32.181 1.26 C 32.181 0.564 31.611 0 30.909 0 C 30.207 0 29.637 0.564 29.637 1.26 C 29.637 1.956 30.207 2.52 30.909 2.52 Z"
                                fill="rgb(0,0,0)"
                        ></path>
                        <path
                                d="M 9.014 3.923 C 8.908 3.915 8.806 3.91 8.7 3.91 C 7.717 3.91 6.784 4.309 6.114 5.015 C 5.444 4.314 4.516 3.91 3.54 3.91 C 1.59 3.91 0 5.481 0 7.413 L 0 11.836 L 1.908 11.836 L 1.908 7.468 C 1.908 6.666 2.574 5.926 3.358 5.847 C 3.413 5.842 3.468 5.838 3.519 5.838 C 4.401 5.838 5.122 6.552 5.126 7.426 L 5.126 11.836 L 7.076 11.836 L 7.076 7.46 C 7.076 6.662 7.738 5.922 8.526 5.842 C 8.582 5.838 8.637 5.834 8.688 5.834 C 9.569 5.834 10.295 6.544 10.299 7.413 L 10.299 11.836 L 12.249 11.836 L 12.249 7.468 C 12.249 6.582 11.918 5.728 11.321 5.07 C 10.723 4.406 9.904 3.999 9.014 3.923 Z"
                                fill="rgb(0,0,0)"
                        ></path>
                        <path
                                d="M 24.422 0.189 L 22.472 0.189 L 22.472 11.845 L 24.422 11.845 Z M 28.153 0.189 L 26.203 0.189 L 26.203 11.845 L 28.153 11.845 Z M 31.884 4.112 L 29.934 4.112 L 29.934 11.84 L 31.884 11.84 Z"
                                fill="rgb(0,0,0)"
                        ></path>
                        <path
                                d="M 41 7.779 C 41 6.754 40.597 5.788 39.868 5.053 C 39.134 4.318 38.168 3.91 37.137 3.91 L 37.087 3.91 C 36.018 3.923 35.009 4.343 34.254 5.095 C 33.5 5.847 33.076 6.842 33.063 7.905 C 33.05 8.988 33.47 10.013 34.246 10.79 C 35.022 11.567 36.048 11.996 37.142 11.996 L 37.146 11.996 C 38.579 11.996 39.923 11.236 40.657 10.013 L 40.75 9.858 L 39.139 9.072 L 39.058 9.203 C 38.655 9.862 37.956 10.253 37.18 10.253 C 36.188 10.253 35.331 9.597 35.068 8.665 L 41 8.665 Z M 37.061 5.666 C 37.952 5.666 38.749 6.246 39.028 7.069 L 35.098 7.069 C 35.374 6.246 36.171 5.666 37.061 5.666 Z"
                                fill="rgb(0,0,0)"
                        ></path>
                    </g>
                </svg>
            </div>
            <div class="col-lg-3">
                <form method="post" action="{$link->getModuleLink('mollie', 'payScreen', [], true)|escape:'html':'UTF-8'}">
                    <input type="hidden" name="mollieCardToken">
                    <button type="submit" class="btn btn-primary pull-right">{l s='Order' mod='mollie'}</button>
                </form>
            </div>
        </div>
    </div>
</div>

