<?php

declare(strict_types=1);

namespace Axytos\DecisionExpert\Shopware\Storefront\CheckoutConfirmPage;

use Axytos\DecisionExpert\Shopware\Configuration\PluginConfigurationValidator;
use Axytos\Shopware\ErrorReporting\ErrorHandler;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CheckoutConfirmPageLoadedEventSubscriber implements EventSubscriberInterface
{
    private PluginConfigurationValidator $pluginConfigurationValidator;
    private CheckoutConfirmPageLoadedEventHandler $checkoutConfirmPageLoadedEventHandler;
    private ErrorHandler $errorHandler;

    public function __construct(
        PluginConfigurationValidator $pluginConfigurationValidator,
        CheckoutConfirmPageLoadedEventHandler $checkoutConfirmPageLoadedEventHandler,
        ErrorHandler $errorHandler
    ) {
        $this->pluginConfigurationValidator = $pluginConfigurationValidator;
        $this->checkoutConfirmPageLoadedEventHandler = $checkoutConfirmPageLoadedEventHandler;
        $this->errorHandler = $errorHandler;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckoutConfirmPageLoadedEvent::class => 'onCheckoutConfirmPageLoaded'
        ];
    }

    public function onCheckoutConfirmPageLoaded(CheckoutConfirmPageLoadedEvent $event): void
    {
        try {
            if ($this->pluginConfigurationValidator->isInvalid()) {
                return;
            }
            $this->checkoutConfirmPageLoadedEventHandler->handle($event);
        } catch (\Throwable $th) {
            $this->errorHandler->handle($th);
        }
    }
}
