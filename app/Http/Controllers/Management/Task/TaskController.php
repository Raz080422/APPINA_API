<?php

namespace App\Http\Controllers\Management\Task;
use App\Models\Management\Task\TaskModel;
use App\Http\Controllers\Authentification\CheckAuth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(Request $request, TaskModel $task, CheckAuth $auth)
    {
        $this->task = $task;
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
    public function GetAllTask(){
        $token = $this->request->input('token');
        $reqData = $this->request->input('reqData');
        $response = $this->task->GetAllTask($reqData);

        $data = $this->ControllerResponse($token, $response);
        return response()->json($data);
    }
    public function GetDetailTask(){
        $token = $this->request->input('token');
        $reqData = $this->request->input('reqData');
        $arraydata = explode('|', $reqData);
        $projectid = $arraydata[0];
        $itemnumber = $arraydata[1];

        $response = $this->task->GetDetailTask($projectid, $itemnumber);

        $data = $this->ControllerResponse($token, $response);
        return response()->json($data);
    }
}
