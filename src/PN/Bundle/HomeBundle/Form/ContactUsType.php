<?php

namespace PN\Bundle\HomeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class ContactUsType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Your Name *',
                "attr" => [
                    'placeholder' => 'Name *',
                    'class' => 'input-style-1'
                ],
                'constraints' => [
                    new NotBlank(["message" => "Please provide your name"]),
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email Address *',
                "attr" => [
                    'placeholder' => 'Email Address *',
                    'class' => 'input-style-1'
                ],
                'constraints' => [
                    new NotBlank(["message" => "Please provide a valid email"]),
                    new Email(["message" => "Your email doesn't seems to be valid"]),
                ]
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'Comment *',
                "attr" => [
                    'placeholder' => 'Comment *',
                    'class' => 'input-style-1',
                    "rows" => 6,
                ],
                'constraints' => [
                    new NotBlank(["message" => "Please provide a comment"]),
                ]
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'error_bubbling' => true
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'contact_form';
    }

}
