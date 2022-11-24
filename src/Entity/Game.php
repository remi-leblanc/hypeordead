<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private $name;

    #[ORM\Column(type: 'datetime')]
    private $created_at;

    #[ORM\OneToMany(targetEntity: Record::class, mappedBy: 'Game')]
    private $records;

    #[ORM\Column(type: 'array')]
    private $subredditName = [];

    #[ORM\Column(type: 'array')]
    private $discordId = [];

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private $twitchId;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $image;

    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'games')]
    private $tags;

    #[ORM\OneToMany(mappedBy: 'game', targetEntity: HourlyRecord::class)]
    private Collection $hourlyRecords;

    #[ORM\OneToMany(mappedBy: 'game', targetEntity: DailyRecord::class)]
    private Collection $dailyRecords;

    public function __construct()
    {
        $this->records = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->hourlyRecords = new ArrayCollection();
        $this->dailyRecords = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|Record[]
     */
    public function getRecords(): Collection
    {
        return $this->records;
    }

    public function addRecord(Record $record): self
    {
        if (!$this->records->contains($record)) {
            $this->records[] = $record;
            $record->setGame($this);
        }

        return $this;
    }

    public function removeRecord(Record $record): self
    {
        if ($this->records->contains($record)) {
            $this->records->removeElement($record);
            // set the owning side to null (unless already changed)
            if ($record->getGame() === $this) {
                $record->setGame(null);
            }
        }

        return $this;
    }

    public function getSubredditName(): ?array
    {
        return $this->subredditName;
    }

    public function setSubredditName(?array $subredditName): self
    {
        $this->subredditName = $subredditName;

        return $this;
    }

    public function getDiscordId(): ?array
    {
        return $this->discordId;
    }
	
    public function setDiscordId(?array $discordId): self
    {
        $this->discordId = $discordId;

        return $this;
    }

    public function getTwitchId(): ?string
    {
        return $this->twitchId;
    }

    public function setTwitchId(?string $twitchId): self
    {
        $this->twitchId = $twitchId;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    /**
     * @return Collection<int, HourlyRecord>
     */
    public function getHourlyRecords(): Collection
    {
        return $this->hourlyRecords;
    }

    public function addHourlyRecord(HourlyRecord $hourlyRecord): self
    {
        if (!$this->hourlyRecords->contains($hourlyRecord)) {
            $this->hourlyRecords->add($hourlyRecord);
            $hourlyRecord->setGame($this);
        }

        return $this;
    }

    public function removeHourlyRecord(HourlyRecord $hourlyRecord): self
    {
        if ($this->hourlyRecords->removeElement($hourlyRecord)) {
            // set the owning side to null (unless already changed)
            if ($hourlyRecord->getGame() === $this) {
                $hourlyRecord->setGame(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, DailyRecord>
     */
    public function getDailyRecords(): Collection
    {
        return $this->dailyRecords;
    }

    public function addDailyRecord(DailyRecord $dailyRecord): self
    {
        if (!$this->dailyRecords->contains($dailyRecord)) {
            $this->dailyRecords->add($dailyRecord);
            $dailyRecord->setGame($this);
        }

        return $this;
    }

    public function removeDailyRecord(DailyRecord $dailyRecord): self
    {
        if ($this->dailyRecords->removeElement($dailyRecord)) {
            // set the owning side to null (unless already changed)
            if ($dailyRecord->getGame() === $this) {
                $dailyRecord->setGame(null);
            }
        }

        return $this;
    }

}
