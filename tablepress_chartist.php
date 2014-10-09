<?php
/*
Plugin Name: TablePress Extension: Chartist
Plugin URI: https://github.com/soderlind/tablepress_chartist
Description: Extension for TablePress to create a chart based on the data in your TablePress table.
Version: 0.2
Author: Per Soderlind
Author URI: http://soderlind.no
*/

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

/**
 * Clear the transient cache when saving a table
 */
add_action( 'tablepress_event_saved_table', array( 'TablePress_Chartist', 'clear_cache' ) );
/**
 * Init TablePress_Row_Filter
 */
add_action( 'tablepress_run', array( 'TablePress_Chartist', 'init' ) );

/**
 * Class that contains the TablePress Row Filtering functionality
 * @author Tobias Bäthge
 * @since 0.1
 */
class TablePress_Chartist{

	/**
	 * Version number
	 *
	 * @since 0.1
	 *
	 * @var string
	 */
	protected static $version = '0.2';
	/**
	 * transient cache prefix
	 *
	 * @since 0.1
	 *
	 * @var string
	 */
	protected static $cache_prefix = 'tablepress_chartist_cache_';
	/**
	 * transient cache time
	 *
	 * @since 0.1
	 *
	 * @var int
	 */
	protected static $cache_time = 60;//43200; // 60 * 60 * 12

	/**
	 * Optional parameters
	 *
	 * @since 0.2
	 * 
	 * @var array
	 */
	protected static $option_atts = array('chartist_low','chartist_high','chartist_showLine','chartist_showArea','chartist_showPoint','chartist_lineSmooth');

	/**
	 * Available aspect ratios
	 *
	 * @since 0.2
	 * 
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
						'1:4'     => 'ct-double-octave'
					);
	/**
	 * Register necessary plugin filter hooks
	 *
	 * @since 0.1
	 */
	public static function init() {
		// load scripts and css
		add_action('wp_enqueue_scripts', array(__CLASS__,'enqueue_scripts_styles'));

		add_filter( 'tablepress_table_render_data', array( __CLASS__, 'save_chart_data' ), 9, 3 );
		add_filter( 'tablepress_table_output', array( __CLASS__, 'output_chart' ), 10, 3 );
		add_filter( 'tablepress_shortcode_table_default_shortcode_atts', array( __CLASS__, 'shortcode_attributes' ) );
	}

	/**
	 * Remove transient cache
	 *
	 * @since 0.1
	 * 
	 * @param  int $id tablepress table id
	 */
	public static function clear_cache($id) {
		delete_transient( self::$cache_prefix . $id);
	}

	/**
	 * Load chartist script and css
	 *
	 * @since 0.1
	 */
	public static function enqueue_scripts_styles () {

		$dir = plugin_dir_url( __FILE__ );
		wp_enqueue_script( 'chartist-js', $dir . 'libdist/chartist.min.js', array(), self::$version, true );
		wp_enqueue_style( 'chartist-css', $dir . 'libdist/chartist.min.css',array(),self::$version);
	}


	/**
	 * Add the Extension's parameters as valid [[table /]] Shortcode attributes
	 *
	 * @since 0.1
	 *
	 * @param array $default_atts Default attributes for the TablePress [[table /]] Shortcode
	 * @return array Extended attributes for the Shortcode
	 */
	public static function shortcode_attributes( $default_atts ) {
		$default_atts['chartist'] = '';
		$default_atts['chartist_table_hide'] = false;
		$default_atts['chartist_aspect_ratio'] = '3:4';
		$default_atts['chartist_low'] = '';
		$default_atts['chartist_high'] = '';
		$default_atts['chartist_width'] = '';
		$default_atts['chartist_height'] = '';
		$default_atts['chartist_showline'] = true;
		$default_atts['chartist_showarea'] = false;
		$default_atts['chartist_showpoint'] = true;
		$default_atts['chartist_linesmooth'] = true;

		return $default_atts;
	}

	/**
	 * Convert chart data to json and save as transient
	 *
	 * @since 0.1
	 * 
	 * @param array $table          The processed table.
	 * @param array $orig_table     The unprocessed table.
	 * @param array $render_options The render options for the table.
	 * @return array               The processed table
	 */
	public static function save_chart_data( $table, $orig_table, $render_options ) {

		if ( ! empty( $render_options['chartist'] ) &&  true ===  $render_options['chartist'] ) {

			if(!get_transient(self::$cache_prefix . $table['id'])) {
				$data = $table['data'];
				$options = $table['options'];
				if (true === $options['table_head']) {
					$json_labels = json_encode($data[0]);
					unset($data[0]);
				}
				$json_data = json_encode(array_merge($data));

				// save chartist data as transient

				$json_chart_template = (true === $options['table_head']) ? "{ labels: %s, series: %s }" : "{ series: %s }" ;
				if ( true === $options['table_head'] ) {
					$json_chart_data = sprintf($json_chart_template, $json_labels, $json_data );
				} else {
					$json_chart_data = sprintf($json_chart_template, $json_data );
				}

				set_transient(self::$cache_prefix . $table['id'], $json_chart_data, self::$cache_time);
			}
		}
		return $table;
	}

	/**
	 * [output_chart description]
	 *
	 * @since 0.1
	 * 
	 * @param string $output         The generated HTML for the table.
	 * @param array  $table          The current table.
	 * @param array  $render_options The render options for the table.
	 * @return string                 The chart and, if enabled through the shortcode, the generated HTML for the table
	 */
	public static function output_chart( $output, $table, $render_options ) {

		if ( ! empty( $render_options['chartist'] ) &&  true ===  $render_options['chartist'] ) {
			if ( false !== ( $json_chart_data = get_transient(self::$cache_prefix . $table['id']))) {
				$json_chart_option = '';
				foreach (self::$option_atts as $key) {
					if (isset($render_options[strtolower($key)])) {
						$json_chart_option .= sprintf('%s %s: %s', ('' !== $json_chart_option)? ',':''
							                               , str_replace('chartist_', '', $key)
							                               , var_export($render_options[strtolower($key)],true)
							                         );
					}
				}

				$chartist_script = <<<EOSCRIPT
<script type="text/javascript">
jQuery( document ).ready( function(){
	Chartist.Line('.ct-chart', {$json_chart_data}, {{$json_chart_option}});
});
</script>
EOSCRIPT;

				$chartist_divtag = sprintf('<div class="ct-chart %s"></div>',(array_key_exists($render_options['chartist_aspect_ratio'],self::$aspect_ratios)) ? self::$aspect_ratios[$render_options['chartist_aspect_ratio']]: 'ct-perfect-fourth');

				if ( ! empty( $render_options['chartist_table_hide'] ) &&  true ===  $render_options['chartist_table_hide'] )
					return  $chartist_divtag . $chartist_script;

				return $chartist_divtag . $chartist_script . $output ;
			} else {
				return $output;
			}
		} else {
			return $output;
		}
	}
}
