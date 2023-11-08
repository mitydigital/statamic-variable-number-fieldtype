<?php

namespace MityDigital\StatamicVariableNumberFieldtype\Support;

use NumberFormatter;
use Statamic\Facades\Site;

class VariableNumber
{
    public function formatCurrency($value, string $currencyCode): string
    {
        return NumberFormatter::create(Site::current()->locale(), NumberFormatter::CURRENCY)
            ->formatCurrency($value, $currencyCode);
    }

    public function formatDecimal($value, int $decimalPlaces): string
    {
        return number_format($value, $decimalPlaces);
    }

    public function formatInteger($value): string
    {
        return filter_var(round($value), FILTER_VALIDATE_INT);
    }
}
