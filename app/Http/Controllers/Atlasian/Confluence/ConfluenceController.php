<?php

namespace App\Http\Controllers\Atlasian\Confluence;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Authentification\CheckAuth;
use App\Http\Controllers\Log\LogController;

use App\Models\Atlasian\Confluence\ConfluenceModel;
use App\Models\Management\Project\ProjectModel;
use App\Models\Management\User\UserModel;

use App\Http\Controllers\Atlasian\Helper\ConfluenceService;

use Illuminate\Http\Request;
use Carbon\Carbon;

class ConfluenceController extends Controller
{
    public function __construct(Request $request, ConfluenceModel $confluence, CheckAuth $auth, ProjectModel $project, UserModel $user, ConfluenceService $confluenceService, LogController $log)
    {
        $this->confluence = $confluence;
        $this->project = $project;
        $this->user = $user;
        $this->log = $log;

        $this->confluenceService = $confluenceService;

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
    public function PostPage($pageId)
    {
        $dataprepare = $this->confluence->GetDataConfluence($pageId);
        $projectDetail = $this->project->GetDetailProject($dataprepare->szProjectId);
        // print_r(($dataprepare));die;
        if ($dataprepare->szCategory == "RootPage") {
            $projectName = $projectDetail->szProjectName;
        } else {
            $projectName = $dataprepare->szTittleProject . " for " . $projectDetail->szJiraCode;
            ;
        }

        $detailuser = $this->user->GetDetailUser('USERLOGIN|' . $projectDetail->szUserCreatorId);
        $token = $detailuser->szAtlasianToken;
        $url = "/rest/api/content";
        $space = $dataprepare->szSpaceKey;
        $title = $projectName;
        $ancestors = $dataprepare->szParentConfluenceId;
        $body = $dataprepare->szBody;

        $request = "{\n    \"space\": {\n        \"key\": \"$space\"\n    },\n    \"type\": \"page\",\n    \"title\": \"$title\",\n    \"ancestors\": [\n        {\n            \"id\": $ancestors\n        }\n    ],\n    \"body\": {\n        \"storage\": {\n            \"value\": \"$body\",\n            \"representation\": \"storage\"\n        }\n    }\n}";

        try {
            $data = $this->confluenceService->post_confluence_service($url, $token, $request);
            $log = $this->log->InsertLogService($request, $data, $url, $pageId);
            // print_r($log);die;
            $links = $data['_links'];
            $confluenceId = $data['id'];
            $config = (object) config('config_url');
            $base_url = $config->confluence_host;
            $shortLink = $base_url . $links['tinyui'];


            $dataProjectPage = [
                "szShortLink" => $shortLink,
                "szPageConfluenceId" => $data['id']
            ];
            $dataParentProject = [
                'szParentConfluenceId' => $data['id']
            ];
            $response = $this->confluence->UpdateDataConfluence($dataProjectPage, $pageId, $dataParentProject, $dataprepare->szTemplateId, $dataprepare->szProjectId);

            if ($response) {
                $templateId = $this->confluence->GetTemplateId($pageId);
                if ($templateId->szTemplateId == 'INATMP-0001' || $templateId->szTemplateId == 'INATMP-0014' || $templateId->szTemplateId == 'INATMP-0019' || $templateId->szTemplateId == 'INATMP-0017') {
                    $pageItem = $this->project->UpdateLinkPageItem($templateId->szProjectId, $templateId->szTemplateId, $templateId->szShortLink);
                }
            }

            // $log = $this->models_confluence->InsertLog($request, $data, $url);
            $responseData = [
                'pageLink' => $shortLink
            ];

            $data = [
                "RESPONSE_CODE" => "0001",
                "RESPONSE_DESCRIPTION" => "Transaction Success",
                "RESPONSE_DATA" => $responseData
            ];
        } catch (\Exception $ex) {
            $data = [
                "RESPONSE_CODE" => "0002",
                "RESPONSE_DESCRIPTION" => "Transaction Failed",
                "RESPONSE_DATA" => $ex->getMessage()
            ];
        }
        return $data;
    }
    public function CreateConfluencePage()
    {
        $pageId = $this->request->input("reqData");

        $data = $this->PostPage($pageId);
        return $data;
    }
    public function GetConfluencePage()
    {
        $reqData = $this->request->input("reqData");
        $request = explode("|", $reqData);
        try {
            $response = $this->GetContentPageByTittle($request[0], $request[1]);
            $data = $response['0'];
            if ($data) {
                $data = [
                    "RESPONSE_CODE" => "0001",
                    "RESPONSE_DESCRIPTION" => "Transaction Success",
                    "RESPONSE_DATA" => $data
                ];
            } else {
                $data = [
                    "RESPONSE_CODE" => "0002",
                    "RESPONSE_DESCRIPTION" => "Transaction Failed",
                    "RESPONSE_DATA" => $data
                ];
            }
        } catch (\Exception $e) {
            $data = [
                "RESPONSE_CODE" => "0002",
                "RESPONSE_DESCRIPTION" => "Transaction Exception",
                "RESPONSE_DATA" => $e->getMessage()
            ];
        }
        return $data;
    }
    public function GenerateDocument()
    {
        $projectId = $this->request->input('reqData');
        $token = $this->request->input('token');
        try {
            if ($this->auth->getAuth($token)) {
                $document = $this->confluence->GetDocument($projectId);
                foreach ($document as $key => $value) {
                    // print_r($value->szTemplateId);die;
                    $response = $this->PostPage($value->szPageId);
                    $tinyUrl = $response['RESPONSE_DATA'];
                    // print_r($tinyUrl['pageLink']);die;

                    // -----------BUGLIST------------
                    if ($value->szTemplateId == 'INATMP-0005') {
                        $docData = [
                            'szBUGLink' => $tinyUrl['pageLink'],
                            'szBUGTittle' => $value->szTittleProject
                        ];
                        $data = $this->project->UpdateProject($docData, $projectId);
                    }
                    // -----------BUGLIST------------

                    // -----------SIT------------
                    else if ($value->szTemplateId == 'INATMP-0006') {
                        $docData = [
                            'szSITLink' => $tinyUrl['pageLink'],
                            'szSITTittle' => $value->szTittleProject
                        ];
                        $data = $this->project->UpdateProject($docData, $projectId);
                    } else if ($value->szTemplateId == 'INATMP-0011') {
                        $docData = [
                            'szSITCaptureLink' => $tinyUrl['pageLink'],
                            'szSITCaptureTittle' => $value->szTittleProject,
                            'szUATCaptureLink' => $tinyUrl['pageLink'],
                            'szUATCaptureTittle' => $value->szTittleProject
                        ];
                        $data = $this->project->UpdateProject($docData, $projectId);
                    }
                    // -----------SIT------------

                    // -----------BAUAT------------
                    else if ($value->szTemplateId == 'INATMP-0007') {
                        $docData = [
                            'szUATLink' => $tinyUrl['pageLink'],
                            'szUATTittle' => $value->szTittleProject
                        ];
                        $data = $this->project->UpdateProject($docData, $projectId);
                    }
                    // -----------BAUAT------------

                    // -----------BADT------------
                    else if ($value->szTemplateId == 'INATMP-0008') {
                        $docData = [
                            'szBADTLink' => $tinyUrl['pageLink'],
                            'szBADTTittle' => $value->szTittleProject
                        ];
                        $data = $this->project->UpdateProject($docData, $projectId);
                    } else if ($value->szTemplateId == 'INATMP-0020') {
                        $docData = [
                            'szBADTCaptureLink' => $tinyUrl['pageLink'],
                            'szBADTCcaptureTittle' => $value->szTittleProject
                        ];
                        $data = $this->project->UpdateProject($docData, $projectId);
                    }
                    // -----------BADT------------

                    // -----------BAPT------------
                    else if ($value->szTemplateId == 'INATMP-0009') {
                        $docData = [
                            'szBAPTLink' => $tinyUrl['pageLink'],
                            'szBAPTTittle' => $value->szTittleProject
                        ];
                        $data = $this->project->UpdateProject($docData, $projectId);
                    } else if ($value->szTemplateId == 'INATMP-0021') {
                        $docData = [
                            'szBAPTCaptureLink' => $tinyUrl['pageLink'],
                            'szBAPTCaptureTittle' => $value->szTittleProject
                        ];
                        $data = $this->project->UpdateProject($docData, $projectId);
                    }
                    // -----------BAPT------------

                    // -----------Stress------------
                    else if ($value->szTemplateId == 'INATMP-0010') {
                        $docData = [
                            'szStressLink' => $tinyUrl['pageLink'],
                            'szStressTittle' => $value->szTittleProject
                        ];
                        $data = $this->project->UpdateProject($docData, $projectId);
                    }

                    // -----------Stress------------
                }

                if ($data) {
                    $data = [
                        'RESPONSE_CODE' => '0001',
                        'RESPONSE_DESC' => 'SUCCESS'
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
        } catch (\Exception $e) {
            $data = [
                "RESPONSE_CODE" => "0002",
                "RESPONSE_DESCRIPTION" => "Transaction Failed",
                "RESPONSE_DATA" => $e->getMessage()
            ];
            // $countDoc = $this->confluence->GetDocument($projectId);
        }
        return $data;
    }
    public function GetContentPageByTittle($userId, $tittlePage)
    {
        $pattern = array("&", "[", "]", " ", "<", ">");
        $replace = array("%26", "%5B", "%5D", "%20", "%3C", "%3E");

        try {
            $detailuser = $this->user->GetDetailUser('USERLOGIN|' . $userId);
            $token = $detailuser->szAtlasianToken;
            $inputvalidate = str_replace($pattern, $replace, $tittlePage);
            $url = '/rest/api/content?expand=body.storage&expand=ancestors&title=' . $inputvalidate;
            $request = '';
            $result = $this->confluenceService->get_confluence_service($url, $token, $request);
            $data = $result['results'];
            // print_r($result['0']);die;
        } catch (\Exception $ex) {
            $data = '';
        }
        return $data;
    }
    public function GetAncestorsPageByTittle($userId, $tittlePage)
    {
        $pattern = array("&", "[", "]", " ", "<", ">");
        $replace = array("%26", "%5B", "%5D", "%20", "%3C", "%3E");

        try {
            $detailuser = $this->user->GetDetailUser('USERLOGIN|' . $userId);
            $token = $detailuser->szAtlasianToken;
            $inputvalidate = str_replace($pattern, $replace, $tittlePage);
            $url = '/rest/api/content?expand=ancestors&title=' . $inputvalidate;
            $request = '';
            $result = $this->confluenceService->get_confluence_service($url, $token, $request);
            $data = $result['results'];
            // print_r($result['0']);die;
        } catch (\Exception $ex) {
            $data = '';
        }
        return $data;
    }
    public function GetSpacePageByTittle($userId, $tittlePage)
    {
        $pattern = array("&", "[", "]", " ", "<", ">");
        $replace = array("%26", "%5B", "%5D", "%20", "%3C", "%3E");

        try {
            $detailuser = $this->user->GetDetailUser('USERLOGIN|' . $userId);
            $token = $detailuser->szAtlasianToken;
            $inputvalidate = str_replace($pattern, $replace, $tittlePage);
            $url = '/rest/api/content?expand=space&title=' . $inputvalidate;
            $request = '';
            $result = $this->confluenceService->get_confluence_service($url, $token, $request);
            $data = $result['results'];
            // print_r($result['0']);die;
        } catch (\Exception $ex) {
            $data = '';
        }
        return $data;
    }
    public function GetLinkPageByTittle($userId, $tittlePage)
    {
        try {
            $config = (object) config('config_url');
            $response = $this->GetContentPageByTittle($userId, $tittlePage);
            $result = $response['0'];
            // $result = $response['results'];
            $links = $result['_links'];
            // print_r($links);die;
            $data = $config->confluence_host . $links['tinyui'];
            // print_r($data);die;
        } catch (\Exception $e) {
            $data = '';
            // print_r($e->getMessage());
        }
        return $data;
    }

    public function GenerateDocumentByJobs($projectId)
    {

    }
    public function SetConfluenceTemplate()
    {
        $token = $this->request->input('token');
        $reqData = $this->request->input('reqData');


        try {
            if ($this->auth->getAuth($token)) {

                $dataInput = explode("|", $reqData);
                $templateid = $dataInput[0];
                $titlepage = $dataInput[1];
                $data = $this->UpdateConfluenceTemplate($templateid, $titlepage);
                if($data){
                    $data = [
                        'RESPONSE_CODE' => '0001',
                        'RESPONSE_DESC' => 'SUCCESS',
                        'RESPONSE_DATA' => $titlepage
                    ];
                }else{
                    $data = [
                        'RESPONSE_CODE' => '0002',
                        'RESPONSE_DESC' => 'Data Not Found',
                        'RESPONSE_DATA' => ''
                    ];
                }
            }else{
                $data = [
                    'RESPONSE_CODE' => '0002',
                    'RESPONSE_DESC' => 'Failed Authentification',
                    'RESPONSE_DATA' => ''
                ];
            }
        } catch (\Exception $e) {
            $data = [
                "RESPONSE_CODE" => "0002",
                "RESPONSE_DESCRIPTION" => "Transaction Failed",
                "RESPONSE_DATA" => $e->getMessage()
            ];
        }
        return $data;
    }
    public function SetParentConfluence()
    {
        $reqData = $this->request->input('reqData');
        $token = $this->request->input('token');
        try {
            $dataRequest = explode('|', $reqData);

            if ($this->auth->getAuth($token)) {
                $pageBody = $this->GetContentPageByTittle($dataRequest[0], $dataRequest[1]);
                $resultBody = $pageBody[0];
                $pageAncestors = $this->GetAncestorsPageByTittle($dataRequest[0], $dataRequest[1]);
                $resultAncestors = $pageAncestors[0];
                $dataAncestors = $resultAncestors['ancestors'];
                $dataAncestors_1 = $dataAncestors[count($dataAncestors) - 1];
                $pageSpace = $this->GetSpacePageByTittle($dataRequest[0], $dataRequest[1]);
                $arraySpace = $pageSpace[0];
                $resultSpace = $arraySpace['space'];
                // print_r($resultBody['id']);die;
                $dataParent = [
                    'szParentId' => $resultBody['id'],
                    'szParentName' => $resultBody['title'],
                    'szAncestorsId' => $dataAncestors_1['id'],
                    'szSpaceId' => $resultSpace['key']
                ];
                $parent = $this->confluence->GetParentById($resultBody['id']);
                // print_r($parent->szParentId);die;
                if ($parent->szParentId < 1) {
                    $data = $this->confluence->UpdateParentConfluence('ADD', $dataParent);
                } else {
                    $data = $this->confluence->UpdateParentConfluence('EDIT', $dataParent);
                }


                if ($data) {
                    $data = [
                        'RESPONSE_CODE' => '0001',
                        'RESPONSE_DESC' => 'SUCCESS'
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
        } catch (\Exception $e) {
            $data = [
                "RESPONSE_CODE" => "0002",
                "RESPONSE_DESCRIPTION" => "Transaction Failed",
                "RESPONSE_DATA" => $e->getMessage()
            ];
            // $countDoc = $this->confluence->GetDocument($projectId);
        }
        return $data;
    }
    public function UpdateConfluenceTemplate($templateid, $titlepage)
    {
        try {
            $config = (object) config('config_url');
            $pattern = array("&", "[", "]", " ", "<", ">");
            $replace = array("%26", "%5B", "%5D", "%20", "%3C", "%3E");
            $dataInput = str_replace($pattern, $replace, $titlepage);
            $inputvalidate = str_replace(array("\r", "\n"), '', $dataInput);

            $url = '/rest/api/content?expand=body.storage&expand=ancestors&expand=body.storage&title=' . $inputvalidate;
            $request = '';
            $result = $this->confluenceService->get_confluence_service($url, "OTAxNDcyODM6QW5pbmRpcmEyMTEyMjA=", $request);
            // print_r(json_encode($result));die;
            $data1 = $result['results'];
            $data = $data1[0];
            // ============== SET BODY PAGE ==============
            $bodydData = $data['body'];
            $bodyStorage = $bodydData['storage'];
            $bodyValue = str_replace('"', '\"', $bodyStorage['value']);
            // print_r($$bodyStorage['value']);die;
            // ============== SET BODY PAGE ==============

            // ============== SET LINK PAGE ==============
            $dataLink = $data['_links'];
            $links = $config->confluence_host . $dataLink['tinyui'];
            // ============== SET LINK PAGE ==============
            $date = Carbon::now();
            $dataUpdate = [
                'szTitle' => $titlepage,
                'szBodyValue' => $bodyValue,
                'dtmLastUpdated' => $date->toDateTimeString(),
                'szLinkTemplate' => $links

            ];
            $data = $this->confluence->UpdateTemplatedata($templateid, $dataUpdate);

            if($data){
                $data = [
                    'RESPONSE_CODE' => '0001',
                    'RESPONSE_DESC' => 'SUCCESS'
                ];
            }else{
                $data = [
                    'RESPONSE_CODE' => '0002',
                    'RESPONSE_DESC' => 'Data Not Found',
                    'RESPONSE_DATA' => ''
                ];
            }
        } catch (Exception $e) {
            $data = [
                "RESPONSE_CODE" => "0002",
                "RESPONSE_DESCRIPTION" => "Transaction Failed",
                "RESPONSE_DATA" => $e->getMessage()
            ];
        }
        return $data;
    }

}
