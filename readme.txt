=== TablePress Extension: Chartist ===
Contributors: PerS
Donate link: http://soderlind.no/donate/
Tags: tablepress, table, chart
Requires at least: 3.9
Tested up to: 4.0
Stable tag: trunk
License: GPLv2 or later

Extension for TablePress to create a responsive chart based on the data in your TablePress table

== Description ==

Using [Chartist.js](http://gionkunz.github.io/chartist-js/), this TablePress extension  creates a responsive chart based on the data in your TablePress table

= Use =

Add the `chartist=true` parameter to your TablePress shortcode, eg: `[table id=1 chartist=true /]`

Optional parameters:

* Hide/show the table: `chartist_table_hide=true` (default:false)
* Show/hide chart line: `chartist_showline=true`(default:true)
* Show/hide show chart area: `chartist_showarea=false` (default:false)
* Set chart y low: `chartist_low=0` (default: table low)
* Set chart y high: `chartist_high=10` (default: table high)
* Enable/disable smooth line: `chartist_linesmooth=true` (default:true)
* Enable/disable line points: `chartist_showpoint=true` (default:true)
* Set chart aspect ratio: `chartist_aspect_ratio=3:4` (default:3:4) Alternatives: 1, 15:16, 8:9, 5:6, 4:5, 3:4, 2:3, 5:8, 1:1.618, 3:5, 9:16, 8:15, 1:2, 2:5, 3:8, 1:3 or 1:4

= custom.css =

If you'd like to overide [the default style](http://gionkunz.github.io/chartist-js/getting-started.html#the-sass-way), you can add a `custom.css` in the plugin directory. It will be loaded after `libdist/chartist.min.css`


== Installation ==

1. Copy the 'tablepress_chartist' folder into your plugins folder
1. Activate the plugin via the Plugins admin page


== Screenshots ==

1. `[table id=1 chartist=true chartist_table_hide=true /]`
2. `[table id=1 chartist=true chartist_table_hide=true chartist_showarea=true /]`
3. `[table id=1 chartist=true chartist_table_hide=true chartist_showarea=true chartist_linesmooth=false/]`
4. `[table id=1 chartist=true chartist_table_hide=true chartist_linesmooth=false chartist_showpoint=false/]`
5. `[table id=1 chartist=true chartist_table_hide=true chartist_showarea=true chartist_showline=false chartist_showpoint=false/]`
6. `[table id=1 chartist=true chartist_table_hide=true chartist_low=0 chartist_high=8 /]`

== Changelog ==
= 0.3 =
* 0.3 Added support for custom.css
= 0.2 =
* Added more optional parameters
= 0.1 =
* Initial release