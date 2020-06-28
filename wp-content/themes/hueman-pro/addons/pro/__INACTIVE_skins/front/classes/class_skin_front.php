<?php
//INSTANTIATED ON 'hu_hueman_loaded'

/**
* FRONT END CLASS
* @author Nicolas GUILLAUME
* @since 1.0
*/
class PC_SKIN_front {

    //Access any method or var of the class with classname::$instance -> var or method():
    static $instance;
    public $current_effect;
    public $model;

    function __construct () {
        self::$instance     =& $this;
        add_action('template_redirect', array( $this, 'set_hooks_and_model') );
    }//end of construct


    //hook : template_redirect
    function set_hooks_and_model() {

    }

} //end of class