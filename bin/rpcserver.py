#!/usr/bin/env python
# vim: ai ts=4 sts=4 et sw=4 encoding=utf-8
#
# FreePBX ODBCAdmin Module
#
# Copyright (c) 2011, VoiceNation, LLC.
#
# This program is free software, distributed under the terms of
# the GNU General Public License Version 2.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU Affero General Public License for more details.
#
# You should have received a copy of the GNU Affero General Public License
# along with this program. If not, see <http://www.gnu.org/licenses/>.
#

# system import
import signal
import subprocess
import sys
import os
from os.path import abspath, dirname, join

from SimpleXMLRPCServer import SimpleXMLRPCServer
from SimpleXMLRPCServer import SimpleXMLRPCRequestHandler

ini_path = os.path.dirname(__file__)
sys.path.append(ini_path)
odbc_file = '/etc/odbc.ini'

from ini import INIConfig
'''
Plan: odbcmodule -> write ini file by php -> rpcserver check + add/edit/delete to odbc.ini
print "-------------------------------"
from iniparse import INIConfig
cfg = INIConfig()
cfg.playlist.expand_playlist = 'True'
cfg.ui.display_clock = 'True'
cfg.ui.display_qlength = 'True'
cfg.ui.width = '150'
print dir(cfg)
print "-------------------------------"
cfgx = INIConfig(open('/etc/odbc.ini'))
print dir(cfgx)
print "-------------------------------"
'''

# Restrict to a particular path.
class RequestHandler(SimpleXMLRPCRequestHandler):
    rpc_paths = ('/RPC2',)

# Create server
server = SimpleXMLRPCServer(("localhost", 8988),
                            requestHandler=RequestHandler)
server.register_introspection_functions()

def _DB2C(): # Parse Database to configure file (asteisk_odbcadmin to file odbc.ini)
    return True

def _C2DB(): # Parse configure file to database (asteisk_odbcadmin to file odbc.ini)
    return True

def _NewConf(): # add a new odbc.ini config
    return True

def _EditConf(): # edit an exist odbc.ini config
    return True

def DoConfigure(mode):
    print "--Now Reconfig odbc seting at /etc/odbc.ini--"
    if mode == "odbc":
        os.system("cp -f /etc/asterisk/odbc_temp.txt /etc/odbc.ini")
        os.system("rm -f /etc/asterisk/odbc_temp.txt")
    elif mode == "odbcinst":
        os.system("cp -f /etc/asterisk/odbcinst_temp.txt /etc/odbcinst.ini")
        os.system("rm -f /etc/asterisk/odbcinst_temp.txt")
    elif mode == "sync":
        os.system("cp -f /etc/odbc.ini /etc/asterisk/odbc_temp.txt")
        #os.system("rm -f /etc/asterisk/odbc_temp.txt")
    return True
server.register_function(DoConfigure, 'DoConfigure')


def update_res_odbc():
    # when to call: odbc object add/edit
    # input: data dsn 
    # output: re-config res_odbc.conf
    # cp to temp_file replace "=>" with "=" (sed -i 's/=>/=/g' res_odbc.conf)
    # do check conf (add/update)
    # write to temp_file + recheck
    # cp to current res_odbc.conf
    return True

def do_update_section(sname, section, odbc_cfg):
    print "--update conf--"
    for key in section:
        value = section._getitem(key)
        odbc_cfg._sections[sname].__setitem__(key.title(), value)
    f = open(odbc_file, 'w')
    print >>f, odbc_cfg
    f.close()
    return 1

def do_add_section(odbc_cfg, ref_cfg):
    print "--add new conf--"
    for r_section in ref_cfg._sections:
        odbc_cfg._new_namespace(r_section)
        for key in ref_cfg._sections[r_section]:
            value = ref_cfg._sections[r_section]._getitem(key)
            odbc_cfg._sections[r_section].__setitem__(key.title(),value)
    # write new configure data
    f = open(odbc_file, 'w')
    print >>f, odbc_cfg
    f.close()


def Configure_Change(reference):
    print "--Do Sync to odbc.ini--"
    odbc_cfg = INIConfig(open(odbc_file))
    ref_cfg = INIConfig(open(reference))
    flag = 0
    for o_section in odbc_cfg._sections:
        for r_section in ref_cfg._sections:
            if r_section == o_section: 
                flag = do_update_section(o_section, ref_cfg._sections[o_section], odbc_cfg)
    if flag==0:
        do_add_section(odbc_cfg, ref_cfg)
    os.system("rm -f %s"%reference)
    # for delete blank line
    os.system("sed -i '/^$/d' %s" %odbc_file)
    return True

server.register_function(Configure_Change, 'Configure_Change')

def Delete_Section(sname):
    print "--Do delete section at odbc.ini--"
    odbc_cfg = INIConfig(open(odbc_file))
    for o_section in odbc_cfg._sections:
        if sname == o_section:
            print "now delete section %s"%o_section
            odbc_cfg.__delitem__(o_section)
            ##  change file odbc.ini again
            f = open(odbc_file, 'w')
            print >>f, odbc_cfg
            f.close()
    # for delete blank line
    os.system("sed -i '/^$/d' %s" %odbc_file)

server.register_function(Delete_Section, 'Delete_Section')

def __setupodbc():
    print "--Now Setting up ODBC server--"
    try: # Install if unix os is Ubuntu
        os.system("apt-get -y install unixODBC unixODBC-dev libmyodbc")
    except:
        pass
    try: # Install if unix os is Centos
        os.system("yum -y install unixODBC unixODBC-devel mysql-connector-odbc")
        version = 0
        stdout_handle = os.popen("rpm -qa | grep asterisk", "r")
        text = stdout_handle.read()
        if (text.find("asterisk16") >= 0):
           version = 16
        elif (text.find("asterisk14") >= 0):
           version = 14
        elif (text.find("asterisk18") >= 0):
           version = 18
        if version > 0:
            os.system("yum -y install asterisk%s-odbc" %version)
    except:
        pass
    os.system("touch /etc/odbc.ini")
    os.system("touch /etc/odbcinst.ini")
    return True

def __setupasodbc():
    print "--Now Setting up ASTERISK ODBC module--"
    try: # Install if unix os is Centos
        os.system("yum -y install libtool-ltdl libtool-ltdl-devel ")
    except:
        pass
    return True

def __setupPostgreSQL():
    print "--Now Setting up PostgreSQL ODBC Driver--"
    try: # Install if unix os is Centos
        os.system("yum -y install postgresql postgresql-server postgresql-odbc")
    except:
        pass
    try: # Install if unix os is Ubuntu
        os.system("apt-get -y install postgresql postgresql-client postgresql-contrib odbc-postgresql")
    except:
        pass
    return True

def __setupMySQL():
    print "--Now Setting up MySQL ODBC driver--"
    try: # Install if unix os is Centos
        os.system("yum -y install mysql-connector-odbc mysql mysql-server mysql-devel")
    except:
        pass
    try: # Install if unix os is Ubuntu
        os.system("apt-get -y install libmyodbc mysql-server mysql-client")
    except:
        pass
    return True

def __doFreeTDSconfig():
    return True

def __setupMSSQL():
    print "--Now Setting up MSSQL FreeTDS ODBC driver--"
    try: # Install if unix os is Centos
        os.system("yum -y install freetds freetds-devel php-mssql")
    except:
        pass
    try: # Install if unix os is Ubuntu
        os.system("apt-get -y install freetds-dev freetds-bin php5-mssql")
    except:
        pass
    __doFreeTDSconfig()
    return True

def DoSetup(key):
    if key == "ODBC":
        __setupodbc()
    elif key == "ASODBC":
        __setupasodbc()
    elif key == "MySQL":
        __setupMySQL()
    elif key == "MSSQL":
        __setupMSSQL()
    elif key == "PostgreSQL":
        __setupPostgreSQL()
    return True
server.register_function(DoSetup, 'DoSetup')

def ping():
    print "Check xmlrpc server alive"
    return True
server.register_function(ping, 'ping')

#------------------------------------------------------------------
# For handeling file res_odbc.conf
#------------------------------------------------------------------
def _res_odbc_delete(sname):
    temp_file = "/etc/asterisk/res_odbc.conf.temp"
    os.system("sed -i 's/=>/=/g' %s"%temp_file)
    print "--Do delete section at res_odbc--"
    res_odbc_cfg = INIConfig(open('/etc/asterisk/res_odbc.conf.temp'))
    print res_odbc_cfg
    for section in res_odbc_cfg._sections:
        if sname == section:
            print "now delete section %s in res_odbc"%section
            res_odbc_cfg.__delitem__(section)
            f = open(temp_file, 'w')
            print >>f, res_odbc_cfg
            f.close()
            print "section %s in res_odbc is deleted"%section
            return True
    # for delete blank line
    os.system("sed -i '/^$/d' %s" %temp_file)
    return True

def res_odbc_delete(sname):

    os.system("cp -f /etc/asterisk/res_odbc.conf /etc/asterisk/res_odbc.conf.temp")
    # ------------------------process delete sesion----------------------
    _res_odbc_delete(sname)
    # ------------------------change format again------------------------
    print "now change temp file format again"
    os.system("sed -i 's/=/=>/g' /etc/asterisk/res_odbc.conf.temp")
    os.system("cp -f /etc/asterisk/res_odbc.conf.temp /etc/asterisk/res_odbc.conf")  
    os.system("rm -f /etc/asterisk/res_odbc.conf.temp")   
    return True
server.register_function(res_odbc_delete, 'res_odbc_delete')

#------------------------------------------------------------------

#------------------------------------------------------------------
# For handeling file odbcinst.ini
#------------------------------------------------------------------
def driver_delete(sname):
    print "now delete a driver"
    odbcinst_file = "/etc/odbcinst.ini"
    odbcinst_cfg = INIConfig(open(odbcinst_file))
    for section in odbcinst_cfg._sections:
        if sname == section:
            print "now delete section %s"%section
            odbcinst_cfg.__delitem__(section)
            ##  change file odbc.ini again
            f = open(odbcinst_file, 'w')
            print >>f, odbcinst_cfg
            f.close()
    # for delete blank line
    os.system("sed -i '/^$/d' %s" %odbcinst_file)
    return True

server.register_function(driver_delete, 'driver_delete')

def driver_add(data):
    print "now add a new driver"
    data_array = data.split("-")
    print data_array
    name = data_array[0]
    driver = data_array[1]
    setup = data_array[2]
    description = data_array[3]
    # now adding
    odbcinst_file = "/etc/odbcinst.ini"
    odbcinst_cfg = INIConfig(open(odbcinst_file))

    odbcinst_cfg._new_namespace(name)
    odbcinst_cfg._sections[name].__setitem__('Driver',driver)
    odbcinst_cfg._sections[name].__setitem__('Setup',setup)
    odbcinst_cfg._sections[name].__setitem__('Description',description)
    print "now re-write odbcinst_file"
    f = open(odbcinst_file, 'w')
    print >>f, odbcinst_cfg
    f.close()
    # for delete blank line
    os.system("sed -i '/^$/d' %s" %odbcinst_file)
    return True
server.register_function(driver_add, 'driver_add')
#------------------------------------------------------------------
# Run the server's main loop
server.serve_forever()
