<?php
namespace App\Model;

class CaseStudy extends AppCustomPostType {
	/**
     * {@inheritdoc}
     */
    public $configuration = array(
        'public' => true,
        'supports'  => array('title', 'editor', 'thumbnail'),
        'publicly_queryable' => true,
        'rewrite'   => array(
            'slug'                => 'case-studies',
            'with_front'          => true,
        ),
        'menu_icon' => 'dashicons-awards',
    );

	function __construct()
    {
        $this->configuration['label'] = array(
            'name'                => __( 'Case Studies', 'Post Type General Name', PROJECT_KEY ),
            'singular_name'       => __( 'Case Study', 'Post Type Singular Name', PROJECT_KEY ),
        );
    }
}