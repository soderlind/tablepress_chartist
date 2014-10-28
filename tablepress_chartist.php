<?php
/*
Plugin Name: TablePress Extension: Chartist
Plugin URI: https://github.com/soderlind/tablepress_chartist
Description: Extension for TablePress to create a responsive chart based on the data in a TablePress table.
Version: 0.6
Author: Per Soderlind
Author URI: http://soderlind.no/
*/

// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

/**
 * Initialize the TablePress Chartist Extension.
 */
add_action( 'tablepress_run', array( 'TablePress_Chartist', 'init' ) );

/**
 * Class that contains the TablePress Chartist Extension functionality.
 * @author Per Soderlind, Tobias Bäthge
 * @since 0.1
 */
class TablePress_Chartist {

	/**
	 * Version number of the Extension.
	 *
	 * @since 0.1
	 * @var string
	 */
	protected static $version = '0.6';

	/**
	 * Available Shortcode attributes, without the `chartist_` prefix.
	 *
	 * @since 0.2
	 * @var array
	 */
	protected static $shortcode_attributes = array(
		'low'          => '',
		'high'         => '',
		'width'        => '',
		'height'       => '',
		'chart'        => 'line',
		'showline'     => true,
		'showarea'     => false,
		'showpoint'    => true,
		'linesmooth'   => true,
		'aspect_ratio' => '3:4',
	);

	/**
	 * Mapping of some Shortcode parameters to their ChartistJS equivalent.
	 *
	 * @since 0.2
	 * @var array
	 */
	protected static $attribute_to_js_mapping = array(
		'low'        => 'low',
		'high'       => 'high',
		'showline'   => 'showLine',
		'showarea'   => 'showArea',
		'showpoint'  => 'showPoint',
		'linesmooth' => 'lineSmooth',
	);

	/**
	 * Available aspect ratios for the chart.
	 *
	 * @since 0.2
	 * @var array
	 */
	protected static $aspect_ratios = array(
		'1'       => 'ct-square',
		'15:16'   => 'ct-minor-second',
		'8:9'     => 'ct-major-second',
		'5:6'     => 'ct-minor-third',
		'4:5'     => 'ct-major-third',
		'3:4'     => 'ct-perfect-fourth',
		'2:3'     => 'ct-perfect-fifth',
		'5:8'     => 'ct-minor-sixth',
		'1:1.618' => 'ct-golden-section',
		'3:5'     => 'ct-major-sixth',
		'9:16'    => 'ct-minor-seventh',
		'8:15'    => 'ct-major-seventh',
		'1:2'     => 'ct-octave',
		'2:5'     => 'ct-major-tenth',
		'3:8'     => 'ct-major-eleventh',
		'1:3'     => 'ct-major-twelfth',
		'1:4'     => 'ct-double-octave',
	);

	/**
	 * Register necessary plugin filter hooks and the [table-chart] Shortcode.
	 *
	 * @since 0.1
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__,'enqueue_scripts_styles' ) );
		add_filter( 'tablepress_shortcode_table_default_shortcode_atts', array( __CLASS__, 'register_shortcode_attributes' ) );
		add_filter( 'tablepress_table_output', array( __CLASS__, 'generate_chart' ), 10, 3 );
		add_shortcode( 'table-chart', array( __CLASS__, 'handle_table_chart_shortcode' ) );
	}

	/**
	 * Handle Shortcode [table-chart id=<ID> /] in `the_content()`.
	 *
	 * @since 0.6
	 *
	 * @param array $shortcode_atts List of attributes that where included in the Shortcode.
	 * @return string Generated HTML code for the chart with the ID <ID>.
	 */
	public static function handle_table_chart_shortcode( $shortcode_atts ){
		// Generate the attribute query array for the template tag function.
		$table_query = array(
			'chartist' => true,
		);
		// Pass all parameters to the template tag parameters.
		foreach ( (array) $shortcode_atts as $attribute => $value ) {
			// Prepend 'chartist_' to all Shortcode attributes that the Extension understands.
			if ( isset( self::$shortcode_attributes[ $attribute ] ) ) {
				$attribute = 'chartist_' . $attribute;
			}
			$table_query[ $attribute ] = $value;
		}

		return tablepress_get_table( $table_query );
	}

	/**
	 * Load Chartist JavaScript and CSS files.
	 *
	 * @TODO: Only load the JavaScript file if there is a chart on the page.
	 *
	 * @since 0.1
	 */
	public static function enqueue_scripts_styles () {
		$dir = plugin_dir_url( __FILE__ );
		wp_enqueue_script( 'chartist-js', $dir . 'libdist/chartist.min.js', array( 'jquery' ), self::$version, true );
		wp_enqueue_style( 'chartist-css', $dir . 'libdist/chartist.min.css', array(), self::$version );
		if ( file_exists( WP_CONTENT_DIR . '/tablepress-chartist-custom.css' ) ) {
			wp_enqueue_style( 'chartist-custom-css', content_url( 'tablepress-chartist-custom.css' ), array( 'chartist-css' ), self::$version );
		}
	}

	/**
	 * Add the Extension's parameters as valid [table /] Shortcode attributes.
	 *
	 * @since 0.1
	 *
	 * @param array $default_atts Default attributes for the TablePress [table /] Shortcode.
	 * @return array Extended attributes for the Shortcode.
	 */
	public static function register_shortcode_attributes( $default_atts ) {
		$default_atts['chartist'] = false;
		foreach ( self::$shortcode_attributes as $attribute => $value ) {
			$default_atts[ 'chartist_' . $attribute ] = $value;
		}
		return $default_atts;
	}

	/**
	 * Generate the HTML and JavaScript code for a Chartist chart, based on the data of the given table.
	 *
	 * @since 0.1
	 *
	 * @param string $output         The generated HTML for the table.
	 * @param array  $table          The current table.
	 * @param array  $render_options The render options for the table.
	 * @return string The generated HTML and JavaScript code for the chart.
	 */
	public static function generate_chart( $output, $table, $render_options ) {
		if ( ! $render_options['chartist'] ) {
			return $output;
		}

		$json_chart_options = array();

		// Determine/sanitize the chart type and add JS calculation functions.
		switch ( strtolower( $render_options[ 'chartist_chart' ] ) ) {
			case 'bar':
				$chart = 'Bar';
				break;
			case 'pie':
				$chart = 'Pie';
				$json_chart_options[] = 'labelInterpolationFnc: function( value ) { return value[0]; }';
				break;
			case 'percent':
				$chart = 'Pie';
				$json_chart_options[] = "labelInterpolationFnc: function( value ) { return Math.round( value / data.series.reduce( sum ) * 100 ) + '%'; }";
				break;
			case 'line':
			default:
				$chart = 'Line';
				break;
		}

		// Convert all numeric table cell values to numeric variables, so that they show up as numbers in the JSON encoded string, as ChartistJS requires that.
		foreach ( $table['data'] as $row_idx => $row ) {
			foreach ( $row as $col_idx => $cell ) {
				$table['data'][ $row_idx ][ $col_idx ] = self::_maybe_string_to_number( $cell );
			}
		}

		// Get labels from the first table row.
		if ( $render_options['table_head'] ) {
			$json_labels = array_shift( $table['data'] );
		}

		// Use only the first row in Pie charts.
		if ( 'Pie' === $chart ) {
			$table['data'] = array_shift( $table['data'] );
		}

		// Create JSON object for the chart data.
		$json_chart_data = array(
			'series' => $table['data'],
		);
		if ( $render_options['table_head'] && 'percent' !== $render_options[ 'chartist_chart' ] ) {
			$json_chart_data['labels'] = $json_labels;
		}
		$json_chart_data = json_encode( (object) $json_chart_data );

		// Add other chart options.
		foreach ( self::$attribute_to_js_mapping as $option_key => $option_js ) {
			$option_key = 'chartist_' . $option_key;
			if ( isset( $render_options[ $option_key ] ) ) {
				$value = self::_maybe_string_to_number( $render_options[ $option_key ] );
				$json_chart_options[] = $option_js . ': ' .	json_encode( $value );
			}
		}
		$json_chart_options = '{ ' . implode( ', ', $json_chart_options ) . ' }';

		// Sanitize the aspect ratio.
		$aspect_ratio = 'ct-perfect-fourth';
		if ( isset( self::$aspect_ratios[ $render_options[ 'chartist_aspect_ratio'] ] ) ) {
			$aspect_ratio = self::$aspect_ratios[ $render_options[ 'chartist_aspect_ratio'] ];
		}

		$chartist_script = <<<JS
<script type="text/javascript">
jQuery(document).ready(function(){
	var	data = {$json_chart_data},
		options = {$json_chart_options},
		sum = function( a, b ) { return a + b; };
	new Chartist.{$chart}( '#chartist-{$render_options['html_id']}', data, options );
});
</script>
JS;

		$chartist_divtag = sprintf(
			"<div id=\"%s\" class=\"ct-chart %s\"></div>\n",
			"chartist-{$render_options['html_id']}",
			$aspect_ratio
		);

		return $chartist_divtag . $chartist_script;
	}

	/**
	 * Convert a string to int or float, if it's a numeric string.
	 *
	 * @since 0.7
	 *
	 * @param string $string String that shall be converted to a number.
	 * @return mixed Possibly converted string.
	 */
	protected static function _maybe_string_to_number( $string ) {
		if ( ! is_numeric( $string ) ) {
			return $string;
		}

		if ( $string == (int) $string ) { // Don't do explicit === check here!
			return (int) $string;
		} else {
			return (float) $string;
		}
	}

} // class TablePress_Chartist
