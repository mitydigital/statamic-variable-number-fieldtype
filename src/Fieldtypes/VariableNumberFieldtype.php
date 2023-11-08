<?php

namespace MityDigital\StatamicVariableNumberFieldtype\Fieldtypes;

use Illuminate\Support\Facades\URL;
use MityDigital\StatamicVariableNumberFieldtype\Facades\VariableNumber;
use Statamic\Facades\Folder;
use Statamic\Fields\Fieldtype;

class VariableNumberFieldtype extends Fieldtype
{
    protected $icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><path d="M53.3 41.995c2.99-4.06 5.39-6.91 7.22-8.55 1.82-1.64 3.6-2.46 5.33-2.46 2.07 0 3.5.71 4.29 2.12.42.73.63 1.63.63 2.67s-.43 2.06-1.3 2.95-1.92 1.34-3.15 1.34c-.76 0-1.62-.29-2.58-.86-.96-.58-1.69-.86-2.18-.86-.97 0-1.9.5-2.78 1.49-.88.99-2.4 3.1-4.55 6.32l.75 3.94c.39 2.02.72 3.68.99 4.98.26 1.3.55 2.5.87 3.6.42 1.52.84 2.62 1.26 3.31s1.04 1.02 1.85 1.02c.74 0 1.63-.54 2.68-1.61.58-.58 1.46-1.64 2.64-3.19l1.65 1.14c-1.39 2.33-3.19 4.55-5.41 6.65-2.22 2.1-4.39 3.15-6.51 3.15-1.78 0-3.25-.74-4.4-2.21-.66-.79-1.23-1.86-1.73-3.23-.26-.71-.62-1.96-1.08-3.76s-.75-2.91-.88-3.33l-.55.94c-2.6 4.45-4.5 7.35-5.7 8.68-1.81 1.99-3.91 2.99-6.29 2.99-1.36 0-2.55-.47-3.56-1.4s-1.51-2.08-1.51-3.44c0-1.13.37-2.17 1.12-3.13s1.79-1.44 3.13-1.44c.81 0 1.81.29 3.01.87 1.19.58 1.97.87 2.34.87.81 0 1.51-.36 2.1-1.08.59-.72 1.75-2.5 3.48-5.36l1.57-2.59c-.26-1.1-.54-2.45-.85-4.05-.3-1.6-.61-3.24-.92-4.92l-.63-3.35c-.45-2.41-1.15-4-2.12-4.76-.55-.45-1.46-.67-2.71-.67-.13 0-.45.02-.94.06-.5.04-1 .09-1.49.14v-2.12c2.36-.29 4.95-.65 7.77-1.1 2.82-.45 4.7-.76 5.64-.94.79 1.05 1.44 2.32 1.95 3.81.51 1.49.9 3.08 1.16 4.76l.43 2.63-.04-.02z"/><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="3.9"><path d="M31.4 87.365h-4.67c-5.29 0-9.58-4.29-9.58-9.58v-18.2c0-5.29-4.29-9.58-9.58-9.58H3.25h4.67c5.29 0 9.58-4.29 9.58-9.58v-18.21c0-5.29 4.29-9.58 9.58-9.58h4.3M68.62 87.365h4.67c5.29 0 9.58-4.29 9.58-9.58v-18.2c0-5.29 4.29-9.58 9.58-9.58h4.3-4.67c-5.29 0-9.58-4.29-9.58-9.58v-18.21c0-5.29-4.29-9.58-9.58-9.58h-4.3" /></g></svg>';

    protected $categories = ['number', 'special'];

    protected $selectableInForms = true;

    public static function title()
    {
        return __('statamic-variable-number-fieldtype::fieldtype.title');
    }

    public function view()
    {
        return match ($this->config('layout', 'radio')) {
            'button' => 'statamic-variable-number-fieldtype::forms.fields.variable_number_button',
            'custom' => $this->config('layout_custom',
                'statamic-variable-number-fieldtype::forms.fields.variable_number_error'),
            'radio' => 'statamic-variable-number-fieldtype::forms.fields.variable_number_radio',
            'select' => 'statamic-variable-number-fieldtype::forms.fields.variable_number_select',
            default => 'statamic-variable-number-fieldtype::forms.fields.variable_number_radio'
        };
    }

    public function extraRenderableFieldData(): array
    {
        //
        // create formatted options
        //
        $extra = [
            'options' => $this->config('options', []),
        ];

        // if url overrides are enabled, should we update the options
        if ($this->config('url_override', false)) {
            $filter = FILTER_VALIDATE_FLOAT;
            if ($this->config('format') === 'integer') {
                $filter = FILTER_VALIDATE_INT;
            }
            $newOptions = [];

            foreach (explode(',', request()->get('vn_'.$this->field()->handle())) as $value) {
                if ($value = filter_var($value, $filter)) {
                    $newOptions[] = [
                        'id' => count($newOptions) + 1,
                        'number' => $value,
                        'default' => false,
                    ];
                }
            }

            if (count($newOptions)) {
                $extra['options'] = $newOptions;
            }
        }

        // process options to have their actual output value based on configuration
        foreach ($extra['options'] as $idx => $option) {
            $option['display'] = $this->formatNumber($option['number']);
            $extra['options'][$idx] = $option;
        }

        //
        // set initial data for alpine to use
        //
        // get the old value
        $old = old($this->field()->handle(), null);

        // if the "get" param is the same as $old, then reset old (i.e. it is an initial load)
        if (request()->get($this->field()->handle()) === $old) {
            $old = null;
        }

        $init = [
            'option' => null,
            'custom' => null,
            'value' => null,
        ];

        // get the default value
        if ($default = collect($extra['options'])
            ->first(fn ($option) => isset($option['default']) && $option['default'])) {
            $init['option'] = $default['number'];
            $init['value'] = $default['number'];
        }

        if ($old !== null) {
            // get the option
            if ($option = collect($extra['options'])->first(fn ($option) => $option['number'] == $old)) {
                $init['option'] = $option['number'];
            } else {
                if ($this->config('allow_custom')) {
                    // we didn't have a matching option, so must be the custom value
                    $init['custom'] = $old;
                }
            }
        }

        $extra['init'] = $init;

        // get the placeholder
        $extra['placeholder'] = $this->config('custom_placeholder',
            null) ?? __('statamic-variable-number-fieldtype::fieldtype.components.custom.placeholder');

        return $extra;
    }

    protected function formatNumber($value): string
    {
        $formatted = match ($this->config('format', 'currency')) {
            'currency' => VariableNumber::formatCurrency($value, $this->config('currency')),
            'decimal' => VariableNumber::formatDecimal($value, $this->config('decimals')),
            'integer' => VariableNumber::formatInteger($value),
            default => $value
        };

        return ''.$formatted;
    }

    public function preProcess($value)
    {
        // if we have a parent, we're part of the CP blueprints (such as a Collection blueprint)
        if ($this->field()->parent()) {
            return [
                'message' => __('statamic-variable-number-fieldtype::fieldtype.errors.cp'),
            ];
        }

        if ($value) {
            return $this->formatNumber($value);
        }

        return $value;
    }

    public function preProcessIndex($value)
    {
        if ($value) {
            return $this->formatNumber($value);
        }

        return $value;
    }

    protected function configFieldItems(): array
    {
        return [
            'options' => [
                'mode' => 'grid',
                'min_rows' => 1,
                'add_row' => __('statamic-variable-number-fieldtype::fieldtype.config.options.add_row'),
                'reorderable' => true,
                'fullscreen' => false,
                'type' => 'grid',
                'display' => __('statamic-variable-number-fieldtype::fieldtype.config.options.display'),
                'instructions' => __('statamic-variable-number-fieldtype::fieldtype.config.options.instructions'),
                'listable' => 'hidden',
                'visibility' => 'visible',
                'replicator_preview' => true,
                'hide_display' => false,

                'fields' => [
                    [
                        'handle' => 'number',
                        'field' => [
                            'width' => 66,
                            'type' => 'float',
                            'display' => __('statamic-variable-number-fieldtype::fieldtype.config.options.fields.numbers'),
                            'validate' => ['required'],
                        ],
                    ],
                    [
                        'handle' => 'default',
                        'field' => [
                            'width' => 33,
                            'type' => 'toggle',
                            'display' => __('statamic-variable-number-fieldtype::fieldtype.config.options.fields.default'),
                        ],
                    ],
                ],
            ],

            'allow_custom' => [
                'inline_label' => __('statamic-variable-number-fieldtype::fieldtype.config.allow_custom.inline_label'),
                'inline_label_when_true' => __('statamic-variable-number-fieldtype::fieldtype.config.allow_custom.inline_label_when_true'),
                'default' => false,
                'type' => 'toggle',
                'display' => __('statamic-variable-number-fieldtype::fieldtype.config.allow_custom.display'),
                'instructions' => __('statamic-variable-number-fieldtype::fieldtype.config.allow_custom.instructions'),
            ],

            'custom_display' => [
                'default' => 'toggle',
                'type' => 'select',
                'display' => __('statamic-variable-number-fieldtype::fieldtype.config.custom_display.display'),
                'instructions' => __('statamic-variable-number-fieldtype::fieldtype.config.custom_display.instructions'),
                'options' => [
                    'toggle' => __('statamic-variable-number-fieldtype::fieldtype.config.custom_display.options.toggle'),
                    'visible' => __('statamic-variable-number-fieldtype::fieldtype.config.custom_display.options.visible'),
                ],

                'if' => [
                    'allow_custom' => 'equals true',
                    //'layout' => '=== radio',
                ],
            ],

            'custom_min' => [
                'type' => 'float',
                'display' => __('statamic-variable-number-fieldtype::fieldtype.config.custom_min.display'),
                'instructions' => __('statamic-variable-number-fieldtype::fieldtype.config.custom_min.instructions'),

                'validate' => [
                    'nullable',
                    'required_if:allow_custom,true',
                ],

                'if' => [
                    'allow_custom' => 'equals true',
                ],
            ],

            'custom_max' => [
                'type' => 'float',
                'display' => __('statamic-variable-number-fieldtype::fieldtype.config.custom_max.display'),
                'instructions' => __('statamic-variable-number-fieldtype::fieldtype.config.custom_max.instructions'),

                'validate' => [
                    'nullable',
                    'required_if:allow_custom,true',
                ],

                'if' => [
                    'allow_custom' => 'equals true',
                ],
            ],

            'custom_placeholder' => [
                'type' => 'text',
                'display' => __('statamic-variable-number-fieldtype::fieldtype.config.custom_placeholder.display'),
                'instructions' => __('statamic-variable-number-fieldtype::fieldtype.config.custom_placeholder.instructions',
                    [
                        'default' => __('statamic-variable-number-fieldtype::fieldtype.components.custom.placeholder'),
                    ]),

                'validate' => [
                    'nullable',
                ],

                'if' => [
                    'allow_custom' => 'equals true',
                ],
            ],

            'format' => [
                'default' => 'currency',
                'type' => 'select',
                'display' => __('statamic-variable-number-fieldtype::fieldtype.config.format.display'),
                'instructions' => __('statamic-variable-number-fieldtype::fieldtype.config.format.instructions'),
                'options' => [
                    'currency' => __('statamic-variable-number-fieldtype::fieldtype.config.format.options.currency'),
                    'decimal' => __('statamic-variable-number-fieldtype::fieldtype.config.format.options.decimal'),
                    'integer' => __('statamic-variable-number-fieldtype::fieldtype.config.format.options.integer'),
                ],
            ],

            'currency' => [
                'type' => 'text',
                'character_limit' => 3,
                'display' => __('statamic-variable-number-fieldtype::fieldtype.config.currency.display'),
                'instructions' => __('statamic-variable-number-fieldtype::fieldtype.config.currency.instructions', [
                    'link' => 'https://www.iban.com/currency-codes',
                ]),

                'validate' => [
                    'nullable',
                    'required_if:format,currency',
                    'max:3',
                    'min:3',
                ],

                'if' => [
                    'format' => 'equals currency',
                ],
            ],

            'decimals' => [
                'type' => 'integer',
                'default' => 2,
                'character_limit' => 3,
                'display' => __('statamic-variable-number-fieldtype::fieldtype.config.decimals.display'),
                'instructions' => __('statamic-variable-number-fieldtype::fieldtype.config.decimals.instructions'),

                'validate' => [
                    'nullable',
                    'required_if:format,decimal',
                    'min:1',
                ],

                'if' => [
                    'format' => 'equals decimal',
                ],
            ],

            'layout' => [
                'default' => 'radio',
                'type' => 'select',
                'display' => __('statamic-variable-number-fieldtype::fieldtype.config.layout.display'),
                'instructions' => __('statamic-variable-number-fieldtype::fieldtype.config.layout.instructions'),
                'options' => [
                    'button' => __('statamic-variable-number-fieldtype::fieldtype.config.layout.options.button'),
                    'radio' => __('statamic-variable-number-fieldtype::fieldtype.config.layout.options.radio'),
                    'select' => __('statamic-variable-number-fieldtype::fieldtype.config.layout.options.select'),
                    'custom' => __('statamic-variable-number-fieldtype::fieldtype.config.layout.options.custom'),
                ],
            ],

            'layout_custom' => [
                'type' => 'select',
                'display' => __('statamic-variable-number-fieldtype::fieldtype.config.layout_custom.display'),
                'instructions' => __('statamic-variable-number-fieldtype::fieldtype.config.layout_custom.instructions'),

                'options' => collect(Folder::disk('resources')
                    ->getFilesRecursively('views'))
                    ->map(fn ($view) => str_replace_first('views/', '', str_before($view, '.')))
                    ->filter(fn ($view) => (
                        $view &&
                        ! str_starts_with($view, 'vendor/') &&
                        ! str_starts_with($view, 'partials/') &&
                        ! str_starts_with($view, 'errors/')
                    ))
                    ->values(),

                'validate' => [
                    'required_if:layout,custom',
                ],

                'if' => [
                    'layout' => 'equals custom',
                ],
            ],

            'url_override' => [
                'inline_label' => __('statamic-variable-number-fieldtype::fieldtype.config.url_override.inline_label'),
                'inline_label_when_true' => __('statamic-variable-number-fieldtype::fieldtype.config.url_override.inline_label_when_true'),
                'default' => false,
                'type' => 'toggle',
                'display' => __('statamic-variable-number-fieldtype::fieldtype.config.url_override.display'),
                'instructions' => __('statamic-variable-number-fieldtype::fieldtype.config.url_override.instructions', [
                    'example' => URL::to('my-form?vn_handle=10,20,30'),
                ]),
            ],
        ];
    }
}
