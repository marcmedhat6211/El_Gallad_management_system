<?php

namespace PN\Bundle\ProductBundle\Entity\Translation;

use Doctrine\ORM\Mapping as ORM;
use PN\Bundle\MediaBundle\Entity\Document;
use PN\Bundle\ProductBundle\Entity\Product;
use PN\LocaleBundle\Entity\Language;
use VM5\EntityTranslationsBundle\Model\EditableTranslation;
use PN\LocaleBundle\Model\TranslationEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="product_translations")
 */
class ProductTranslation extends TranslationEntity implements EditableTranslation
{
    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    protected $title;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="PN\Bundle\ProductBundle\Entity\Product", inversedBy="translations")
     */
    protected $translatable;

    /**
     * @var Language
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="PN\LocaleBundle\Entity\Language")
     */
    protected $language;

    public function getId(): ?int
    {
        return $this->translatable->getId();
    }

    public function setTitle($title) {
        $this->title = $title;

        return $this;
    }
    public function getTitle() {
        return $this->title;
    }
}
