<?php

namespace PN\Bundle\CMSBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use PN\SeoBundle\Form\SeoType;
use PN\ContentBundle\Form\PostType;
use VM5\EntityTranslationsBundle\Form\Type\TranslationsType;
use PN\Bundle\CMSBundle\Form\Translation\DynamicPageTranslationType;

class DynamicPageType extends AbstractType {

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('title')
                ->add('seo', SeoType::class)
                ->add('post', PostType::class)
                ->add('translations', TranslationsType::class, [
                    'entry_type' => DynamicPageTranslationType::class,
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
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'PN\Bundle\CMSBundle\Entity\DynamicPage'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix() {
        return 'pn_bundle_cmsbundle_dynamicpage';
    }

}
