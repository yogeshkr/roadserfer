<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use App\Traits\TimeStampTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OrderRepository::class)
 * @ORM\Table(name="`order`")
 */
class Order
{
    use TimeStampTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Station::class)
     * @ORM\JoinColumn(name="pickup_station", referencedColumnName="id", nullable=false)
     */
    private $fromStation;

    /**
     * @ORM\ManyToOne(targetEntity=Station::class)
     * @ORM\JoinColumn(name="drop_station", referencedColumnName="id", nullable=false)
     */
    private $toStation;

    /**
     * @ORM\Column(name="booked_from", type="date")
     */
    private $bookedFrom;

    /**
     * @ORM\Column(name="booked_to", type="date")
     */
    private $bookedTo;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $totalAmount;

    /**
     * @ORM\OneToMany(targetEntity=OrderDetail::class, mappedBy="order_id")
     */
    private $orderDetails;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="orders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    public function __construct()
    {
        $this->orderDetails = new ArrayCollection();
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

    public function getFromStation(): ?Station
    {
        return $this->fromStation;
    }

    public function setFromStation(?Station $fromStation): self
    {
        $this->fromStation = $fromStation;

        return $this;
    }

    public function getToStation(): ?Station
    {
        return $this->toStation;
    }

    public function setToStation(?Station $toStation): self
    {
        $this->toStation = $toStation;

        return $this;
    }

    public function getBookedFrom(): ?\DateTimeInterface
    {
        return $this->bookedFrom;
    }

    public function setBookedFrom(\DateTimeInterface $bookedFrom): self
    {
        $this->bookedFrom = $bookedFrom;

        return $this;
    }

    public function getBookedTo(): ?\DateTimeInterface
    {
        return $this->bookedTo;
    }

    public function setBookedTo(\DateTimeInterface $bookedTo): self
    {
        $this->bookedTo = $bookedTo;

        return $this;
    }

    public function getTotalAmount(): ?string
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(?string $totalAmount): self
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    /**
     * @return Collection<int, OrderDetail>
     */
    public function getOrderDetails(): Collection
    {
        return $this->orderDetails;
    }

    public function addOrderDetail(OrderDetail $orderDetail): self
    {
        if (!$this->orderDetails->contains($orderDetail)) {
            $this->orderDetails[] = $orderDetail;
            $orderDetail->setOrder($this);
        }

        return $this;
    }

    public function removeOrderDetail(OrderDetail $orderDetail): self
    {
        if ($this->orderDetails->removeElement($orderDetail)) {
            // set the owning side to null (unless already changed)
            if ($orderDetail->getOrder() === $this) {
                $orderDetail->setOrder(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
