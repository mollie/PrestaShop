<?php

use Mollie\Exception\OrderCreationException;
use Mollie\Exception\RetryOverException;
use Mollie\Handler\RetryHandler;
use PHPUnit\Framework\TestCase;

class RetryHandlerTest extends TestCase
{
    /**
     * @dataProvider retryFunctionDataProvider
     */
    public function testRetry($function, array $options, $expectedResult)
    {
        $retryHandler = new RetryHandler();
        $result = $retryHandler->retry(
            $function,
            $options
        );

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @dataProvider retryFunctionExceptionDataProvider
     */
    public function testRetryException($function, array $options, $expectedException)
    {
        $this->expectException($expectedException);
        $retryHandler = new RetryHandler();
        $retryHandler->retry(
            $function,
            $options
        );
    }

    public function retryFunctionDataProvider()
    {
        $testId = 1;

        return [
            'test basic case' => [
                'function' => function () {
                    return true;
                },
                'options' => [
                    'max' => 3,
                    'wait' => 1,
                    'accepted_exception' => OrderCreationException::class,
                ],
                'result' => true,
            ],
            'test with object' => [
                'function' => function () {
                    $order = new Order();
                    if ($order) {
                        return $order;
                    }

                    return false;
                },
                'options' => [
                    'max' => 3,
                    'wait' => 1,
                    'accepted_exception' => OrderCreationException::class,
                ],
                'result' => new Order(),
            ],
            'test with param' => [
                'function' => function () use ($testId) {
                    $order = new Order($testId);
                    if ($order) {
                        return $order;
                    }

                    return false;
                },
                'options' => [
                    'max' => 3,
                    'wait' => 1,
                    'accepted_exception' => OrderCreationException::class,
                ],
                'result' => new Order($testId),
            ],
        ];
    }

    public function retryFunctionExceptionDataProvider()
    {
        return [
            'retry with expected exception' => [
                'function' => function () {
                    throw new OrderCreationException('unit test', OrderCreationException::ORDER_IS_NOT_CREATED);
                },
                'options' => [
                    'max' => 3,
                    'wait' => 1,
                    'accepted_exception' => OrderCreationException::class,
                ],
                'expectedException' => RetryOverException::class,
            ],
            'retry without expected exception' => [
                'function' => function () {
                    throw new OrderCreationException('unit test', OrderCreationException::ORDER_IS_NOT_CREATED);
                },
                'options' => [
                    'max' => 3,
                    'wait' => 1,
                ],
                'expectedException' => OrderCreationException::class,
            ],
        ];
    }
}
