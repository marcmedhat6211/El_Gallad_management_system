<?php

namespace PN\Bundle\CMSBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use VM5\EntityTranslationsBundle\Form\Type\TranslationsType;
use PN\Bundle\CMSBundle\Form\Translation\FaqTranslationType;

class FaqType extends AbstractType {

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('question')
                ->add('answer', TextareaType::class, ['attr' => ['rows' => 5]])
                ->add('translations', TranslationsType::class, [
                    'entry_type' => FaqTranslationType::class,
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
            'data_class' => 'PN\Bundle\CMSBundle\Entity\Faq'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix() {
        return 'pn_bundle_cmsbundle_faq';
    }

}
