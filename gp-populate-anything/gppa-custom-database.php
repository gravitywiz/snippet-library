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
}

add_action('init', function() {
	gp_populate_anything()->register_object_type( 'database-testing', 'GPPA_Object_Type_Database_Testing' );
});
