<?php

namespace PN\Bundle\CMSBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use VM5\EntityTranslationsBundle\Form\Type\TranslationsType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use PN\MediaBundle\Form\SingleImageType;
use PN\Bundle\CMSBundle\Form\Translation\PartnerTranslationType;

class PartnerType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
//                ->add('url', UrlType::class, array('required' => false))
            ->add('image', SingleImageType::class, ["mapped" => false])
            ->add('tarteb', IntegerType::class, [
                "label" => "Sort No.",
                'required' => false,
            ])
            ->add('rating', IntegerType::class, [
                'required' => false,
                'attr' => [
                    'min' => 0,
                    'max' => 5,
                ]
            ])
            ->add('review')
            ->add('publish')
            ->add('translations', TranslationsType::class, [
                'entry_type' => PartnerTranslationType::class,
                "label" => false,
                'entry_language_options' => [
                    'en' => [
                        'required' => true,
                    ]
                ],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PN\Bundle\CMSBundle\Entity\Partner'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'pn_bundle_cmsbundle_partner';
    }

}
