<?php

namespace App\Form;

use App\Entity\User;
use Karser\Recaptcha3Bundle\Form\Recaptcha3Type;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
// use Gregwar\CaptchaBundle\Type\CaptchaType;

class RegistrationFormType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('email', EmailType::class, [
                "attr" => [
                    "placeholder" => "Enter your email",
                    "class" => "form-control ",
                ],

                'constraints' => [
                    new Email([
                        'message' => 'invalid email address.',
                    ]),
                    new NotBlank([
                        'message' => 'Email field should not be empty.',
                    ]),
                ],

            ])
            ->add('username', TextType::class, ['attr' => ['placeholder' => 'Username']]
            )

            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])

            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'label' => true,
                'options' => ['attr' => ['class' => 'password-field']],
                'attr' => ['autocomplete' => 'new-password'],
                'first_options' => [
                    'label' => 'Password',
                    'placeholder' => 'Password'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
                'required' => true,
                'first_options' => [
                    'label' => 'Password',
                    // 'placeholder' => 'Password'
                ],
                'second_options' => ['label' => 'Repeat Password'],
            ])

//   ->add('captcha_widget', CaptchaType::class, array(
//    'width' => 200,
//     'mapped'=>false,
// ))
// ->add('captcha', CaptchaType::class)

            ->add('captcha', Recaptcha3Type::class, [
                // 'constraints' => new Recaptcha3(),
                'constraints' => new Recaptcha3(['message' => 'There were problems with your captcha. Please try again or contact with support and provide following code(s): {{ errorCodes }}']),

                'action_name' => 'homepage',
                // 'script_nonce_csp' => $nonceCSP,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
