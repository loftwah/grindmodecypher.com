<?php
//MODULE PARAMS
add_filter( 'czr_js_customizer_control_params', 'ha_add_pro_control_params');
//hook : 'czr_js_customizer_control_params'
function ha_add_pro_control_params( $params ) {
    $params = ! is_array( $params ) ? array() : $params;
    //force isThemeSwitchOn to false in Hueman Pro Addon
    $params['isThemeSwitchOn'] = false;
    $params['isPro'] = true;
    return $params;
}
