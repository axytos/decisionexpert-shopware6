<?php

declare(strict_types=1);

namespace Axytos\DecisionExpert\Shopware\Core;

use Axytos\ECommerce\Clients\PaymentControl\PaymentControlAction;
use Axytos\ECommerce\Clients\PaymentControl\PaymentControlCheckFailedException;
use Axytos\ECommerce\Clients\PaymentControl\PaymentControlClientInterface;
use Axytos\ECommerce\Clients\PaymentControl\PaymentControlConfirmFailedException;
use Axytos\Shopware\PaymentMethod\PaymentMethodPredicates;
use Axytos\DecisionExpert\Shopware\Configuration\PluginConfigurationValidator;
use Axytos\Shopware\DataAbstractionLayer\OrderEntityRepository;
use Axytos\Shopware\ErrorReporting\ErrorHandler;
use Axytos\Shopware\Order\OrderCheckProcessStateMachine;
use Axytos\Shopware\Order\OrderStateMachine;
use Axytos\Shopware\Routing\Router;
use Shopware\Core\Checkout\Payment\Cart\Token\TokenStruct;
use Shopware\Core\Checkout\Payment\PaymentService;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

class PaymentServiceDecorator extends PaymentService
{
    private PaymentService $decorated;
    private PluginConfigurationValidator $pluginConfigurationValidator;
    private OrderEntityRepository $orderEntityRepository;
    private Router $router;
    private PaymentControlOrderDataFactory $paymentControlOrderDataFactory;
    private PaymentControlClientInterface $paymentControlClient;
    private PaymentControlOrderCacheFactory $paymentControlOrderCacheFactory;
    private OrderCheckProcessStateMachine $orderCheckProcessStateMachine;
    private PaymentMethodPredicates $paymentMethodPredicates;
    private ErrorHandler $errorHandler;
    private OrderStateMachine $orderStateMachine;

    public function __construct(
        PaymentService $decorated,
        PluginConfigurationValidator $pluginConfigurationValidator,
        OrderEntityRepository $orderEntityRepository,
        Router $router,
        PaymentControlOrderDataFactory $paymentControlOrderDataFactory,
        PaymentControlClientInterface $paymentControlClient,
        PaymentControlOrderCacheFactory $paymentControlOrderCacheFactory,
        OrderCheckProcessStateMachine $orderCheckProcessStateMachine,
        PaymentMethodPredicates $paymentMethodPredicates,
        ErrorHandler $errorHandler,
        OrderStateMachine $orderStateMachine
    ) {
        $this->decorated = $decorated;
        $this->pluginConfigurationValidator = $pluginConfigurationValidator;
        $this->orderEntityRepository = $orderEntityRepository;
        $this->router = $router;
        $this->paymentControlOrderDataFactory = $paymentControlOrderDataFactory;
        $this->paymentControlClient = $paymentControlClient;
        $this->paymentControlOrderCacheFactory = $paymentControlOrderCacheFactory;
        $this->orderCheckProcessStateMachine = $orderCheckProcessStateMachine;
        $this->paymentMethodPredicates = $paymentMethodPredicates;
        $this->errorHandler = $errorHandler;
        $this->orderStateMachine = $orderStateMachine;
    }

    public function handlePaymentByOrder(
        string $orderId,
        RequestDataBag $dataBag,
        SalesChannelContext $context,
        ?string $finishUrl = null,
        ?string $errorUrl = null
    ): ?RedirectResponse {
        try {
            if ($this->pluginConfigurationValidator->isInvalid()) {
                return $this->completeOrder($orderId, $dataBag, $context, $finishUrl, $errorUrl);
            }
            return $this->executePaymentControl($orderId, $dataBag, $context, $finishUrl, $errorUrl);
        } catch (Throwable $t) {
            $this->errorHandler->handle($t);
            return $this->completeOrder($orderId, $dataBag, $context, $finishUrl, $errorUrl);
        }
    }

    private function executePaymentControl(
        string $orderId,
        RequestDataBag $dataBag,
        SalesChannelContext $context,
        ?string $finishUrl = null,
        ?string $errorUrl = null
    ): ?RedirectResponse {
        try {
            $this->orderCheckProcessStateMachine->setUnchecked($orderId, $context);

            $order = $this->orderEntityRepository->findOrder($orderId, $context->getContext());
            $orderData = $this->paymentControlOrderDataFactory->create($order, $context);
            $paymentControlOrderCache = $this->paymentControlOrderCacheFactory->create($orderId, $context);
            $paymentControlAction = $this->paymentControlClient->check($orderData, $paymentControlOrderCache);

            $this->orderCheckProcessStateMachine->setChecked($orderId, $context);

            if ($paymentControlAction === PaymentControlAction::CHANGE_PAYMENT_METHOD) {
                return $this->changePaymentMethod($orderId, $context);
            }

            if ($paymentControlAction === PaymentControlAction::CANCEL_ORDER) {
                return $this->cancelOrder($orderId, $context);
            }

            $this->paymentControlClient->confirm($orderData, $paymentControlOrderCache);

            $this->orderCheckProcessStateMachine->setConfirmed($orderId, $context);

            return $this->completeOrder($orderId, $dataBag, $context, $finishUrl, $errorUrl);
        } catch (PaymentControlCheckFailedException | PaymentControlConfirmFailedException $e) {
            $this->orderCheckProcessStateMachine->setFailed($orderId, $context);

            if (!$this->usesAllowedFallbackPaymentMethod($context)) {
                return $this->changePaymentMethodWithError($orderId, $context);
            }

            return $this->completeOrder($orderId, $dataBag, $context, $finishUrl, $errorUrl);
        }
    }

    private function usesAllowedFallbackPaymentMethod(SalesChannelContext $context): bool
    {
        $paymentMethod = $context->getPaymentMethod();
        return $this->paymentMethodPredicates->isAllowedFallback($paymentMethod);
    }

    private function changePaymentMethodWithError(string $orderId, SalesChannelContext $context): RedirectResponse
    {
        $this->orderStateMachine->failPayment($orderId, $context);
        return $this->router->redirectToEditOrderPageWithError($orderId);
    }

    private function changePaymentMethod(string $orderId, SalesChannelContext $context): RedirectResponse
    {
        $this->orderStateMachine->failPayment($orderId, $context);
        return $this->router->redirectToEditOrderPage($orderId);
    }

    private function cancelOrder(string $orderId, SalesChannelContext $context): RedirectResponse
    {
        $this->orderStateMachine->cancelOrder($orderId, $context);
        return $this->router->redirectToCheckoutFailedPage();
    }

    private function completeOrder(
        string $orderId,
        RequestDataBag $dataBag,
        SalesChannelContext $context,
        ?string $finishUrl = null,
        ?string $errorUrl = null
    ): ?RedirectResponse {
        return $this->decorated->handlePaymentByOrder($orderId, $dataBag, $context, $finishUrl, $errorUrl);
    }

    public function finalizeTransaction(string $paymentToken, Request $request, SalesChannelContext $context): TokenStruct
    {
        return $this->decorated->finalizeTransaction($paymentToken, $request, $context);
    }
}
