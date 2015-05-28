<?php
/*
Plugin Name: WP API JSON READER
Plugin URI: http://wordpress.org/extend/plugins/wpapi-json-reader/
Description: This plugin provide a widget(s) to get, read and show posts from wordpress sites (with WP REST API Installed) to an other(s) wordpress site(s)!
Author: elsteno
Author URI: http://elsteno.gr/
Version: 1.1
Domain Path: /languages
Text Domain: wpapijsonreader
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html

License URI: https://www.gnu.org/licenses/gpl-2.0.html

{Plugin Name} is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

{Plugin Name} is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with {Plugin Name}. If not, see {License URI}.
*/






/**
 * Version number.
 *
 * @var string
 */
define( 'WPAPI_JSON_READER_VERSION', '0.1.0' );

/**
 * Define Directory and Register Styles.
 *
 *
 */
define( 'WPAPI_JSON_READER_URL', plugins_url( '', __FILE__ ) );
add_action( 'wp_enqueue_scripts', 'wpapi_json_reader_styles' );

function wpapi_json_reader_styles(){
    wp_enqueue_style( 'wpapi-json-reader', WPAPI_JSON_READER_URL."/styles.css" );
}

/**
 * Exit if accessed directly
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;





class WP_Api_Json_Feeds extends WP_Widget {

    public function __construct() {

        parent::__construct(
            'WP_Api_Json_Feeds',
            __( 'WP API JSON FEEDS', 'wpapijsonreader' ),
            array(
                'classname'   => 'wpapijsonreader_widget',
                'description' => __( 'A Simple WP-API JSON Reader Plugin', 'wpapijsonreader' )
                )
        );

        load_plugin_textdomain( 'wpapijsonreader', false, basename( dirname( __FILE__ ) ) . '/languages' );

    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget( $args, $instance ) {

        extract( $args );

        $title      	= apply_filters( 'widget_title', $instance['title'] );
        $restURL    	= $instance['wpapiUrl'];
        $restPostsNum   = $instance['wpapiPostsNum'];
        $restPostsCat   = $instance['wpapiPostsCat'];


        echo $before_widget;

        if ( $title ) :
            echo $before_title . $title . $after_title;
        endif;

        if ( ! $restPostsNum ) :
	    	$restPostsNum = 5;
        endif;

        if ( ! $restPostsCat ) :
	    	$restPostsCat= -1;
        endif;

        if ( $restURL && $restPostsNum && $restPostsCat ):
	        $url = $restURL.'/wp-json/posts?filter[cat]='.$restPostsCat.'&filter[posts_per_page]='.$restPostsNum;
        endif;




        $response = wp_remote_get( $url );

		if( is_wp_error( $response ) ) {

			$error_message = $response->get_error_message();
			echo "Something went wrong: $error_message";

		} else {

			$data = wp_remote_retrieve_body( $response );

			if ( ! is_wp_error( $data ) ) :

				$apotelesma = json_decode( $data );

				echo '<ul class="jsonlists">';

				foreach ($apotelesma as $pare ) :

					$trititle = wp_trim_words( $pare->title, 10, '' );
					echo '<li><a href="'.$pare->link.'">' . $trititle . '...</a></li>';

				endforeach;

				echo '</ul>';

			endif;

		}


        echo $after_widget;

    }




    /**
      * Sanitize widget form values as they are saved.
      *
      * @see WP_Widget::update()
      *
      * @param array $new_instance Values just sent to be saved.
      * @param array $old_instance Previously saved values from database.
      *
      * @return array Updated safe values to be saved.
      */
    public function update( $new_instance, $old_instance ) {

        $instance = $old_instance;

        $instance['title'] 			= strip_tags( $new_instance['title'] );
        $instance['wpapiUrl'] 		= strip_tags( $new_instance['wpapiUrl'] );
        $instance['wpapiPostsNum'] 	= strip_tags( $new_instance['wpapiPostsNum'] );
        $instance['wpapiPostsCat'] 	= strip_tags( $new_instance['wpapiPostsCat'] );

        return $instance;

    }



    /**
      * Back-end widget form.
      *
      * @see WP_Widget::form()
      *
      * @param array $instance Previously saved values from database.
      */
    public function form( $instance ) {

        $title      	= esc_attr( $instance['title'] );
        $wpapiUrl   	= esc_attr( $instance['wpapiUrl'] );
        $wpapiPostsNum	= esc_attr( $instance['wpapiPostsNum'] );
        $wpapiPostsCat	= esc_attr( $instance['wpapiPostsCat'] );
        ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Widget Title:','wpapijsonreader' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>

 		<p>
			<label for="<?php echo $this->get_field_id( 'wpapiUrl' ); ?>"><?php _e( 'Website URL:','wpapijsonreader' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'wpapiUrl' ); ?>" name="<?php echo $this->get_field_name( 'wpapiUrl' ); ?>" type="text" value="<?php echo esc_attr( $wpapiUrl ); ?>">
		</p>

 		<p>
			<label for="<?php echo $this->get_field_id( 'wpapiPostsNum' ); ?>"><?php _e( 'Number of Posts:','wpapijsonreader' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'wpapiPostsNum' ); ?>" name="<?php echo $this->get_field_name( 'wpapiPostsNum' ); ?>" type="text" value="<?php echo esc_attr( $wpapiPostsNum ); ?>">
		</p>

 		<p>
			<label for="<?php echo $this->get_field_id( 'wpapiPostsCat' ); ?>"><?php _e( 'Posts Category ID:','wpapijsonreader' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'wpapiPostsCat' ); ?>" name="<?php echo $this->get_field_name( 'wpapiPostsCat' ); ?>" type="text" value="<?php echo esc_attr( $wpapiPostsCat ); ?>">
		</p>



    <?php
    }


}


/* Register the widget */
add_action( 'widgets_init', function(){
     register_widget( 'WP_Api_Json_Feeds' );
});