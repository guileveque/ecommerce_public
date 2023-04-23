<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $totalPrice = null;

    #[ORM\Column]
    private ?bool $cartStatus = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $purchaser = null;

    #[ORM\ManyToMany(targetEntity: Catalog::class, inversedBy: 'orders')]
    private Collection $products;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $creationDate = null;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTotalPrice(): ?float
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(float $totalPrice): self
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    public function getCartStatus(): ?bool
    {
        return $this->cartStatus;
    }
    public function setCartStatus(bool $cartStatus): self
    {
        $this->cartStatus = $cartStatus;

        return $this;
    }

    public function getPurchaser(): ?User
    {
        return $this->purchaser;
    }

    public function setPurchaser(?User $purchaser): self
    {
        $this->purchaser = $purchaser;

        return $this;
    }

    /**
     * @return Collection<int, Catalog>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Catalog $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
        }

        return $this;
    }

    public function removeProduct(Catalog $product): self
    {
        $this->products->removeElement($product);

        return $this;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeInterface $creationDate): self
    {
        $this->creationDate = $creationDate;

        return $this;
    }
}
