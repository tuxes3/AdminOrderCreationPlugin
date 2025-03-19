<?php

declare(strict_types=1);

namespace Sylius\AdminOrderCreationPlugin\Form\Type;

use Sylius\AdminOrderCreationPlugin\Doctrine\ORM\ProductVariantRepositoryInterface;
use Sylius\Bundle\AdminBundle\Form\Type\ProductVariantAutocompleteType;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\DataMapper\DataMapper;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class OrderItemType extends AbstractResourceType implements DataMapperInterface
{
    /** @var ProductVariantRepositoryInterface */
    private $productVariantRepository;

    private DataMapperInterface $dataMapper;

    public function __construct(
        string $dataClass,
        array $validationGroups,
        ProductVariantRepositoryInterface $productVariantRepository,
    ) {
        parent::__construct($dataClass, $validationGroups);

        $this->productVariantRepository = $productVariantRepository;
        $this->dataMapper = new DataMapper();
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantity', IntegerType::class, [
                'attr' => ['min' => 1],
                'label' => 'sylius.ui.quantity',
                'empty_data' => 1,
            ])
            ->add('variant', ProductVariantAutocompleteType::class, [
                'label' => 'sylius.ui.variant',
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options): void {
                $event
                    ->getForm()
                    ->add('adjustments', CollectionType::class, [
                        'label' => false,
                        'entry_type' => AdjustmentType::class,
                        'entry_options' => [
                            'label' => 'sylius_admin_order_creation.ui.item_discount',
                            'currency' => $options['currency'],
                            'type' => AdjustmentType::ORDER_ITEM_DISCOUNT_ADJUSTMENT,
                        ],
                        'allow_add' => true,
                        'allow_delete' => true,
                        'by_reference' => false,
                        'button_add_label' => 'sylius_admin_order_creation.ui.add_discount',
                    ])
                ;
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event): void {
                /** @var array $data */
                $data = $event->getData();
                if (empty($data['quantity'])) {
                    $data['quantity'] = '1';
                }

                $event->setData($data);
            })
            ->setDataMapper($this)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setRequired('currency');
    }

    /**
     * @inheritdoc
     */
    public function getBlockPrefix(): string
    {
        return 'sylius_admin_order_creation_new_order_order_item';
    }

    public function mapDataToForms(mixed $viewData, \Traversable $forms): void
    {
        $this->dataMapper->mapDataToForms($viewData, $forms);
    }

    public function mapFormsToData(\Traversable $forms, mixed &$viewData): void
    {
        $this->dataMapper->mapFormsToData($forms, $viewData);
    }
}
