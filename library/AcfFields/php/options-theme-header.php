<?php 

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
    'key' => 'group_56a22a9c78e54',
    'title' => 'Header',
    'fields' => array(
        0 => array(
            'layout' => 'vertical',
            'choices' => array(
                'business' => __('Business (default)', 'municipio'),
                'casual' => __('Casual', 'municipio'),
                'jumbo' => __('Jumbo', 'municipio'),
                'contrasted-nav' => __('Contrasted navbar', 'municipio'),
            ),
            'default_value' => 'business',
            'other_choice' => 0,
            'save_other_choice' => 0,
            'allow_null' => 0,
            'return_format' => 'value',
            'key' => 'field_56a22aaa83835',
            'label' => __('Layout', 'municipio'),
            'name' => 'header_layout',
            'type' => 'radio',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
        ),
        1 => array(
            'default_value' => 0,
            'message' => __('Header should stick to view (floats when scrolling)', 'municipio'),
            'ui' => 0,
            'ui_on_text' => '',
            'ui_off_text' => '',
            'key' => 'field_58737dd1dc762',
            'label' => __('Sticky', 'municipio'),
            'name' => 'header_sticky',
            'type' => 'true_false',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '33.3333%',
                'class' => '',
                'id' => '',
            ),
        ),
        2 => array(
            'default_value' => 0,
            'message' => __('Transparent header on front page', 'municipio'),
            'ui' => 0,
            'ui_on_text' => '',
            'ui_off_text' => '',
            'key' => 'field_58737dd1dc763',
            'label' => __('Transparent', 'municipio'),
            'name' => 'header_transparent',
            'type' => 'true_false',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '33.3333%',
                'class' => '',
                'id' => '',
            ),
        ),
        3 => array(
            'default_value' => 0,
            'message' => __('Center all contents of the header', 'municipio'),
            'ui' => 0,
            'ui_on_text' => '',
            'ui_off_text' => '',
            'key' => 'field_58737dd1dc76345',
            'label' => __('Centered', 'municipio'),
            'name' => 'header_centered',
            'type' => 'true_false',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '33.3333%',
                'class' => '',
                'id' => '',
            ),
        ),
        4 => array(
            'layout' => 'vertical',
            'choices' => array(
                'light' => __('Light', 'municipio'),
                'dark' => __('Dark', 'municipio'),
            ),
            'default_value' => 'light',
            'other_choice' => 0,
            'save_other_choice' => 0,
            'allow_null' => 0,
            'return_format' => 'value',
            'key' => 'field_56a22aaa83835wef',
            'label' => __('Header content color', 'municipio'),
            'name' => 'header_content_color',
            'type' => 'radio',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => array(
                0 => array(
                    0 => array(
                        'field' => 'field_58737dd1dc763',
                        'operator' => '==',
                        'value' => 1,
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
        ),
    ),
    'location' => array(
        0 => array(
            0 => array(
                'param' => 'options_page',
                'operator' => '==',
                'value' => 'acf-options-header',
            ),
        ),
    ),
    'menu_order' => 0,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'hide_on_screen' => '',
    'active' => 1,
    'description' => '',
    'local' => 'php',
));
}