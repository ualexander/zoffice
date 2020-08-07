<?php

$navigationListUser = [
	'captionDesign' => [
		'title' => 				'Дизайн',
		'url' => 					'#',
		'isCaption' => 		true,
		'isActive' => 		false,
		'isAvailable' =>	true
	],
	'designOrdersList' => [
		'title' => 				'<i class="ni ni-bullet-list-67 text-primary"></i><span class="nav-link-text">Заявки</span>',
		'url' => 					$PROG_CONFIG['HOST'] . '/design.php?action=orders_list',
		'isCaption' => 		false,
		'isActive' => 		false,
		'isAvailable' =>	true
	],
	'designNewOrder' => [
		'title' => 				'<i class="ni ni-fat-add text-primary"></i><span class="nav-link-text">Создать</span>',
		'url' => 					$PROG_CONFIG['HOST'] . '/design.php?action=new_order_card',
		'isCaption' => 		false,
		'isActive' => 		false,
		'isAvailable' =>	true
	],
	'captionProduction' => [
		'title' => 				'Производство',
		'url' => 					'#',
		'isCaption' => 		true,
		'isActive' => 		false,
		'isAvailable' =>	true
	],
	'productionOrdersList' => [
		'title' => 				'<i class="ni ni-bullet-list-67 text-primary"></i><span class="nav-link-text">Заявки</span>',
		'url' => 					$PROG_CONFIG['HOST'] . '/production.php?action=orders_list',
		'isCaption' => 		false,
		'isActive' => 		false,
		'isAvailable' =>	true
	],
	'productionNewOrder' => [
		'title' => 				'<i class="ni ni-fat-add text-primary"></i><span class="nav-link-text">Создать</span>',
		'url' => 					$PROG_CONFIG['HOST'] . '/production.php?action=new_order',
		'isCaption' => 		false,
		'isActive' => 		false,
		'isAvailable' =>	true
	]
];
