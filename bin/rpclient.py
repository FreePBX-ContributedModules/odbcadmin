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

import sys
import os
import subprocess 
import xmlrpclib

def odbc():
    srv = xmlrpclib.Server('http://localhost:8988')
    media = sys.argv[2]
    mode = sys.argv[1]
    try:
        media_key = media.split("=")[0]
        media_val = media.split("=")[1]
    except:
        pass
    if mode == "CONFIG":
        if media_key == "sync":
            action = srv.Configure_Change(media_val)
        elif media_key == "delete":
            action = srv.Delete_Section(media_val)
        else:   
            action = srv.DoConfigure(media) 

    elif mode == "SETUP":
        action = srv.DoSetup(media)

    elif mode == "RESODBC":
        if media_key == "del":
            action = srv.res_odbc_delete(media_val)

    elif mode == "DRIVER":
        if media_key == "del":
            action = srv.driver_delete(media_val)
        elif media_key == "add":
            action = srv.driver_add(media_val)

    elif mode == "PING":
        try:
            action = srv.ping()
        except:
            print 'Failed to connect to xmlrpc server!'
if __name__ == "__main__":
    odbc()

