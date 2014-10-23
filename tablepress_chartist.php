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
	 * Optional parameters for the Shortcode.
	 *
	 * @since 0.2
	 * @var array
	 */
	protected static $option_atts = array(
		'low',
		'high',
		'showLine',
		'showArea',
		'showPoint',
		'lineSmooth',
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
	 * Store table array
	 *
	 * @since 0.6
	 * @var array
	 */
	protected static $table;

	/**
	 * Store render_options array
	 *
	 * @since 0.6
	 * @var array
	 */
	protected static $render_options;

	/**
	 * chart number
	 *
	 * @since 0.6
	 * @var integer
	 */
	protected static $num = 1;

	/**
	 * Register necessary plugin filter hooks.
	 *
	 * @since 0.1
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__,'enqueue_scripts_styles' ) );
		add_filter( 'tablepress_table_render_data', array( __CLASS__,'get_table' ), 10, 3 );
		add_filter( 'tablepress_shortcode_table_default_shortcode_atts', array( __CLASS__, 'shortcode_attributes' ) );
		add_filter( 'tablepress_table_output', array( __CLASS__, 'output_chart' ), 10, 3 );
		add_shortcode('table-chart', array( __CLASS__, 'table_chart'));
	}

	/**
	 * Save table and options arrays
	 *
	 * @since 0.6
	 * 
	 * @param  array $table          table array
	 * @param  array $orig_table     original table array
	 * @param  array $render_options options array
	 * @return array                 table array
	 */
	public static function get_table($table, $orig_table, $render_options ) {
		self::$table = $table;
		self::$render_options = $render_options;
		return $table;
	}

	/**
	 * [table-chart] shortcode
	 *
	 * @since 0.6
	 * 
	 * @param  array $atts shortcode attributes
	 * @return string      The generated HTML and JavaScript code for the chart.
	 */
	public static function table_chart($atts){

		//cast string boolean to boolean ('false' to false)
		if (isset($atts['showline']))   $atts['showline'] = 'true' === $atts['showline'];
		if (isset($atts['showarea']))   $atts['showarea'] = 'true' === $atts['showarea'];
		if (isset($atts['showpoint']))  $atts['showpoint'] = 'true' === $atts['showpoint'];
		if (isset($atts['linesmooth'])) $atts['linesmooth'] = 'true' === $atts['linesmooth'];

		$options = wp_parse_args( 
			shortcode_atts(array(
				'chartist'     => true,
				'aspect_ratio' => '3:4',
				'low'          => '',
				'high'         => '',
				'width'        => '',
				'height'       => '',
				'chart'        => 'line',
				'showline'     => true,
				'showarea'     => false,
				'showpoint'    => true,
				'linesmooth'   => true,
				'shortcode'    => 'table-chart'
			), $atts)
			, self::$render_options
		);

		return self::output_chart ('', self::$table, $options);
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
		wp_enqueue_script( 'chartist-js', $dir . 'libdist/chartist.min.js', array('jquery'), self::$version, true );
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

		$chart_id = sprintf("%s_%s",str_replace('-', '_', $render_options['html_id']), self::$num);
		self::$num = self::$num + 1;
		$json_chart_option = '';

		$option_prefix = 'chartist_';
		if (isset($render_options['shortcode']) && 'table-chart' === $render_options['shortcode']) {
			$option_prefix = '';
		}

		switch (strtolower($render_options[$option_prefix . 'chart'])) {
			case 'bar':
				$chart = 'Bar';
				break;
			case 'pie':
				$chart = 'Pie';
				$json_chart_option = "labelInterpolationFnc: function(value) {return value[0]}";
				break;
			case 'percent':
				$chart = 'Pie';
				$json_chart_option = "labelInterpolationFnc: function(value) { return Math.round(value / data_{$chart_id}.series.reduce(sum_{$chart_id}) * 100) + '%'; }";
				break;
			default:
				$chart = 'Line';
				break;
		}

		//convert table values to numeric
		foreach ( $table['data'] as $row_idx => $row ) {
		  foreach ( $row as $col_idx => $cell ) {
		    if ( is_numeric( $cell ) ) {
		      if ( $cell == (int) $cell ) {
		        $table['data'][ $row_idx ][ $col_idx ] = (int) $cell;
		      } else {
		        $table['data'][ $row_idx ][ $col_idx ] = (float) $cell;
		      }
		    }
		  }
		}



		if ( $render_options['table_head'] ) {
			$head_row = array_shift( $table['data'] );
			$json_labels = json_encode( $head_row );
			$json_data = json_encode( ('Pie' !== $chart) ? $table['data'] : array_shift( $table['data'] )); // if 'Pie' only use the first row
			if ('percent' === strtolower($render_options[$option_prefix . 'chart'])) {
				$json_chart_template = "series: %s";
				$json_chart_data = sprintf( $json_chart_template,  $json_data );
			} else {
				$json_chart_template = "labels: %s, series: %s";
				$json_chart_data = sprintf( $json_chart_template, $json_labels, $json_data );
			}
		} else {
			$json_data = json_encode( ('Pie' !== $chart) ? $table['data'] : array_shift( $table['data'] )); // if 'Pie' only use the first row
			$json_chart_template = "series: %s";
			$json_chart_data = sprintf( $json_chart_template, $json_data );
		}

		if ('' === $json_chart_option) {
			foreach ( self::$option_atts as $option ) {
				$key = $option_prefix . strtolower( $option );
				if ( isset( $render_options[ $key ] ) ) {
					$json_chart_option .= sprintf(
						'%s %s: %s',
						( '' !== $json_chart_option ) ? ',' : '',
						$option,
						var_export( $render_options[ $key ], true )
					);
				}
			}
		}
		$chartist_script = <<<JS
<script type="text/javascript">
jQuery(document).ready(function(){
	var data_{$chart_id} = {
		{$json_chart_data}
	};
	var options_{$chart_id} = {
		{$json_chart_option}
	}
	var sum_{$chart_id} = function(a, b) { return a + b };
	new Chartist.{$chart}('#chartist_{$chart_id}', data_{$chart_id}, options_{$chart_id});
});
</script>
JS;

		$chartist_divtag = sprintf(
			'<div id="%s" class="ct-chart %s"></div>',
			"chartist_{$chart_id}",
			( array_key_exists( $render_options[$option_prefix . 'aspect_ratio'], self::$aspect_ratios ) ) ? self::$aspect_ratios[ $render_options[$option_prefix . 'aspect_ratio'] ]: 'ct-perfect-fourth'
		);

		return $chartist_divtag . $chartist_script;
	}

}
