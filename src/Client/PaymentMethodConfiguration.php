<?php

declare(strict_types=1);

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

    /**
     * @param string $paymentMethodId
     * @return bool
     */
    public function isIgnored($paymentMethodId)
    {
        $ignoredPaymentMethodIds = $this->pluginConfig->getIgnoredPaymentMethods();
        return in_array($paymentMethodId, $ignoredPaymentMethodIds);
    }

    /**
     * @param string $paymentMethodId
     * @return bool
     */
    public function isSafe($paymentMethodId)
    {
        $safePaymentMethodIds = $this->pluginConfig->getSafePaymentMethods();
        return in_array($paymentMethodId, $safePaymentMethodIds);
    }

    /**
     * @param string $paymentMethodId
     * @return bool
     */
    public function isUnsafe($paymentMethodId)
    {
        $unsafePaymentMethodIds = $this->pluginConfig->getUnsafePaymentMethods();
        return in_array($paymentMethodId, $unsafePaymentMethodIds);
    }

    /**
     * @param string $paymentMethodId
     * @return bool
     */
    public function isNotConfigured($paymentMethodId)
    {
        return !$this->isIgnored($paymentMethodId)
            && !$this->isSafe($paymentMethodId)
            && !$this->isUnsafe($paymentMethodId);
    }
}
