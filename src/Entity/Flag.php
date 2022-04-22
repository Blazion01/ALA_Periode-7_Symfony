<?php

namespace App\Entity;

use App\Repository\FlagRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FlagRepository::class)]
class Flag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'flags')]
    #[ORM\JoinColumn(nullable: false)]
    private $Flagger;

    #[ORM\ManyToOne(targetEntity: Profile::class, inversedBy: 'flags')]
    #[ORM\JoinColumn(nullable: false)]
    private $Flagged;

    #[ORM\Column(type: 'boolean')]
    private $Interested;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFlagger(): ?User
    {
        return $this->Flagger;
    }

    public function setFlagger(?User $Flagger): self
    {
        $this->Flagger = $Flagger;

        return $this;
    }

    public function getFlagged(): ?Profile
    {
        return $this->Flagged;
    }

    public function setFlagged(?Profile $Flagged): self
    {
        $this->Flagged = $Flagged;

        return $this;
    }

    public function getInterested(): ?bool
    {
        return $this->Interested;
    }

    public function setInterested(bool $Interested): self
    {
        $this->Interested = $Interested;

        return $this;
    }
}
