<?php

namespace App\Entity;

use App\Repository\StationInventoryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=StationInventoryRepository::class)
 */
class StationInventory
{

    public const INVENTORY_CHECK_IN = 0;
    public const INVENTORY_CHECK_OUT = 1;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Station::class, inversedBy="stationInventories")
     * @ORM\JoinColumn(nullable=false)
     */
    private $station;

    /**
     * @ORM\ManyToOne(targetEntity=Equipment::class, inversedBy="equipmentInventories")
     * @ORM\JoinColumn(nullable=false)
     */
    private $equipment;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantity;

    /**
     * @ORM\Column(type="date")
     */
    private $inventoryDate;

    /**
     * @ORM\Column(type="integer")
     */
    private $inventoryType;

    /**
     * @ORM\ManyToOne(targetEntity=Order::class)
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", nullable=true)
     */
    private $orderId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStation(): ?Station
    {
        return $this->station;
    }

    public function setStation(Station $station): self
    {
        $this->station = $station;

        return $this;
    }

    public function getEquipment(): ?Equipment
    {
        return $this->equipment;
    }

    public function setEquipment($equipment): self
    {
        $this->equipment = $equipment;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getInventoryDate(): ?\DateTimeInterface
    {
        return $this->inventoryDate;
    }

    public function setInventoryDate(\DateTimeInterface $inventoryDate): self
    {
        $this->inventoryDate = $inventoryDate;

        return $this;
    }

    public function getInventoryType(): ?int
    {
        return $this->inventoryType;
    }

    public function setInventoryType(int $inventoryType): self
    {
        $this->inventoryType = $inventoryType;

        return $this;
    }

    public function getOrderId(): ?Order
    {
        return $this->orderId;
    }

    public function setOrderId(?Order $orderId): self
    {
        $this->orderId = $orderId;

        return $this;
    }
}
