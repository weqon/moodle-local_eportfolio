# ePortfolio #

The “ePortfolio” in Moodle provides a way to share your own content created
with H5P available with other students. The H5P content can be created directly
via the plugin or existing content types can be uploaded via a form. 
Currently, the content can be shared for viewing and grading. 
A teacher also has the option of making H5P content available as a 
“template”.

Also, a H5P content type has been developed for the ePortfolio-Plugin:
https://www.olivertacke.de/labs/2022/10/25/show-what-you-can-do-with-h5p-portfolio/

## Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually ##

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/local/eportfolio

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

### After installation ###
Set the capability "moodle/h5p:deploy" for role "user".  

*The default "user" role requires the capability "moodle/h5p:deploy".  
Without this capability user can't share H5P content and other participants cannot access the file.*

Set the default roles for "students" and "grading teacher" in the plugin settings.  

## Relase notes ##

**Version 0.2.0**  
***Please make a backup before updating!***  

- Added new DB table local_eportfolio
  - Each content now has its own entry. In the previous version the content was only stored in the “files” table
- Added new fields "title" and "description"
  - It is now possible to add an individual title and a short description
- New "renderer"-like class to output the ePortfolio overview page (index.php)
  - Improved usability
- Added capability check for all pages
- Added setting page
  - Enable entry for main navigation 
  - Default grading teacher role 
  - Default student role
- Sharing form improvements
  - Info box, if and where an ePortfolio file was already shared
  - Removed hard coded role IDs
- Several code improvements & quality check
  - Removed capability assignment from install.php and upgrade.php
  - Removed hard coded role IDs
  - Preparations to upload the plugin to the Moodle plugin repo

## License ##

2024 weQon UG <support@weqon.net>

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.
