<?php

$STATUS_LIST_DESIGN = [
	0 =>		[
		'name' => 'ожидание назначения дизайнера',
		'icon' => '<span class="badge badge-pill bg-warning text-dark">ожидание назн. диз.</span>'
	],
	100 =>	[
		'name' => 'получено дизайнером',
		'icon' => '<span class="badge badge-pill bg-info text-white">получено диз.</span>'
	],
	200 =>	[
		'name' => 'начата работа',
		'icon' => '<span class="badge badge-pill bg-primary text-white">начата работа</span>'
	],
	210 =>	[
		'name' => 'готовность 10%',
		'icon' => '<span class="badge badge-pill bg-primary text-white">готовность 10%</span>'
	],
	220 =>	[
		'name' => 'готовность 20%',
		'icon' => '<span class="badge badge-pill bg-primary text-white">готовность 20%</span>'
	],
	230 =>	[
		'name' => 'готовность 30%',
		'icon' => '<span class="badge badge-pill bg-primary text-white">готовность 30%</span>'
	],
	240 =>	[
		'name' => 'готовность 40%',
		'icon' => '<span class="badge badge-pill bg-primary text-white">готовность 40%</span>'
	],
	250 =>	[
		'name' => 'готовность 50%',
		'icon' => '<span class="badge badge-pill bg-primary text-white">готовность 50%</span>'
	],
	260 =>	[
		'name' => 'готовность 60%',
		'icon' => '<span class="badge badge-pill bg-primary text-white">готовность 60%</span>'
	],
	270 =>	[
		'name' => 'готовность 70%',
		'icon' => '<span class="badge badge-pill bg-primary text-white">готовность 70%</span>'
	],
	280 =>	[
		'name' => 'готовность 80%',
		'icon' => '<span class="badge badge-pill bg-primary text-white">готовность 80%</span>'
	],
	290 =>	[
		'name' => 'готовность 90%',
		'icon' => '<span class="badge badge-pill bg-primary text-white">готовность 90%</span>'
	],
	300 =>	[
		'name' => 'выполнено',
		'icon' => '<span class="badge badge-pill bg-success text-white">выполнено</span>'
	],
	999 =>	[
		'name' => 'отменено',
		'icon' => '<span class="badge badge-pill bg-secondary text-dark">отменено</span>'
	]
];

$STATUS_ID_DESIGN = [
	'WAIT' 			=> 0,
	'RECEIVED'	=> 100,
	'START'			=> 200,
	'READY_10' 	=> 210,
	'READY_20' 	=> 220,
	'READY_30' 	=> 230,
	'READY_40' 	=> 240,
	'READY_50' 	=> 250,
	'READY_60' 	=> 260,
	'READY_70' 	=> 270,
	'READY_80' 	=> 280,
	'READY_90' 	=> 290,
	'DONE' 			=> 300,
	'CANCEL' 		=> 999,
];
