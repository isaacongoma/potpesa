
// Register Custom Post Type
function potpesa_payment() {

	$labels = array(
		'name'                  => _x( 'Pot Payments', 'Post Type General Name', 'potpesa' ),
		'singular_name'         => _x( 'Pot Payment', 'Post Type Singular Name', 'potpesa' ),
		'menu_name'             => __( 'Pot Payments', 'potpesa' ),
		'name_admin_bar'        => __( 'Pot Payment', 'potpesa' ),
		'archives'              => __( 'Payment Archives', 'potpesa' ),
		'attributes'            => __( 'Payment Attributes', 'potpesa' ),
		'parent_item_colon'     => __( 'Parent Payment:', 'potpesa' ),
		'all_items'             => __( 'All Payments', 'potpesa' ),
		'add_new_item'          => __( 'Add New Payment', 'potpesa' ),
		'add_new'               => __( 'Add New', 'potpesa' ),
		'new_item'              => __( 'New Payment', 'potpesa' ),
		'edit_item'             => __( 'Edit Payment', 'potpesa' ),
		'update_item'           => __( 'Update Payment', 'potpesa' ),
		'view_item'             => __( 'View Payment', 'potpesa' ),
		'view_items'            => __( 'View Payments', 'potpesa' ),
		'search_items'          => __( 'Search Payment', 'potpesa' ),
		'not_found'             => __( 'Not found', 'potpesa' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'potpesa' ),
		'featured_image'        => __( 'Featured Image', 'potpesa' ),
		'set_featured_image'    => __( 'Set featured image', 'potpesa' ),
		'remove_featured_image' => __( 'Remove featured image', 'potpesa' ),
		'use_featured_image'    => __( 'Use as featured image', 'potpesa' ),
		'insert_into_item'      => __( 'Insert into payment', 'potpesa' ),
		'uploaded_to_this_item' => __( 'Uploaded to this payment', 'potpesa' ),
		'items_list'            => __( 'Items list', 'potpesa' ),
		'items_list_navigation' => __( 'Items list navigation', 'potpesa' ),
		'filter_items_list'     => __( 'Filter payments list', 'potpesa' ),
	);
	$args = array(
		'label'                 => __( 'Pot Payment', 'potpesa' ),
		'description'           => __( 'Payments for SwahilipotHub Members', 'potpesa' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor', 'trackbacks' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 20,
		'menu_icon'             => 'dashicons-money',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => false,
		'exclude_from_search'   => true,
		'publicly_queryable'    => false,
		'capability_type'       => 'page',
		'show_in_rest'          => false,
	);
	register_post_type( 'potpesa_payment', $args );

}
add_action( 'init', 'potpesa_payment', 0 );
