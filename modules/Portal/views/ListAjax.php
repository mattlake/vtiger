<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Portal_ListAjax_View extends Vtiger_List_View {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('getRecordCount');
		$this->exposeMethod('getPageCount');
		$this->exposeMethod('getListViewCount');
	}

	function preProcess(\Http\Request $request) {
		return true;
	}

	function postProcess(\Http\Request $request) {
		return true;
	}

	function process(\Http\Request $request) {
		$mode = $request->get('mode');
		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	public function getListViewCount(\Http\Request $request) {
		$listViewModel = new Portal_ListView_Model();
		$countResult = $listViewModel->getRecordCount();
		return $countResult;
	}

	public function getRecordCount(\Http\Request $request) {

		$countResult = $this->getListViewCount($request);
		$result['count'] = $countResult;
		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Function to get the page count for list
	 * @return total number of pages
	 */
	function getPageCount(\Http\Request $request) {
		$listViewCount = $this->getListViewCount($request);
		$pagingModel = new Vtiger_Paging_Model();
		$pageLimit = $pagingModel->getPageLimit();
		$pageCount = ceil((int) $listViewCount / (int) $pageLimit);

		if ($pageCount == 0) {
			$pageCount = 1;
		}
		$result = array();
		$result['page'] = $pageCount;
		$result['numberOfRecords'] = $listViewCount;
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

}
