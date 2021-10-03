<?php

namespace PN\Bundle\ProductBundle\Form;

use PN\Bundle\ProductBundle\Entity\Attribute;
use PN\Bundle\ProductBundle\Form\Translation\AttributeTranslationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use VM5\EntityTranslationsBundle\Form\Type\TranslationsType;

class AttributeType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $disableTypeField = $options['disableTypeField'];

        if ($disableTypeField == false) {
            $builder->add('type', ChoiceType::class, [
                'choices' => Attribute::$types,
                "attr" => ["class" => "select-search"],
            ]);
        }
        $builder
            ->add('title',TextType::class,[
                "label"=>"Spec"
            ])
            ->add('search')
            ->add('mandatory')
            ->add('tarteb', IntegerType::class, [
                "label" => "Sort No.",
                'required' => false,
                "attr" => ['min' => 0],
            ])
            ->add('translations', TranslationsType::class, [
                'entry_type' => AttributeTranslationType::class,
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
            'data_class' => Attribute::class,
//            "allow_extra_fields" => true,
            "label" => false,
            'disableTypeField' => false,
        ));
    }

}
