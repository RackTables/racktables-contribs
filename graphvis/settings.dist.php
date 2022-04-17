<?php
	/*
	This file is part of Graphvis.

    Graphvis is free software: you can redistribute it and/or modify it under
	the terms of the GNU General Public License as published by the Free
	Software Foundation, either version 3 of the License, or (at your option)
	any later version.

    Graphvis is distributed in the hope that it will be useful, but WITHOUT ANY
	WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
	FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
    
	You should have received a copy of the GNU General Public License along with
	Graphvis. If not, see <https://www.gnu.org/licenses/>.
	*/

	// ------------------------------------------------------------------------
	// Main settings
	// ------------------------------------------------------------------------
	
	// Note that some settings are available from web : Main page / Configuration / User interface
	// GRAPHVIS_DEFLT_LOGICAL_TAGFILTER : Default tag ID to display on graphvis logical tab. Ie. "24" for tag id 24
	// GRAPHVIS_LOGICAL_TAGS_ROOT : Children of this tag will be offered as tag filter for devices in Logical view
	// GRAPHVIS_PHYSICAL_ROWSTOEXCLUDE : Comma-separated list of row IDs to hide by default on physical tab

	
	// All link types used in your logical network, and their display settings
	$LOGICALLINKTYPES = [
		'25GBase-KR'      => ['id' => 'backplane',             'type' => '25GBase-KR',      'speed' => 25000000000,  'color' => '#804c00', 'width' => 3],
		'100GBase-KP4'    => ['id' => 'backplane',             'type' => '100GBase-KP4',    'speed' => 100000000000, 'color' => '#fe9900', 'width' => 4],
		'100GBase-KR4'    => ['id' => 'backplane',             'type' => '100GBase-KR4',    'speed' => 100000000000, 'color' => '#fe9900', 'width' => 4],
		'100Base-TX'      => ['id' => 'copper',                'type' => '100Base-TX',      'speed' => 100000000,    'color' => '#800001', 'width' => 1],
		'1000Base-T'      => ['id' => 'copper',                'type' => '1000Base-T',      'speed' => 1000000000,   'color' => '#fe0000', 'width' => 2],
		'10GBase-CX4'     => ['id' => 'direct-attached-cable', 'type' => '10GBase-CX4',     'speed' => 10000000000,  'color' => '#ff0198', 'width' => 3],
		'25GBase-CR'      => ['id' => 'direct-attached-cable', 'type' => '25GBase-CR',      'speed' => 25000000000,  'color' => '#65017f', 'width' => 3],
		'100GBase-CR10'   => ['id' => 'direct-attached-cable', 'type' => '100GBase-CR10',   'speed' => 100000000000, 'color' => '#cc00ff', 'width' => 4],
		'100GBase-CR4'    => ['id' => 'direct-attached-cable', 'type' => '100GBase-CR4',    'speed' => 100000000000, 'color' => '#cc00ff', 'width' => 4],
		'100Base-FX'      => ['id' => 'fiber-multi-mode',      'type' => '100Base-FX',      'speed' => 100000000,    'color' => '#01ffff', 'width' => 1],
		'100Base-SX'      => ['id' => 'fiber-multi-mode',      'type' => '100Base-SX',      'speed' => 100000000,    'color' => '#01ffff', 'width' => 1],
		'1000Base-SX'     => ['id' => 'fiber-multi-mode',      'type' => '1000Base-SX',     'speed' => 1000000000,   'color' => '#017f7e', 'width' => 2],
		'1000Base-SX+'    => ['id' => 'fiber-multi-mode',      'type' => '1000Base-SX+',    'speed' => 1000000000,   'color' => '#017f7e', 'width' => 2],
		'10GBase-LRM'     => ['id' => 'fiber-multi-mode',      'type' => '10GBase-LRM',     'speed' => 10000000000,  'color' => '#0465ff', 'width' => 3],
		'10GBase-SR'      => ['id' => 'fiber-multi-mode',      'type' => '10GBase-SR',      'speed' => 10000000000,  'color' => '#0465ff', 'width' => 3],
		'25GBase-SR'      => ['id' => 'fiber-multi-mode',      'type' => '25GBase-SR',      'speed' => 25000000000,  'color' => '#01327f', 'width' => 3],
		'40GBase-SR4'     => ['id' => 'fiber-multi-mode',      'type' => '40GBase-SR4',     'speed' => 40000000000,  'color' => '#19007f', 'width' => 3],
		'100GBase-SR10'   => ['id' => 'fiber-multi-mode',      'type' => '100GBase-SR10',   'speed' => 100000000000, 'color' => '#3300ff', 'width' => 4],
		'100GBase-SR4'    => ['id' => 'fiber-multi-mode',      'type' => '100GBase-SR4',    'speed' => 100000000000, 'color' => '#3300ff', 'width' => 4],
		'100Base-BX10-D'  => ['id' => 'fiber-single-mode',     'type' => '100Base-BX10-D',  'speed' => 100000000,    'color' => '#657f00', 'width' => 1],
		'100Base-BX10-U'  => ['id' => 'fiber-single-mode',     'type' => '100Base-BX10-U',  'speed' => 100000000,    'color' => '#657f00', 'width' => 1],
		'100Base-EX'      => ['id' => 'fiber-single-mode',     'type' => '100Base-EX',      'speed' => 100000000,    'color' => '#657f00', 'width' => 1],
		'100Base-LX10'    => ['id' => 'fiber-single-mode',     'type' => '100Base-LX10',    'speed' => 100000000,    'color' => '#657f00', 'width' => 1],
		'100Base-ZX'      => ['id' => 'fiber-single-mode',     'type' => '100Base-ZX',      'speed' => 100000000,    'color' => '#657f00', 'width' => 1],
		'1000Base-BX10-D' => ['id' => 'fiber-single-mode',     'type' => '1000Base-BX10-D', 'speed' => 1000000000,   'color' => '#ccff00', 'width' => 2],
		'1000Base-BX10-U' => ['id' => 'fiber-single-mode',     'type' => '1000Base-BX10-U', 'speed' => 1000000000,   'color' => '#ccff00', 'width' => 2],
		'1000Base-BX40-D' => ['id' => 'fiber-single-mode',     'type' => '1000Base-BX40-D', 'speed' => 1000000000,   'color' => '#ccff00', 'width' => 2],
		'1000Base-BX40-U' => ['id' => 'fiber-single-mode',     'type' => '1000Base-BX40-U', 'speed' => 1000000000,   'color' => '#ccff00', 'width' => 2],
		'1000Base-BX80-D' => ['id' => 'fiber-single-mode',     'type' => '1000Base-BX80-D', 'speed' => 1000000000,   'color' => '#ccff00', 'width' => 2],
		'1000Base-BX80-U' => ['id' => 'fiber-single-mode',     'type' => '1000Base-BX80-U', 'speed' => 1000000000,   'color' => '#ccff00', 'width' => 2],
		'1000Base-EX'     => ['id' => 'fiber-single-mode',     'type' => '1000Base-EX',     'speed' => 1000000000,   'color' => '#ccff00', 'width' => 2],
		'1000Base-LX'     => ['id' => 'fiber-single-mode',     'type' => '1000Base-LX',     'speed' => 1000000000,   'color' => '#ccff00', 'width' => 2],
		'1000Base-LX10'   => ['id' => 'fiber-single-mode',     'type' => '1000Base-LX10',   'speed' => 1000000000,   'color' => '#ccff00', 'width' => 2],
		'1000Base-ZX'     => ['id' => 'fiber-single-mode',     'type' => '1000Base-ZX',     'speed' => 1000000000,   'color' => '#ccff00', 'width' => 2],
		'10GBase-ER'      => ['id' => 'fiber-single-mode',     'type' => '10GBase-ER',      'speed' => 10000000000,  'color' => '#00ff65', 'width' => 3],
		'10GBase-LR'      => ['id' => 'fiber-single-mode',     'type' => '10GBase-LR',      'speed' => 10000000000,  'color' => '#00ff65', 'width' => 3],
		'10GBase-LX4'     => ['id' => 'fiber-single-mode',     'type' => '10GBase-LX4',     'speed' => 10000000000,  'color' => '#00ff65', 'width' => 3],
		'10GBase-ZR'      => ['id' => 'fiber-single-mode',     'type' => '10GBase-ZR',      'speed' => 10000000000,  'color' => '#00ff65', 'width' => 3],
		'25Gbase-ER'      => ['id' => 'fiber-single-mode',     'type' => '25Gbase-ER',      'speed' => 25000000000,  'color' => '#007f32', 'width' => 3],
		'25GBase-LR'      => ['id' => 'fiber-single-mode',     'type' => '25GBase-LR',      'speed' => 25000000000,  'color' => '#007f32', 'width' => 3],
		'40GBase-ER4'     => ['id' => 'fiber-single-mode',     'type' => '40GBase-ER4',     'speed' => 40000000000,  'color' => '#197f00', 'width' => 3],
		'40GBase-FR'      => ['id' => 'fiber-single-mode',     'type' => '40GBase-FR',      'speed' => 40000000000,  'color' => '#197f00', 'width' => 3],
		'40GBase-LR4'     => ['id' => 'fiber-single-mode',     'type' => '40GBase-LR4',     'speed' => 40000000000,  'color' => '#197f00', 'width' => 3],
		'100GBase-ER10'   => ['id' => 'fiber-single-mode',     'type' => '100GBase-ER10',   'speed' => 100000000000, 'color' => '#32ff00', 'width' => 4],
		'100GBase-ER4'    => ['id' => 'fiber-single-mode',     'type' => '100GBase-ER4',    'speed' => 100000000000, 'color' => '#32ff00', 'width' => 4],
		'100GBase-LR10'   => ['id' => 'fiber-single-mode',     'type' => '100GBase-LR10',   'speed' => 100000000000, 'color' => '#32ff00', 'width' => 4],
		'100GBase-LR4'    => ['id' => 'fiber-single-mode',     'type' => '100GBase-LR4',    'speed' => 100000000000, 'color' => '#32ff00', 'width' => 4],
		NULL              => ['id' => 'UNDEF',                 'type' => 'UNDEF',           'speed' => 0,            'color' => '#FFFF00', 'width' => 5],
	];


	// All link types used in your physical network, and their display settings
	$PHYSICALLINKTYPES = $LOGICALLINKTYPES;
	
	// Mapping from 'objtype_id' to Javascript node group, for display settings
	// Keys will be used to filter network objects to display on logical tab
	$NETOBJ_JS_GROUPS = [
		7    => 'router', // Router
		8    => 'switch', // Network switch
		1503 => 'switch', // Network chassis
		965  => 'wireless', // Wireless
		1323 => 'video', // Voice/Video
	];


	// Netobjs display settings
	$NETOBJS_DISPLAY_SETTINGS = [
		'switch' => [
			'font-size' => 18,
			'border' => '#C37F00',
			'background' => '#FFA807',
			'highlight-border' => '#C37F00',
			'highlight-background' => '#FFCA66',
			'hover-border' => '#C37F00',
			'hover-background' => '#FFCA66'
		],
		'router' => [
			'font-size' => 18,
			'border' => '#FFA500',
			'background' => '#FFFF00',
			'highlight-border' => '#FFA500',
			'highlight-background' => '#FFFFA3',
			'hover-border' => '#FFA500',
			'hover-background' => '#FFFFA3'
		],
		'wireless' => [
			'font-size' => 14,
			'border' => '#4AD63A',
			'background' => '#C2FABC',
			'highlight-border' => '#4AD63A',
			'highlight-background' => '#E6FFE3',
			'hover-border' => '#4AD63A',
			'hover-background' => '#E6FFE3'
		],
		'video' => [
			'font-size' => 14,
			'border' => '#FD5A77',
			'background' => '#FFC0CB',
			'highlight-border' => '#FD5A77',
			'highlight-background' => '#FFD1D9',
			'hover-border' => '#FD5A77',
			'hover-background' => '#FFD1D9'
		],
	];
?>
