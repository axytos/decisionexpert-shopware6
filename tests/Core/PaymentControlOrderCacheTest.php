<?php

declare(strict_types=1);

namespace Axytos\DecisionExpert\Shopware\Tests\Core;

use Axytos\ECommerce\DataTransferObjects\PaymentControlCheckResponseDto;
use Axytos\DecisionExpert\Shopware\Core\PaymentControlOrderCache;
use Axytos\ECommerce\DataMapping\DtoArrayMapper;
use Axytos\Shopware\DataAbstractionLayer\OrderEntityRepository;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class PaymentControlOrderCacheTest extends TestCase
{
    private const CUSTOM_FIELD_NAME_CHECK_RESPONSE = 'axytos_decision_expert_check_response';
    private const CUSTOM_FIELD_NAME_DATA_HASH = 'axytos_decision_expert_order_data_hash';

    private string $orderId;

    /** @var SalesChannelContext&MockObject */
    private SalesChannelContext $salesChannelContext;

    /** @var OrderEntityRepository&MockObject */
    private OrderEntityRepository $orderEntityRepository;

    /** @var DtoArrayMapper&MockObject */
    private DtoArrayMapper $dtoArrayMapper;

    private PaymentControlOrderCache $sut;

    public function setUp(): void
    {
        $this->orderId = 'orderId';
        $this->salesChannelContext = $this->createMock(SalesChannelContext::class);
        $this->orderEntityRepository = $this->createMock(OrderEntityRepository::class);
        $this->dtoArrayMapper = $this->createMock(DtoArrayMapper::class);

        $this->sut = new PaymentControlOrderCache(
            $this->orderId,
            $this->salesChannelContext,
            $this->orderEntityRepository,
            $this->dtoArrayMapper
        );

        $this->setUpSalesChannelContext();
    }

    private function setUpSalesChannelContext(): void
    {
        $this->salesChannelContext->method('getContext')->willReturn($this->createMock(Context::class));
    }

    private function setUpCustomFields(array $customFields): void
    {
        $this->orderEntityRepository
            ->method('getCustomFields')
            ->with($this->orderId, $this->salesChannelContext->getContext())
            ->willReturn($customFields);
    }

    public function test_getCheckResponse_returns_null_if_custom_field_is_not_set(): void
    {
        $this->setUpCustomFields([]);

        $actual = $this->sut->getCheckResponse();

        $this->assertNull($actual);
    }

    public function test_getCheckResponse_returns_PaymentControlCheckResponseDto_if_custom_field_is_set(): void
    {
        $responseDto = $this->createMock(PaymentControlCheckResponseDto::class);
        $responseArray = [];

        $this->setUpCustomFields([
            self::CUSTOM_FIELD_NAME_CHECK_RESPONSE => $responseArray
        ]);

        $this->dtoArrayMapper
            ->method('fromArray')
            ->with($responseArray, PaymentControlCheckResponseDto::class)
            ->willReturn($responseDto);

        $actual = $this->sut->getCheckResponse();

        $this->assertSame($responseDto, $actual);
    }

    public function test_setCheckResponse_saves_PaymentControlCheckResponseDto_as_custom_field_array(): void
    {
        $responseDto = $this->createMock(PaymentControlCheckResponseDto::class);
        $responseArray = [];

        $this->dtoArrayMapper
            ->method('toArray')
            ->with($responseDto)
            ->willReturn($responseArray);

        $this->orderEntityRepository
            ->expects($this->once())
            ->method('updateCustomFields')
            ->with($this->orderId, [self::CUSTOM_FIELD_NAME_CHECK_RESPONSE => $responseArray], $this->salesChannelContext->getContext());

        $this->sut->setCheckResponse($responseDto);
    }

    public function test_getCheckRequestHash_returns_null_if_custom_field_is_not_set(): void
    {
        $this->setUpCustomFields([]);

        $actual = $this->sut->getCheckRequestHash();

        $this->assertNull($actual);
    }

    public function test_getCheckRequestHash_returns_hash_if_custom_field_is_set(): void
    {
        $hash = 'hash';

        $this->setUpCustomFields([
            self::CUSTOM_FIELD_NAME_DATA_HASH => $hash
        ]);

        $actual = $this->sut->getCheckRequestHash();

        $this->assertSame($hash, $actual);
    }

    public function test_setCheckRequestHash_saves_hash_as_custom_field(): void
    {
        $hash = 'hash';

        $this->orderEntityRepository
            ->expects($this->once())
            ->method('updateCustomFields')
            ->with($this->orderId, [self::CUSTOM_FIELD_NAME_DATA_HASH => $hash], $this->salesChannelContext->getContext());

        $this->sut->setCheckRequestHash($hash);
    }
}
