<?php

declare(strict_types=1);

namespace Axytos\DecisionExpert\Shopware\Storefront\CheckoutConfirmPage;

use Shopware\Core\Framework\Struct\Struct;

class CheckoutConfirmPageExtension extends Struct
{
    public bool $showCreditCheckAgreement;
    public string $creditCheckAgreementInfo;
}
