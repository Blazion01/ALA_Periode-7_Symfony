<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use App\Entity\Profile;

class WagFiltersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('country', EntityType::class, [
                'class' => Profile::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->select('p.country')
                        ->distinct()
                        ->orderBy('p.country', 'ASC');
                },
                'choice_label' => 'country',
                'expanded' => true,
                'required' =>- false,
            ])
            ->add('province', EntityType::class, [
                'class' => Profile::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->select('p.province')
                        ->distinct()
                        ->orderBy('p.province', 'ASC');
                },
                'choice_label' => 'province',
                'expanded' => true,
                'required' =>- false,
            ])
            ->add('city', EntityType::class, [
                'class' => Profile::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->select('p.city')
                        ->distinct()
                        ->orderBy('p.city', 'ASC');
                },
                'choice_label' => 'city',
                'expanded' => true,
                'multiple' => true,
                'required' =>- false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
