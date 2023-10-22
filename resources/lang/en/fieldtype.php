<?php

return [
    'title' => 'Variable Number',

    'components' => [
        'custom' => [
            'label' => 'Enter your custom value',
            'placeholder' => 'Custom...',
        ],

        'options' => [
            'placeholder' => 'Select...',
            'other' => 'Custom',
        ],
    ],

    'config' => [
        'allow_custom' => [
            'display' => 'Allow custom number?',

            'inline_label' => 'User must select one of the defined numbers',
            'inline_label_when_true' => 'User can enter their own number',

            'instructions' => 'When enabled, users will be able to select from the pre-defined numbers, or enter their own number.',
        ],

        'currency' => [
            'display' => 'Currency',
            'instructions' => 'Numbers will be presented in this currency. Must be a <a href=":link" target="_blank">valid currency code</a> supported by your platform.',
        ],

        'custom_display' => [
            'display' => 'Custom number display',
            'instructions' => 'Show the custom field only when "custom" is selected, or have the custom field be always visible, and when filled, sets mode to "other".',

            'options' => [
                'toggle' => 'Show only if "custom" is selected',
                'visible' => 'Direct input for custom values',
            ],
        ],

        'custom_min' => [
            'display' => 'Minimum',
            'instructions' => 'The minimum custom value allowed.',
        ],

        'custom_max' => [
            'display' => 'Maximum',
            'instructions' => 'The maximum custom value allowed.',
        ],

        'decimals' => [
            'display' => 'Decimals',
            'instructions' => 'The number of decimals to round numbers to.',
        ],

        'format' => [
            'display' => 'Format',
            'instructions' => 'How should numbers, including custom numbers, be formatted.',

            'options' => [
                'currency' => 'Currency',
                'decimal' => 'Decimal',
                'integer' => 'Integer',
            ],
        ],

        'layout' => [
            'display' => 'Layout',
            'instructions' => 'Defines the layout for the fieldtype.',

            'options' => [
                'button' => 'Button',
                'radio' => 'Radio buttons',
                'select' => 'Select',
                'custom' => 'Custom...',
            ],
        ],

        'layout_custom' => [
            'display' => 'Custom Layout',
            'instructions' => 'Defines the layout for the fieldtype.',

            'options' => [
                'button' => 'Button',
                'radio' => 'Radio',
                'select' => 'Select',
                'custom' => 'Custom...',
            ],
        ],

        'options' => [
            'add_row' => 'Add option...',
            'display' => 'Options',
            'instructions' => 'Each value must be a valid number.<br><br>If your Variable Number fieldtype is configured to be a currency, your values will be rounded to two decimal places.<br><br>The first "default" found, if any, will be used as the default option.',

            'fields' => [
                'default' => 'Default?',
                'numbers' => 'Numbers',
            ],
        ],

        'url_override' => [
            'display' => 'Allow URL overrides?',

            'inline_label' => 'Restrict to the defined numbers',
            'inline_label_when_true' => 'Numbers can be defined using URL parameters',

            'instructions' => 'When enabled, providing a URL parameter of your field handle with a comma-delimited string will override the selectable Options with the URL-provided options. For example, to set as 10, 20 and 30, you could use:<br>:example',
        ],
    ],

    'errors' => [
        'cp' => 'The Variable Number fieldtype is designed for use in your Forms blueprints only.',

        'layout_custom' => 'Your field is missing a custom layout configuration.',
    ],
];
