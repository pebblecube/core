# Pebblecube
Pebblecube is video game analytics library, includes: analytics, achievements, scores and much more. This repo contains all the files necessary to run your own instance of Pebblecube.

## Features
Below some of Pebblecube's features:

* Multi project management and Multi users
* Custom events with multiple data types (integer, float, string, boolean and arrays)
* Custom functions and Constants definition
* Geo data
* Multiple scoreboard and achievement management
* Encrypted API's communications
* ...

## Requirements
Below are the requirements to run your own Pebblecube:

* PHP 5.3 or higher
* MongoDb 1.8.1 or higher
* Python 2.6 or higher
* Memecached 1.4 or higher

## Basic architecture
Pebblecube consists of 3 main parts: Website, API and Cron Jobs. The website code is in the */www* folder and contains all the pages that let users manage their projects plus all the API documentation. The API documentation has been built using the *Get Simple CMS* which has saved all the data to static xml files. APIs methods are in the */api* folder.

All the data is stored in a MongoDb database, but all the statistics are *crunched* by a couple of python scripts that run some map reduces and queries the maxmind ip database for geo data.

Pebblecube was built to run on a Amazon EC2 Image, you can find a list of commands for configure an Amazon Ubuntu Maverick image in the *Amazon.md* file. All files by default are stored in the */www/files* folder, it is possible to activate the S3 option and save files in your personal S3 bucket (this feature is not 100% complete).

## The code
Below the list of folders with a quick description

* api: main api folder
    * methods: main api methids folder, one folder for every api section
* lib: the common core shared between apis and www
    * business: business login methods
	* common: common model
	* mrs: MongoDb map reduces
	* py: python scripts
	* util: useful scripts
* www: website folder
    * devs: developer's personal pages
	* docs: Get Simple CMS folder
	* files: files storage
	* gui: graphics elements folder
	* prj: project managements folder
	* static_pages: self-explanatory
	* stats: project's stats pages

## Configuration
Pebblecube's configuration is pretty simple, all the files are in the root of the lib folder.

* config.php: database and Memcached configuration.
* config_paths.php: file repository paths
* config_s3.php: config paths in case S3 mode is enable
* config_urls.php: url paths

Once you have changed these files you have to configure the scheduled Cron Jobs that execute all the map reduces: */lib/py/daily_mrs.py* and */lib/py/geofind.py*. If Crons are not configured users won't see any statistics since the data displayed is only the *reduced* data. Regarding MongoDb all the collections will be created automatically, just create a database and change the configuration in *config.php*.

In addition is necessary to give write access to the */www/files* and */www/docs* folders.

For more documentation refer to project documentation at *http://yourhost/docs*

## License
Pebblecube is released under the **GNU General Public License, version 2**. For more info visit 
[http://www.gnu.org/licenses/gpl-2.0.html](http://www.gnu.org/licenses/gpl-2.0.html "http://www.gnu.org/licenses/gpl-2.0.html")

## Open Source
Pebblecube includes a lot of scripts and projects from the open source community, please refer to their website for licensing and support; Projects included are:

* Get simple CMS [http://get-simple.info/](http://get-simple.info/) distribuited under [GPLv3 License](http://www.gnu.org/licenses/gpl-3.0.html)
* Highcharts [http://shop.highsoft.com/highcharts.html](http://shop.highsoft.com/highcharts.html) distribuited under [Creative Commons Attribution-NonCommercial 3.0 License](http://creativecommons.org/licenses/by-nc/3.0/).
* Fancybox [http://fancybox.net/](http://fancybox.net/) licensed under both [MIT](http://www.opensource.org/licenses/mit-license.php) and [GPL licenses](http://www.gnu.org/licenses/gpl.html)
* jQuery [http://jquery.com/](http://jquery.com/), licensed under both [MIT](http://www.opensource.org/licenses/mit-license.php) and [GPL licenses](http://www.gnu.org/licenses/gpl.html)
* jQuery Color picker, Stefan Petre [http://www.eyecon.ro/](http://www.eyecon.ro/), licensed under both [MIT](http://www.opensource.org/licenses/mit-license.php) and [GPL licenses](http://www.gnu.org/licenses/gpl.html)
* Canvas tutorial script by Bill Mill [http://billmill.org/static/canvastutorial](http://billmill.org/static/canvastutorial)
* Adapt.js [http://adapt.960.gs/](http://adapt.960.gs/), licensed under both [MIT](http://www.opensource.org/licenses/mit-license.php) and [GPL licenses](http://www.gnu.org/licenses/gpl.html)
* jQuery validation plug-in [http://docs.jquery.com/Plugins/Validation](http://docs.jquery.com/Plugins/Validation), licensed under both [MIT](http://www.opensource.org/licenses/mit-license.php) and [GPL licenses](http://www.gnu.org/licenses/gpl.html)
* EvalMath by Miles Kaufmann [http://www.twmagic.com/](http://www.twmagic.com/)

If we forgot to put you in the list please contact us.

### The Authors

Giovanni Ferron <giovanni@pebblecube.com> and Richard Adem <richard@pebblecube.com>
