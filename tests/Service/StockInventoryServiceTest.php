<?php

namespace App\Tests\Service;

use App\Entity\Station;
use App\Repository\StationInventoryRepository;
use App\Repository\StationRepository;
use App\Service\StockInventoryService;
use App\Tests\BaseTestCase;
use Symfony\Component\HttpFoundation\Request;

class StockInventoryServiceTest extends BaseTestCase
{
    public function setUp() :void
    {
        parent::setUp();
        $this->stockInventoryServiceObj = new StockInventoryService($this->entityManager);
        $this->stockInventoryServiceMockObj= $this->getMockBuilder(StockInventoryService::class)
            ->setConstructorArgs([
                    $this->entityManager,
                ]);
    }

    /**
     * @param Request $request
     * @param $stationId
     * @param Station $station
     * @dataProvider getStationStockDataProvider
     * @return void
     */
    public function testGetStationStock(Request $request, $stationId, Station $station)
    {
        $stockInventoryServiceMockObj = $this->stockInventoryServiceMockObj->setMethods([
            'getStationStockBy'
        ])->getMock();
        $stationRepo = $this->createMock(StationRepository::class);
        $stationRepo->expects($this->any())->method('findOneBy')->with(['id' => $stationId, 'status' => Station::STATUS_ACTIVE])->willReturn($station);
        $this->entityManager->expects($this->any())->method('getRepository')->willReturn($stationRepo);
        $response = $stockInventoryServiceMockObj->getStationStock($stationId, $request);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('stationId', $response['data']);
        $this->assertArrayHasKey('stationName', $response['data']);
        $this->assertArrayHasKey('stationStock', $response['data']);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testGetStationStockWithInvalidTimelineException()
    {
        $dates = [
            'from' => '2022-05-21' ,
            'to' => '2022-05-20'
        ];
        $request = new Request($dates);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Selected timeline is incorrect.');
        $this->stockInventoryServiceObj->getStationStock(1, $request);
    }

    /**
     * @param Request $request
     * @param $stationId
     * @param Station $station
     * @return void
     * @dataProvider getStationStockDataProvider
     * @throws \ReflectionException
     */
    public function testGetStationStockWithStationNotFoundException(Request $request, $stationId, Station $station)
    {
        $stationRepo = $this->createMock(StationRepository::class);
        $stationRepo->expects($this->any())->method('findOneBy')->with(['id' => $stationId, 'status' => Station::STATUS_ACTIVE])->willReturn(null);
        $this->entityManager->expects($this->any())->method('getRepository')->willReturn($stationRepo);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Station not found');
        $this->stockInventoryServiceObj->getStationStock(1, $request);
    }

    /**
     * @param Station $station
     * @param $fromDate
     * @param $toDate
     * @param $expected
     * @dataProvider getStationStockByDataProvider
     * @return void
     * @throws \ReflectionException
     */
    public function testGetStationStockBy(Station $station, $fromDate, $toDate, $expected)
    {
        $stationInvRepo = $this->createMock(StationInventoryRepository::class);
        $stationInvRepo->expects($this->any())->method('getStationStockBy')->willReturn([
            [
                'equipmentId' => 1,
                'equipmentName' => 'Toilets',
                'quantity' => 1
            ],
            [
                'equipmentId' => 2,
                'equipmentName' => 'Chairs',
                'quantity' => 1
            ]
        ]);
        $this->entityManager->expects($this->any())->method('getRepository')->willReturn($stationInvRepo);
        $response = $this->stockInventoryServiceObj->getStationStockBy($station, $fromDate, $toDate);
        $this->assertIsArray($response);
        $this->assertSame($expected, $response);
    }

    public function getStationStockDataProvider(): array
    {
        $dates = [
            'from' => '2022-05-10' ,
            'to' => '2022-05-20'
        ];
        $request = new Request($dates);
        $station = $this->createMock(Station::class);
        $station->setStationName('Berlin');
        return [
            [$request, 1, $station]
        ];
    }

    public function getStationStockByDataProvider(): array
    {
        $station = $this->createMock(Station::class);
        $station->setStationName('Berlin');
        $expected = [
            '2022-05-10' => [
                [
                    'equipmentId' => 1,
                    'equipmentName' => 'Toilets',
                    'quantity' => 1
                ],
                [
                    'equipmentId' => 2,
                    'equipmentName' => 'Chairs',
                    'quantity' => 1
                ]
            ],
            '2022-05-11' => [
                [
                    'equipmentId' => 1,
                    'equipmentName' => 'Toilets',
                    'quantity' => 1
                ],
                [
                    'equipmentId' => 2,
                    'equipmentName' => 'Chairs',
                    'quantity' => 1
                ]
            ]
        ];
        return [
            [$station, '2022-05-10', '2022-05-11', $expected],
        ];
    }

}
