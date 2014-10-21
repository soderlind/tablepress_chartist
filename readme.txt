=== TablePress Extension: Chartist ===
Contributors: PerS
Donate link: http://soderlind.no/donate/
Tags: tablepress, table, chart, responsive
Requires at least: 3.9
Tested up to: 4.0
Stable tag: 0.5
License: GPLv2 or later

Create a responsive chart based on the data in a TablePress table.

== Description ==

Using [Chartist.js](http://gionkunz.github.io/chartist-js/), this [TablePress](https://wordpress.org/plugins/tablepress/) extension creates a responsive chart based on the data in a TablePress table.

= Use =

Add the `chartist=true` parameter to your TablePress shortcode, e.g.: `[table id=1 chartist=true /]`.

Optional parameters:

* Show/hide chart line: `showline=true` (default: true)
* Show/hide show chart area: `showarea=false` (default: false)
* Set chart y low: `low=0` (default: table low)
* Set chart y high: `high=10` (default: table high)
* Enable/disable smooth line: `linesmooth=true` (default: true)
* Enable/disable line points: `showpoint=true` (default: true)
* Set chart aspect ratio: `aspect_ratio=3:4` (default: 3:4) Alternatives: 1, 15:16, 8:9, 5:6, 4:5, 3:4, 2:3, 5:8, 1:1.618, 3:5, 9:16, 8:15, 1:2, 2:5, 3:8, 1:3, or 1:4
* Select chart type: `chart=bar`(default: line) Alternatives: line, bar, pie or percent. Pie or percent will only use the first datarow. Percent will ignore the header row.

If the "Table Head Row" option is set for the table, the Extension will use the head row data for the chart labels.

= CSS customizations =

If you'd like to overide [the default style](http://gionkunz.github.io/chartist-js/getting-started.html#the-sass-way), you can add a `tablepress-chartist-custom.css` in `wp-content` directory. It will be loaded after the Extension's default CSS file `libdist/chartist.min.css`.

**Example:**
`
/**
 * SVG Shape CSS properties: http://tutorials.jenkov.com/svg/svg-and-css.html#shape-css-properties
 */

/* First line / bar is .ct-series-a, next is .ct-series-b etc. */
.ct-chart .ct-series.ct-series-a .ct-bar,
.ct-chart .ct-series.ct-series-a .ct-line,
.ct-chart .ct-series.ct-series-a .ct-point  {
	stroke: #073DA0;
}

.ct-series .ct-line, .ct-chart .ct-bar {
	fill: none;
	stroke-width: 10px;
}

.ct-chart .ct-point {
	stroke-width: 10px;
	stroke-linecap: round;
}
`

== Installation ==

Prerequisite (install first): The [TablePress](https://wordpress.org/plugins/tablepress/) plugin

1. In `Plugins->Add New`, search for `tablepress chartist`
1. Click `Install Now`
1. When the plugin is installed, activate it.

== Screenshots ==

1. `[table id=1 chartist=true /]`
2. `[table id=1 chartist=true showarea=true /]`
3. `[table id=1 chartist=true showarea=true linesmooth=false /]`
4. `[table id=1 chartist=true linesmooth=false showpoint=false /]`
5. `[table id=1 chartist=true showarea=true showline=false showpoint=false /]`
6. `[table id=1 chartist=true low=0 high=8 /]`
7. `[table id=1 chartist=true chart=bar /]`
8. `[table id=1 chartist=true chart=pie /]`
9. `[table id=1 chartist=true chart=percent /]`

== Changelog ==
= 0.5 =
* **Breaking change**: Simplified optional parameters (removed prefix `chartist_`), new optional parameters are: showline, showarea, low, high, linesmooth, showpoint and aspect_ratio. See examples in [screenshots](https://wordpress.org/plugins/tablepress-chartist/screenshots/)
* Added support for `chart=pie` and `chart=percent` 
= 0.4 =
* Added support for bar chart: `chartist_chart=bar`
= 0.3 =
* 0.3 Added support for CSS customizations
= 0.2 =
* Added more optional parameters
= 0.1 =
* Initial release
