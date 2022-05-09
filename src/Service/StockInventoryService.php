<?php

namespace App\Service;

use App\Entity\Equipment;
use App\Entity\Station;
use App\Entity\StationInventory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StockInventoryService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Int $stationId
     * @param Request $request
     * @return array[]
     * @throws \Exception
     */
    public function getStationStock(Int $stationId, Request $request): array
    {
        $timelineFrom = $request->query->get('from');
        $timelineTo = $request->query->get('to');
        if($timelineFrom > $timelineTo){
            throw new \Exception('Selected timeline is incorrect.');
        }
        $station = $this->entityManager->getRepository(Station::class)->findOneBy(['id' => $stationId, 'status' => Station::STATUS_ACTIVE]);
        if($station instanceof Station){
            $data = [];
            $data['stationId'] = $station->getId();
            $data['stationName'] = $station->getStationName();
            $data['stationStock'] = $this->getStationStockBy($station, $timelineFrom, $timelineTo);

            return ['data' => $data];
        }
        throw new NotFoundHttpException('Station not found');
    }

    public function getStationStockBy(Station $station, $fromDate, $toDate): array
    {
        $data = [];
        $period = new \DatePeriod(new \DateTime($fromDate), new \DateInterval('P1D'), new \DateTime($toDate . '+1 day'));
        foreach($period as $date){
            $result = $this->entityManager->getRepository(StationInventory::class)->getStationStockBy($station, $date->format('Y-m-d'));
            $data[$date->format('Y-m-d')] = $result;
        }
        return $data;
    }
}