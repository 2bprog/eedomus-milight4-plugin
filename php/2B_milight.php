<?
// -----------------------------------------------------------------------------
// 2B_milight : interface de pilotage des ampoules milight v4
// -----------------------------------------------------------------------------
// basé sur  Milight/LimitlessLED/EasyBulb PHP API 
// https://github.com/yasharrashedi/LimitlessLED/blob/master/Milight.php
// -----------------------------------------------------------------------------
/*
 * Milight/LimitlessLED/EasyBulb PHP API
 * The MIT License (MIT)
 *
 * Copyright (c) 2014 Yashar Rashedi <info@rashedi.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
*/
// -----------------------------------------------------------------------------
//
// ?vars=[VAR1]
// &cmd=[on|off|tonight|towhite|bri|brimax|brimin|discomode|discoslower|discofaster|color]
// [&bri=[0 à 100]
// [&color=[0 à 100],[0 à 100],[0 à 100]]
// [&set=(on)]
// [&api=(onapi)]

// -----------------------------------------------------------------------------
//

// vars : 
//  VAR1 :  [ip:port,group,type]
//   - ip:port  : ip + port du bridge v4
//   - group    : Identifiant du groupe [0|1|2|3|4] 0 = tous
//   - type     : [rgbw|white]
//
// type : 

// &vars=[ip:port,group,type]
// ip:port : 
// group : [0|1|2|3|4]
// type  : [rgbw|white]

// type=[rgbw]
//		cmd=[on,off,tonight,towhite]
//
// type=[white]
//		cmd=[on,off,tonight,towhite]

// Lecture ip + port
$vars = getArg('vars', false, ',0,');
$varsar = explode(',',$vars);

$hostp = $varsar[0];
$group = $varsar[1]; // 0 - all ou 1,2,3,4
$type = $varsar[2]; // rgbw ou white

$cmd   = getArg("cmd",false, ''); // commande
$bri = getArg("bri",false, '');  // luminosité
$color = getArg("color",false, ''); // couleur au format eedomus
$seton= getArg("set",false, '0'); //on
$apion= getArg("api",false, '0'); //onapi

// debug
if ($hostp == '')
{
       $hostp = '10.66.253.200:8899';
       $type = 'rgbw';
       $group = '1';
       $cmd="tonight";
}

$hostar = explode(":",$hostp); 
$host = '';  // ip 
if (count($hostar) > 0) $host=$hostar[0];
$port = '80'; // port 8899
if (count($hostar) > 1) $port=$hostar[1];
	

$rgbwCodes = array(
        //RGBW Bulb commands
		
		'0off' => array(0x41, 0x00),
        '1off' => array(0x46, 0x00),
        '2off' => array(0x48, 0x00),
        '3off' => array(0x4a, 0x00),
        '4off' => array(0x4c, 0x00),        
		'0on' => array(0x42, 0x00),
        '1on' => array(0x45, 0x00),
        '2on' => array(0x47, 0x00),
        '3on' => array(0x49, 0x00),
        '4on' => array(0x4B, 0x00),
        '0tonight' => array(0xC1, 0x00),
        '1tonight' => array(0xC6, 0x00),
        '2tonight' => array(0xC8, 0x00),
        '3tonight' => array(0xCA, 0x00),
        '4tonight' => array(0xCC, 0x00),
		'0towhite' => array(0xc2, 0x00),
        '1towhite' => array(0xc5, 0x00),
        '2towhite' => array(0xc7, 0x00),
        '3towhite' => array(0xc9, 0x00),
        '4towhite' => array(0xcb, 0x00),
		
		'bri' => array(0x0e, 0x00), // utiliser le 2eme octet pour la valeur 
		'brimax' => array(0x0e, 0x1b),
		'brimin' => array(0x0e, 0x02), 
        'discomode' => array(0x4d, 0x00),
        'discoslower' => array(0x43, 0x00),
        'discofaster' => array(0x44, 0x00),
        'color' => array(0x40, 0x00) // utiliser le 2eme octet pour la valeur 
/*      'colortoviolet' => array(0x40, 0x00),
        'colortoroyalblue' => array(0x40, 0x10),
        'colortobabyblue' => array(0x40, 0x20),
        'colortoaqua' => array(0x40, 0x30),
        'colortoroyalmint' => array(0x40, 0x40),
        'colortoseafoamgreen' => array(0x40, 0x50),
        'colortogreen' => array(0x40, 0x60),
        'colortolimegreen' => array(0x40, 0x70),
        'colortoyellow' => array(0x40, 0x80),
        'colortoyelloworange' => array(0x40, 0x90),
        'colortoorange' => array(0x40, 0xa0),
        'colortored' => array(0x40, 0xb0),
        'colortopink' => array(0x40, 0xc0),
        'colortofusia' => array(0x40, 0xd0),
        'colortolilac' => array(0x40, 0xe0),
        'colortolavendar' => array(0x40, 0xf0),	
        */
		);
		
$whiteCodes = array(
		
		'0off' => array(0x39, 0x00),
        '1off' => array(0x3b, 0x00),
		'2off' => array(0x33, 0x00),
		'3off' => array(0x3a, 0x00),
		'4off' => array(0x36, 0x00),
        '0on' => array(0x35, 0x00),		
		'1on' => array(0x38, 0x00),
		'2on' => array(0x3d, 0x00),
        '3on' => array(0x37, 0x00),        
        '4on' => array(0x32, 0x00),
        '0tonight' => array(0xbb, 0x00),
        '1tonight' => array(0xbb, 0x00),
        '2tonight' => array(0xb3, 0x00),
        '3tonight' => array(0xba, 0x00),
        '4tonight' => array(0xb6, 0x00),
		'0brimax' => array(0xb5, 0x00),		        
        '1brimax' => array(0xb8, 0x00),
        '2brimax' => array(0xbd, 0x00),
        '3brimax' => array(0xb7, 0x00),
        '4brimax' => array(0xb2, 0x00),
        'briup' => array(0x3c, 0x00),
        'bridown' => array(0x34, 0x00),	       
        'warminc' => array(0x3e, 0x00),
        'coolinc' => array(0x3f, 0x00)
		);
		
$type = strtolower($type);
$cmd = strtolower($cmd);
$command = '';
switch ($type) 
{
    case 'rgbw':
		switch ($cmd)
		{
				case 'on':
				case 'off':				
				case 'towhite':
					$command = $group.$cmd;	
					break;				
				case 'tonight':
					sdk_sendonoroff($host, $port, $group, 'off', $rgbwCodes);
					$command = $group.$cmd;					
					break;
				case 'color':
				    $rgb = explode(',', $color);
				    $hsl = sdk_milight_rgbToHsl(floor($rgb[0] * 2.54), floor($rgb[1] * 2.54), floor($rgb[2] * 2.54));
				    $micolor = sdk_milight_hslToMilightColor($hsl);
				    sdk_sendonoroff($host, $port, $group, 'on', $rgbwCodes);
                    sdk_milight_send($host, $port, array(0x40, $micolor));
                    break;
				case 'bri':
				    if ($bri != '')
				    {
				        if ($bri <0) $bri = 0;
				        if ($bri > 100) $bri = 100;
				        $bri = round(2+(($bri/100)*25));
                        sdk_sendonoroff($host, $port, $group, 'on', $rgbwCodes);
                        sdk_milight_send($host, $port, array(0x4e, $bri));
				    }
				    break;
				case 'brimax':
				case 'brimin':
				case 'discomode':
				case 'discoslower':
				case 'discofaster':
					sdk_sendonoroff($host, $port, $group, 'on', $rgbwCodes);
					$command = $cmd;		
					break;					
				
        }
		echo '*'.$command;
		if ($command != '') sdk_milight_send($host, $port, $rgbwCodes[$command]);					
        break;
        
    case 'white':
		switch ($cmd)
		{
				case 'on':
				case 'off':				
				case 'brimax':
					$command = $group.$cmd;
					break;	
				case 'tonight':
					sdk_sendonoroff($host, $port, $group, 'off', $whiteCodes);
					$command = $group.$cmd;
					break;	
				case 'briup':
				case 'bridown':
				case 'warminc':
				case 'coolinc':
				    sdk_sendonoroff($host, $port, $group, 'on', $whiteCodes);
					$command = $cmd;
					break;	
				case 'brimin':
				    sdk_sendonoroff($host, $port, $group, 'on', $whiteCodes);
				     for ($i = 0; $i < 10; $i++) {
				         sdk_milight_send($host, $port, $whiteCodes['bridown']);
                       }
				    break;
					
        }
		if ($command != '') sdk_milight_send($host, $port, $whiteCodes[$command]);					
        break;
		
	
}	
// fixe la lampe a on si elle etait a off
if ($seton != 0)
{
	$onitem=getValue($apion);
    if ($onitem["value"] == 0)	setValue($apion, -1, false, true);		
}

// $onoroff = 'on' ou 'off'
function sdk_sendonoroff($host, $port, $group, $onoroff, Array $codes)
{
	$ret = false;
	$command = $codes[$group.$onoroff];
	if (isset($command))
	{
		sdk_milight_send($host, $port, $command);
		$ret = true;
	}
	return $ret;
}


function sdk_milight_send($host, $port, Array $command)
{
	$command_repeats = 10;
	$command[] = 0x55; // last byte is always 0x55, will be appended to all commands
	$message = vsprintf (str_repeat('%c', count($command)), $command);

	for($repetition=0; $repetition<$command_repeats; $repetition++) 
	{
		if ($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) 
		{
    		socket_sendto($socket, $message, strlen($message), 0, $host, $port);	
    		socket_close($socket);
    	    usleep(10000); //wait 10ms * 10 before sending next command
		}
	}
}

 function sdk_milight_rgbToHsl($r, $g, $b)
{
	$r = $r / 255;
	$g = $g / 255;
	$b = $b / 255;
	$max = max($r, $g, $b);
	$min = min($r, $g, $b);
	$l = ($max + $min) / 2;
	$d = $max - $min;
	$h = '';
	if ($d == 0) {
		$h = $s = 0;
	} else {
		$s = $d / (1 - abs(2 * $l - 1));
		switch ($max) {
			case $r:
				$h = 60 * fmod((($g - $b) / $d), 6);
				if ($b > $g) {
					$h += 360;
				}
				break;
			case $g:
				$h = 60 * (($b - $r) / $d + 2);
				break;
			case $b:
				$h = 60 * (($r - $g) / $d + 4);
				break;
		}
	}
	return array($h, $s, $l);
}

function sdk_milight_hslToMilightColor($hsl)
{
        $color = (256 + 176 - (int)($hsl[0] / 360.0 * 255.0)) % 256;
        return $color + 0xfa;
 }


/*
 public function sdk_milight_rgbHexToRgb($hexColor)
{
	$hexColor = ltrim($hexColor, '#');
	$hexColorLenghth = strlen($hexColor);
	if ($hexColorLenghth != 8 && $hexColorLenghth != 6) {
		throw new \Exception('Color hex code must match 8 or 6 characters');
	}
	if ($hexColorLenghth == 8) {
		$r = hexdec(substr($hexColor, 2, 2));
		$g = hexdec(substr($hexColor, 4, 2));
		$b = hexdec(substr($hexColor, 6, 2));
		if (($r == 0 && $g == 0 && $b == 0) || ($r == 255 && $g == 255 && $b == 255)) {
			throw new \Exception('Color cannot be black or white');
		}
		return array($r, $g, $b);
	}
	$r = hexdec(substr($hexColor, 0, 2));
	$g = hexdec(substr($hexColor, 2, 2));
	$b = hexdec(substr($hexColor, 4, 2));
	if (($r == 0 && $g == 0 && $b == 0) || ($r == 255 && $g == 255 && $b == 255)) {
		throw new \Exception('Color cannot be black or white');
	}
	return array($r, $g, $b);
}
*/

?>