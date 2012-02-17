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

global $db;
global $amp_conf;

$filename = '/var/lib/asterisk/bin/freepbx_engine';
$cmd = "python /var/lib/asterisk/bin/rpcserver.py";
//$somecontent = "#!/usr/bin/env bash\n\nkill `ps -ef | grep ".'"'. $cmd .'" ' ."| awk '{print $2}'`\nnohup $cmd &\n";
$somecontent = "#!/usr/bin/env bash\n\nnohup $cmd &\n";
//$somecontent = "#!/usr/bin/env bash\n\nkillall -9 python\nnohup python /var/lib/asterisk/bin/rpcserver.py &\n";
// prepare for python xmlrpc server
function delxlines($txtfile, $numlines, $somecontent)
{
	if (file_exists("temp.txt"))
	{
		unlink("temp.txt");
	}

	$arrayz = file("$txtfile");
	$tempWrite = fopen("temp.txt", "w");
	fwrite($tempWrite, $somecontent);

	for ($i = $numlines; $i < count($arrayz); $i++)
	{
		fputs($tempWrite, "$arrayz[$i]");
	}

	fclose($tempWrite);
	copy("temp.txt", "$txtfile");
	unlink("temp.txt");
}
exec("sed -i '/python/d' $filename");
delxlines($filename, 1, $somecontent);

// Try setup module ODBC CENTOS + UBUNTU
//exec("yum -y unixODBC-devel unixODBC mysql-connector-odbc");
//exec("apt-get -y install libmyodbc unixODBC unixODBC-dev");
//this only can be instal by root

if($amp_conf["AMPDBENGINE"] == "sqlite3")  {
	$sql = "
	CREATE TABLE IF NOT EXISTS odbcadmin 
	(
		id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
		dsname varchar(45) not null, 
		dbname varchar(150) not null,  
		username varchar(25) null, 
		password varchar(25) null
		host varchar(5) not null, 
		port varchar(20) null, 
        dbtype varchar(10) NOT NULL,
		description varchar(12) null, 
        trace varchar(3) NOT NULL default 'no',
        tracefile varchar(30) NOT NULL default '',
        enable varchar(3) NOT NULL default 'yes',
		status varchar(45) null, 
	);
	";
}
else  {
	$sql = "CREATE TABLE IF NOT EXISTS odbcadmin (id INT(11) NOT NULL AUTO_INCREMENT, dsname varchar(30) NOT NULL default '', dbname varchar(40) NOT NULL default '', username varchar(30) NOT NULL default '', password varchar(30) NOT NULL default '', host varchar(20) NOT NULL default '', port varchar(20) NOT NULL default '3306', dbtype varchar(10) NOT NULL default 'mysql', description varchar(80) NOT NULL default '', trace varchar(3) NOT NULL default 'no', tracefile varchar(30) NOT NULL default '', enable varchar(3) NOT NULL default 'yes', status int(1) NOT NULL default 0, UNIQUE (id), PRIMARY KEY (id)) TYPE=MyISAM; 
";
	$query = "CREATE TABLE IF NOT EXISTS odbcdriver (id INT(11) NOT NULL AUTO_INCREMENT, name varchar(80) NOT NULL default '', driver varchar(180) NOT NULL default '' ,setup varchar(180) NOT NULL default '', description varchar(180) NOT NULL default '', enable varchar(3) NOT NULL default 'yes', UNIQUE (id), PRIMARY KEY (id)) TYPE=MyISAM;";

}
$check = $db->query($sql);
if(DB::IsError($check)) {
	die_freepbx("Can not create odbcadmin table");
}
$query_check = $db->query($query);
if(DB::IsError($query_check)) {
	die_freepbx("Can not create odbcdriver table");
}

function odbcadmin_getbyname($dsname){
	$sql="SELECT * FROM odbcadmin WHERE dsname='{$dsname}'";
	$results=sql($sql, "getRow", DB_FETCHMODE_ASSOC);
	return isset($results)?$results:null;
}

// load data configuration from odbc.ini to database (only after DB is created)
function data_source_sync(){
	$odbc_ini = '/etc/odbc.ini';
	//check if file /etc/odbc.ini exists
	if (file_exists($odbc_ini)){

		//load all ini data
		$ini_array = parse_ini_file($odbc_ini, true);
		//loop use for save array to DB
		foreach ($ini_array as $key => $value) {

			$dsname = $key;
			$dbname = array_key_exists('Database', $value)?$value["Database"]:'';
			$dbtype = array_key_exists('Driver', $value)?$value["Driver"]:'';
			$username = array_key_exists('User', $value)?$value["User"]:$username;
			$username = array_key_exists('Username', $value)?$value["Username"]:$username;
			$password = array_key_exists('Password', $value)?$value["Password"]:'';
			$host = array_key_exists('Server', $value)?$value["Server"]:$host;
			$host = array_key_exists('Servername', $value)?$value["Servername"]:$host;
			$port = array_key_exists('Port', $value)?$value["Port"]:'';
			$description = array_key_exists('Description', $value)?$value["Description"]:'';
			$trace = array_key_exists('Trace', $value)?$value["Trace"]:'';
			$tracefile = array_key_exists('TraceFile', $value)?$value["TraceFile"]:'';

            if ($key == "asterisk-cdr"){
                $dbname = "asteriskcdrdb";
                $username = "freepbx";
                $password = "fpbx";
                $port = 3306 ;
            }

			// excute DB insert
			if ($dbname != '' && odbcadmin_getbyname("$dsname") == null){
				$sql="INSERT INTO odbcadmin (dsname, dbname, dbtype, username, password, host, port , description, trace, tracefile) values ('$dsname', '$dbname', '$dbtype', '$username', '$password', '$host', '$port', '$description', '$trace', '$tracefile')";
				sql($sql);
			}
		}
	}
}

function odbcdriver_getbyname($name){
	$sql="SELECT * FROM odbcdriver WHERE name='{$name}'";
	$results=sql($sql, "getRow", DB_FETCHMODE_ASSOC);
	return isset($results)?$results:null;
}

// load data configuration from odbcinst.ini to database (only after DB is created)
function driver_sync(){
	$odbcinst_ini = '/etc/odbcinst.ini';
	//check if file /etc/odbcinst.ini exists
	if (file_exists($odbcinst_ini)){

		//load all ini data
		$ini_array = parse_ini_file($odbcinst_ini, true);
		//loop use for save array to DB
		foreach ($ini_array as $key => $value) {
			$dv_name = $key;
			$dv_setup = array_key_exists('Setup', $value)?$value["Setup"]:'';
			$dv_driver = array_key_exists('Driver', $value)?$value["Driver"]:'';
			$dv_description = array_key_exists('Description', $value)?$value["Description"]:'';
			$dv_enable = 'yes';
			// excute DB insert
			if ($dv_name != '' && odbcdriver_getbyname("$dv_name") == null){
				$sql="INSERT INTO odbcdriver (name, driver, setup, description, enable) values ('$dv_name', '$dv_driver', '$dv_setup', '$dv_description', '$dv_enable')";
				sql($sql);
			}
		}
	}
}

function add_default_driver() {
    $driver = "";
    $setup = "";
    $enable = "yes";
    if (odbcdriver_getbyname("MySQL") == null) {
        $driver = "/usr/lib/libmyodbc3.so";
        if (file_exists('/usr/lib/libmyodbc3.so')) $driver = "/usr/lib/libmyodbc3.so";
        else if (file_exists('/usr/lib/odbc/libmyodbc.so')) $driver = "/usr/lib/odbc/libmyodbc.so";
        else if (file_exists('/usr/lib64/libmyodbc3.so')) $driver = "/usr/lib64/libmyodbc3.so";
		$sql="INSERT INTO odbcdriver (name, driver, setup, description, enable) values ('MySQL', '$driver', '$setup', 'test with mysql', '$enable')";
		sql($sql);
    }
    if (odbcdriver_getbyname("MSSQL") == null) {
        $driver = "/usr/lib/libtdsodbc.so";
        if (file_exists('/usr/lib/libtdsodbc.so')) $driver = "/usr/lib/libtdsodbc.so";
        else if (file_exists('/usr/lib/odbc/libtdsodbc.so')) $driver = "/usr/lib/odbc/libtdsodbc.so";
        else if (file_exists('/usr/lib/odbc/libtdsS.so')) $driver = "/usr/lib/odbc/libtdsS.so";
        else if (file_exists('/usr/local/lib/libtdsodbc.so')) $driver = "/usr/local/lib/libtdsodbc.so";
		$sql="INSERT INTO odbcdriver (name, driver, setup, description, enable) values ('MSSQL', '$driver', '$setup', 'test with mssql', '$enable')";
		sql($sql);
    }
    if (odbcdriver_getbyname("PostgreSQL") == null) {
        $driver = "/usr/lib/libodbcpsql.so";
        if (file_exists('/usr/lib/libodbcpsql.so')) $driver = "/usr/lib/libodbcpsql.so";
        else if (file_exists('/usr/lib/odbc/libodbcpsqlS.so')) $driver = "/usr/lib/odbc/libodbcpsqlS.so";
        else if (file_exists('/usr/lib64/libodbcpsql.so')) $driver = "/usr/lib64/libodbcpsql.so";
		$sql="INSERT INTO odbcdriver (name, driver, setup, description, enable) values ('PostgreSQL', '$driver', '$setup', 'test with PostgreSQL', '$enable') ";
		sql($sql);
    }    
}

data_source_sync();
driver_sync();
add_default_driver()

?>
