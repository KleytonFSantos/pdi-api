<?php

namespace App\Domain\Entity;

use App\Domain\Enum\UserRoles;
use App\Domain\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email', 'cpf'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    #[Assert\Type('string')]
    private ?string $email = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    #[Assert\Type('string')]
    private ?string $name = null;

    #[ORM\Column(length: 14)]
    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    #[Assert\Type('string')]
    #[Assert\Length(min: 11, max: 14)]
    private ?string $cpf = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    #[Assert\Choice([[UserRoles::Logista->value], [UserRoles::Comun->value]])]
    private array $roles = [];

    #[ORM\OneToOne(inversedBy: 'user', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Wallet $wallet = null;

    #[ORM\OneToMany(targetEntity: Transaction::class, mappedBy: 'user')]
    private Collection $transaction;

    #[ORM\Column]
    #[Assert\NotNull()]
    #[Assert\Type('string')]
    #[Assert\NotBlank()]
    private ?string $password = null;

    #[Assert\NotNull()]
    #[Assert\Type('string')]
    #[Assert\NotBlank()]
    #[Assert\EqualTo(propertyPath: 'password')]
    private ?string $passwordConfirmation = null;

    public function __construct()
    {
        $this->transaction = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getCpf(): ?string
    {
        return $this->cpf;
    }

    public function setCpf(?string $cpf): void
    {
        $this->cpf = $cpf;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getPasswordConfirmation(): ?string
    {
        return $this->passwordConfirmation;
    }

    public function setPasswordConfirmation(?string $passwordConfirmation): void
    {
        $this->passwordConfirmation = $passwordConfirmation;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getWallet(): ?Wallet
    {
        return $this->wallet;
    }

    public function setWallet(?Wallet $wallet): static
    {
        $this->wallet = $wallet;

        return $this;
    }

    public function getTransactions(): Collection
    {
        return $this->transaction;
    }

    public function addTransaction(Transaction $transaction): void
    {
        if (!$this->transaction->contains($transaction)) {
            $this->transaction->add($transaction);
            $transaction->setUser($this);
        }
    }

    public function removeTransaction(Transaction $transaction): void
    {
        if ($this->transaction->removeElement($transaction)) {
            if ($transaction->getUser() === $this) {
                $transaction->setUser(null);
            }
        }
    }
}
