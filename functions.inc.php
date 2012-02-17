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

//----------------- Processing res_odbc.conf -----------------------------
function get_res_odbc_data(){
	$data = Null;
	$res_odbc = '/etc/asterisk/res_odbc.conf';
	if (file_exists($res_odbc)){
		$data = parse_ini_file($res_odbc, true);
	}
	return $data;
}


function is_session_exists($name, $data){
	$flag = array_key_exists($name, $data);
	return $flag;
}

function delete_res_odbc($name){
	exec("python /var/lib/asterisk/bin/rpclient.py RESODBC del=$name");
	return True;
}

function add_resodbc($dsn, $enabled, $username, $password){
	$myFile = '/etc/asterisk/res_odbc.conf';
	$data=
"
[$dsn]
enabled => $enabled
dsn => $dsn
username => $username
password => $password
pre-connect => yes
pooling => yes
limit => 25
";

	$fh = fopen($myFile, 'a') or die("can't open file");
	fwrite($fh, $data);
	fclose($fh);
}

function update_res_odbc($dsn, $enabled, $username, $password){
	$res_odbc_data = get_res_odbc_data();
	delete_res_odbc($dsn);
	add_resodbc($dsn, $enabled, $username, $password);

}


//------------------------------------------------------------------------


// define odbc config data like a dict keywords
function ini_creater($dsname, $dbname, $dbtype, $username, $password, $host, $port, $description, $trace, $tracefile , $enable){

    $mysql_data = 
"
[$dsname]
Description         = $description
Driver              = $dbtype
Database            = $dbname
Server              = $host
User                = $username
Password            = $password
Port                = $port
";  

    $psql_data = 
"
[$dsname]
Description         = $description
Driver              = $dbtype
TraceFile           = $tracefile
Trace               = $trace
Database            = $dbname
Servername          = $host
Username            = $username
Password            = $password
Port                = $port
";  

    $mssql_data = 
"
[$dsname]
Description         = $description
Driver              = $dbtype
Server              = $host
Database            = $dbname
Port                = $port
Username            = $username
Password            = $password
TraceFile           = $tracefile
Trace               = $trace
TDS_Version         = 4.2
";  

    if ($dbtype == 'MySQL') $data = $mysql_data;
    else if ($dbtype == 'PostgreSQL') $data = $psql_data;
    else if ($dbtype == 'MSSQL') $data = $mssql_data;

    $fname = "".rand(5, 1577).".ini";
    $myFile = "/etc/asterisk/$fname";
    $fh = fopen($myFile, 'w') or die("can't open file");
    fwrite($fh, $data);
    fclose($fh);
    return $myFile;
}

//-------------------------- odbc driver handle ----------------------
/*
// define odbc config data like a dict keywords
function driver_creater($name, $driver, $setup, $description){
    $data = 
"
[$name]
Description     = $description
Driver          = $driver
Setup           = $setup
";  
    $fname = "".rand(1578, 15557).".ini";
    $myFile = "/etc/asterisk/$fname";
    $fh = fopen($myFile, 'w') or die("can't open file");
    fwrite($fh, $data);
    fclose($fh);
    return $myFile;
}
*/
function odbcdriver_list(){
	$sql = "SELECT * FROM odbcdriver";
	$results= sql($sql, "getAll");

	foreach($results as $result){
		$driver_records[] = array($result[0],$result[1],$result[2],$result[5]);
	}
	return isset($driver_records)?$driver_records:null;
}

function odbcdriver_get($ext){
	$sql="SELECT * FROM odbcdriver WHERE id=$ext";
	$results=sql($sql, "getRow", DB_FETCHMODE_ASSOC);
	return isset($results)?$results:null;
}

function odbcdriver_getbyname($name){
	$sql="SELECT * FROM odbcdriver WHERE name='{$name}'";
	$results=sql($sql, "getRow", DB_FETCHMODE_ASSOC);
	return isset($results)?$results:null;
}

function odbcdriver_delete($ext){
    $section = odbcdriver_get($ext);
    $section_name = $section['name'];
    exec("python /var/lib/asterisk/bin/rpclient.py DRIVER del=$section_name");
	$sql="DELETE FROM odbcdriver where id=$ext";
	sql($sql);
}

function odbcdriver_add($name, $driver, $setup, $description, $enable){
	if($enable == 'yes'){
		$add_string = "".$name."-".$driver."-".$setup."-".$description;
		exec("python /var/lib/asterisk/bin/rpclient.py DRIVER add=$add_string");
	}
	$sql="INSERT INTO odbcdriver (name, driver, setup, description, enable) values ('$name', '$driver', '$setup', '$description', '$enable')";
	sql($sql);
}

function odbcdriver_edit($ext, $name, $driver, $setup, $description, $enable){
	//edit = del old + add new
    $section = odbcdriver_get($ext);
    $section_name = $section['name'];
    exec("python /var/lib/asterisk/bin/rpclient.py DRIVER del=$section_name");
	if($enable == 'yes'){
		$add_string = "".$name."-".$driver."-".$setup."-".$description;
		exec("python /var/lib/asterisk/bin/rpclient.py DRIVER add=$add_string");
	}
	$sql="UPDATE odbcdriver set name='$name' where id='$ext'";
	sql($sql);
	$sql="UPDATE odbcdriver set driver='$driver' where id='$ext'";
	sql($sql);
	$sql="UPDATE odbcdriver set setup='$setup' where id='$ext'";
	sql($sql);
	$sql="UPDATE odbcdriver set description='$description' where id='$ext'";
	sql($sql);
	$sql="UPDATE odbcdriver set enable='$enable' where id='$ext'";
	sql($sql);

}

//-------------------------- odbc data source handle ----------------------
function odbcadmin_list(){
	$sql = "SELECT * FROM odbcadmin";
	$results= sql($sql, "getAll", DB_FETCHMODE_ASSOC);

	foreach($results as $result){
		$odbc_records[] = array($result['id'],$result['dsname'],$result['username'],$result['password'],$result['dbtype']);
	}
	return isset($odbc_records)?$odbc_records:null;
}

function odbcadmin_get($extdisplay){
	$sql="SELECT * FROM odbcadmin WHERE id=$extdisplay";
	$results=sql($sql, "getRow", DB_FETCHMODE_ASSOC);
	return isset($results)?$results:null;
}

function odbcadmin_getbyname($dsname){
	$sql="SELECT * FROM odbcadmin WHERE dsname='{$dsname}'";
	$results=sql($sql, "getRow", DB_FETCHMODE_ASSOC);
	return isset($results)?$results:null;
}

function odbcadmin_del($extdisplay){
    $section = odbcadmin_get($extdisplay);
    $section_name = $section['dsname'];
    exec("python /var/lib/asterisk/bin/rpclient.py CONFIG delete=$section_name");
	delete_res_odbc($section_name); //delete at res_odbc.conf
	$sql="DELETE FROM odbcadmin where id=$extdisplay";
	sql($sql);
}

function odbcadmin_add($dsname, $dbname, $dbtype, $username, $password, $host, $port, $description, $trace, $tracefile, $enable){
    if ($port == ''){$port = '3306';}
	$sql="INSERT INTO odbcadmin (dsname, dbname, dbtype, username, password, host, port , description, trace, tracefile, enable) values ('$dsname', '$dbname', '$dbtype', '$username', '$password', '$host', '$port', '$description', '$trace', '$tracefile', '$enable')";
	sql($sql);
    $file = ini_creater($dsname, $dbname, $dbtype, $username, $password, $host, $port, $description, $trace, $tracefile, $enable);
    exec("python /var/lib/asterisk/bin/rpclient.py CONFIG sync=$file");
	update_res_odbc($dsname, $enable, $username, $password);
}

function odbcadmin_edit($extdisplay, $dsname, $dbname, $dbtype, $username, $password, $host, $port, $description, $trace, $tracefile, $enable){
    if ($port == ''){$port = '3306';}
    
    // init delete the old section (for easy handle)
    $section = odbcadmin_get($extdisplay);
    $section_name = $section['dsname'];
    exec("python /var/lib/asterisk/bin/rpclient.py CONFIG delete=$section_name");

	$sql="UPDATE odbcadmin set dsname='$dsname' where id='$extdisplay'";
	sql($sql);
	$sql="UPDATE odbcadmin set dbname='$dbname' where id='$extdisplay'";
	sql($sql);
	$sql="UPDATE odbcadmin set username='$username' where id='$extdisplay'";
	sql($sql);
	$sql="UPDATE odbcadmin set password='$password' where id='$extdisplay'";
	sql($sql);
	$sql="UPDATE odbcadmin set host='$host' where id='$extdisplay'";
	sql($sql);
	$sql="UPDATE odbcadmin set port='$port' where id='$extdisplay'";
	sql($sql);
	$sql="UPDATE odbcadmin set dbtype='$dbtype' where id='$extdisplay'";
	sql($sql);
	$sql="UPDATE odbcadmin set description='$description' where id='$extdisplay'";
	sql($sql);
	$sql="UPDATE odbcadmin SET trace='$trace', tracefile='$tracefile', enable='$enable' where id='$extdisplay'";
	sql($sql);

    $file = ini_creater($dsname, $dbname, $dbtype, $username, $password, $host, $port, $description, $trace, $tracefile , $enable);
    exec("python /var/lib/asterisk/bin/rpclient.py CONFIG sync=$file");
	update_res_odbc($dsname, $enable, $username, $password);
}

function save_resodbc($post_resodbc_data){
    $myFile = "/etc/asterisk/res_odbc.conf";
    $fh = fopen($myFile, 'w') or die("can't open file");
    fwrite($fh, $post_resodbc_data);
    fclose($fh);
}

function save_odbc($post_odbc_data){
    $myFile = "/etc/asterisk/odbc_temp.txt";
    $fh = fopen($myFile, 'w') or die("can't open file");
    fwrite($fh, $post_odbc_data);
    fclose($fh);
    exec("python /var/lib/asterisk/bin/rpclient.py CONFIG odbc");
}
function save_odbcinst($post_odbcinst_data){
    $myFile = "/etc/asterisk/odbcinst_temp.txt";
    $fh = fopen($myFile, 'w') or die("can't open file");
    fwrite($fh, $post_odbcinst_data);
    fclose($fh);
    exec("python /var/lib/asterisk/bin/rpclient.py CONFIG odbcinst");
}

function draw_table() {
	$table = table(odbcadmin_list());
	//$table->setIdField('username', true); // set and display id field
	//$table->setImagePrefix('user');
	$table->show();
}

//--------- Check DB Connect ----------------//
function check_db_connect($dsname, $username, $password){
    // isql -v dsn username pass
    $cflag = False;
    $error = "";
    exec("isql -v $dsname $username $password", $result);
    foreach($result as $l){
        $pos = strpos($l,'Connected');
        $error .= " " . $l;
        if ($pos === false) {
            $cflag = $cflag ;
        }
        else {
            $cflag = True;   
        }
    }
    if ($cflag) return "OK";
    else return $error;
}

//--------- Check XMLRPC server connect ----------------//
function rpc_status(){
    $out = "";
    // note that the part at the end 2>&1 will redirect error output to standard output
    exec("python /var/lib/asterisk/bin/rpclient.py PING 1 2>&1", $result);
    foreach($result as $l){
        $out .= " " . $l;
    }
    if ($out === "") return True;
    else return False;
    
}

//--------- Setup Controler ----------------//
function setup_odbc(){
    exec("python /var/lib/asterisk/bin/rpclient.py SETUP ODBC");
    return True;
}

function setup_asodbc(){
    exec("python /var/lib/asterisk/bin/rpclient.py SETUP ASODBC");
    return True;
}

//--------- Check Install Status ----------------//
function find_odbc(){
    /* 
    check on 32/64 bits system ubuntu/centos
    /usr/lib/libodbcinst.so /usr/lib/libodbc.so
    /usr/lib64/libodbcinst.so /usr/lib64/libodbc.so
    */
    if (file_exists('/usr/lib/libodbc.so') && file_exists('/usr/lib/libodbcinst.so')){
        return True;
    }
    else if(file_exists('/usr/lib64/libodbc.so') && file_exists('/usr/lib64/libodbcinst.so')){
        return True;
    }
    else  return False;
}

function find_asodbc(){
    /* 
    /usr/lib/asterisk/modules/func_odbc.so
    */
    if (file_exists('/usr/lib/asterisk/modules/func_odbc.so') && file_exists('/usr/lib/asterisk/modules/res_odbc.so')){
        return True;
    }
    else  return False;
}

function find_odbcdriver($driver){
    /* 
    Find if we can support what driver (mysql, PostgreSQL, mssql ...)
    */

    switch ($driver) {
        case 'MySQL':
            // /usr/lib/libmyodbc3.so  /usr/lib/odbc/libmyodbc.so   /usr/lib64/libmyodbc3.so;
            if(file_exists('/usr/lib/libmyodbc3.so') || file_exists('/usr/lib/odbc/libmyodbc.so') || file_exists('/usr/lib64/libmyodbc3.so'))
                return True;
            else
                return False;   
         
        case 'MSSQL':
            if(file_exists('/usr/lib/libtdsodbc.so') || file_exists('/usr/lib64/libtdsodbc.so') || file_exists('/usr/lib/odbc/libtdsS.so') || file_exists('/usr/local/lib/libtdsodbc.so')) {
                return True;   
            }
            else
                return False;   

        case 'PostgreSQL':
            // /usr/lib/libodbcpsql.so /usr/lib/libodbcpsqlS.so
            if(file_exists('/usr/lib/libodbcpsql.so') || file_exists('/usr/lib64/libodbcpsql.so') || file_exists('/usr/lib/odbc/libodbcpsqlS.so')){

                $out = "";
                exec("psql --help", $result);
                foreach($result as $l){
                    $out .= " " . $l;
                }
                if ($out == "") return False;   // this mean psql command does not exist
                else return True;       
            }
            else
                return False;  

        case 'SQLite':
            return False;
        default:
            return False;
    }
}

function setup_odbcdriver($driver){
    /* 
    Install odbc driver (mysql, sqlite, mssql ...)
    */

    switch ($driver) {
        case 'MySQL':
        // yum -y mysql-connector-odbc mysql mysql-server mysql-devel
            exec("python /var/lib/asterisk/bin/rpclient.py SETUP MySQL");
            return True;   
         
        case 'MSSQL':
            exec("python /var/lib/asterisk/bin/rpclient.py SETUP MSSQL");
            return True;

        case 'PostgreSQL':
        // yum -y install postgresql postgresql-server postgresql-odbc
            exec("python /var/lib/asterisk/bin/rpclient.py SETUP PostgreSQL");
            return True;  
    }
}

function create_base_drivers() {
}

?>
