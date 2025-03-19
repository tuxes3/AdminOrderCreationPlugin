<?php

declare(strict_types=1);

namespace Sylius\AdminOrderCreationPlugin\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

#[AsEntityAutocompleteField(
    alias: 'sylius_admin_customer',
    route: 'sylius_admin_entity_autocomplete',
)]
final class CustomerAutocompleteType extends AbstractType
{
    public function __construct(
        private readonly string $customerClass,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => $this->customerClass,
            'choice_label' => 'email',
            'choice_value' => 'id',
            'query_builder' => function (EntityRepository $repository) {
                return $repository->createQueryBuilder('c')
                    ->setMaxResults(20)
                ;
            },
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_admin_customer_autocomplete';
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}
