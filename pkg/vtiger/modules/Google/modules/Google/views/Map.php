<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Google_Map_View extends Vtiger_Detail_View {

	function checkPermission(\Http\Request $request) {
		$moduleName = $request->getModule();
		$recordId = $request->get('record');

		$recordPermission = Users_Privileges_Model::isPermitted($moduleName, 'DetailView', $recordId);
		if(!$recordPermission) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
		}

		return true;
	}

	/**
	 * must be overriden
	 * @param \Http\Request $request
	 * @return boolean 
	 */
	function preProcess(\Http\Request $request) {
		return true;
	}

	/**
	 * must be overriden
	 * @param \Http\Request $request
	 * @return boolean 
	 */
	function postProcess(\Http\Request $request) {
		return true;
	}

	/**
	 * called when the request is recieved.
	 * if viewtype : detail then show location
	 * TODO : if viewtype : list then show the optimal route.
	 * @param \Http\Request $request
	 */
	function process(\Http\Request $request) {
		switch ($request->get('viewtype')) {
			case 'detail':$this->showLocation($request);
				break;
			default:break;
		}
	}

	/**
	 * display the template.
	 * @param \Http\Request $request
	 */
	function showLocation(\Http\Request $request) {
		$viewer = $this->getViewer($request);
		// record and source_module values to be passed to populate the values in the template,
		// required to get the respective records address based on the module type.
		$viewer->assign('RECORD', $request->get('record'));
		$viewer->assign('SOURCE_MODULE', $request->get('source_module'));
		$viewer->view('map.tpl', $request->getModule());
	}

}