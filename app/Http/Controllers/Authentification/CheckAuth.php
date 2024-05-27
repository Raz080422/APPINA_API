<?php

namespace App\Http\Controllers\Authentification;

use App\Models\Management\Config\ConfigModel;

use Illuminate\Http\Request;
use Validator, DB, Redirect, Hash, Auth, Session;

class CheckAuth
{
    public function __construct( ConfigModel $configItem)
    {
        $this->configData = $configItem;
    }
    public function getAuth(string $token){
        $result = false;
        $dataToken = explode('|',$token);

        $username   = $dataToken[0];
        $password   = $dataToken[1];
        try{
            if(Hash::check(strtoupper('AdminAPIINA'),$username)){
                if(Hash::check('P@ssw0rd',$password)){
                    $result = true;
                }
            }
        } catch(\Exception $ex){
            $result = false;
        }
        return $result;
    }
}
