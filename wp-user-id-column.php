<?php
/*
Plugin Name: WP User ID Column
Description: Adds the user ID column to the WP Users dashboard.  Sortable as well!
Author: r-a-y
Author URI: http://profiles.wordpress.org/r-a-y
*/

add_action( 'plugins_loaded', array( 'Ray_User_ID_Column', 'init' ) );

class Ray_User_ID_Column {
	/**
	 * Internal name used to register our user ID column.
	 *
	 * @var string
	 */
	public $column_name = 'id';

	/**
	 * Static init method.
	 */
	public static function init() {
		return new self();
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_head-users.php', array( $this, 'setup_hooks' ) );
	}

	/**
	 * Callback method used to setup hooks.
	 *
	 * Fired on the 'admin_head-users.php' hook so our plugin only runs inside
	 * the WP Users dashboard.
	 */
	public function setup_hooks() {
		// setup screen
		$this->setup_screen();

		// time to register some hooks!
		add_filter( "manage_{$this->screen}_columns",          array( $this, 'register_column' ), 99 );
		add_filter( "manage_{$this->screen}_sortable_columns", array( $this, 'register_sortable_column' ) );
		add_action( "manage_users_custom_column",              array( $this, 'setup_column' ),    10, 3 );

		// might as well inject some CSS while we're here!
	?>

		<style type="text/css">
		th.column-id, td.column-id {width:45px;}
		</style>

	<?php
	}

	/**
	 * Sets up the current screen needed to register the correct hooks.
	 */
	protected function setup_screen() {
		// check if we're in the network admin dashboard
		if( is_network_admin() ) {
			$this->screen = 'users-network';

		// regular admin dashboard
		} else {
			$this->screen = 'users';
		}
	}

	/**
	 * Register our 'ID' column with WP's user list table.
	 *
	 * We inject our column directly after the checkbox column.
	 *
	 * @uses Ray_User_ID_Column::array_insert()
	 *
	 * @param array $columns The currently-registered columns.
	 */
	public function register_column( $columns ) {
		$columns = self::array_insert(
			$columns,

			// add our custom column
			array( $this->column_name => __( 'ID', 'wp-userid-col' ) ),

			// position to inject our column
			1
		);

		return $columns;
	}

	/**
	 * Register our sortable column with WP's user list table.
	 *
	 * @param array $retval The currently-registered sortable columns.
	 */
	public function register_sortable_column( $retval ) {
		$retval[$this->column_name] = 'id';

		return $retval;
	}

	/**
	 * Returns the user ID to output when the column name matches our registered
	 * one.
	 *
	 * @param mixed $retval
	 * @param string $column_name The registered column name that the list table is currently on.
	 * @param int $user_id The user ID associated with the current user row.
	 */
	public function setup_column( $retval, $column_name, $user_id ) {
		if ( $this->column_name == $column_name ) {
			return $user_id;
		}

		return $retval;
	}

	/**
	 * Helper method to insert an array into an existing associative array.
	 *
	 * Props soulmerge and ragulka from StackOverflow.
	 *
	 * @link http://stackoverflow.com/a/11114956
	 *
	 * @param array $array The original array before insertion
	 * @param array $values The array to insert into the original array
	 * @param int $offset The position to add the array.
	 */
	public static function array_insert( $array, $values, $offset ) {
		return array_slice( $array, 0, $offset, true ) + $values + array_slice( $array, $offset, NULL, true );
	}
}