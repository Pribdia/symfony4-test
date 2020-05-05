<?php

namespace App\Form;

use App\Entity\Activity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActivityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name',TextType::class, [
                'label' => "Nom de l'activté",
                'required' => true
            ])
            ->add('start',DateType::class, [
                'label' => "Date de début",
                'required' => true
            ])
            ->add('end',DateType::class, [
                'label' => "Date de fin",
                'required' => true
            ])
            ->add('minAge',IntegerType::class, [
                'label' => "Age minimum",
                'required' => true
            ])
            ->add('nbParticipant',IntegerType::class, [
                'label' => "Nombre de participants",
                'required' => true
            ])
            ->add('send',SubmitType::class,[
                'label' => "Ajouter une activité"
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Activity::class,
        ]);
    }
}
