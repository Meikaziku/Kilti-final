<?php

namespace App\Entity;

use App\Repository\ContentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContentRepository::class)]
class Content
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /*
    ============================
    CONTENT INFORMATION
    ============================
    */

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $url = null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $duration = null;

    // Stats du reportage
    // rating : moyenne des notes des utilisateurs, recalculée à chaque vote (voir RatingService)
    // ratingsCount : nombre de notes reçues

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $views = 0;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $rating = null;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $ratingsCount = 0;

    /*
    ============================
    WORKFLOW STATUS
    ============================
    */

    #[ORM\Column(length: 50)]
    private string $status = 'pending';

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isValidated = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $validatedAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $rejectedReason = null;

    /*
    ============================
    RELATIONS
    ============================
    */

    // Creator user (owner of content)
    #[ORM\ManyToOne(targetEntity: Users::class, inversedBy: 'contents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Users $user = null;

    // Admin/moderator validator
    #[ORM\ManyToOne(targetEntity: Users::class, inversedBy: 'validatedContents')]
    private ?Users $validatedBy = null;

    #[ORM\ManyToOne(inversedBy: 'contents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ContentCategory $category = null;

    /**
     * @var Collection<int, Watchlist>
     */
    #[ORM\OneToMany(targetEntity: Watchlist::class, mappedBy: 'content')]
    private Collection $watchlists;

    /**
     * @var Collection<int, Rating>
     */
    #[ORM\OneToMany(targetEntity: Rating::class, mappedBy: 'content', orphanRemoval: true, cascade: ['remove'])]
    private Collection $ratings;

    public function __construct()
    {
        $this->watchlists = new ArrayCollection();
        $this->ratings = new ArrayCollection();
    }

    /* ============================
       GETTERS & SETTERS
    ============================ */

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;
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

    // Construit l'URL publique de l'image à partir du nom de fichier stocké en base
    public function getImagePath(): string
    {
        if (!$this->image) {
            return '';
        }

        return '/uploads/' . $this->image;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): self
    {
        $this->duration = $duration;
        return $this;
    }

    public function getViews(): int
    {
        return $this->views;
    }

    public function setViews(int $views): self
    {
        $this->views = $views;
        return $this;
    }

    public function incrementViews(): self
    {
        $this->views++;
        return $this;
    }

    public function getRating(): ?float
    {
        return $this->rating;
    }

    public function setRating(?float $rating): self
    {
        $this->rating = $rating;
        return $this;
    }

    public function getRatingsCount(): int
    {
        return $this->ratingsCount;
    }

    public function setRatingsCount(int $ratingsCount): self
    {
        $this->ratingsCount = $ratingsCount;
        return $this;
    }

    public function getRatings(): Collection
    {
        return $this->ratings;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function isValidated(): bool
    {
        return $this->isValidated;
    }

    public function setIsValidated(bool $isValidated): self
    {
        $this->isValidated = $isValidated;
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

    public function getValidatedAt(): ?\DateTimeImmutable
    {
        return $this->validatedAt;
    }

    public function setValidatedAt(?\DateTimeImmutable $validatedAt): self
    {
        $this->validatedAt = $validatedAt;
        return $this;
    }

    public function getRejectedReason(): ?string
    {
        return $this->rejectedReason;
    }

    public function setRejectedReason(?string $rejectedReason): self
    {
        $this->rejectedReason = $rejectedReason;
        return $this;
    }

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(?Users $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getValidatedBy(): ?Users
    {
        return $this->validatedBy;
    }

    public function setValidatedBy(?Users $validatedBy): self
    {
        $this->validatedBy = $validatedBy;
        return $this;
    }

    public function getCategory(): ?ContentCategory
    {
        return $this->category;
    }

    public function setCategory(?ContentCategory $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, Watchlist>
     */
    public function getWatchlists(): Collection
    {
        return $this->watchlists;
    }

    public function addWatchlist(Watchlist $watchlist): static
    {
        if (!$this->watchlists->contains($watchlist)) {
            $this->watchlists->add($watchlist);
            $watchlist->setContent($this);
        }

        return $this;
    }

    public function removeWatchlist(Watchlist $watchlist): static
    {
        if ($this->watchlists->removeElement($watchlist)) {
            if ($watchlist->getContent() === $this) {
                $watchlist->setContent(null);
            }
        }

        return $this;
    }
}
