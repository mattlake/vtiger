<?php

use Http\Request;

/**
 * Abstract Controller Class
 */
abstract class Vtiger_Controller
{

    protected $exposedMethods = array();

    function __construct()
    {
    }

    function loginRequired()
    {
        return true;
    }

    abstract function getViewer(Request $request);

    abstract function process(Request $request);

    function validateRequest(Request $request)
    {
    }

    function preProcess(Request $request)
    {
    }

    // Control the exposure of methods to be invoked from client (kind-of RPC)

    function postProcess(Request $request)
    {
    }

    /**
     * Function invokes exposed methods for this class
     * @param string $name - method name
     * @param Request $request
     * @throws Exception
     */
    function invokeExposedMethod()
    {
        $parameters = func_get_args();
        $name = array_shift($parameters);
        if (! empty($name) && $this->isMethodExposed($name)) {
            return call_user_func_array(array($this, $name), $parameters);
        }
        throw new Exception(vtranslate('LBL_NOT_ACCESSIBLE'));
    }

    /**
     * Function checks if the method is exposed for client usage
     * @param string $name - method name
     * @return boolean
     */
    function isMethodExposed($name)
    {
        if (in_array($name, $this->exposedMethods)) {
            return true;
        }
        return false;
    }

    /**
     * Function that will expose methods for external access
     * @param string $name - method name
     */
    protected function exposeMethod($name)
    {
        if (! in_array($name, $this->exposedMethods)) {
            $this->exposedMethods[] = $name;
        }
    }
}

/**
 * Abstract Action Controller Class
 */
abstract class Vtiger_Action_Controller extends Vtiger_Controller
{
    function __construct()
    {
        parent::__construct();
    }

    function getViewer(Request $request)
    {
        throw new AppException ('Action - implement getViewer - JSONViewer');
    }

    function validateRequest(Request $request)
    {
        return $request->validateReadAccess();
    }

    function preProcess(Request $request)
    {
        return true;
    }

    function postProcess(Request $request)
    {
        return true;
    }

    /**
     * @param Request $request
     *
     * @return bool
     * @throws AppException
     */
    function checkPermission(Request $request)
    {
        $permissions = $this->requiresPermission($request);
        foreach ($permissions as $permission) {
            if (array_key_exists('module_parameter', $permission)) {
                if ($request->has($permission['module_parameter']) && ! empty(
                    $request->get(
                        $permission['module_parameter']
                    )
                    )) {
                    $moduleParameter = $request->get($permission['module_parameter']);
                } elseif ($request->has('record') && ! empty($request->get('record'))) {
                    $moduleParameter = getSalesEntityType($request->get('record'));
                }
            } else {
                $moduleParameter = 'module';
            }
            if (array_key_exists('record_parameter', $permission)) {
                $recordParameter = $request->get($permission['record_parameter']);
            } else {
                $recordParameter = '';
            }
            if (! Users_Privileges_Model::isPermitted($moduleParameter, $permission['action'], $recordParameter)) {
                throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
            }
        }

        return true;
    }

    //TODO: need to revisit on this as we are not sure if this is helpful
    /*function preProcessParentTplName(\Http\Request $request) {
        return false;
    }*/

    /**
     * This will return all the permission checks that should be done
     * @param Request $request
     * @return array
     */
    function requiresPermission(Request $request)
    {
        return array();
    }

    protected function preProcessDisplay(Request $request)
    {
    }

    protected function preProcessTplName(Request $request)
    {
        return false;
    }
}

/**
 * Abstract View Controller Class
 */
abstract class Vtiger_View_Controller extends Vtiger_Action_Controller
{

    protected $viewer;

    function __construct()
    {
        parent::__construct();
    }

    function preProcess(Request $request, $display = true)
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $viewer = $this->getViewer($request);
        $viewer->assign('PAGETITLE', $this->getPageTitle($request));
        $viewer->assign('SCRIPTS', $this->getHeaderScripts($request));
        $viewer->assign('STYLES', $this->getHeaderCss($request));
        $viewer->assign('SKIN_PATH', Vtiger_Theme::getCurrentUserThemePath());
        $viewer->assign('LANGUAGE_STRINGS', $this->getJSLanguageStrings($request));
        $viewer->assign('LANGUAGE', $currentUser->get('language'));

        if ($request->getModule() != 'Install') {
            $userCurrencyInfo = getCurrencySymbolandCRate($currentUser->get('currency_id'));
            $viewer->assign('USER_CURRENCY_SYMBOL', $userCurrencyInfo['symbol']);
        }
        $viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());

        if ($display) {
            $this->preProcessDisplay($request);
        }
    }

    function getViewer(Request $request)
    {
        if (! $this->viewer) {
            global $vtiger_current_version, $vtiger_display_version, $onlyV7Instance;
            $viewer = new Vtiger_Viewer();
            $viewer->assign('APPTITLE', getTranslatedString('APPTITLE'));
            $viewer->assign('VTIGER_VERSION', $vtiger_current_version);
            $viewer->assign('VTIGER_DISPLAY_VERSION', $vtiger_display_version);
            $viewer->assign('ONLY_V7_INSTANCE', $onlyV7Instance);
            $this->viewer = $viewer;
        }
        return $this->viewer;
    }

    function getPageTitle(Request $request)
    {
        $moduleName = $request->getModule();
        $recordId = $request->get('record');
        if ($recordId && $moduleName) {
            $module = Vtiger_Module_Model::getInstance($moduleName);
            if ($module && $module->isEntityModule()) {
                $recordName = Vtiger_Util_Helper::getRecordName($recordId);
            }
        }

        if ($recordName) {
            return vtranslate($moduleName, $moduleName) . ' - ' . $recordName;
        } else {
            $currentLang = Vtiger_Language_Handler::getLanguage();
            $customWebTitle = Vtiger_Language_Handler::getLanguageTranslatedString(
                $currentLang,
                'LBL_' . $moduleName . '_WEBTITLE',
                $request->getModule(false)
            );
            if ($customWebTitle) {
                return $customWebTitle;
            }
            return vtranslate($moduleName, $moduleName);
        }
    }

    /**
     * Retrieves headers scripts that need to loaded in the page
     * @param Request $request - request model
     * @return array<int,string> - array of Vtiger_JsScript_Model
     */
    function getHeaderScripts(Request $request)
    {
        $headerScriptInstances = array();
        $languageHandlerShortName = Vtiger_Language_Handler::getShortLanguageName();
        $fileName = "libraries/jquery/posabsolute-jQuery-Validation-Engine/js/languages/jquery.validationEngine-$languageHandlerShortName.js";
        if (! file_exists($fileName)) {
            $fileName = "~libraries/jquery/posabsolute-jQuery-Validation-Engine/js/languages/jquery.validationEngine-en.js";
        } else {
            $fileName = "~libraries/jquery/posabsolute-jQuery-Validation-Engine/js/languages/jquery.validationEngine-$languageHandlerShortName.js";
        }
        $jsFileNames = array($fileName);
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($jsScriptInstances, $headerScriptInstances);
        return $headerScriptInstances;
    }

    //Note : To get the right hook for immediate parent in PHP,
    // specially in case of deep hierarchy
    //TODO: Need to revisit this.
    /*function preProcessParentTplName(\Http\Request $request) {
        return parent::preProcessTplName($request);
    }*/

    function checkAndConvertJsScripts($jsFileNames)
    {
        $fileExtension = 'js';

        $jsScriptInstances = array();
        if ($jsFileNames) {
            foreach ($jsFileNames as $jsFileName) {
                // TODO Handle absolute inclusions (~/...) like in checkAndConvertCssStyles
                $jsScript = new Vtiger_JsScript_Model();

                // external javascript source file handling
                if (strpos($jsFileName, 'http://') === 0 || strpos($jsFileName, 'https://') === 0) {
                    $jsScriptInstances[$jsFileName] = $jsScript->set('src', $jsFileName);
                    continue;
                }

                $completeFilePath = Vtiger_Loader::resolveNameToPath($jsFileName, $fileExtension);

                if (file_exists($completeFilePath)) {
                    if (strpos($jsFileName, '~') === 0) {
                        $filePath = ltrim(ltrim($jsFileName, '~'), '/');
                        // if ~~ (reference is outside vtiger6 folder)
                        if (substr_count($jsFileName, "~") == 2) {
                            $filePath = "../" . $filePath;
                        }
                    } else {
                        $filePath = str_replace('.', '/', $jsFileName) . '.' . $fileExtension;
                    }

                    $jsScriptInstances[$jsFileName] = $jsScript->set('src', $filePath);
                } else {
                    $fallBackFilePath = Vtiger_Loader::resolveNameToPath(
                        Vtiger_JavaScript::getBaseJavaScriptPath() . '/' . $jsFileName,
                        'js'
                    );
                    if (file_exists($fallBackFilePath)) {
                        $filePath = str_replace('.', '/', $jsFileName) . '.js';
                        $jsScriptInstances[$jsFileName] = $jsScript->set(
                            'src',
                            Vtiger_JavaScript::getFilePath($filePath)
                        );
                    }
                }
            }
        }
        return $jsScriptInstances;
    }

    /**
     * Retrieves css styles that need to loaded in the page
     * @param Request $request - request model
     * @return array - array of Vtiger_CssScript_Model
     */
    function getHeaderCss(Request $request)
    {
        return array();
    }

    /**
     * Function returns the Client side language string
     * @param Request $request
     */
    function getJSLanguageStrings(Request $request)
    {
        $moduleName = $request->getModule(false);
        if ($moduleName === 'Settings:Users') {
            $moduleName = 'Users';
        }
        return Vtiger_Language_Handler::export($moduleName, 'jsLanguageStrings');
    }

    protected function preProcessDisplay(Request $request)
    {
        $viewer = $this->getViewer($request);
        $displayed = $viewer->view($this->preProcessTplName($request), $request->getModule(false));
        /*if(!$displayed) {
            $tplName = $this->preProcessParentTplName($request);
            if($tplName) {
                $viewer->view($tplName, $request->getModule());
            }
        }*/
    }

    protected function preProcessTplName(Request $request)
    {
        return 'Header.tpl';
    }

    function postProcess(Request $request)
    {
        $moduleName = $request->getModule();
        $viewer = $this->getViewer($request);
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $viewer->assign('ACTIVITY_REMINDER', $currentUser->getCurrentUserActivityReminderInSeconds());
        $viewer->view('Footer.tpl', $moduleName);
    }

    /**
     * Function returns the css files
     * @param array $cssFileNames
     * @param string $fileExtension
     * @return array<Vtiger_CssScript_Model>
     *
     * First check if $cssFileName exists
     * if not, check under layout folder $cssFileName eg:layouts/vlayout/$cssFileName
     */
    function checkAndConvertCssStyles($cssFileNames, $fileExtension = 'css')
    {
        $cssStyleInstances = array();
        foreach ($cssFileNames as $cssFileName) {
            $cssScriptModel = new Vtiger_CssScript_Model();

            if (strpos($cssFileName, 'http://') === 0 || strpos($cssFileName, 'https://') === 0) {
                $cssStyleInstances[] = $cssScriptModel->set('href', $cssFileName);
                continue;
            }
            $completeFilePath = Vtiger_Loader::resolveNameToPath($cssFileName, $fileExtension);
            $filePath = null;
            if (file_exists($completeFilePath)) {
                if (strpos($cssFileName, '~') === 0) {
                    $filePath = ltrim(ltrim($cssFileName, '~'), '/');
                    // if ~~ (reference is outside vtiger6 folder)
                    if (substr_count($cssFileName, "~") == 2) {
                        $filePath = "../" . $filePath;
                    }
                } else {
                    $filePath = str_replace('.', '/', $cssFileName) . '.' . $fileExtension;
                    $filePath = Vtiger_Theme::getStylePath($filePath);
                }
                $cssStyleInstances[] = $cssScriptModel->set('href', $filePath);
            }
        }
        return $cssStyleInstances;
    }
}