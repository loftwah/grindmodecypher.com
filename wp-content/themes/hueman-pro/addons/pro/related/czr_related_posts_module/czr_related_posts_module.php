<?php
function hu_register_related_posts_module( $args ) {
    $defaults = array(
        'setting_id' => '',

        'base_url_path' => '',
        'version' => '',

        'option_value' => array(), //<= will be used for the dynamic registration

        'setting' => array(),
        'control' => array(),
        'section' => array(), //array( 'id' => '', 'label' => '' ),

        'sanitize_callback' => '',
        'validate_callback' => ''
    );
    $args = wp_parse_args( $args, $defaults );

    if ( ! isset( $GLOBALS['czr_base_fmk_namespace'] ) ) {
        error_log( __FUNCTION__ . ' => global czr_base_fmk not set' );
        return;
    }

    $czrnamespace = $GLOBALS['czr_base_fmk_namespace'];
    //czr_fn\czr_register_dynamic_module
    $CZR_Fmk_Base_fn = $czrnamespace . 'CZR_Fmk_Base';
    if ( ! function_exists( $CZR_Fmk_Base_fn) ) {
        error_log( __FUNCTION__ . ' => Namespace problem => ' . $CZR_Fmk_Base_fn );
        return;
    }


    $CZR_Fmk_Base_fn() -> czr_pre_register_dynamic_setting( array(
        'setting_id' => $args['setting_id'],
        'module_type' => 'czr_related_posts_module',
        'option_value' => ! is_array( $args['option_value'] ) ? array() : $args['option_value'],

        'setting' => $args['setting'],

        'section' => $args['section'],

        'control' => $args['control']
    ));

    // DEFAULT MODEL
    $defaults_model = HU_AD() -> pro_related_posts -> related_post_model;

    // czr_fn\czr_register_dynamic_module()
    $CZR_Fmk_Base_fn() -> czr_pre_register_dynamic_module( array(
        'dynamic_registration' => true,
        'module_type' => 'czr_related_posts_module',

        // 'sanitize_callback' => 'hu_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'hu_validate_callback__czr_social_module',

        'customizer_assets' => array(
            'control_js' => array(
                // handle + params for wp_enqueue_script()
                // @see https://developer.wordpress.org/reference/functions/wp_enqueue_script/
                'czr_related_posts_module' => array(
                    'src' => sprintf(
                        '%1$s/assets/js/%2$s',
                        $args['base_url_path'],
                        '_4_1_0_related_mod_init.js'
                    ),
                    'deps' => array('customize-controls' , 'jquery', 'underscore'),
                    'ver' => ( defined('WP_DEBUG') && true === WP_DEBUG ) ? time() : $args['version'],
                    'in_footer' => true
                )
            ),
            'localized_control_js' => array(
                'deps' => 'czr-customizer-fmk',
                'global_var_name' => 'relatedPostsModuleParams',
                'params' => array(
                    'defaultModel'        => HU_AD() -> pro_related_posts -> related_post_model,//<= The model is declared once in init-pro-related-posts
                    // 'relPostsCellHeight'  => array(
                    //     'normal'         => __( 'Normal', 'hueman' ),
                    //     'tall'           => __( 'Tall', 'hueman' )
                    // ),
                    'relPostsOrderBy'     => array(
                        'rand'          => __( 'Random order', 'hueman' ),
                        'comment_count' => __( 'Number of comments', 'hueman' ),
                        'date'          => __( 'Published date', 'hueman' )
                    ),
                    'relPostsRelatedBy' => array(
                        'categories'      => __('Categories', 'hueman'),
                        'tags'            => __('Tags', 'hueman'),
                        'post_format'    => __('Post format', 'hueman'),
                        'all'             => __('Categories or tags or post formats', 'hueman'),
                        'no_conds'        => __('No conditions', 'hueman')
                    )
                )
            )
        ),

        'tmpl' => array(
            'item-inputs' => array(
                'tabs' => array(
                    array(
                        'title' => __('Design', 'text_domain_to_be_replaced'),
                        'inputs' => array(
                            'enable' => array(
                                'input_type'  => 'nimblecheck',
                                'title'       => __('Display related posts after a single post', 'text_domain_to_be_replaced'),
                                'title_width' => 'width-80',
                                'input_width' => 'width-20',
                                'default'     => $defaults_model['enable'],//HU_AD() -> pro_related_posts -> related_post_model
                                'notice_after' => __('This is a good way to engage your visitors and generate more page views.', 'hueman')
                            ),
                            'col_number' => array(
                                'input_type'  => 'number',
                                'title'       => __('Max number of columns', 'text_domain_to_be_replaced'),
                                'min'         => 1,
                                'max'         => 4,
                                'step'        => 1,
                                'default'     => $defaults_model['col_number'],//HU_AD() -> pro_related_posts -> related_post_model
                                'notice_after' => __("1 to 4 columns. The number of post columns will be adapted depending on the choosen page layout and the user's device.", 'hueman')
                            ),
                            'display_heading' => array(
                                'input_type'  => 'nimblecheck',
                                'title'       => __('Display a heading for the related posts', 'text_domain_to_be_replaced'),
                                'title_width' => 'width-80',
                                'input_width' => 'width-20',
                                'default'     => $defaults_model['display_heading'],//HU_AD() -> pro_related_posts -> related_post_model
                            ),
                            'heading_text' => array(
                                'input_type'  => 'text',
                                'title'       => __("Heading's text", 'text_domain_to_be_replaced'),
                                'default'     => $defaults_model['heading_text'],//HU_AD() -> pro_related_posts -> related_post_model
                                'placeholder' => __('Enter a heading', 'hueman')
                            ),
                            'freescroll' => array(
                                'input_type'  => 'nimblecheck',
                                'title'       => __('Free scroll', 'text_domain_to_be_replaced'),
                                'title_width' => 'width-80',
                                'input_width' => 'width-20',
                                'default'     => $defaults_model['freescroll'],//HU_AD() -> pro_related_posts -> related_post_model
                                'notice_after' => __('Enables your posts to be freely scrolled and flicked without being aligned to an end position.', 'hueman')
                            ),
                            'ajax_enabled' => array(
                                'input_type'  => 'nimblecheck',
                                'title'       => __('Load just in time', 'hueman'),
                                'title_width' => 'width-80',
                                'input_width' => 'width-20',
                                'default'     => $defaults_model['ajax_enabled'],//HU_AD() -> pro_related_posts -> related_post_model
                                'notice_after' => __('When this option is checked, the block of related posts is loaded just before becoming visible on scroll. Enabling this option <strong>makes your single post pages load faster, in particular on mobile devices.</strong><br/> <strong> Note :</strong> this behaviour is not previewable when customizing but will take effect on your published posts.')
                            ),
                        )//inputs
                    ),//tab1
                    array(
                        'title' => __('Related posts', 'text_domain_to_be_replaced'),
                        'inputs' => array(
                            'post_number' => array(
                                'input_type'  => 'number',
                                'title'       => __('Maximum number of related posts', 'text_domain_to_be_replaced'),
                                'min'         => 1,
                                'step'        => 1,
                                'default'     => $defaults_model['post_number'],//HU_AD() -> pro_related_posts -> related_post_model
                            ),
                            'related_by' => array(
                                'input_type'  => 'select',
                                'title'       => __('Relationship', 'hueman'),
                                'default'     => $defaults_model['related_by'],//HU_AD() -> pro_related_posts -> related_post_model
                            ),
                            'order_by' => array(
                                'input_type'  => 'select',
                                'title'       => __('Choose how to sort your related posts', 'hueman'),
                                'default'     => $defaults_model['order_by'],//HU_AD() -> pro_related_posts -> related_post_model
                                'notice_after' => __('The descending order is always used when relevant.', 'hueman')
                            ),
                        )//inputs
                    )//tab2
                )//tabs
            )
        )//tmpl
    ));
}//hu_register_body_bg_module()
