<?php

return [
    'pricing' => [
        'input_usd_per_million' => (float) env('AI_INPUT_USD_PER_MILLION', 0.15),
        'output_usd_per_million' => (float) env('AI_OUTPUT_USD_PER_MILLION', 0.60),
    ],
    'usd_brl' => (float) env('AI_USD_BRL', 5.00),
];

