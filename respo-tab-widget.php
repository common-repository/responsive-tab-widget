<?php
/*
Plugin Name: Responsive Tab Widget
Version: 0.2
Description: Display popular posts and recent posts in a tab format in your site.
Author: <a href="http://crayonux.com/about-me-2/">Basanta Moharana</a>
Author URI: http://crayonux.com/about-me-2/ 
Plugin URI: http://crayonux.com/responsive-tab-widget/
*/




/**
 * Front-end scripts
 */

if(!function_exists('respotab_enqueue_scripts')){

	function respotab_enqueue_scripts(){		
		wp_enqueue_style( 'respo', plugins_url( 'respo.css' , __FILE__ ), false, '0.0.1' );
	
		// JS script
		wp_enqueue_script('jquery-tabs-new', 'http://code.jquery.com/ui/1.10.3/jquery-ui.js', array('jquery') );
		wp_enqueue_script('jquery-tabs', plugins_url( 'js/jquery-tabs-init.js' , __FILE__ ), array('jquery-tabs-new') );			
	}
}

add_action( 'wp_enqueue_scripts', 'respotab_enqueue_scripts' );

/**
 * Register Responsive Tab Widget
 */
 
 
function register_respo_tab_widget() {
    register_widget( 'Respo_Tab_Widget' );
}
add_action( 'widgets_init', 'register_respo_tab_widget' );


 
function respo_set_post_views($postID) {
    $count_key = 'respo_post_views_count';
    $count = get_post_meta($postID, $count_key, true);
    if($count==''){
        $count = 0;
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '0');
    }else{
        $count++;
        update_post_meta($postID, $count_key, $count);
    }
}

remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0); 



/**
 * Add filter to single post
 */
 

function respo_post_view() {
	if(is_single()){
		respo_set_post_views(get_the_ID());
		// return fftw_get_post_views(get_the_ID());
	}
} 


 

/**
 * Responsive Tab Widget Class
 */


class Respo_Tab_Widget extends WP_Widget {

	
	/**
	 * Widget actual process
	 * Reference: http://codex.wordpress.org/Plugins/WordPress_Widgets_Api
	 */
	
	public function __construct() {
		$widget_ops = array('classname' => 'respo_tab_widget', 'description' => __('Display popular posts, recent posts in tabs.'));
		$control_ops = array('width' => 250, 'height' => 350);
		parent::__construct(null, __('Responsive Tab Widget'), $widget_ops, $control_ops);
	}
	
	
	
	
	/**
	 * Widget output
	 * Reference: http://codex.wordpress.org/Plugins/WordPress_Widgets_Api
	 */ 
	 
	public function widget( $args, $instance ) {
		extract( $args );
		$popular = empty( $instance['pop'] ) ? '' : $instance['pop'];
		$poplimit = empty( $instance['poplimit'] ) ? '' : $instance['poplimit'];
		$recent = empty( $instance['recent'] ) ? '' : $instance['recent'];
		$recentlimit = empty( $instance['recentlimit'] ) ? '' : $instance['recentlimit'];
		echo $before_widget;
	?>			
	
	<div class="respo-tab-widget-wrap">	
		<ul class="respo-nav tabs">
			<?php
				$popular = $popular;			
				$recent = $recent;
			?>
			
			<?php if(!empty ($popular)){ ?>
			<li class="popular"><a href="#popular"><?php echo esc_html($popular); ?></a></li>
			<?php } ?>
			<?php if(!empty($recent)){ ?>
			<li class="recent"><a href="#recent"><?php echo esc_html($recent); ?></a></li>
			<?php } ?>
		</ul>
		
		<div class="respo-panes tabs-panes">
		
			
			<?php if(!empty ($popular)){ ?>
			<div class="respo-pane1" id="popular">
				<?php 	
				
				// Popular posts	
				
				$popular = new WP_Query( array( 
					'posts_per_page' => $poplimit, 
					'meta_key' => 'respo_post_views_count', 
					'orderby' => 'meta_value_num', 
					'order' => 'DESC'  
				) );
				
				$html = '<ul class="respo-show-thumbnail">';
				while ( $popular->have_posts() ) : $popular->the_post();
					$html .= '<li>';
					$html .= '<a href="'. get_permalink() .'">';
					$html .= get_the_post_thumbnail(get_the_ID(), array(100,100));	
					$html .= '<span>' . get_the_title() . '</span>';
					$html .= '</a>';
					$html .= '</li>';
				endwhile;		
				$hmtl .= '</ul>';					
				echo $html; 				
				?>
			</div><!--pane1 -->	
			<?php } ?>
		
			
			<?php if(!empty($recent)){ ?>
			<div class="respo-pane2" id="recent">
			
				<?php
				
				/**
				 * Recent posts
				 * ------------
				 */			 

				global $post;

				$args = array(
					'post_type' => 'post', 
					'numberposts' => $recentlimit		
				);

				$get_query_posts = get_posts($args);

				if($get_query_posts) :

					$count=0;
					$html = '<ul class="respo-show-thumbnail">';
					foreach($get_query_posts as $post) : 
						setup_postdata($post);
						$count++;
						$html .= '<li>';
						$html .= '<a href="'. get_permalink() .'">';
						$html .= get_the_post_thumbnail(get_the_ID(), array(100,100));	
						$html .= '<span>' . get_the_title() . '</span>';
						$html .= '</a>';
						$html .= '</li>';
						
					endforeach;	
					$html .= '</ul>';					
					echo $html;
				endif;
				// End of recent posts
				?>		
			
			</div> <!--pane-2 -->
			<?php } ?>
		</div>
		
	</div>	
		
		
		
		
	<?php	
		
		echo $after_widget;
		
	}	
	

	
	
	/**
	 * Widget form on admin
	 * Reference: http://codex.wordpress.org/Plugins/WordPress_Widgets_Api
	 */ 
	
	public function form( $instance ) {

		$instance = wp_parse_args( (array) $instance, array(  
			'pop' => 'Popular', 
			'poplimit' => 10,
			'recent' => 'Recent', 
			'recentlimit' => 10
		) );
		$popular = strip_tags($instance['pop']);
		$poplimit = strip_tags($instance['poplimit']);
		$recent = strip_tags($instance['recent']);
		$recentlimit = strip_tags($instance['recentlimit']);
	?>	
	
		
		<!-- = Popular posts setting -->
	
		<p><span><strong><?php _e('Popular posts:'); ?></strong></span><br />
		
		<label for="<?php echo $this->get_field_id('pop'); ?>" style="display:inline;"><?php _e('Label: ');?></label><input id="<?php echo $this->get_field_id('pop'); ?>" size="29" name="<?php echo $this->get_field_name('pop'); ?>" type="text" value="<?php echo esc_attr($popular); ?>" /> <br />
		
		<label for="<?php echo $this->get_field_id('poplimit'); ?>" style="display:inline;"><?php _e('Limit number: ');?></label><input id="<?php echo $this->get_field_id('poplimit'); ?>" size="2" name="<?php echo $this->get_field_name('poplimit'); ?>" type="text" value="<?php echo esc_attr($poplimit); ?>" />
		
		</p>
		
		<!-- / Popular posts setting -->
		
		
		<!-- = Recent posts setting -->
		
		<p><span><strong><?php _e('Recent posts:'); ?></strong></span><br />
		
		<label for="<?php echo $this->get_field_id('recent'); ?>" style="display:inline;"><?php _e('Label: ');?></label><input id="<?php echo $this->get_field_id('recent'); ?>" size="29" name="<?php echo $this->get_field_name('recent'); ?>" type="text" value="<?php echo esc_attr($recent); ?>" /> <br />
		
		<label for="<?php echo $this->get_field_id('recentlimit'); ?>" style="display:inline;"><?php _e('Limit number: ');?></label><input id="<?php echo $this->get_field_id('recentlimit'); ?>" size="2" name="<?php echo $this->get_field_name('recentlimit'); ?>" type="text" value="<?php echo esc_attr($recentlimit); ?>" />
		
		</p>
		
		<!--Recent posts setting -->
	<?php
	}
	/**
	 * Processes widget options to be saved
	 * Reference: http://codex.wordpress.org/Plugins/WordPress_Widgets_Api
	 */ 
	 
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['pop'] = strip_tags($new_instance['pop']);
		$instance['poplimit'] = strip_tags($new_instance['poplimit']);
		$instance['recent'] = strip_tags($new_instance['recent']);
		$instance['recentlimit'] = strip_tags($new_instance['recentlimit']);	
		return $instance;
	}	
	
}