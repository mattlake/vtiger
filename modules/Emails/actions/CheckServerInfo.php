<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Emails_CheckServerInfo_Action extends Vtiger_Action_Controller {

	public function requiresPermission(\Http\Request $request) {
		$permissions = parent::requiresPermission($request);
		$permissions[] = array('module_parameter' => 'module', 'action' => 'DetailView');
		return $permissions;
	}
	
	public function checkPermission(\Http\Request $request) {
		return parent::checkPermission($request);
	}

	function process(\Http\Request $request) {
		$db = PearDatabase::getInstance();
		$response = new Vtiger_Response();

		$result = $db->pquery('SELECT 1 FROM vtiger_systems WHERE server_type = ?', array('email'));
		if($db->num_rows($result)) {
			$response->setResult(true);
		} else {
			$response->setResult(false);
		}
		return $response;
	}
}