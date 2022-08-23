<?php declare(strict_types=1);

namespace Axytos\DecisionExpert\Shopware\Tests\Core;

use Axytos\DecisionExpert\Shopware\Core\PaymentControlOrderCache;
use Axytos\DecisionExpert\Shopware\Core\PaymentControlOrderCacheFactory;
use Axytos\ECommerce\DataMapping\DtoArrayMapper;
use Axytos\Shopware\DataAbstractionLayer\OrderEntityRepository;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class PaymentControlOrderCacheFactoryTest extends TestCase
{
    private PaymentControlOrderCacheFactory $sut;

    public function setUp(): void
    {
        $this->sut = new PaymentControlOrderCacheFactory(
            $this->createMock(OrderEntityRepository::class),
            $this->createMock(DtoArrayMapper::class)
        );
    }

    public function test_create_creates_PaymentControlOrderCache(): void
    {
        $orderId = 'orderId';

        /** @var SalesChannelContext&MockObject $salesChannelContext */
        $salesChannelContext = $this->createMock(SalesChannelContext::class);

        $actual = $this->sut->create($orderId, $salesChannelContext);

        $this->assertInstanceOf(PaymentControlOrderCache::class, $actual);
    }
}
