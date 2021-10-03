<?php

namespace PN\Bundle\CMSBundle\Entity\Translation;

use Doctrine\ORM\Mapping as ORM;
use PN\Bundle\CMSBundle\Entity\Partner;
use PN\LocaleBundle\Entity\Language;
use VM5\EntityTranslationsBundle\Model\EditableTranslation;
use PN\LocaleBundle\Model\TranslationEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="partner_translations")
 */
class PartnerTranslation extends TranslationEntity implements EditableTranslation {

    /**
     * @var string
     * @ORM\Column(name="title", type="string", length=50)
     */
    protected $title;

    /**
     * @var
     * @ORM\Column(name="review", type="text", nullable=true)
     */
    protected $review;

    /**
     * @var
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="PN\Bundle\CMSBundle\Entity\Partner", inversedBy="translations")
     */
    protected $translatable;

    /**
     * @var Language
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="PN\LocaleBundle\Entity\Language")
     */
    protected $language;

    /**
     * Set title
     *
     * @param string $title
     *
     * @return PartnerTranslation
     */
    public function setTitle($title) {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    public function getReview(): ?string
    {
        return $this->review;
    }

    public function setReview(?string $review): self
    {
        $this->review = $review;

        return $this;
    }

}
