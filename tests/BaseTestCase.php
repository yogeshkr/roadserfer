<?php


namespace App\Tests;


use App\Service\OrderService;
use App\Service\StockInventoryService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class BaseTestCase extends TestCase
{
    protected $stockInventoryService;
    protected $entityManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->orderService = $this->createMock(OrderService::class);
        $this->stockInventoryService = $this->createMock(StockInventoryService::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager = null;
        $this->orderService = null;
        $this->stockInventoryService = null;
    }
}
