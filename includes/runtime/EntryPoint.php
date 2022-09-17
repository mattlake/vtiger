<?php

use Http\Request;

vimport('includes.exceptions.AppException');

vimport('includes.http.Request');
vimport('includes.http.Response');
vimport('includes.http.Session');

vimport('includes.runtime.Globals');
vimport('includes.runtime.Controller');
vimport('includes.runtime.Viewer');
vimport('includes.runtime.Theme');
vimport('includes.runtime.BaseModel');
vimport('includes.runtime.JavaScript');

vimport('includes.runtime.LanguageHandler');
vimport('includes.runtime.Cache');
vimport('vtlib.Vtiger.Runtime');

abstract class Vtiger_EntryPoint
{

    /**
     * Login data
     */
    protected $login = false;

    /**
     * Check if login data is present.
     */
    function hasLogin()
    {
        return $this->getLogin() ? true : false;
    }

    /**
     * Get login data.
     */
    function getLogin()
    {
        return $this->login;
    }

    /**
     * Set login data.
     */
    function setLogin($login)
    {
        if ($this->login) {
            throw new AppException('Login is already set.');
        }
        $this->login = $login;
    }

    abstract function process(Request $request);
}