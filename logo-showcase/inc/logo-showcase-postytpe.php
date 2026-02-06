<?php
	if ( ! defined( 'ABSPATH' ) ) {
	    exit;
	}

	// Register logo showcase Post Type
	function logo_showcase_wordpress_post_types_register() {
		$labels = array(
			'name'               => _x( 'Logo Showcase', 'post type general name', 'logoshowcase' ),
			'singular_name'      => _x( 'Logo Showcase', 'post type singular name', 'logoshowcase' ),
			'menu_name'          => _x( 'Logo Showcase', 'admin menu', 'logoshowcase' ),
			'name_admin_bar'     => _x( 'Logo Showcase', 'add new on admin bar', 'logoshowcase' ),
			'add_new'            => _x( 'Add Showcase', 'Logo Showcase', 'logoshowcase' ),
			'add_new_item'       => __( 'Add Showcase', 'logoshowcase' ),
			'new_item'           => __( 'New Showcase', 'logoshowcase' ),
			'edit_item'          => __( 'Edit Logo Showcase', 'logoshowcase' ),
			'view_item'          => __( 'View Logo Showcase', 'logoshowcase' ),
			'all_items'          => __( 'All Showcases', 'logoshowcase' ),
			'search_items'       => __( 'Search Logo Showcase', 'logoshowcase' ),
			'parent_item_colon'  => __( 'Parent Logo Showcase:', 'logoshowcase' ),
			'not_found'          => __( 'No Showcase found.', 'logoshowcase' ),
			'not_found_in_trash' => __( 'No Showcase found in Trash.', 'logoshowcase' )
		);
		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Description.', 'logoshowcase' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'Logo Showcase' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title' ),
		);
		register_post_type( 'tplogoshowcase', $args );
	}
	add_action( 'init', 'logo_showcase_wordpress_post_types_register' );

	// Logo Showcase Register Column
	function logo_showcase_free_add_shortcode_column_7( $columns7 ) {
		return array_merge( $columns7,
			array(
				'shortcode' 	=> __( 'Shortcode', 'logoshowcase' ),
				'doshortcode' 	=> __( 'Template Shortcode', 'logoshowcase' )
			)
		);
	}
	add_filter( 'manage_tplogoshowcase_posts_columns' , 'logo_showcase_free_add_shortcode_column_7' );

	// Logo Showcase Display Shortcode or Do Shortcode into column
	function logo_showcase_add_posts_shortcode_display_7( $column7, $post_id ) {
		if ( $column7 == 'shortcode' ) { ?>
			<input style="background:#ddd" type="text" onClick="this.select();" value="[logo_showcase <?php echo 'id=&quot;'.$post_id.'&quot;';?>]" />
			<?php
		}
		if ( $column7 == 'doshortcode' ) { ?>
			<textarea cols="40" rows="2" style="background:#ddd;" onClick="this.select();" ><?php echo '<?php echo do_shortcode( "[logo_showcase id='; echo "'".$post_id."']"; echo '" ); ?>'; ?></textarea>
			<?php
		}
	}
	add_action( 'manage_tplogoshowcase_posts_custom_column' , 'logo_showcase_add_posts_shortcode_display_7', 10, 2 );

	// Change logo showcase Post Title
	function logo_showcase_wordpress_title( $title ) {
	  	$screen = get_current_screen();
	  	if  ( 'tplogoshowcase' == $screen->post_type ) {
			$title = 'Logo Showcase Title';
	  	}
	  	return $title;
	}
	add_filter( 'enter_title_here', 'logo_showcase_wordpress_title' );

	function logoshowcase_wp_shortcode_section($post) {
	    // Show only for 'tplogoshowcase' post type
	    if ($post->post_type !== 'tplogoshowcase') {
	        return;
	    }

	    // Generate the dynamic shortcode
	    $shortcode = "[logo_showcase id='" . $post->ID . "']";
	    $php_code = '<?php echo do_shortcode("[logo_showcase id=' . $post->ID . ']"); ?>';
	    ?>
	    
	    <div style="padding: 15px 15px 25px 15px; border: 1px solid #ddd; background: #f9f9f9; margin-top: 15px;">
		    <div style="display: flex; gap: 20px;">
			    <div style="width: 50%;">
			        <p>
			            <strong><?php _e( 'Shortcode','logoshowcase' ); ?>:</strong>
			            <span id="shortcode-notice" style="color: green; display: none; margin-left: 10px;"><?php _e( 'Shortcode copied!','logoshowcase' ); ?></span>
			        </p>
			        <p class="option-info"><?php _e('Click to copy the shortcode and paste it into a page or post to display Logo Showcase.','logoshowcase' ); ?></p>
			        <input type="text" id="shortcode-text" style="width:100%; cursor:pointer; box-shadow: none; border:none;outline:none;border-radius: 0" value="<?php echo esc_attr($shortcode); ?>" readonly onclick="copyToClipboard(this, 'shortcode-notice')">
			    </div>
			    <div style="width: 50%;">
			        <p>
			            <strong><?php _e( 'PHP Code for Theme Files','logoshowcase' ); ?>:</strong>
			            <span id="php-notice" style="color: green; display: none; margin-left: 10px;"><?php _e( 'PHP code copied!','logoshowcase' ); ?></span>
			        </p>
			        <p class="option-info"><?php _e('Click to copy the PHP code and use it in your theme files to display Logo Showcase.','logoshowcase' ); ?></p>
			        <input type="text" id="php-code-text" style="width:100%; cursor:pointer; box-shadow: none; border:none;outline:none;border-radius: 0" value="<?php echo esc_attr($php_code); ?>" readonly onclick="copyToClipboard(this, 'php-notice')">
			    </div>
		    </div>
	    </div>

	    <script>
	        function copyToClipboard(inputField, noticeId) {
	            inputField.select();
	            navigator.clipboard.writeText(inputField.value);

	            // Show copied message beside the label
	            var notice = document.getElementById(noticeId);
	            notice.style.display = "inline";

	            // Hide the message after 2 seconds
	            setTimeout(function() {
	                notice.style.display = "none";
	            }, 2000);
	        }
	    </script>
	    <?php
	}
	add_action('edit_form_after_title', 'logoshowcase_wp_shortcode_section');