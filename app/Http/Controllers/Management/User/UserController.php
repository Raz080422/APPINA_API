<?php

namespace App\Http\Controllers\Management\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ModelControllers\ProjectItem;
use App\Http\Controllers\Authentification\CheckAuth;

use App\Models\Management\User\UserModel;

use Illuminate\Http\Request;
use Validator, DB, DateTime, Session, Carbon\Carbon;

class UserController extends Controller
{
    public function __construct(Request $request, UserModel $user, CheckAuth $auth)
    {
        $this->user = $user;
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
    public function GetAllUser()
    {
        $token = $this->request->input('token');
        $reqData = $this->request->input('reqData');
        $response = $this->user->GetUser();

        $data = $this->ControllerResponse($token, $response);
        return response()->json($data);
    }
    public function GetLoginDetail()
    {
        $token = $this->request->input('token');
        $reqData = $this->request->input('reqData');
        $response = $this->user->GetLoginDetail($reqData);

        $data = $this->ControllerResponse($token, $response);
        return response()->json($data);
    }

    public function GetDetailUser()
    {
        $token = $this->request->input('token');
        $reqData = $this->request->input('reqData');
        $response = $this->user->GetDetailUser($reqData);

        $data = $this->ControllerResponse($token, $response);
        return response()->json($data);
    }

    public function GetTeam()
    {
        $token = $this->request->input('token');
        $reqData = $this->request->input('reqData');
        $response = $this->user->GetTeam();

        $data = $this->ControllerResponse($token, $response);
        return response()->json($data);
    }

    public function CheckUser()
    {
        $token = $this->request->input('token');
        $reqData = $this->request->input('reqData');
        $response = $this->user->CheckUser($reqData);

        $data = $this->ControllerResponse($token, $response);
        return response()->json($data);
    }

    public function postTransaction()
    {
        $token = $this->request->input('token');
        $reqData = $this->request->input('reqData');
        // var_dump($reqData);die;
        try {
            $szUserId = $this->request->input('szUserId');
            $szUserName = $this->request->input('szUserName');
            $szMemberName = $this->request->input('szMemberName');
            $szTeamRole = $this->request->input('szTeamRole');
            $szTeamId = $this->request->input('szTeamId');
            $szStatus = $this->request->input('szStatus');
            $szJGPG = $this->request->input('szJGPG');
            $szOrganization = $this->request->input('szOrganization');
            $szPartnerName = $this->request->input('szPartnerName');
            $dtmSK = $this->request->input('dtmSK');
            $szPersonalNumber = $this->request->input('szPersonalNumber');
            $szJiraId = $this->request->input('szJiraId');
            $szConfluenceId = $this->request->input('szConfluenceId');
            $szMemberId = $this->request->input('szMemberId');
            $action = $this->request->input('action');

            $rules['action'] = 'required';

            if (strtoupper($action) != 'REMOVE') {
                $rules['username'] = 'required';
            }
            if (strtoupper($action) != 'ADD') {
                $rules['idoperator'] = 'required';

                // $check = DB::table('W_OPERATOR')->where('IDOPERATOR', $idoperator)->count();
                $check = $this->management->checkUser('userId', $szUserId);
                // var_dump($check);die;
                if ($check == 0) {
                    // $data = $this->project->getUser($reqData);
                    $data = [
                        'RESPONSE_CODE' => '0002',
                        'RESPONSE_DESC' => 'Data Not Found',
                    ];

                    return response()->json($data);
                }
            } else {
                // $check = DB::table('W_OPERATOR')->where('USERNAME', $username)->count();
                $check = $this->management->checkUser('userlogin', $szUserName);
                // var_dump($check);var_dump($szUserName);die;
                if ($check > 0) {
                    $data = [
                        'RESPONSE_CODE' => '0002',
                        'RESPONSE_DESC' => 'Username ' . $szUserName . ' Already Exist',
                    ];
                    return response()->json($data);
                }
            }
            $validator = Validator::make($this->request->all(), $rules);
            if ($validator->fails()) {
                $validation_desc = $validator->errors()->first('username');
                $validation_desc .= ' ' . $validator->errors()->first('action');
                $data = [
                    'RESPONSE_CODE' => '0002',
                    'RESPONSE_DESC' => $validator->errors()
                ];
                return response()->json($data);
            }
            $dateNow = Carbon::now();
            $dataMember = [
                'szMemberId' => $szUserName,
                'szMemberName' => $szMemberName,
                'szTeamId' => $szTeamId,
                'szOrganizationalStructure' => $szOrganization,
                'szRole' => $szJGPG,
                'szPartnerName' => $szPartnerName,
                'szTeamRole' => $szTeamRole,
                'szPersonalNumber' => $szPersonalNumber,
                'szJiraId' => $szJiraId,
                'szConfluenceId ' => $szConfluenceId,
                'dtmTMTStarted' => $dtmSK,
                'dtmLastUpdated' => $dateNow->toDateTimeString()
            ];
            $dataUser = [
                'szUserId' => $szUserId,
                'dtmLastUpdated' => $dateNow->toDateTimeString(),
                'szTeamMemberId' => $szMemberId,
                'szUserLogin' => $szUserName,
                'szStatus' => $szStatus
            ];
            $additionalDate = ['dtmCreated' => $dateNow->toDateTimeString()];
            // var_dump($dataMember);die;
            // var_dump($dataUser);die;
            switch (strtoupper($action)) {
                case 'ADD':
                    // Add Creation Date
                    $dataMember = $dataMember + $additionalDate;
                    $dataUser = $dataUser + $additionalDate;
                    // Set User Id
                    $prefixUser = $this->configItem->getConfigValue('Prefix', 'User');
                    $lastIdUser = $this->configItem->getConfigValue('Counter', 'User');
                    $dataUser['szUserId'] = $prefixUser . "-" . str_pad($lastIdUser + 1, 4, 0, STR_PAD_LEFT);
                    $prefixMember = $this->configItem->getConfigValue('Prefix', 'Member');
                    $lastIdMember = $this->configItem->getConfigValue('Counter', 'Member');
                    $dataMember['szMemberId'] = $prefixMember . "-" . str_pad($lastIdMember + 1, 4, 0, STR_PAD_LEFT);
                    $dataUser['szTeamMemberId'] = $prefixMember . "-" . str_pad($lastIdMember + 1, 4, 0, STR_PAD_LEFT);
                    $exec = $this->management->insertUser($dataUser, $dataMember);
                    // var_dump($exec);die;
                    if ($exec) {

                        $data = [
                            'RESPONSE_CODE' => '0001',
                            'RESPONSE_DESC' => 'Data Saved Successfully',
                        ];
                    } else {
                        $data = [
                            'RESPONSE_CODE' => '0002',
                            'RESPONSE_DESC' => 'Data Failed to save',
                        ];
                    }
                    break;
                case 'EDIT':
                    // $exec = DB::table('W_OPERATOR')->where('IDOPERATOR', $idoperator)->update($data);
                    $exec = true;
                    if ($exec) {
                        $data = [
                            'RESPONSE_CODE' => '0001',
                            'RESPONSE_DESC' => 'Data Updated Successfully',
                        ];
                    } else {
                        $data = [
                            'RESPONSE_CODE' => '0002',
                            'RESPONSE_DESC' => 'Data Failed to update',
                        ];
                    }
                    break;
                case 'REMOVE':
                    $exec = true;
                    // $exec = DB::table('W_OPERATOR')->where('IDOPERATOR', $idoperator)->delete();
                    if ($exec) {
                        $data = [
                            'RESPONSE_CODE' => '0001',
                            'RESPONSE_DESC' => 'Data Deleted Successfully',
                        ];
                    } else {
                        $data = [
                            'RESPONSE_CODE' => '0002',
                            'RESPONSE_DESC' => 'Data Failed to remove',
                        ];
                    }
                    break;
                default:
                    $data = [
                        'RESPONSE_CODE' => '0002',
                        'RESPONSE_DESC' => 'Action Not Valid',
                    ];
                    break;
            }
            // var_dump($reqData);die;
        } catch (\Exception $ex) {
            $data = [
                'RESPONSE_CODE' => '0002',
                'RESPONSE_DESC' => 'Request Exception' . $ex
            ];
        }
        return response()->json($data);
    }
}
