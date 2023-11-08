<?php

use MityDigital\StatamicVariableNumberFieldtype\Tags\VariableNumber;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Form;

it('outputs a variable number partial with fields from the tag\'s context', function () {
    // create the tag
    $tag = new VariableNumber();

    // load fields from the form blueprint
    $blueprint = Blueprint::find('forms/has_variable_number');

    // set the context
    $tag->setContext([
        'fields' => $blueprint->fields()->toPublishArray(),
    ]);

    // run the tax, and expect an output
    expect($tag->index())->not()->toBeNull();
});

it('outputs a variable number partial with form from the tag\'s params', function () {
    // create the tag
    $tag = new VariableNumber();

    // load form
    $form = Form::find('has_variable_number');

    $tag->setContext([]);

    // set the params
    $tag->setParameters([
        'form' => $form,
    ]);

    // run the tax, and expect an output
    expect($tag->index())->not()->toBeNull();
});

it('outputs nothing when the blueprint does not contain a variable number fieldtype', function () {
    // create the tag
    $tag = new VariableNumber();

    // load fields from the form blueprint
    $blueprint = Blueprint::find('forms/no_variable_number');

    // set the context
    $tag->setContext([
        'fields' => $blueprint->fields()->toPublishArray(),
    ]);

    // run the tax, and expect an output
    expect($tag->index())->toBeNull();
});
