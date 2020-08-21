<?php

use Mollie\Service\UpgradeNoticeService;
use PHPUnit\Framework\TestCase;

class UpgradeNoticeServiceTest extends TestCase
{

    /**
     * @dataProvider dataProvider
     * @param $currentTimeStamp
     * @param $noticeCloseTimeStamp
     * @param $result
     */
    public function testIsUpgradeNoticeClosed($currentTimeStamp, $noticeCloseTimeStamp, $result)
    {
        $upgradeNoticeService = new UpgradeNoticeService();

        $isClosed = $upgradeNoticeService->isUpgradeNoticeClosed($currentTimeStamp, $noticeCloseTimeStamp);
        $this->assertEquals($result, $isClosed);
    }

    public function dataProvider()
    {
        return [
            'case1' =>
                [
                    'currentTimeStamp' => strtotime('2020-05-01 00:00:00'),
                    'noticeCloseTimeStamp' =>strtotime('-29 days', strtotime('2020-05-01 00:00:00')),
                    'result' => false
                ],
            'case2' =>
                [
                    'currentTimeStamp' => strtotime('2020-05-01 00:00:00'),
                    'noticeCloseTimeStamp' =>strtotime('-28 days', strtotime('2020-05-01 00:00:00')),
                    'result' => true
                ],
            'case3' =>
                [
                    'currentTimeStamp' => strtotime('2020-05-01 00:00:00'),
                    'noticeCloseTimeStamp' =>strtotime('-27 days', strtotime('2020-05-01 00:00:00')),
                    'result' => true
                ]
        ];
    }

}
