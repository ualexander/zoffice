<?php

function renderTemplate($template, $data) {
	$string = '';
	if (file_exists($template)) {
		ob_start();
		require_once($template);
		$string = ob_get_clean();
		return $string;
	} else {
		return $string;
	}
}


function getStringFromGetQuery($getArr) {

	unset($getArr['page']);
	unset($getArr['error_massage']);
	unset($getArr['alert_massage']);

	$getQueryString = '?';
	foreach ($getArr as $key => $value) {
		$getQueryString = $getQueryString . $key . '=' . $value . '&';
	}
	return $getQueryString;
}


function getPagination($config, $url, $con, $sqlQuery, $sqlParametrs) {

	$sqlPagination = ' ';
	$tmpPagination = '';

	$tmpPaginationData = [
		'config' => $config,
		'url' => $url . getStringFromGetQuery($_GET),
		'pagesQuantity' => 0,
		'currentPage' => 0
	];

	$paginationCount = dbSelectData($con, $sqlQuery, $sqlParametrs)[0]['pgn'];

	if ($paginationCount > $config['MAX_TABLE_ROWS']) {

		$sqlPaginationItem = $config['MAX_TABLE_ROWS'];
		$sqlPaginationStart = 0;

		$tmpPaginationData['pagesQuantity'] = ceil($paginationCount / $config['MAX_TABLE_ROWS']);
		$tmpPaginationData['currentPage'] = 1;

		if (isset($_GET['page']) && $_GET['page'] > 1) {
			$sqlPaginationStart = floor($config['MAX_TABLE_ROWS'] * (floor($_GET['page']) - 1));
			$tmpPaginationData['currentPage'] = floor($_GET['page']);
		}

		$sqlPagination = 'LIMIT ' . $sqlPaginationItem . ' OFFSET ' . $sqlPaginationStart . ' ';
		$tmpPagination = renderTemplate($_SERVER['DOCUMENT_ROOT'] . '/src/templates/pagination.php', $tmpPaginationData);
	}

	return [
		'sqlPagination' => $sqlPagination,
		'tmpPagination' => $tmpPagination
	];
}

function getOrderName($orderId) {
	$maxOrderNameBodyLenght = 4;
	$orderIdLength = mb_strlen($orderId);
	if ($orderIdLength > $maxOrderNameBodyLenght) {
		return substr($orderId, $orderIdLength - $maxOrderNameBodyLenght,
				$orderIdLength) . '-' . date('y-m');
	} else {

		return str_repeat('0',
				$maxOrderNameBodyLenght - $orderIdLength) . $orderId . '-' . date('m-y');
	}
}

function sortStr($str, $maxLength) {
	if (iconv_strlen($str) > $maxLength) {
		return '<span data-toggle="tooltip" data-placement="top" title="' . $str . '">' . mb_strimwidth($str, 0, $maxLength) . '...</span>';
	} else {
		return $str;
	}
}
