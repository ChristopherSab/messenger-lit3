<?php


namespace App\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ChatFormType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('message', TextType::class, ['label' => 'Message:',
            'attr' => [
                'class' => 'form-control',
                'id' => 'content',
                'placeholder' => 'Type Your Message'
            ]])


            ->add('image', FileType::class, ['label' => 'Upload File>>>', 'mapped' => false,
                'required' => false,
            ]);
    }

}