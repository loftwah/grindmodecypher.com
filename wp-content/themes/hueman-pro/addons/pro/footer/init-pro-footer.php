<?php
/**
* PRO FOOTER CLASS
* @author Nicolas GUILLAUME
* @since 1.0
*/
final class PC_HAFOOTER {
    static $instance;

    function __construct () {
        self::$instance     =& $this;
        //- content picker nonce
        add_filter( 'hu_credits_html', array( $this, 'ha_set_pro_footer_credits' ) );
    }//end of construct

    function ha_set_pro_footer_credits() {
        ob_start();
        ?>
          <div id="credit" style="<?php echo ! hu_is_checked( 'credit' ) ? 'display:none' : ''; ?>">
            <p><?php _e('Powered by','hueman-pro'); ?>&nbsp;<a class="fab fa-wordpress" title="<?php _e( 'Powered by WordPress', 'hueman-pro' ) ?>" href="<?php echo esc_url( __( 'https://wordpress.org/', 'hueman-pro' ) ); ?>" target="_blank"></a> - <?php _e('Designed with','hueman-pro'); ?>&nbsp;<a href="https://presscustomizr.com/hueman-pro" title="<?php _e('Hueman Pro','hueman-pro'); ?>"><?php _e('Hueman Pro','hueman-pro'); ?></a></p>
          </div><!--/#credit-->
        <?php
        $credits_html = ob_get_contents();
        if ($credits_html) ob_end_clean();
        return $credits_html;
    }
} //end of class