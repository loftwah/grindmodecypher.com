<?php
if ( ! class_exists( 'WFC_Customize_Modules' ) ) :
    class WFC_Customize_Modules extends WP_Customize_Control {
        public $module_type;
        /**
         * Constructor.
        *
        */
        public function __construct($manager, $id, $args = array()) {
            //let the parent do what it has to
            parent::__construct($manager, $id, $args );
        }

        public function render_content(){}

        public function to_json() {
            parent::to_json();
            $this->json['module_type'] = $this->module_type;
        }

    }
endif;