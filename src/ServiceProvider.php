<?php

namespace MityDigital\StatamicVariableNumberFieldtype;

use MityDigital\StatamicVariableNumberFieldtype\Fieldtypes\VariableNumberFieldtype;
use MityDigital\StatamicVariableNumberFieldtype\Tags\VariableNumber;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $fieldtypes = [
        VariableNumberFieldtype::class,
    ];

    protected $tags = [
        VariableNumber::class,
    ];

    protected $vite = [
        'input' => [
            'resources/js/cp.js',
        ], 'publicDirectory' => 'resources/dist',
    ];

    public function bootAddon()
    {
        $this->publishes([
            __DIR__.'/../resources/views/forms/fields' => resource_path('views/vendor/statamic-variable-number-fieldtype/forms/fields'),
        ], 'statamic-variable-number-fieldtype-views');

        $this->publishes([
            __DIR__.'/../resources/views/snippets/variable_number.antlers.html' => resource_path('views/vendor/statamic-variable-number-fieldtype/snippets/variable_number.antlers.html'),
        ], 'statamic-variable-number-fieldtype-logic');
    }
}
