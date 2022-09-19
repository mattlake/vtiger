<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

// TODO This is a stop-gap measure to have the
// user continue working with Calendar when dropping from Event View.
class Events_Calendar_View extends Vtiger_Index_View {
	
	public function requiresPermission(\Http\Request $request){
		$permissions = parent::requiresPermission($request);
		$permissions[] = array('module_parameter' => 'custom_module', 'action' => 'DetailView');
		$request->set('custom_module', 'Calendar');
		return $permissions;
	}
	
	public function checkPermission(\Http\Request $request) {
		return parent::checkPermission($request);
	}
	
	public function preProcess(\Http\Request $request, $display = true) {}
	public function postProcess(\Http\Request $request) {}
	
	public function process(\Http\Request $request) {
		header("Location: index.php?module=Calendar&view=Calendar");
	}
}
