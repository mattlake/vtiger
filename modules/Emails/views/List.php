<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Emails_List_View extends Vtiger_List_View {

	
	public function requiresPermission(\Http\Request $request) {
		$permissions = parent::requiresPermission($request);
		$permissions[] = array('module_parameter' => 'custom_module', 'action' => 'DetailView');
		$request->set('custom_module', 'MailManager');
		return $permissions;
	}
	
	public function checkPermission(\Http\Request $request) {
		return parent::checkPermission($request);
	}
	
	public function preProcess(\Http\Request $request) {
	}

	public function process(\Http\Request $request) {
		header('Location: index.php?module=MailManager&view=List');
	}
}