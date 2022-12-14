<?php

declare(strict_types=1);

namespace Axytos\DecisionExpert\Shopware\Tests;

use Axytos\ECommerce\Clients\Checkout\CheckoutClientInterface;
use Axytos\ECommerce\Clients\Checkout\CreditCheckAgreementLoadFailedException;
use Axytos\Shopware\PaymentMethod\PaymentMethodCollectionFilter;
use Axytos\DecisionExpert\Shopware\Storefront\CheckoutConfirmPage\CheckoutConfirmPageExtension;
use Axytos\DecisionExpert\Shopware\Storefront\CheckoutConfirmPage\CheckoutConfirmPageLoadedEventHandler;
use Exception;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Shopware\Core\Checkout\Payment\PaymentMethodCollection;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPage;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;

class CheckoutConfirmPageLoadedEventHandlerTest extends TestCase
{
    private const EXTENSION_NAME = 'axytos_decision_expert_checkout_confirm_page';

    private const PAYMENT_METHOD_ID = 'paymentMethodId';

    /** @var CheckoutClientInterface&MockObject */
    private CheckoutClientInterface $checkoutClient;

    /** @var PaymentMethodCollectionFilter&MockObject */
    private PaymentMethodCollectionFilter $paymentMethodCollectionFilter;

    private CheckoutConfirmPageLoadedEventHandler $sut;


    /** @var CheckoutConfirmPage&MockObject */
    private CheckoutConfirmPage $page;

    /** @var SalesChannelContext&MockObject */
    private SalesChannelContext $salesChannelContext;

    /** @var PaymentMethodEntity&MockObject */
    private PaymentMethodEntity $paymentMethod;

    /** @var PaymentMethodCollection&MockObject */
    private PaymentMethodCollection $paymentMethods;

    /** @var PaymentMethodCollection&MockObject */
    private PaymentMethodCollection $fallbackPaymentMethods;

    /** @var CheckoutConfirmPageLoadedEvent&MockObject */
    private CheckoutConfirmPageLoadedEvent $event;

    public function setUp(): void
    {
        $this->checkoutClient = $this->createMock(CheckoutClientInterface::class);
        $this->paymentMethodCollectionFilter = $this->createMock(PaymentMethodCollectionFilter::class);

        $this->sut = new CheckoutConfirmPageLoadedEventHandler(
            $this->checkoutClient,
            $this->paymentMethodCollectionFilter
        );

        $this->page = $this->createMock(CheckoutConfirmPage::class);
        $this->salesChannelContext = $this->createMock(SalesChannelContext::class);
        $this->paymentMethod = $this->createMock(PaymentMethodEntity::class);
        $this->paymentMethods = $this->createMock(PaymentMethodCollection::class);
        $this->fallbackPaymentMethods = $this->createMock(PaymentMethodCollection::class);
        $this->event = $this->createMock(CheckoutConfirmPageLoadedEvent::class);

        $this->setUpEvent();
        $this->setUpPaymentMethodCollectionFilter();
    }

    private function setUpEvent(): void
    {
        $this->paymentMethod->method('getId')->willReturn(self::PAYMENT_METHOD_ID);
        $this->salesChannelContext->method('getPaymentMethod')->willReturn($this->paymentMethod);
        $this->page->method('getPaymentMethods')->willReturn($this->paymentMethods);
        $this->event->method('getPage')->willReturn($this->page);
        $this->event->method('getSalesChannelContext')->willReturn($this->salesChannelContext);
    }

    private function setUpPaymentMethodCollectionFilter(): void
    {
        $this->paymentMethodCollectionFilter
            ->method('filterAllowedFallbackPaymentMethods')
            ->with($this->paymentMethods)
            ->willReturn($this->fallbackPaymentMethods);
    }

    private function setUpCheckoutClient(
        bool $mustShowCreditCheckAgreement,
        string $getCreditCheckAgreementInfo
    ): void {
        $this->checkoutClient
            ->method('mustShowCreditCheckAgreement')
            ->with(self::PAYMENT_METHOD_ID)
            ->willReturn($mustShowCreditCheckAgreement);

        $this->checkoutClient
            ->method('getCreditCheckAgreementInfo')
            ->willReturn($getCreditCheckAgreementInfo);
    }

    private function setUpCheckoutClientFailed(): void
    {
        $this->checkoutClient
            ->method('getCreditCheckAgreementInfo')
            ->willThrowException(new CreditCheckAgreementLoadFailedException(new Exception()));
    }

    /**
     * @group legacy
     */
    public function test_handle_adds_CheckoutConfirmPageExtension(): void
    {
        $matchExtension = $this->callback(function ($extension) {
            return $extension instanceof CheckoutConfirmPageExtension;
        });

        $this->page
            ->expects($this->once())
            ->method('addExtension')
            ->with(self::EXTENSION_NAME, $matchExtension);

        $this->sut->handle($this->event);
    }

    /**
     * @group legacy
     */
    public function test_handle_sets_ShowCreditCheckAgreement(): void
    {
        $this->setUpCheckoutClient(true, 'CreditCheckAgreementInfo');

        $matchExtension = $this->callback(function (CheckoutConfirmPageExtension $extension) {
            return $extension->showCreditCheckAgreement === true;
        });

        $this->page
            ->expects($this->once())
            ->method('addExtension')
            ->with(self::EXTENSION_NAME, $matchExtension);

        $this->sut->handle($this->event);
    }

    /**
     * @group legacy
     */
    public function test_handle_sets_CreditCheckAgreementInfo(): void
    {
        $this->setUpCheckoutClient(true, 'CreditCheckAgreementInfo');

        $matchExtension = $this->callback(function (CheckoutConfirmPageExtension $extension) {
            return $extension->creditCheckAgreementInfo === 'CreditCheckAgreementInfo';
        });

        $this->page
            ->expects($this->once())
            ->method('addExtension')
            ->with(self::EXTENSION_NAME, $matchExtension);

        $this->sut->handle($this->event);
    }

    /**
     * @group legacy
     */
    public function test_handle_does_not_show_CreditCheckAgreement_if_CreditCheckAgreement_cannot_be_loaded(): void
    {
        $this->setUpCheckoutClientFailed();

        $matchExtension = $this->callback(function (CheckoutConfirmPageExtension $extension) {
            return $extension->showCreditCheckAgreement === false
                && $extension->creditCheckAgreementInfo === '';
        });

        $this->page
            ->expects($this->once())
            ->method('addExtension')
            ->with(self::EXTENSION_NAME, $matchExtension);

        $this->sut->handle($this->event);
    }

    /**
     * @group legacy
     */
    public function test_handle_only_shows_fallback_payment_methods_if_CreditCheckAgreement_cannot_be_loaded(): void
    {
        $this->setUpCheckoutClientFailed();

        $this->page
            ->expects($this->once())
            ->method('setPaymentMethods')
            ->with($this->fallbackPaymentMethods);

        $this->sut->handle($this->event);
    }
}
