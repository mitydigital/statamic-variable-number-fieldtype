<?php

namespace MityDigital\StatamicVariableNumberFieldtype\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string formatCurrency($value, string $currencyCode)
 * @method static string formatDecimal($value, int $decimalPlaces)
 * @method static string formatInteger($value)
 *
 * @see StatamicStripeProductFieldtype
 */
class VariableNumber extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \MityDigital\StatamicVariableNumberFieldtype\Support\VariableNumber::class;
    }
}
