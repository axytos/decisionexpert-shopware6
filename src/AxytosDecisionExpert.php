<?php declare(strict_types=1);

namespace Axytos\DecisionExpert\Shopware;

use Shopware\Core\Framework\Plugin;

if (file_exists(__DIR__ . '/../vendor/autoload.php'))
{
    require_once(__DIR__ . '/../vendor/autoload.php');
}

class AxytosDecisionExpert extends Plugin
{
}