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
            'resources/js/site.js',
        ], 'publicDirectory' => 'resources/dist',
    ];

    public function bootAddon()
    {
        $this->publishes([
            __DIR__.'/../resources/views/forms/fields' => resource_path('views/vendor/statamic-variable-number-fieldtype/forms/fields'),
        ], 'statamic-variable-number-fieldtype-views');
    }
}
