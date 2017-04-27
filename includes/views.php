<?php

/*LIST OF MOBILE LAYOUTS*/
$__mobile_template_path = MOBILE_TEMPLATE_URI;
return array(
    'layouts' => array(
        'path' => $__mobile_template_path . 'layouts/',
        'views' => array(
            'editr',
            'default',
            'merge',
            'merge2',
            'noheader',
            'noheader2',
            'noheader_hidden',
            'noheader_findr',
            'open',
            'printr'
        )
    ),
    'components' => array(
        'path' => $__mobile_template_path . 'components/',
        'views' => array(
            'back',
            'back_active',
            'next',
            'next_active',
            'prompt',
            'prompt_big',
            'prompt_date',
            'prompt_date2',
            'prompt_hidden',
            'prompt_selector',
            'prompt_selector_multi',
            'table',
            'table_prefix',
            'table_prefix_noheader',
            'table_prefix_findr',
            'table_prefix_old'
        )
    )
);