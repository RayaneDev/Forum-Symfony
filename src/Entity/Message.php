<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MessageRepository")
 */
class Message
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="messages")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="messagesReceived")
     * @Assert\EqualTo(propertyPath="author", message="Vous ne pouvez pas être le destinataire de votre propre message !")
     */
    private $dest;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    private $destsPseudos;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Answer", mappedBy="message", orphanRemoval=true)
     */
    private $answers;

    public function __construct()
    {
        $this->dest = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->answers = new ArrayCollection();
    }

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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getDest(): Collection
    {
        return $this->dest;
    }

    public function addDest(User $dest): self
    {
        if (!$this->dest->contains($dest)) {
            $this->dest[] = $dest;
        }

        return $this;
    }

    public function removeDest(User $dest): self
    {
        if ($this->dest->contains($dest)) {
            $this->dest->removeElement($dest);
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getDestsPseudos(): ?string
    {
        return $this->destsPseudos; 
    }

    public function setDestsPseudos(string $destsPseudos): self 
    {
        $this->destsPseudos = $destsPseudos; 

        return $this; 
    }

    /**
     * @return Collection|Answer[]
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addAnswer(Answer $answer): self
    {
        if (!$this->answers->contains($answer)) {
            $this->answers[] = $answer;
            $answer->setMessage($this);
        }

        return $this;
    }

    public function removeAnswer(Answer $answer): self
    {
        if ($this->answers->contains($answer)) {
            $this->answers->removeElement($answer);
            // set the owning side to null (unless already changed)
            if ($answer->getMessage() === $this) {
                $answer->setMessage(null);
            }
        }

        return $this;
    }

}
