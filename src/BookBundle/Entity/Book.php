<?php

namespace BookBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="book")
 * @ORM\Entity(repositoryClass="BookBundle\Repository\BookRepository")
 */
class Book
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(max="255")
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(max="255")
     */
    private $author;

    /**
     * @ORM\Column(type="date")
     * @Assert\NotBlank()
     * @Assert\DateTime()
     */
    private $readDate;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\NotNull()
     */
    private $isDownloadAllowed;

    /**
     * @ORM\Column(type="string", nullable=true, length=255)
     * @Assert\Length(max="255")
     */
    private $cover;

    /**
     * @ORM\Column(type="string", nullable=true, length=255)
     * @Assert\Length(max="255")
     */
    private $source;

    public function getId() : ?int
    {
        return $this->id;
    }

    public function getTitle() : ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title) : void
    {
        $this->title = $title;
    }

    public function getAuthor() : ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author) : void
    {
        $this->author = $author;
    }

    public function getReadDate() : ?\DateTime
    {
        return $this->readDate;
    }

    public function setReadDate($readDate) : void
    {
        $this->readDate = $readDate;
    }

    public function getIsDownloadAllowed() : ?bool
    {
        return $this->isDownloadAllowed;
    }

    public function setIsDownloadAllowed(?bool $isDownloadAllowed) : void
    {
        $this->isDownloadAllowed = $isDownloadAllowed;
    }

    public function getCover() : ?string
    {
        return $this->cover;
    }

    public function setCover(?string $cover) : void
    {
        $this->cover = $cover;
    }

    public function getSource() : ?string
    {
        return $this->source;
    }

    public function setSource(?string $source) : void
    {
        $this->source = $source;
    }
}
