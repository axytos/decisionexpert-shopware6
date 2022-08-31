<?php

declare(strict_types=1);

namespace Axytos\DecisionExpert\Shopware\Client;

use Axytos\ECommerce\Abstractions\FallbackModeConfigurationInterface;
use Axytos\ECommerce\Abstractions\FallbackModes;
use Axytos\DecisionExpert\Shopware\Configuration\PluginConfiguration;

class FallbackModeConfiguration implements FallbackModeConfigurationInterface
{
    public PluginConfiguration $pluginConfig;

    public function __construct(PluginConfiguration $pluginConfig)
    {
        $this->pluginConfig = $pluginConfig;
    }

    public function getFallbackMode(): string
    {
        $value = $this->pluginConfig->getFallbackMode();

        switch ($value) {
            case 'ALL_PAYMENT_METHODS':
                return FallbackModes::ALL_PAYMENT_METHODS;
            case 'NO_UNSAFE_PAYMENT_METHODS':
                return FallbackModes::NO_UNSAFE_PAYMENT_METHODS;
            case 'IGNORED_AND_NOT_CONFIGURED_PAYMENT_METHODS':
                return FallbackModes::IGNORED_AND_NOT_CONFIGURED_PAYMENT_METHODS;
            default:
                return FallbackModes::ALL_PAYMENT_METHODS;
        }
    }
}
