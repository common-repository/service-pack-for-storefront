<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly...
}

/**
 * Slider Module.
 *
 * @class    SPFS_Slider
 * @since    0.0.1
 * @package  SPFS/Modules
 * @category Modules
 * @author   Opportus
 */
class SPFS_Slider {

  /**
   * @var array $slide_link_box
   */
  private $slide_link_box = array( 
	  'id'              => 'spfs_slide_link',
	  'title'           => 'Slide Link',
	  'page'            => array( 'spfs_slide' ),
	  'context'         => 'normal',
	  'priority'        => 'default',
	  'fields'          => array(
		  array(
			  'name'        => 'Slide URL',
			  'desc'        => '',
			  'id'          => 'spfs_slide_url',
			  'class'       => 'spfs_slide_url',
			  'type'        => 'text',
			  'rich_editor' => 0,            
			  'max'         => 0             
		  ),
	  ),
  );


  /**
   * Slider Module constructor.
   */
  public function __construct() {
    add_shortcode( 'spfs_slider', array( $this, 'shortcode' ) );
    add_action( 'init', array( $this, 'register_slide_post_type' ) );
    add_action( 'woocommerce_before_main_content', array( $this, 'template' ), 30 );  
    add_action( 'save_post', array( $this, 'save' ) );
    add_action( 'add_meta_boxes_spfs_slide', array( $this, 'add_meta_boxes' ) );
    add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
  }

  /**
   * Slider Module scripts.
   *
   * Hooked into 'wp_enqueue_scripts' action.
   */
  public function enqueue_scripts() {
    if ( is_front_page() || SPFS::get_instance()->page_has_shortcode( 'spfs_slider' ) ) {
      wp_register_style( 'spfs-slider-style', SPFS_URL . 'assets/css/slider.min.css' );
      wp_register_script( 'spfs-slider-init-script', SPFS_URL . 'assets/js/slider-init.min.js', array( 'jquery' ) );
	    wp_register_script( 'spfs-slider-script', SPFS_URL . 'assets/js/slider.min.js', array( 'jquery' ), false, true );
      wp_enqueue_style( 'spfs-slider-style' );
		  wp_enqueue_script( 'spfs-slider-init-script' );
		  wp_enqueue_script( 'spfs-slider-script' );
    }
  }

  /**
   * Slider Module shortcode.
   *
   * Hooked into 'spfs_slider' action.
   *
   * @return string $slider
   */
  public function shortcode() {
    ob_start();
	  $this->template();
	  $slider = ob_get_clean();
    
    return $slider;
  }

  /**
   * Register slide post type.
   *
   * Hooked into 'init' action.
   */
  public function register_slide_post_type() {
    $labels = array(
		  'name'              => _x( 'Slides', 'post type general name', 'service-pack-for-storefront' ),
		  'singular_name'     => _x( 'Slide', 'post type singular name', 'service-pack-for-storefront' ),
		  'add_new'           => __( 'Add New Slide', 'service-pack-for-storefront' ),
	  	'add_new_item'      => __( 'Add New Slide', 'service-pack-for-storefront' ),
		  'edit_item'         => __( 'Edit Slide', 'service-pack-for-storefront' ),
		  'new_item'          => __( 'New Slide', 'service-pack-for-storefront' ),
		  'view_item'         => __( 'View Slide', 'service-pack-for-storefront' ),
		  'search_items'      => __( 'Search Slides', 'service-pack-for-storefront' ),
		  'not_found'         => __( 'Slide', 'service-pack-for-storefront' ),
		  'not_found_in_trash'=> __( 'Slide', 'service-pack-for-storefront' ),
		  'parent_item_colon' => __( 'Slide', 'service-pack-for-storefront' ),
		  'menu_name'         => __( 'Slides', 'service-pack-for-storefront' )
	  );
    $taxonomies = array();
    $supports = array( 'title', 'thumbnail' );
    $post_type_args = array(
		  'labels'            => $labels,
		  'singular_label'    => __( 'Slide', 'service-pack-for-storefront' ),
		  'public'            => true,
		  'show_ui'           => true,
		  'publicly_queryable'=> true,
		  'query_var'         => true,
		  'capability_type'   => 'post',
		  'has_archive'       => false,
		  'hierarchical'      => false,
		  'rewrite'           => array( 'slug' => 'spfs_slide', 'with_front' => false ),
		  'supports'          => $supports,
		  'menu_position'     => 27,
		  'menu_icon'         => 'dashicons-images-alt',
		  'taxonomies'        => $taxonomies
	  );
    register_post_type( 'spfs_slide', $post_type_args );
  }

  /**
   * Add admin meta box.
   *
   * Hooked into 'add_meta_boxes_spfs_slide' action.
   */
  public function add_meta_boxes() {
    foreach ( $this->slide_link_box['page'] as $page ) {
      add_meta_box(
        $this->slide_link_box['id'],
			  $this->slide_link_box['title'],
			  array( $this, 'meta_box' ),
			  $page,
			  'normal',
			  'default'
		  );
    }
  }

  /**
   * Meta box template.
   *
   * @param object $post
   */
  public function meta_box( $post )  {
    echo '<table class="form-table">';
    
    foreach ( $this->slide_link_box['fields'] as $field ) {
      $meta = get_post_meta( $post->ID, $field['id'], true );
      
      echo '<tr>';
      echo '<th style="width:20%"><label for="' . esc_attr( $field['id'] ) . '">' . esc_html( $field['name'] ) . '</label></th>';
      echo '<td class="field_type_' . esc_attr( str_replace( ' ', '_', $field['type'] ) ) . '">';

      if ( 'text' === $field['type'] ) {
				echo '<input type="text" name="' . esc_attr( $field['id'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $meta ) . '" size="30" style="width:97%" /><br />' . esc_html( $field['desc'] );
		  }
      echo '</td>';
      echo '</tr>';
	  }
    echo '</table>';
    
    wp_nonce_field( 'spfs_slide_link_box_name', 'security' );
  }

  /**
   * Save slider.
   *
   * Hooked into 'save_post' action.
   *
   * @param int $post_id
   */
  public function save( $post_id ) {
		if ( isset( $_POST['spfs_slide_link_box_nonce'] ) && ! wp_verify_nonce( $_POST['spfs_slide_link_box_nonce'], 'security' ) ) {
		  return $post_id;
	  }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		  return $post_id;
	  }
    if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {
		  if ( ! current_user_can( 'edit_page', $post_id ) ) {
			  return $post_id;
		  }
	  }
    elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
		  return $post_id;
	  }
    foreach ( $this->slide_link_box['fields'] as $field ) {
		  $old = get_post_meta( $post_id, $field['id'], true );
		  $new = isset( $_POST[ $field['id'] ] ) ? sanitize_text_field( $_POST[ $field['id'] ] ) : '';
      if ( $new && $new != $old ) {
				if ( 'date' == $field['type'] ) {
					$new = format_date( $new );
					update_post_meta( $post_id, $field['id'], $new );
				}
        else {
					if ( is_string( $new ) ) {
						$new = $new;
					}
					update_post_meta( $post_id, $field['id'], $new );
				}
			}
      elseif ( '' == $new && $old ) {
			  delete_post_meta( $post_id, $field['id'], $old );
		  }
	  } 
  }

  /**
   * Slider template.
   *
   * Hooked into 'woocommerce_before_main_content' action.
   */
  public function template() {
    if ( ! is_front_page() && ! SPFS::get_instance()->page_has_shortcode( 'spfs_slider' ) ) {
      return;
    }
    $args = array(
		  'post_type'      => 'spfs_slide',
		  'posts_per_page' => 5
	  );  
    $query = new WP_Query( $args );
    
    if ( $query->have_posts() ) {
      echo '<div class="flexslider">';
      echo '<ul class="slides">';
      
      while ( $query->have_posts() ) {
        $query->the_post();
        echo '<li>';
        
        if ( '' != get_post_meta( get_the_id(), 'spfs_slide_url', true) ) {
          echo '<a href="' . esc_url( get_post_meta( get_the_id(), 'spfs_slide_url', true ) ) . '">';
			  }
        echo the_post_thumbnail();
        
        if ( '' != get_post_meta( get_the_id(), 'spfs_slide_url', true ) ) {
          echo '</a>';
        }
        echo '</li>';
      }
      echo '</ul>';
      echo '</div>';
	  }
	  wp_reset_postdata();
  }
}
