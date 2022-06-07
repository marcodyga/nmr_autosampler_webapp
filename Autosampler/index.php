<!DOCTYPE html>
<html>
  <head>
    <title>NMR Autosampler</title>
    <meta charset="utf-8" />
    <script type="text/javascript">

// This is a list of all columns of the Autosampler table (Database Field Names + Properties).
// Properties get special handling when rendered in info();
var allColumns = ["ID", "Holder", "User", "Solvent", "Protocol", "Properties", "Method", "Name", "Date", "StartDate", "Status", "Progress", "Result"];

function Seitenende() {
  document.getElementById('seitenende').scrollIntoView(true);
}

function refresh() {
    // First part of Refreshing routine:
    // Use AJAX to get the XML document produced by sample_info.php
    xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.readyState==4 && xmlhttp.status==200) {
            // call the 2nd part
            info(xmlhttp.responseXML);
        }
    }
    xmlhttp.open("GET", "sample_info.php");
    xmlhttp.responseType = "document";
    xmlhttp.send();
}

function info(xmlDoc) {
    // Second part of Refreshing routine:
    // Process the information
    // first get all ids of the rows in the table
    var allElements = document.getElementsByTagName("tr");
    var allIds = [];
    for (var i = 0, n = allElements.length; i < n; ++i) {
      var el = allElements[i];
      if (el.id.substring(0,3) == "row") { allIds.push(el.id.slice(3)); }
    }
    var n_rows = allIds.length;
    // now get all ids in the database
    var n_samples = xmlDoc.getElementsByTagName("sample").length;
    var newIds = [];
    for (i=0; i<n_samples; i++) {
        newIds.push(xmlDoc.getElementsByTagName("ID")[i].childNodes[0].nodeValue);
    }
    // loop over all received samples
    var x = xmlDoc.getElementsByTagName("ID");
    for (i=0; i<n_samples; i++) {
        // check if we already have this sample in our table
        var thisID = x[i].childNodes[0].nodeValue;
        if(!contains(allIds, thisID)) {
            // ok this sample is not in the table, we have to get it in there!
            document.getElementById("tbody").innerHTML += createRow(thisID);
        }
        // Now refresh this sample
        allColumns.forEach(function(entry) {
			var newValue;
			if (entry == "Properties") {
				// render properties
				newValue = "";
				var properties = xmlDoc.getElementsByTagName("sampleProperties")[i].getElementsByTagName("property");
				for (var prop of properties) {
					var name = prop.getElementsByTagName("friendlyName")[0].innerHTML;
					var val = prop.getElementsByTagName("strvalue")[0].innerHTML;
					if (newValue != "") {
						newValue += "; ";
					}
					newValue += "<i>" + name + "</i>:&nbsp;" + val;
				}
				newValue += ".";
			} else {
				// for all other columns, directly put the value into the html
				newValue = xmlDoc.getElementsByTagName(entry)[i].childNodes[0].nodeValue;
			}
			var innerHTML = document.getElementById(entry+thisID).innerHTML;
			if(innerHTML != newValue) {
				document.getElementById(entry+thisID).innerHTML = newValue;
			}
        });
        if(xmlDoc.getElementsByTagName("SampleType")[i].childNodes[0].nodeValue != "Sample") {
            // If it's a shimming, display the Shim Type in the "Protocol" column.
            var innerHTML = document.getElementById("Protocol"+thisID).innerHTML;
            var shimTypeDisplayString = "<b>"+xmlDoc.getElementsByTagName("SampleType")[i].childNodes[0].nodeValue+"</b>";
            if(innerHTML != shimTypeDisplayString) {
                document.getElementById("Protocol"+thisID).innerHTML = shimTypeDisplayString;
            }
        }
        // give the rows the correct background colours
        if(i % 2 == 1) {
            var bgcolor = "#f1f1ff";
        } else {
            var bgcolor = "#dcdcff";
        }
        document.getElementById("row"+thisID).setAttribute("bgcolor", bgcolor);
        // give the username the right mouseover
<?php
include("params.php");
foreach($UserData as $user) {
    echo "        if(document.getElementById('User'+thisID).innerHTML == '" . $user['shortname'] . "') {\n";
    echo "            document.getElementById('User'+thisID).title = '" . $user['fullname'] . "';\n";
    echo "        }\n";
}
?>
        // hide the "move down" button if there are no samples below
        if(!(i+1 in x)) {
            document.getElementById("moveDownTD"+thisID).style.visibility = "hidden";
        } else {
            var status2 = xmlDoc.getElementsByTagName("Status")[i+1].childNodes[0].nodeValue;
            if(status2 == "Finished" || status2 == "Running" || status2 == "Failed") {
                document.getElementById("moveDownTD"+thisID).style.visibility = "hidden";
            } else {
                document.getElementById("moveDownTD"+thisID).style.visibility = "visible";
            }
        }
        // hide the "move up" button if there are no samples above
        if(!(i-1 in x)) {
            document.getElementById("moveUpTD"+thisID).style.visibility = "hidden";
        } else {
            var status2 = xmlDoc.getElementsByTagName("Status")[i-1].childNodes[0].nodeValue;
            if(status2 == "Finished" || status2 == "Running" || status2 == "Failed") {
                document.getElementById("moveUpTD"+thisID).style.visibility = "hidden";
            } else {
                document.getElementById("moveUpTD"+thisID).style.visibility = "visible";
            }
        }
        // give the Status column the right background color and
        // hide "add sample" button and the "move sample up/down" buttons
        // if the sample is running, failed or finished.
        var status = xmlDoc.getElementsByTagName("Status")[i].childNodes[0].nodeValue;
        var hideAdd = false;
        var bgcolor = "#ffffff";
        if (status == "Failed") {
            bgcolor = "#dd0000";
            hideAdd = true;
        } else if (status == "Finished") {
            bgcolor = "#00d200";
            hideAdd = true;
        } else if (status == "Running") {
            bgcolor = "#ffff00";
            hideAdd = true;
        }
        document.getElementById("Status"+thisID).setAttribute("bgcolor", bgcolor);
        if (hideAdd) {
            document.getElementById("addTD"+thisID).style.visibility = "hidden";
            document.getElementById("moveUpTD"+thisID).style.visibility = "hidden";
            document.getElementById("moveDownTD"+thisID).style.visibility = "hidden";
        }
        // Hide the "delete sample" button if the sample is running (user should abort first)
        // commented out since it allows to solve problems after crashes etc.
        /*if (status == "Running") {
            document.getElementById("deleteTD"+thisID).style.visibility = "hidden";
        } else {
            document.getElementById("deleteTD"+thisID).style.visibility = "visible";
        }*/
    }
    // now we also have to loop over all samples in our table
    for (i=0; i<n_rows; i++) {
        var thisRow = allIds[i];
        if(!contains(newIds, thisRow)) {
            // this sample is not in the database anymore and should be removed
            var row = document.getElementById("row"+thisRow);
            row.parentNode.removeChild(row);
        }
    }
    // here we can extract the information on Shimming, LastShim, ShimProgress, QueueStat, Timestamp and ASStatus
    var Shimming = xmlDoc.getElementsByTagName("Shimming")[0].childNodes[0].nodeValue;
    var LastShim = xmlDoc.getElementsByTagName("LastShim")[0].childNodes[0].nodeValue;
    var ShimProgress = xmlDoc.getElementsByTagName("ShimProgress")[0].childNodes[0].nodeValue;
    var QueueStat = xmlDoc.getElementsByTagName("QueueStat")[0].childNodes[0].nodeValue;
    var Timestamp = xmlDoc.getElementsByTagName("Timestamp")[0].childNodes[0].nodeValue;
    var ASStatus = xmlDoc.getElementsByTagName("AutosamplerStatus")[0].childNodes[0].nodeValue;
    // now we can keep the table foot updated.
    // first we update the first element from the left: QueueControl
    var SinceShim = Timestamp-LastShim;
    var output = "<form action=\"updateDB.php\" method=\"post\" target=\"debugFrame\" id=\"QueueControl\" style=\"display: inline;\">";
    if(ASStatus <= 0 || (ASStatus == 3 && Shimming != 0)) {
        if(QueueStat==0 && Shimming==0) {
            // In this case, you can start the measurement or shim manually.
            if(ASStatus >= 0) {
                // If autosampler is connected, you can shim and measure, or start the measurement directly.
                output += "<input type='submit' name='Start' value='Start' /> the queue, or ";
                output += "<input type='hidden' name='mode' value='start' />";
                output += "</form><form action=\"updateDB.php\" method=\"post\" target=\"debugFrame\" id=\"QueueControl\" style=\"display: inline;\">";
                output += "<input type='submit' name='Shim' value='Shim and start' /> instead.";
                output += "<input type='hidden' name='mode' value='shimstart' />";
            }
        } else if(SinceShim>43200 && QueueStat==0 && Shimming==0) {
            if(ASStatus >= 0) {
                output += "<input type='submit' name='Shim' value='Shim and start' /> the queue.";
                output += "<input type='hidden' name='mode' value='shimstart' />";
            }
        } else if(Shimming==1) {
            output += "Shimming in progress, please wait!";
        } else if(Shimming==2) {
            output += "CheckShim reported system not ready. Performing QuickShim (" + ShimProgress + "&nbsp;%). This may take up to 5 min.";
        } else if(Shimming==3) {
            output += "1<sup>st</sup> QuickShim reported system not ready. Performing another QuickShim (" + ShimProgress + "&nbsp;%).";
        } else if(Shimming==4) {
            output += "2<sup>nd</sup> QuickShim reported system still not ready. Trying one last QuickShim (" + ShimProgress + "&nbsp;%).";
        } else if(Shimming==5) {
            output += "QuickShim failed three times. Check if shimming sample (10% D<sub>2</sub>O + 90% H<sub>2</sub>O) is inserted correctly and <input type='submit' name='Shim' value='try again' />.";
            output += "<input type='hidden' name='mode' value='shim' />";
        } else if(QueueStat==1) {
            output += "Sample is being measured.";
        }
    } else if(ASStatus == 3) {
		if(QueueStat==1) {
			// Autosampler reports measurement is running, and a new one can't be started.
			output += "Queue is running.";
		} else {
			// Queue should be aborted very soon
			output += "Queue has been aborted, please wait...";
		}
    } else {
        // Autosampler reports an error, no runs can be started
        output += "&nbsp;";
    }
    output += "</form>";
	if(document.getElementById("QueueControl").innerHTML !== output) {
		document.getElementById("QueueControl").innerHTML = output;
	}
    // now we will update the 2nd element from the left: AbortControl
    output = "";
    if(QueueStat==1) {
        output += "<input type='submit' name='Abort' value='Abort current run' />";  
        output += "<input type='hidden' name='mode' value='abort' />";
    } else if(QueueStat==0) {
        output += "<input type='submit' name='clear' value='Clear table' onclick=\"return confirm('Are you sure you want to delete the sequence table unrecoverably?');\" />";
        output += "<input type='hidden' name='mode' value='clear' />";
    }
    if(document.getElementById("AbortControl").innerHTML !== output) {
		document.getElementById("AbortControl").innerHTML = output;
	}
    // now the 3rd element: ASStatusTD (autosampler status)
    output = "";
    var bgcolor = "";
    if(ASStatus == -2) {
        output += "Connection to Autosampler lost!";
        bgcolor = "#ffffff";
    } else if(ASStatus == -1) {
        output += "Could not connect to Autosampler.";
        bgcolor = "#ffffff";
    } else if(ASStatus == 0) {
        output += "Autosampler is ready to use.";
        bgcolor = "#00d200";
    } else if(ASStatus == 1) {
        output += "Autosampler is at work!";
        bgcolor = "#ffff3a";
    } else if(ASStatus == 2) {
        output += "Error in the Autosampler! Please check if a NMR tube is stuck inside.";
        bgcolor = "#ff3a3a";
    } else if(ASStatus == 3) {
        output += "Autosampler reports measurement is running!";
        bgcolor = "#ffff00";
    } else if(ASStatus == 4) {
        output += "Error: Slider did not properly open, please check if a NMR tube is stuck the device!";
        bgcolor = "#ff3a3a";
    } else if(ASStatus == 5) {
        output += "Error: Slider did not properly close, please check if a NMR tube is stuck the device!";
        bgcolor = "#ff3a3a";
    } else if(ASStatus == 6) {
        output += "Error: A NMR tube was detected inside of the spectrometer before starting the queue. Please remove it from Holder 32.";
        bgcolor = "#ff3a3a";
    } else if(ASStatus == 7) {
        output += "Error: When trying to return a sample, no sample could be detected in the spectrometer!";
        bgcolor = "#ff3a3a";
    } else if(ASStatus == 8) {
        output += "Error: When trying to start a measurement, no sample could be detected in the specified holder!";
        bgcolor = "#ff3a3a";
    } else if(ASStatus == 9) {
        output += "Autosampler reports an unknown error raised by this table!";
        bgcolor = "#ff3a3a";
    } else {
        output += "Unknown error from the Autosampler!";
        bgcolor = "#ff3a3a";
    }
	if(document.getElementById("ASStatusTD").innerHTML !== output) {
		document.getElementById("ASStatusTD").innerHTML = output;
		document.getElementById("ASStatusTD").setAttribute("bgcolor", bgcolor);
	}
}

function createRow(id) {
    var output = "";
    output += "<tr id='row"+id+"'>\n";
    allColumns.forEach(function(entry) {
        output += "<td style='text-align: center' id='"+entry+id+"'></td>\n";
    });
    output += "<td>";
    output += "  <span id='deleteTD"+id+"'>"
    output += "    <form action='updateDB.php' target='debugFrame' method='post'>";
    output += "      <input type='submit' value='&minus;' />";
    output += "      <input type='hidden' name='mode' value='delete' />";
    output += "      <input type='hidden' name='ID' value='"+id+"' />";
    output += "    </form>";
    output += "  </span>";
    output += "</td>";
    output += "<td>";
    output += "  <span id='addTD"+id+"'>";
    output += "    <form action='add_sample.php' target='addSampleFrame' method='get'>";
    output += "      <input type='submit' value='+' />";
    output += "      <input type='hidden' name='ID' value='"+id+"' />";
    output += "    </form>";
    output += "  </span>";
    output += "</td>";
    output += "<td>";
    output += "  <span id='moveUpTD"+id+"'>";
    output += "    <form action='updateDB.php' target='debugFrame' method='post'>";
    output += "      <input type='submit' value='&#x2191;' />";
    output += "      <input type='hidden' name='mode' value='moveUp' />";
    output += "      <input type='hidden' name='ID' value='"+id+"' />";
    output += "    </form>";
    output += "  </span>";
    output += "</td>";
    output += "<td>";
    output += "  <span id='moveDownTD"+id+"'>";
    output += "    <form action='updateDB.php' target='debugFrame' method='post'>";
    output += "      <input type='submit' value='&#x2193;' />";
    output += "      <input type='hidden' name='mode' value='moveDown' />";
    output += "      <input type='hidden' name='ID' value='"+id+"' />";
    output += "    </form>";
    output += "  </span>";
    output += "</td>";
    output += "</tr>";
    return output;
}

function contains(a, obj) {
    // checks if a contains obj
    // credit: http://stackoverflow.com/questions/237104/how-do-i-check-if-an-array-includes-an-object-in-javascript
    for (var i = 0; i < a.length; i++) {
        if (a[i] === obj) {
            return true;
        }
    }
    return false;
}

function test() {
    document.getElementById("tbody").innerHTML = "";
}

function showStatus() {
    var frame = window.document.getElementById("debugFrame");
    if(frame.width == 500) {
        frame.width = 0;
        frame.height = 0;
        frame.style.visibility = "hidden";
    } else {
        frame.width = 500;
        frame.height = 200;
        frame.style.visibility = "visible";
    }
}

    </script>
  </head>
  <body onload="Seitenende(); setInterval(refresh, 500);">
    <iframe width="0" height="0" src="" name="debugFrame" id="debugFrame" style="visibility:hidden"></iframe>
    <div id="tableHTML">
      <noscript>
        <p><b>This page does not work without JavaScript, please make sure JavaScript is activated in your browser.</b></p>
      </noscript>
      
      <table border='1' id='table'>  
        <thead>
          <tr>
<?php   // Creates the table head with $Parameters
foreach ($Parameters as $ColumnName) {
    echo "<th>$ColumnName</th>";
}
?>

          </tr>
        </thead>
<!-- Creates table foot; Should be filled with "Start", "Pause", and "Stop" buttons as well as status fields.-->
        <tfoot>
          <tr>
            <td style='text-align: center' colspan="7">
              <div id="QueueControl">
                <!-- the form inside QueueControl is defined by the AJAX. -->
              </div>
            </td>
            <td style='text-align: center' colspan="1">
              <form action="updateDB.php" method="post" target="debugFrame" id="AbortControl">
           
              </form>
            </td>
            <td colspan='5' id='ASStatusTD' style="text-align:center">
              
            </td>
            <td style='text-align:center' colspan='4'>
              <form action='add_sample.php' target='addSampleFrame' method='get'>
                <input type='submit' value='Add new sample' />
                <input type='hidden' name='ID' value='-1' />
              </form>
            </td>
          </tr>
        </tfoot>
        <tbody id="tbody">
<?php
// the table body (and the elements in the table foot)
// can be empty, it will be filled automatically by the AJAX.
// i think it is better to only create this HTML code in one place, so there will be no 
// redundance of code.
?>
        </tbody>
      </table>
    
    </div>
    <div>
    </div>
    <div>
       <iframe width="0" height="0" id="addSampleFrame" name="addSampleFrame" style="visibility:hidden"></iframe>
    </div>
    <div id="seitenende">
      <!--<a href='javascript:showStatus();'>show/hide status information</a> | -->
      <a href='users.php'>manage user data</a> | 
      <a href='config.php'>configure autosampler</a> |
	  <a href='protocols.php'>configure protocols</a> |
	  <a href='nmr_standards.php'>configure internal standards</a> |
	  <a href='methods.php'>configure processing methods</a> |
      <a href='resetShimming.php' target='debugFrame'>reset shimming</a>
    </div>
  </body>
</html>