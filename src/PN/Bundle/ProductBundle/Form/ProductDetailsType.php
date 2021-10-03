<?php

namespace PN\Bundle\ProductBundle\Form;

use Doctrine\ORM\EntityManagerInterface;
use PN\Bundle\ProductBundle\Entity\ProductDetails;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProductDetailsType extends AbstractType
{

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
                $builder
                   ->add('tearSheet', FileType::class, [
                        "mapped"=>false,
                        "required"=>false,
                        "attr" => [
                            "class" => "form-control",
                            "accept" => ".doc,.docx,application/pdf, application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document",
                        ],
                        'constraints' => [
                            new File([
                                "maxSize" => "3M",
                                "mimeTypes" => [
                                    'application/msword',
                                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                    'application/pdf',
                                ],
                            ]),
                        ],
                    ]);
    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ProductDetails::class,
            "error_bubbling" => true,
        ));
    }
}
