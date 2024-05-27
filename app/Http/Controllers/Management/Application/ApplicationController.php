<?php

namespace App\Http\Controllers\Management\Application;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Authentification\CheckAuth;

use App\Models\Management\Application\ApplicationModel;
;

use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function __construct(Request $request, ApplicationModel $application, CheckAuth $auth)
    {
        $this->application = $application;
        $this->request = $request;
        $this->auth = $auth;
    }
    public function ControllerResponse($token, $response)
    {
        try {
            if ($this->auth->getAuth($token)) {
                if ($response) {
                    $data = [
                        'RESPONSE_CODE' => '0001',
                        'RESPONSE_DESC' => 'SUCCESS',
                        'RESPONSE_DATA' => $response
                    ];
                } else {
                    $data = [
                        'RESPONSE_CODE' => '0002',
                        'RESPONSE_DESC' => 'Data Not Found',
                        'RESPONSE_DATA' => ''
                    ];
                }
            } else {
                $data = [
                    'RESPONSE_CODE' => '0002',
                    'RESPONSE_DESC' => 'Failed Authentification',
                    'RESPONSE_DATA' => ''
                ];
            }
        } catch (\Exception $ex) {
            $data = [
                'RESPONSE_CODE' => '0002',
                'RESPONSE_DESC' => 'Request Exception : ' . $ex,
                'RESPONSE_DATA' => ''
            ];
        }
        return $data;
    }
    public function GetAllApplication(){
        $token = $this->request->input('token');
        $reqData = $this->request->input('reqData');
        $response = $this->application->GetAllApplication();

        $data = $this->ControllerResponse($token, $response);
        return response()->json($data);
    }
    public function GetDetailApplication(){
        $token = $this->request->input('token');
        $reqData = $this->request->input('reqData');
        $response = $this->application->GetDetailApplication($reqData);

        $data = $this->ControllerResponse($token, $response);
        return response()->json($data);
    }
}
