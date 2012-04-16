<?php
/*
Author: Stephen Harris http://profiles.wordpress.org/stephenh1988/
Github: https://github.com/stephenh1988

This is a class implementation of the wp.tuts+ tutorial: http://wp.tutsplus.com/tutorials/theme-development/add-jquery-autocomplete-to-your-sites-search/

In the tutorial the javascript is inside a seperate file. For convenience for me- and because I feel the script is small enough - in this class I am printing the script
in the footer (after the necessary jQuery files). Generally you should follow the method outlined in the tutorial.

This can be used as-is inside a functions.php or a plug-in. Whichever you feel is more appropriate for your use case.

The class constants are 
  - action: The name to identify the ajax action corresponding to generating a suggestion list. Should be unique to you and the action.
*/
class AutoComplete {

    //Name of the action - should be unique to you and this action.
    static $action = 'my_autocomplete';

    public function load() {
        add_action( 'init', array( __CLASS__, 'init'));
    }

    public function init() {
        //Register style - you can create your own jQuery UI theme and store it in the plug-in folder
        wp_register_style('my-jquery-ui','http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css');    

	//Enqueue our scripts & styles when search form is displayed
        add_action( 'get_search_form', array( __CLASS__, 'get_search_form' ) );

	//Ajax actions to return suggestions
        add_action( 'wp_ajax_'.self::$action, array( __CLASS__, 'autocomplete_suggestions' ) );
        add_action( 'wp_ajax_nopriv_'.self::$action, array( __CLASS__, 'autocomplete_suggestions' ) );
    }

    static function get_search_form( $form ) {
        wp_enqueue_script( 'jquery-ui-autocomplete' );
        wp_enqueue_style('my-jquery-ui');
        add_action( 'wp_print_footer_scripts', array( __CLASS__, 'print_footer_scripts' ), 11 );
    }

    private function print_footer_scripts() {
        ?>
	<script type="text/javascript">
	jQuery(document).ready(function ($){
		var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
        	var ajaxaction = '<?php echo self::$action ?>';
        	$("#secondary #searchform #s").autocomplete({
			delay: 0,
			minLength: 3,
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

    private function autocomplete_suggestions() {
        $posts = get_posts( array(
            's' => $_REQUEST['term'],
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

        echo $_GET["callback"] . "(" . json_encode($suggestions) . ")";  
        exit;
    }
}
AutoComplete::load();
