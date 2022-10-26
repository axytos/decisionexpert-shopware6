<?php

declare(strict_types=1);

namespace Axytos\DecisionExpert\Shopware\Tests;

use Axytos\DecisionExpert\Shopware\Configuration\PluginConfiguration;
use Axytos\DecisionExpert\Shopware\Configuration\PluginConfigurationValidator;
use Exception;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class PluginConfigurationValidatorTest extends TestCase
{
    /** @var PluginConfiguration&MockObject */
    private PluginConfiguration $pluginConfiguration;

    private PluginConfigurationValidator $sut;

    public function setUp(): void
    {
        $this->pluginConfiguration = $this->createMock(PluginConfiguration::class);

        $this->sut = new PluginConfigurationValidator($this->pluginConfiguration);
    }

    /**
     * @dataProvider isInvalidTestDataProvider
     */
    public function test_isInvalid(
        string $apiKey,
        array $safePaymentMethodIds,
        array $unsafePaymentMethodIds,
        array $ignoredPaymentMethodIds,
        bool $expectedOutcome
    ): void {
        $this->pluginConfiguration->method('getApiKey')->willReturn($apiKey);
        $this->pluginConfiguration->method('getSafePaymentMethods')->willReturn($safePaymentMethodIds);
        $this->pluginConfiguration->method('getUnsafePaymentMethods')->willReturn($unsafePaymentMethodIds);
        $this->pluginConfiguration->method('getIgnoredPaymentMethods')->willReturn($ignoredPaymentMethodIds);

        $actual = $this->sut->isInvalid();

        $this->assertEquals($expectedOutcome, $actual);
    }

    public function isInvalidTestDataProvider(): array
    {
        return [
            ['',[],[],[],true],
            ['',['id'],[],[],true],
            ['',[],['id'],[],true],
            ['',[],[],['id'],true],
            ['apiKey',[],[],[],true],
            ['apiKey',['id'],[],[],false],
            ['apiKey',[],['id'],[],false],
            ['apiKey',[],[],['id'],false],
        ];
    }

    public function test_isInvalid_returns_true_getApiKey_throws_Exception(): void
    {
        $this->pluginConfiguration->method('getApiKey')->willThrowException(new Exception());

        $this->assertTrue($this->sut->isInvalid());
    }

    public function test_isInvalid_returns_true_getSafePaymentMethods_throws_Exception(): void
    {
        $this->pluginConfiguration->method('getApiKey')->willReturn('apiKey');

        $this->pluginConfiguration
            ->expects($this->once())
            ->method('getSafePaymentMethods')
            ->willThrowException(new Exception());

        $this->assertTrue($this->sut->isInvalid());
    }

    public function test_isInvalid_returns_true_getUnsafePaymentMethods_throws_Exception(): void
    {
        $this->pluginConfiguration->method('getApiKey')->willReturn('apiKey');

        $this->pluginConfiguration
            ->expects($this->once())
            ->method('getUnsafePaymentMethods')
            ->willThrowException(new Exception());

        $this->assertTrue($this->sut->isInvalid());
    }

    public function test_isInvalid_returns_true_getIgnoredPaymentMethods_throws_Exception(): void
    {
        $this->pluginConfiguration->method('getApiKey')->willReturn('apiKey');

        $this->pluginConfiguration
            ->expects($this->once())
            ->method('getIgnoredPaymentMethods')
            ->willThrowException(new Exception());

        $this->assertTrue($this->sut->isInvalid());
    }
}
