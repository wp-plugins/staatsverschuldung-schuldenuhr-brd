<?php

/*
Plugin Name: ITSecMedia BRD Schuldenuhr
Plugin URI: https://www.coininvest.com
Description: Schuldenuhr der Bundesrepublik Deutschland inklusive pro-Kopf Verschuldung
Version: 1.0.00
Author: CoinInvest / ITSecMedia
Author URI: https://www.coininvest.com
*/

/**
 * DEFAULTS
 ******************************************************************************/

# require_once(dirname(__FILE__) . '/includes/ism-price-sql.class.php');
# require_once(dirname(__FILE__) . '/includes/ism-price-html.class.php');

if ( !defined( 'ABSPATH' ) ) {
    exit( 'Sorry, you are not allowed to access this file directly.' );
}

# define( 'ISM_PRICE_PLUGIN_DIR', dirname( __FILE__ ) );
# define( 'ISM_PRICE_PLUGIN_DIR', plugin_dir_path(__FILE__) );

/**
 * ACTIVATION & DEACTIVATION
 ******************************************************************************/

register_activation_hook( __FILE__, 'ism_schuldenuhr_activation');

function ism_schuldenuhr_activation() {
}

register_deactivation_hook( __FILE__, 'ism_schuldenuhr_deactivation');

function ism_schuldenuhr_deactivation() {
}

/**
 * STYLESHEETS & INLINE JAVASCRIPT
 ******************************************************************************/

add_action( 'wp_head', 'ism_schuldenuhr_head' );

/**
 * Add the plugin stylesheet to the header
 */
function ism_schuldenuhr_head() {

    $css_url = plugins_url( 'css/default.css', __FILE__ );
    echo '<link type="text/css" rel="stylesheet" href="'.$css_url.'" title="master" media="screen" />'.PHP_EOL;

    // Get plugin directory path:
    // Create global accessible variable for js-script
    // !!! Moved in footer and called within Widget — ism_schuldenuhr_javascript_credit()
    //
    // http://codex.wordpress.org/Determining_Plugin_and_Content_Directories
    // http://codex.wordpress.org/Function_Reference/wp_localize_script
    // http://wordpress.org/support/topic/plugin-javascript-trying-to-reference-plugins-directory
    // http://stackoverflow.com/questions/12426710/wordpress-how-can-i-pick-plugins-directory-in-my-javascript-file
    // http://stackoverflow.com/questions/9631910/write-php-inside-javascript-alert

    # echo "<script type='text/javascript'>var ism_schuldenuhr_url_json = '" . plugin_dir_url( __FILE__ ) . "'</script>";
}

/**
 * CUSTOM JAVASCRIPT FOR CLIENT-BASED DYNAMIC CONFIGURATION OF ism-schuldenuhr.js
 ******************************************************************************/

function ism_schuldenuhr_javascript() {
?>
    <script type="text/javascript">
        var ism_schuldenuhr_url_json = "<?php echo plugin_dir_url( __FILE__ ); ?>";
        var ism_schuldenuhr_donate = false;
    </script>
<?php
}

function ism_schuldenuhr_javascript_credit() {
?>
    <script type="text/javascript">
        var ism_schuldenuhr_url_json = "<?php echo plugin_dir_url( __FILE__ ); ?>";
        var ism_schuldenuhr_donate = true;
    </script>
<?php
}

/**
 * SCRIPTS
 ******************************************************************************/

add_action( 'wp_enqueue_scripts', 'ism_schuldenuhr_scripts' );

/**
 * Load the script files
 */
function ism_schuldenuhr_scripts() {
    wp_register_script( 'ism-schuldenuhr', plugins_url('js/ism-schuldenuhr.js', __FILE__), array( 'jquery' ), '1.0.0', TRUE );
    wp_enqueue_script( 'ism-schuldenuhr' );
    # wp_enqueue_script( 'ism-schuldenuhr', plugins_url('js/ism-schuldenuhr-dist.js', __FILE__), array( 'jquery' ), '1.0.0', TRUE );
}

/**
 * WIDGET
 ******************************************************************************/

add_action( 'widgets_init', 'ism_schuldenuhr_register_widget' );

function ism_schuldenuhr_register_widget() {
    register_widget( 'ISM_schuldenuhr_widget' );
}

class ISM_schuldenuhr_widget extends WP_Widget {

    public function __construct() {

        $widget_ops = array (
            'classname' => 'ism_schuldenuhr', # CSS class name
            'description' => 'Laufende Staatsverschuldung der BRD inklusive Pro-Kopf Berechnung'
        );

        $this->WP_Widget( 'ISM_schuldenuhr_widget', 'ITSecMedia BRD-Schuldenuhr', $widget_ops );
    }

    /**
     * Outputs the content of the widget
     *
     * @param array $args
     * @param array $instance
     */
    function widget($args, $instance) {

        extract($args);

        echo $before_widget;

        $title = apply_filters( 'widget_title', $instance['title'] );
        if (!empty( $title )) {
            echo $before_title . $title . $after_title;
        }

        echo '<div id="ism-schuldenuhr-canvas"></div>'.PHP_EOL;

        echo $after_widget;

        // Add custom JavaScript in footer
        if ( $instance['show_credit'] ) {
            add_action( 'wp_footer', 'ism_schuldenuhr_javascript_credit' );
        } else {
            add_action( 'wp_footer', 'ism_schuldenuhr_javascript' );
        }
    }

    /**
     * Outputs the options form on admin
     *
     * @param array $instance The widget options
     */
    function form($instance) {

        $defaults = array(
            'title'       => 'BRD Schuldenuhr',
            'show_credit' => false
        );

        $instance = wp_parse_args( (array) $instance, $defaults );

        $title       = $instance['title'];
        $show_credit = isset( $instance['show_credit'] ) ? (bool) $instance['show_credit'] : true;
?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'show_credit' ); ?>"><?php _e( 'We appreciate if you show a credit link' ); ?></label>
            <input class="checkbox" type="checkbox" <?php checked( $show_credit ); ?> id="<?php echo $this->get_field_id( 'show_credit' ); ?>" name="<?php echo $this->get_field_name( 'show_credit' ); ?>" />
        </p>
<?php
    }

    /**
     * Processing widget options on save
     *
     * @param array $new_instance The new options
     * @param array $old_instance The previous options
     */
    function update($instance_new, $instance_old) {

        $instance = $instance_old;
        $instance['title']       = strip_tags( $instance_new['title'] );
        $instance['show_credit'] = !empty($instance_new['show_credit']) ? 1 : 0;

        return $instance;
    }
}

/**
 * DEBUG
 ******************************************************************************/

function ism_debug($message) {
    if (WP_DEBUG === true) {
        if (is_array($message) || is_object($message)) {
            error_log(print_r($message, true));
        } else {
            error_log($message);
        }
    }
}
