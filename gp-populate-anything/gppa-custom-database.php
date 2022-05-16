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
 *
 *
 * Video
 * https://www.loom.com/share/f9bf62e358dd4c54ab346998c5f3b516
 *
 *
 *
 */
class GPPA_Object_Type_Database_Testing extends GPPA_Object_Type_Database {
	const DB_NAME = 'testing';

	const DB_USER = DB_USER;

	const DB_PASSWORD = DB_PASSWORD;

	const DB_HOST = DB_HOST;

	public function get_label() {
		return esc_html__( 'Database: ', 'gp-populate-anything' ) . self::DB_NAME;
	}

	public function get_db() {
		return new wpdb( self::DB_USER, self::DB_PASSWORD, self::DB_NAME, self::DB_HOST );
	}
}

add_action('init', function() {
	if ( class_exists( 'GP_Populate_Anything' ) ) {
		gp_populate_anything()->register_object_type( 'database-testing', 'GPPA_Object_Type_Database_Testing' );
	}
});
