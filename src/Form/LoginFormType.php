<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class LoginFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Username',
                'constraints' => [
                    new NotBlank(
                        array('message' => User::VALID_USERNAME_ERROR)
                    )
                ],
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'username'
                ]
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Password',
                'required' => false,
                'constraints' => [
                    new NotBlank(
                        array('message' => User::VALID_PASSWORD_ERROR)
                    )
                ],
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'password'
                ]
            ])
            ->add('login', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-lg btn-info btn-block'
                ]
            ]);
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'data_class' => User::class,
            'csrf_field_name' => 'csrf_token',
            'csrf_token_id' => 'authenticate'
        ]);
    }
}
