<?php

declare(strict_types=1);

namespace Axytos\DecisionExpert\Shopware\Core;

use Axytos\ECommerce\Clients\PaymentControl\PaymentControlCacheInterface;
use Axytos\ECommerce\DataMapping\DtoArrayMapper;
use Axytos\ECommerce\DataTransferObjects\PaymentControlCheckResponseDto;
use Axytos\Shopware\DataAbstractionLayer\OrderEntityRepository;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class PaymentControlOrderCache implements PaymentControlCacheInterface
{
    private const CUSTOM_FIELD_NAME_CHECK_RESPONSE = 'axytos_decision_expert_check_response';
    private const CUSTOM_FIELD_NAME_DATA_HASH = 'axytos_decision_expert_order_data_hash';

    private string $orderId;
    private SalesChannelContext $salesChannelContext;
    private OrderEntityRepository $orderEntityRepository;
    private DtoArrayMapper $dtoArrayMapper;

    public function __construct(
        string $orderId,
        SalesChannelContext $salesChannelContext,
        OrderEntityRepository $orderEntityRepository,
        DtoArrayMapper $dtoArrayMapper
    ) {
        $this->orderId = $orderId;
        $this->salesChannelContext = $salesChannelContext;
        $this->orderEntityRepository = $orderEntityRepository;
        $this->dtoArrayMapper = $dtoArrayMapper;
    }

    /**
     * @return \Axytos\ECommerce\DataTransferObjects\PaymentControlCheckResponseDto|null
     */
    public function getCheckResponse()
    {
        $customFields = $this->orderEntityRepository->getCustomFields($this->orderId, $this->getContext());

        if (!array_key_exists(self::CUSTOM_FIELD_NAME_CHECK_RESPONSE, $customFields)) {
            return null;
        }

        return $this->dtoArrayMapper->fromArray($customFields[self::CUSTOM_FIELD_NAME_CHECK_RESPONSE], PaymentControlCheckResponseDto::class);
    }

    /**
     * @param \Axytos\ECommerce\DataTransferObjects\PaymentControlCheckResponseDto $checkResponse
     * @return void
     */
    public function setCheckResponse($checkResponse)
    {
        $customFields = [
            self::CUSTOM_FIELD_NAME_CHECK_RESPONSE => $this->dtoArrayMapper->toArray($checkResponse)
        ];

        $this->orderEntityRepository->updateCustomFields($this->orderId, $customFields, $this->getContext());
    }

    /**
     * @return string|null
     */
    public function getCheckRequestHash()
    {
        $customFields = $this->orderEntityRepository->getCustomFields($this->orderId, $this->getContext());

        if (!array_key_exists(self::CUSTOM_FIELD_NAME_DATA_HASH, $customFields)) {
            return null;
        }

        return $customFields[self::CUSTOM_FIELD_NAME_DATA_HASH];
    }

    /**
     * @param string $hash
     * @return void
     */
    public function setCheckRequestHash($hash)
    {
        $customFields = [
            self::CUSTOM_FIELD_NAME_DATA_HASH => $hash
        ];

        $this->orderEntityRepository->updateCustomFields($this->orderId, $customFields, $this->getContext());
    }

    private function getContext(): Context
    {
        return $this->salesChannelContext->getContext();
    }
}
