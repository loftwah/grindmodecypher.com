<?php
/* ------------------------------------------------------------------------- *
 *  Admin panel functions
/* ------------------------------------------------------------------------- */

/*  Post formats script
/* ------------------------------------ */
if ( !function_exists( 'hu_post_formats_script' ) ) {

  function hu_post_formats_script( $hook ) {
    // Only load on posts, pages
    if ( !in_array($hook, array('post.php','post-new.php')) )
      return;

    global $post;
    wp_enqueue_script('post-formats', get_template_directory_uri() . '/assets/admin/js/post-formats.js', array( 'jquery' ));
    wp_localize_script( 'post-formats',
      'HUPostFormatsParams' ,
      array(
        'currentPostFormat' => get_post_format( $post ),
      )
    );
  }

}
add_action( 'admin_enqueue_scripts', 'hu_post_formats_script');


/* ------------------------------------------------------------------------- *
 *  Loads and instanciates admin pages related classes
/* ------------------------------------------------------------------------- */
if ( is_admin() && !hu_is_customizing() ) {
    if ( !defined( 'HU_IS_PRO' ) || !HU_IS_PRO ) {
        //Update notice
        load_template( get_template_directory() . '/functions/admin/class-admin-update-notification.php' );
        new HU_admin_update_notification;
    }
    if ( hu_is_checked('about-page') ) {
      load_template( get_template_directory() . '/functions/admin/class-admin-page.php' );
      new HU_admin_page;
    }
}

add_action( 'admin_init' , 'hu_admin_style' );

function hu_admin_style() {
  wp_enqueue_style(
    'hu-admincss',
    sprintf('%1$sassets/admin/css/hu_admin.css' , HU_BASE_URL ),
    array(),
    ( defined('WP_DEBUG') && true === WP_DEBUG ) ? time() : HUEMAN_VER
  );
}


/* ------------------------------------------------------------------------- *
 *  Loads functions for plugin recommendation
/* ------------------------------------------------------------------------- */
if ( is_admin() && !hu_is_customizing() && !hu_is_plugin_active('nimble-builder/nimble-builder.php') ) {
    load_template( get_template_directory() . '/functions/admin/class-plugin-rec.php' );
}


/* ------------------------------------------------------------------------- *
 *  Initialize the meta boxes.
/* ------------------------------------------------------------------------- */
//Managing plugins on jetpack's wordpress.com dashboard fix
//https://github.com/presscustomizr/hueman/issues/541
//For some reason admin_init is fired but is_admin() returns false
//so some required OT admin files are not loaded:
//see OT_Loader::admin_includes() : it returns if not is_admin()
if ( is_admin() ) {
    add_action( 'admin_init', 'hu_custom_meta_boxes' );
}

function hu_custom_meta_boxes() {

    /*  Custom meta boxes
    /* ------------------------------------ */
    $page_options = array(
      'id'          => 'page-options',
      'title'       => 'Page Options',
      'desc'        => '',
      'pages'       => array( 'page' ),
      'context'     => 'normal',
      'priority'    => 'high',
      'fields'      => array(
        array(
          'label'   => 'Heading',
          'id'    => '_heading',
          'type'    => 'text'
        ),
        array(
          'label'   => 'Subheading',
          'id'    => '_subheading',
          'type'    => 'text'
        ),
        array(
          'label'   => sprintf('%1$s</br><i style="font-size:12px">%2$s</i>', __('Select a widget zone for the primary sidebar.', 'hueman'), __('Notes : 1)This will override any default settings of the customizer options panel. 2) The primary sidebar is placed on the left in a 3 columns layout. It can be on the right in a 2 columns layout, when the content is on the left.', 'hueman') ),
          'id'    => '_sidebar_primary',
          'type'    => 'sidebar-select',
          'desc'    => ''
        ),
        array(
          'label'   => sprintf('%1$s</br><i style="font-size:12px">%2$s</i>', __('Select a widget zone for the secondary sidebar.', 'hueman'), __('Notes : 1)This will override any default settings of the customizer options panel. 2) The secondary sidebar is placed on the right in a 3 columns layout.', 'hueman') ),
          'id'    => '_sidebar_secondary',
          'type'    => 'sidebar-select',
          'desc'    => ''
        )
      )
    );

    $post_options = array(
      'id'          => 'post-options',
      'title'       => 'Post Options',
      'desc'        => '',
      'pages'       => apply_filters( 'hu_custom_meta_boxes_post_options_in', array( 'post') ),
      'context'     => 'normal',
      'priority'    => 'high',
      'fields'      => array(
        array(
          'label'    => sprintf('%1$s</br><i style="font-size:12px">%2$s</i>', __('Select a widget zone for the left sidebar.', 'hueman'), __('This will override any default settings of the customizer options panel.', 'hueman') ),
          'id'    => '_sidebar_primary',
          'type'    => 'sidebar-select',
          'desc'    => ''
        ),
        array(
          'label'    => sprintf('%1$s</br><i style="font-size:12px">%2$s</i>', __('Select a widget zone for the right sidebar.', 'hueman'), __('This will override any default settings of the customizer options panel.', 'hueman') ),
          'id'    => '_sidebar_secondary',
          'type'    => 'sidebar-select',
          'desc'    => ''
        )
      )
    );


    if ( apply_filters( 'hu_enable_singular_layout_meta_box', true ) ) {
      $post_options['fields'][] = array(
          'label'   => 'Layout',
          'id'    => '_layout',
          'type'    => 'radio-image',
          'desc'    => 'Overrides the default layout option',
          'std'   => 'inherit',
          'choices' => array(
            array(
              'value'   => 'inherit',
              'label'   => 'Inherit Layout',
              'src'   => get_template_directory_uri() . '/assets/admin/img/layout-off.png'
            ),
            array(
              'value'   => 'col-1c',
              'label'   => '1 Column',
              'src'   => get_template_directory_uri() . '/assets/admin/img/col-1c.png'
            ),
            array(
              'value'   => 'col-2cl',
              'label'   => '2 Column Left',
              'src'   => get_template_directory_uri() . '/assets/admin/img/col-2cl.png'
            ),
            array(
              'value'   => 'col-2cr',
              'label'   => '2 Column Right',
              'src'   => get_template_directory_uri() . '/assets/admin/img/col-2cr.png'
            ),
            array(
              'value'   => 'col-3cm',
              'label'   => '3 Column Middle',
              'src'   => get_template_directory_uri() . '/assets/admin/img/col-3cm.png'
            ),
            array(
              'value'   => 'col-3cl',
              'label'   => '3 Column Left',
              'src'   => get_template_directory_uri() . '/assets/admin/img/col-3cl.png'
            ),
            array(
              'value'   => 'col-3cr',
              'label'   => '3 Column Right',
              'src'   => get_template_directory_uri() . '/assets/admin/img/col-3cr.png'
            )
          )
        );

        $page_options['fields'][] = array(
          'label'   => sprintf('%1$s</br><i style="font-size:12px">%2$s</i>', __('Select a layout for this page.', 'hueman'), __('This will override any default settings of the customizer options panel.', 'hueman') ),
          'id'    => '_layout',
          'type'    => 'radio-image',
          'desc'    => '',
          'std'   => 'inherit',
          'choices' => array(
            array(
              'value'   => 'inherit',
              'label'   => 'Inherit Layout',
              'src'   => get_template_directory_uri() . '/assets/admin/img/layout-off.png'
            ),
            array(
              'value'   => 'col-1c',
              'label'   => '1 Column',
              'src'   => get_template_directory_uri() . '/assets/admin/img/col-1c.png'
            ),
            array(
              'value'   => 'col-2cl',
              'label'   => '2 Column Left',
              'src'   => get_template_directory_uri() . '/assets/admin/img/col-2cl.png'
            ),
            array(
              'value'   => 'col-2cr',
              'label'   => '2 Column Right',
              'src'   => get_template_directory_uri() . '/assets/admin/img/col-2cr.png'
            ),
            array(
              'value'   => 'col-3cm',
              'label'   => '3 Column Middle',
              'src'   => get_template_directory_uri() . '/assets/admin/img/col-3cm.png'
            ),
            array(
              'value'   => 'col-3cl',
              'label'   => '3 Column Left',
              'src'   => get_template_directory_uri() . '/assets/admin/img/col-3cl.png'
            ),
            array(
              'value'   => 'col-3cr',
              'label'   => '3 Column Right',
              'src'   => get_template_directory_uri() . '/assets/admin/img/col-3cr.png'
            )
          )
        );
    }



    //post format are @fromfull => keep it in hueman on wp.org
    $post_format_audio = array(
      'id'          => 'format-audio',
      'title'       => 'Format: Audio',
      'desc'        => 'These settings enable you to embed audio into your posts. You must provide both .mp3 and .ogg/.oga file formats in order for self hosted audio to function accross all browsers.',
      'pages'       => array( 'post' ),
      'context'     => 'normal',
      'priority'    => 'high',
      'fields'      => array(
        array(
          'label'   => 'MP3 File URL',
          'id'    => '_audio_mp3_url',
          'type'    => 'upload',
          'desc'    => 'The URL to the .mp3 or .m4a audio file'
        ),
        array(
          'label'   => 'OGA File URL',
          'id'    => '_audio_ogg_url',
          'type'    => 'upload',
          'desc'    => 'The URL to the .oga, .ogg audio file'
        )
      )
    );
    $post_format_gallery = array(
      'id'          => 'format-gallery',
      'title'       => 'Format: Gallery',
      'desc'        => '<a title="Add Media" data-editor="content" class="button insert-media add_media" id="insert-media-button" href="#">Add Media</a> <br /><br />
                To create a gallery, upload your images and then select "<strong>Uploaded to this post</strong>" from the dropdown (in the media popup) to see images attached to this post. You can drag to re-order or delete them there. <br /><br /><i>Note: Do not click the "Insert into post" button. Only use the "Insert Media" section of the upload popup, not "Create Gallery" which is for standard post galleries.</i>',
      'pages'       => array( 'post' ),
      'context'     => 'normal',
      'priority'    => 'high',
      'fields'      => array()
    );
    $post_format_chat = array(
      'id'          => 'format-chat',
      'title'       => 'Format: Chat',
      'desc'        => 'Input chat dialogue.',
      'pages'       => array( 'post' ),
      'context'     => 'normal',
      'priority'    => 'high',
      'fields'      => array(
        array(
          'label'   => 'Chat Text',
          'id'    => '_chat',
          'type'    => 'textarea',
          'rows'    => '2'
        )
      )
    );
    $post_format_link = array(
      'id'          => 'format-link',
      'title'       => 'Format: Link',
      'desc'        => 'Input your link.',
      'pages'       => array( 'post' ),
      'context'     => 'normal',
      'priority'    => 'high',
      'fields'      => array(
        array(
          'label'   => 'Link Title',
          'id'    => '_link_title',
          'type'    => 'text'
        ),
        array(
          'label'   => 'Link URL',
          'id'    => '_link_url',
          'type'    => 'text'
        )
      )
    );
    $post_format_quote = array(
      'id'          => 'format-quote',
      'title'       => 'Format: Quote',
      'desc'        => 'Input your quote.',
      'pages'       => array( 'post' ),
      'context'     => 'normal',
      'priority'    => 'high',
      'fields'      => array(
        array(
          'label'   => 'Quote',
          'id'    => '_quote',
          'type'    => 'textarea',
          'rows'    => '2'
        ),
        array(
          'label'   => 'Quote Author',
          'id'    => '_quote_author',
          'type'    => 'text'
        )
      )
    );
    $post_format_video = array(
      'id'          => 'format-video',
      'title'       => 'Format: Video',
      'desc'        => 'These settings enable you to embed videos into your posts.',
      'pages'       => array( 'post' ),
      'context'     => 'normal',
      'priority'    => 'high',
      'fields'      => array(
        array(
          'label'   => 'Video URL',
          'id'    => '_video_url',
          'type'    => 'text',
          'desc'    => ''
        )
      )
    );

    /*  Register meta boxes
    /* ------------------------------------ */
    ot_register_meta_box( $page_options );
    ot_register_meta_box( $post_format_audio );
    ot_register_meta_box( $post_format_chat );
    ot_register_meta_box( $post_format_gallery );
    ot_register_meta_box( $post_format_link );
    ot_register_meta_box( $post_format_quote );
    ot_register_meta_box( $post_format_video );
    ot_register_meta_box( $post_options );
}


if ( is_admin() && !hu_is_customizing() ) {
    add_action( 'init' , 'hu_add_editor_style' );
    //@return void()
    //hook : init
    // It used to be after_setup_theme, but, don't know from whic WP version, is_rtl() always returns false at that stage.
    function hu_add_editor_style() {
        //we need only the relative path, otherwise get_editor_stylesheets() will treat this as external CSS
        //which means:
        //a) child-themes cannot override it
        //b) no check on the file existence will be made (producing the rtl error, for instance : https://github.com/presscustomizr/customizr/issues/926)
        $_stylesheets = array(
            'assets/admin/css/block-editor-style.css', //block editor style
            'assets/admin/css/editor-style.css',
            //hu_get_front_style_url(),
            //get_stylesheet_uri()
        );

        $gfont_src = hu_maybe_add_gfonts_to_editor();
        if ( apply_filters( 'hu_add_user_fonts_to_editor' , false != $gfont_src ) )
          $_stylesheets = array_merge( $_stylesheets , $gfont_src );

        add_editor_style( $_stylesheets );
    }


    /*
    * @return css string
    *
    */
    function hu_maybe_add_gfonts_to_editor() {
      $user_font     = hu_get_option( 'font' );
      $gfamily       = hu_get_fonts( array( 'font_id' => $user_font, 'request' => 'src' ) );//'Source+Sans+Pro:400,300italic,300,400italic,600&subset=latin,latin-ext',
      //bail here if self hosted font (titilium) of web font
      if ( ( empty( $gfamily ) || !is_string( $gfamily ) ) )
        return;

      //Commas in a URL need to be encoded before the string can be passed to add_editor_style.
      return array(
        str_replace(
          ',',
          '%2C',
          sprintf( '//fonts.googleapis.com/css?family=%s', $gfamily )
        )
      );
    }
}

add_filter( 'tiny_mce_before_init'  , 'hu_user_defined_tinymce_css' );

/**
* Extend TinyMCE config with a setup function.
* See http://www.tinymce.com/wiki.php/API3:event.tinymce.Editor.onInit
* http://wordpress.stackexchange.com/questions/120831/how-to-add-custom-css-theme-option-to-tinymce
* @package Customizr
* @since Customizr 3.2.11
*
*/
function hu_user_defined_tinymce_css( $init ) {
  if ( !apply_filters( 'hu_add_custom_fonts_to_editor' , true ) )
    return $init;

  if ( 'tinymce' != wp_default_editor() )
    return $init;

  //some plugins fire tiny mce editor in the customizer
  //in this case, the CZR_resource class has to be loaded
  // if ( !class_exists('CZR_resources') || !is_object(CZR_resources::$instance) ) {
  //   CZR___::$instance -> czr_fn_req_once( 'inc/czr-init.php' );
  //   new CZR_resources();
  // }

  // google / web fonts style
  $user_font    = hu_get_option( 'font' );
  $family       = hu_get_fonts( array( 'font_id' => $user_font, 'request' => 'family' ) );//'"Raleway", Arial, sans-serif'
  $family       = ( empty( $family ) || !is_string( $family ) ) ? "'Titillium Web', Arial, sans-serif" : $family;

  //maybe add rtl class
  $_mce_body_context = is_rtl() ? 'mce-content-body.rtl' : 'mce-content-body';

  //fonts
  $_css = "body.{$_mce_body_context}{ font-family : {$family}; }\n";

  $init['content_style'] = trim( preg_replace('/\s+/', ' ', $_css ) );

  return $init;
}