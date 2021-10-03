<?php

namespace PN\Bundle\ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SearchKeyword
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="search_keyword")
 * @ORM\Entity(repositoryClass="PN\Bundle\ProductBundle\Repository\SearchKeywordsRepository")
 */
class SearchKeyword {
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="keyword", type="string", length=150)
     */
    protected $keyword;

    /**
     * @var string
     *
     * @ORM\Column(name="canonical_keyword", type="string", length=150)
     */
    protected $canonicalKeyword;


    /**
     * @var string
     *
     * @ORM\Column(name="ip", type="string", length=50)
     */
    protected $ip;

    /**
     * @var string
     *
     * @ORM\Column(name="created", type="datetime")
     */
    protected $created;

    /**
     * Now we tell doctrine that before we persist or update we call the updatedTimestamps() function.
     *
     * @ORM\PrePersist
     */
    public function updatedTimestamps() {
        $this->setCreated(new \DateTime(date('Y-m-d H:i:s')));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKeyword(): ?string
    {
        return $this->keyword;
    }

    public function setKeyword(string $keyword): self
    {
        $this->keyword = $keyword;

        return $this;
    }

    public function getCanonicalKeyword(): ?string
    {
        return $this->canonicalKeyword;
    }

    public function setCanonicalKeyword(string $canonicalKeyword): self
    {
        $this->canonicalKeyword = $canonicalKeyword;

        return $this;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

}
