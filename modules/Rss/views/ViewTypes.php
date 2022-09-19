<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Rss_ViewTypes_View extends Vtiger_IndexAjax_View {

    function __construct() {
        parent::__construct();
        $this->exposeMethod('getRssWidget');
        $this->exposeMethod('getRssAddForm');
    }
        
	/**
     * Function to display rss sidebar widget
     * @param <\Http\Request> $request
     */
    public function getRssWidget(\Http\Request $request) {
        $module = $request->get('module');
        $moduleModel = Vtiger_Module_Model::getInstance($module);
        $rssSources = $moduleModel->getRssSources();
        $viewer = $this->getViewer($request);
        $viewer->assign('MODULE', $module);
        $viewer->assign('RSS_SOURCES', $rssSources);
        echo $viewer->view('RssWidgetContents.tpl', $module, true);
    }
    
    /**
     * Function to get the rss add form 
     * @param <\Http\Request> $request
     */
    public function getRssAddForm(\Http\Request $request) {
        $module = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($module);
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE',$module);
        $viewer->view('RssAddForm.tpl', $module);
    }
   
}
