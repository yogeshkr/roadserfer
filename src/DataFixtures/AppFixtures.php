<?php

namespace App\DataFixtures;

use App\Entity\Equipment;
use App\Entity\Order;
use App\Entity\OrderDetail;
use App\Entity\Station;
use App\Entity\StationInventory;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private  $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager)
    {
        $faker =  Factory::create();
        $stations = ['Munich', 'Paris', 'Porto', 'Madrid'];
        $equipments = ['Camper Van', 'Toilets', 'Bed Sheets', 'Sleeping Bags', 'Camping Tables', 'Chairs'];
        $stationList = $equipmentList = [];
        foreach($stations as $key => $station){
            $stationObj = new Station();
            $stationObj->setStationName($station);
            $stationObj->setStationCode(strtoupper(str_replace(' ', '', $station)));
            $stationObj->setStatus(1);
            $stationObj->setCreatedAt($faker->dateTime());
            $stationObj->setUpdatedAt($faker->dateTime());
            $manager->persist($stationObj);
            $manager->flush();
            $stationList[$key] = $stationObj;
        }

        foreach($equipments as $key => $equipment){
            $equipmentObj = new Equipment();
            $equipmentObj->setName($equipment);
            $equipmentObj->setCode(strtoupper(str_replace(' ', '', $equipment)));
            $equipmentObj->setStatus(1);
            $equipmentObj->setQuantity(100);
            $equipmentObj->setPrice($faker->randomFloat(1, 1, 99));
            $equipmentObj->setCreatedAt($faker->dateTime());
            $equipmentObj->setUpdatedAt($faker->dateTime());
            $manager->persist($equipmentObj);
            $manager->flush();
            $equipmentList[$key] = $equipmentObj;
        }

        foreach($stationList as $station){
            foreach($equipmentList as  $equipment){
                $sInventory = new StationInventory();
                $sInventory->setStation($station);
                $sInventory->setEquipment($equipment);
                $sInventory->setQuantity(random_int(1, 5));
                $sInventory->setInventoryDate($faker->dateTimeBetween('-1 month', 'now'));
                $sInventory->setInventoryType(StationInventory::INVENTORY_CHECK_IN);
                $manager->persist($sInventory);
            }
        }

        $user = new User();
        $user->setUsername('dummy');
        $password = $this->hasher->hashPassword($user, 'dummy');
        $user->setPassword($password);
        $user->setRoles(['ROLE_USER']);
        $manager->persist($user);
        $manager->flush();

        $fromDate = new \DateTime('now -10 Days');
        $toDate = new \DateTime('now');
        for($i = 0; $i < count($stationList)-2; $i++){
            $order = new Order();
            $order->setUser($user);
            $order->setFromStation($stationList[$i]);
            $order->setToStation($stationList[$i+1]);
            $order->setBookedFrom($fromDate);
            $order->setBookedTo($toDate);
            $order->setTotalAmount($faker->randomFloat(1, 1, 999));
            $order->setCreatedAt($faker->dateTime());
            $order->setUpdatedAt($faker->dateTime());
            $manager->persist($order);
            foreach($equipmentList as  $equipment){
                $orderDetail = new OrderDetail();
                $orderDetail->setEquipment($equipment);
                $orderDetail->setOrder($order);
                $orderDetail->setQuantity(1);
                $orderDetail->setPrice($faker->randomFloat(1, 1, 99));
                $orderDetail->setCreatedAt($faker->dateTime());
                $orderDetail->setUpdatedAt($faker->dateTime());
                $manager->persist($orderDetail);

                $sInventory = new StationInventory();
                $sInventory->setOrderId($order);
                $sInventory->setStation($stationList[$i]);
                $sInventory->setEquipment($equipment);
                $sInventory->setQuantity(-1);
                $sInventory->setInventoryDate($fromDate);
                $sInventory->setInventoryType(StationInventory::INVENTORY_CHECK_OUT);
                $manager->persist($sInventory);

                $sInventory1 = new StationInventory();
                $sInventory1->setStation($stationList[$i+1]);
                $sInventory1->setOrderId($order);
                $sInventory1->setEquipment($equipment);
                $sInventory1->setQuantity(1);
                $sInventory1->setInventoryDate($toDate);
                $sInventory1->setInventoryType(StationInventory::INVENTORY_CHECK_IN);
                $manager->persist($sInventory1);
            }
        }
        $manager->flush();
    }
}