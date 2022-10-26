<?php

declare(strict_types=1);

namespace Axytos\DecisionExpert\Shopware\Tests\Client;

use Axytos\ECommerce\Abstractions\FallbackModeConfigurationInterface;
use Axytos\ECommerce\Abstractions\FallbackModes;
use Axytos\DecisionExpert\Shopware\Client\FallbackModeConfiguration;
use Axytos\DecisionExpert\Shopware\Configuration\PluginConfiguration;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FallbackModeConfigurationTest extends TestCase
{
    /** @var PluginConfiguration&MockObject $pluginConfiguration */
    private PluginConfiguration $pluginConfiguration;

    private FallbackModeConfiguration $sut;

    public function setUp(): void
    {
        $this->pluginConfiguration = $this->createMock(PluginConfiguration::class);

        $this->sut = new FallbackModeConfiguration(
            $this->pluginConfiguration
        );
    }

    public function test_implements_FallbackModeConfigurationInterface(): void
    {
        $this->assertInstanceOf(FallbackModeConfigurationInterface::class, $this->sut);
    }

    public function test_getFallbackMode_returns_ALL_PAYMENT_METHODS_when_plugin_is_configured_to_use_all_payment_methods(): void
    {
        $this->pluginConfiguration
            ->method('getFallbackMode')
            ->willReturn('ALL_PAYMENT_METHODS');

        $actual = $this->sut->getFallbackMode();

        $this->assertEquals(FallbackModes::ALL_PAYMENT_METHODS, $actual);
    }

    public function test_getFallbackMode_returns_NO_UNSAFE_PAYMENT_METHODS_when_plugin_is_configured_to_use_no_unsafe_payment_methods(): void
    {
        $this->pluginConfiguration
            ->method('getFallbackMode')
            ->willReturn('NO_UNSAFE_PAYMENT_METHODS');

        $actual = $this->sut->getFallbackMode();

        $this->assertEquals(FallbackModes::NO_UNSAFE_PAYMENT_METHODS, $actual);
    }

    public function test_getFallbackMode_returns_IGNORED_AND_NOT_CONFIGURED_PAYMENT_METHODS_when_plugin_is_configured_to_use_only_ignored_and_not_configured_payment_methods(): void
    {
        $this->pluginConfiguration
            ->method('getFallbackMode')
            ->willReturn('IGNORED_AND_NOT_CONFIGURED_PAYMENT_METHODS');

        $actual = $this->sut->getFallbackMode();

        $this->assertEquals(FallbackModes::IGNORED_AND_NOT_CONFIGURED_PAYMENT_METHODS, $actual);
    }

    public function test_getFallbackMode_returns_ALL_PAYMENT_METHODS_as_default(): void
    {
        $this->pluginConfiguration
            ->method('getFallbackMode')
            ->willReturn('anything');

        $actual = $this->sut->getFallbackMode();

        $this->assertEquals(FallbackModes::ALL_PAYMENT_METHODS, $actual);
    }
}
