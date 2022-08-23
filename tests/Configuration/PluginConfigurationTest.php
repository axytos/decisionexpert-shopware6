<?php declare(strict_types=1);

namespace Axytos\DecisionExpert\Shopware\Tests;

use Axytos\DecisionExpert\Shopware\Configuration\PluginConfiguration;
use Axytos\DecisionExpert\Shopware\Configuration\PluginConfigurationValueNames;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class PluginConfigurationTest extends TestCase
{
    /** @var SystemConfigService&MockObject $systemConfigService */
    private SystemConfigService $systemConfigService;
    private PluginConfiguration $sut;

    public function setUp(): void
    {
        $this->systemConfigService = $this->createMock(SystemConfigService::class);
        
        $this->sut = new PluginConfiguration($this->systemConfigService);
    }

    /** @param mixed $value */
    private function setSystemConfigServiceReturnValue(string $name, $value): void
    {
        $this->systemConfigService
            ->method('get')
            ->with($name)
            ->willReturn($value);
    }

    public function test_getApiHost_returns_api_host_from_configuration(): void
    {
        $expected = 'apiHost';

        $this->systemConfigService
            ->method('getString')
            ->with(PluginConfigurationValueNames::API_HOST)
            ->willReturn($expected);

        $actual = $this->sut->getApiHost();

        $this->assertEquals($expected, $actual);
    }

    public function test_getApiKey_returns_api_key_from_configuration(): void
    {
        $expected = 'apiKey';

        $this->systemConfigService
            ->method('getString')
            ->with(PluginConfigurationValueNames::API_KEY)
            ->willReturn($expected);

        $actual = $this->sut->getApiKey();

        $this->assertEquals($expected, $actual);
    }

    public function test_getSafePaymentMethods_returns_safe_payment_methods_from_configuration(): void
    {
        $expected = ['paymentMethodId'];
        $this->setSystemConfigServiceReturnValue(PluginConfigurationValueNames::SAFE_PAYMENT_METHODS, $expected);

        $actual = $this->sut->getSafePaymentMethods();

        $this->assertEquals($expected, $actual);
    }

    public function test_getSafePaymentMethods_returns_empty_array_if_safe_payment_methods_are_not_configured(): void
    {
        $notArrayValues = [5, 3.0, true, 'a string', null];

        foreach($notArrayValues as $notArrayValue)
        {
            $this->setSystemConfigServiceReturnValue(PluginConfigurationValueNames::SAFE_PAYMENT_METHODS, $notArrayValue);

            $result = $this->sut->getSafePaymentMethods();
    
            $this->assertIsArray($result);
            $this->assertEmpty($result);
        }
    }

    public function test_getUnsafePaymentMethods_returns_unsafe_payment_methods_from_configuration(): void
    {
        $expected = ['paymentMethodId'];
        $this->setSystemConfigServiceReturnValue(PluginConfigurationValueNames::UNSAFE_PAYMENT_METHODS, $expected);

        $actual = $this->sut->getUnsafePaymentMethods();

        $this->assertEquals($expected, $actual);
    }

    public function test_getUnsafePaymentMethods_returns_empty_array_if_unsafe_payment_methods_are_not_configured(): void
    {
        $notArrayValues = [5, 3.0, true, 'a string', null];

        foreach($notArrayValues as $notArrayValue)
        {
            $this->setSystemConfigServiceReturnValue(PluginConfigurationValueNames::UNSAFE_PAYMENT_METHODS, $notArrayValue);

            $result = $this->sut->getUnsafePaymentMethods();
    
            $this->assertIsArray($result);
            $this->assertEmpty($result);
        }
    }

    public function test_getIgnoredPaymentMethods_returns_ignored_payment_methods_from_configuration(): void
    {
        $expected = ['paymentMethodId'];
        $this->setSystemConfigServiceReturnValue(PluginConfigurationValueNames::IGNORED_PAYMENT_METHODS, $expected);

        $actual = $this->sut->getIgnoredPaymentMethods();

        $this->assertEquals($expected, $actual);
    }

    public function test_getIgnoredPaymentMethods_returns_empty_array_if_ignored_payment_methods_are_not_configured(): void
    {
        $notArrayValues = [5, 3.0, true, 'a string', null];

        foreach($notArrayValues as $notArrayValue)
        {
            $this->setSystemConfigServiceReturnValue(PluginConfigurationValueNames::IGNORED_PAYMENT_METHODS, $notArrayValue);

            $result = $this->sut->getIgnoredPaymentMethods();
    
            $this->assertIsArray($result);
            $this->assertEmpty($result);
        }        
    }

    public function test_getFallbackMode_returns_fallback_mode_from_configuration(): void
    {
        $expected = 'fallbackMode';

        $this->systemConfigService
            ->method('getString')
            ->with(PluginConfigurationValueNames::FALLBACK_MODE)
            ->willReturn($expected);

        $actual = $this->sut->getFallbackMode();

        $this->assertEquals($expected, $actual);
    }
}