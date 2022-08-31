<?php

declare(strict_types=1);

namespace Axytos\DecisionExpert\Shopware\Configuration;

class PluginConfigurationValidator
{
    private PluginConfiguration $pluginConfiguration;

    public function __construct(PluginConfiguration $pluginConfiguration)
    {
        $this->pluginConfiguration = $pluginConfiguration;
    }

    public function isInvalid(): bool
    {
        try {
            return $this->apiKeyIsNotConfigured()
                || $this->paymentMethodsAreNotConfigured();
        } catch (\Throwable $th) {
            return true;
        }
    }

    private function apiKeyIsNotConfigured(): bool
    {
        return empty($this->pluginConfiguration->getApiKey());
    }

    private function paymentMethodsAreNotConfigured(): bool
    {
        return empty($this->pluginConfiguration->getSafePaymentMethods())
            && empty($this->pluginConfiguration->getUnsafePaymentMethods())
            && empty($this->pluginConfiguration->getIgnoredPaymentMethods());
    }
}
