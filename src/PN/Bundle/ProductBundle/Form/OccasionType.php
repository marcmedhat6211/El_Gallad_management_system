<?php

namespace PN\Bundle\ProductBundle\Form;

use PN\Bundle\ProductBundle\Entity\Occasion;
use PN\Bundle\ProductBundle\Form\Translation\OccasionTranslationType;
use PN\ContentBundle\Form\Model\PostTypeModel;
use PN\ContentBundle\Form\PostType;
use PN\SeoBundle\Form\SeoType;
use PN\Utils\Date;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use VM5\EntityTranslationsBundle\Form\Type\TranslationsType;

class OccasionType extends AbstractType
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
            ->add('startDate', TextType::class, [
                "label" => "Start Date",
                "required" => true,
                'attr' => ['data-date-format' => 'dd/mm/yyyy', "class" => "datepicker-future"],
            ])
            ->add('endDate', TextType::class, [
                "label" => "End Date",
                "required" => true,
                'attr' => ['data-date-format' => 'dd/mm/yyyy', "class" => "datepicker-future"],
                "constraints" => [
                    new GreaterThanOrEqual(["propertyPath" => "parent.all[startDate].data"]),
                ],
            ])
            ->add('active', CheckboxType::class, [
                "required" => false,
                "label" => "Active",
            ])
            ->add('translations', TranslationsType::class, [
                'entry_type' => OccasionTranslationType::class,
                "label" => false,
                'entry_language_options' => [
                    'en' => [
                        'required' => true,
                    ],
                ],
            ]);

        $builder->get('startDate')
            ->addModelTransformer(new CallbackTransformer(
                    function ($date) {
                        if ($date == null) {
                            return null;
                        }

                        // transform the DateTime to a string
                        return $date->format('d/m/Y');
                    }, function ($date) {
                    // transform the string back to DateTime
                    if ($date) {
                        return Date::convertDateFormatToDateTime($date, Date::DATE_FORMAT3);
                    }

                    return null;
                })
            );
        $builder->get('endDate')
            ->addModelTransformer(new CallbackTransformer(
                    function ($date) {
                        if ($date == null) {
                            return null;
                        }

                        // transform the DateTime to a string
                        return $date->format('d/m/Y');
                    }, function ($date) {
                    // transform the string back to DateTime
                    if ($date) {
                        return Date::convertDateFormatToDateTime($date, Date::DATE_FORMAT3);
                    }

                    return null;
                })
            );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Occasion::class,
        ));
    }

}
