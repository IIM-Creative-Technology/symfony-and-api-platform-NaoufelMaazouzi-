<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass=TaskRepository::class)
 */
#[ApiResource(normalizationContext: ['groups' => ['task']])]

class Task
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    #[Groups("task")]
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[Groups("task")]
    private $name;

    /**
     * @ORM\Column(type="text")
     */
    #[Groups("task")]
    private $description;

    /**
     * @ORM\Column(type="datetime")
     */
    #[Groups("task")]
    private $deadline;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="tasks")
     * @ORM\JoinColumn(nullable=false)
     */
    #[Groups("task")]
    private $client;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    #[Groups("task")]
    private $realisationDate;

    /**
     * @ORM\OneToMany(targetEntity=Evils::class, mappedBy="task", cascade={"persist"})
     */
    #[Groups("task")]
    private $evils;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="tasks", cascade={"persist"})
     */
    #[Groups("task")]
    private $superHero;

    /**
     * @ORM\ManyToOne(targetEntity=Priority::class, inversedBy="tasks")
     * @ORM\JoinColumn(nullable=false)
     */
    #[Groups("task")]
    private $priority;

    /**
     * @ORM\ManyToOne(targetEntity=Status::class, inversedBy="tasks", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     */
    #[Groups("task")]
    public $status;

    public function __construct()
    {
        $this->evils = new ArrayCollection();
        $this->superHero = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDeadline(): ?\DateTimeInterface
    {
        return $this->deadline;
    }

    public function setDeadline(\DateTimeInterface $deadline): self
    {
        $this->deadline = $deadline;

        return $this;
    }

    public function getClient(): ?User
    {
        return $this->client;
    }

    public function setClient(?User $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getRealisationDate(): ?\DateTimeInterface
    {
        return $this->realisationDate;
    }

    public function setRealisationDate(?\DateTimeInterface $realisationDate): self
    {
        $this->realisationDate = $realisationDate;

        return $this;
    }

    /**
     * @return Collection|Evils[]
     */
    public function getEvils(): Collection
    {
        return $this->evils;
    }

    public function addEvil(Evils $evil): self
    {
        $this->evils->add($evil);
        // $this->evils[] = $evil;
        // $this->evils->add($evil);
        $evil->setTask($this);

        return $this;
    }

    public function removeEvil(Evils $evil): self
    {
        if ($this->evils->removeElement($evil)) {
            // set the owning side to null (unless already changed)
            if ($evil->getTask() === $this) {
                $evil->setTask(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getSuperHero(): Collection
    {
        return $this->superHero;
    }

    public function addSuperHero(User $superHero): self
    {
        $this->superHero->add($superHero);
        $superHero->addTask($this);

        return $this;
    }

    public function removeSuperHero(User $superHero): self
    {
        $this->superHero->removeElement($superHero);

        return $this;
    }

    public function getPriority(): ?Priority
    {
        return $this->priority;
    }

    public function setPriority(?Priority $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }
}
