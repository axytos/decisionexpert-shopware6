<?php

declare(strict_types=1);

namespace Axytos\DecisionExpert\Shopware\Tests;

use Axytos\DecisionExpert\Shopware\Configuration\PluginConfigurationValidator;
use Axytos\DecisionExpert\Shopware\Storefront\AccountEditOrderPage\AccountEditOrderPageLoadedEventHandler;
use Axytos\DecisionExpert\Shopware\Storefront\AccountEditOrderPage\AccountEditOrderPageLoadedEventSubscriber;
use Axytos\Shopware\ErrorReporting\ErrorHandler;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Storefront\Page\Account\Order\AccountEditOrderPageLoadedEvent;

class AccountEditOrderPageLoadedEventSubscriberTest extends TestCase
{
    /** @var PluginConfigurationValidator&MockObject */
    private PluginConfigurationValidator $pluginConfigurationValidator;

    /** @var AccountEditOrderPageLoadedEventHandler&MockObject */
    private AccountEditOrderPageLoadedEventHandler $accountEditOrderPageLoadedEventHandler;

    /** @var ErrorHandler&MockObject */
    private ErrorHandler $errorHandler;

    private AccountEditOrderPageLoadedEventSubscriber $sut;

    public function setUp(): void
    {
        $this->pluginConfigurationValidator = $this->createMock(PluginConfigurationValidator::class);
        $this->accountEditOrderPageLoadedEventHandler = $this->createMock(AccountEditOrderPageLoadedEventHandler::class);
        $this->errorHandler = $this->createMock(ErrorHandler::class);

        $this->sut = new AccountEditOrderPageLoadedEventSubscriber(
            $this->pluginConfigurationValidator,
            $this->accountEditOrderPageLoadedEventHandler,
            $this->errorHandler
        );
    }

    public function test_onAccountEditOrderPageLoaded_is_subscribed_to_AccountEditOrderPageLoadedEvent(): void
    {
        $subscribedEvents = $this->sut->getSubscribedEvents();

        $this->assertEquals(
            $subscribedEvents[AccountEditOrderPageLoadedEvent::class],
            'onAccountEditOrderPageLoaded'
        );
    }

    public function test_onAccountEditOrderPageLoaded_plugin_configuraton_is_valid_executes_handler(): void
    {
        $event = $this->createMock(AccountEditOrderPageLoadedEvent::class);

        $this->pluginConfigurationValidator->method('isInvalid')->willReturn(false);

        $this->accountEditOrderPageLoadedEventHandler
            ->expects($this->once())
            ->method('handle')
            ->with($event);

        $this->sut->onAccountEditOrderPageLoaded($event);
    }

    public function test_onAccountEditOrderPageLoaded_plugin_configuraton_is_invalid_does_not_execute_handler(): void
    {
        $event = $this->createMock(AccountEditOrderPageLoadedEvent::class);

        $this->pluginConfigurationValidator->method('isInvalid')->willReturn(true);

        $this->accountEditOrderPageLoadedEventHandler
            ->expects($this->never())
            ->method('handle')
            ->with($event);

        $this->sut->onAccountEditOrderPageLoaded($event);
    }

    public function test_onAccountEditOrderPageLoaded_reprots_errors(): void
    {
        $event = $this->createMock(AccountEditOrderPageLoadedEvent::class);
        $exception = new Exception();

        $this->pluginConfigurationValidator->method('isInvalid')->willReturn(false);

        $this->accountEditOrderPageLoadedEventHandler
            ->method('handle')
            ->willThrowException($exception);

        $this->errorHandler
            ->expects($this->once())
            ->method('handle')
            ->with($exception);

        $this->sut->onAccountEditOrderPageLoaded($event);
    }
}
