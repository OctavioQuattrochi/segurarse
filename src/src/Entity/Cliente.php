<?php

namespace App\Entity;

use App\Repository\ClienteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ClienteRepository::class)]
class Cliente
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["cliente:read", "poliza:read"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "El nombre es obligatorio.")]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: "El nombre debe tener al menos {{ limit }} caracteres.",
        maxMessage: "El nombre no puede tener más de {{ limit }} caracteres."
    )]
    #[Groups(["cliente:read", "poliza:read"])]
    private ?string $nombre = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "El apellido es obligatorio.")]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: "El apellido debe tener al menos {{ limit }} caracteres.",
        maxMessage: "El apellido no puede tener más de {{ limit }} caracteres."
    )]
    #[Groups(["cliente:read", "poliza:read"])]
    private ?string $apellido = null;

    #[ORM\Column(length: 20, unique: true)]
    #[Assert\NotBlank(message: "El DNI es obligatorio.")]
    #[Assert\Regex(
        pattern: "/^\d{7,8}$/",
        message: "El DNI debe contener solo números y tener entre 7 y 8 dígitos."
    )]
    #[Groups(["cliente:read"])]
    private ?string $dni = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: "La fecha de nacimiento es obligatoria.")]
    #[Assert\LessThan("-18 years", message: "El cliente debe ser mayor de 18 años.")]
    #[Groups(["cliente:read"])]
    private ?\DateTimeInterface $fechaNacimiento = null;

    /**
     * @var Collection<int, Poliza>
     */
    #[ORM\OneToMany(targetEntity: Poliza::class, mappedBy: 'cliente', cascade: ['persist', 'remove'])]
    private Collection $polizas;

    public function __construct()
    {
        $this->polizas = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getApellido(): ?string
    {
        return $this->apellido;
    }

    public function setApellido(string $apellido): static
    {
        $this->apellido = $apellido;

        return $this;
    }

    public function getDni(): ?string
    {
        return $this->dni;
    }

    public function setDni(string $dni): static
    {
        $this->dni = $dni;

        return $this;
    }

    public function getFechaNacimiento(): ?\DateTimeInterface
    {
        return $this->fechaNacimiento;
    }

    public function setFechaNacimiento(\DateTimeInterface $fechaNacimiento): static
    {
        $this->fechaNacimiento = $fechaNacimiento;

        return $this;
    }
    
    public function getPolizas(): array
    {
        return $this->polizas->map(fn(Poliza $poliza) => [
            'id' => $poliza->getId(),
            'auto' => $poliza->getAuto(),
            'costo' => $poliza->getCosto(),
            'fechaVigencia' => $poliza->getFechaVigencia()->format('Y-m-d')
        ])->toArray();
    }

    public function addPoliza(Poliza $poliza): static
    {
        if (!$this->polizas->contains($poliza)) {
            $this->polizas->add($poliza);
            $poliza->setCliente($this);
        }

        return $this;
    }

    public function removePoliza(Poliza $poliza): static
    {
        if ($this->polizas->removeElement($poliza)) {
            
            if ($poliza->getCliente() === $this) {
                $poliza->setCliente(null);
            }
        }

        return $this;
    }
}
