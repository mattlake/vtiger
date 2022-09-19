<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Documents_ListAjax_View extends Documents_List_View {
	function __construct() {
		parent::__construct();
		$this->exposeMethod('getRecordsCount');
		$this->exposeMethod('getPageCount');
		$this->exposeMethod('showSearchResults');
		$this->exposeMethod('ShowListColumnsEdit');
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

	/**
	 * Extending Vtiger List Ajax API to show Advance Search results
	 * @param \Http\Request $request
	 */
	public function showSearchResults(\Http\Request $request) {
		$vtigerListAjaxInstance = new Vtiger_ListAjax_View();
		$vtigerListAjaxInstance->showSearchResults($request);
	}

	/**
	 * Extending Vtiger List Ajax API to show List Columns Edit view
	 * @param \Http\Request $request
	 */
	public function ShowListColumnsEdit(\Http\Request $request){
		$vtigerListAjaxInstance = new Vtiger_ListAjax_View();
		$vtigerListAjaxInstance->ShowListColumnsEdit($request);
	}
}