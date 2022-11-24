<?php

namespace App\Entity;

use App\Repository\RecordRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecordRepository::class)]
class Record
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'datetime')]
    private $date;

    #[ORM\ManyToOne(targetEntity: Game::class, inversedBy: 'records')]
    #[ORM\JoinColumn(nullable: false)]
    private $game;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $redditOnlineWeek;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $discordOnlineWeek;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $twitchViewersWeek;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $redditTotalMembers;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $discordTotalMembers;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $redditPostCountWeek;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $twitchMainLangWeek;

    #[ORM\Column]
    private ?bool $hidden = null;

    #[ORM\Column(nullable: true)]
    private ?int $twitchFollowers = null;

    public function getId(): ?int
   	{
   		return $this->id;
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

    public function getGame(): ?Game
   	{
   		return $this->game;
   	}

    public function setGame(?Game $game): self
   	{
   		$this->game = $game;

   		return $this;
   	}

    public function getRedditOnlineWeek(): ?int
   	{
   		return $this->redditOnlineWeek;
   	}

    public function setRedditOnlineWeek(?int $redditOnlineWeek): self
   	{
   		$this->redditOnlineWeek = $redditOnlineWeek;

   		return $this;
   	}

    public function getDiscordOnlineWeek(): ?int
   	{
   		return $this->discordOnlineWeek;
   	}

    public function setDiscordOnlineWeek(?int $discordOnlineWeek): self
   	{
   		$this->discordOnlineWeek = $discordOnlineWeek;

   		return $this;
   	}

    public function getTwitchViewersWeek(): ?int
   	{
   		return $this->twitchViewersWeek;
   	}

    public function setTwitchViewersWeek(?int $twitchViewersWeek): self
   	{
   		$this->twitchViewersWeek = $twitchViewersWeek;

   		return $this;
   	}

    public function getRedditTotalMembers(): ?int
   	{
   		return $this->redditTotalMembers;
   	}

    public function setRedditTotalMembers(?int $redditTotalMembers): self
   	{
   		$this->redditTotalMembers = $redditTotalMembers;

   		return $this;
   	}

    public function getDiscordTotalMembers(): ?int
   	{
   		return $this->discordTotalMembers;
   	}

    public function setDiscordTotalMembers(?int $discordTotalMembers): self
   	{
   		$this->discordTotalMembers = $discordTotalMembers;

   		return $this;
   	}

    public function getRedditPostCountWeek(): ?int
   	{
   		return $this->redditPostCountWeek;
   	}

    public function setRedditPostCountWeek(?int $redditPostCountWeek): self
   	{
   		$this->redditPostCountWeek = $redditPostCountWeek;

   		return $this;
   	}

    public function getTwitchMainLangWeek(): ?string
   	{
   		return $this->twitchMainLangWeek;
   	}

    public function setTwitchMainLangWeek(?string $twitchMainLangWeek): self
   	{
   		$this->twitchMainLangWeek = $twitchMainLangWeek;

   		return $this;
   	}

    public function isHidden(): ?bool
    {
        return $this->hidden;
    }

    public function setHidden(bool $hidden): self
    {
        $this->hidden = $hidden;

        return $this;
    }

    public function getTwitchFollowers(): ?int
    {
        return $this->twitchFollowers;
    }

    public function setTwitchFollowers(?int $twitchFollowers): self
    {
        $this->twitchFollowers = $twitchFollowers;

        return $this;
    }
}
