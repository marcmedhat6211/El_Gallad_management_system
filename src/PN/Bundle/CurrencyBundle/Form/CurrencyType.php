<?php

namespace PN\Bundle\CurrencyBundle\Form;

use PN\Bundle\CurrencyBundle\Entity\Currency;
use PN\Bundle\CurrencyBundle\Form\Translation\CurrencyTranslationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use VM5\EntityTranslationsBundle\Form\Type\TranslationsType;

class CurrencyType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', \Symfony\Component\Form\Extension\Core\Type\CurrencyType::class, [
                "required" => true,
                "placeholder"=> "Choose an option",
                "attr" => ["class" => 'select-search'],
            ])
            ->add('title')
            ->add('symbol')
            ->add('translations', TranslationsType::class, [
                'entry_type' => CurrencyTranslationType::class,
                "label" => false,
                'entry_language_options' => [
                    'en' => [
                        'required' => true,
                    ],
                ],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Currency::class,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'pn_bundle_CurrencyBundle_Currency';
    }

}
