<?php

namespace PN\Bundle\CMSBundle\Entity\Translation;

use Doctrine\ORM\Mapping as ORM;
use VM5\EntityTranslationsBundle\Model\EditableTranslation;
use PN\LocaleBundle\Model\TranslationEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="faq_translations")
 */
class FaqTranslation extends TranslationEntity implements EditableTranslation {

    /**
     * @var string
     *
     * @ORM\Column(name="question", type="text")
     */
    protected $question;

    /**
     * @var string
     *
     * @ORM\Column(name="answer", type="text")
     */
    protected $answer;

    /**
     * @var 
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="PN\Bundle\CMSBundle\Entity\Faq", inversedBy="translations")
     */
    protected $translatable;

    /**
     * @var Language
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="PN\LocaleBundle\Entity\Language")
     */
    protected $language;


    /**
     * Set question
     *
     * @param string $question
     *
     * @return FaqTranslation
     */
    public function setQuestion($question)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * Get question
     *
     * @return string
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set answer
     *
     * @param string $answer
     *
     * @return FaqTranslation
     */
    public function setAnswer($answer)
    {
        $this->answer = $answer;

        return $this;
    }

    /**
     * Get answer
     *
     * @return string
     */
    public function getAnswer()
    {
        return $this->answer;
    }
}
