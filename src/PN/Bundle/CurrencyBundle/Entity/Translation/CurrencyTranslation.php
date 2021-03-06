<?php

namespace PN\Bundle\CurrencyBundle\Entity\Translation;

use Doctrine\ORM\Mapping as ORM;
use PN\Bundle\CurrencyBundle\Entity\Currency;
use PN\LocaleBundle\Entity\Language;
use PN\LocaleBundle\Model\TranslationEntity;
use VM5\EntityTranslationsBundle\Model\EditableTranslation;

/**
 * @ORM\Entity
 * @ORM\Table(name="currency_translation")
 */
class CurrencyTranslation extends TranslationEntity implements EditableTranslation
{

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=50)
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(name="symbol", type="string", length=255)
     */
    protected $symbol;

    /**
     * @var
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="PN\Bundle\CurrencyBundle\Entity\Currency", inversedBy="translations")
     */
    protected $translatable;

    /**
     * @var Language
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="PN\LocaleBundle\Entity\Language")
     */
    protected $language;


    /**
     * Set title.
     *
     * @param string $title
     *
     * @return CurrencyTranslation
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set symbol.
     *
     * @param string $symbol
     *
     * @return CurrencyTranslation
     */
    public function setSymbol($symbol)
    {
        $this->symbol = $symbol;

        return $this;
    }

    /**
     * Get symbol.
     *
     * @return string
     */
    public function getSymbol()
    {
        return $this->symbol;
    }

    public function getTranslatable(): ?Currency
    {
        return $this->translatable;
    }

    public function getLanguage(): ?Language
    {
        return $this->language;
    }


}
