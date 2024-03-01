<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['groupForUser'])]
    private ?int $id = null;

    #[Groups(['groupForUser'])]
    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private ?string $email = null;

    /**
     * @var string The hashed password
     */
    #[ORM\Column(type: 'string')]
    private string $password;

    #[ORM\OneToOne(targetEntity: Persona::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Persona $persona = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\ManyToMany(targetEntity: Cours::class, inversedBy: 'users')]
    private Collection $cours;

    #[ORM\OneToMany(targetEntity: CoursAccess::class, mappedBy: 'user')]
    private Collection $coursAccesses;

    public function __construct()
    {
        $this->cours = new ArrayCollection();
        $this->coursAccesses = new ArrayCollection();
    }

    // UserInterface methods
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
    }

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getPersona(): ?Persona
    {
        return $this->persona;
    }

    public function setPersona(Persona $persona): self
    {
        $this->persona = $persona;
        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @return Collection<int, Cours>
     */
    public function getCours(): Collection
    {
        return $this->cours;
    }

    public function addCours(Cours $cours): self
    {
        if (!$this->cours->contains($cours)) {
            $this->cours->add($cours);
            $cours->addUser($this);
        }

        return $this;
    }

    public function removeCours(Cours $cours): self
    {
        if ($this->cours->removeElement($cours)) {
            $cours->removeUser($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, CoursAccess>
     */
    public function getCoursAccesses(): Collection
    {
        return $this->coursAccesses;
    }

    public function addCoursAccess(CoursAccess $coursAccess): static
    {
        if (!$this->coursAccesses->contains($coursAccess)) {
            $this->coursAccesses->add($coursAccess);
            $coursAccess->setUser($this);
        }

        return $this;
    }

    public function removeCoursAccess(CoursAccess $coursAccess): static
    {
        if ($this->coursAccesses->removeElement($coursAccess)) {
            // set the owning side to null (unless already changed)
            if ($coursAccess->getUser() === $this) {
                $coursAccess->setUser(null);
            }
        }

        return $this;
    }
}
