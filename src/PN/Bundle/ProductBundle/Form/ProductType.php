<?php

namespace PN\Bundle\ProductBundle\Form;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PN\Bundle\ProductBundle\Entity\Category;
use PN\Bundle\ProductBundle\Entity\Product;
use PN\Bundle\ProductBundle\Entity\ProductPrice;
use PN\Bundle\ProductBundle\Form\Translation\ProductTranslationType;
use PN\ContentBundle\Form\Model\PostTypeModel;
use PN\ContentBundle\Form\PostType;
use PN\SeoBundle\Form\SeoType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use VM5\EntityTranslationsBundle\Form\Type\TranslationsType;

class ProductType extends AbstractType
{

    private $em;
    private $session;
    private $product;

    public function __construct(EntityManagerInterface $em, SessionInterface $session)
    {
        $this->em = $em;
        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->product = $builder->getData();
//        $postTypeModel = new PostTypeModel();
//        $postTypeModel->add("description", "Description", [
//            "required" => false,
//        ]);
//        $postTypeModel->add("brief", "Brief");
        $builder
            ->add('title')
            ->add('serial')
            ->add('sku', TextType::class, [
                "label" => "SKU",
                "required" => true,
            ])
            ->add('price')
            ->add('category', EntityType::class, [
                'required' => true,
                'placeholder' => 'Choose an option',
                'class' => Category::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->where('c.deleted IS NULL')
                        ->orderBy('c.id', 'DESC');
                },
            ])
//            ->add('tag', TextType::class, [
//                "label" => "Search Terms",
//                "required" => false,
//            ])
//            ->add('publish', CheckboxType::class, [
//                "required" => false,
//                "label" => "Published",
//            ])
//            ->add('featured')
//            ->add('newArrival', CheckboxType::class, [
//                "required" => false,
//                "label" => "New Arrival",
//            ])
            ->add('seo', SeoType::class)
//            ->add('post', PostType::class, [
//                "attributes" => $postTypeModel,
//                "required" => false,
//            ])
//            ->add('details', ProductDetailsType::class)
            ->add('translations', TranslationsType::class, [
                'entry_type' => ProductTranslationType::class,
                "label" => false,
                'entry_language_options' => [
                    'en' => [
                        'required' => true,
                    ],
                ],
            ]);
        //        $builder->get('category')->addModelTransformer(new CategoryTransformer($this->em));
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData']);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    public function onPreSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $entity = $event->getData();
        $this->getSubAttributesField($form, $entity, $entity->getCategory());
    }


    public function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $entity = $event->getData();

        $category = $this->em->getRepository(Category::class)->find($entity["category"]);
        $this->getSubAttributesField($form, $this->product, $category);
    }


    private function getSubAttributesField(FormInterface $form, ?Product $product, ?Category $category)
    {
        $form->add('subAttributes', ProductHasAttributeType::class, array(
            "mapped" => false,
            "label" => false,
//            'category' => $category,
            'product' => $product,
        ));
    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Product::class,
        ));
    }

}
