<?php
/*
Plugin Name: TablePress Extension: Chartist
Plugin URI: https://github.com/soderlind/tablepress_chartist
Description: Extension for TablePress to create a responsive chart based on the data in a TablePress table.
Version: 0.4
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
	protected static $version = '0.3';

	/**
	 * Optional parameters for the Shortcode.
	 *
	 * @since 0.2
	 * @var array
	 */
	protected static $option_atts = array(
		'chartist_low',
		'chartist_high',
		'chartist_showLine',
		'chartist_showArea',
		'chartist_showPoint',
		'chartist_lineSmooth',
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
	 * Register necessary plugin filter hooks.
	 *
	 * @since 0.1
	 */
	public static function init() {
		// Enqueue JavaScript and CSS files.
		add_action( 'wp_enqueue_scripts', array( __CLASS__,'enqueue_scripts_styles' ) );

		add_filter( 'tablepress_shortcode_table_default_shortcode_atts', array( __CLASS__, 'shortcode_attributes' ) );
		add_filter( 'tablepress_table_output', array( __CLASS__, 'output_chart' ), 10, 3 );
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
		wp_enqueue_script( 'chartist-js', $dir . 'libdist/chartist.min.js', array(), self::$version, true );
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
	public static function shortcode_attributes( $default_atts ) {
		$default_atts['chartist'] = false;
		$default_atts['chartist_aspect_ratio'] = '3:4';
		$default_atts['chartist_low'] = '';
		$default_atts['chartist_high'] = '';
		$default_atts['chartist_width'] = '';
		$default_atts['chartist_height'] = '';
		$default_atts['chartist_chart'] = 'line';
		$default_atts['chartist_showline'] = true;
		$default_atts['chartist_showarea'] = false;
		$default_atts['chartist_showpoint'] = true;
		$default_atts['chartist_linesmooth'] = true;

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
	public static function output_chart( $output, $table, $render_options ) {

		if ( ! $render_options['chartist'] ) {
			return $output;
		}

		if ( $render_options['table_head'] ) {
			$head_row = array_shift( $table['data'] );
			$json_labels = json_encode( $head_row );
			$json_data = json_encode( $table['data'] );
			$json_chart_template = "{ labels: %s, series: %s }";
			$json_chart_data = sprintf( $json_chart_template, $json_labels, $json_data );
		} else {
			$json_data = json_encode( $table['data'] );
			$json_chart_template = "{ series: %s }";
			$json_chart_data = sprintf( $json_chart_template, $json_data );
		}

		$chart = ('bar' === strtolower($render_options['chartist_chart'])) ? 'Bar' : 'Line';

		$json_chart_option = '';
		foreach ( self::$option_atts as $key ) {
			if ( isset( $render_options[ strtolower( $key ) ] ) ) {
				$json_chart_option .= sprintf(
					'%s %s: %s',
					( '' !== $json_chart_option ) ? ',' : '',
					str_replace( 'chartist_', '', $key ),
					var_export( $render_options[ strtolower( $key ) ], true )
				);
			}
		}

		$chartist_script = <<<JS
<script type="text/javascript">
jQuery(document).ready(function(){
	Chartist.{$chart}('#chartist-{$render_options['html_id']}', {$json_chart_data}, {{$json_chart_option}});
});
</script>
JS;

		$chartist_divtag = sprintf(
			'<div id="%s" class="ct-chart %s"></div>',
			"chartist-{$render_options['html_id']}",
			( array_key_exists( $render_options['chartist_aspect_ratio'], self::$aspect_ratios ) ) ? self::$aspect_ratios[ $render_options['chartist_aspect_ratio'] ]: 'ct-perfect-fourth'
		);

		return $chartist_divtag . $chartist_script;
	}
}
