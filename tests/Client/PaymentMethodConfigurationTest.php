<?php

declare(strict_types=1);

namespace Axytos\DecisionExpert\Shopware\Tests\Client;

use Axytos\ECommerce\Abstractions\PaymentMethodConfigurationInterface;
use Axytos\DecisionExpert\Shopware\Client\PaymentMethodConfiguration;
use Axytos\DecisionExpert\Shopware\Configuration\PluginConfiguration;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class PaymentMethodConfigurationTest extends TestCase
{
    /** @var PluginConfiguration&MockObject $pluginConfiguration */
    private PluginConfiguration $pluginConfiguration;

    private PaymentMethodConfiguration $sut;

    public function setUp(): void
    {
        $this->pluginConfiguration = $this->createMock(PluginConfiguration::class);

        $this->sut = new PaymentMethodConfiguration(
            $this->pluginConfiguration
        );
    }

    public function test_implements_PaymentMethodConfigurationInterface(): void
    {
        $this->assertInstanceOf(PaymentMethodConfigurationInterface::class, $this->sut);
    }

    /**
     * @dataProvider isSafeTestDataProvider
     */
    public function test_isSafe(
        string $paymentMethodId,
        array $safePaymentMethodIds,
        array $unsafePaymentMethodIds,
        array $ignoredPaymentMethodIds,
        bool $expectedOutcome
    ): void {
        $this->pluginConfiguration->method('getSafePaymentMethods')->willReturn($safePaymentMethodIds);
        $this->pluginConfiguration->method('getUnsafePaymentMethods')->willReturn($unsafePaymentMethodIds);
        $this->pluginConfiguration->method('getIgnoredPaymentMethods')->willReturn($ignoredPaymentMethodIds);

        $actual = $this->sut->isSafe($paymentMethodId);

        $this->assertEquals($expectedOutcome, $actual);
    }

    public function isSafeTestDataProvider(): array
    {
        // id, safe, unsafe, ignored, outcome
        return [
            ['id1', [], [], [], false],
            ['id1', ['id1'], [], [], true],
            ['id1', [], ['id1'], [], false],
            ['id1', [], [], ['id1'], false],
            ['id1', ['id2'], ['id3'], ['id4'], false],
            ['id2', ['id2'], ['id3'], ['id4'], true],
            ['id3', ['id2'], ['id3'], ['id4'], false],
            ['id4', ['id2'], ['id3'], ['id4'], false],
            ['id1', ['id2','id1'], ['id3'], ['id4'], true],
            ['id1', ['id2'], ['id3','id1'], ['id4'], false],
            ['id1', ['id2'], ['id3'], ['id4','id1'], false],
        ];
    }


    /**
     * @dataProvider isUnsafeTestDataProvider
     */
    public function test_isUnsafe(
        string $paymentMethodId,
        array $safePaymentMethodIds,
        array $unsafePaymentMethodIds,
        array $ignoredPaymentMethodIds,
        bool $expectedOutcome
    ): void {
        $this->pluginConfiguration->method('getSafePaymentMethods')->willReturn($safePaymentMethodIds);
        $this->pluginConfiguration->method('getUnsafePaymentMethods')->willReturn($unsafePaymentMethodIds);
        $this->pluginConfiguration->method('getIgnoredPaymentMethods')->willReturn($ignoredPaymentMethodIds);

        $actual = $this->sut->isUnsafe($paymentMethodId);

        $this->assertEquals($expectedOutcome, $actual);
    }

    public function isUnsafeTestDataProvider(): array
    {
        // id, safe, unsafe, ignored, outcome
        return [
            ['id1', [], [], [], false],
            ['id1', ['id1'], [], [], false],
            ['id1', [], ['id1'], [], true],
            ['id1', [], [], ['id1'], false],
            ['id1', ['id2'], ['id3'], ['id4'], false],
            ['id2', ['id2'], ['id3'], ['id4'], false],
            ['id3', ['id2'], ['id3'], ['id4'], true],
            ['id4', ['id2'], ['id3'], ['id4'], false],
            ['id1', ['id2','id1'], ['id3'], ['id4'], false],
            ['id1', ['id2'], ['id3','id1'], ['id4'], true],
            ['id1', ['id2'], ['id3'], ['id4','id1'], false],
        ];
    }


    /**
     * @dataProvider isIgnoredTestDataProvider
     */
    public function test_isIgnored(
        string $paymentMethodId,
        array $safePaymentMethodIds,
        array $unsafePaymentMethodIds,
        array $ignoredPaymentMethodIds,
        bool $expectedOutcome
    ): void {
        $this->pluginConfiguration->method('getSafePaymentMethods')->willReturn($safePaymentMethodIds);
        $this->pluginConfiguration->method('getUnsafePaymentMethods')->willReturn($unsafePaymentMethodIds);
        $this->pluginConfiguration->method('getIgnoredPaymentMethods')->willReturn($ignoredPaymentMethodIds);

        $actual = $this->sut->isIgnored($paymentMethodId);

        $this->assertEquals($expectedOutcome, $actual);
    }

    public function isIgnoredTestDataProvider(): array
    {
        // id, safe, unsafe, ignored, outcome
        return [
            ['id1', [], [], [], false],
            ['id1', ['id1'], [], [], false],
            ['id1', [], ['id1'], [], false],
            ['id1', [], [], ['id1'], true],
            ['id1', ['id2'], ['id3'], ['id4'], false],
            ['id2', ['id2'], ['id3'], ['id4'], false],
            ['id3', ['id2'], ['id3'], ['id4'], false],
            ['id4', ['id2'], ['id3'], ['id4'], true],
            ['id1', ['id2','id1'], ['id3'], ['id4'], false],
            ['id1', ['id2'], ['id3','id1'], ['id4'], false],
            ['id1', ['id2'], ['id3'], ['id4','id1'], true],
        ];
    }

    /**
     * @dataProvider isNotConfiguredTestDataProvider
     */
    public function test_isNotConfigured(
        string $paymentMethodId,
        array $safePaymentMethodIds,
        array $unsafePaymentMethodIds,
        array $ignoredPaymentMethodIds,
        bool $expectedOutcome
    ): void {
        $this->pluginConfiguration->method('getSafePaymentMethods')->willReturn($safePaymentMethodIds);
        $this->pluginConfiguration->method('getUnsafePaymentMethods')->willReturn($unsafePaymentMethodIds);
        $this->pluginConfiguration->method('getIgnoredPaymentMethods')->willReturn($ignoredPaymentMethodIds);

        $actual = $this->sut->isNotConfigured($paymentMethodId);

        $this->assertEquals($expectedOutcome, $actual);
    }

    public function isNotConfiguredTestDataProvider(): array
    {
        return [
            ['id1', [], [], [], true],
            ['id1', ['id1'], [], [], false],
            ['id1', [], ['id1'], [], false],
            ['id1', [], [], ['id1'], false],
            ['id1', ['id2'], ['id3'], ['id4'], true],
            ['id2', ['id2'], ['id3'], ['id4'], false],
            ['id3', ['id2'], ['id3'], ['id4'], false],
            ['id4', ['id2'], ['id3'], ['id4'], false],
            ['id1', ['id2','id1'], ['id3'], ['id4'], false],
            ['id1', ['id2'], ['id3','id1'], ['id4'], false],
            ['id1', ['id2'], ['id3'], ['id4','id1'], false],
        ];
    }
}
