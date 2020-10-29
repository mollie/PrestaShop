<?php

namespace Utility;

use Mollie\Utility\RefundUtility;
use PHPUnit\Framework\TestCase;

class RefundUtilityTest extends TestCase
{
    /**
     * @dataProvider getRefundLinesDataProvider
     *
     * @param $lines
     * @param $result
     */
    public function testGetRefundLines($lines, $result)
    {
        $refunds = RefundUtility::getRefundLines($lines);

        self::assertEquals($result, $refunds);
    }

    /**
     * @dataProvider getIsOrderLinesRefundPossibleDataProvider
     *
     * @param $lines
     * @param $availableRefund
     * @param $result
     */
    public function testIsOrderLinesRefundPossible($lines, $availableRefund, $result)
    {
        $refunds = RefundUtility::isOrderLinesRefundPossible($lines, $availableRefund);

        self::assertEquals($result, $refunds);
    }

    public function getRefundLinesDataProvider()
    {
        return [
            'normal refund' => [
                'lines' => [
                    0 => [
                        'id' => 'odl_tnw1ay',
                        'orderId' => 'ord_7v67h8',
                        'quantity' => 1,
                        'totalAmount' => [
                            'value' => '100.00',
                            'currency' => 'EUR'
                        ],
                    ]
                ],
                'result' => [
                    'lines' => [
                        0 => [
                            'id' => 'odl_tnw1ay',
                            'quantity' => 1,
                            'amount' => [
                                'value' => '100.00',
                                'currency' => 'EUR'
                            ]
                        ]
                    ]
                ]
            ],
            'voucher refund' => [
                'lines' => [
                    0 => [
                        'id' => 'odl_tnw1ay',
                        'orderId' => 'ord_7v67h8',
                        'quantity' => 1,
                        'totalAmount' => [
                            'value' => '100.00',
                            'currency' => 'EUR'
                        ],
                        'amountRefunded' => [
                            'value' => '0.00',
                            'currency' => 'EUR'
                        ]
                    ]
                ],
                'result' => [
                    'lines' => [
                        0 => [
                            'id' => 'odl_tnw1ay',
                            'quantity' => 1,
                            'amount' => [
                                'value' => '100.00',
                                'currency' => 'EUR'
                            ]
                        ]
                    ]
                ]
            ],
            'voucher refund 2 products' => [
                'lines' => [
                    0 => [
                        'id' => 'odl_tnw1ay',
                        'orderId' => 'ord_7v67h8',
                        'quantity' => 1,
                        'totalAmount' => [
                            'value' => '100.00',
                            'currency' => 'EUR'
                        ],
                    ],
                    1 => [
                        'id' => 'odl_tnw1aa',
                        'orderId' => 'ord_7v67h8',
                        'quantity' => 1,
                        'totalAmount' => [
                            'value' => '11.00',
                            'currency' => 'EUR'
                        ],
                    ]
                ],
                'result' => [
                    'lines' => [
                        0 => [
                            'id' => 'odl_tnw1ay',
                            'quantity' => 1,
                            'amount' => [
                                'value' => '100.00',
                                'currency' => 'EUR'
                            ]
                        ],
                        1 => [
                            'id' => 'odl_tnw1aa',
                            'quantity' => 1,
                            'amount' => [
                                'value' => '11.00',
                                'currency' => 'EUR'
                            ]
                        ]
                    ]
                ]
            ],
        ];
    }

    public function getIsOrderLinesRefundPossibleDataProvider()
    {
        return [
            'normal refund' => [
                'lines' => [
                    0 => [
                        'id' => 'odl_tnw1ay',
                        'orderId' => 'ord_7v67h8',
                        'quantity' => 1,
                        'totalAmount' => [
                            'value' => '100.00',
                            'currency' => 'EUR'
                        ],
                        'amountRefunded' => [
                            'value' => '0.00',
                            'currency' => 'EUR'
                        ]
                    ]
                ],
                'remainingAmount' => [
                    'value' => '100.00',
                    'currency' => 'EUR'
                ],
                'result' => true
            ],
            'voucher refund' => [
                'lines' => [
                    0 => [
                        'id' => 'odl_tnw1ay',
                        'orderId' => 'ord_7v67h8',
                        'quantity' => 1,
                        'totalAmount' => [
                            'value' => '100.00',
                            'currency' => 'EUR'
                        ],
                        'amountRefunded' => [
                            'value' => '0.00',
                            'currency' => 'EUR'
                        ]
                    ]
                ],
                'remainingAmount' => [
                    'value' => '90.00',
                    'currency' => 'EUR'
                ],
                'result' => false
            ],
            'voucher refund 2 products' => [
                'lines' => [
                    0 => [
                        'id' => 'odl_tnw1ay',
                        'orderId' => 'ord_7v67h8',
                        'quantity' => 1,
                        'totalAmount' => [
                            'value' => '100.00',
                            'currency' => 'EUR'
                        ],
                        'amountRefunded' => [
                            'value' => '0.00',
                            'currency' => 'EUR'
                        ]
                    ],
                    1 => [
                        'id' => 'odl_tnw1aa',
                        'orderId' => 'ord_7v67h8',
                        'quantity' => 1,
                        'totalAmount' => [
                            'value' => '11.00',
                            'currency' => 'EUR'
                        ],
                        'amountRefunded' => [
                            'value' => '0.00',
                            'currency' => 'EUR'
                        ]
                    ]
                ],
                'remainingAmount' => [
                    'value' => '99.00',
                    'currency' => 'EUR'
                ],
                'result' => false
            ],
            'voucher refund 2 products with quantity' => [
                'lines' => [
                    0 => [
                        'id' => 'odl_tnw1ay',
                        'orderId' => 'ord_7v67h8',
                        'quantity' => 5,
                        'totalAmount' => [
                            'value' => '500.00',
                            'currency' => 'EUR'
                        ],
                        'amountRefunded' => [
                            'value' => '0.00',
                            'currency' => 'EUR'
                        ]
                    ],
                    1 => [
                        'id' => 'odl_tnw1aa',
                        'orderId' => 'ord_7v67h8',
                        'quantity' => 3,
                        'totalAmount' => [
                            'value' => '33.00',
                            'currency' => 'EUR'
                        ],
                        'amountRefunded' => [
                            'value' => '0.00',
                            'currency' => 'EUR'
                        ]
                    ]
                ],
                'remainingAmount' => [
                    'value' => '500.00',
                    'currency' => 'EUR'
                ],
                'result' => false
            ]
        ];
    }

}
