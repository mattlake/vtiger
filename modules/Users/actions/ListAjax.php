<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Users_ListAjax_Action extends Vtiger_BasicAjax_Action{
	function __construct() {
		parent::__construct();
	}

    public function requiresPermission(\Http\Request $request) {
		return array();
	}
    
	function checkPermission(\Http\Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		if(!$currentUser->isAdminUser()) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED', 'Vtiger'));
		}
	}

	function preProcess(\Http\Request $request) {
		return true;
	}

	function postProcess(\Http\Request $request) {
		return true;
	}

	function process(\Http\Request $request) {
		$mode = $request->get('mode');
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}
}
