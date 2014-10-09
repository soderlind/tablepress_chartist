=== TablePress Extension: Chartist ===
Contributors: PerS
Donate link: http://soderlind.no/donate/
Tags: tablepress, table, chart
Requires at least: 3.9
Tested up to: 4.0
Stable tag: trunk
License: GPLv2 or later

Extension for TablePress to create a responsive chart based on the data in a TablePress table.

== Description ==

Using [Chartist.js](http://gionkunz.github.io/chartist-js/), this TablePress extension creates a responsive chart based on the data in a TablePress table.

= Use =

Add the `chartist=true` parameter to your TablePress shortcode, e.g.: `[table id=1 chartist=true /]`.

Optional parameters:

* Show/hide chart line: `chartist_showline=true` (default: true)
* Show/hide show chart area: `chartist_showarea=false` (default: false)
* Set chart y low: `chartist_low=0` (default: table low)
* Set chart y high: `chartist_high=10` (default: table high)
* Enable/disable smooth line: `chartist_linesmooth=true` (default: true)
* Enable/disable line points: `chartist_showpoint=true` (default: true)
* Set chart aspect ratio: `chartist_aspect_ratio=3:4` (default: 3:4) Alternatives: 1, 15:16, 8:9, 5:6, 4:5, 3:4, 2:3, 5:8, 1:1.618, 3:5, 9:16, 8:15, 1:2, 2:5, 3:8, 1:3, or 1:4

= CSS customizations =

If you'd like to overide [the default style](http://gionkunz.github.io/chartist-js/getting-started.html#the-sass-way), you can add a `tablepress-chartist-custom.css` in `wp-content` directory. It will be loaded after the Extension's default CSS file `libdist/chartist.min.css`.

== Installation ==

1. Install the TablePress plugin.
1. Copy the `tablepress_chartist` folder into your `wp-content/plugins` folder.
1. Activate the plugin via the "Plugins" admin screen in WordPress.

== Screenshots ==

1. `[table id=1 chartist=true /]`
2. `[table id=1 chartist=true chartist_showarea=true /]`
3. `[table id=1 chartist=true chartist_showarea=true chartist_linesmooth=false /]`
4. `[table id=1 chartist=true chartist_linesmooth=false chartist_showpoint=false /]`
5. `[table id=1 chartist=true chartist_showarea=true chartist_showline=false chartist_showpoint=false /]`
6. `[table id=1 chartist=true chartist_low=0 chartist_high=8 /]`

== Changelog ==
= 0.3 =
* 0.3 Added support for CSS customizations
= 0.2 =
* Added more optional parameters
= 0.1 =
* Initial release
