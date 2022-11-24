<?php

namespace App\Entity;

use App\Repository\HourlyRecordRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HourlyRecordRepository::class)]
class HourlyRecord
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'hourlyRecords')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Game $game = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(nullable: true)]
    private ?int $redditOnline = null;

    #[ORM\Column(nullable: true)]
    private ?int $discordOnline = null;

    #[ORM\Column(nullable: true)]
    private ?int $twitchViewers = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $twitchMainLang = null;

    #[ORM\Column(nullable: true)]
    private ?int $twitchStreams = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setGame(?Game $game): self
    {
        $this->game = $game;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getRedditOnline(): ?int
    {
        return $this->redditOnline;
    }

    public function setRedditOnline(?int $redditOnline): self
    {
        $this->redditOnline = $redditOnline;

        return $this;
    }

    public function getDiscordOnline(): ?int
    {
        return $this->discordOnline;
    }

    public function setDiscordOnline(?int $discordOnline): self
    {
        $this->discordOnline = $discordOnline;

        return $this;
    }

    public function getTwitchViewers(): ?int
    {
        return $this->twitchViewers;
    }

    public function setTwitchViewers(?int $twitchViewers): self
    {
        $this->twitchViewers = $twitchViewers;

        return $this;
    }

    public function getTwitchMainLang(): ?string
    {
        return $this->twitchMainLang;
    }

    public function setTwitchMainLang(?string $twitchMainLang): self
    {
        $this->twitchMainLang = $twitchMainLang;

        return $this;
    }

    public function getTwitchStreams(): ?int
    {
        return $this->twitchStreams;
    }

    public function setTwitchStreams(?int $twitchStreams): self
    {
        $this->twitchStreams = $twitchStreams;

        return $this;
    }
}
