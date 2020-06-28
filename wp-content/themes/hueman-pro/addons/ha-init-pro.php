<?php

//last version sync
if( ! defined( 'LAST_THEME_VERSION_FMK_SYNC' ) ) define( 'LAST_THEME_VERSION_FMK_SYNC' , '3.3.10' );//<= used only in the free addons, but has to be defined here because invoked in addons/ha-czr.php
if( ! defined( 'MINIMAL_AUTHORIZED_THEME_VERSION' ) ) define( 'MINIMAL_AUTHORIZED_THEME_VERSION' , '3.3.0' );

/* ------------------------------------------------------------------------- *
 *  LOADS PRO ADDONS CLASSES
/* ------------------------------------------------------------------------- */
//PRO HEADER
if ( ha_is_skop_on() ) {
    //Requires the skope feature
    if ( ! is_object( HU_AD() -> pro_header ) ) {
        require_once( HA_BASE_PATH . 'addons/pro/header/init-pro-header.php' );
        HU_AD() -> pro_header = new PC_HAPH();
        // require_once( HA_BASE_PATH . 'addons/header-posts-slider/init-posts-slider.php' );
        // HU_AD() -> posts_slider_header = new HU_POSTS_SLIDER_HEADER();
    }
}

//PRO FOOTER
if ( ! is_object( HU_AD() -> pro_footer ) ) {
  require_once( HA_BASE_PATH . 'addons/pro/footer/init-pro-footer.php' );
  HU_AD() -> pro_footer = new PC_HAFOOTER();
}

//PRO RELATED POSTS
if ( ! is_object( HU_AD() -> pro_related_posts ) ) {
  require_once( HA_BASE_PATH . 'addons/pro/related/init-pro-related-posts.php' );
  HU_AD() -> pro_related_posts = new PC_HAPRELPOSTS();
  // require_once( HA_BASE_PATH . 'addons/header-posts-slider/init-posts-slider.php' );
  // HU_AD() -> posts_slider_header = new HU_POSTS_SLIDER_HEADER();
}

//MASONRY
if ( ! is_object( HU_AD() -> pro_grids ) ) {
  require_once( HA_BASE_PATH . 'addons/pro/grids/init-pro-grids.php' );
  HU_AD() -> pro_grids = new PC_HAPGRIDS();
}

//INFINITE
if ( ! is_object( HU_AD() -> pro_infinite ) ) {
  require_once( HA_BASE_PATH . 'addons/pro/infinite/init-pro-infinite.php' );
  HU_AD() -> pro_infinite_scroll = new PC_HAPINF();
}

//WFC
//this autoloads
if ( file_exists( HA_BASE_PATH . 'addons/pro/wfc/wordpress-font-customizer.php' ) ) {
  require_once( HA_BASE_PATH . 'addons/pro/wfc/wordpress-font-customizer.php' );
} else {
  error_log( 'Missing WFC file');
}

//CUSTOM SCRIPTS
if ( ! is_object( HU_AD() -> pro_custom_scripts ) ) {
  require_once( HA_BASE_PATH . 'addons/pro/custom-scripts/init-pro-custom-scripts.php' );
  HU_AD() -> pro_custom_scripts = new PC_HA_CUSTOM_SCRIPTS();
}

//SKINS
// if ( ! is_object( HU_AD() -> pro_skins ) ) {
//   require_once( HA_BASE_PATH . 'addons/pro/skins/init-pro-skins.php' );
//   HU_AD() -> pro_skins = new PC_HASKINS();
// }




/* ------------------------------------------------------------------------- *
*  LOAD PRO MODULES AND INPUTS TEMPLATES
*  Since June 2018, loaded from each individual module
/* ------------------------------------------------------------------------- */
// function hu_load_pro_module_tmpl() {
//   $_tmpl = array(
//       //MODULES
//       //HA_BASE_PATH . 'addons/pro/czr_tmpl/mods/slide-module-tmpl.php',
//       //HA_BASE_PATH . 'addons/pro/czr_tmpl/mods/related-posts-module-tmpl.php',
//       //HA_BASE_PATH . 'addons/pro/czr_tmpl/mods/posts-slider-module-tmpl.php',
//   );
//   foreach ($_tmpl as $_path) {
//       require_once( $_path );
//   }
// }
// hu_load_pro_module_tmpl();




//LOADS RESOURCES :
//=> Extends the preview callbacks for pro modules
//=> add localized params
require_once( HA_BASE_PATH . 'addons/pro/czr_resources/1_modules-resources-for-controls.php' );
require_once( HA_BASE_PATH . 'addons/pro/czr_resources/2_modules-resources-for-preview.php' );
require_once( HA_BASE_PATH . 'addons/pro/czr_resources/3_modules-resources-for-preview.php' );

/* ------------------------------------------------------------------------- *
 *  LOAD ASSETS RESSOURCES
/* ------------------------------------------------------------------------- */
//FRONT ASSETS :
add_action( 'wp_enqueue_scripts', 'ha_enqueue_pro_front_assets');
//hook : 'wp_enqueue_scripts'
/* Enqueue Plugin resources */
function ha_enqueue_pro_front_assets() {
    wp_enqueue_style(
        'hph-front-style' ,
        //sprintf('%1$s/front/assets/css/hph-front%2$s.css' , HU_PRO_HEADER_BASE_URL, ( defined('WP_DEBUG') && true === WP_DEBUG ) ? '' : '.min'),
        sprintf('%1$saddons/assets/front/css/hph-front%2$s.css' , HU_AD() -> ha_get_base_url() , ( defined('WP_DEBUG') && true === WP_DEBUG ) ? '' : '.min'),
        null,
        ( defined('WP_DEBUG') && true === WP_DEBUG ) ? HUEMAN_VER . time() : HUEMAN_VER,
        $media = 'all'
    );

    // Used for the pro header slider and the related posts
    // Registered here and enqueued as dependency of 'hph-js' in class_hap_front.php if needed on the page
    // is also enqueued in init-pro-related-posts.php when is_single()
    // @todo : don't load if related and pro header off
    if ( hu_is_customizing() ) {
        wp_enqueue_script(
            'hph-flickity-js',
            sprintf('%1$saddons/pro/header/assets/front/vendors/flickity%2$s.js' , HU_AD() -> ha_get_base_url() , ( defined('WP_DEBUG') && true === WP_DEBUG ) ? '' : '.min'),
            null,
            ( defined('WP_DEBUG') && true === WP_DEBUG ) ? HUEMAN_VER . time() : HUEMAN_VER,
            true
        );
    } else {
        wp_register_script(
            'hph-flickity-js',
            sprintf('%1$saddons/pro/header/assets/front/vendors/flickity%2$s.js' , HU_AD() -> ha_get_base_url() , ( defined('WP_DEBUG') && true === WP_DEBUG ) ? '' : '.min'),
            null,
            ( defined('WP_DEBUG') && true === WP_DEBUG ) ? HUEMAN_VER . time() : HUEMAN_VER,
            true
        );
    }

}