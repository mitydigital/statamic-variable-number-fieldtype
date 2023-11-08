<?php

use MityDigital\StatamicVariableNumberFieldtype\Support\VariableNumber;

beforeEach(function () {
    $this->support = app(VariableNumber::class);
});

it('correctly formats a number to a given currency', function () {
    // $1.00
    expect($this->support->formatCurrency(1, 'AUD'))
        ->toBe('$1.00');

    // $1.50
    expect($this->support->formatCurrency(1.5, 'AUD'))
        ->toBe('$1.50');

    // $1.999
    expect($this->support->formatCurrency(1.999, 'AUD'))
        ->toBe('$2.00');

    // USD 1.00
    expect($this->support->formatCurrency(1, 'USD'))
        ->toBe('USD'."\u{A0}".'1.00');

    // USD 1.50
    expect($this->support->formatCurrency(1.5, 'USD'))
        ->toBe('USD'."\u{A0}".'1.50');

    // VUV 1
    expect($this->support->formatCurrency(1, 'VUV'))
        ->toBe('VUV'."\u{A0}".'1');

    // VUV 2
    expect($this->support->formatCurrency(1.5, 'VUV'))
        ->toBe('VUV'."\u{A0}".'2');
});

it('correctly formats a decimal to a given number of places', function () {
    // 1.00 (formatting)
    expect($this->support->formatDecimal(1, 2))
        ->toBe('1.00');

    // 1.2345 (truncating)
    expect($this->support->formatDecimal(1.2345, 2))
        ->toBe('1.23');

    // 1.235 (rounding)
    expect($this->support->formatDecimal(1.235, 2))
        ->toBe('1.24');
});

it('correctly formats as an integer', function () {
    // 1.00
    expect($this->support->formatInteger(1.00))
        ->toBe('1');

    // 1.5
    expect($this->support->formatInteger(1.5))
        ->toBe('2');

    // 1.4
    expect($this->support->formatInteger(1.4))
        ->toBe('1');
});
