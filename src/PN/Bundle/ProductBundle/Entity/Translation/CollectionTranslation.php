<?php

namespace PN\Bundle\ProductBundle\Entity\Translation;

use Doctrine\ORM\Mapping as ORM;
use PN\Bundle\ProductBundle\Entity\Collection;
use PN\LocaleBundle\Entity\Language;
use PN\LocaleBundle\Model\TranslationEntity;
use VM5\EntityTranslationsBundle\Model\EditableTranslation;

/**
 * @ORM\Entity
 * @ORM\Table(name="collection_translations")
 */
class CollectionTranslation extends TranslationEntity implements EditableTranslation
{

    /**
     * @ORM\Column(name="title", type="string", length=100)
     */
    private $title;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="PN\Bundle\ProductBundle\Entity\Collection", inversedBy="translations")
     */
    protected $translatable;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="PN\LocaleBundle\Entity\Language")
     */
    protected $language;

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

}
