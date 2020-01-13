<?
// -----------------------------------------------------------------------------
// 2B_milight : interface de pilotage des ampoules milight v4
// -----------------------------------------------------------------------------
// basé sur  
// 	- https://github.com/yasharrashedi/LimitlessLED/blob/master/Milight.php
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
// &vars=[VAR1]
//   VAR1 :  [ip:port,group,type]
//   		- ip:port  : ip + port envoi + port reception du bridge v4
//   		- group    : Identifiant du groupe [0|1|2|3|4] 0 = tous
//   		- type 	   : rgb | rgbw
// &cmd=
//  on
//	off
//	bri : [0 à 100]
//	bridown
//	briup
//  brimax
//  brimin
//	towhite
//	tonight
//	color : [0 à 100],[0 à 100],[0 à 100]]
//	mode 
// 	slowest
// 	slower
// 	faster
//  fastest
//  tempdown
//  tempup
// [&set=(on "0 à 1")]
// [&api=(onapi "Code API")]
//
// -----------------------------------------------------------------------------
// Obsolete : [&cmd=[discomode, discoslower,discofaster, warminc, coolinc]
//            [&bri= | &color= ] 
// -----------------------------------------------------------------------------



sdk_header("text/xml");
echo "<milight4>\r\n";

// Lecture ip + port
$vars = getArg('vars', false, ',0,');
$varsar = explode(',',$vars);
$hostp = $varsar[0];
$hostar = explode(":",$hostp); 

$host = '';  // ip 
$port = '8899'; // port 8899

if (count($hostar) > 0) $host=$hostar[0];
if (count($hostar) > 1) $port=$hostar[1];

$group = $varsar[1]; // 0 - all ou 1,2,3,4
$type = strtolower($varsar[2]); // rgbw ou white
$cmdar = strtolower(getArg('cmd',false, '')); // commande

$cmdar = explode(':', $cmdar);
$cmd = '';
$cmdparam = '';
if (count($cmdar) > 0) $cmd=$cmdar[0];
if (count($cmdar) > 1) $cmdparam=$cmdar[1];

if ($cmd == 'discomode') $cmd = 'mode';
if ($cmd == 'discoslower') $cmd = 'slower';
if ($cmd == 'discofaster') $cmd = 'faster';
if ($cmd == 'coolinc') $cmd = 'tempdown';
if ($cmd == 'warminc') $cmd = 'tempup';

if ($cmd == 'bri' && $cmdparam !== '')			$bri = $cmdparam;  		else $bri = getArg('bri',false, -1);
if ($cmd == 'color' && $cmdparam !== '') 		$color = $cmdparam;  	else $color = getArg('color',false, '');

$seton= getArg("set",false, '0'); //on
$apion= getArg("api",false, '0'); //onapi

$perreur = '';
if ($host =='') $perreur .= "[host vide] ";	// host vide
if ($group < 0 || $group > 4) $perreur .= "[groupe ($group) incorrect] ";		
if ( $cmd=='bri' && (($bri < 0) || ($bri > 100)))	$perreur .= "[bri ($bri) incorrect] ";	
if ( $cmd=='color' && (count(explode(',', $color)) != 3))	$perreur .= "[color ($color) incorrect] ";	

 if (!(($type == 'rgbw') || ($type == 'white'))) $perreur .= "[type ($type) incorrect] ";	// type incorrect

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
		'bri' => array(0x4e, 0x00), // utiliser le 2eme octet pour la valeur  // voir 0x4e !
        'mode' => array(0x4d, 0x00),
        'slower' => array(0x43, 0x00),
        'faster' => array(0x44, 0x00),
        'color' => array(0x40, 0x00) // utiliser le 2eme octet pour la valeur 
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
		'tempdown' => array(0x3f, 0x00),
        'tempup' => array(0x3e, 0x00),        
		);

/*
// Ampoules et contoleur RGB (ancienne génération) => pas de zone
$rgbCodes = array(
		'off'  => array(0x21, 0x00),
		'on'  => array(0x22, 0x00),
		'color'  => array(0x20, 0x00),		
		'bridown'  => array(0x24, 0x00),
		'briup'  => array(0x23, 0x00),
		'slower'  => array(0x26, 0x00),
		'faster'  => array(0x25, 0x00),		
		'modedown' => array(0x28, 0x00),
		'modeup' => array(0x27, 0x00),
		);
*/	
		
echo "<input>\r\n";
echo "<host>".$host."</host>\r\n";
echo "<port>".$port."</port>\r\n";
echo "<group>".$group."</group>\r\n";
echo "<type>".$type."</type>\r\n";
echo "<cmd>".$cmd."</cmd>\r\n";
echo "<bri>".$bri."</bri>\r\n";
echo "<color>".$color."</color>\r\n";
echo "<seton>".$seton."</seton>\r\n";
echo "<apion>".$apion."</apion>\r\n";
echo "</input>\r\n";

echo "<tmt>\r\n";

if ($perreur == '')
{	

	switch ($type) 
	{
		case 'rgbw':
			echo "<rgbw>\r\n";
			switch ($cmd)
			{
					case 'on':
					case 'off':				
					case 'towhite':
						sdk_milight_send($host, $port, $rgbwCodes[$group.$cmd]);					
						break;				
					case 'tonight':
						sdk_sendonoroff($host, $port, $group, 'off', $rgbwCodes);
						sdk_milight_send($host, $port, $rgbwCodes[$group.$cmd]);					
						break;
					case 'color':
						$rgb = explode(',', $color);
						$hsl = sdk_milight_rgbToHsl(floor($rgb[0] * 2.54), floor($rgb[1] * 2.54), floor($rgb[2] * 2.54));
						$micolor = sdk_milight_hslToMilightColor($hsl);
						sdk_sendonoroff($host, $port, $group, 'on', $rgbwCodes);
						sdk_milight_send($host, $port, array(0x40, $micolor));
						break;
					case 'brimax':
					case 'brimin':
					case 'bri':
						if ($cmd == 'brimin') $bri=0;
						if ($cmd == 'brimax') $bri=100;
						if ($bri !== '')
						{
							if ($bri <0) $bri = 0;
							if ($bri > 100) $bri = 100;
							$bri = round(2+(($bri/100)*25));
							sdk_sendonoroff($host, $port, $group, 'on', $rgbwCodes);
							$briCodes = $rgbwCodes['bri'];
							$briCodes[1] = $bri;
							sdk_milight_send($host, $port, $briCodes);
						}
						break;
					case 'mode':
						sdk_sendonoroff($host, $port, $group, 'on', $rgbwCodes);	
						sdk_milight_send($host, $port, $rgbwCodes[$cmd]);					
						break;
					case 'slowest':				
					case 'slower':
					case 'faster':
					case 'fastest':
						sdk_sendonoroff($host, $port, $group, 'on', $rgbwCodes);
						$imax = 1;
						$cmdtmp = $cmd;
						if ($cmd == 'slowest' || $cmd == 'fastest')  $imax = 8;
						if ($cmd == 'slowest')  $cmdtmp = 'slower';
						if ($cmd == 'fastest')  $cmdtmp = 'faster';
						for ($i=0 ; $i < $imax ; $i++)
						{
							sdk_milight_send($host, $port, $rgbwCodes[$cmdtmp]);
							sdk_sleepms(50);						
						}
						
						break;		
					default:
						$perreur .= "[commande $cmd inconnu] ";	
					
			}		
			echo "</rgbw>\r\n";		
			break;
			
		case 'white':
			echo "<white>\r\n";		
			switch ($cmd)
			{
					case 'on':
					case 'off':				
					case 'brimax':					
						sdk_milight_send($host, $port, $whiteCodes[$group.$cmd]);					
						break;	
					case 'tonight':
						sdk_sendonoroff($host, $port, $group, 'off', $whiteCodes);
						sdk_milight_send($host, $port, $whiteCodes[$group.$cmd]);					
						break;	
					case 'bridown':
					case 'briup':				
					case 'tempdown':
					case 'tempup':
						sdk_sendonoroff($host, $port, $group, 'on', $whiteCodes);
						sdk_milight_send($host, $port, $whiteCodes[$cmd]);					
						break;	
					case 'brimin':
						sdk_sendonoroff($host, $port, $group, 'on', $whiteCodes);
						 for ($i = 0; $i < 10; $i++) {						 
							 sdk_milight_send($host, $port, $whiteCodes['bridown']);
							 sdk_sleepms(50);
						   }
						break;
					default:
						$perreur .= "[commande $cmd inconnu] ";						
			}
			echo "</white>\r\n";		
			break;			
	}	
}

// fixe la lampe a on si elle etait a off
if ($perreur != '') 
{
	echo "<error>$perreur</error>\r\n";	
}
else if ($seton != 0)
{
	echo "<seton>\r\n";
	$onitem=getValue($apion);
    if ($onitem["value"] == 0)	
	{
		echo "<setValue>-1</setValue>\r\n";
		setValue($apion, -1, false, true);		
	}
	echo "</seton>\r\n";
}

echo "</tmt>\r\n";
echo "</milight4>\r\n";

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
	$command_repeats = 1;
	$command[] = 0x55; // last byte is always 0x55, will be appended to all commands
	
	$trame = vsprintf (str_repeat('%c', count($command)), $command);		
	echo "<send>";
     
	for($repetition=0; $repetition<$command_repeats; $repetition++) 
	{
		if ($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) 
		{
    		socket_sendto($socket, $trame, strlen($trame), 0, $host, $port);	
    		socket_close($socket);
			if ($repetition == 0)
			{
				echo "<msg>".bin2hex($trame)."</msg>";
				echo "<msglen>".strlen($trame)."</msglen>";			
			}
			usleep(10000); //wait 10ms * 10 before sending next command
		}
	}
	echo "</send>\r\n";
}

 function sdk_milight_rgbToHsl($r, $g, $b)
{
	echo "<rgbToHsl>";
	echo "<rgb>".$r.",".$g.",".$b."</rgb>";
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
	echo "<hsl>".$h.",".$s.",".$l."</hsl>";
	echo "</rgbToHsl>\r\n";
	return array($h, $s, $l);
}

function sdk_milight_hslToMilightColor($hsl)
{
        $color = (256 + 176 - (int)($hsl[0] / 360.0 * 255.0)) % 256;
        return $color + 0xfa;
 }

function sdk_sleepms($ms)
{
	$ms = abs($ms);
	if( $ms > 5000) $ms = 5000;
	for ($i = 0; $i < $ms ; $i++)
	{
		usleep(1000); //wait 1ms 
	}
}


?>