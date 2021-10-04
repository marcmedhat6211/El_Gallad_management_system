<?php

namespace PN\Bundle\ProductBundle\Form;

use PN\Bundle\ProductBundle\Entity\Category;
use PN\Bundle\ProductBundle\Form\Translation\CategoryTranslationType;
use PN\ContentBundle\Form\Model\PostTypeModel;
use PN\ContentBundle\Form\PostType;
use PN\MediaBundle\Form\SingleImageType;
use PN\SeoBundle\Form\SeoType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Image;
use VM5\EntityTranslationsBundle\Form\Type\TranslationsType;

class CategoryType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
//        $postTypeModel = new PostTypeModel();
//        $postTypeModel->add("description", "Description", ["required" => false]);

        $builder
            ->add('title')
            ->add('seo', SeoType::class)
//            ->add('image', SingleImageType::class, ["mapped" => false])
//            ->add('tarteb', IntegerType::class, [
//                "label" => "Sort No.",
//                'required' => false,
//            ])
            ->add('translations', TranslationsType::class, [
                'entry_type' => CategoryTranslationType::class,
                //                    'query_builder' => function(EntityRepository $er) {
                //                        return $er->createQueryBuilder('languages')
                //                                ->where("languages.locale = 'fr'");
                //                    }, // optional
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
            'data_class' => Category::class,
        ));
    }
}
