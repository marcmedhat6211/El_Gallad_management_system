<?php

namespace PN\Bundle\CMSBundle\Form;

use PN\Bundle\CMSBundle\Form\Translation\ServiceTranslationType;
use PN\SeoBundle\Form\SeoType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use VM5\EntityTranslationsBundle\Form\Type\TranslationsType;
use PN\ContentBundle\Form\Model\PostTypeModel;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use PN\ContentBundle\Form\PostType;

class ServiceType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $postTypeModel = new PostTypeModel();
        $postTypeModel->add("brief", "Brief");
        $postTypeModel->add("description", "Description");
        $builder->add('title')
            ->add('publish')
            ->add('seo', SeoType::class)
            ->add('tarteb', IntegerType::class, [
                "label" => "Sort No.",
                'required' => false,
            ])
            ->add('contactText', TextareaType::class, [
                'required' => false,
            ])
            ->add('post', PostType::class, [
                "attributes" => $postTypeModel
            ])
            ->add('translations', TranslationsType::class, [
                'entry_type' => ServiceTranslationType::class,
//                    'query_builder' => function(EntityRepository $er) {
//                        return $er->createQueryBuilder('languages')
//                                ->where("languages.locale = 'fr'");
//                    }, // optional
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
            'data_class' => 'PN\Bundle\CMSBundle\Entity\Service'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'pn_bundle_cmsbundle_service';
    }

}
