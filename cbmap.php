<?php
/*
Plugin Name: WP Confiscati Bene map shortcode
Plugin URI: http://wordpress.org/plugins/wp-cbmap-shortcode/
Description: [cbmap t=(1..20, istat codes for Italian regions), dl=(regioni|province|comuni, first shown level), i=((int), istat codes for Italian regions, provinces or municipalities (depends on dl parameter))] shortcode
Version: 0.1
Author: jenkin
Author URI: https://github.com/Dataninja
License: GPLv3
*/


if ( ! function_exists( 'cbmap_unqprfx_embed_shortcode' ) ) :

	function cbmap_unqprfx_enqueue_script() {
		wp_enqueue_script( 'jquery' );
	}
	add_action( 'wp_enqueue_scripts', 'cbmap_unqprfx_enqueue_script' );
	
	
	function cbmap_unqprfx_embed_shortcode( $atts, $content = null ) {
        $defaults = array(
            'width' => '100%',
            'height' => '640',
            'src' => 'http://dev.dataninja.it/confiscatibene/anbsc/choropleth/',
            'md' => 'embed'
		);

		foreach ( $defaults as $default => $value ) { // add defaults
			if ( ! @array_key_exists( $default, $atts ) ) { // hide warning with "@" when no params at all
				$atts[$default] = $value;
			}
		}

		// get_params_from_url
		if ( isset( $atts["get_params_from_url"] ) && ( $atts["get_params_from_url"] == '1' || $atts["get_params_from_url"] == 1 ) ) {
			$encode_string = '';
			if ( $_GET != NULL ) {
				if ( strpos( $atts["src"], '?' ) ) { // if we already have '?' and GET params
					$encode_string = '&';
				} else {
					$encode_string = '?';
				}
				foreach( $_GET as $key => $value ) {
					$encode_string .= $key.'='.$value.'&';
				}
			}
			$encode_string = rtrim($encode_string, '&'); // remove last '&'
			$atts["src"] .= $encode_string;
		}

		$html = '';
		if ( isset( $atts["same_height_as"] ) ) {
			$same_height_as = $atts["same_height_as"];
		} else {
			$same_height_as = '';
		}
		
		if ( $same_height_as != '' ) {
			$atts["same_height_as"] = '';
			if ( $same_height_as != 'content' ) { // we are setting the height of the iframe like as target element
				if ( $same_height_as == 'document' || $same_height_as == 'window' ) { // remove quotes for window or document selectors
					$target_selector = $same_height_as;
				} else {
					$target_selector = '"' . $same_height_as . '"';
				}
				$html .= '
					<script>
					jQuery(function($){
						var target_height = $(' . $target_selector . ').height();
						$("iframe.' . $atts["class"] . '").height(target_height);
					});
					</script>
				';
			} else { // set the actual height of the iframe (show all content of the iframe without scroll)
				$html .= '
					<script>
					jQuery(function($){
						$("iframe.' . $atts["class"] . '").bind("load", function() {
							var embed_height = $(this).contents().find("body").height();
							$(this).height(embed_height);
						});
					});
					</script>
				';
			}
		}
        $html .= "\n".'<!-- wp-cbmap-shortcode plugin v.2.9 wordpress.org/plugins/wp-cbmap-shortcode/ -->'."\n";
		$html .= '<iframe width="'.$atts['width'].'" height="'.$atts['height'].'" scrolling="no" frameborder="0" class="iframe cbmap" src="'.$atts['src'].'?';
        foreach( $atts as $attr => $value ) {
			if ( $attr != 'same_height_as' && $attr != 'src' && $attr != 'width' && $attr != 'height' ) { // remove some attributes
				if ( $value != '' ) { // adding all attributes
					$html .= $attr . '=' . $value . '&';
				} else { // adding empty attributes
					$html .= ' ' . $attr;
				}
			}
        }
        trim($html,"&"); // Remove last &
		$html .= '"></iframe>'."\n";
		return $html;
	}
	add_shortcode( 'cbmap', 'cbmap_unqprfx_embed_shortcode' );
	

	function cbmap_unqprfx_plugin_meta( $links, $file ) { // add 'Plugin page' and 'Donate' links to plugin meta row
		if ( strpos( $file, 'cbmap.php' ) !== false ) {
			$links = array_merge( $links, array( '<a href="https://github.com/Dataninja/wp-cbmap-shortcode" title="Plugin page">WP Confiscati Bene map shortcode</a>' ) );
			//$links = array_merge( $links, array( '<a href="http://web-profile.com.ua/donate/" title="Support the development">Donate</a>' ) );
		}
		return $links;
	}
	add_filter( 'plugin_row_meta', 'cbmap_unqprfx_plugin_meta', 10, 2 );
	
endif; // end of (function_exists('cbmap_unqprfx_embed_shortcode'))
