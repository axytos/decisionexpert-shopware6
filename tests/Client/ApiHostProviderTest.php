<?php declare(strict_types=1);

namespace Axytos\DecisionExpert\Shopware\Tests\Client;

use Axytos\ECommerce\Abstractions\ApiHostProviderInterface;
use Axytos\DecisionExpert\Shopware\Client\ApiHostProvider;
use Axytos\DecisionExpert\Shopware\Configuration\PluginConfiguration;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ApiHostProviderTest extends TestCase
{
    /** @var PluginConfiguration&MockObject */
    private PluginConfiguration $pluginConfiguration;

    private ApiHostProvider $sut;

    public function setUp(): void
    {
        $this->pluginConfiguration = $this->createMock(PluginConfiguration::class);

        $this->sut = new ApiHostProvider(
            $this->pluginConfiguration
        );
    }

    public function test_implements_ApiHostProviderInterface(): void
    {
        $this->assertInstanceOf(ApiHostProviderInterface::class, $this->sut);
    }

    public function test_getApiHost_returns_api_key_from_configuration(): void
    {
        $expected = 'apihost';
        $this->pluginConfiguration
            ->method('getApiHost')
            ->willReturn($expected);

        $actual = $this->sut->getApiHost();

        $this->assertSame($expected, $actual);
    }
}