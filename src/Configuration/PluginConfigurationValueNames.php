<?php

declare(strict_types=1);

namespace Axytos\DecisionExpert\Shopware\Configuration;

abstract class PluginConfigurationValueNames
{
    public const API_HOST = 'AxytosDecisionExpert.config.apiHost';
    public const API_KEY = 'AxytosDecisionExpert.config.apiKey';
    public const SAFE_PAYMENT_METHODS = 'AxytosDecisionExpert.config.safePaymentMethods';
    public const UNSAFE_PAYMENT_METHODS = 'AxytosDecisionExpert.config.unsafePaymentMethods';
    public const IGNORED_PAYMENT_METHODS = 'AxytosDecisionExpert.config.ignoredPaymentMethods';
    public const FALLBACK_MODE = 'AxytosDecisionExpert.config.fallBackMode';
}
