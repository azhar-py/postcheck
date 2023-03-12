<?php 
 /**
  * @package PostCheck
 * Plugin Name:       Post Check
 * Plugin URI:        https://github.com/azhar-py/postcheck.git/
 * Description:       Show or hide content based on specific rules.
 * Version:           1.0.1
 * Author:            Azhar Khan
 * Author URI:        https://www.linkedin.com/in/azhar-khan-cs/
 * License:           GPL v2 or later
 * Update URI:        https://github.com/azhar-py/postcheck.git/
 * Text Domain:       my-basics-plugin
 * Domain Path:       /languages
 */


defined('ABSPATH') or die("Hey, you can't access this page");


function activate() {
    // flush rewrite rules
    flush_rewrite_rules();
}

function deactivate() {
    // flush rewrite rules
    flush_rewrite_rules();
}


function hide_post_content($query2) {

    $protected_post = [];
    if(!current_user_can( 'administrator' )){
        global $wpdb;
        $query = "SELECT * FROM {$wpdb->prefix}posts WHERE post_type = 'post' AND post_status = 'publish'";
        $results = $wpdb->get_results( $query );

        foreach ( $results as $post ) {
            $query = "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_mepr_rules_content' AND meta_value = %d";
            $count = $wpdb->get_var( $wpdb->prepare( $query, $post->ID ) );

            if($count > 0 ){
                if(get_current_user_id() == 0 ){
                    array_push($protected_post ,$post->ID);
                }else{
                    $query = "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_mepr_rules_content' AND meta_value = %d";
                    $rules_id = $wpdb->get_var( $wpdb->prepare( $query, $post->ID ) );
                    
                    
                    
                    
                    
                    
                    
                    
            
                    $query = "SELECT memberships  FROM wpon_mepr_members WHERE  user_id = %d";
                    $access_condition = $wpdb->get_var( $wpdb->prepare( $query, get_current_user_id() ) );
               
                    
                    
                 



                    $query = "SELECT user_registered  FROM {$wpdb->users} WHERE  ID = %d";
                    $created_at = $wpdb->get_var( $wpdb->prepare( $query, get_current_user_id() ) );
                
            
            
                    $query = "SELECT COUNT(*) FROM wpon_mepr_rule_access_conditions WHERE rule_id = %d AND access_condition = %d";
                    $count = $wpdb->get_var( $wpdb->prepare( $query, $rules_id  , $access_condition ) );
                    
                    if($count == 0){
                        array_push($protected_post ,$post->ID);
                    }else{
                        $query = "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_mepr_rules_drip_enabled' AND post_id = %d";
                        $drip_status = $wpdb->get_var( $wpdb->prepare( $query, $rules_id ) );

                        if($drip_status == 1){
                          
                            $query = "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_mepr_rules_drip_amount' AND post_id = %d";
                            $drip_amount = $wpdb->get_var( $wpdb->prepare( $query, $rules_id ) );
                            $currentDateTime = date('Y-m-d H:i:s');
                            $diffInDays = round((strtotime($currentDateTime) - strtotime($created_at)) / (60 * 60 * 24));
                            if($drip_amount >  $diffInDays){
                               
                                array_push($protected_post ,$post->ID);
                            }

                        }

                    }
                }
            }

        }

    }
    
    


    $query2->set( 'post__not_in', $protected_post );
}


add_filter('pre_get_posts', 'hide_post_content');

// activation
register_activation_hook( __FILE__, 'activate' );
// deactivation
register_deactivation_hook( __FILE__, 'deactivate' );

