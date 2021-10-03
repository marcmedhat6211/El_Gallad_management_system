<?php

namespace PN\Bundle\ProductBundle\Form\Filter;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PN\Bundle\ProductBundle\Entity\Category;
use PN\Bundle\ProductBundle\Entity\Collection;
use PN\Bundle\ProductBundle\Entity\Occasion;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductFilterType extends AbstractType
{

    private $em;

    public function __construct(EntityManagerInterface $em )
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->setMethod("get")
            ->add('str', TextType::class, [
                "required" => false,
                "label" => "Search",
                "attr" => [
                    "placeholder" => "Title, SKU",
                    "autocomplete" => "off",
                ],
            ])
            ->add('category', EntityType::class, [
                "required" => false,
                "multiple" => true,
                'placeholder' => 'Choose an option',
                'class' => Category::class,
                "attr" => [
                    "class" => "select-search",
                    'data-placeholder' => 'Choose One or More',
                ],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->where('c.deleted IS NULL')
                        ->orderBy('c.id', 'DESC');
                },
            ])
//            ->add('collection', EntityType::class, [
//                "required" => false,
//                'placeholder' => 'All',
//                'class' => Collection::class,
//                "attr" => [
//                    "class" => "select-search",
//                ],
//                'choice_label' => function ($collection) {
//                    $label = $collection->getTitle();
//                    if (!$collection->getPublish()) {
//                        $label .= " (Not publish)";
//                    }
//
//                    return $label;
//                },
//                'query_builder' => function (EntityRepository $er) {
//                    return $er->createQueryBuilder('t')
//                        ->where('t.deleted IS NULL')
//                        ->orderBy('t.id', 'DESC');
//                },
//            ])
//            ->add('occasion', EntityType::class, [
//                "required" => false,
//                'placeholder' => 'All',
//                'class' => Occasion::class,
//                "attr" => [
//                    "class" => "select-search",
//                ],
//                'choice_label' => function ($occasion) {
//                    $label = $occasion->getTitle();
//                    if (!$occasion->getActive()) {
//                        $label .= " (Not active)";
//                    }
//
//                    return $label;
//                },
//                'query_builder' => function (EntityRepository $er) {
//                    return $er->createQueryBuilder('t')
//                        ->where('t.deleted IS NULL')
//                        ->orderBy('t.id', 'DESC');
//                },
//            ])
            ->add('newArrival', ChoiceType::class, [
                "required" => false,
                "label" => "New Arrival",
                "placeholder" => "All",
                "choices" => [
                    "Yes" => true,
                    "No" => false,
                ],
                "attr" => [
                    "class" => "select-search",
                ],
            ])
            ->add('publish', ChoiceType::class, [
                "required" => false,
                "label" => "Published",
                "placeholder" => "All",
                "choices" => [
                    "Yes" => true,
                    "No" => false,
                ],
                "attr" => [
                    "class" => "select-search",
                ],
            ])
            ->add('featured', ChoiceType::class, [
                "required" => false,
                "placeholder" => "All",
                "choices" => [
                    "Yes" => true,
                    "No" => false,
                ],
                "attr" => [
                    "class" => "select-search",
                ],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
