<?php

namespace PN\Bundle\CMSBundle\Form;

use PN\Bundle\CMSBundle\Form\Translation\TeamTranslationType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use VM5\EntityTranslationsBundle\Form\Type\TranslationsType;
use PN\MediaBundle\Form\SingleImageType;

class TeamType extends AbstractType {

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('name')
                ->add('position')
                ->add('image', SingleImageType::class, ["mapped" => false])
//                ->add('shortDesc', TextareaType::class, [
//                    "label" => "Short description",
//                    'attr' => ['rows' => 5]
//                ])
                ->add('tarteb', IntegerType::class, [
                    "label" => "Sort no."
                ])
                ->add('publish')
                ->add('translations', TranslationsType::class, [
                    'entry_type' => TeamTranslationType::class,
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
            'data_class' => 'PN\Bundle\CMSBundle\Entity\Team'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix() {
        return 'pn_bundle_cmsbundle_management_team';
    }

}
