class AutoComplete {

    static $action = 'my_autocomplete';//Name of the action - should be unique to your plugin.

    static function load() {
        add_action( 'init', array( __CLASS__, 'init'));
    }

    static function init() {
        //Register style - you can create your own jQuery UI theme and store it in the plug-in folder
        wp_register_style('my-jquery-ui','http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css');    
        add_action( 'get_search_form', array( __CLASS__, 'get_search_form' ) );
        add_action( 'wp_print_footer_scripts', array( __CLASS__, 'print_footer_scripts' ), 11 );
        add_action( 'wp_ajax_'.self::$action, array( __CLASS__, 'autocomplete_suggestions' ) );
        add_action( 'wp_ajax_nopriv_'.self::$action, array( __CLASS__, 'autocomplete_suggestions' ) );
    }

    static function get_search_form( $form ) {
        wp_enqueue_script( 'jquery-ui-autocomplete' );
        wp_enqueue_style('my-jquery-ui');
        return $form;
    }

    static function print_footer_scripts() {
        ?>
    <script type="text/javascript">
    jQuery(document).ready(function ($){
        var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
        var ajaxaction = '<?php echo self::$action ?>';
        $("#secondary #searchform #s").autocomplete({
            delay: 0,
            minLength: 0,
            source: function(req, response){  
                $.getJSON(ajaxurl+'?callback=?&action='+ajaxaction, req, response);  
            },
            select: function(event, ui) {
                window.location.href=ui.item.link;
            },
        });
    });
    </script><?php
    }

    static function autocomplete_suggestions() {
        $posts = get_posts( array(
            's' => trim( esc_attr( strip_tags $_REQUEST['term'] ) ) ),
        ) );
        $suggestions=array();

        global $post;
        foreach ($posts as $post): 
                    setup_postdata($post);
            $suggestion = array();
            $suggestion['label'] = esc_html($post->post_title);
            $suggestion['link'] = get_permalink();

            $suggestions[]= $suggestion;
        endforeach;

        $response = $_GET["callback"] . "(" . json_encode($suggestions) . ")";  
        echo $response;  
        exit;
    }
}
AutoComplete::load();
