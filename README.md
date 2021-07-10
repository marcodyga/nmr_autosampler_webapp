# Webinterface for NMR Autosampler

## Introduction

The "RotoMate" is a 3D-printed autosampler for the [Magritek Spinsolve](https://magritek.com/products/spinsolve/) benchtop NMR spectrometer, based on Arduino, Python, and PHP.

Our open-access manuscript with detailed build instructions and CAD data can be found under the following URL: https://doi.org/10.1016/j.ohx.2021.e00211

This repository contains the code for the **Webinterface** of the "RotoMate".

## Third-party software

The webinterface requires [XAMPP](https://www.apachefriends.org/de/index.html).

## Setup

Before installing the webapp, you should set up the [Arduino](https://github.com/marcodyga/nmr_autosampler_arduino).

After installing XAMPP, you will have to create a database called "autosampler" and import the autosampler.sql file. The easiest way to do this is via phpMyAdmin, which is included in XAMPP and can be found at http://localhost/phpmyadmin by default. To create a database in phpMyAdmin, click on "New" in the navigation bar on the left side, enter "autosampler" as database name, and click on "create". Now, click on "Import" in the upper navigation bar and import the autosampler.sql from this repository.

Copy the "Autosampler" folder into the htdocs directory of XAMPP (typically C:\xampp\htdocs). If you changed the password of your MySQL installation, enter the login data into *mysql_userdata.php*. The webinterface should now be available under http://localhost/Autosampler. Click on the link "configure autosampler" and add the correct settings. This will also set up the python software, which also reads its configuration from the MySQL database. 

You will also need to set up remote control on your *Spinsolve* spectrometer. From *Spinsolve*'s main menu, go to *System*, *Prefs*, and *Setup*. Under the tab *Remote Control*, set a port number (e.g. 13000), then enable remote control.

* `NMR folder` is the location where the autosampler will save spectra
* `NMR IP` is the IP address of the PC on which Spinsolve is running - this should usually be localhost
* `NMR port` is the remote control port of Spinsolve (see above)
* `Autosampler port` is the COM port of the autosampler's Arduino controller, you can find it out via the [Arduino IDE](https://github.com/marcodyga/nmr_autosampler_arduino) or Windows' Device Manager.
* `ACD folder` is the installation folder of ACD NMR Processor - to be precise: the folder which contains SPECMAN.EXE

Finally, add the first user to the database by clicking on "manage user data" and entering your data.

Continue by setting up the [python software to control the queue and autosampler](https://github.com/marcodyga/nmr_autosampler_python).

## Licence

This code is available under the conditions of [GNU General Public Licence version 3](https://www.gnu.org/licenses/gpl-3.0.en.html) or any later version.
