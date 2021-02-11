<?php


namespace App\Form;


use App\Entity\Profile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('bio', TextareaType::class, [
                'label' => 'bio',
                'attr' => [
                    'class' => 'form-control'

                ]
            ])
            ->add('city', TextType::class, [
                'label' => 'city',
                'attr' => [
                    'class' => 'form-control'

                ]
            ])
            ->add('register', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-lg btn-info btn-block'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Profile::class,
        ]);
    }
}