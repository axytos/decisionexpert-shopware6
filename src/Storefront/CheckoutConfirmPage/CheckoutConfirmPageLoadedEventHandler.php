<?php

declare(strict_types=1);

namespace Axytos\DecisionExpert\Shopware\Storefront\CheckoutConfirmPage;

use Axytos\ECommerce\Clients\Checkout\CheckoutClientInterface;
use Axytos\ECommerce\Clients\Checkout\CreditCheckAgreementLoadFailedException;
use Axytos\Shopware\PaymentMethod\PaymentMethodCollectionFilter;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPage;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;

class CheckoutConfirmPageLoadedEventHandler
{
    private const EXTENSION_NAME = 'axytos_decision_expert_checkout_confirm_page';

    private CheckoutClientInterface $checkoutClient;
    private PaymentMethodCollectionFilter $paymentMethodCollectionFilter;

    public function __construct(
        CheckoutClientInterface $checkoutClient,
        PaymentMethodCollectionFilter $paymentMethodCollectionFilter
    ) {
        $this->checkoutClient = $checkoutClient;
        $this->paymentMethodCollectionFilter = $paymentMethodCollectionFilter;
    }

    public function handle(CheckoutConfirmPageLoadedEvent $event): void
    {
        try {
            $this->showCreditCheckAgreement($event);
        } catch (CreditCheckAgreementLoadFailedException $e) {
            $this->showFallbackPaymentMethods($event);
        }
    }

    private function showCreditCheckAgreement(CheckoutConfirmPageLoadedEvent $event): void
    {
        /** @var CheckoutConfirmPage */
        $page = $event->getPage();

        $showCreditCheckAgreement = $this->getShowCreditCheckAgreement($event);
        $creditCheckAgreementInfo = $this->getCreditCheckAgreementInfo($event);

        $this->extendPage($page, $showCreditCheckAgreement, $creditCheckAgreementInfo);
    }

    private function showFallbackPaymentMethods(CheckoutConfirmPageLoadedEvent $event): void
    {
        /** @var CheckoutConfirmPage */
        $page = $event->getPage();

        $this->extendPage($page, false, '');
        $this->filterPaymentMethods($page);
    }

    private function filterPaymentMethods(CheckoutConfirmPage $page): void
    {
        $paymentMethods = $page->getPaymentMethods();
        $paymentMethods = $this->paymentMethodCollectionFilter->filterAllowedFallbackPaymentMethods($paymentMethods);
        $page->setPaymentMethods($paymentMethods);
    }

    private function getShowCreditCheckAgreement(CheckoutConfirmPageLoadedEvent $event): bool
    {
        $salesChannelContext = $event->getSalesChannelContext();
        $selectedPaymentMethod = $salesChannelContext->getPaymentMethod();

        return $this->checkoutClient->mustShowCreditCheckAgreement($selectedPaymentMethod->getId());
    }

    private function getCreditCheckAgreementInfo(CheckoutConfirmPageLoadedEvent $event): string
    {
        return $this->checkoutClient->getCreditCheckAgreementInfo();
    }

    private function extendPage(
        CheckoutConfirmPage $page,
        bool $showCreditCheckAgreement,
        string $creditCheckAgreementInfo
    ): void {
        $extension = new CheckoutConfirmPageExtension();
        $extension->showCreditCheckAgreement = $showCreditCheckAgreement;
        $extension->creditCheckAgreementInfo = $creditCheckAgreementInfo;

        $page->addExtension(self::EXTENSION_NAME, $extension);
    }
}
