=== WP Mantis ===
Contributors: simsmaster, rtprime
Tags: mantis, bug tracker, changelog, roadmap
Requires at least: 2.8
Tested up to: 3.2
Stable tag: 1.2.2

Extended Version of "WP Mantis Table". Allows to view Changelogs, Roadmaps and Buglists from MantisBT
in Wordpress Pages and Post.

== Description ==

**Eine Deutsche Version kann [hier](http://niklas-rother.de/projekte/wp-mantis "Deutsches Readme") gefunden werden**

Original Work by Robert Torres. His Version was not working, since he had a typo in a variable. I fixed this error and
extendet this Plugin with some new Features like Changelogs, Roadmaps etc.

This plugin for Wordpress 2.8 and above allows you to insert information from a Mantis Bug Tracker
into a wordpress page or post.  The plugin is perfect for projects which use WordPress for blogging,
but also utilize Mantis for their bug tracking/issue tracking. You can include bug lists,
changelogs and roadmaps.

After you installed the Plugin you have to do the following steps:

1. Create a user in Mantis wich represents your blog. You could name him 'wordpress'. This user needs at least Reporter
Rights due a limitation in Mantis, but you can see the botton of the page to find out, how to change this.
2. Configure the Plugin.
3. Add `[Mantis]` to the post or page where you want to appear the infomation from Mantis. See below for further instructions.

= About the Shortcode =
You must provide options in the Shortcode:

* To include a bug list use `[Mantis bugs proj_id=x]`. Replace x with the ID of the project wich you want to display.
You can find the ID in the URL of the Mantis Management Page for this project.
* To include a roadmap use `[Mantis roadmap ver_id=x]`. Replace x with the ID of the Version for that you want to display
the roadmap. You can find this ID in the URL, when you view a single Roadmap in Mantis.
* To include a changelog use `[Mantis changelog ver_id=x]`. See the instruction above this to find the ID.

When including a roadmap or a changelog you can speficify a project and version *name* instead of the ID:
`[Mantis roadmap proj_name=my_project ver_name=1.2.3]` or you can use a project ID and a version name: `[Mantis roadmap proj_id=x ver_name=y]`
**The `ver_name` Paramter is optional, if missing the full changelog/roadmap for the project will be displayed**

If you use the 'bugs' option, you can add the 'exclude_stat' or 'include_stat' parameter. After the eqal sign you can add (comma seperated)
the IDs of the statuses to exclude or include. (You can not use include and exclude at the same time, of course!)
So if you dont want to include closed and fixed bugs you would write `[Mantis bugs proj_id=1 exclude_stat=90,80]`
Here is a list of the IDs:

* 10 New
* 20 Feedback
* 30 Acknowledged
* 40 Confirmed
* 50 Assigned
* 80 Resolved
* 90 Closed

With the `limit` paramter you can limit the displayed bugs. To display the latest 5 fixed bugs you would write
`[Mantis bugs proj_id=1 include_stat=80 limit=5]`

You can change the table background color for each status and use the localized status names from your mantis installation.

= Why the Mantis User needs Reporter rights =
This plugin uses the Mantis SOAP API to get the bug information. And to access this API the user needs at least Reporter
right. To change this you can create a 'mc_config_inc.php' file in your '/api/soap' directory with the following
content:
`<?php $g_mc_readonly_access_level_threshold = VIEWER; ?>`
Now Viewer rights are suffiant for the user.

= Further plans: (eventually) =
* Only include the CSS/JavaScript if the `[Mantis]` shortcode is in the content

= Translations =
The original plugin is in english, but you can translate in your langguage, since the pot file is included. If I should include
your translation, email me: info at niklas-rother dot de. So far the following translations are included:

* German/Deutsch
* Frensch/Francais

= Know Bugs =
* The changelog and roadmap is in the default language, not in the language of the user. At this point I dont see a change to fix this.

== Installation ==

Installation is extremely easy:

1. Download the .zip file and unarchive into your `/wp-content/plugins` directory.
2. Activate the plugin.
3. Visit the WP Mantis page under Settings to set the locations of your Mantis Installation, Username/Password, etc.
4. Add `[Mantis]` to the page or post where you want the table to appear. See the Description for the options.

== Screenshots ==

1. The normal Buglist in a page.
2. A Roadmap in a page.
3. The Admin interface (in German)
4. The colorpicker in the admin interface.

== Changelog ==

= 1.2.2 =
* Replaced preg_match() with strstr(). This fixes a crasy bug on some servers.

= 1.2.1 =
* Bugfix: Ampersand (&) in the username or password would cause errors.

= 1.2 =
* Addet `include_stat` parameter. It´s the oposite to exclude_stat
* Addet `limit` parameter to limit the displayed bugs.

= 1.1 =
* Added pagination
* Replaced cURL with Snoopy, now the Plugin could work on some Servers, wich had cURL disabled.
* Roadmaps and Changelogs can now be displayed for whole Projects.
* Roadmaps and Changelogs can now be specified by a project and version name.

= 1.0 =
* First release of my fork
* Fixed a typo in a var name. This prevents the plugin from working.
* Changed bgcolor to its CSS equivalent. Otherwise the table color was overwritten by the theme.
* Removed the the_content filter and replaced it with a shortcode ([Mantis])
* Addet attributes to the shortcode. Now you can view "bugs" "roadmap" and "changelog". For more information see readme.txt
* Addet the id "mantis_bugs" to the bugs table.
* Addet CSS file. Some styles copied from Mantis.
* Addet option to shorten the bug description.
* Addet option to load status description from the Mantis
* Addet a colorpicker for the colors in the admin area.

= 0.1 =
* Initial release of WP Mantis Tables