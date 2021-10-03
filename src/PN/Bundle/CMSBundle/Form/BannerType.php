<?php

namespace PN\Bundle\CMSBundle\Form;

use PN\Bundle\CMSBundle\Entity\Banner;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use VM5\EntityTranslationsBundle\Form\Type\TranslationsType;
use PN\Bundle\CMSBundle\Form\Translation\BannerTranslationType;
use PN\MediaBundle\Form\SingleImageType;

class BannerType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title')
            ->add('placement', ChoiceType::class, array(
                'choices' => Banner::$placements,
            ))
            ->add('tarteb', IntegerType::class, [
                "label" => "Sort No.",
                'required' => false,
            ])
            ->add('publish')
            ->add('actionButton', TextType::class, [
                "required" => false,
                "label" => "Action Button Name",
            ])
            ->add('url', UrlType::class, array('required' => false))
            ->add('text', TextareaType::class, array('label' => 'Banner Text', 'required' => false))
            ->add('openType', NULL, array('label' => 'Open new tab'))
            ->add('image', SingleImageType::class, ["mapped" => false])
            ->add('translations', TranslationsType::class, [
                'entry_type' => BannerTranslationType::class,
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
            'data_class' => 'PN\Bundle\CMSBundle\Entity\Banner'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'pn_bundle_cmsbundle_banner';
    }

}
