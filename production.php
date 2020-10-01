<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/src/include.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/src/header_session_start.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/src/header_tmp_data.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/src/header_alert_massage.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/src/header_authorization_user.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/src/header_notify.php');

date_default_timezone_set($PROG_CONFIG['TIMEZONE']);
$_SESSION['navList'] = cleanActiveTabs($_SESSION['navList']);

///////////////////////////////////////////////////////////////////////////////////////////////

// права доступа

if (isset($_GET['action']) && $_GET['action'] == 'new_order_card') {

	errorIfAccessDenied($_SESSION['user']['auth_production_order_new'],
		$PROG_CONFIG['HOST'] . '/production.php?error_massage=' . $PROG_DATA['ERROR']['ACCESS_DENIED'] . ' ' . __LINE__);

	$_SESSION['formId'] = md5(time());
	$tmpLayoutContentData['formId'] = $_SESSION['formId'];

	$tmpLayoutData['RELOAD_EVERY_MIN'] = false;

	if (isset($_SESSION['navList']['productionNewOrder']['isActive']))
		$_SESSION['navList']['productionNewOrder']['isActive'] = true;
	$tmpLayoutData['title'] = 'Новая заявка на производство';

	$tmpLayoutContentData['designers'] =
		dbSelectData($con, 'SELECT * FROM adm_users WHERE auth_design_order_change_status = 1 AND is_block = 0 AND is_deleted = 0', []) ?? [];

	$tmpLayoutData['content'] = renderTemplate($_SERVER['DOCUMENT_ROOT'] . '/src/templates/production/new_order.php', $tmpLayoutContentData);
}


///////////////////////////////////////////////////////////////////////////////////////////////

// права доступа

if (isset($_GET['action']) && isset($_GET['id']) && $_GET['action'] == 'order_info_card') {

	errorIfAccessDenied($_SESSION['user']['auth_production_order_view'],
		$PROG_CONFIG['HOST'] . '/production.php?error_massage=' . $PROG_DATA['ERROR']['ACCESS_DENIED'] . ' ' . __LINE__);

	$tmpLayoutData['title'] = 'Заявка на производство';

	$_SESSION['formId'] = md5(time());
	$tmpLayoutContentData['formId'] = $_SESSION['formId'];

	$tmpLayoutContentData['activeTab'] = isset($_GET['active_tab']) ? $_GET['active_tab'] : 'notes';

	$tmpLayoutContentData['order'] =
		dbSelectData($con, 'SELECT *, 
		DATE_FORMAT(const_deadline_date, ' . $PROG_CONFIG['DATE_FORMAT'] . ') AS const_deadline_date,
		' . addSuffixStatusList('const_datetime_status_', $PROG_DATA['STATUS_ID_PRODUCTION'], $PROG_CONFIG['DATETIME_FORMAT']) . ' , 
		
		DATE_FORMAT(adv_deadline_date, ' . $PROG_CONFIG['DATE_FORMAT'] . ') AS adv_deadline_date,
		' . addSuffixStatusList('adv_datetime_status_', $PROG_DATA['STATUS_ID_PRODUCTION'], $PROG_CONFIG['DATETIME_FORMAT']) . ' , 
		
		DATE_FORMAT(furn_deadline_date, ' . $PROG_CONFIG['DATE_FORMAT'] . ') AS furn_deadline_date,
		' . addSuffixStatusList('furn_datetime_status_', $PROG_DATA['STATUS_ID_PRODUCTION'], $PROG_CONFIG['DATETIME_FORMAT']) . ' , 
		
		DATE_FORMAT(steel_deadline_date, ' . $PROG_CONFIG['DATE_FORMAT'] . ') AS steel_deadline_date,
		' . addSuffixStatusList('steel_datetime_status_', $PROG_DATA['STATUS_ID_PRODUCTION'], $PROG_CONFIG['DATETIME_FORMAT']) . ' , 
		
		DATE_FORMAT(install_deadline_date, ' . $PROG_CONFIG['DATE_FORMAT'] . ') AS install_deadline_date,
		' . addSuffixStatusList('install_datetime_status_', $PROG_DATA['STATUS_ID_PRODUCTION'], $PROG_CONFIG['DATETIME_FORMAT']) . ' , 
		
		DATE_FORMAT(supply_deadline_date, ' . $PROG_CONFIG['DATE_FORMAT'] . ') AS supply_deadline_date,
		' . addSuffixStatusList('supply_datetime_status_', $PROG_DATA['STATUS_ID_PRODUCTION'], $PROG_CONFIG['DATETIME_FORMAT']) . ' 
		
		FROM production_orders WHERE id = ?', [$_GET['id']])[0] ?? [];

	if (empty($tmpLayoutContentData['order'])) {
		redirectToIf(false, '', $PROG_CONFIG['HOST'] .
			'/production.php?action=orders_list&error_massage=' . $PROG_DATA['ERROR']['ID']);
	}

	$tmpLayoutContentData['designer'] = dbSelectData($con,
			'SELECT * FROM adm_users WHERE id = ?',
			[$tmpLayoutContentData['order']['designer_id'] ?? 0])[0] ?? [];

	$tmpLayoutContentData['createUser'] = dbSelectData($con,
			'SELECT * FROM adm_users WHERE id = ?',
			[$tmpLayoutContentData['order']['create_user_id'] ?? 0])[0] ?? [];

	$tmpLayoutContentData['confirmStartUser'] = dbSelectData($con,
			'SELECT * FROM adm_users WHERE id = ?',
			[$tmpLayoutContentData['order']['confirm_start_user_id'] ?? 0])[0] ?? [];

	$tmpLayoutContentData['confirmCancelUser'] = dbSelectData($con,
			'SELECT * FROM adm_users WHERE id = ?',
			[$tmpLayoutContentData['order']['confirm_cancel_user_id'] ?? 0])[0] ?? [];

	$notesQuery = 'SELECT u.last_name, u.first_name, 
	n.id, n.user_id, n.order_id, n.order_type, 
	DATE_FORMAT(n.create_datetime, ' . $PROG_CONFIG['DATETIME_FORMAT'] . ') AS create_datetime, 
	n.priority, n.note 
	FROM notes n 
	LEFT JOIN adm_users u ON n.user_id = u.id 
	WHERE n.order_id = ? AND n.order_type = ? 
	ORDER by n.id DESC';

	$tmpLayoutContentData['notes'] =
		dbSelectData($con, $notesQuery, [$tmpLayoutContentData['order']['id'], $PROG_DATA['ORDER_TYPES']['PRODUCTION']]);

	$filesQuery = 'SELECT u.last_name AS last_name, u.first_name AS first_name, 
	f.id, f.is_deleted, f.user_id, f.note, f.name, f.path, 
	DATE_FORMAT(f.change_datetime, ' . $PROG_CONFIG['DATETIME_FORMAT'] . ') AS change_datetime 
	FROM files f 
	LEFT JOIN adm_users u ON f.user_id = u.id 
	WHERE f.order_id = ? AND f.order_type = ? 
	ORDER by f.id DESC';

	$tmpLayoutContentData['files'] =
		dbSelectData($con, $filesQuery, [$tmpLayoutContentData['order']['id'], $PROG_DATA['ORDER_TYPES']['PRODUCTION']]);

	$tmpLayoutModalData = [
		'CONFIG' => &$PROG_CONFIG,
		'PROG_DATA' => $PROG_DATA,
		'formId' => $_SESSION['formId'],
		'orderId' => $tmpLayoutContentData['order']['id'],
		'orderType' => $PROG_DATA['ORDER_TYPES']['PRODUCTION'],
		'redirectSuccess' => $PROG_CONFIG['HOST'] . '/production.php?action=order_info_card&active_tab=notes&id=' .
			$tmpLayoutContentData['order']['id'],
		'redirectError' => $PROG_CONFIG['HOST'] . '/production.php?action=order_info_card&active_tab=notes&id=' .
			$tmpLayoutContentData['order']['id']
	];

	$tmpLayoutData['modal'] =
		renderTemplate($_SERVER['DOCUMENT_ROOT'] . '/src/templates/notes/modal_new_note.php',
			$tmpLayoutModalData);

	$tmpLayoutData['content'] =
		renderTemplate($_SERVER['DOCUMENT_ROOT'] . '/src/templates/production/order_info_card.php',
			$tmpLayoutContentData);
}


///////////////////////////////////////////////////////////////////////////////////////////////

// права доступа

if (isset($_GET['action']) && $_GET['action'] == 'orders_list') {

	errorIfAccessDenied($_SESSION['user']['auth_production_order_view'],
		$PROG_CONFIG['HOST'] . '/production.php?error_massage=' . $PROG_DATA['ERROR']['ACCESS_DENIED'] . ' ' . __LINE__);


	if (isset($_SESSION['navList']['productionOrdersListMy']['isActive']) &&
		isset($_GET['department']) && $_SESSION['user']['availDepProd'] !== false &&
		implode(',', $_SESSION['user']['availDepProd']) == $_GET['department'])
		$_SESSION['navList']['productionOrdersListMy']['isActive'] = true;
	else if (isset($_SESSION['navList']['productionOrdersList']['isActive']))
		$_SESSION['navList']['productionOrdersList']['isActive'] = true;
	$tmpLayoutData['title'] = 'Заявки на производство';


	$tmpLayoutContentData['formData']['department'] = $_GET['department'] ?? '';
	$tmpLayoutContentData['formData']['createUserId'] = $_GET['create_user_id'] ?? '';
	$tmpLayoutContentData['formData']['designerId'] = $_GET['designer_id'] ?? '';
	$tmpLayoutContentData['formData']['priority'] = $_GET['priority'] ?? '';
	$tmpLayoutContentData['formData']['status'] = $_GET['status'] ?? '';
	$tmpLayoutContentData['formData']['deadline'] = $_GET['deadline'] ?? '';
	$tmpLayoutContentData['formData']['search'] = $_GET['search'] ?? '';
	$tmpLayoutContentData['formData']['dateFrom'] = $_GET['date_from'] ?? '';
	$tmpLayoutContentData['formData']['dateTo'] = $_GET['date_to'] ?? '';

	$sqlQuerySelectPagination = 'SELECT COUNT(*) as pgn FROM production_orders o ';

	$sqlQuerySelect = 'SELECT 
       ud.last_name AS ud_last_name, ud.first_name AS ud_first_name, 
       uc.last_name AS uc_last_name, uc.first_name AS uc_first_name, ';

	foreach ($PROG_DATA['DEPARTMENTS_LIST'] as $depKey => $depVal) {
		$sqlQuerySelect = $sqlQuerySelect . 'o.' . $depKey . '_current_status, 
			DATE_FORMAT(' . $depKey . '_datetime_status_0, ' . $PROG_CONFIG['DATE_FORMAT'] . ') AS ' . $depKey . '_datetime_status_0, 
			DATE_FORMAT(' . $depKey . '_deadline_date, ' . $PROG_CONFIG['DATE_FORMAT'] . ') AS ' . $depKey . '_deadline_date, ';
	}

	$sqlQuerySelect = $sqlQuerySelect . 'o.id, o.designer_id, o.order_name_in, o.order_name_out, o.client_name, 
       o.order_priority, o.error_priority 
       FROM production_orders o ';

	$sqlQueryJoin1 = 'LEFT JOIN adm_users ud ON o.designer_id = ud.id ';
	$sqlQueryJoin2 = 'LEFT JOIN adm_users uc ON o.create_user_id = uc.id ';
	$sqlQueryWhere = 'WHERE o.id > 0 ';
	$sqlParameters = [];
	$sqlSortBy = 'ORDER BY o.id * o.order_priority * o.sort_priority * o.error_priority DESC ';


	$statusFilter = paramSqlFilterArrVal(',', $_GET['status'] ?? '', $PROG_DATA['STATUS_ID_PRODUCTION']);
	if ($statusFilter === false) {
		foreach ($PROG_DATA['STATUS_ID_PRODUCTION'] as $key => $val)
			$statusFilter[] = $val;
	}

	$departmentFilter = paramSqlFilterArrKey(',', $_GET['department'] ?? '', $PROG_DATA['DEPARTMENTS_LIST']);
	if ($departmentFilter === false)
		foreach ($PROG_DATA['DEPARTMENTS_LIST'] as $key => $val)
			$departmentFilter[] = $key;

	$dateFilter = isset($_GET['date_from']) && isset($_GET['date_to']) && $_GET['date_from'] && $_GET['date_to'];

	$departmentOrAnd = (isset($_GET['department']) && $_GET['department'] == 'all') ? 'AND' : 'OR';
	$statusFilterStr = implode(', ', $statusFilter);


	if (isset($_GET['department']) && $dateFilter === false &&
		count($departmentFilter) !== count($PROG_DATA['DEPARTMENTS_LIST'])) {

		$sqlQueryWhere = $sqlQueryWhere . 'AND (';

		foreach ($departmentFilter as $val)
			$sqlQueryWhere = $sqlQueryWhere . "{$val}_datetime_status_0 IS NOT NULL OR ";

		$sqlQueryWhere = substr($sqlQueryWhere, 0, -4);
		$sqlQueryWhere = $sqlQueryWhere . ') ';
	}


	if (isset($_GET['create_user_id']) && $_GET['create_user_id'] != 'any') {
		$sqlQueryWhere = $sqlQueryWhere . 'AND create_user_id = ? ';
		$sqlParameters[] = $_GET['create_user_id'];
	}

	if (isset($_GET['designer_id']) && $_GET['designer_id'] != 'any') {
		$sqlQueryWhere = $sqlQueryWhere . 'AND designer_id = ? ';
		$sqlParameters[] = $_GET['designer_id'];
	}

	if (isset($_GET['priority']) && $_GET['priority'] != 'any') {
		$sqlQueryWhere = $sqlQueryWhere . 'AND order_priority = ? ';
		$sqlParameters[] = $_GET['priority'];
	}


	if (isset($_GET['status']) && $_GET['status'] !== 'any' && $dateFilter === false) {

		$sqlQueryWhere = $sqlQueryWhere . 'AND (';

		foreach ($departmentFilter as $depKey => $depVal) {
			if (isset($_GET['department']) && $_GET['department'] == 'all')
				$sqlQueryWhere = $sqlQueryWhere . "({$depVal}_current_status IN ({$statusFilterStr}) OR {$depVal}_datetime_status_0 IS NULL) {$departmentOrAnd} ";
			else
				$sqlQueryWhere = $sqlQueryWhere . "{$depVal}_current_status IN ({$statusFilterStr}) {$departmentOrAnd} ";
		}
		$sqlQueryWhere = substr($sqlQueryWhere, 0, -2 - strlen($departmentOrAnd));
		$sqlQueryWhere = $sqlQueryWhere . ') ';
	}


	if (isset($_GET['deadline']) && mb_strlen($_GET['deadline']) > 0) {
		$sqlQueryWhere = $sqlQueryWhere . 'AND (';
		foreach ($departmentFilter as $depKey => $depVal) {

			$sqlQueryWhere = $sqlQueryWhere . "{$depVal}_deadline_date <= NOW() + INTERVAL ? DAY OR ";
			$sqlParameters[] = $_GET['deadline'];
		}
		$sqlQueryWhere = substr($sqlQueryWhere, 0, -4);
		$sqlQueryWhere = $sqlQueryWhere . ') ';
	}


	if (isset($_GET['search']) && $_GET['search']) {
		$sqlQueryWhere = $sqlQueryWhere . 'AND (order_name_in LIKE ? OR order_name_out LIKE ? OR client_name LIKE ?) ';
		$sqlParameters[] = '%' . $_GET['search'] . '%';
		$sqlParameters[] = '%' . $_GET['search'] . '%';
		$sqlParameters[] = '%' . $_GET['search'] . '%';
	}

	if ($dateFilter) {

		$sqlQueryWhere = $sqlQueryWhere . 'AND (';

		foreach ($departmentFilter as $depKey => $depVal) {
			foreach ($statusFilter as $stKey => $stVal) {

				if (isset($_GET['department']) && $_GET['department'] == 'all')
					$sqlQueryWhere = $sqlQueryWhere . "(({$depVal}_datetime_status_{$stVal} BETWEEN ? AND DATE_ADD(?, INTERVAL 1 DAY)) OR 
						{$depVal}_datetime_status_0 IS NULL) {$departmentOrAnd} ";
				else
					$sqlQueryWhere = $sqlQueryWhere . "({$depVal}_datetime_status_{$stVal} BETWEEN ? AND DATE_ADD(?, INTERVAL 1 DAY)) {$departmentOrAnd} ";

				$sqlParameters[] = date('Y-m-d H:i:s', strtotime($_GET['date_from']));
				$sqlParameters[] = date('Y-m-d H:i:s', strtotime($_GET['date_to']));
			}
		}
		$sqlQueryWhere = substr($sqlQueryWhere, 0, -2 - strlen($departmentOrAnd));
		$sqlQueryWhere = $sqlQueryWhere . ') ';
	}

	$tmpLayoutContentData['sql'] = $sqlQueryWhere;

	$paginationData =
		getPagination($PROG_CONFIG, $PROG_CONFIG['HOST'] . '/production.php', $con, $sqlQuerySelectPagination .
			$sqlQueryWhere, $sqlParameters);

	$tmpLayoutData['pagination'] = $paginationData['tmpPagination'];
	$sqlPagination = $paginationData['sqlPagination'];

	$tmpLayoutContentData['showDepartment'] =
		($departmentFilter === false || count($departmentFilter) > 1) ? false : $departmentFilter[0];

	$tmpLayoutContentData['createUsers'] =
		dbSelectData($con, 'SELECT * FROM adm_users WHERE auth_design_order_new = 1 ORDER BY last_name', []);
	$tmpLayoutContentData['designers'] =
		dbSelectData($con, 'SELECT * FROM adm_users WHERE auth_design_order_change_status = 1 ORDER BY last_name', []);

	$tmpLayoutContentData['orders'] =
		dbSelectData($con, $sqlQuerySelect . $sqlQueryJoin1 . $sqlQueryJoin2 . $sqlQueryWhere . $sqlSortBy . $sqlPagination, $sqlParameters) ?? [];

	foreach ($tmpLayoutContentData['orders'] as $key => $val)
	{
		$tmpLayoutContentData['orders'][$key]['general_status'] =
			currentGeneralStatus($val, $PROG_DATA['DEPARTMENTS_LIST']);
	}

	$tmpLayoutData['content'] =
		renderTemplate($_SERVER['DOCUMENT_ROOT'] . '/src/templates/production/orders_list.php', $tmpLayoutContentData);
}


///////////////////////////////////////////////////////////////////////////////////////////////

// права доступа

if (isset($_POST['action']) && $_POST['action'] == 'new_order_data') {

	errorIfAccessDenied($_SESSION['user']['auth_production_order_new'],
		$PROG_CONFIG['HOST'] . '/production.php?error_massage=' . $PROG_DATA['ERROR']['ACCESS_DENIED'] . ' ' . __LINE__);

	errorIfDoubleClick($_SESSION['formId'], $_POST['form_id'],
		$PROG_CONFIG['HOST'] . '/production.php?action=new_order_card&error_massage=' . $PROG_DATA['ERROR']['DOUBLE_CLICK']);

	$_SESSION['formId'] = 'none';

	if (isValidNewProductionOrderData($PROG_CONFIG) === false) {
		redirectToIf(false, '',
			$PROG_CONFIG['HOST'] . '/production.php?action=new_order_card&error_massage=' . $PROG_DATA['ERROR']['INPUT_DATA']);
	}

	$newOrderData = [
		'create_user_id' => $_SESSION['user']['id'],
		'designer_id' => $_POST['designer_id'],

		'order_name_out' => $_POST['order_name_out'],

		'order_priority' => $PROG_DATA['PRIORITY_ID']['NORM'],

		'client_name' => correctFormatUpper($_POST['client_name']),
		'mobile_phone' => correctFormat($_POST['mobile_phone']),
		'email' => correctFormatLower($_POST['email']),

		'task_text' => correctFormat($_POST['task_text']),
		'task_quantity' => $_POST['task_quantity'],

		'install_task' => correctFormat($_POST['install_task'] ?? ''),
		'install_address' => correctFormat($_POST['install_address'] ?? '')
	];

	if (isset($_POST['const']) && $_POST['const'] == 'on' && isset($_POST['const_deadline']) && $_POST['const_deadline']) {
		$newOrderData['const_deadline_date'] = date('Y-m-d H:i:s', strtotime($_POST['const_deadline']));
		$newOrderData['const_current_status'] = $PROG_DATA['STATUS_ID_PRODUCTION']['WAIT_START'];
		$newOrderData['const_datetime_status_0'] = date('Y-m-d H:i:s');
	}

	if (isset($_POST['adv']) && $_POST['adv'] == 'on' && isset($_POST['adv_deadline']) && $_POST['adv_deadline']) {
		$newOrderData['adv_deadline_date'] = date('Y-m-d H:i:s', strtotime($_POST['adv_deadline']));
		$newOrderData['adv_current_status'] = $PROG_DATA['STATUS_ID_PRODUCTION']['WAIT_START'];
		$newOrderData['adv_datetime_status_0'] = date('Y-m-d H:i:s');
	}

	if (isset($_POST['furn']) && $_POST['furn'] == 'on' && isset($_POST['furn_deadline']) && $_POST['furn_deadline']) {
		$newOrderData['furn_deadline_date'] = date('Y-m-d H:i:s', strtotime($_POST['furn_deadline']));
		$newOrderData['furn_current_status'] = $PROG_DATA['STATUS_ID_PRODUCTION']['WAIT_START'];
		$newOrderData['furn_datetime_status_0'] = date('Y-m-d H:i:s');
	}

	if (isset($_POST['steel']) && $_POST['steel'] == 'on' && isset($_POST['steel_deadline']) && $_POST['steel_deadline']) {
		$newOrderData['steel_deadline_date'] = date('Y-m-d H:i:s', strtotime($_POST['steel_deadline']));
		$newOrderData['steel_current_status'] = $PROG_DATA['STATUS_ID_PRODUCTION']['WAIT_START'];
		$newOrderData['steel_datetime_status_0'] = date('Y-m-d H:i:s');
	}

	if (isset($_POST['install']) && $_POST['install'] == 'on' && isset($_POST['install_deadline']) && $_POST['install_deadline']) {
		$newOrderData['install_deadline_date'] = date('Y-m-d H:i:s', strtotime($_POST['install_deadline']));
		$newOrderData['install_current_status'] = $PROG_DATA['STATUS_ID_PRODUCTION']['WAIT_START'];
		$newOrderData['install_datetime_status_0'] = date('Y-m-d H:i:s');
	}

	if (isset($_POST['supply']) && $_POST['supply'] == 'on' && isset($_POST['supply_deadline']) && $_POST['supply_deadline']) {
		$newOrderData['supply_deadline_date'] = date('Y-m-d H:i:s', strtotime($_POST['supply_deadline']));
		$newOrderData['supply_current_status'] = $PROG_DATA['STATUS_ID_PRODUCTION']['WAIT_START'];
		$newOrderData['supply_datetime_status_0'] = date('Y-m-d H:i:s');
	}

	mysqli_query($con, 'START TRANSACTION');

	$newOrder = dbInsertData($con, 'production_orders', $newOrderData);

	$updateOrderNameIn = dbExecQuery($con, "UPDATE production_orders SET order_name_in = ? WHERE id = ?",
		[getOrderName($newOrder), $newOrder]);

	if ($newOrder && $updateOrderNameIn)
		mysqli_query($con, 'COMMIT');
	else
		mysqli_query($con, 'ROLLBACK');

	redirectToIf($newOrder && $updateOrderNameIn,
		$PROG_CONFIG['HOST'] . '/production.php?action=order_info_card&id=' . $newOrder . '&alert_massage=' . $PROG_DATA['ALERT']['OK'],
		$PROG_CONFIG['HOST'] . '/production.php?action=new_order_card&error_massage=' . $PROG_DATA['ERROR']['BD_WRITE']);
}


///////////////////////////////////////////////////////////////////////////////////////////////

// права доступа

if (isset($_POST['action']) && isset($_POST['order_id']) && isset($_POST['priority']) &&
	$_POST['action'] == 'change_priority') {

	errorIfAccessDenied($_SESSION['user']['auth_production_order_change_priority'],
		$PROG_CONFIG['HOST'] . '/production.php?error_massage=' . $PROG_DATA['ERROR']['ACCESS_DENIED'] . ' ' . __LINE__);

	if (array_key_exists($_POST['priority'], $PROG_DATA['PRIORITY_ORDERS']) === false) {
		redirectToIf(false, '',
			$PROG_CONFIG['HOST'] .
			'/production.php?action=order_info_card&id=' . $_POST['order_id'] . '&error_massage=ACCESS DENIED' . __LINE__);
	}

	$changePriorityQuery = 'UPDATE production_orders SET order_priority = ? WHERE id = ?';
	$changePriority = dbExecQuery($con, $changePriorityQuery, [$_POST['priority'], $_POST['order_id']]);

	redirectToIf($changePriority,
		$PROG_CONFIG['HOST'] .
		'/production.php?action=order_info_card&id=' . $_POST['order_id'] . '&alert_massage=' . $PROG_DATA['ALERT']['OK'],
		$PROG_CONFIG['HOST'] .
		'/production.php?action=order_info_card&id=' . $_POST['order_id'] . '&error_massage=' . $PROG_DATA['ERROR']['BD_WRITE']);
}


///////////////////////////////////////////////////////////////////////////////////////////////

if (isset($_GET['action']) && isset($_GET['order_id']) &&
	isset($_GET['redirect_success']) && isset($_GET['redirect_error']) &&
	($_GET['action'] == 'add_error' || $_GET['action'] == 'cancel_error')) {

	$errorPriority = $_GET['action'] == 'cancel_error' ? 1 : 2;

	redirectToIf(dbExecQuery($con, 'UPDATE production_orders SET error_priority = ? WHERE id = ?', [$errorPriority, $_GET['order_id']]),
		$_GET['redirect_success'] . '&alert_massage=' . $PROG_DATA['ALERT']['OK'],
		$_GET['redirect_error'] . '&error_massage=' . $PROG_DATA['ERROR']['BD_WRITE']);
}


///////////////////////////////////////////////////////////////////////////////////////////////

// (все цеха) проверка что цех учавствует впроекте '{цех}_datetime_status_0' !null

// (все цеха) запросить отмену только тот кто создал

// (все цеха) подтвердить отмену у кого есть права && статус 'ожидание подтверждения отмены - 998'

// (все цеха) запустить в работу у кго есть права && статус 'ожидание подтверждения - 0'

// (все цеха) изменение статуса для всех цехов только на статусы (WAIT_START RECEIVED WAIT_CANCEL CANCEL)


// (отдельные цеха) у кого есть права
// (отдельные цеха) статус меняется только в большую сторону
// (отдельные цеха) изменение статуса по цехам только если стадия больше RECEIVED
// (отдельные цеха) изменение статуса по цехам в диапозоне от START до ISSUED

// если статус отгружено (ISSUED) а статус выполнено (DONE) не ставился, дата выполнения как отгружен

if (isset($_POST['action']) && isset($_POST['order_id']) && isset($_POST['department']) && isset($_POST['status']) &&
	isset($_POST['redirect_success']) && isset($_POST['redirect_error']) &&
	$_POST['action'] == 'change_status') {

	$orderData = dbSelectData($con, 'SELECT * FROM production_orders WHERE id = ?', [$_POST['order_id']])[0] ?? [];

	// заказ существует
	if (isset($orderData['id']) == false)
		redirectToIf(false, '',
			$PROG_CONFIG['HOST'] . '/production.php?action=order_info_card&id=' .
			$_POST['order_id'] . '&error_massage=' . $PROG_DATA['ERROR']['ACCESS_DENIED'] . ' ' . __LINE__);

	// статус существует
	if (array_key_exists($_POST['status'], $PROG_DATA['STATUS_LIST_PRODUCTION']) === false) {
		redirectToIf(false, '',
			$PROG_CONFIG['HOST'] . '/production.php?action=order_info_card&id=' .
			$_POST['order_id'] . '&error_massage=' . $PROG_DATA['ERROR']['ACCESS_DENIED'] . ' ' . __LINE__);
	}

	// департамент существует
	if ($_POST['department'] != 'all' && isset($orderData[$_POST['department'] . '_current_status']) == false) {
		redirectToIf(false, '',
			$PROG_CONFIG['HOST'] . '/production.php?action=order_info_card&id=' .
			$_POST['order_id'] . '&error_massage=' . $PROG_DATA['ERROR']['ACCESS_DENIED'] . ' ' . __LINE__);
	}

	// запросить отмену только тот кто создал
	if ($_POST['status'] == $PROG_DATA['STATUS_ID_PRODUCTION']['WAIT_CANCEL'] &&
		$_SESSION['user']['id'] != $orderData['create_user_id']) {
		redirectToIf(false, '',
			$PROG_CONFIG['HOST'] . '/production.php?action=order_info_card&id=' .
			$_POST['order_id'] . '&error_massage=' . $PROG_DATA['ERROR']['ACCESS_DENIED'] . ' ' . __LINE__);
	}

	// подтвердить отмену у кого есть права && статус 'ожидание подтверждения отмены - (998 WAIT_CANCEL)'
	if ($_POST['status'] == $PROG_DATA['STATUS_ID_PRODUCTION']['CANCEL'] &&
		($_SESSION['user']['auth_production_order_cancel'] == 0 ||
			currentGeneralStatus($orderData, $PROG_DATA['DEPARTMENTS_LIST']) != $PROG_DATA['STATUS_ID_PRODUCTION']['WAIT_CANCEL'])) {
		redirectToIf(false, '',
			$PROG_CONFIG['HOST'] . '/production.php?action=order_info_card&id=' .
			$_POST['order_id'] . '&error_massage=' . $PROG_DATA['ERROR']['ACCESS_DENIED'] . ' ' . __LINE__);
	}

	// запустить в работу у кго есть права && статус 'ожидание подтверждения - (0 WAIT_START)'
	if ($_POST['status'] == $PROG_DATA['STATUS_ID_PRODUCTION']['RECEIVED'] &&
		($_SESSION['user']['auth_production_order_start'] == 0 ||
			currentGeneralStatus($orderData, $PROG_DATA['DEPARTMENTS_LIST']) != $PROG_DATA['STATUS_ID_PRODUCTION']['WAIT_START'])) {
		redirectToIf(false, '',
			$PROG_CONFIG['HOST'] . '/production.php?action=order_info_card&id=' .
			$_POST['order_id'] . '&error_massage=' . $PROG_DATA['ERROR']['ACCESS_DENIED'] . ' ' . __LINE__);
	}

	// изменение статуса для всех цехов только на статусы (WAIT_START RECEIVED WAIT_CANCEL CANCEL)
	if ($_POST['department'] == 'all' &&
		$_POST['status'] != $PROG_DATA['STATUS_ID_PRODUCTION']['WAIT_START'] &&
		$_POST['status'] != $PROG_DATA['STATUS_ID_PRODUCTION']['RECEIVED'] &&
		$_POST['status'] != $PROG_DATA['STATUS_ID_PRODUCTION']['WAIT_CANCEL'] &&
		$_POST['status'] != $PROG_DATA['STATUS_ID_PRODUCTION']['CANCEL']) {
		redirectToIf(false, '',
			$PROG_CONFIG['HOST'] . '/production.php?action=order_info_card&id=' .
			$_POST['order_id'] . '&error_massage=' . $PROG_DATA['ERROR']['ACCESS_DENIED'] . ' ' . __LINE__);
	}

	// изменение статуса по цехам в диапозоне от START до ISSUED
	if ($_POST['department'] != 'all' &&
		($_POST['status'] < $PROG_DATA['STATUS_ID_PRODUCTION']['START'] ||
			$_POST['status'] > $PROG_DATA['STATUS_ID_PRODUCTION']['ISSUED'])) {
		redirectToIf(false, '',
			$PROG_CONFIG['HOST'] . '/production.php?action=order_info_card&id=' .
			$_POST['order_id'] . '&error_massage=' . $PROG_DATA['ERROR']['ACCESS_DENIED'] . ' ' . __LINE__);
	}

	// (отдельные цеха) изменение статуса по цехам только если стадия больше RECEIVED
	if ($_POST['department'] != 'all' &&
		$orderData[$_POST['department'] . '_current_status'] < $PROG_DATA['STATUS_ID_PRODUCTION']['RECEIVED']) {
		redirectToIf(false, '',
			$PROG_CONFIG['HOST'] . '/production.php?action=order_info_card&id=' .
			$_POST['order_id'] . '&error_massage=' . $PROG_DATA['ERROR']['ACCESS_DENIED'] . ' ' . __LINE__);
	}

	// изменение статуса по цехам у кого есть права
	if ($_POST['department'] != 'all' && $_SESSION['user']['auth_production_order_change_status_' . $_POST['department']] == 0) {
		redirectToIf(false, '',
			$PROG_CONFIG['HOST'] . '/production.php?action=order_info_card&id=' .
			$_POST['order_id'] . '&error_massage=' . $PROG_DATA['ERROR']['ACCESS_DENIED'] . ' ' . __LINE__);
	}

	// изменение статуса по цехам меняется только в большую сторону
	if ($_POST['department'] != 'all' && $_POST['status'] <= $orderData[$_POST['department'] . '_current_status']) {
		redirectToIf(false, '',
			$PROG_CONFIG['HOST'] . '/production.php?action=order_info_card&id=' .
			$_POST['order_id'] . '&error_massage=' . $PROG_DATA['ERROR']['ACCESS_DENIED'] . ' ' . __LINE__);
	}


	$sqlQueryUpdate = 'UPDATE production_orders ';
	$sqlQuerySet = 'SET ';
	$sqlQueryWhere = 'WHERE id = ?';
	$sqlParameters = [];


	if (($_POST['department'] == 'all' || $_POST['department'] == 'const') && $orderData['const_datetime_status_0']) {

		if ($_POST['status'] == $PROG_DATA['STATUS_ID_PRODUCTION']['ISSUED'] &&
			$orderData['const_current_status'] < $PROG_DATA['STATUS_ID_PRODUCTION']['DONE']) {

			$sqlQuerySet = $sqlQuerySet . 'const_datetime_status_300 = ?, ';
			$sqlParameters[] = date('Y-m-d H:i:s');
		}

		$sqlQuerySet = $sqlQuerySet . 'const_current_status = ?, const_datetime_status_' . $_POST['status'] . ' = ?, ';
		$sqlParameters[] = $_POST['status'];
		$sqlParameters[] = date('Y-m-d H:i:s');
	}

	if (($_POST['department'] == 'all' || $_POST['department'] == 'adv') && $orderData['adv_datetime_status_0']) {

		if ($_POST['status'] == $PROG_DATA['STATUS_ID_PRODUCTION']['ISSUED'] &&
			$orderData['adv_current_status'] < $PROG_DATA['STATUS_ID_PRODUCTION']['DONE']) {

			$sqlQuerySet = $sqlQuerySet . 'adv_datetime_status_300 = ?, ';
			$sqlParameters[] = date('Y-m-d H:i:s');
		}

		$sqlQuerySet = $sqlQuerySet . 'adv_current_status = ?, adv_datetime_status_' . $_POST['status'] . ' = ?, ';
		$sqlParameters[] = $_POST['status'];
		$sqlParameters[] = date('Y-m-d H:i:s');
	}

	if (($_POST['department'] == 'all' || $_POST['department'] == 'furn') && $orderData['furn_datetime_status_0']) {

		if ($_POST['status'] == $PROG_DATA['STATUS_ID_PRODUCTION']['ISSUED'] &&
			$orderData['furn_current_status'] < $PROG_DATA['STATUS_ID_PRODUCTION']['DONE']) {

			$sqlQuerySet = $sqlQuerySet . 'furn_datetime_status_300 = ?, ';
			$sqlParameters[] = date('Y-m-d H:i:s');
		}

		$sqlQuerySet = $sqlQuerySet . 'furn_current_status = ?, furn_datetime_status_' . $_POST['status'] . ' = ?, ';
		$sqlParameters[] = $_POST['status'];
		$sqlParameters[] = date('Y-m-d H:i:s');
	}

	if (($_POST['department'] == 'all' || $_POST['department'] == 'steel') && $orderData['steel_datetime_status_0']) {

		if ($_POST['status'] == $PROG_DATA['STATUS_ID_PRODUCTION']['ISSUED'] &&
			$orderData['steel_current_status'] < $PROG_DATA['STATUS_ID_PRODUCTION']['DONE']) {

			$sqlQuerySet = $sqlQuerySet . 'steel_datetime_status_300 = ?, ';
			$sqlParameters[] = date('Y-m-d H:i:s');
		}

		$sqlQuerySet = $sqlQuerySet . 'steel_current_status = ?, steel_datetime_status_' . $_POST['status'] . ' = ?, ';
		$sqlParameters[] = $_POST['status'];
		$sqlParameters[] = date('Y-m-d H:i:s');
	}

	if (($_POST['department'] == 'all' || $_POST['department'] == 'install') && $orderData['install_datetime_status_0']) {

		if ($_POST['status'] == $PROG_DATA['STATUS_ID_PRODUCTION']['ISSUED'] &&
			$orderData['install_current_status'] < $PROG_DATA['STATUS_ID_PRODUCTION']['DONE']) {

			$sqlQuerySet = $sqlQuerySet . 'install_datetime_status_300 = ?, ';
			$sqlParameters[] = date('Y-m-d H:i:s');
		}

		$sqlQuerySet = $sqlQuerySet . 'install_current_status = ?, install_datetime_status_' . $_POST['status'] . ' = ?, ';
		$sqlParameters[] = $_POST['status'];
		$sqlParameters[] = date('Y-m-d H:i:s');
	}

	if (($_POST['department'] == 'all' || $_POST['department'] == 'supply') && $orderData['supply_datetime_status_0']) {

		if ($_POST['status'] == $PROG_DATA['STATUS_ID_PRODUCTION']['ISSUED'] &&
			$orderData['supply_current_status'] < $PROG_DATA['STATUS_ID_PRODUCTION']['DONE']) {

			$sqlQuerySet = $sqlQuerySet . 'supply_datetime_status_300 = ?, ';
			$sqlParameters[] = date('Y-m-d H:i:s');
		}

		$sqlQuerySet = $sqlQuerySet . 'supply_current_status = ?, supply_datetime_status_' . $_POST['status'] . ' = ?, ';
		$sqlParameters[] = $_POST['status'];
		$sqlParameters[] = date('Y-m-d H:i:s');
	}

	// todo когда у всех цехов выполено
	if ($_POST['status'] == $PROG_DATA['STATUS_ID_PRODUCTION']['DONE'] ||
		$_POST['status'] == $PROG_DATA['STATUS_ID_PRODUCTION']['ISSUED'] ||
		$_POST['status'] == $PROG_DATA['STATUS_ID_PRODUCTION']['CANCEL']) {

		$sqlQuerySet = $sqlQuerySet . 'order_priority = 1, sort_priority = 1, error_priority = 1, ';
	}

	$sqlQuerySet = substr($sqlQuerySet, 0, -2);
	$sqlQuerySet = $sqlQuerySet . ' ';
	$sqlParameters[] = $_POST['order_id'];


	mysqli_query($con, 'START TRANSACTION');

	$confirmStart = true;
	if ($_POST['status'] == $PROG_DATA['STATUS_ID_PRODUCTION']['RECEIVED']) {
		$confirmStart = dbExecQuery($con,
			'UPDATE production_orders SET confirm_start_user_id = ? WHERE id = ?', [$_SESSION['user']['id'], $_POST['order_id']]);
	}

	$confirmCancel = true;
	if ($_POST['status'] == $PROG_DATA['STATUS_ID_PRODUCTION']['CANCEL']) {
		$confirmCancel = dbExecQuery($con,
			'UPDATE production_orders SET confirm_cancel_user_id = ? WHERE id = ?', [$_SESSION['user']['id'], $_POST['order_id']]);
	}


	$changeStatus = dbExecQuery($con, $sqlQueryUpdate . $sqlQuerySet . $sqlQueryWhere, $sqlParameters);

	if ($confirmStart && $confirmCancel && $changeStatus)
		mysqli_query($con, 'COMMIT');
	else
		mysqli_query($con, 'ROLLBACK');

	redirectToIf($confirmStart && $confirmCancel && $changeStatus,
		$_POST['redirect_success'] . '&alert_massage=' . $PROG_DATA['ALERT']['OK'],
		$_POST['redirect_error'] . '&error_massage=' . $PROG_DATA['ERROR']['BD_WRITE']);

}


echo renderTemplate($_SERVER['DOCUMENT_ROOT'] . '/src/templates/layout.php', $tmpLayoutData);
