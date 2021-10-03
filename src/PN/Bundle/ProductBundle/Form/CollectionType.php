<?php

namespace PN\Bundle\ProductBundle\Form;

use PN\Bundle\ProductBundle\Entity\Collection;
use PN\Bundle\ProductBundle\Form\Translation\CollectionTranslationType;
use PN\ContentBundle\Form\Model\PostTypeModel;
use PN\ContentBundle\Form\PostType;
use PN\MediaBundle\Form\SingleImageType;
use PN\SeoBundle\Form\SeoType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use VM5\EntityTranslationsBundle\Form\Type\TranslationsType;

class CollectionType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $postTypeModel = new PostTypeModel();
        $postTypeModel->add("description", "Description", ["required" => false]);
        $builder
            ->add('title')
            ->add('seo', SeoType::class)
            ->add('post', PostType::class, [
                "attributes" => $postTypeModel,
            ])
//            ->add('image', SingleImageType::class, ["mapped" => false])
            ->add('tarteb', IntegerType::class, [
                "required" => false,
                "label" => "Sort No.",
                'attr' => ['min' => 0],
            ])
            ->add('publish', CheckboxType::class, [
                "required" => false,
                "label" => "Active",
            ])
            ->add('translations', TranslationsType::class, [
                'entry_type' => CollectionTranslationType::class,
                "label" => false,
                'entry_language_options' => [
                    'en' => [
                        'required' => true,
                    ],
                ],
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Collection::class,
        ));
    }

}
