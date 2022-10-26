<?php

declare(strict_types=1);

namespace Axytos\DecisionExpert\Shopware\Tests\Core;

use Axytos\ECommerce\Clients\PaymentControl\PaymentControlAction;
use Axytos\ECommerce\Clients\PaymentControl\PaymentControlCheckFailedException;
use Axytos\ECommerce\Clients\PaymentControl\PaymentControlClientInterface;
use Axytos\ECommerce\Clients\PaymentControl\PaymentControlConfirmFailedException;
use Axytos\ECommerce\Clients\PaymentControl\PaymentControlOrderData;
use Axytos\Shopware\PaymentMethod\PaymentMethodPredicates;
use Axytos\DecisionExpert\Shopware\Configuration\PluginConfigurationValidator;
use Axytos\DecisionExpert\Shopware\Core\PaymentControlOrderCache;
use Axytos\DecisionExpert\Shopware\Core\PaymentControlOrderCacheFactory;
use Axytos\DecisionExpert\Shopware\Core\PaymentControlOrderDataFactory;
use Axytos\Shopware\Order\OrderCheckProcessStateMachine;
use Axytos\DecisionExpert\Shopware\Core\PaymentServiceDecorator;
use Axytos\Shopware\DataAbstractionLayer\OrderEntityRepository;
use Axytos\Shopware\ErrorReporting\ErrorHandler;
use Axytos\Shopware\Order\OrderStateMachine;
use Axytos\Shopware\Routing\Router;
use Exception;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\Checkout\Payment\PaymentService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\RedirectResponse;

class PaymentServiceDecoratorTest extends TestCase
{
    /** @var PaymentService&MockObject */
    private PaymentService $decorated;

    /** @var PluginConfigurationValidator&MockObject */
    private PluginConfigurationValidator $pluginConfigurationValidator;

    /** @var OrderEntityRepository&MockObject */
    private OrderEntityRepository $orderEntityRepository;

    /** @var Router&MockObject */
    private Router $router;

    /** @var PaymentControlOrderDataFactory&MockObject */
    private PaymentControlOrderDataFactory $paymentControlOrderDataFactory;

    /** @var PaymentControlClientInterface&MockObject */
    private PaymentControlClientInterface $paymentControlClient;

    /** @var PaymentControlOrderCacheFactory&MockObject */
    private PaymentControlOrderCacheFactory $paymentControlOrderCacheFactory;

    /** @var PaymentMethodPredicates&MockObject */
    private PaymentMethodPredicates $paymentMethodPredicates;

    /** @var OrderCheckProcessStateMachine&MockObject */
    private OrderCheckProcessStateMachine $orderCheckProcessStateMachine;

    /** @var ErrorHandler&MockObject */
    private ErrorHandler $errorHandler;

    /** @var OrderStateMachine&MockObject */
    private OrderStateMachine $orderStateMachine;


    private PaymentServiceDecorator $sut;

    private const ORDER_ID = 'orderId';
    private const FINISH_URL = 'finishUrl';
    private const ERROR_URL = 'errorUrl';

    /** @var RequestDataBag&MockObject */
    private RequestDataBag $requestDataBag;

    /** @var Context&MockObject */
    private Context $context;

    /** @var SalesChannelContext&MockObject */
    private SalesChannelContext $salesChannelContext;

    /** @var PaymentMethodEntity&MockObject */
    private PaymentMethodEntity $paymentMethod;

    /** @var OrderEntity&MockObject */
    private OrderEntity $order;

    /** @var PaymentControlOrderData&MockObject */
    private PaymentControlOrderData $orderData;

    /** @var PaymentControlOrderCache&MockObject */
    private PaymentControlOrderCache $paymentControlOrderCache;

    /** @var RedirectResponse&MockObject */
    private RedirectResponse $completeOrderResponse;

    /** @var RedirectResponse&MockObject */
    private RedirectResponse $cancelOrderResponse;

    /** @var RedirectResponse&MockObject */
    private RedirectResponse $changePaymentMethodResponse;

    /** @var RedirectResponse&MockObject */
    private RedirectResponse $changePaymentMethodWithErrorResponse;

    public function setUp(): void
    {
        $this->decorated = $this->createMock(PaymentService::class);
        $this->pluginConfigurationValidator = $this->createMock(PluginConfigurationValidator::class);
        $this->orderEntityRepository = $this->createMock(OrderEntityRepository::class);
        $this->router = $this->createMock(Router::class);
        $this->paymentControlOrderDataFactory = $this->createMock(PaymentControlOrderDataFactory::class);
        $this->paymentControlClient = $this->createMock(PaymentControlClientInterface::class);
        $this->paymentControlOrderCacheFactory = $this->createMock(PaymentControlOrderCacheFactory::class);
        $this->orderCheckProcessStateMachine = $this->createMock(OrderCheckProcessStateMachine::class);
        $this->paymentMethodPredicates = $this->createMock(PaymentMethodPredicates::class);
        $this->errorHandler = $this->createMock(ErrorHandler::class);
        $this->orderStateMachine = $this->createMock(OrderStateMachine::class);

        $this->sut = new PaymentServiceDecorator(
            $this->decorated,
            $this->pluginConfigurationValidator,
            $this->orderEntityRepository,
            $this->router,
            $this->paymentControlOrderDataFactory,
            $this->paymentControlClient,
            $this->paymentControlOrderCacheFactory,
            $this->orderCheckProcessStateMachine,
            $this->paymentMethodPredicates,
            $this->errorHandler,
            $this->orderStateMachine
        );

        $this->requestDataBag = $this->createMock(RequestDataBag::class);
        $this->context = $this->createMock(Context::class);
        $this->salesChannelContext = $this->createMock(SalesChannelContext::class);
        $this->order = $this->createMock(OrderEntity::class);
        $this->paymentMethod = $this->createMock(PaymentMethodEntity::class);
        $this->orderData = $this->createMock(PaymentControlOrderData::class);
        $this->paymentControlOrderCache = $this->createMock(PaymentControlOrderCache::class);

        $this->completeOrderResponse = $this->createMock(RedirectResponse::class);
        $this->cancelOrderResponse = $this->createMock(RedirectResponse::class);
        $this->changePaymentMethodResponse = $this->createMock(RedirectResponse::class);
        $this->changePaymentMethodWithErrorResponse = $this->createMock(RedirectResponse::class);

        $this->setUpSalesChannelContext();
        $this->setUpPaymentControlOrderCache();
        $this->setUpResponses();
    }

    private function setUpSalesChannelContext(): void
    {
        $this->salesChannelContext
            ->method('getContext')
            ->willReturn($this->context);
    }

    private function setUpPaymentControlOrderCache(): void
    {
        $this->orderEntityRepository
            ->method('findOrder')
            ->with(self::ORDER_ID, $this->context)
            ->willReturn($this->order);

        $this->paymentControlOrderDataFactory
            ->method('create')
            ->with($this->order, $this->salesChannelContext)
            ->willReturn($this->orderData);

        $this->paymentControlOrderCacheFactory
            ->method('create')
            ->with(self::ORDER_ID, $this->salesChannelContext)
            ->willReturn($this->paymentControlOrderCache);
    }

    private function setUpResponses(): void
    {
        $this->decorated
            ->method('handlePaymentByOrder')
            ->with(self::ORDER_ID, $this->requestDataBag, $this->salesChannelContext, self::FINISH_URL, self::ERROR_URL)
            ->willReturn($this->completeOrderResponse);

        $this->router
            ->method('redirectToCheckoutFailedPage')
            ->willReturn($this->cancelOrderResponse);

        $this->router
            ->method('redirectToEditOrderPage')
            ->with(self::ORDER_ID)
            ->willReturn($this->changePaymentMethodResponse);

        $this->router
            ->method('redirectToEditOrderPageWithError')
            ->with(self::ORDER_ID)
            ->willReturn($this->changePaymentMethodWithErrorResponse);
    }

    private function setUpPaymentControlAction(string $paymentControlAction): void
    {
        $this->paymentControlClient
            ->method('check')
            ->with($this->orderData, $this->paymentControlOrderCache)
            ->willReturn($paymentControlAction);
    }

    private function setUpCheckFailed(): void
    {
        $this->paymentControlClient
            ->method('check')
            ->willThrowException(new PaymentControlCheckFailedException(new Exception()));
    }

    private function setUpConfirmFailed(): void
    {
        $this->paymentControlClient
            ->method('confirm')
            ->willThrowException(new PaymentControlConfirmFailedException(new Exception()));
    }

    private function setUpFallbackAllowed(bool $allowed): void
    {
        $this->salesChannelContext
            ->method('getPaymentMethod')
            ->willReturn($this->paymentMethod);

        $this->paymentMethodPredicates
            ->method('isAllowedFallback')
            ->with($this->paymentMethod)
            ->willReturn($allowed);
    }

    public function test_handlePaymentByOrder_plugin_configuration_is_invalid_completes_order(): void
    {
        $this->pluginConfigurationValidator->method('isInvalid')->willReturn(true);

        $actual = $this->sut->handlePaymentByOrder(self::ORDER_ID, $this->requestDataBag, $this->salesChannelContext, self::FINISH_URL, self::ERROR_URL);

        $this->assertSame($this->completeOrderResponse, $actual);
    }

    public function test_handlePaymentByOrder_plugin_configuration_is_invalid_does_not_execute_check(): void
    {
        $this->pluginConfigurationValidator->method('isInvalid')->willReturn(true);

        $this->paymentControlClient
            ->expects($this->never())
            ->method('check');

        $this->sut->handlePaymentByOrder(self::ORDER_ID, $this->requestDataBag, $this->salesChannelContext, self::FINISH_URL, self::ERROR_URL);
    }

    public function test_handlePaymentByOrder_delegates_to_decorated_if_action_is_to_complete_order(): void
    {
        $paymentControlAction = PaymentControlAction::COMPLETE_ORDER;

        $this->setUpPaymentControlAction($paymentControlAction);

        $actual = $this->sut->handlePaymentByOrder(self::ORDER_ID, $this->requestDataBag, $this->salesChannelContext, self::FINISH_URL, self::ERROR_URL);

        $this->assertSame($this->completeOrderResponse, $actual);
    }

    public function test_handlePaymentByOrder_calls_confirm_if_action_is_to_complete_order(): void
    {
        $paymentControlAction = PaymentControlAction::COMPLETE_ORDER;

        $this->setUpPaymentControlAction($paymentControlAction);

        $this->paymentControlClient
            ->expects($this->once())
            ->method('confirm')
            ->with($this->orderData);

        $this->sut->handlePaymentByOrder(self::ORDER_ID, $this->requestDataBag, $this->salesChannelContext, self::FINISH_URL, self::ERROR_URL);
    }

    public function test_handlePaymentByOrder_does_not_delegate_to_decorated_if_action_is_to_cancel_order(): void
    {
        $paymentControlAction = PaymentControlAction::CANCEL_ORDER;

        $this->setUpPaymentControlAction($paymentControlAction);

        $actual = $this->sut->handlePaymentByOrder(self::ORDER_ID, $this->requestDataBag, $this->salesChannelContext, self::FINISH_URL, self::ERROR_URL);

        $this->assertNotSame($this->completeOrderResponse, $actual);
    }

    public function test_handlePaymentByOrder_redirects_to_checkout_failed_page_if_action_is_to_cancel_order(): void
    {
        $paymentControlAction = PaymentControlAction::CANCEL_ORDER;

        $this->setUpPaymentControlAction($paymentControlAction);

        $actual = $this->sut->handlePaymentByOrder(self::ORDER_ID, $this->requestDataBag, $this->salesChannelContext, self::FINISH_URL, self::ERROR_URL);

        $this->assertSame($this->cancelOrderResponse, $actual);
    }

    public function test_handlePaymentByOrder_cancels_order_if_action_is_to_cancel_order(): void
    {
        $paymentControlAction = PaymentControlAction::CANCEL_ORDER;

        $this->setUpPaymentControlAction($paymentControlAction);

        $this->orderStateMachine
            ->expects($this->once())
            ->method('cancelOrder')
            ->with(self::ORDER_ID, $this->salesChannelContext);

        $this->sut->handlePaymentByOrder(self::ORDER_ID, $this->requestDataBag, $this->salesChannelContext, self::FINISH_URL, self::ERROR_URL);
    }

    public function test_handlePaymentByOrder_does_not_call_confirm_if_action_is_to_cancel_order(): void
    {
        $paymentControlAction = PaymentControlAction::CANCEL_ORDER;

        $this->setUpPaymentControlAction($paymentControlAction);

        $this->paymentControlClient
            ->expects($this->never())
            ->method('confirm');

        $this->sut->handlePaymentByOrder(self::ORDER_ID, $this->requestDataBag, $this->salesChannelContext, self::FINISH_URL, self::ERROR_URL);
    }

    public function test_handlePaymentByOrder_does_not_delegate_to_decorated_if_action_is_to_change_payment_method(): void
    {
        $paymentControlAction = PaymentControlAction::CHANGE_PAYMENT_METHOD;

        $this->setUpPaymentControlAction($paymentControlAction);

        $actual = $this->sut->handlePaymentByOrder(self::ORDER_ID, $this->requestDataBag, $this->salesChannelContext, self::FINISH_URL, self::ERROR_URL);

        $this->assertNotSame($this->completeOrderResponse, $actual);
    }

    public function test_handlePaymentByOrder_redirects_to_edit_order_page_if_action_is_to_change_payment_method(): void
    {
        $paymentControlAction = PaymentControlAction::CHANGE_PAYMENT_METHOD;

        $this->setUpPaymentControlAction($paymentControlAction);

        $actual = $this->sut->handlePaymentByOrder(self::ORDER_ID, $this->requestDataBag, $this->salesChannelContext, self::FINISH_URL, self::ERROR_URL);

        $this->assertSame($this->changePaymentMethodResponse, $actual);
    }

    public function test_handlePaymentByOrder_fails_payment_if_action_is_to_change_payment_method(): void
    {
        $paymentControlAction = PaymentControlAction::CHANGE_PAYMENT_METHOD;

        $this->setUpPaymentControlAction($paymentControlAction);

        $this->orderStateMachine
            ->expects($this->once())
            ->method('failPayment')
            ->with(self::ORDER_ID, $this->salesChannelContext);

        $this->sut->handlePaymentByOrder(self::ORDER_ID, $this->requestDataBag, $this->salesChannelContext, self::FINISH_URL, self::ERROR_URL);
    }

    public function test_handlePaymentByOrder_does_not_call_confirm_if_action_is_to_change_payment_method(): void
    {
        $paymentControlAction = PaymentControlAction::CHANGE_PAYMENT_METHOD;

        $this->setUpPaymentControlAction($paymentControlAction);

        $this->paymentControlClient
            ->expects($this->never())
            ->method('confirm');

        $this->sut->handlePaymentByOrder(self::ORDER_ID, $this->requestDataBag, $this->salesChannelContext, self::FINISH_URL, self::ERROR_URL);
    }

    public function test_handlePaymentByOrder_check_fails_fallback_allowed_sets_failed_state(): void
    {
        $this->setUpCheckFailed();
        $this->setUpFallbackAllowed(true);

        $this->orderCheckProcessStateMachine
            ->expects($this->once())
            ->method('setFailed')
            ->with(self::ORDER_ID, $this->salesChannelContext);

        $this->sut->handlePaymentByOrder(self::ORDER_ID, $this->requestDataBag, $this->salesChannelContext, self::FINISH_URL, self::ERROR_URL);
    }

    public function test_handlePaymentByOrder_check_fails_fallback_allowed_completes_order(): void
    {
        $this->setUpCheckFailed();
        $this->setUpFallbackAllowed(true);

        $actual = $this->sut->handlePaymentByOrder(self::ORDER_ID, $this->requestDataBag, $this->salesChannelContext, self::FINISH_URL, self::ERROR_URL);

        $this->assertSame($this->completeOrderResponse, $actual);
    }

    public function test_handlePaymentByOrder_check_fails_fallback_not_allowed_sets_failed_state(): void
    {
        $this->setUpCheckFailed();
        $this->setUpFallbackAllowed(false);

        $this->orderCheckProcessStateMachine
            ->expects($this->once())
            ->method('setFailed')
            ->with(self::ORDER_ID, $this->salesChannelContext);

        $this->sut->handlePaymentByOrder(self::ORDER_ID, $this->requestDataBag, $this->salesChannelContext, self::FINISH_URL, self::ERROR_URL);
    }

    public function test_handlePaymentByOrder_check_fails_fallback_not_allowed_redirects_to_change_payment_page(): void
    {
        $this->setUpCheckFailed();
        $this->setUpFallbackAllowed(false);

        $actual = $this->sut->handlePaymentByOrder(self::ORDER_ID, $this->requestDataBag, $this->salesChannelContext, self::FINISH_URL, self::ERROR_URL);

        $this->assertSame($this->changePaymentMethodWithErrorResponse, $actual);
    }

    public function test_handlePaymentByOrder_confirm_fails_fallback_allowed_sets_failed_state(): void
    {
        $this->setUpConfirmFailed();
        $this->setUpFallbackAllowed(true);

        $this->orderCheckProcessStateMachine
            ->expects($this->once())
            ->method('setFailed')
            ->with(self::ORDER_ID, $this->salesChannelContext);

        $this->sut->handlePaymentByOrder(self::ORDER_ID, $this->requestDataBag, $this->salesChannelContext, self::FINISH_URL, self::ERROR_URL);
    }

    public function test_handlePaymentByOrder_confirm_fails_fallback_allowed_completes_order(): void
    {
        $this->setUpConfirmFailed();
        $this->setUpFallbackAllowed(true);

        $actual = $this->sut->handlePaymentByOrder(self::ORDER_ID, $this->requestDataBag, $this->salesChannelContext, self::FINISH_URL, self::ERROR_URL);

        $this->assertSame($this->completeOrderResponse, $actual);
    }

    public function test_handlePaymentByOrder_confirm_fails_fallback_not_allowed_sets_failed_state(): void
    {
        $this->setUpConfirmFailed();
        $this->setUpFallbackAllowed(false);

        $this->orderCheckProcessStateMachine
            ->expects($this->once())
            ->method('setFailed')
            ->with(self::ORDER_ID, $this->salesChannelContext);

        $this->sut->handlePaymentByOrder(self::ORDER_ID, $this->requestDataBag, $this->salesChannelContext, self::FINISH_URL, self::ERROR_URL);
    }

    public function test_handlePaymentByOrder_confirm_fails_fallback_not_allowed_redirects_to_change_payment_page(): void
    {
        $this->setUpConfirmFailed();
        $this->setUpFallbackAllowed(false);

        $actual = $this->sut->handlePaymentByOrder(self::ORDER_ID, $this->requestDataBag, $this->salesChannelContext, self::FINISH_URL, self::ERROR_URL);

        $this->assertSame($this->changePaymentMethodWithErrorResponse, $actual);
    }

    public function test_handlePaymentByOrder_completes_order_on_unknown_error(): void
    {
        $this->paymentControlClient->method('check')->willThrowException(new Exception());

        $actual = $this->sut->handlePaymentByOrder(self::ORDER_ID, $this->requestDataBag, $this->salesChannelContext, self::FINISH_URL, self::ERROR_URL);

        $this->assertSame($this->completeOrderResponse, $actual);
    }

    public function test_handlePaymentByOrder_reports_on_unknown_error(): void
    {
        $unkownError = new Exception();
        $this->paymentControlClient->method('check')->willThrowException($unkownError);

        $this->errorHandler
            ->expects($this->once())
            ->method('handle')
            ->with($unkownError);

        $this->sut->handlePaymentByOrder(self::ORDER_ID, $this->requestDataBag, $this->salesChannelContext, self::FINISH_URL, self::ERROR_URL);
    }
}
