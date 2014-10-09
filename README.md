#TablePress Extension: Chartist

![Sample chart](assets/screenshot-1.jpg)

##About

Using [Chartist.js](http://gionkunz.github.io/chartist-js/), this [TablePress](https://tablepress.org/) extension  creates a responsive chart based on the data in your TablePress table


##Usage

Add the `chartist=true` parameter to your TablePress shortcode, eg: `[table id=1 chartist=true /]`

**Optional parameters**

* Show/hide chart line: `chartist_showline=true`(default:true)
* Show/hide show chart area: `chartist_showarea=false` (default:false)
* Set chart y low: `chartist_low=0` (default: table low)
* Set chart y high: `chartist_high=10` (default: table high)
* Enable/disable smooth line: `chartist_linesmooth=true` (default:true)
* Enable/disable line points: `chartist_showpoint=true` (default:true)
* Set chart aspect ratio: `chartist_aspect_ratio=3:4` (default:3:4) Alternatives: 1, 15:16, 8:9, 5:6, 4:5, 3:4, 2:3, 5:8, 1:1.618, 3:5, 9:16, 8:15, 1:2, 2:5, 3:8, 1:3 or 1:4

In TablePress, if table head is set, the extention will use the row as chart labels.

##custom.css

If you'd like to overide [the default style](http://gionkunz.github.io/chartist-js/getting-started.html#the-sass-way), you can add a `tablepress-chartist-custom.css` in `wp-content` directory. It will be loaded after the Extension's default CSS file `libdist/chartist.min.css`.

##Installation

**Prerequisite:** The [TablePress](https://tablepress.org/) plugin

1. Copy the 'tablepress_chartist' folder into your plugins folder
1. Activate the plugin via the Plugins admin page


##Changelog
* 0.3 Added support for custom.css
* 0.2 Added more optional parameters
* 0.1 Initial release (i.e. an early beta)

##Credits

* Gion Kunz for creating [Chartist.js](http://gionkunz.github.io/chartist-js/)
* Tobias Bäthge for creating [TablePress](https://tablepress.org/)


##Copyright and License

TablePress Extension: Chartist is copyright 2014 Per Soderlind

TablePress Extension: Chartist is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any later version.

TablePress Extension: Chartist is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the [GNU General Public License](LICENSE) for more details.

You should have received a copy of the GNU Lesser General Public License along with 23 Video content provider for EPiServer. If not, see http://www.gnu.org/licenses/.

