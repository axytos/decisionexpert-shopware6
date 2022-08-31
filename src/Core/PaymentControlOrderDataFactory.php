<?php

declare(strict_types=1);

namespace Axytos\DecisionExpert\Shopware\Core;

use Axytos\ECommerce\Clients\PaymentControl\PaymentControlOrderData;
use Axytos\Shopware\DataMapping\CustomerDataDtoFactory;
use Axytos\Shopware\DataMapping\DeliveryAddressDtoFactory;
use Axytos\Shopware\DataMapping\InvoiceAddressDtoFactory;
use Axytos\Shopware\DataMapping\PaymentControlBasketDtoFactory;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class PaymentControlOrderDataFactory
{
    private CustomerDataDtoFactory $customerDataDtoFactory;
    private DeliveryAddressDtoFactory $deliveryAddressDtoFactory;
    private InvoiceAddressDtoFactory $invoiceAddressDtoFactory;
    private PaymentControlBasketDtoFactory $paymentControlBasketDtoFactory;

    public function __construct(
        CustomerDataDtoFactory $customerDataDtoFactory,
        DeliveryAddressDtoFactory $deliveryAddressDtoFactory,
        InvoiceAddressDtoFactory $invoiceAddressDtoFactory,
        PaymentControlBasketDtoFactory $paymentControlBasketDtoFactory
    ) {
        $this->customerDataDtoFactory = $customerDataDtoFactory;
        $this->deliveryAddressDtoFactory = $deliveryAddressDtoFactory;
        $this->invoiceAddressDtoFactory = $invoiceAddressDtoFactory;
        $this->paymentControlBasketDtoFactory = $paymentControlBasketDtoFactory;
    }

    public function create(OrderEntity $order, SalesChannelContext $salesChannelContext): PaymentControlOrderData
    {
        $paymentControlOrderData = new PaymentControlOrderData();

        // paymentMethodId

        $paymentControlOrderData->paymentMethodId = $salesChannelContext->getPaymentMethod()->getId();

        // deliveryAddress

        $paymentControlOrderData->deliveryAddress = $this->deliveryAddressDtoFactory->create($order);

        // invoiceAddress

        $paymentControlOrderData->invoiceAddress = $this->invoiceAddressDtoFactory->create($order);

        // basket

        $paymentControlOrderData->basket = $this->paymentControlBasketDtoFactory->create($order);

        // personalData

        $paymentControlOrderData->personalData = $this->customerDataDtoFactory->create($order);

        return $paymentControlOrderData;
    }
}
