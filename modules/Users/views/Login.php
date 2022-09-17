<?php

use Http\Request;

vimport('~~/vtlib/Vtiger/Net/Client.php');

class Users_Login_View extends Vtiger_View_Controller
{

    function loginRequired()
    {
        return false;
    }

    function checkPermission(Request $request)
    {
        return true;
    }

    function preProcess(Request $request, $display = true)
    {
        $viewer = $this->getViewer($request);
        $viewer->assign('PAGETITLE', $this->getPageTitle($request));
        $viewer->assign('SCRIPTS', $this->getHeaderScripts($request));
        $viewer->assign('STYLES', $this->getHeaderCss($request));
        $viewer->assign('MODULE', $request->getModule());
        $viewer->assign('VIEW', $request->get('view'));
        $viewer->assign('LANGUAGE_STRINGS', array());
        if ($display) {
            $this->preProcessDisplay($request);
        }
    }

    function getPageTitle(Request $request)
    {
        $companyDetails = Vtiger_CompanyDetails_Model::getInstanceById();
        return $companyDetails->get('organizationname');
    }

    function getHeaderScripts(Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);

        $jsFileNames = array(
            '~libraries/jquery/boxslider/jquery.bxslider.min.js',
            'modules.Vtiger.resources.List',
            'modules.Vtiger.resources.Popup',
        );
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($jsScriptInstances, $headerScriptInstances);
        return $headerScriptInstances;
    }

    function process(Request $request)
    {
        $finalJsonData = array();

        $modelInstance = Settings_ExtensionStore_Extension_Model::getInstance();
        $news = $modelInstance->getNews();

        if ($news && $news['result']) {
            $jsonData = $news['result'];
            $oldTextLength = vglobal('listview_max_textlength');
            foreach ($jsonData as $blockData) {
                if ($blockData['type'] === 'feature') {
                    $blockData['heading'] = "What's new in Vtiger Cloud";
                } else {
                    if ($blockData['type'] === 'news') {
                        $blockData['heading'] = "Latest News";
                        $blockData['image'] = '';
                    }
                }

                vglobal('listview_max_textlength', 80);
                $blockData['displayTitle'] = textlength_check($blockData['title']);

                vglobal('listview_max_textlength', 200);
                $blockData['displaySummary'] = textlength_check($blockData['summary']);
                $finalJsonData[$blockData['type']][] = $blockData;
            }
            vglobal('listview_max_textlength', $oldTextLength);
        }

        $viewer = $this->getViewer($request);
        $viewer->assign('DATA_COUNT', count($jsonData));
        $viewer->assign('JSON_DATA', $finalJsonData);

        $mailStatus = $request->get('mailStatus');
        $error = $request->get('error');
        $message = '';
        if ($error) {
            switch ($error) {
                case 'login'        :
                    $message = 'Invalid credentials';
                    break;
                case 'fpError'        :
                    $message = 'Invalid Username or Email address';
                    break;
                case 'statusError'    :
                    $message = 'Outgoing mail server was not configured';
                    break;
            }
        } else {
            if ($mailStatus) {
                $message = 'Mail has been sent to your inbox, please check your e-mail';
            }
        }

        $viewer->assign('ERROR', $error);
        $viewer->assign('MESSAGE', $message);
        $viewer->assign('MAIL_STATUS', $mailStatus);
        $viewer->view('Login.tpl', 'Users');
    }

    function postProcess(Request $request)
    {
        $moduleName = $request->getModule();
        $viewer = $this->getViewer($request);
        $viewer->view('Footer.tpl', $moduleName);
    }
}