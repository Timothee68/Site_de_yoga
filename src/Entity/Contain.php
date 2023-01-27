<?php

namespace App\Entity;

use App\Repository\ContainRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ContainRepository::class)
 */
class Contain
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantity;

    /**
     * @ORM\ManyToOne(targetEntity=ProductSheet::class, inversedBy="CommandContain")
     */
    private $ProductContain;

    /**
     * @ORM\ManyToOne(targetEntity=Command::class, inversedBy="contains")
     */
    private $CommandContain;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getProductContain(): ?ProductSheet
    {
        return $this->ProductContain;
    }

    public function setProductContain(?ProductSheet $ProductContain): self
    {
        $this->ProductContain = $ProductContain;

        return $this;
    }

    public function getCommandContain(): ?Command
    {
        return $this->CommandContain;
    }

    public function setCommandContain(?Command $CommandContain): self
    {
        $this->CommandContain = $CommandContain;

        return $this;
    }
}
