# PHP Gallery
Modern, but simple remake of the classic ASP hash-based photo gallery.
Will server all images from a specified folder outside the webroot to the website.
Allows view-counting, thumbs and ratings. Compatible to mobile devices for swiping.
May easily be extended to a more secure version hiding some images depending on the user account. Exif data is read and displayed. GPS positions are reverse-geocoded. Thumbs, exif, ratings are stored to db with the image hash as key, so moving the file will not alter any information.

## Installation

### Prepare Database

Create a Database by importing ``sql\gallery.sql`` file to your mysql server using phpmyadmin.

### Configure

Set DB-Settings, Image-Path and google API-Key in ``config.php``

### Copy files to your web-documents-folder
Just copy them there and open the url.. First load of a folder will take time, because thumbs and Geolocation are created.
