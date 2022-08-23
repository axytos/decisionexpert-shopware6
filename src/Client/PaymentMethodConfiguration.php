<?php declare(strict_types=1);

namespace Axytos\DecisionExpert\Shopware\Client;

use Axytos\ECommerce\Abstractions\PaymentMethodConfigurationInterface;
use Axytos\DecisionExpert\Shopware\Configuration\PluginConfiguration;

class PaymentMethodConfiguration implements PaymentMethodConfigurationInterface
{
    public PluginConfiguration $pluginConfig;

    public function __construct(PluginConfiguration $pluginConfig)
    {
        $this->pluginConfig = $pluginConfig;
    }

    public function isIgnored(string $paymentMethodId): bool
    {
        $ignoredPaymentMethodIds = $this->pluginConfig->getIgnoredPaymentMethods();
        return in_array($paymentMethodId, $ignoredPaymentMethodIds);
    }

    public function isSafe(string $paymentMethodId): bool
    {
        $safePaymentMethodIds = $this->pluginConfig->getSafePaymentMethods();
        return in_array($paymentMethodId, $safePaymentMethodIds);
    }

    public function isUnsafe(string $paymentMethodId): bool
    {
        $unsafePaymentMethodIds = $this->pluginConfig->getUnsafePaymentMethods();
        return in_array($paymentMethodId, $unsafePaymentMethodIds);
    }

    public function isNotConfigured(string $paymentMethodId): bool
    {
        return !$this->isIgnored($paymentMethodId)
            && !$this->isSafe($paymentMethodId)
            && !$this->isUnsafe($paymentMethodId);
    }
}