<?php
/**
 * Gravity Perks // Populate Anything // Custom Database
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * By default, Populate Anything will only show the database that the current WordPress
 * installation is on.
 *
 * The following snippet allows registering additional an additional database with custom credentials.
 *
 * You can add multiple databases by adjusting the class names and adding additional
 * gp_populate_anything()->register_object_type() calls.
 */
class GPPA_Object_Type_Database_Testing extends GPPA_Object_Type_Database {
	private const DB_NAME = 'testing';

	private const DB_USER = DB_USER;

	private const DB_PASSWORD = DB_PASSWORD;

	private const DB_HOST = DB_HOST;

	public function get_label() {
		return esc_html__( 'Database: ', 'gp-populate-anything' ) . self::DB_NAME;
	}

	public function get_db() {
		return new wpdb( self::DB_USER, self::DB_PASSWORD, self::DB_NAME, self::DB_HOST );
	}

	public function __construct( $id ) {
		parent::__construct( $id );

		add_action( sprintf( 'gppa_pre_object_type_query_%s', $id ), array( $this, 'add_filter_hooks' ) );
	}

	public function add_filter_hooks() {
		add_filter( sprintf( 'gppa_object_type_%s_filter', $this->id ), array( $this, 'process_filter_default' ), 10, 4 );
	}
}

add_action('init', function() {
	gp_populate_anything()->register_object_type( 'database-testing', 'GPPA_Object_Type_Database_Testing' );
});
