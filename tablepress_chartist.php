<?php
/*
Plugin Name: TablePress Extension: Chartist
Plugin URI: http://soderlind.no
Description: Extension for TablePress to filter table rows by using additional Shortcode parameters
Version: 1.0
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
	protected static $version = '0.1';
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
		wp_enqueue_script( 'chartist-js', $dir . 'libdist/chartist.min.js', array(), self::$version, false );
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
		$default_atts['chartist_table_display'] = true;
		$default_atts['chartist_aspect_ratio'] = '3:4';
		$default_atts['filter_columns'] = 'all';
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

		if ( false !== ( $json_chart_data = get_transient(self::$cache_prefix . $table['id'])) {
		$chartist_script = <<<EOSCRIPT
<script type="text/javascript">
jQuery( document ).ready( function(){
	Chartist.Line('.ct-chart', {$json_chart_data});
});
</script>
EOSCRIPT;

		if ( ! empty( $render_options['chartist_table_display'] ) &&  false ===  $render_options['chartist_table_display'] )
			return  "<div class='ct-chart ct-perfect-fourth'></div>" . $chartist_script;

		return "<div class='ct-chart ct-perfect-fourth'></div>" . $chartist_script . $output ;
		} else {
			return $output;
		}
	}
}
