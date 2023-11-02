# Tanken
This is a very easy app (also for mobiles) for getting prices of fuel in germany.
The app reads (via a php script) the gas station data from the public clever-tanken website, 
reformat this information, sort the offers by price and displays it in a very readable way.

# Common
The app contains code of:
- html 
- javascript
- css
- php

# How to develop
if you want to develop new functionality or only to see, how this app works - do the following steps:
- Install a webserver on your computer (like xampp) 
- Clone this repository into the your webserver http folder (on xampp this is the subfolder ./htdocs) 
- Open the php script database.php and
- Uncomment the line after the line ```//next line required for XAMPP (local) ``` 
- Comment the line after the line  ``` //next line required for limacity ``` 
- Save the changes php script file
- Open the javascript file ServerQuery.js and
- Uncomment the line ``` sServerURL = "http://127.0.0.1/tanken/database.php"; ```
- Comment the following line ``` sServerURL = "https://franzweb.lima-city.de/Tanken/database.php"; ```
- Now you can run the app on your local computer :-)


