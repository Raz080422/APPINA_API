<?php

namespace App\Http\Controllers\Management\Document;
use App\Models\Management\Document\DocumentModel;
use App\Http\Controllers\Authentification\CheckAuth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Atlasian\Confluence\ConfluenceController;
use Illuminate\Http\Request;

use Carbon\Carbon;

class DocumentController extends Controller
{
    public function __construct(Request $request, DocumentModel $document, CheckAuth $auth, ConfluenceController $confluence)
    {
        $this->document = $document;
        $this->request = $request;
        $this->auth = $auth;
        $this->confluence = $confluence;
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
    public function GetAllDocument(){
        $token = $this->request->input('token');
        $reqData = $this->request->input('reqData');
        $response = $this->document->GetAllDocument($reqData);

        $data = $this->ControllerResponse($token, $response);
        return response()->json($data);
    }
    public function GetMappingDocument(){
        $token = $this->request->input('token');
        $reqData = $this->request->input('reqData');
        $response = $this->document->GetMappingDocument($reqData);

        $data = $this->ControllerResponse($token, $response);
        return response()->json($data);
    }
    public function GetTemplate(){
        $token = $this->request->input('token');
        $response = $this->document->GetAllTemplate();

        $data = $this->ControllerResponse($token, $response);
        return response()->json($data);
    }
    public function GetDetailTemplate(){
        $token = $this->request->input('token');
        $reqData = $this->request->input('reqData');
        $response = $this->document->GetDetailTemplate($reqData);

        $data = $this->ControllerResponse($token, $response);
        return response()->json($data);
    }

    public function GetRootTemplate(){
        $token = $this->request->input('token');
        $reqData = $this->request->input('reqData');
        $response = $this->document->GetRootTemplate();

        $data = $this->ControllerResponse($token, $response);
        return response()->json($data);
    }
    public function UpdateTemplate(){
        $token = $this->request->input('token');
        $templateId = $this->request->input('templateId');
        $tittle = $this->request->input('tittle');
        $rootPage = $this->request->input('rootPage');
        $categoryPage = $this->request->input('categoryPage');
        $templateName = $this->request->input('templateName');
        $action = $this->request->input('action');
        $status = $this->request->input('status');

        $dataUpdate = [
            'szTitle'       => $tittle,
            'szRootPage'    => $rootPage,
            'szCategory'    => $categoryPage,
            'szStatus'      => $status,
            'szTemplateName'=> $templateName,
            'dtmLastUpdated'=> Carbon::now()->toDateTimeString()
        ];
        if(strtolower($action) == 'edit'){
            $data = $this->document->UpdateTemplate($templateId, $dataUpdate);
            if($data){
                $data = 'Sukses Update';
            }
        }else if(strtolower($action) == 'update'){
            $data = $this->confluence->UpdateConfluenceTemplate($templateId, $templateName);
            // print_r($data);die;
            if($data['RESPONSE_CODE'] == '0001'){
                $data = $this->document->UpdateTemplate($templateId, $dataUpdate);
                if($data){
                    $data = 'Sukses Update';
                }

        // print_r($data);die;
        // print_r($data);die;

            }
        }
        $data = $this->ControllerResponse($token, $data);
        // print_r($data);die;
        return response()->json($data);
        // print_r($dataUpdate);die;

    }
}
