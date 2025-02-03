<?php

namespace App\Entity;

use App\Repository\PolizaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PolizaRepository::class)]
class Poliza
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["poliza:read", "cliente:read"])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Cliente::class, inversedBy: 'polizas')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["poliza:read"])]
    #[Assert\NotNull(message: "La póliza debe estar asociada a un cliente.")]
    private ?Cliente $cliente = null;

    #[ORM\Column(length: 255)]
    #[Groups(["poliza:read", "poliza:write", "cliente:read"])]
    #[Assert\NotBlank(message: "El modelo del auto es obligatorio.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "El modelo del auto no puede tener más de {{ limit }} caracteres."
    )]
    private ?string $auto = null;

    #[ORM\Column]
    #[Groups(["poliza:read", "poliza:write"])]
    #[Assert\NotNull(message: "El costo es obligatorio.")]
    #[Assert\Positive(message: "El costo debe ser un valor positivo.")]
    private ?float $costo = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(["poliza:read", "poliza:write"])]
    #[Assert\NotNull(message: "La fecha de vigencia es obligatoria.")]
    #[Assert\GreaterThan("today", message: "La fecha de vigencia debe ser futura.")]
    private ?\DateTimeInterface $fechaVigencia = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCliente(): ?Cliente
    {
        return $this->cliente;
    }

    public function setCliente(?Cliente $cliente): static
    {
        $this->cliente = $cliente;
        return $this;
    }

    public function getAuto(): ?string
    {
        return $this->auto;
    }

    public function setAuto(string $auto): static
    {
        $this->auto = $auto;
        return $this;
    }

    public function getCosto(): ?float
    {
        return $this->costo;
    }

    public function setCosto(float $costo): static
    {
        $this->costo = $costo;
        return $this;
    }

    public function getFechaVigencia(): ?\DateTimeInterface
    {
        return $this->fechaVigencia;
    }

    public function setFechaVigencia(\DateTimeInterface $fechaVigencia): static
    {
        $this->fechaVigencia = $fechaVigencia;
        return $this;
    }
}
