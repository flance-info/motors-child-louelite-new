<?php


//rental options

function stm_rent_car_options_de( $wp_customize ) {

    //lauto section
    $wp_customize->add_section(
        'stm_add_rental_section_de',
        array(
            'title' => esc_html__( 'Lauto Options', 'motors-child' ),
            'priority' => 150
        )
    );

    //add phone
    $wp_customize->add_setting(
        'stm_add_lauto_phone',
        array(
            'sanitize_callback' => 'wp_filter_nohtml_kses'
        )
    );

    $wp_customize->add_control(
        'stm_add_lauto_phone',
        array(
            'label' => esc_html__( 'Phone Products Grid', 'motors-child' ),
            'section' => 'stm_add_rental_section_de',
            'type' => 'text'
        )
    );

    //add email
    $wp_customize->add_setting(
        'stm_add_lauto_email',
        array(
            'sanitize_callback' => 'wp_filter_nohtml_kses' //removes all HTML from content
        )
    );

    $wp_customize->add_control(
        'stm_add_lauto_email',
        array(
            'label' => esc_html__( 'Email Products Grid', 'motors-child' ),
            'section' => 'stm_add_rental_section_de',
            'type' => 'text'
        )
    );


    //add title
    $wp_customize->add_setting(
        'stm_add_lauto_title',
        array(
            'sanitize_callback' => 'wp_filter_nohtml_kses' //removes all HTML from content
        )
    );

    $wp_customize->add_control(
        'stm_add_lauto_title',
        array(
            'label' => esc_html__( 'Title Products Grid', 'motors-child' ),
            'section' => 'stm_add_rental_section_de',
            'type' => 'text'
        )
    );



}
add_action( 'customize_register', 'stm_rent_car_options_de' );


if (function_exists('vc_map')) {
    add_action('init', 'vc_stm_elements_de', 101);

    function vc_stm_elements_de(){

        vc_map(array(
            'name' => __('STM Rent Car Form', 'motors'),
            'base' => 'stm_rent_car_form',
            'category' => __('STM', 'motors'),
            'html_template' => get_stylesheet_directory().'/vc_templates/stm_rent_car_form.php',
            'params' => array(
                array(
                    'type' => 'textfield',
                    'heading' => __('Set working hours comma separate ( 08:00, 09:00 .... )', 'motors'),
                    'param_name' => 'office_working_hours',
                ),
                array(
                    'type' => 'textfield',
                    'heading' => __('Set weekend working hours comma separate ( 08:00, 09:00 .... )', 'motors-child'),
                    'param_name' => 'office_weekends_working_hours',
                    'group' => __('Weekends', 'motors-child'),
                ),
                array(
                    'type' => 'checkbox',
                    'heading' => __('Disable weekends?', 'motors'),
                    'param_name' => 'disable_weekends',
                    'group' => __('Weekends', 'motors-child')
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => __('Saturday', 'motors-child'),
                    'param_name' => 'disable_saturday',
                    'value' => array(
                        __('Enable this Day', 'motors-child') => 'enable',
                        __('Disable All', 'motors-child') => 'all',
                        __('Disable Pick Up', 'motors-child') => 'pickup',
                        __('Disable Return', 'motors-child') => 'return',
                    ),
                    'std' => 'all',
                    'group' => __('Weekends', 'motors-child'),
                    'dependency' => array(
                        'element' => 'disable_weekends',
						'not_empty'   => true,
                    )
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => __('Sunday', 'motors-child'),
                    'param_name' => 'disable_sunday',
                    'value' => array(
                        __('Enable this Day', 'motors-child') => 'enable',
                        __('Disable All', 'motors-child') => 'all',
                        __('Disable Pick Up', 'motors-child') => 'pickup',
                        __('Disable Return', 'motors-child') => 'return',
                    ),
                    'std' => 'all',
                    'group' => __('Weekends', 'motors-child'),
                    'dependency' => array(
                        'element' => 'disable_weekends',
						'not_empty'   => true,
                    )
                ),
                // array(
                //     'type' => 'checkbox',
                //     'heading' => __('Disable Saturday for Pick Up', 'motors-child'),
                //     'param_name' => 'disable_saturday_pickup',
                //     'value' => true,
                //     'std' => true,
                //     'group' => __('Weekends', 'motors-child'),
                //     'dependency' => array(
                //         'element' => 'disable_weekends',
				// 		'not_empty'   => true,
                //     )
                // ),
                // array(
                //     'type' => 'checkbox',
                //     'heading' => __('Disable Saturday for Return', 'motors-child'),
                //     'param_name' => 'disable_saturday_return',
                //     'value' => true,
                //     'std' => true,
                //     'group' => __('Weekends', 'motors-child'),
                //     'dependency' => array(
                //         'element' => 'disable_weekends',
				// 		'not_empty'   => true,
                //     )
                // ),
                // array(
                //     'type' => 'checkbox',
                //     'heading' => __('Disable Sunday for Pick Up', 'motors-child'),
                //     'param_name' => 'disable_sunday_pickup',
                //     'value' => true,
                //     'std' => true,
                //     'group' => __('Weekends', 'motors-child'),
                //     'dependency' => array(
                //         'element' => 'disable_weekends',
				// 		'not_empty'   => true,
                //     )
                // ),
                // array(
                //     'type' => 'checkbox',
                //     'heading' => __('Disable Sunday for Return', 'motors-child'),
                //     'param_name' => 'disable_sunday_return',
                //     'value' => true,
                //     'std' => true,
                //     'group' => __('Weekends', 'motors-child'),
                //     'dependency' => array(
                //         'element' => 'disable_weekends',
				// 		'not_empty'   => true,
                //     )
                // ),
                array(
                    'type' => 'checkbox',
                    'heading' => __('Display pop-up for office selection on the right (on left by default)', 'motors'),
                    'param_name' => 'office_right',
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => __('Style', 'motors'),
                    'param_name' => 'style',
                    'value' => array(
                        __('Style 1', 'motors') => 'style_1',
                        __('Style 2', 'motors') => 'style_2',
                    ),
                    'std' => 'style_1'
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => __('Align', 'motors'),
                    'param_name' => 'align',
                    'value' => array(
                        __('Left', 'motors') => 'text-left',
                        __('Center', 'motors') => 'text-center',
                        __('Right', 'motors') => 'text-right',
                    ),
                    'std' => 'text-right',
                ),
                array(
                    'type' => 'css_editor',
                    'heading' => __('Css', 'motors'),
                    'param_name' => 'css',
                    'group' => __('Design options', 'motors')
                )
            )
        ));


        vc_map(array(
            'name' => __('STM Products Grid', 'motors'),
            'base' => 'stm_car_class_grid',
            'category' => __('STM', 'motors'),
            'html_template' => get_stylesheet_directory().'/vc_templates/stm_car_class_grid.php',
            'params' => array(
                array(
                    'type' => 'textfield',
                    'heading' => __('Number of items to show', 'motors'),
                    'param_name' => 'posts_per_page',
                ),
                array(
                    'type' => 'checkbox',
                    'heading' => __('Display Rent Cars?', 'motors'),
                    'param_name' => 'display_rent_cars',
                ),
                array(
                    'type' => 'checkbox',
                    'heading' => __('Display Simple Cars?', 'motors'),
                    'param_name' => 'display_simple_cars',
                ),
                array(
                    'type' => 'css_editor',
                    'heading' => __('Css', 'motors'),
                    'param_name' => 'css',
                    'group' => __('Design options', 'motors')
                )
            )
        ));

    }

}


add_action('stm_add_rental_offices', 'stm_add_rental_fields_de');

function stm_add_rental_fields_de($manager){

    ?>
    <style>
        #butterbean-control-in_rental_car_de {
            position: absolute;
            right: 30px;
            text-align: right;
        }
    </style>
    <?php


    $manager->register_control(
        'in_rental_car_de',
        array(
            'type' => 'checkbox',
            'section' => 'stm_info',
            'value' => 'on',
            'label' => esc_html__( 'In rental?', 'stm_vehicles_listing' ),
            'attr' => array( 'class' => 'widefat order_1' )
        )
    );

    /*Settings*/
    $manager->register_setting(
        'in_rental_car_de',
        array( 'sanitize_callback' => 'stm_listings_validate_checkbox' )
    );
}
