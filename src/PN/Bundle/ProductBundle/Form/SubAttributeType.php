<?php

namespace PN\Bundle\ProductBundle\Form;

use Doctrine\ORM\EntityManagerInterface;
use PN\Bundle\ProductBundle\Entity\SubAttribute;
use PN\Bundle\ProductBundle\Form\Translation\SubAttributeTranslationType;
use PN\LocaleBundle\Entity\Language;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use VM5\EntityTranslationsBundle\Form\Type\TranslationsType;

class SubAttributeType extends AbstractType
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $languages = $this->em->getRepository(Language::class)->findAll();
        $entryLanguageOptions = [
            "en" => ["required" => true],
        ];
        foreach ($languages as $language) {
            $entryLanguageOptions[$language->getLocale()] = ["required" => true];
        }

        $builder
            ->add('title',TextType::class,[
                "label"=>"Add New Attribute"
            ])
            ->add('translations', TranslationsType::class, [
                'entry_type' => SubAttributeTranslationType::class,
                //                    'query_builder' => function(EntityRepository $er) {
                //                        return $er->createQueryBuilder('languages')
                //                                ->where("languages.locale = 'fr'");
                //                    }, // optional
                "label" => false,
                'entry_language_options' => $entryLanguageOptions,
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'preSetData'));
        $builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'preSubmit'));
    }

    public function preSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $entity = $event->getData();

    }

    public function preSubmit(FormEvent $event)
    {
        $entity = $event->getData();
        $form = $event->getForm();

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => SubAttribute::class,
            "label"=>false
        ));
    }

}
