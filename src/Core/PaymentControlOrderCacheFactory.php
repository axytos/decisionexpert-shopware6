<?php

declare(strict_types=1);

namespace Axytos\DecisionExpert\Shopware\Core;

use Axytos\ECommerce\DataMapping\DtoArrayMapper;
use Axytos\Shopware\DataAbstractionLayer\OrderEntityRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class PaymentControlOrderCacheFactory
{
    private OrderEntityRepository $orderEntityRepository;
    private DtoArrayMapper $dtoArrayMapper;

    public function __construct(
        OrderEntityRepository $orderEntityRepository,
        DtoArrayMapper $dtoArrayMapper
    ) {
        $this->orderEntityRepository = $orderEntityRepository;
        $this->dtoArrayMapper = $dtoArrayMapper;
    }

    public function create(string $orderId, SalesChannelContext $salesChannelContext): PaymentControlOrderCache
    {
        return new PaymentControlOrderCache(
            $orderId,
            $salesChannelContext,
            $this->orderEntityRepository,
            $this->dtoArrayMapper
        );
    }
}
