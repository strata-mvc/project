<?php
namespace App\Model;

class TeamMember extends AppCustomPostType {

	public $configuration = array(
        'public' => true,
        'supports'  => array('title', 'editor', 'thumbnail'),
        'publicly_queryable' => true,
        'rewrite'   => array(
            'slug'                => 'team',
            'with_front'          => true,
        ),
        'menu_icon' => 'dashicons-businessman',
    );

	function __construct()
    {
        $this->configuration['label'] = array(
            'name'                => __( 'Team Members', 'Post Type General Name', PROJECT_KEY ),
            'singular_name'       => __( 'Team Member', 'Post Type Singular Name', PROJECT_KEY ),
        );
    }
}
