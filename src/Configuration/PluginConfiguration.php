<?php

declare(strict_types=1);

namespace Axytos\DecisionExpert\Shopware\Configuration;

use Shopware\Core\System\SystemConfig\SystemConfigService;

class PluginConfiguration
{
    private SystemConfigService $systemConfigService;

    public function __construct(SystemConfigService $systemConfigService)
    {
        $this->systemConfigService = $systemConfigService;
    }

    public function getApiHost(): string
    {
        return $this->systemConfigService->getString(PluginConfigurationValueNames::API_HOST);
    }

    public function getApiKey(): string
    {
        return $this->systemConfigService->getString(PluginConfigurationValueNames::API_KEY);
    }

    /** @return string[] */
    public function getSafePaymentMethods(): array
    {
        return $this->getArray(PluginConfigurationValueNames::SAFE_PAYMENT_METHODS);
    }

    /** @return string[] */
    public function getUnsafePaymentMethods(): array
    {
        return $this->getArray(PluginConfigurationValueNames::UNSAFE_PAYMENT_METHODS);
    }

    /** @return string[] */
    public function getIgnoredPaymentMethods(): array
    {
        return $this->getArray(PluginConfigurationValueNames::IGNORED_PAYMENT_METHODS);
    }

    public function getFallbackMode(): string
    {
        return $this->systemConfigService->getString(PluginConfigurationValueNames::FALLBACK_MODE);
    }

    /** @return string[] */
    private function getArray(string $key)
    {
        $value = $this->systemConfigService->get($key);

        if (!is_array($value)) {
            return [];
        }

        return $value;
    }
}
