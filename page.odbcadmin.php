<?php 
/**
 * FreePBX ODBCAdmin Module
 *
 * Copyright (c) 2011, VoiceNation, LLC.
 *
 * This program is free software, distributed under the terms of
 * the GNU General Public License Version 2.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
*/

// determine if Asterisk Addons for MySQL are installed and if ODBC support is intalled
// first check mysql
// look for /usr/lib/asterisk/modules/app_addon_sql_mysql.so
$mysqlInstalled = file_exists('/usr/lib/asterisk/modules/app_addon_sql_mysql.so');

// next check asterisk odbc support
$ASodbcInstalled = find_asodbc();

// next check linux odbc install
$UnixodbcInstalled = find_odbc();

$iam = 'odbcadmin'; //used for switch on config.php
$type = 'tool';

// set curent odbc object
$dsname = isset($_REQUEST['dsname'])?$_REQUEST['dsname']:'';
$dbname = isset($_REQUEST['dbname'])?$_REQUEST['dbname']:'';
$dbtype = isset($_REQUEST['dbtype'])?$_REQUEST['dbtype']:'';
$username = isset($_REQUEST['username'])?$_REQUEST['username']:'';
$password = isset($_REQUEST['password'])?$_REQUEST['password']:'';
$host = isset($_REQUEST['host'])?$_REQUEST['host']:'';
$port = isset($_REQUEST['port'])?$_REQUEST['port']:'';
$description = isset($_REQUEST['description'])?$_REQUEST['description']:'';
$trace = isset($_REQUEST['trace'])?$_REQUEST['trace']:'';
$tracefile = isset($_REQUEST['tracefile'])?$_REQUEST['tracefile']:'';
$enable = isset($_REQUEST['asenable'])?$_REQUEST['asenable']:'';
$enable = isset($_REQUEST['asenable'])?$_REQUEST['asenable']:'';
$dsnid = isset($_REQUEST['ext'])?$_REQUEST['ext']:'';


$dv_name = isset($_REQUEST['dv_name'])?$_REQUEST['dv_name']:'';
$dv_setup = isset($_REQUEST['dv_setup'])?$_REQUEST['dv_setup']:'';
$dv_driver = isset($_REQUEST['dv_driver'])?$_REQUEST['dv_driver']:'';
$dv_description = isset($_REQUEST['dv_description'])?$_REQUEST['dv_description']:'';
$dv_enable = isset($_REQUEST['dv_enable'])?$_REQUEST['dv_enable']:'';

// look for form post
isset($_POST['action'])?$postaction = $_POST['action']:$postaction='';

switch ($postaction) {
	case "odbcadd":
        $exist = odbcadmin_getbyname($dsname);
        if ($exist == null) {
	        odbcadmin_add($dsname, $dbname, $dbtype, $username, $password, $host, $port, $description, $trace, $tracefile, $enable);
	        needreload();
	        redirect_standard();
        }
        else {
            echo "This DSN is exist, please select another one!";
        }
	break;
	case "odbcdelete":
		odbcadmin_del($dsnid);
		needreload();
		redirect_standard();
	break;
	case "odbcedit":
		odbcadmin_edit($dsnid, $dsname, $dbname, $dbtype, $username, $password, $host, $port, $description, $trace, $tracefile, $enable);
		needreload();
		redirect_standard('ext');
	break;

	case "driveradd":
        $exist = odbcdriver_getbyname($dv_name);
        if ($exist == null) {
	        odbcdriver_add($dv_name, $dv_driver, $dv_setup, $dv_description, $dv_enable);
	        needreload();
	        redirect_standard();
        }
        else {
            echo "This DB driver name is exist, please select another one!";
        }
	break;
	case "driveredit":
		odbcdriver_edit($dsnid, $dv_name, $dv_driver, $dv_setup, $dv_description, $dv_enable);
		needreload();
		redirect_standard();
	break;
	case "driverdelete":
		odbcdriver_delete($dsnid);
		needreload();
		redirect_standard();
	break;
    //for setup menu
	case "install_odbc":
        setup_odbc();
		needreload();
		redirect_standard();
	break;
	case "install_asodbc":
        setup_asodbc();
		needreload();
		redirect_standard();
	break;
	case "install_PostgreSQL":
        setup_odbcdriver('PostgreSQL');
		needreload();
		redirect_standard();
	break;
	case "install_MySQL":
        setup_odbcdriver('MySQL');
		needreload();
		redirect_standard();
	break;
	case "install_MSSQL":
        setup_odbcdriver('MSSQL');
		needreload();
		redirect_standard();
	break;
}

// look for get
isset($_GET['action'])?$action = $_GET['action']:$action='';
isset($_GET['ext'])?$ext=$_GET['ext']:$ext='';

$xmlrpc = rpc_status();
if ($xmlrpc){
    switch ($action) {
	    // this for menu odbc data source
	    case "dsnlist":
		    odbcadmin_sidebar($ext, $type, $iam);
		    dsnlist_show($type, $iam);
	    break;
	    case "odbcadd":
		    odbcadmin_sidebar($ext, $type, $iam);
		    odbcadmin_show(null, $type, $iam);
	    break;
	    case "odbcedit":
		    odbcadmin_sidebar($ext, $type, $iam);
		    odbcadmin_show($ext, $type, $iam);
	    break;

	    // this for menu odbc driver
	    // case "driverlist":
		//    odbcadmin_sidebar($ext, $type, $iam);
		//    driverlist_show($type, $iam);
	    //break;
	    case "driveradd":
		    odbcadmin_sidebar($ext, $type, $iam);
		    odbcdriver_show(null, $type, $iam);
	    break;
	    case "driveredit":
		    odbcadmin_sidebar($ext, $type, $iam);
		    odbcdriver_show($ext, $type, $iam);
	    break;

	    // another menu
	    case "fileconf":
		    odbcadmin_sidebar(null, $type, $iam);
		    fileconf_show($type, $iam);
	    break;
	    case "setup":
		    odbcadmin_sidebar(null, $type, $iam);
		    setup_show($mysqlInstalled, $ASodbcInstalled, $UnixodbcInstalled);
	    break;
	    default:
		    odbcadmin_sidebar(null, $type, $iam);
		    odbcadmin_index($mysqlInstalled, $ASodbcInstalled, $UnixodbcInstalled );
	    break;
    }
}
else { // mean xmlrpc server not started
    odbcadmin_sidebar(null, $type, $iam);
    odbcadmin_index($mysqlInstalled, $ASodbcInstalled, $UnixodbcInstalled );
}

function draw_status_box($text, $status, $tooltip = false, $total_width = 250) {
	switch ($status) {
		case "ok":
			$status_text = _("OK");
			$class = "graphok";
		break;
		case "warn":
			$status_text = _("Warn");
			$class = "graphwarn";
		break;
		case "error":
			$status_text = _("ERROR");
			$class = "grapherror";
		break;
		case "disabled":
			$status_text = _("Disabled");
			$class = "";
		break;
	}
	if ($tooltip !== false) {
		$status_text = '<a href="#" title="'.$tooltip.'">'.$status_text.'</a>';
	}
	
	$out = "<div class=\"databox statusbox\" style=\"width:".$total_width."px;\">\n";
	$out .= " <div class=\"dataname\">".$text."</div>\n";
	$out .= " <div id=\"datavalue_".str_replace(" ","_",$text)."\" class=\"datavalue ".$class."\">".$status_text."</div>\n";
	$out .= "</div>\n";
	
	return $out;
}

function show_procinfo($ASodbcInstalled, $UnixodbcInstalled) {
	global $amp_conf;
	$out = '';
	
	$out .= "<h4><img src='images/asterisk_orange.png' alt=''/> "._("Server Status")."</h4>";

	// UnixODBC
	if ($UnixodbcInstalled) {
		$out .= draw_status_box(_("UnixODBC"), "ok", _('UnixODBC server is installed.'));
	}
     else {
		$out .= draw_status_box(_("UnixODBC"), "warn", _('UnixODBC cannot be confirmed in your server!'));
	}
	// Asterisk ODBC
	if ($ASodbcInstalled) {
		$out .= draw_status_box(_("Asterisk-ODBC module"), "ok", _('Asterisk-ODBC module is enabled.'));
	}
     else {
		$out .= draw_status_box(_("Asterisk-ODBC module"), "warn", _('Asterisk-ODBC module cannot be confirmed!'));
	}

	$out .= "<h4><img src='images/asterisk_orange.png' alt=''/> "._("ODBC Drivers")."</h4>";
	// MySQL
    $odbcMySQL = find_odbcdriver('MySQL');
	if ($odbcMySQL) {
		$out .= draw_status_box(_("MySQL"), "ok", _('MySQL driver for ODBC is installed'));
	}
     else {
		$out .= draw_status_box(_("MySQL"), "disabled", _('MySQL driver for ODBC is NOT installed!'));
	}

	// PostgreSQL
    $odbcPostgreSQL = find_odbcdriver('PostgreSQL');
	if ($odbcPostgreSQL) {
		$out .= draw_status_box(_("PostgreSQL"), "ok", _('PostgreSQL driver for ODBC is installed'));
	}
     else {
		$out .= draw_status_box(_("PostgreSQL"), "disabled", _('PostgreSQL driver for ODBC is NOT installed!'));
	}


	// Microsoft SQL Server
    $odbcMSSQL = find_odbcdriver('MSSQL');
	if ($odbcMSSQL) {
		$out .= draw_status_box(_("Microsoft SQL Server"), "ok", _('Microsoft SQL Server driver for ODBC is installed'));
	}
     else {
		$out .= draw_status_box(_("Microsoft SQL Server"), "disabled", _('Microsoft SQL Server driver for ODBC is NOT installed!'));
	}
    /*
	// SQLite

    $odbcSQLite = find_odbcdriver('SQLite');
	if ($odbcSQLite) {
		$out .= draw_status_box(_("SQLite"), "ok", _('SQLite driver for ODBC is installed'));
	}
     else {
		$out .= draw_status_box(_("SQLite"), "disabled", _('SQLite driver for ODBC is NOT installed!'));
	}
    */
	return $out;
}

function odbcadmin_index($mysqlInstalled, $ASodbcInstalled, $UnixodbcInstalled) {
    $xmlrpc = rpc_status();
    echo "<h5>ODBC is a standardized database driver for Windows, Linux, Mac OS X, and Unix platforms. The 'ODBC Admin Configuration Module' helps you to easily setup your Asterisk server to work with external database like MySQL, MSSQL, PostgreSQL based on the UnixODBC driver. For users who don't have experience with ODBC, this module will provide a quick and easy way to get up and running, while for the advanced user this module will help to maintain database configuration and save time.</h5>";
    echo "<hr>";
    if ($xmlrpc == False){
        echo "<h4><font color='red'>WARNING: Can NOT connect to your server. Please do 'amportal restart' and then refresh this page!</font></h4>";
        echo "<h4><img src='images/asterisk_orange.png' alt=''/> Please note - if you have just installed this module:</h4>";
        echo "<ul>";
        echo "<li>First, make sure you have \"applied configuration changes\" after install.";
        echo "<li>Next, run the command 'amportal restart' as root user on your server (from the command line) or simply reboot the server.  There are some initialization functions we need to do in order to install odbc components.</li>";
        echo "<li>Otherwise, make sure 'UnixODBC' AND 'Asterisk, ODBC capable' are already installed.</li>";
        echo "<li>Note that this module requires python to be installed on your system (it most likely is).</li>";
        echo "<li>You can see your server status in the list below.</li>";
        echo "</ul>";
    }
    else if (!$ASodbcInstalled || !$UnixodbcInstalled){
        echo "<h4><font color='red'>Almost There: Now we need to perform some setup and install functions to enable ODBC. Please go to the <a href='config.php?type=tool&display=odbcadmin&action=setup' target='_blank'><font color='blue'>Setup Page</font></a> for more information!</font></h4>";}
    else
        echo "<p>Please <a href = 'config.php?type=tool&display=odbcadmin&action=dsnlist'><font color='red'>click here</font></a> if you want to go to your database connect management page.</p>";
?>
<div>
	<?php echo show_procinfo($ASodbcInstalled, $UnixodbcInstalled); ?>
</div>

	<br><p>For more information, documentation, the latest release of this module, or to report a bug, visit the <a target="_blank" href="http://www.qualityansweringservice.com/anatomy-oscc/freepbx/odbcadmin"><font color="red">ODBC-Admin home</font></a>.</p>

<?php
}

function fileconf_show($type, $iam){
    $res_odbc = "/etc/asterisk/res_odbc.conf";
    $odbc = "/etc/odbc.ini";
    $odbcinst = '/etc/odbcinst.ini';
    echo "<h5>This page is only using for you to review the UnixODBC config files (odbc.ini, odbcinst.ini) and the Asterisk res_odbc.conf ... You can make sure that ODBC Admin module has accurately generated your desired configuration by reviewing these files online.</h5>";
    echo "<hr>";
    $fh = fopen($res_odbc, 'r');
    $res_odbc_data = fread($fh, filesize($res_odbc));
    fclose($fh);

    $fh = fopen($odbc, 'r');
    $odbc_data = fread($fh, filesize($odbc));
    fclose($fh);

    $fh = fopen($odbcinst, 'r');
    $odbcinst_data = fread($fh, filesize($odbcinst));
    fclose($fh);
    echo "<table>";
        echo "<tr><td>";  
            echo "<h4><img src='images/asterisk_orange.png' alt=''/> "._("Asterisk res_odbc file:")."</h4>";    
            echo '<textarea name="res_odbc_data" rows="15" cols="80">';
            echo $res_odbc_data;
            echo '</textarea>';
            echo "</br>";
        echo "</td></tr>";

        echo "<tr><td>";  
            echo "<h4><img src='images/asterisk_orange.png' alt=''/> "._("The odbc.ini file:")."</h4>"; 
            echo '<textarea rows="10" cols="80" name="odbc_data">';
            echo $odbc_data;
            echo '</textarea>';
            echo "</br>";
        echo "</td></tr>";

        echo "<tr><td>";  
            echo "<h4><img src='images/asterisk_orange.png' alt=''/> "._("The odbcinst.ini file:")."</h4>";
            echo '<textarea rows="10" cols="80" name="odbcinst_data" >';
            echo $odbcinst_data;
            echo '</textarea>';
            echo "</br>";
        echo "</td></tr>";
    echo "</table>";

}

function setup_show($mysqlInstalled, $ASodbcInstalled, $UnixodbcInstalled){

	$engineinfo = engine_getinfo();
	$astver =  $engineinfo['version'];

    // Value for test only
    //$ASodbcInstalled = False;
    //$UnixodbcInstalled = True;

    echo "<h5>The 'Module Settings Page' will help you to review configuration options. In this page you will configure and setup any needed database drivers.</h5>";
    echo "<hr>";
	echo "<h4><img src='images/asterisk_orange.png' alt=''/> <strong>UnixODBC Server</strong></h4>";
	if (!$UnixodbcInstalled){
   		echo "<form name=\"install_odbc\" action=\"".'config.php?type=tool&display=odbcadmin&action=setup'."\" method=\"post\" onsubmit=\"return ODBC_onsubmit();\">"; 
        echo "<h5><font color=DarkOrange>UnixODBC cannot be confirmed on your server.</font></h5>";
        echo "<p>Please note that using this setup tool will auto install UnixODBC and attempt to install Asterisk with ODBC support!  Manually install ODBC support in Asterisk if you have a custom compiled version or if this does not work for you.</p>";
		echo "<p><font color=DarkOrange>Instructions for manually installing unixODBC are located <a href='http://www.unixodbc.org/unixODBCsetup.html' target='_blank'><font color='red'>here</font></a>.</font></p>";
		echo "<input type='hidden' name='action' value='install_odbc'>";
    	echo "<input type=submit Value='Install ODBC' >"; 
    	echo "</form>";
	}

	else{
		exec('odbcinst -j',$odbc_status);
		echo '<div class="odbc_status">';
		foreach($odbc_status as $l){
			echo "$l</br>";
		}
		echo '</div>'; 
    }

	if ($UnixodbcInstalled && !$ASodbcInstalled){
   		echo "<form name=\"install_asodbc\" action=\"".'config.php?type=tool&display=odbcadmin&action=setup'."\" method=\"post\" onsubmit=\"return ASODBC_onsubmit();\">"; 
		echo "<h4><img src='images/asterisk_orange.png' alt=''/> <strong>Enable Asterisk-ODBC Module</strong></h4>";
		echo "<p><font color=DarkOrange>Asterisk ODBC support is not enabled on your server!  Instructions for compiling Asterisk with ODBC are <a href='http://www.qualityansweringservice.com/anatomy-oscc/asterisk/install_odbc' target='_blank'><font color='red'>here</font></a>.</br>Instructions for configuring ODBC in linux and Asterisk are <a href='http://astbook.asteriskdocs.org/en/2nd_Edition/asterisk-book-html-chunk/installing_configuring_odbc.html' target='_blank'><font color='red'>here</font></a></br></br><font color='red'>IMPORTANCE: After installion of dependent packages, don't forget to RECOMPILE your Asterisk server software!</font></font></p>";
		echo "<input type='hidden' name='action' value='install_asodbc'>";
    	echo "<input type=submit Value='Install Dependent Packet' >"; 
    	echo "</form>";
        //.
	}
    /*
	else{
		echo "<p>Your Asterisk server has ODBC support!</p>";            
        } */

    if ($UnixodbcInstalled && $ASodbcInstalled){

	echo "<h4><img src='images/asterisk_orange.png' alt=''/> <strong>"._("Database Drivers List:")."</strong></h4>";
    ?>
    <table border = 1>
        <tr>
            <td align = 'center'><strong>Driver</strong></td>
            <td align = 'center'><strong>Description</strong></td>
            <td align = 'center'><strong>Supported</strong></td>
        </tr>
        <tr>
            <td>MySQL</td>
            <td>provide access to a MySQL database using the industry standard ODBC API</td> 
            <?php 
               
            if (find_odbcdriver('MySQL')){ 
                echo "<td align = 'center'><img src='images/flag_blue.png' alt=''/></td>"; 
            }
            else{
                echo "<td align = 'center'>"; 
       		    echo "<form name=\"install_MySQL\" action=\"".'config.php?type=tool&display=odbcadmin&action=setup'."\" method=\"post\" onsubmit=\"return MySQL_onsubmit();\">";
		        echo "<input type='hidden' name='action' value='install_MySQL'>";
    	        echo "<input type=submit Value='Install' >";
    	        echo "</form>";
                echo "</td>"; 
            }
            ?>
        </tr>
        <tr>
            <td>PostgreSQL</td>
            <td>provide access to a PostgreSQL database using the industry standard ODBC API</td> 
            <?php 
                
            if (find_odbcdriver('PostgreSQL')){ 
                echo "<td align = 'center'><img src='images/flag_blue.png' alt=''/></td>"; 

            }
            else{
                echo "<td align = 'center'>"; 
       		    echo "<form name=\"install_PostgreSQL\" action=\"".'config.php?type=tool&display=odbcadmin&action=setup'."\" method=\"post\" onsubmit=\"return PostgreSQL_onsubmit();\">";
		        echo "<input type='hidden' name='action' value='install_PostgreSQL'>";
    	        echo "<input type=submit Value='Install' >";
    	        echo "</form>";
                echo "</td>"; 
            }
            ?>
        </tr>
        <tr>
            <td>MSSQL</td>
            <td>provide access to a Microsoft SQL database using FreeTDS and ODBC API</td> 
            <?php 
                
            if (find_odbcdriver('MSSQL')){ 
                echo "<td align = 'center'><img src='images/flag_blue.png' alt=''/></td>"; 
            }
            else{
                echo "<td align = 'center'>"; 
       		    echo "<form name=\"install_MSSQL\" action=\"".'config.php?type=tool&display=odbcadmin&action=setup'."\" method=\"post\" onsubmit=\"return MSSQL_onsubmit();\">";
		        echo "<input type='hidden' name='action' value='install_MSSQL'>";
    	        echo "<input type=submit Value='Install' >";
    	        echo "</form>";
                echo "</td>"; 
            }
            ?>
        </tr>
    </table>
    <ul>
        <li>If you see <img src='images/flag_blue.png' alt=''/> symbol it means that this driver is ready to use (installed successful). If not, you can press 'install' button to set it up.</li>
	    <li>Please checking this link <a href = "http://www.unixodbc.org/drivers.html">http://www.unixodbc.org/drivers.html</a> for further instructions.</li>
    </ul>
    <?php   
    driverlist_show('tool', 'odbcadmin');
	}
}

// --------------------------------------------------
// This will for ODBC Driver Management 
//---------------------------------------------------

// list all odbc driver here
function driverlist_show($type, $iam){
    echo("<h4><img src='images/asterisk_orange.png' alt=''/> <strong>ODBC Drivers Configuration:</strong></h4>");
    echo ("<h5><i>This feature allows you to add/enable a database driver which you can use with the ODBC API. You must make sure that this driver lib (.so file) is already installed on your server and can be used to communicate with UnixODBC.</i></h5>");
    echo("<img src='images/add.png' alt=''/>");
    echo "<i><a id='".($sel==''?'current':'std')."' ";
    echo "href='config.php?type={$type}&amp;display={$iam}&amp;action=driveradd'>"._(" Add Your New Custom Driver")."</a></i>";
    //echo ("</br>");
    echo"</br>
        <table border = 1>
        <tr>
            <th align='center'>Name</th>
            <th>Driver</th>
            <th align='center'>Enabled</th>
            <th align='center'>Notice</th>
        </tr>";

    //get the list of paging groups
    $driverlist = odbcdriver_list();
    if ($driverlist) {
            foreach ($driverlist as $driver) {
                $display = true;
                if ($driver[1] == 'PostgreSQL' || $driver[1] == 'MySQL' || $driver[1] == 'MSSQL'){
                    $display = find_odbcdriver($driver[1]);
                }

                if ($display) {
                    $note = "";
                    if (file_exists(""."${driver[2]}") == false) {
                        $note = 'Driver file does not exist!';}
                    echo "<tr><td align='left'><a id=\"".($sel==$driver[0] ? 'current':'std');
                    echo "\" href=\"config.php?type=${type}&amp;display=";
                    echo "${iam}&amp;ext=${driver[0]}&amp;action=driveredit\"><strong><u><i>";
                    echo "${driver[1]}";
                    echo"</i></u></strong></a></td>";
                    echo"<td>${driver[2]}</td>";
                    echo"<td align='center'>${driver[3]}</td>";
                    echo"<td>$note</td>";
                }
            }
    }
    echo"</table>";
    //echo "<h5>Notices:</h5>";
    echo "<ul><li>You can click on a driver's name to edit its options.</li><li>Make sure the driver is enabled (enabled = yes) so it can be use for a DSN.</li></ul>";
}

// for add/delete odbc driver
function odbcdriver_show($xtn, $type, $iam){
	//get driver information if editing
	if(!empty($xtn)) {
		$thisxtn = odbcdriver_get($xtn);
        $dv_name= $thisxtn['name'];
        $dv_driver= $thisxtn['driver'];
        $dv_setup= $thisxtn['setup'];
        $dv_enable = $thisxtn['enable']; 
		$dv_description = $thisxtn['description']; 
		if(!is_array($thisxtn)) {
			echo "Error: cannot retreive configuration info for this object";
			return;
		}

		$action = 'driveredit';
		
		//draw delete button
		echo <<< End_Of_Delete
		<form method="POST" action="{$_SERVER['PHP_SELF']}?type={$type}&display={$iam}">
		<input type="hidden" name="action" value="driverdelete">
		<input type="hidden" name="ext" value="{$thisxtn['id']}">
		<input type="submit" value="Delete This Driver"></form>
End_Of_Delete;
	}

	echo("<hr>");

	if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'driveradd') {
		$thisxtn['ext'] = $_REQUEST['ext'];
		$thisxtn[0] = $_REQUEST['ext'];
		$action = 'driveradd';
	}

    echo "<form name=\"addNewdriver\" action=\"".$_SERVER['PHP_SELF']."?type={$type}&display={$iam}&action={$action}"."\" method=\"post\">";

	echo "<input type='hidden' name='action' value='{$action}'>";
	echo "<input type='hidden' name='ext' value='{$thisxtn['id']}'>";

    echo "<table>";

    echo "<tr><td colspan=2><h3>";
    echo ($_REQUEST['ext'] ? _('Edit ODBC Driver') : _('Add ODBC Driver'));
    echo "</h3></td></tr>\n";

    //Session Name
    echo "<tr ";
    echo ($_REQUEST['ext'] ? '' : '');
    echo "><td>";
    echo "<a href=\"#\" class=\"info\" >"._("Name")."\n";
    echo "<span>"._("Name (REQUIRED)")."</span></a>\n";
    echo "</td><td>\n";
    /*
    $list_type = get_driverlist();
    echo "&nbsp;&nbsp;<select name=\"dv_name\" tabindex=".++$tabindex.">\n";
    foreach ($list_type as $s){		
		if ($s == "".$dv_name){
			echo "<option value = '{$s}' selected>{$s}</option>\n";
		}
		else{
			echo "<option value = '{$s}'>{$s}</option>\n";
		}
    }
    echo "</select>\n";
    */
    echo "<input type=text name=\"dv_name\" value=\"$dv_name\" tabindex=".++$tabindex.">\n";
    echo "</td></tr>\n";

    /*
    echo "<tr ";
    echo ($_REQUEST['ext'] ? '' : '');
    echo "><td>";
    echo "<a href=\"#\" class=\"info\" >"._("Name")."\n";
    echo "<span>"._("Name (REQUIRED)")."</span></a>\n";
    echo "</td>";
    echo "<td>";
    echo "<input type=text name=\"dv_name\" value=\"$dv_name\" tabindex=".++$tabindex.">\n";
    echo "</td></tr>\n";
    */

    //Driver 
    echo "<tr ";
    echo ($_REQUEST['ext'] ? '' : '');
    echo "><td>";
    echo "<a href=\"#\" class=\"info\" >"._("Driver")."\n";
    echo "<span>"._("Driver (REQUIRED)")."</span></a>\n";
    echo "</td>";
    echo "<td>";
    echo "<input type=text name=\"dv_driver\" value=\"$dv_driver\" tabindex=".++$tabindex.">\n";
    echo "</td></tr>\n";

    //Setup 
    echo "<tr ";
    echo ($_REQUEST['ext'] ? '' : '');
    echo "><td>";
    echo "<a href=\"#\" class=\"info\" >"._("Setup")."\n";
    echo "<span>"._("Setup (REQUIRED)")."</span></a>\n";
    echo "</td>";
    echo "<td>";
    echo "<input type=text name=\"dv_setup\" value=\"$dv_setup\" tabindex=".++$tabindex.">\n";
    echo "</td></tr>\n";

    //description 
    echo "<tr ";
    echo ($_REQUEST['ext'] ? '' : '');
    echo "><td>";
    echo "<a href=\"#\" class=\"info\" >"._("Description")."\n";
    echo "<span>"._("Setup (REQUIRED)")."</span></a>\n";
    echo "</td>";
    echo "<td>";
    echo "<input type=text name=\"dv_description\" value=\"$dv_description\" tabindex=".++$tabindex.">\n";
    echo "</td></tr>\n";

    //Enable For using
    echo "<tr><td>\n";
    echo "<a href=\"#\" class=\"info\">"._("Enable")."\n";
    echo "<span>"._("RES_ODBC Enable (REQUIRED)")."</span></a>\n";
    echo "</td><td>\n";
    $list_type = array('yes', 'no'); 
    echo "&nbsp;&nbsp;<select name=\"dv_enable\" tabindex=".++$tabindex.">\n";
    foreach ($list_type as $s){
        echo "<option value=\"$s\"";
        if($s==$dv_enable) echo " SELECTED";
        echo ">$s</option>\n";
    }
    echo "</select>\n";
    echo "</td></tr>\n";

    echo "<tr><td></td><td><input type=submit Value='Submit Changes' tabindex=".++$tabindex."></td></tr></table>";

	echo"</form>";
}

// --------------------------------------------------
// This will for ODBC DSN Management 
//---------------------------------------------------

// list all dsn here
function dsnlist_show($type, $iam){
    echo ("<h5>The Data Source Name (DSN) by which an ODBC application will connect to the server. This page will help you to manage (update/delete) list of current DSNs and include add a new one. Before you connect to a database using the Connector/ODBC driver you must configure an ODBC Data Source Name. The DSN associates the various configuration parameters required to communicate with a database to a specific name. You use the DSN in an application to communicate with the database, rather than specifying individual parameters within the application itself. DSN information can be user specific, system specific, or provided in a special file. ODBC data source names are configured in different ways, depending on your platform and ODBC driver.</h5>");
    echo("<hr>");
    echo("<h3>Data Sources Management</h3>");
    echo("<img src='images/add.png' alt=''/>");
    echo "<i><a id='".($sel==''?'current':'std')."' ";
    echo "href='config.php?type={$type}&amp;display={$iam}&amp;action=odbcadd'>"._(" Add New DSN")."</a></i>";
    echo ("</br>");
    echo"</br>
        <table border = 1>
        <tr>
            <th align='center'>DSN</th>
            <th align='center'>ODBC driver</th>
            <th align='center'>Status</th>
        </tr>";

    //get the list of paging groups
    $dsnlist = odbcadmin_list();
    if ($dsnlist) {
            foreach ($dsnlist as $dsn) {
		        $thisxtn = odbcadmin_get($dsn[0]);
                $dsname=$thisxtn['dsname'];
                $dbname=$thisxtn['dbname'];
                $dbtype=$thisxtn['dbtype'];
                $username=$thisxtn['username'];
                $password=$thisxtn['password'];
                $host=$thisxtn['host'];
                $port=$thisxtn['port'];
                $description=$thisxtn['description'];
                $trace =$thisxtn['trace'];
                $tracefile =$thisxtn['tracefile'];
                $enable =$thisxtn['enable']; 
                $do_presave = odbcadmin_edit($dsn[0], $dsname, $dbname, $dbtype, $username, $password, $host, $port, $description, $trace, $tracefile, $enable);
                echo "<tr><td align='left'><a id=\"".($sel==$dsn[0] ? 'current':'std');
                echo "\" href=\"config.php?type=${type}&amp;display=";
                echo "${iam}&amp;ext=${dsn[0]}&amp;action=odbcedit\"><strong><u><i>";
                echo "${dsn[1]}";
                echo"</i></u></strong></a></td>";
                echo"<td align='center'>${dsn[4]}</td>";
                $username = "${dsn[2]}";
                $password = "${dsn[3]}";

                $check_status = check_db_connect($dsn[1], $username, $password);
                if ($check_status == 'OK')
                    echo"<td align='center'><img src='images/connect.png' alt=''/></td>";
                else
                    echo"<td align='center'><img src='images/disconnect.png' alt=''/></td>";
                echo"</tr>";
            }
    }
    echo"</table>";
    echo "</br></br>";
    echo "<h5>Site Notes:</h5>";
   // echo "<ul><li>You can click on a DSN from the list above to edit its data.</li><li>'ODBC driver' is got from <a href = 'config.php?type=tool&display=odbcadmin&action=driverlist'>this list</a>.</li><li><img src='images/connect.png' alt=''/>    DSN status is connected.</li><li><img src='images/disconnect.png' alt=''/>    DSN status is disconnected or got error.</li></ul>";
    echo "<ul><li>You can click on a DSN from the list above to edit its data.</li><li><img src='images/connect.png' alt=''/>    DSN status is connected.</li><li><img src='images/disconnect.png' alt=''/>    DSN status is disconnected or got error.</li></ul>";
}

// for add/edit a dsn
function odbcadmin_show($xtn, $type, $iam) {
	
	//get settings if editing
	if(!empty($xtn)) {
		$thisxtn = odbcadmin_get($xtn);
        $dsname=$thisxtn['dsname'];
        $dbname=$thisxtn['dbname'];
        $dbtype=$thisxtn['dbtype'];
        $username=$thisxtn['username'];
        $password=$thisxtn['password'];
        $host=$thisxtn['host'];
        $port=$thisxtn['port'];
        $description=$thisxtn['description'];
        $trace =$thisxtn['trace'];
        $tracefile =$thisxtn['tracefile'];
        $enable =$thisxtn['enable']; 
		if(!is_array($thisxtn)) {
			echo "Error: cannot retreive configure info for this object";
			return;
		}

		$action = 'odbcedit';
		
		//draw delete button
		echo <<< End_Of_Delete

		<form method="POST" action="{$_SERVER['PHP_SELF']}?type={$type}&display={$iam}">
		    <input type="hidden" name="action" value="odbcdelete">
		    <input type="hidden" name="ext" value="{$thisxtn['id']}">
		    <input type="submit" value="Delete This DSN">
        </form>

End_Of_Delete;
	}
?>
	<hr>
<?php
	
	if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'odbcadd') {
		$thisxtn['ext'] = $_REQUEST['ext'];
		$thisxtn[0] = $_REQUEST['ext'];
		$action = 'odbcadd';
	}
	


	//echo "<form method='POST' action='{$_SERVER['PHP_SELF']}?type=$type&display=$iam&ext=$ext&action=$action'>";
    echo "<form name=\"addNew\" action=\"".$_SERVER['PHP_SELF']."?type={$type}&display={$iam}&action={$action}"."\" method=\"post\">";

	echo "<input type='hidden' name='action' value='{$action}'>";
	echo "<input type='hidden' name='ext' value='{$thisxtn['id']}'>";

    echo "<table>";

    echo "<tr><td colspan=2><h3>";
    echo ($_REQUEST['ext'] ? _('Edit DSN Settings') : _('Add New DSN'));
    echo "</h3></td></tr>";

    //Status Of Connection Check
    if ($_REQUEST['ext']){

        $connect_status = check_db_connect($dsname, $username, $password);
	    if ($connect_status == "OK") {
		    $cstatus = draw_status_box(_("Connection Status"), "ok", _('Check database connection successed.'), 200);
	    }
         else {
		    $cstatus = draw_status_box(_("Connection Status"), "error", _("$connect_status"), 200);
	    }
        
        echo "<tr align='center'><td align='left' colspan=2>";
        echo $cstatus;   
        echo "</td></tr>";
    }

    echo "<tr><td></td></tr>";


    //DB type
    echo "<tr><td>\n";
    echo "<a href=\"#\" class=\"info\">"._("DB-Type")."\n";
    echo "<span>"._("Database Type (REQUIRED)")."</span></a>\n";
    echo "</td><td>\n";
    //$type =($ext ? $type : "mysql");
    //$list_type = array('PostgreSQL', 'MySQL', 'SQLite', 'MSSQL'); 
    //$list_type = array('PostgreSQL', 'MySQL', 'MSSQL'); 
    $list_type = odbcdriver_list();
    echo "&nbsp;&nbsp;<select name=\"dbtype\" tabindex=".++$tabindex.">\n";
    foreach ($list_type as $s){		
		if ($s[1] == "".$dbtype){
			echo "<option value = '{$s[1]}' selected>{$s[1]}</option>\n";
		}
		else if ($s[3] == "yes" && find_odbcdriver($s[1])){
			echo "<option value = '{$s[1]}'>{$s[1]}</option>\n";
		}
    }
    echo "</select>\n";
    echo "</td></tr>\n";

    //DSNAME
    echo "<tr ";
    echo ($_REQUEST['ext'] ? '' : '');
    echo "><td>";
    echo "<a href=\"#\" class=\"info\" >"._("DSN-name")."\n";
    echo "<span>"._("DSNAME (REQUIRED)")."</span></a>\n";
    echo "</td>";
    echo "<td>";
    echo "<input type=text name=\"dsname\" value=\"$dsname\" tabindex=".++$tabindex.">\n";
    echo "</td></tr>\n";

    //DB_name
    echo "<tr><td>\n";
    echo "<a href=\"#\" class=\"info\">"._("DB-name")."\n";
    echo "<span>"._("DBname (REQUIRED)")."</span></a>\n";
    echo "</td><td>\n";
    echo "<input type=text tabindex=".++$tabindex." name=\"dbname\" value=\"$dbname\"\n";
    echo "</td></tr>\n";

    //USERNAME
    echo "<tr><td>\n";
    echo "<a href=\"#\" class=\"info\">"._("Username")."\n";
    echo "<span>"._("Usename (REQUIRED)")."</span></a>\n";
    echo "</td><td>\n";
    echo "<input type=text tabindex=".++$tabindex." name=\"username\" value=\"$username\">\n";
    echo "</td></tr>\n";

    //PASSWORD
    echo "<tr><td>\n";
    echo "<a href=\"#\" class=\"info\">"._("Password")."\n";
    echo "<span>"._("Password (REQUIRED)")."</span></a>\n";
    echo "</td><td>\n";
    echo "<input type=text name=\"password\" value=\"$password\" tabindex=".++$tabindex.">\n";
    echo "</td></tr>\n";

    //HOST
    echo "<tr><td>\n";
    echo "<a href=\"#\" class=\"info\">"._("Host")."\n";
    echo "<span>"._("Host (REQUIRED)")."</span></a>\n";
    echo "</td><td>\n";
    echo "<input type=text name=\"host\" value=\"$host\" tabindex=".++$tabindex.">\n";
    echo "</td></tr>\n";

    //PORT
    echo "<tr><td>\n";
    echo "<a href=\"#\" class=\"info\">"._("Port")."\n";
    echo "<span>"._("Empty for default")."</span></a>\n";
    echo "</td><td>\n";
    echo "<input type=text name=\"port\" value=\"$port\" tabindex=".++$tabindex.">\n";
    echo "</td></tr>\n";


    //DESCRIPTION
    echo "<tr><td>\n";
    echo "<a href=\"#\" class=\"info\">"._("Description")."\n";
    echo "<span>"._("Description")."</span></a>\n";
    echo "</td><td>\n";
    echo "<input type=text name=\"description\" value=\"$description\" tabindex=".++$tabindex.">\n";
    echo "</td></tr>\n";

    //Trace
    echo "<tr><td>\n";
    echo "<a href=\"#\" class=\"info\">"._("Trace")."\n";
    echo "<span>"._("Trace Enable")."</span></a>\n";
    echo "</td><td>\n";
    $list_type = array('yes', 'no'); 
    echo "&nbsp;&nbsp;<select name=\"trace\" tabindex=".++$tabindex.">\n";
    foreach ($list_type as $s){
        echo "<option value=\"$s\"";
        if($s==$trace) echo " SELECTED";
        echo ">$s</option>\n";
    }
    echo "</select>\n";
    echo "</td></tr>\n";

    //TraceFile
    echo "<tr><td>\n";
    echo "<a href=\"#\" class=\"info\">"._("TraceFile")."\n";
    echo "<span>"._("TraceFile")."</span></a>\n";
    echo "</td><td>\n";
    echo "<input type=text name=\"tracefile\" value=\"$tracefile\" tabindex=".++$tabindex.">\n";
    echo "</td></tr>\n";

    //Enable For Res_odbc
    echo "<tr><td>\n";
    echo "<a href=\"#\" class=\"info\">"._("Enable")."\n";
    echo "<span>"._("RES_ODBC Enable (REQUIRED)")."</span></a>\n";
    echo "</td><td>\n";
    $list_type = array('yes', 'no'); 
    echo "&nbsp;&nbsp;<select name=\"asenable\" tabindex=".++$tabindex.">\n";
    foreach ($list_type as $s){
        echo "<option value=\"$s\"";
        if($s==$enable) echo " SELECTED";
        echo ">$s</option>\n";
    }
    echo "</select>\n";
    echo "</td></tr>\n";
    echo "<tr align = 'right'><td align = 'center' colspan = 2><input type=submit Value='Submit Changes' tabindex=".++$tabindex."></td></tr>";

	echo"</table></form>";
	

}

function odbcadmin_sidebar($sel, $type, $iam) {
    $xmlrpc = rpc_status();
    $ASodbcInstalled = find_asodbc();
    $UnixodbcInstalled = find_odbc();

    if ($xmlrpc){ // if we can connect to xmlrpc server

        echo "</div><div class='rnav'>\n";
	    echo "<ul><li><a id='".($sel==''?'current':'std')."' ";
        echo "href='config.php?type={$type}&amp;display={$iam}&amp;action='>"._("Home")."</a></li>";
	    echo "<li><a id='".($sel==''?'current':'std')."' ";
        echo "href='config.php?type={$type}&amp;display={$iam}&amp;action=setup'>"._("Module Setup")."</a></li>";
	    echo "<br/>";
        if ($UnixodbcInstalled && $ASodbcInstalled){
            echo "<li><a id='".($sel==''?'current':'std')."' ";
            echo "href='config.php?type={$type}&amp;display={$iam}&amp;action=dsnlist'>"._("Data Source Name")."</a></li>";
            //echo "<li><a id='".($sel==''?'current':'std')."' ";
            //echo "href='config.php?type={$type}&amp;display={$iam}&amp;action=driverlist'>"._("ODBC Drivers")."</a></li>";

            //echo "<br/>";
	        echo "<li><a id='".($sel==''?'current':'std')."' ";
            echo "href='config.php?type={$type}&amp;display={$iam}&amp;action=fileconf'>"._("Files Configure Viewer")."</a></li>";
        }       
    } 
        echo "</ul></div><div class='content'><h2>"._("ODBC Admin Configuration")."</h2>\n";   
}


?>


<script language='javascript'>
    var cform = document.addNew;
    if(cform.name.value == ''){
	    cform.name.focus();
    }
    function addNew_onsubmit() {
	    var msgInvalidName = 'Please enter a dsname records';
	    if(isEmpty(cform.dsname.value)){
		    return warnInvalid(cform.dsname, msgInvalidName);
	    }
    }

    function ODBC_onsubmit(){
        alert ("This will install ODBC on your server. The install can take several minutes!");
    }

    function ASODBC_onsubmit(){
        alert ("This will install ODBC support in your Asterisk server!");
    }

    function PostgreSQL_onsubmit(){
        alert ("This will install the PostgreSQL ODBC driver on your server!");
    }
    function MySQL_onsubmit(){
        alert ("This will install the MySQL ODBC driver on your server!");
    }
    function MSSQL_onsubmit(){
        alert ("This will install the FreeTDS-ODBC driver for Microsoft SQL connect.");
    }
</script>
