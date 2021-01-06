# Webinterface for NMR Autosampler

## Introduction

The "NMR-Killer" is a 3D-printed autosampler for the [Magritek Spinsolve](https://magritek.com/products/spinsolve/) benchtop NMR spectrometer, based on Arduino, Python, and PHP.

This repository contains the code for the **Webinterface** of the "NMR-Killer".

## Third-party software

The webinterface requires [XAMPP](https://www.apachefriends.org/de/index.html).

## Setup

After installing XAMPP, you will have to create a database called "autosampler" and import the autosampler.sql file. The easiest way to do this is via phpMyAdmin, which is included in XAMPP and can be found at http://localhost/phpmyadmin by default.

Copy the "Autosampler" folder into the htdocs directory of XAMPP (typically C:\xampp\htdocs). If you changed the password of your MySQL installation, enter the login data into *mysql_userdata.php*. The webinterface should now be available under http://localhost/Autosampler. Click on the link "configure autosampler" and add the correct settings. This will also set up the python software, which also reads its configuration from the MySQL database. Then, add yourself to the database by clicking on "manage user data" and entering your data.

## Licence

This code is available under the conditions of [GNU General Public Licence version 3](https://www.gnu.org/licenses/gpl-3.0.en.html) or any later version.