<?php

/*
Plugin Name: Recent Meetup.com Checkins
Plugin URI: #
Description: A widget to display your recent Meetup.com checkins. More features coming soon!
Author: Brad Parbs
Version: 0.1
Author URI: http://bradparbs.com
License: GPL2+
*/
class meetup_checkins_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'meetup_checkins', // Base ID
            'Recent Meetup Checkins', // Name
            array( 'description' => __( 'Your recent checkins on Meetup.com', 'text_domain' ), ) // Args
        );
    }
    public function widget( $args, $instance ) {
        extract( $args );
        $title = apply_filters( 'widget_title', $instance['title'] );
        $apikey = $instance['apikey'];
        $memberid = $instance['memberid'];


        echo $before_widget;
        if ( ! empty( $title ) )
            echo $before_title . $title . $after_title;

        function grab_from_curl($url){
            $ch = curl_init( $url );
            curl_setopt_array( $ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array('Content-type: application/json') ,
            ) );
        return json_decode(curl_exec($ch)); 
        }
        
        function object_to_array( $object ) {
            if ( is_array( $object ) || is_object( $object ) ) {
                $result = array();
                foreach ( $object as $key => $value ) {
                    $result[$key] = object_to_array( $value );
                }
                return $result;
            }
            return $object;
        }

        $getcheckins = "https://api.meetup.com/2/checkins?key=$apikey&sign=true&member_id=$memberid&page=20";
        
        $result = object_to_array( grab_from_curl( $getcheckins ) );

        echo "<ul>";
        
        foreach ( $result['results'] as $key => $value ) {   

            $eventcheck = $result['results'][$key]['event_id'];
            $event = object_to_array ( grab_from_curl("https://api.meetup.com/2/event/$eventcheck?key=$apikey&sign=true&page=1" ) );

            echo "<li><a href='" ;
            echo $event['event_url'] ;
            echo "'>";
            echo $event['name'] ;
            //echo "</a> organized by <a href='";
            //echo $event['urlname'];
            //echo "'>";
            //echo $event['group']['name'] ;
            echo "</a> on " ;
            echo date( "M n" , substr( $result['results'][$key]['time'], 0, -3) );
            echo "</li>";

        }

        echo "</ul>";

        echo $after_widget;
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = strip_tags( $new_instance['title'] );
        $instance['apikey'] = strip_tags($new_instance['apikey']);
        $instance['memberid'] = strip_tags($new_instance['memberid']);


        return $instance;
    }
    public function form( $instance ) {
        if ( isset( $instance[ 'title' ] ) ) {
            $title = $instance[ 'title' ];
        }
        else {
            $title = __( 'Recent Meetup Checkins', 'text_domain' );
        }
        $apikey = esc_attr($instance['apikey']);
        $memberid = esc_attr($instance['memberid']);


        ?>
        <p>
        <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
        <label for="<?php echo $this->get_field_id('apikey'); ?>"><?php _e('API Key: <em>(You can get it from <a href="http://www.meetup.com/meetup_api/key/">here</a>)</em>'); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id('apikey'); ?>" name="<?php echo $this->get_field_name('apikey'); ?>" type="text" value="<?php echo $apikey; ?>" />
        </p>
        <label for="<?php echo $this->get_field_id('memberid'); ?>"><?php _e('Member ID: <em>(You can get it from your Meetup.com profile. http://meetup.com/members/<strong>1234567</strong>)</em>'); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id('memberid'); ?>" name="<?php echo $this->get_field_name('memberid'); ?>" type="text" value="<?php echo $memberid; ?>" />
        </p>
        <?php 
    }

}
add_action( 'widgets_init', create_function( '', "register_widget('meetup_checkins_Widget');" ) );

function meetup_checkins_grab_event_data( $id, $key ){
        return object_to_array ( grab_from_curl("https://api.meetup.com/2/event/$id?key=$key&sign=true&page=1" ) );

    }

