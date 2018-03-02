<?php

namespace App\Form;

use App\Entity\SpecialOffer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SpecialOfferType
 * @package App\Form
 */
class SpecialOfferType extends AbstractType
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * SpecialOfferType constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('country')
            ->add('city', DependentEntityType::class,
                [
                    'depends_on' => 'country',
                    'alias' => 'city_on_country',
                    'placeholder' => '-- Select country --',
                    'required' => false,
                    'invalid_message' => 'City is not valid'
                ]
            )
            ->add('hotel', DependentEntityType::class,
                [
                    'depends_on' => 'city',
                    'alias' => 'hotel_on_city',
                    'placeholder' => '-- Select city --',
                    'required' => false,
                    'invalid_message' => 'Hotel is not valid'
                ]
            )
            ->add('discount', DiscountType::class)
            ->add('isActive', ChoiceType::class, ['choices' => ['Нет' => false, 'Да' => true]])
            ->add('submit', SubmitType::class)
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SpecialOffer::class,
            'invalid_message' => 'Special offer is not valid'
        ]);
    }
}
