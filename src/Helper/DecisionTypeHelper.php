<?php declare(strict_types=1);

namespace App\Helper;

use App\Enum\DecisionType;

class DecisionTypeHelper
{
    public function map(int $decisionType): string
    {
        $decisionText = '';
        switch ($decisionType) {
            case DecisionType::REJECTED:
                $decisionText = 'Reddedildi';
                break;
            case DecisionType::APPROVED:
                $decisionText = 'Kabul Edildi';
                break;
            case DecisionType::NOT_DECIDED:
                $decisionText = 'Karar Verilmedi';
                break;
            case DecisionType::MAYBE_APPROVED:
                $decisionText = 'Kabule Yakın';
                break;
        }
        return $decisionText;
    }
}