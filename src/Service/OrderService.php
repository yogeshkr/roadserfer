<?php

namespace App\Service;

use App\Entity\Equipment;
use App\Entity\Order;
use App\Entity\OrderDetail;
use App\Entity\Station;
use App\Entity\StationInventory;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class OrderService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var StockInventoryService
     */
    private $stockInventoryService;

    public function __construct(EntityManagerInterface $entityManager, StockInventoryService $stockInventory)
    {
        $this->entityManager = $entityManager;
        $this->stockInventoryService = $stockInventory;
    }

    /**
     * @param User $user
     * @param $orderArray
     * @return array
     * @throws \Exception
     */
    public function createOrder(User $user, $orderArray): array
    {
        $bookedFrom = $orderArray['bookedFrom'];
        $bookedTo = $orderArray['bookedTo'];
        $pickupStationId = $orderArray['pickupStation'];
        $dropStationId = $orderArray['dropStation'];
        $equipments = $orderArray['equipments'];
        if($bookedFrom > $bookedTo){
            throw new \Exception('Selected timeline is incorrect.');
        }

        $pickupStation = $this->checkStation($pickupStationId);
        $dropStation = $this->checkStation($dropStationId);
        if(!$pickupStation){
            throw new \Exception('Selected pickup station is invalid.');
        }

        if(!$dropStation){
            throw new \Exception('Selected drop station is invalid.');
        }

        if(count($equipments) <= 0){
            throw new \Exception('Equipments should be selected.');
        }

        $dataSet = [];
        foreach($equipments as $equipment){
            $dataSet[$equipment['Id']] = $equipment['Quantity'];
        }

        $stockCheck = $this->stockInventoryService->getStationStockBy($pickupStation, $bookedFrom, $bookedTo);
        foreach($stockCheck as $stocks){
            foreach($stocks as $stock){
                if(isset($dataSet[$stock['equipmentId']])){
                    if($dataSet[$stock['equipmentId']] > $stock['quantity']){
                        throw new \Exception('Equipments is out of stock.');
                    }
                }
            }
        }

        $order = $this->addOrder($user, $pickupStation, $dropStation, $orderArray);
        $this->addOrderDetails($order, $pickupStation, $dropStation, $orderArray);
        $this->entityManager->flush();

        return [
            'orderId' => $order->getId(),
            'status' => true,
            'message' => 'Order Created'
        ];
    }

    /**
     * @param $stationId
     * @return Station|false
     */
    public function checkStation($stationId)
    {
        $station = $this->entityManager->getRepository(Station::class)->findOneBy(['id' => $stationId, 'status' => Station::STATUS_ACTIVE]);
        if($station instanceof Station){
            return $station;
        }
        return false;
    }

    /**
     * @param User $user
     * @param Station $pickupStation
     * @param Station $dropStation
     * @param $orderArray
     * @return Order
     * @throws \Exception
     */
    public function addOrder(User $user, Station $pickupStation, Station $dropStation, $orderArray): Order
    {
        $bookedFrom = $orderArray['bookedFrom'];
        $bookedTo = $orderArray['bookedTo'];
        $order = new Order();
        $order->setUser($user);
        $order->setFromStation($pickupStation);
        $order->setToStation($dropStation);
        $order->setBookedFrom(new \DateTime($bookedFrom));
        $order->setBookedTo(new \DateTime($bookedTo));
        $order->setTotalAmount($orderArray['totalAmount']);
        $order->setCreatedAt(new \DateTime());
        $order->setUpdatedAt(new \DateTime());
        $this->entityManager->persist($order);

        return $order;
    }

    public function addOrderDetails(Order $order, Station $pickupStation, Station $dropStation, $orderArray): void
    {
        $equipments = $orderArray['equipments'];
        $bookedFrom = $orderArray['bookedFrom'];
        $bookedTo = $orderArray['bookedTo'];
        foreach($equipments as $equipment){
            $equipmentObj = $this->entityManager->getRepository(Equipment::class)->findOneBy(['id' => $equipment['Id'], 'status' => Equipment::STATUS_ACTIVE]);
            $orderDetail = new OrderDetail();
            $orderDetail->setEquipment($equipmentObj);
            $orderDetail->setOrder($order);
            $orderDetail->setQuantity($equipment['Quantity']);
            $orderDetail->setPrice($equipment['Price']);
            $orderDetail->setCreatedAt(new \DateTime());
            $orderDetail->setUpdatedAt(new \DateTime());
            $this->entityManager->persist($orderDetail);
            $this->addStationInventory($order, $pickupStation, $equipmentObj, -(int)$equipment['Quantity'], new \DateTime($bookedFrom), StationInventory::INVENTORY_CHECK_OUT);
            $this->addStationInventory($order, $dropStation, $equipmentObj, (int)$equipment['Quantity'], new \DateTime($bookedTo));
            $this->entityManager->flush();
        }
    }

    public function addStationInventory(Order $order, Station $station, Equipment $equipment, $quantity, $inventoryDate, $inventoryType = StationInventory::INVENTORY_CHECK_IN): void
    {
        $sInventory = new StationInventory();
        $sInventory->setOrderId($order);
        $sInventory->setStation($station);
        $sInventory->setEquipment($equipment);
        $sInventory->setQuantity($quantity);
        $sInventory->setInventoryDate($inventoryDate);
        $sInventory->setInventoryType($inventoryType);
        $this->entityManager->persist($sInventory);
        $this->entityManager->flush();
    }
}
