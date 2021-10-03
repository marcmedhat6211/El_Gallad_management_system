<?php

namespace PN\Bundle\HomeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\File;

class CareerType extends AbstractType {

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'First Name *',
                "attr" => [
                    'placeholder' => 'First Name *',
                ],
                'constraints' => [
                    new NotBlank(["message" => "Please provide your first name"]),
                ]
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Last Name *',
                "attr" => [
                    'placeholder' => 'Last Name *',
                ],
                'constraints' => [
                    new NotBlank(["message" => "Please provide your last name"]),
                ]
            ])
            ->add('phone', TextType::class, [
                'label' => 'Phone Number *',
                "attr" => [
                    'placeholder' => 'Phone Number *',
                    "class" => 'only-numeric'
                ],
                'constraints' => [
                    new NotBlank(["message" => "Please provide your phone number"]),
                ]
            ])
            ->add('yearOfExperience', IntegerType::class, [
                'label' => 'Years of Experience *',
                "attr" => [
                    'placeholder' => 'Years of Experience *',
                    "class" => 'only-numeric'
                ],
                'constraints' => [
                    new NotBlank(["message" => "Please provide your years of experience"]),
                ]
            ])
            ->add('applyFor', TextType::class, [
                'label' => 'Job Applying For *',
                "attr" => [
                    'placeholder' => 'Job Applying For *',
                ],
                'constraints' => [
                    new NotBlank(["message" => "Please provide your job title"]),
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email Address *',
                "attr" => [
                    'placeholder' => 'Email Address *',
                ],
                'constraints' => [
                    new NotBlank(["message" => "Please provide a valid email"]),
                    new Email(["message" => "Your email doesn't seems to be valid"]),
                ]
            ])
            ->add('coverLetter', TextareaType::class, [
                'label' => 'Cover Letter *',
                "attr" => [
                    'placeholder' => 'Cover Letter *',
                    "rows" => 5
                ],
                'constraints' => [
                    new NotBlank(["message" => "Please provide your cover letter"]),
                ]
            ])
            ->add('resume', FileType::class, [
                'label' => 'Upload Resume *',
                "attr" => [
                    "class"=>"form-control",
                    "accept" => ".doc,.docx,application/pdf, application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                ],
                'constraints' => [
                    new NotBlank(["message" => "Please provide your cover letter"]),
                    new File([
                        "maxSize" => "3M",
                        "mimeTypes" => ['application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/pdf'],
                    ]),
                ]
            ]);;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'error_bubbling' => true
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix() {
        return 'career_form';
    }

}
