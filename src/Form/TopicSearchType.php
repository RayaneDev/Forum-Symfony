<?php

namespace App\Form;

use App\Entity\TopicSearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TopicSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('research', TextType::class, array(
                'label' => False
            ))
            ->add('section', ChoiceType::class, array(
                'choices' => array(
                    'Sujet' => 'title', 
                    'Auteur' => 'author', 
                    'Message' => 'message'
                ), 
                'label' => False
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TopicSearch::class,
        ]);
    }
}
