<?php

use Http\Request;

class Vtiger_Index_View extends Vtiger_Basic_View
{

    function __construct()
    {
        parent::__construct();
    }

    public function requiresPermission(Request $request)
    {
        $permissions = parent::requiresPermission($request);
        $permissions[] = array(
            'module_parameter' => 'module',
            'action' => 'DetailView',
            'record_parameter' => 'record'
        );
        return $permissions;
    }

    public function preProcess(Request $request, $display = true)
    {
        parent::preProcess($request, false);

        $viewer = $this->getViewer($request);

        $moduleName = $request->getModule();
        if (! empty($moduleName)) {
            $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
            $viewer->assign('MODULE', $moduleName);
            $linkParams = array('MODULE' => $moduleName, 'ACTION' => $request->get('view'));
            $linkModels = $moduleModel->getSideBarLinks($linkParams);

            $viewer->assign('QUICK_LINKS', $linkModels);
            $this->setModuleInfo($request, $moduleModel);
        }

        $viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('REQUEST_INSTANCE', $request);
        $viewer->assign('CURRENT_VIEW', $request->get('view'));
        if ($display) {
            $this->preProcessDisplay($request);
        }
    }

    /**
     * Setting module related Information to $viewer (for Vtiger7)
     * @param type $request
     * @param type $moduleModel
     */
    public function setModuleInfo($request, $moduleModel)
    {
        $fieldsInfo = array();
        $basicLinks = array();
        $settingLinks = array();

        $moduleFields = $moduleModel->getFields();
        foreach ($moduleFields as $fieldName => $fieldModel) {
            $fieldsInfo[$fieldName] = $fieldModel->getFieldInfo();
        }

        $moduleBasicLinks = $moduleModel->getModuleBasicLinks();
        if ($moduleBasicLinks) {
            foreach ($moduleBasicLinks as $basicLink) {
                $basicLinks[] = Vtiger_Link_Model::getInstanceFromValues($basicLink);
            }
        }

        $moduleSettingLinks = $moduleModel->getSettingLinks();
        if ($moduleSettingLinks) {
            foreach ($moduleSettingLinks as $settingsLink) {
                $settingLinks[] = Vtiger_Link_Model::getInstanceFromValues($settingsLink);
            }
        }
        $viewer = $this->getViewer($request);
        $viewer->assign('FIELDS_INFO', json_encode($fieldsInfo));
        $viewer->assign('MODULE_BASIC_ACTIONS', $basicLinks);
        $viewer->assign('MODULE_SETTING_ACTIONS', $settingLinks);
    }

    public function postProcess(Request $request)
    {
        $moduleName = $request->getModule();
        $viewer = $this->getViewer($request);
        $viewer->view('IndexPostProcess.tpl', $moduleName);

        parent::postProcess($request);
    }

    //Note : To get the right hook for immediate parent in PHP,
    // specially in case of deep hierarchy
    /*function preProcessParentTplName(\Http\Request $request) {
        return parent::preProcessTplName($request);
    }*/

    public function process(Request $request)
    {
        $moduleName = $request->getModule();
        $viewer = $this->getViewer($request);
        $viewer->view('Index.tpl', $moduleName);
    }

    /**
     * Function to get the list of Script models to be included
     * @param Request $request
     * @return <Array> - List of Vtiger_JsScript_Model instances
     */
    function getHeaderScripts(Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();

        $jsFileNames = array(
            'modules.Vtiger.resources.Vtiger',
            "modules.$moduleName.resources.$moduleName",
            "~libraries/jquery/jquery.stickytableheaders.min.js",
        );

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
        return $headerScriptInstances;
    }

    public function validateRequest(Request $request)
    {
        $request->validateReadAccess();
    }

    protected function preProcessTplName(Request $request)
    {
        return 'IndexViewPreProcess.tpl';
    }
}