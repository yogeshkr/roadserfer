<?php

namespace App\Entity;

use App\Repository\StationRepository;
use App\Traits\TimeStampTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=StationRepository::class)
 */
class Station
{
    use TimeStampTrait;

    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 1;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $station_name;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $station_code;

    /**
     * @ORM\Column(type="boolean", options={"default": "1"})
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity=StationInventory::class, mappedBy="station_id", orphanRemoval=true)
     */
    private $stationInventories;

    /**
     * @ORM\OneToMany(targetEntity=Order::class, mappedBy="station_id", orphanRemoval=true)
     */
    private $pickupStation;

    /**
     * @ORM\OneToMany(targetEntity=Order::class, mappedBy="station_id", orphanRemoval=true)
     */
    private $dropStation;

    public function __construct()
    {
        $this->stationInventories = new ArrayCollection();
        $this->pickupStation = new ArrayCollection();
        $this->dropStation = new ArrayCollection();
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->updatedAt = new \DateTime();
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStationName(): ?string
    {
        return $this->station_name;
    }

    public function setStationName(string $station_name): self
    {
        $this->station_name = $station_name;

        return $this;
    }

    public function getStationCode(): ?string
    {
        return $this->station_code;
    }

    public function setStationCode(string $station_code): self
    {
        $this->station_code = $station_code;

        return $this;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function __toString() {
        return  $this->station_name;
    }
}
