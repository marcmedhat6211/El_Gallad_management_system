<?php

namespace PN\Bundle\CMSBundle\Form;

use Doctrine\ORM\EntityRepository;
use PN\Bundle\CMSBundle\Entity\Service;
use PN\SeoBundle\Form\SeoType;
use PN\Utils\Date;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use VM5\EntityTranslationsBundle\Form\Type\TranslationsType;
use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use PN\Bundle\CMSBundle\Form\Translation\ProjectTranslationType;
use PN\ContentBundle\Form\Model\PostTypeModel;
use PN\Bundle\ProductBundle\Entity\Product;
use PN\ContentBundle\Form\PostType;

class ProjectType extends AbstractType {

    private $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $postTypeModel = new PostTypeModel();
        $postTypeModel->add("description", "Description");
        $builder
            ->add('title')
            ->add('interiorDesignerName')
            ->add('projectScope')
            ->add('client')
            ->add('date', TextType::class, [
                "required" => false,
                "attr" => [
                    "class" => "datepicker",
                ],
            ])
            ->add('publish')
//                ->add('featured')
            ->add('tarteb', IntegerType::class, [
                "label" => "Sort No.",
                'required' => false,
            ])
            ->add('seo', SeoType::class)
            ->add('post', PostType::class, [
                "attributes" => $postTypeModel
            ])
            ->add('services', EntityType::class, [
                "label" => "Services",
                "required" => false,
                "multiple" => true,
                'placeholder' => 'Choose an option',
                'class' => Service::class,
                "attr" => [
                    "class" => "select-search",
                ],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->where('s.deleted IS NULL')
                        ->orderBy('s.id', 'DESC');
                },
            ])
            ->add('products', EntityType::class, [
                "label" => "Products",
                "required" => false,
                "multiple" => true,
                'placeholder' => 'Choose an option',
                'class' => Product::class,
                "attr" => [
                    "class" => "select-search",
                ],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->where('p.deleted IS NULL')
                        ->andWhere('p.publish IS NOT NULL')
                        ->orderBy('p.id', 'DESC');
                },
            ])
            ->add('translations', TranslationsType::class, [
                'entry_type' => ProjectTranslationType::class,
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

        $builder->get('date')
            ->addModelTransformer(new CallbackTransformer(
                    function ($date) {
                        if ($date == null) {
                            return date('d/m/Y');
                        }

                        // transform the DateTime to a string
                        return $date->format('d/m/Y');
                    }, function ($date) {
                    if ($date == null) {
                        $date = date('d/m/Y');
                    }

                    // transform the string back to DateTime
                    return Date::convertDateFormatToDateTime($date, Date::DATE_FORMAT3);
                })
            );
//        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData']);
//        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

//    function onPreSubmit(FormEvent $event) {
//        $form = $event->getForm();
//        $data = $event->getData();
//
//        $services = [];
//        if (array_key_exists('relatedProducts', $data)) {
//            foreach ($data['relatedProducts'] as $relatedProduct) {
//                $relatedProducts[] = $this->em->getRepository(Product::class)->find($relatedProduct);
//            }
//        }
//        $this->addRelatedProductElements($form, $relatedProducts);
//    }
//
//    function onPreSetData(FormEvent $event) {
//        $entity = $event->getData();
//        $form = $event->getForm();
//
////        $relatedProduct = $entity->getRelatedProducts();
////        $this->addRelatedProductElements($form, $relatedProduct);
//    }
//
//    protected function addRelatedProductElements(FormInterface $form, $relatedProduct = []) {
//        $form->add('relatedProducts', EntityType::class, [
//            'required' => false,
//            'multiple' => true,
//            'placeholder' => 'Choose an option',
//            'class' => Product::class,
//            'choices' => $relatedProduct,
//            'choice_label' => function ($product) {
//                $title = $product->getTitle();
//                if (!$product->getPublish()) {
//                    $title .= " (Unpublished)";
//                }
//                return $title;
//            },
//            "attr" => [
////                "class" => "select-search"
//            ],
//        ]);
//    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'PN\Bundle\CMSBundle\Entity\Project'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix() {
        return 'pn_bundle_cmsbundle_project';
    }

}
