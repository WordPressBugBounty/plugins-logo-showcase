<?php
	if ( ! defined( 'ABSPATH' ) ) {
	    exit;
	}
?>

<style type="text/css">
	.logo-showcase-main-section-<?php echo esc_attr( $postid ); ?>{
		transition: all 0.5s;
	}
	.logo-showcase-main-section-<?php echo esc_attr( $postid ); ?> .lsw-logo-items{
		background: <?php echo esc_attr( $logo_showcase_items_background_color ) ?>;
		border: <?php echo esc_attr( $logo_showcase_item_roderwidth ); ?>px solid <?php echo esc_attr( $logo_showcase_columns_border_color ) ?>;
	    padding: <?php echo esc_attr( $logo_showcase_item_padding ); ?>px;
		border-radius: <?php echo esc_attr( $logo_showcase_columns_radius ); ?>px;
		transition: all 0.5s;
	    -webkit-box-align: center;
	    -ms-flex-align: center;
	    -webkit-box-pack: center;
	    -ms-flex-pack: center;
	    -webkit-align-items: center;
	    align-items: center;
	    display: -webkit-box;
	    display: -webkit-flex;
	    display: -ms-flexbox;
	    display: flex;
	    -webkit-justify-content: center;
	    justify-content: center;
	}
	.logo-showcase-main-section-<?php echo esc_attr( $postid ); ?> .lsw-logo-items:hover{
		background: <?php echo esc_attr( $logo_showcase_items_hover_background ) ?>;
		border: <?php echo esc_attr( $logo_showcase_item_roderwidth ); ?>px solid <?php echo esc_attr( $logo_showcase_columns_border_hover_color ) ?>;
	}
	.logo-showcase-main-section-<?php echo esc_attr( $postid ); ?> .lsw-logo-items-thumb{
/*		text-align: center;
	    position: relative;
	    overflow: hidden;*/
	}
	<?php if( $logo_showcase_columns_image_effect == 1 ){ ?>
		.logo-showcase-main-section-<?php echo esc_attr( $postid ); ?> .lsw-logo-items-thumb img{
		    filter:grayscale(0);
			transition: 0.3s;
		}
	<?php } ?>
	<?php if( $logo_showcase_columns_image_effect_hover == 1 ){ ?>
		.logo-showcase-main-section-<?php echo esc_attr( $postid ); ?> .lsw-logo-items-thumb img:hover{
		    filter:grayscale(0);
			transition: 0.3s;
		}
	<?php } ?>
	<?php if( $logo_showcase_columns_hover_effect == 1 ){ ?>
		.logo-showcase-main-section-<?php echo esc_attr( $postid ); ?> .lsw-logo-items:hover img{
		    -webkit-transform: scale(1.10);
		    -moz-transform: scale(1.10);
		    transform: scale(1.10);
		}
	<?php }elseif ( $logo_showcase_columns_hover_effect == 2 ) { ?>
		.logo-showcase-main-section-<?php echo esc_attr( $postid ); ?> .lsw-logo-items img{
			-webkit-transform: scale(1.1);
		    transform: scale(1.1);
		    -webkit-transition: .3s ease-in-out;
		    transition: .3s ease-in-out;
		}
		.logo-showcase-main-section-<?php echo esc_attr( $postid ); ?> .lsw-logo-items:hover img{
			-webkit-transform: scale(1);
		    transform: scale(1);
		}
	<?php }elseif ( $logo_showcase_columns_hover_effect == 3 ) { ?>
		.logo-showcase-main-section-<?php echo esc_attr( $postid ); ?> .lsw-logo-items:hover{
		    transform:translateY(-0.3rem);
		}
	<?php } ?>
	<?php if ( $logo_showcase_free_show_title_hide == 1 ) { ?>
		.logo-showcase-main-section-<?php echo esc_attr( $postid ); ?> .lsw-logo-items .lsw-logo-items-title{
			text-align: <?php echo esc_attr( $logo_showcase_columns_title_position ); ?>;
			font-size: <?php echo esc_attr( $logo_showcase_columns_title_font_size ); ?>px;
			font-style:<?php echo esc_attr( $logo_showcase_free_title_font_style ); ?>;
			color:<?php echo esc_attr( $logo_showcase_columns_title_font_color ); ?>;
			margin-bottom: 10px;
		}
	<?php } ?>
	<?php if ( $logo_showcase_navigation == 'true' ) { ?>
	#logo-showcase-wrap-<?php echo esc_attr( $postid ); ?> .owl-nav {}
	#logo-showcase-wrap-<?php echo esc_attr( $postid ); ?> .owl-nav .owl-next,
	#logo-showcase-wrap-<?php echo esc_attr( $postid ); ?> .owl-nav .owl-prev {
		position: absolute;
	    top: 0;
	    left: auto;
	    right: 0;
		width: 30px;
		height: 30px;
		line-height: 30px;
		text-align:center;
		background: <?php echo esc_attr( $logo_showcase_navigation_bg_color ); ?> none repeat scroll 0 0;
		color: <?php echo esc_attr( $logo_showcase_navigation_text_color ); ?>;
		border: 1px solid <?php echo esc_attr( $logo_showcase_navigation_bg_color ); ?>;
		border-radius: 0%;
	}
	#logo-showcase-wrap-<?php echo esc_attr( $postid ); ?> .owl-nav .owl-next{
		right: 35px;
	}
	#logo-showcase-wrap-<?php echo esc_attr( $postid ); ?> .owl-nav .owl-prev{ }
	<?php if ( $logo_showcase_navigation_position == 'topleft' ){ ?>
		#logo-showcase-wrap-<?php echo esc_attr( $postid ); ?>{
			padding-top:50px;
		}
		#logo-showcase-wrap-<?php echo esc_attr( $postid ); ?> .owl-nav .owl-next{
			right: auto;
		}
		#logo-showcase-wrap-<?php echo esc_attr( $postid ); ?> .owl-nav .owl-prev{
			left:35px;
		}
	<?php }elseif ( $logo_showcase_navigation_position == 'centred' ){ ?>
		#logo-showcase-wrap-<?php echo esc_attr( $postid ); ?> .owl-nav .owl-next{
		    top: 50%;
		    transform: translateY(-50%);
		    left:-15px;
		}
		#logo-showcase-wrap-<?php echo esc_attr( $postid ); ?> .owl-nav .owl-prev{
		    top: 50%;
		    transform: translateY(-50%);
		    right:-15px;
		}
	<?php }elseif ( $logo_showcase_navigation_position == 'topright' ){ ?>
		#logo-showcase-wrap-<?php echo esc_attr( $postid ); ?>{
			padding-top:50px;
		}
	<?php } ?>
	#logo-showcase-wrap-<?php echo esc_attr( $postid ); ?> .owl-nav .owl-prev{}
	#logo-showcase-wrap-<?php echo esc_attr( $postid ); ?> .owl-nav .owl-next:hover,
	#logo-showcase-wrap-<?php echo esc_attr( $postid ); ?> .owl-nav .owl-prev:hover {
		color: <?php echo esc_attr( $logo_showcase_navigation_hover_text_color ); ?>;
		background: <?php echo esc_attr( $logo_showcase_navigation_hover_bg_color ); ?> none repeat scroll 0 0;
		border: 1px solid <?php echo esc_attr( $logo_showcase_navigation_hover_bg_color ); ?>;
	}
	<?php } if ( $logo_showcase_pagination == 'true' ) { ?>
	#logo-showcase-wrap-<?php echo esc_attr( $postid ); ?> .owl-dots {
	    display: block;
	  	text-align: center;
	    width: 100%;
	    overflow: hidden;
	    margin: 0;
	    margin-top: 10px;
	    padding: 0;
	}
	<?php if( $logo_showcase_pagination_style == 1 ){ ?>
		#logo-showcase-wrap-<?php echo esc_attr( $postid ); ?> .owl-dots .owl-dot {
			width: 10px;
			height: 10px;
			display: inline-block;
			position: relative;
			background: <?php echo esc_attr( $logo_showcase_pagination_bg_color ); ?>;
			margin: 0px 4px;
			border-radius: 50%;
		}
	<?php }elseif( $logo_showcase_pagination_style == 2 ){ ?>
		#logo-showcase-wrap-<?php echo esc_attr( $postid ); ?> .owl-dots .owl-dot {
			width: 10px;
			height: 10px;
			display: inline-block;
			position: relative;
			background: <?php echo esc_attr( $logo_showcase_pagination_bg_color ); ?>;
			margin: 0px 4px;
			border-radius: 0%;
		}
	<?php }elseif( $logo_showcase_pagination_style == 3 ){ ?>
		#logo-showcase-wrap-<?php echo esc_attr( $postid ); ?> .owl-dots .owl-dot {
			width: 25px;
			height: 6px;
			display: inline-block;
			position: relative;
			background: <?php echo esc_attr( $logo_showcase_pagination_bg_color ); ?>;
			margin: 0px 4px;
			border-radius: 50px;
		}
	<?php } ?>
	#logo-showcase-wrap-<?php echo esc_attr( $postid ); ?> .owl-dots .owl-dot.active {
		background: <?php echo esc_attr( $logo_showcase_pagination_active_bg_color ); ?>;
	}
	<?php } ?>
</style>

<div class="logo-showcase-main-section-<?php echo esc_attr( $postid ); ?>">
	<div class="logo-showcase-wrap owl-carousel"
	    id="logo-showcase-wrap-<?php echo esc_attr($postid); ?>"
	    data-loop="<?php echo esc_attr($loop); ?>"
	    data-margin="<?php echo esc_attr($margin); ?>"
	    data-autoplay="<?php echo esc_attr($logo_showcase_columns_show_auto_play); ?>"
	    data-autoplay-speed="<?php echo esc_attr($logo_showcase_columns_show_slide_speed); ?>"
	    data-autoplay-timeout="<?php echo esc_attr($autoplaytimeout); ?>"
	    data-hover-pause="<?php echo esc_attr($stop_hover_play); ?>"
	    data-nav="<?php echo esc_attr($logo_showcase_navigation); ?>"
	    data-dots="<?php echo esc_attr($logo_showcase_pagination); ?>"
	    data-items-mobile="<?php echo esc_attr($itemsmobile); ?>"
	    data-items-tablet="<?php echo esc_attr($itemsdesktopsmall); ?>"
	    data-items-desktop="<?php echo esc_attr($itemsdesktop); ?>"
	    data-items-large="<?php echo esc_attr($logo_showcase_columns_show_items); ?>"
		data-tooltip-enabled="<?php echo esc_attr($logo_showcase_columns_show_hide_tooltips); ?>"
	    data-tipso-position="<?php echo esc_attr($logo_showcase_tooltips_positions); ?>"
	    data-tipso-color="<?php echo esc_attr($logo_showcase_tooltips_color); ?>"
	    data-tipso-bgcolor="<?php echo esc_attr($logo_showcase_tooltips_bgcolor); ?>"
	    >

		<?php
			$i = 0;

			foreach ( $featuress as $feature ) {
			    // 1. Get the image markup
			    $logothumb = wp_get_attachment_image( $feature['logo_showcase_uploader'], 'thumb-full', false );
			    
			    // 2. Setup dynamic HTML attributes and classes for clean markup
			    $item_classes = array( 'lsw-logo-items' );
			    $data_attr    = '';
			    
			    if ( $logo_showcase_columns_show_hide_tooltips == 1 && ! empty( $feature['logo_showcase_title'] ) ) {
			        $item_classes[] = 'js-tipso-trigger';
			        $item_classes[] = 'tipso_style';
			        $data_attr      = ' data-tipso="' . esc_attr( $feature['logo_showcase_title'] ) . '"';
			    }
			    
			    // Convert class array to space-separated string
			    $class_string = implode( ' ', $item_classes );
			    ?>

			    <div class="<?php echo esc_attr( $class_string ); ?>"<?php echo $data_attr; ?>>
			        <div class="lsw-logo-items-thumb">
			            
			            <?php if ( empty( $feature['logo_showcase_link_url'] ) ) : ?> 
			                <?php echo $logothumb; ?>
			            <?php else : ?>
			                <a href="<?php echo esc_url( $feature['logo_showcase_link_url'] ); ?>">
			                    <?php echo $logothumb; ?>
			                </a>
			            <?php endif; ?>

			            <?php if ( $logo_showcase_free_show_title_hide == 1 && ! empty( $feature['logo_showcase_title'] ) ) : ?>
			                <div class="lsw-logo-items-title"> 
			                    <?php echo esc_html( $feature['logo_showcase_title'] ); ?> 
			                </div>
			            <?php endif; ?>

			        </div>
			    </div>

			    <?php 
			    // 3. Break out of the loop after 8 iterations
			    $i++; 
			    if ( $i === 8 ) { 
			        break; 
			    } 
			} 
		?>
	</div>
</div>