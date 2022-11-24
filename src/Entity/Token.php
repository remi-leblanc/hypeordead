<?php

namespace App\Entity;

use App\Repository\TokenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TokenRepository::class)]
class Token
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $serviceName;

    #[ORM\Column(type: 'string', length: 255)]
    private $headerKey;

    #[ORM\Column(type: 'string', length: 255)]
    private $headerValue;

    #[ORM\Column(type: 'datetime')]
    private $updatedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getServiceName(): ?string
    {
        return $this->serviceName;
    }

    public function setServiceName(string $serviceName): self
    {
        $this->serviceName = $serviceName;

        return $this;
    }

    public function getHeaderKey(): ?string
    {
        return $this->headerKey;
    }

    public function setHeaderKey(string $headerKey): self
    {
        $this->headerKey = $headerKey;

        return $this;
    }

    public function getHeaderValue(): ?string
    {
        return $this->headerValue;
    }

    public function setHeaderValue(string $headerValue): self
    {
        $this->headerValue = $headerValue;

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
}
