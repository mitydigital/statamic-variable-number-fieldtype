<?php

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use MityDigital\StatamicVariableNumberFieldtype\Facades\VariableNumber as VariableNumberFacade;
use MityDigital\StatamicVariableNumberFieldtype\Fieldtypes\VariableNumberFieldtype;
use MityDigital\StatamicVariableNumberFieldtype\Support\VariableNumber;
use Statamic\Entries\Entry;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Site;

beforeEach(function () {
    $this->fieldtype = app(VariableNumberFieldtype::class);
});

//
// view
//
it('returns the correct view based on the config', function () {
    // load the blueprint
    $blueprint = Blueprint::find('forms/has_variable_number');

    // get the variable number
    $field = $blueprint->field('variable_number');

    //
    // RADIO
    //
    $config = $field->fieldtype()->config();
    expect($config['layout'])
        ->toBe('radio')
        ->and($field->fieldtype()->view())
        ->toBe('statamic-variable-number-fieldtype::forms.fields.variable_number_radio');

    //
    // SELECT
    //
    // re-configure
    $config = $field->config();
    $config['layout'] = 'select';
    $field->setConfig($config);

    $config = $field->fieldtype()->config();
    expect($config['layout'])
        ->toBe('select')
        ->and($field->fieldtype()->view())
        ->toBe('statamic-variable-number-fieldtype::forms.fields.variable_number_select');

    //
    // BUTTON
    //
    // re-configure
    $config = $field->config();
    $config['layout'] = 'button';
    $field->setConfig($config);

    $config = $field->fieldtype()->config();
    expect($config['layout'])
        ->toBe('button')
        ->and($field->fieldtype()->view())
        ->toBe('statamic-variable-number-fieldtype::forms.fields.variable_number_button');

    //
    // CUSTOM
    //
    // re-configure
    $config = $field->config();
    $config['layout'] = 'custom';
    $config['layout_custom'] = 'my_layout_file';
    $field->setConfig($config);

    $config = $field->fieldtype()->config();
    expect($config['layout'])
        ->toBe('custom')
        ->and($field->fieldtype()->view())
        ->toBe('my_layout_file');
});

//
// extraRenderableFieldData
//
it('correctly defines the configured options when no overrides are set', function () {
    // load the blueprint
    $blueprint = Blueprint::find('forms/has_variable_number');

    // get the variable number
    $field = $blueprint->field('variable_number');

    $config = $field->fieldtype()->config();
    $extra = $field->fieldtype()->extraRenderableFieldData();

    // set options
    $options = $config['options'];
    foreach ($options as $index => $option) {
        $option['display'] = VariableNumberFacade::formatCurrency($option['number'], 'AUD');
        $options[$index] = $option;
    }

    expect($extra)
        ->toHaveKey('options')
        ->and($extra['options'])
        ->toBeArray()
        ->toBe($options)
        ->and(request()->get('vn_'.$field->handle()))->toBeNull()
        ->and($config['url_override'])
        ->toBeFalse();
});

it('correctly defines the override options when overrides are set', function () {
    // load the blueprint
    $blueprint = Blueprint::find('forms/has_variable_number');

    // get the variable number
    $field = $blueprint->field('variable_number');

    // allow url overrides
    $config = $field->config();
    $config['url_override'] = true;
    $field->setConfig($config);

    // set request options
    $request = new Request([], [], ['vn_'.$field->handle() => '1,2,3']);
    $request->setRouteResolver(function () use ($request) {
        return (new Route('GET', '', []))->bind($request);
    });
    app()->instance('request', $request);

    // set current site
    Site::setCurrent('default');

    // get the bits
    $config = $field->fieldtype()->config();
    $extra = $field->fieldtype()->extraRenderableFieldData();

    $options = [
        [
            'id' => 1,
            'number' => 1.0,
            'default' => false,
            'display' => '$1.00',
        ],
        [
            'id' => 2,
            'number' => 2.0,
            'default' => false,
            'display' => '$2.00',
        ],
        [
            'id' => 3,
            'number' => 3.0,
            'default' => false,
            'display' => '$3.00',
        ],
    ];

    expect($extra)
        ->toHaveKey('options')
        ->and($extra['options'])
        ->toBeArray()
        ->toBe($options)
        ->and(request()->get('vn_'.$field->handle()))->toBe('1,2,3')
        ->and($config['url_override'])
        ->toBeTrue();
});

it('correctly ignores the override options when url override is disabled', function () {
    // load the blueprint
    $blueprint = Blueprint::find('forms/has_variable_number');

    // get the variable number
    $field = $blueprint->field('variable_number');

    $config = $field->fieldtype()->config();
    $extra = $field->fieldtype()->extraRenderableFieldData();

    // set options
    $options = $config['options'];
    foreach ($options as $index => $option) {
        $option['display'] = VariableNumberFacade::formatCurrency($option['number'], 'AUD');
        $options[$index] = $option;
    }

    // set request options
    $requestMock = Mockery::mock(Request::class)
        ->makePartial()
        ->shouldReceive('get')
        ->once()
        ->andReturn('1,2,3');
    app()->instance('request', $requestMock->getMock());

    expect($extra)
        ->toHaveKey('options')
        ->and($extra['options'])
        ->toBeArray()
        ->toBe($options)
        ->and(request()->get('vn_'.$field->handle()))->toBe('1,2,3')
        ->and($config['url_override'])
        ->toBeFalse();
});

it('correctly sets init data on re-post', function () {
    // load the blueprint
    $blueprint = Blueprint::find('forms/has_variable_number');

    // get the variable number
    $field = $blueprint->field('variable_number');

    //
    // INITIAL
    //
    $config = $field->fieldtype()->config();
    $extra = $field->fieldtype()->extraRenderableFieldData();
    expect(collect($config['options'])->filter(fn ($option) => $option['default'])->first()['number'])
        ->toBe(10);
    expect($extra['init']['option'])->toBe(10); // the default

    //
    // USE DEFAULT OPTIONS
    //
    request()->setLaravelSession(session());
    $this->withSession(['_old_input.'.$field->handle() => 5]);

    $extra = $field->fieldtype()->extraRenderableFieldData();

    expect($extra['init']['option'])->toBe(5); // the "old" data

    //
    // USE OVERRIDES
    //

    // reconfigure
    $config = $field->config();
    $config['url_override'] = true;
    $field->setConfig($config);

    // set request options
    $request = new Request([], [], ['vn_'.$field->handle() => '1,2,3']);
    $request->setRouteResolver(function () use ($request) {
        return (new Route('GET', '', []))->bind($request);
    });
    app()->instance('request', $request);

    request()->setLaravelSession(session());
    $this->withSession(['_old_input.'.$field->handle() => 3]);

    $extra = $field->fieldtype()->extraRenderableFieldData();

    expect($extra['init']['option'])->toBe(3.0);

    // attempt a custom value, but not have it configured to allow it
    request()->setLaravelSession(session());
    $this->withSession(['_old_input.'.$field->handle() => 5]);

    $extra = $field->fieldtype()->extraRenderableFieldData();

    expect($extra['init']['option'])->toBeNull()
        ->and($extra['init']['custom'])->toBeNull();

    // allow custom value
    $config = $field->config();
    $config['allow_custom'] = true;
    $field->setConfig($config);

    request()->setLaravelSession(session());
    $this->withSession(['_old_input.'.$field->handle() => 5]);

    $extra = $field->fieldtype()->extraRenderableFieldData();

    expect($extra['init']['option'])->toBeNull()
        ->and($extra['init']['custom'])->toBe(5);

});

it('correctly sets the placeholder', function () {
    // load the blueprint
    $blueprint = Blueprint::find('forms/has_variable_number');

    // get the variable number
    $field = $blueprint->field('variable_number');

    // get the extra
    $extra = $field->fieldtype()->extraRenderableFieldData();

    expect($extra)
        ->toHaveKey('placeholder')
        ->and($extra['placeholder'])
        ->toBe(__('statamic-variable-number-fieldtype::fieldtype.components.custom.placeholder'));

    // re-configure
    $config = $field->config();
    $config['custom_placeholder'] = 'My custom placeholder';
    $field->setConfig($config);

    // get the extra
    $extra = $field->fieldtype()->extraRenderableFieldData();

    expect($extra)
        ->toHaveKey('placeholder')
        ->and($extra['placeholder'])
        ->toBe($config['custom_placeholder']);
});

//
// formatNumber
//
it('formats numbers', function () {
    // load the blueprint
    $blueprint = Blueprint::find('forms/has_variable_number');

    // get the variable number
    $field = $blueprint->field('variable_number');

    $this->partialMock(VariableNumber::class)
        ->shouldReceive('formatCurrency')
        ->once()
        ->andReturn('$1.50')
        ->shouldReceive('formatDecimal')
        ->once()
        ->andReturn('1.50')
        ->shouldReceive('formatInteger')
        ->once()
        ->andReturn('1');

    // CURRENCY call the pre-process
    expect($field->fieldtype()->preProcess(1.5))
        ->toBe('$1.50');

    // re-configure
    $config = $field->config();
    $config['format'] = 'decimal';
    $config['decimals'] = '2';
    $field->setConfig($config);

    // DECIMAL call the pre-process
    expect($field->fieldtype()->preProcess(1.5))
        ->toBe('1.50');

    // re-configure
    $config = $field->config();
    $config['format'] = 'integer';
    $field->setConfig($config);

    // INTEGER call the pre-process
    expect($field->fieldtype()->preProcess(1.5))
        ->toBe('1');

    // still returns the value if a weird format is received
    $config = $field->config();
    $config['format'] = 'fake';
    $field->setConfig($config);

    // INTEGER call the pre-process
    expect($field->fieldtype()->preProcess(1.5))
        ->toBe('1.5');
});

//
// preProcess
//
it('correctly returns an error message if part of a non-form blueprint', function () {
    // load the blueprint
    $blueprint = Blueprint::find('collections/pages/article');

    // set the parent
    $blueprint->setParent(new Entry());

    // get the variable number
    $field = $blueprint->field('variable_number');

    // expect an error array
    expect($field->fieldtype()->preProcess(1))
        ->toBeArray()
        ->toHaveKey('message');
});

//
// preProcess
//
it('formats numbers before show', function () {
    // load the blueprint
    $blueprint = Blueprint::find('forms/has_variable_number');

    // get the variable number
    $field = $blueprint->field('variable_number');

    $this->partialMock(VariableNumber::class)
        ->shouldReceive('formatCurrency')
        ->once();

    // call the pre-process
    $field->fieldtype()->preProcess(1.5);
});

//
// preProcessIndex
//
it('formats numbers before index', function () {
    // load the blueprint
    $blueprint = Blueprint::find('forms/has_variable_number');

    // get the variable number
    $field = $blueprint->field('variable_number');

    $this->partialMock(VariableNumber::class)
        ->shouldReceive('formatCurrency')
        ->once();

    // call the pre-process
    $field->fieldtype()->preProcessIndex(1.5);
});

//
// configFieldItems
//
it('has the expected configuration options', function () {
    $config = callProtectedMethod($this->fieldtype, 'configFieldItems');

    expect($config)
        ->toHaveKeys([
            'options',
            'allow_custom',
            'custom_display',
            'custom_min',
            'custom_max',
            'custom_placeholder',
            'format',
            'currency',
            'decimals',
            'layout',
            'layout_custom',
            'url_override',
        ]);

    //
    // options
    //
    // must have 1 row
    expect($config['options'])
        ->toHaveKey('min_rows')
        ->and($config['options']['min_rows'])
        ->toBe(1);

    // requires a number and default in the grid
    expect($config['options'])
        ->toHaveKey('fields')
        ->and($config['options']['fields'])
        ->toBeArray()
        ->and(collect($config['options']['fields'])->pluck('handle')->toArray())
        ->toBe(['number', 'default']);

    //
    // custom display
    //
    // 'if' => 'allow_custom' => 'equals true',
    expect($config['custom_display']['if'])
        ->toBeArray()
        ->toHaveKey('allow_custom')
        ->and($config['custom_display']['if']['allow_custom'])
        ->toBe('equals true');

    //
    // custom min
    //
    // 'required_if:allow_custom,true',
    expect($config['custom_min']['validate'])
        ->toBeArray()
        ->toContain('required_if:allow_custom,true');

    //  'if' => 'allow_custom' => 'equals true',
    expect($config['custom_min']['if'])
        ->toBeArray()
        ->toHaveKey('allow_custom')
        ->and($config['custom_min']['if']['allow_custom'])
        ->toBe('equals true');

    //
    // custom max
    //
    // 'required_if:allow_custom,true',
    expect($config['custom_max']['validate'])
        ->toBeArray()
        ->toContain('required_if:allow_custom,true');

    //  'if' => 'allow_custom' => 'equals true',
    expect($config['custom_max']['if'])
        ->toBeArray()
        ->toHaveKey('allow_custom')
        ->and($config['custom_max']['if']['allow_custom'])
        ->toBe('equals true');

    //
    // custom placeholder
    //
    // 'if' => 'allow_custom' => 'equals true',
    expect($config['custom_placeholder']['if'])
        ->toBeArray()
        ->toHaveKey('allow_custom')
        ->and($config['custom_placeholder']['if']['allow_custom'])
        ->toBe('equals true');

    //
    // currency
    //
    // 'required_if:format,currency',
    expect($config['currency']['validate'])
        ->toBeArray()
        ->toContain('required_if:format,currency');

    //  'if' => 'format' => 'equals currency',
    expect($config['currency']['if'])
        ->toBeArray()
        ->toHaveKey('format')
        ->and($config['currency']['if']['format'])
        ->toBe('equals currency');

    //
    // decimals
    //
    // 'required_if:format,decimal',
    expect($config['decimals']['validate'])
        ->toBeArray()
        ->toContain('required_if:format,decimal');

    //  'if' => 'format' => 'equals decimal',
    expect($config['decimals']['if'])
        ->toBeArray()
        ->toHaveKey('format')
        ->and($config['decimals']['if']['format'])
        ->toBe('equals decimal');

    //
    // layout custom
    //
    // 'required_if:layout,custom',
    expect($config['layout_custom']['validate'])
        ->toBeArray()
        ->toContain('required_if:layout,custom');

    //  'if' => 'format' => 'equals decimal',
    expect($config['layout_custom']['if'])
        ->toBeArray()
        ->toHaveKey('layout')
        ->and($config['layout_custom']['if']['layout'])
        ->toBe('equals custom');
});
