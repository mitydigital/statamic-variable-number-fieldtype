<?php

namespace MityDigital\StatamicVariableNumberFieldtype\Tags;

use Statamic\Forms\FieldsVariable;
use Statamic\Tags\Partial;
use Statamic\Tags\Tags;

class VariableNumber extends Tags
{
    /**
     * The {{ variable_number }} tag.
     *
     * @return string|array
     */
    public function index()
    {
        // do we have fields? if so, check if we have a variable number
        $fields = $this->context->get('fields', null);

        if (is_array($fields)) {
            // convert to an array
            $fields = collect($fields);
        } elseif (! $fields) {
            // do we have a form parameter?
            $form = $this->params->get('form', null);

            // set to the fields
            $fields = $form->fields();
        }

        if (get_class($fields) === FieldsVariable::class) {
            $fields = collect($fields->extra());
        }

        // in the fields of the form, do we have a variable number field?
        if ($fields->filter(fn ($field) => (is_array($field) && $field['type'] === 'variable_number') ||
            (is_object($field) && method_exists($field, 'type') && $field->type() === 'variable_number'))
            ->count()) {

            $partial = app()->make(Partial::class);

            $partial->setContext($this->context);
            $partial->setParameters([
                'src' => 'statamic-variable-number-fieldtype::snippets/variable_number',
            ]);

            return $partial->wildcard('src');
        }
    }
}
