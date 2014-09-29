<?php
/*
Plugin Name: BuddyPress Avatar Cropper Skipper
Version: 0.1
Description: Skip the BuddyPress avatar cropper when uploading a new avatar image.
Author: Jeroen Schmit
Author URI: http://slimndap.com
Plugin URI: http://slimndap.com
*/

class BP_Avatar_Cropper_Skipper {
	
	function __construct() {
		add_action('bp_template_redirect',array($this,'bp_template_redirect'));
	}

	/**
	 * Skip the Buddypress avatar cropper.
	 * Automatically crop the uploaded image to the default avatar dimensions.
	 * @see xprofile_screen_change_avatar()
	 */

	function bp_template_redirect() {
	
		if ( 'crop-image' == bp_get_avatar_admin_step() ) {
		
			/**
			 * Calculate the width and height based on the default avatar dimensions.
			 */
			 
			$args = $this->get_crop_values();
	
			if ( ! bp_core_avatar_handle_crop( $args ) ) {
				bp_core_add_message( __( 'There was a problem cropping your avatar.', 'buddypress' ), 'error' );
			} else {
				do_action( 'xprofile_avatar_uploaded' );
				bp_core_add_message( __( 'Your new avatar was uploaded successfully.', 'buddypress' ) );
				bp_core_redirect( bp_loggedin_user_domain() );
			}
			
		}

	}
	
	/**
	 * Get the crop values for the avatar.
	 * Note: the cropping area is placed in the center of the uploaded image.
	 */
	
	function get_crop_values() {
	
		/**
		 * Calculate the target crop ratio, taken from the default avatar dimensions.
		 */
		 
		$crop_ratio = bp_core_avatar_full_width() / bp_core_avatar_full_height();
		
		/**
		 * Calculate the ratio of the uploaded image.
		 */
		 
		$image_data = getimagesize(bp_core_avatar_upload_path().bp_get_avatar_to_crop_src());
		$image_ratio = $image_data[0] / $image_data[1];
		

		/**
		 * Calculate crop values.
		 */

		if ( $image_ratio > $crop_ratio ) {
		
			/**
			 * The target image is taller than the uploaded image.
			 * Cut off from left and right of uploaded image.
			 */ 
		
			$crop_values = array(
				'crop_w' => $image_data[1] * $crop_ratio,
				'crop_h' => $image_data[1],
				'crop_x' => ($image_data[0] - ($image_data[1] * $crop_ratio)) / 2,
				'crop_y' => 0,
			);
		} else {

			/**
			 * The uploaded image is taller than the target image.
			 * Cut off from top and bottom of uploaded image.
			 */ 
		
			$crop_values = array(
				'crop_w' => $image_data[0],
				'crop_h' => $image_data[0] / $crop_ratio,
				'crop_x' => 0,
				'crop_y' => ($image_data[1] - ($image_data[0] / $crop_ratio)) / 2,
			);
		}

		$crop_values['item_id'] = bp_displayed_user_id();
		$crop_values['original_file'] = bp_get_avatar_to_crop_src();
		
		return $crop_values;
		
	}
	
}

/**
 * Load only when BuddyPress is present.
 */
function bp_avatar_cropper_skipper_loader() {
	new BP_Avatar_Cropper_Skipper();
}
add_action( 'bp_include', 'bp_avatar_cropper_skipper_loader' );


