<?php

namespace App\Tests\Service;

use App\Entity\Equipment;
use App\Entity\Order;
use App\Entity\Station;
use App\Entity\User;
use App\Repository\EquipmentRepository;
use App\Repository\StationRepository;
use App\Service\OrderService;
use App\Tests\BaseTestCase;

class OrderServiceTest extends BaseTestCase
{
    public function setUp() :void
    {
        parent::setUp();
        $this->orderServiceObj = new OrderService($this->entityManager, $this->stockInventoryService);
        $this->orderServiceMockObj = $this->getMockBuilder(OrderService::class)
            ->setConstructorArgs([
                $this->entityManager,
                $this->stockInventoryService,
            ]);
    }

    /**
     * @param User $user
     * @param $orderArray
     * @param $pickupStation
     * @param $dropStation
     * @param $expected
     * @dataProvider createOrderDataProvider
     * @return void
     * @throws \ReflectionException
     *
     */
    public function testCreateOrderWithExceptions(User $user, $orderArray, $pickupStation, $dropStation, $stockArray, $expected)
    {
        $orderServiceMockObj = $this->orderServiceMockObj->setMethods([
            'checkStation',
            'addOrder',
            'addOrderDetails',
        ])->getMock();
        $orderServiceMockObj->expects($this->any())->method('checkStation')->willReturnOnConsecutiveCalls($pickupStation, $dropStation);
        $this->stockInventoryService->expects($this->any())->method('getStationStockBy')->willReturn($stockArray);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage($expected);
        $orderServiceMockObj->createOrder($user, $orderArray);
    }

    public function createOrderDataProvider(): array
    {
        $order = [
            "pickupStation" => 3,
            "dropStation" => 4,
            "bookedFrom" => "2022-05-21",
            "bookedTo" => "2022-05-20",
            "totalAmount" => "60.00",
            "equipments" => []
        ];

        $order1 = [
            "pickupStation" => 3,
            "dropStation" => 4,
            "bookedFrom" => "2022-05-10",
            "bookedTo" => "2022-05-20",
            "totalAmount" => "60.00",
            "equipments" => []
        ];

        $order2 = [
            "pickupStation" => 3,
            "dropStation" => 4,
            "bookedFrom" => "2022-05-15",
            "bookedTo" => "2022-05-20",
            "totalAmount" => "60.00",
            "equipments" => [
                [
                    "Id" => 1,
                    "Price" => "10.00",
                    "Quantity" => 5
                ],
            ]
        ];

        $stockArray = [
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
        ];

        $pickupStation = $this->createMock(Station::class);
        $dropStation = $this->createMock(Station::class);
        $user = $this->createMock(User::class);
        return [
            [$user, $order, false, $dropStation, null, 'Selected timeline is incorrect.'],
            [$user, $order1, false, $dropStation, null, 'Selected pickup station is invalid.'],
            [$user, $order1, $pickupStation, false, null, 'Selected drop station is invalid.'],
            [$user, $order1, $pickupStation, $dropStation, null, 'Equipments should be selected.'],
            [$user, $order2, $pickupStation, $dropStation, $stockArray, 'Equipments is out of stock.'],
        ];
    }


    /**
     * @param User $user
     * @param $orderArray
     * @param $pickupStation
     * @param $dropStation
     * @param $stockArray
     * @dataProvider createOrderSuccessDataProvider
     * @return void
     * @throws \ReflectionException
     */
    public function testCreateOrderSuccessDataProvider(User $user, $orderArray, $pickupStation, $dropStation, $stockArray)
    {

        $orderServiceMockObj = $this->orderServiceMockObj->setMethods([
            'checkStation',
            'addOrder',
            'addOrderDetails',
        ])->getMock();
        $orderServiceMockObj->expects($this->any())->method('checkStation')->willReturnOnConsecutiveCalls($pickupStation, $dropStation);
        $orderServiceMockObj->expects($this->any())->method('addOrder')->willReturn(new Order());
        $orderServiceMockObj->expects($this->any())->method('addOrderDetails')->willReturn(true);
        $this->stockInventoryService->expects($this->any())->method('getStationStockBy')->willReturn($stockArray);
        $response = $orderServiceMockObj->createOrder($user, $orderArray);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('orderId', $response);
        $this->assertArrayHasKey('status', $response);
        $this->assertArrayHasKey('message', $response);
    }

    public function createOrderSuccessDataProvider(): array
    {
        $order2 = [
            "pickupStation" => 3,
            "dropStation" => 4,
            "bookedFrom" => "2022-05-15",
            "bookedTo" => "2022-05-20",
            "totalAmount" => "60.00",
            "equipments" => [
                [
                    "Id" => 1,
                    "Price" => "10.00",
                    "Quantity" => 1
                ],
            ]
        ];

        $stockArray = [
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
        ];

        $pickupStation = $this->createMock(Station::class);
        $dropStation = $this->createMock(Station::class);
        $user = $this->createMock(User::class);

        return [
            [$user, $order2, $pickupStation, $dropStation, $stockArray]
        ];
    }

    /**
     * @param $stationId
     * @param $station
     * @dataProvider checkStationDataProvider
     * @return void
     */
    public function testCheckStation($stationId, $station)
    {
        $stationRepo = $this->createMock(StationRepository::class);
        $stationRepo->expects($this->any())->method('findOneBy')->with(['id' => $stationId, 'status' => Station::STATUS_ACTIVE])->willReturn($station);
        $this->entityManager->expects($this->any())->method('getRepository')->willReturn($stationRepo);
        $response = $this->orderServiceObj->checkStation($stationId);
        $this->assertSame($station, $response);
    }

    public function checkStationDataProvider(): array
    {
        $station = $this->createMock(Station::class);

        return [
            [1, $station],
            [1, false],
        ];
    }

    public function testAddOrder()
    {
        $pickupStation = $this->createMock(Station::class);
        $dropStation = $this->createMock(Station::class);
        $user = $this->createMock(User::class);
        $orderArray = [
            "pickupStation" => 3,
            "dropStation" => 4,
            "bookedFrom" => "2022-05-15",
            "bookedTo" => "2022-05-20",
            "totalAmount" => "60.00",
            "equipments" => [
                [
                    "Id" => 1,
                    "Price" => "10.00",
                    "Quantity" => 1
                ],
            ]
        ];

        $response = $this->orderServiceObj->addOrder($user, $pickupStation, $dropStation, $orderArray);
        $this->assertInstanceOf(Order::class, $response);
    }

    public function testAddOrderDetails()
    {
        $pickupStation = $this->createMock(Station::class);
        $dropStation = $this->createMock(Station::class);
        $order = $this->createMock(Order::class);
        $orderArray = [
            "pickupStation" => 3,
            "dropStation" => 4,
            "bookedFrom" => "2022-05-15",
            "bookedTo" => "2022-05-20",
            "totalAmount" => "60.00",
            "equipments" => [
                [
                    "Id" => 1,
                    "Price" => "10.00",
                    "Quantity" => 1
                ],
            ]
        ];

        $equipmentRepo = $this->createMock(EquipmentRepository::class);
        $equipmentRepo->expects($this->any())->method('findOneBy')->with(['id' => 1, 'status' => Equipment::STATUS_ACTIVE])->willReturn(new Equipment());
        $this->entityManager->expects($this->any())->method('getRepository')->willReturn($equipmentRepo);
        $response = $this->orderServiceObj->addOrderDetails($order, $pickupStation, $dropStation, $orderArray);
        $this->assertNull($response);
    }
}