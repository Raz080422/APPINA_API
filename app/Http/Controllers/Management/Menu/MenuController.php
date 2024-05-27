<?php

namespace App\Http\Controllers\Management\Menu;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Authentification\CheckAuth;

use App\Models\Management\Menu\MenuModel;

use Illuminate\Http\Request;
use Validator, DB, DateTime, Session, Carbon\Carbon;

class MenuController extends Controller
{
    public function __construct(Request $request, MenuModel $menu, CheckAuth $auth)
    {
        $this->menu = $menu;
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
    public function GetMenu()
    {
        $token = $this->request->input('token');
        $reqData = $this->request->input('reqData');
        $response = $this->menu->GetMenu($reqData);

        $data = $this->ControllerResponse($token, $response);
        return response()->json($data);
    }
}
