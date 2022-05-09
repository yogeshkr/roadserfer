<?php

namespace App\Repository;

use App\Entity\Station;
use App\Entity\StationInventory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StationInventory>
 *
 * @method StationInventory|null find($id, $lockMode = null, $lockVersion = null)
 * @method StationInventory|null findOneBy(array $criteria, array $orderBy = null)
 * @method StationInventory[]    findAll()
 * @method StationInventory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StationInventoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StationInventory::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(StationInventory $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(StationInventory $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @param Station $station
     * @param $inventoryDate
     * @return float|int|mixed|string
     * @throws \Doctrine\ORM\Exception\ORMException
     */
    public function getStationStockBy(Station $station, $inventoryDate)
    {
        try{
            $qb = $this->createQueryBuilder('a')
                ->select('e.id as equipmentId, e.name as equipmentName, SUM(a.quantity) as quantity')
                ->leftJoin('App\Entity\Equipment', 'e', 'WITH', 'e.id = a.equipment')
                ->where('a.station = :station')
                ->andWhere('a.inventoryDate <= :inventoryDate')
                ->setParameters([
                    'station' => $station->getId(),
                    'inventoryDate' => $inventoryDate,
                ])
                ->groupBy('e.id')
                ->orderBy('e.id', 'ASC');

            return $qb->getQuery()->execute();
        }catch(\Exception $exception){
            throw new \Doctrine\ORM\Exception\ORMException($exception->getMessage());
        }
    }
}
