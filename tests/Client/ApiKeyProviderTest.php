<?php declare(strict_types=1);

namespace Axytos\DecisionExpert\Shopware\Tests\Client;

use Axytos\ECommerce\Abstractions\ApiKeyProviderInterface;
use Axytos\DecisionExpert\Shopware\Client\ApiKeyProvider;
use Axytos\DecisionExpert\Shopware\Configuration\PluginConfiguration;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ApiKeyProviderTest extends TestCase
{
    /** @var PluginConfiguration&MockObject $pluginConfiguration */
    private PluginConfiguration $pluginConfiguration;
    private ApiKeyProvider $sut;

    public function setUp(): void
    {
        $this->pluginConfiguration = $this->createMock(PluginConfiguration::class);

        $this->sut = new ApiKeyProvider(
            $this->pluginConfiguration
        );
    }

    public function test_implements_ApiKeyProviderInterface(): void
    {
        $this->assertInstanceOf(ApiKeyProviderInterface::class, $this->sut);
    }

    public function test_getApiKey_returns_api_key_from_configuration(): void
    {
        $expected = 'apikey';
        $this->pluginConfiguration
            ->method('getApiKey')
            ->willReturn($expected);

        $actual = $this->sut->getApiKey();

        $this->assertSame($expected, $actual);
    }
}