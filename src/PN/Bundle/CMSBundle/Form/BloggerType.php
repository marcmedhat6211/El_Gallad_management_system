<?php

namespace PN\Bundle\CMSBundle\Form;

use Doctrine\ORM\EntityRepository;
use PN\Utils\Date;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use VM5\EntityTranslationsBundle\Form\Type\TranslationsType;
use PN\Bundle\CMSBundle\Form\Translation\BloggerTranslationType;
use PN\Bundle\CMSBundle\Entity\BloggerTag;
Use PN\ContentBundle\Form\PostType;
use PN\SeoBundle\Form\SeoType;

class BloggerType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $postTypeModel = new \PN\ContentBundle\Form\Model\PostTypeModel();


        $builder->add('title')
            ->add('publish')
            ->add('seo', SeoType::class)
            ->add('date', TextType::class, [
                "required" => false,
                "attr" => [
                    "class" => "datepicker",
                ],
            ])
            ->add('post', PostType::class)
            ->add('translations', TranslationsType::class, [
                'entry_type' => BloggerTranslationType::class,
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
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PN\Bundle\CMSBundle\Entity\Blogger'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'pn_bundle_cmsbundle_blogger';
    }

}
