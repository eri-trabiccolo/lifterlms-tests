<?php
/**
 * Plug llms_current_time() to allow mocking of the current time via the $llms_tests_mock_time global
 * @param    string       $type   Type of time to retrieve. Accepts 'mysql', 'timestamp', or PHP date format string (e.g. 'Y-m-d').
 * @param    int|bool     $gmt    Optional. Whether to use GMT timezone. Default false.
 * @return   int|string           Integer if $type is 'timestamp', string otherwise.
 * @since    1.2.0
 * @version  1.2.0
 */
function llms_current_time( $type, $gmt = 0 ) {
	global $llms_tests_mock_time;
	if ( ! empty( $llms_tests_mock_time ) ) {

		switch ( $type ) {
			case 'mysql':
				return date( 'Y-m-d H:i:s', $llms_tests_mock_time );
			case 'timestamp':
				return $llms_tests_mock_time;
			default:
				return date( $type, $llms_tests_mock_time );
		}

	}
	return current_time( $type, $gmt );
}

/**
 * Set the mocked current time
 * @param    mixed     $time  date time string parsable by date()
 * @return   void
 * @since    1.2.0
 * @version  1.2.0
 */
function llms_tests_mock_current_time( $time ) {
	global $llms_tests_mock_time;
	$llms_tests_mock_time = is_numeric( $time ) ? $time : strtotime( $time );
}

/**
 * Reset current time after mocking it
 * @return   void
 * @since    1.2.0
 * @version  1.2.0
 */
function llms_tests_reset_current_time() {
	global $llms_tests_mock_time;
	$llms_tests_mock_time = null;
}

/**
 * Plug core `llms_filter_input` to allow data to be mocked via the mock request test case methods.
 *
 * @param   int    $type           One of INPUT_GET, INPUT_POST, INPUT_COOKIE, INPUT_SERVER, or INPUT_ENV.
 * @param   string $variable_name  Name of a variable to get.
 * @param   int    $filter         The ID of the filter to apply.
 * @param   mixed  $options        Associative array of options or bitwise disjunction of flags. If filter accepts options, flags can be provided in "flags" field of array.
 * @return  Value of the requested variable on success, FALSE if the filter fails, or NULL if the variable_name variable is not set. If the flag FILTER_NULL_ON_FAILURE is used, it returns FALSE if the variable is not set and NULL if the filter fails.
 * @since   [version]
 * @version [version]
 */
function llms_filter_input( $type, $variable_name, $filter = FILTER_DEFAULT, $options = array() ) {

	// Get the raw data.
	switch( $type ) {

		case INPUT_POST:
			$data = $_POST;
			break;

		case INPUT_GET:
			$data = $_GET;
			break;

		case INPUT_SERVER:
			$data = $_SERVER;
			break;

		case INPUT_ENV:
			$data = $_ENV;
			break;

		case INPUT_COOKIE:
			$data = $_COOKIE;
			break;

		default:
			$data = array();

	}

	if ( isset( $data[ $variable_name ] ) ) {

		return filter_var( $data[ $variable_name ], $filter, $options );

	}

	return null;

}

/**
 * Plug llms_redirect_and_exit() to throw a redirect exception instead of redirecting and exiting
 * @param    string     $location  full URL to redirect to
 * @param    array      $options   array of options
 *                                 $status  int   HTTP status code of the redirect [default: 302]
 *                                 $safe    bool  If true, use `wp_safe_redirect()` otherwise use `wp_redirect()` [default: true]
 * @return   void
 * @since    3.19.4
 * @version  3.19.4
 */
function llms_redirect_and_exit( $location, $options = array() ) {
	$options = wp_parse_args( $options, array(
		'status' => 302,
		'safe' => true,
	) );
	throw new LLMS_Unit_Test_Exception_Redirect( $location, $options['status'], null, $options['safe'] );
}
