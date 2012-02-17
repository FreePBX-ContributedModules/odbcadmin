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

function delxlines($txtfile, $numlines)
{
if (file_exists("temp.txt"))
{
unlink("temp.txt");
}
$arrayz = file("$txtfile");
$tempWrite = fopen("temp.txt", "w");
for ($i = $numlines; $i < count($arrayz); $i++)
{
fputs($tempWrite, "$arrayz[$i]");
}
fclose($tempWrite);
copy("temp.txt", "$txtfile");
unlink("temp.txt");
}

exec("sed -i '/python/d' $filename");

?>
