<?php declare(strict_types=1);

namespace Axytos\DecisionExpert\Shopware\Client;

use Axytos\DecisionExpert\Shopware\Configuration\PluginConfiguration;
use Axytos\ECommerce\Abstractions\ApiHostProviderInterface;

class ApiHostProvider implements ApiHostProviderInterface
{
    public PluginConfiguration $pluginConfig;

    public function __construct(PluginConfiguration $pluginConfig)
    {
        $this->pluginConfig = $pluginConfig;
    }

    public function getApiHost(): string
    {
        return $this->pluginConfig->getApiHost();
    }
}