<?php

namespace App\Entity;

use App\Repository\CookieConsentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CookieConsentRepository::class)]
class CookieConsent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $analytics = null;

	#[ORM\Column]
    private ?bool $ads = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $cookieKey = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAnalytics(): ?bool
    {
        return $this->analytics;
    }

    public function setAnalytics(bool $analytics): self
    {
        $this->analytics = $analytics;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCookieKey(): ?string
    {
        return $this->cookieKey;
    }

    public function setCookieKey(string $cookieKey): self
    {
        $this->cookieKey = $cookieKey;

        return $this;
    }

    public function getAds(): ?bool
    {
        return $this->ads;
    }

    public function setAds(bool $ads): self
    {
        $this->ads = $ads;

        return $this;
    }
}
