<?php

namespace BookBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="book")
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
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message = "Field 'title' must be a non-empty string")
     */
    private $title;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message = "Field 'author' must be a non-empty string")
     */
    private $author;

    /**
     * @ORM\Column(type="date")
     * @Assert\NotBlank(message = "Field 'readDate' must be a valid date. Format '2001-12-30'")
     */
    private $readDate;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isDownloadAllowed;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\File(mimeTypes={ "image/jpeg", "image/png" })
     */
    private $cover;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\File(maxSize="5M")
     */
    private $source;

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function setAuthor($author)
    {
        $this->author = $author;
    }

    public function getReadDate()
    {
        return $this->readDate;
    }

    public function setReadDate($readDate)
    {
        $this->readDate = $readDate;
    }

    public function getIsDownloadAllowed()
    {
        return $this->isDownloadAllowed;
    }

    public function setIsDownloadAllowed($isDownloadAllowed)
    {
        $this->isDownloadAllowed = $isDownloadAllowed;
    }

    public function getCover()
    {
        return $this->cover;
    }

    public function setCover($cover)
    {
        $this->cover = $cover;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function setSource($source)
    {
        $this->source = $source;
    }
}
