<?php declare(strict_types=1);

namespace Axytos\DecisionExpert\Shopware\Tests;

use Axytos\DecisionExpert\Shopware\AxytosDecisionExpert;
use PHPUnit\Framework\TestCase;

class AxytosDecisionExpertTest extends TestCase
{
    public function test_AxytosDecisionExpert_can_be_constructed(): void
    {
        $plugin = new AxytosDecisionExpert(true, 'basePath');

        $this->assertNotNull($plugin);
    }
}