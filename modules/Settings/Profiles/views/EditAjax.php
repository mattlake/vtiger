<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Class Settings_Profiles_EditAjax_View extends Settings_Profiles_Edit_View {

    public function preProcess(\Http\Request $request, $display=true) {
        return true;
    }
    
    public function postProcess(\Http\Request $request) {
        return true;
    }
    
    public function process(\Http\Request $request) {
        echo $this->getContents($request);
    }
    
    public function getContents(\Http\Request $request) {
        $this->initialize($request);
		
        $qualifiedModuleName = $request->getModule(false);
        $viewer = $this->getViewer ($request);
		$viewer->assign('SCRIPTS', $this->getHeaderScripts($request));
        $viewer->assign('SHOW_EXISTING_PROFILES', true);
        return $viewer->view('EditViewContents.tpl',$qualifiedModuleName,true);
    }
	
	/**
	 * Function to get the list of Script models to be included
	 * @param \Http\Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getHeaderScripts(\Http\Request $request) {
		$moduleName = $request->getModule();

		$jsFileNames = array(
			"modules.Settings.Profiles.resources.Profiles",
		);
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		return $jsScriptInstances;
	}
    
}
