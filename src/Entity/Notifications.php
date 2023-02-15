<?php

namespace App\Entity;

use App\Repository\NotificationsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotificationsRepository::class)]
class Notifications
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'notifications')]
    private ?User $users = null;

    #[ORM\Column(length: 255)]
    private ?string $type_not = null;

    #[ORM\Column]
    private ?int $type_id;

    #[ORM\Column]
    private ?int $readed;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created = null;

    #[ORM\Column(length: 110, nullable: true)]
    private ?string $extra = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsers(): ?User
    {
        return $this->users;
    }

    public function setUsers(?User $users): self
    {
        $this->users = $users;

        return $this;
    }

    public function getTypeNot(): ?string
    {
        return $this->type_not;
    }

    public function setTypeNot(string $type_not): self
    {
        $this->type_not = $type_not;

        return $this;
    }

    public function getTypeId(): ?int
    {
        return $this->type_id;
    }

    public function setTypeId(int $type_id): self
    {
        $this->type_id = $type_id;

        return $this;
    }

    public function getReaded(): ?int
    {
        return $this->readed;
    }

    public function setReaded(int $readed): self
    {
        $this->readed = $readed;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getExtra(): ?string
    {
        return $this->extra;
    }

    public function setExtra(?string $extra): self
    {
        $this->extra = $extra;

        return $this;
    }
}
