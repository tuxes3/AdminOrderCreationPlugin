<?php

declare(strict_types=1);

namespace Sylius\AdminOrderCreationPlugin\Controller;

use Sylius\AdminOrderCreationPlugin\Factory\OrderFactoryInterface;
use Sylius\AdminOrderCreationPlugin\Form\Type\NewOrderType;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class OrderPreviewAction
{
    /** @var OrderFactoryInterface */
    private $orderFactory;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var OrderProcessorInterface */
    private $orderProcessor;

    /** @var Environment */
    private $twig;

    public function __construct(
        OrderFactoryInterface $orderFactory,
        FormFactoryInterface $formFactory,
        OrderProcessorInterface $orderProcessor,
        Environment $twig,
    ) {
        $this->orderFactory = $orderFactory;
        $this->formFactory = $formFactory;
        $this->orderProcessor = $orderProcessor;
        $this->twig = $twig;
    }

    public function __invoke(Request $request): Response
    {
        /** @var string $customerId */
        $customerId = $request->attributes->get('customerId');

        /** @var string $channelCode */
        $channelCode = $request->attributes->get('channelCode');

        $order = $this->orderFactory->createForCustomerAndChannel($customerId, $channelCode);
        $form = $this->formFactory->create(NewOrderType::class, $order);

        /** @var OrderInterface $order */
        $order = $form->handleRequest($request)->getData();
        $this->orderProcessor->process($order);

        if ($form->isSubmitted() && $form->isValid()) {
            return new Response($this->twig->render('@SyliusAdminOrderCreationPlugin/Order/preview.html.twig', [
                'form' => $form->createView(),
            ]));
        }

        return new Response($this->twig->render('@SyliusAdminOrderCreationPlugin/Order/create.html.twig', [
            'form' => $form->createView(),
        ]));
    }
}
