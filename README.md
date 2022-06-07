# Webinterface for NMR Autosampler

## Introduction

The "RotoMate" is a 3D-printed autosampler for the [Magritek Spinsolve](https://magritek.com/products/spinsolve/) benchtop NMR spectrometer, based on Arduino, Python, and PHP.

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

# Configuration of Protocols

The default database in autosampler.sql contains three protocols implemented by default, namely "1D PROTON+", "1D FLUORINE+", and "FLUORINE T1". Depending on your needs and the capabilities of your spectrometer, you might want to add, edit, or remove protocols.

The required information can be extracted from the Spinsolve software by running the AvailableProtocolRequest.py script. You must add the appropriate IP and Port as described in the [python docs](https://github.com/marcodyga/nmr_autosampler_python).

To change the available protocols, click on "configure protocols" on the bottom end of the Autosampler webinterface. To add a protocol, enter a display name and the XML Key. You can find the XML key in the response to the AvailableProtocolRequest under <Protocol protocol="**XML KEY**">. A nucleus can be selected, which will be used to determine which processing methods can later be used in combination with this protocol. If the automatic processing can't be used with the protocol, e.g. for T1 measurements, select "none". Click on the + button to add the new protocol. 

The options available to you for each measurement ("properties") can then be configured by clicking on the link in the "Properties" column. Like the protocol, each property has an XML key, which you can find out from the AvailableProtocolRequest under <Option name="**XML KEY**">. In addition, a friendly name must be added which will then be displayed by the software. If there are only a limited number of options available, you must add them under "Options (JSON)" as a **[JSON array](https://www.w3schools.com/js/js_json_arrays.asp)** - take a look at the default protocols as an example on how this might be implemented. The available options are usually listed in the AvailableProtocolRequest, but this data might not always be correct! Make sure to cross-check the options with the ones which are presented to you in the Spinsolve software. On the other hand, if a free text value may be given to the Spinsolve software (e.g. center frequency), check the "Free text?" checkbox. In the "Default Value" column, you can then enter a default value.

Make sure to test the new protocol by queuing a new sample and selecting your new protocol under "Protocol". While the measurement is running in Spinsolve, make sure that the options are all correctly set on the left side of the Spinsolve interface.

## Licence

This code is available under the conditions of [GNU General Public Licence version 3](https://www.gnu.org/licenses/gpl-3.0.en.html) or any later version.