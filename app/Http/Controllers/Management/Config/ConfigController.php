<?php

namespace App\Http\Controllers\Management\Config;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ModelControllers\ProjectItem;
use App\Http\Controllers\Authentification\CheckAuth;

use App\Models\Management\Config\ConfigModel;

use Illuminate\Http\Request;
use Validator, DB, DateTime, Session;

class ConfigController extends Controller
{
    public function __construct(Request $request, ConfigModel $config, CheckAuth $auth)
    {
        $this->config = $config;
        $this->request = $request;
        $this->auth = $auth;
    }
    public function GetConfigValue($configname, $configitem){
        
    }
}
