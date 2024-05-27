<?php

namespace App\Http\Controllers\Management\Project;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Authentification\CheckAuth;
use App\Http\Controllers\Atlasian\Confluence\ConfluenceController;

use App\Models\Management\Config\ConfigModel;
use App\Models\Management\Project\ProjectModel;
use App\Models\Atlasian\Confluence\ConfluenceModel;
use App\Models\Management\Application\ApplicationModel;

use App\Jobs\BackendJobs;

use Illuminate\Http\Request;
use Validator, DB, DateTime, Session, Carbon\Carbon;

class ProjectController extends Controller
{
    public function __construct(Request $request, ProjectModel $project, CheckAuth $auth, ConfigModel $config, ConfluenceModel $confluence, ConfluenceController $confluenceController, ApplicationModel $application)
    {
        $this->project = $project;
        $this->config = $config;
        $this->confluence = $confluence;
        $this->application = $application;
        $this->confluenceController = $confluenceController;

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
    public function GetMainDashboard()
    {
        $token = $this->request->input('token');
        $reqData = $this->request->input('reqData');
        $response = $this->project->GetMainDashboard();
        BackendJobs::dispatch($response)->onQueue('allproject');
        $data = $this->ControllerResponse($token, $response);
        return response()->json($data);
    }
    public function GetActiveProject()
    {
        $token = $this->request->input('token');
        $reqData = $this->request->input('reqData');
        $response = $this->project->GetActiveProject($reqData);

        $data = $this->ControllerResponse($token, $response);
        return response()->json($data);
    }
    public function GetDetailProject()
    {
        $token = $this->request->input('token');
        $reqData = $this->request->input('reqData');
        $response = $this->project->GetDetailProject($reqData);

        $data = $this->ControllerResponse($token, $response);
        return response()->json($data);
    }

    public function GetRootPage()
    {
        $token = $this->request->input('token');
        $reqData = $this->request->input('reqData');
        $response = $this->project->GetRootPage();

        $data = $this->ControllerResponse($token, $response);
        return response()->json($data);
    }

    public function GetStatusProject()
    {
        $token = $this->request->input('token');
        $reqData = $this->request->input('reqData');

        $data = $this->ControllerResponse($token, $this->project->GetStatusProject());
        return response()->json($data);
    }
    public function GetParentPage()
    {
        $token = $this->request->input('token');
        $reqData = $this->request->input('reqData');
        $response = $this->project->GetParentPage($reqData);

        $data = $this->ControllerResponse($token, $response);
        return response()->json($data);
    }
    public function PostProject()
    {
        $status = '';
        $configpage = $this->config->GetConfigValue('Counter', 'Project') + 1;
        $configprefix = $this->config->GetConfigValue('Prefix', 'Default');

        $projectid = $configprefix . "-" . str_pad($configpage, 4, 0, STR_PAD_LEFT);
        $token = $this->request->input('token');
        if ($this->auth->getAuth($token)) {
            $szTittle = $this->request->input('szTittle');
            $szJiraLink = $this->request->input('szJiraLink');
            $szDescription = $this->request->input('szDescription');
            $szAncestors = $this->request->input('szAncestors');
            $szStatus = $this->request->input('szStatus');
            $szTeamId = $this->request->input('szTeamId');
            $szApplicationId = $this->request->input('application');
            $szBRDTittle = $this->request->input('szBRDTittle');
            $szDevTittle = $this->request->input('szDevTittle');
            $szCreator = $this->request->input('szCreator');
            $jiraKey = $this->request->input('szJiraKey');

            $action = $this->request->input('action');
            $date = Carbon::now();

            $szSpaceKey = $this->project->GetParentConfluence($szAncestors);
            $brdLink = $this->confluenceController->GetLinkPageByTittle($szCreator, $szBRDTittle);
            $devLink = $this->confluenceController->GetLinkPageByTittle($szCreator, $szDevTittle);
            // print_r($szSpaceKey->szSpaceId);die;
            // print_r($devLink);die;

            // CAse BRD LINK and DEV Link Not Found
            //


            $appDetail = $this->application->GetDetailApplication($szApplicationId);
            $dataProject = [
                'szProjectId' => $projectid,
                'szProjectName' => $szTittle,
                'szApplicationId' => $szApplicationId,
                'szStatusProjectId' => $szStatus,
                'szTeamId' => $szTeamId,
                'dtmCreated' => $date->toDateTimeString(),
                'dtmLastUpdated' => $date->toDateTimeString(),
                'szUserCreatorId' => $szCreator,
                'szDescription' => $szDescription,
                'szJiraLink' => $szJiraLink,
                'szBRDTittle' => $szBRDTittle,
                'szBRDLink' => $brdLink,
                'szDevTittle' => $szDevTittle,
                'szDevLink' => $devLink,
                'szJiraCode' => $jiraKey,
                'szTeamQA'      => $appDetail->szQATeam,
                'szTeamDev'     => $appDetail->szDevTeam,
                'szTeamUser'    => $appDetail->szUserTeam,
                'szTeamOps'     => $appDetail->szOpsTeam
            ];



            // print_r($dataProject);
            // print_r($szSpaceKey->szSpaceId);
            // die;
            if (strtoupper($action) == 'ADD') {
                // ----------------------Bypass insert Data project--------------------------
                $project = $this->project->InsertProject($dataProject);
                // $project = true;
                // ---------------------- || ---------------------------------------
                // var_dump($project);die;
                if ($project) {
                    $status = "| 1 | Sukses";

                    $templatepageitem = json_decode(json_encode($this->project->GetTemplatePageitem()), true);
                    // print_r($templatepageitem);die;

                    // -------------------------- Bypass Insert Data Detail Project -------------------------------
                    foreach ($templatepageitem as $row) {
                        $dataProjectItem = [
                            'szProjectId' => $projectid,
                            'shItemNumber' => $row['shItemNumber'],
                            'szitem' => $row['szConfigValue'],
                            'dtmLastUpdated' => '',
                            'szStatus' => '0',
                            'szTemplateId' => $row['szConfigModule']
                        ];
                        $pageitem = $this->project->InsertProjectItem($dataProjectItem);
                        // $pageitem = true;
                    }
                    // -----------------------------------||----------------------------------------


                    // var_dump($pageitem);die;
                    if ($pageitem) {

                        $status = $status . "\n| 2 | Sukses";

                        // ------------------------Bypass Insert Data Page----------------------------
                        $date = Carbon::now();
                        $template = $this->project->GetTemplateProjectPage();
                        $projectName = '';
                        foreach ($template as $tmp) {
                            $szParentConfluenceId = '';
                            if ($tmp->szCategory == 'RootPage') {
                                $szParentConfluenceId = $szAncestors;
                                $tittlePage = $szTittle;
                                $projectName = $tittlePage;
                            } else if($tmp->szCategory == 'BugPage'){
                                $tittlePage = "03. Buglist ".$jiraKey." - ".$projectName;
                            }else {
                                $tittlePage = str_replace('<JiraCode>', $jiraKey, $tmp->szTitle);
                            }


                            $dataProjectPage = [
                                'szProjectId' => $projectid,
                                'szTemplateId' => $tmp->szTemplateId,
                                'szTittleProject' => $tittlePage,
                                'szCategory' => $tmp->szCategory,
                                'szRootPage' => $tmp->szRootPage,
                                'szParentConfluenceId' => $szParentConfluenceId,
                                'szSpaceKey' => $szSpaceKey->szSpaceId
                            ];
                            // print_r($dataProjectPage);die;
                            $body = $this->project->GetTemplatePageBody($tmp->szTemplateId);
                            $pageitem = [
                                'szBody' => $body,
                                'dtmCreated' => $date->toDateTimeString(),
                                'szProjectId' => $projectid,
                                'szVersion' => 1
                            ];

                            $projectPage = $this->project->InsertProjectPage($dataProjectPage, $pageitem);
                        }
                        $config = $this->config->UpdateConfigValue('Counter', 'Project', $configpage);

                        // print_r($config);die;
                        // --------------------- || --------------------------------------------
                        if ($projectPage) {

                            $status = $status . "\n| 3 | Sukses";
                            $data = [
                                'RESPONSE_CODE' => '0001',
                                'RESPONSE_DESC' => 'SUCCESS',
                                'RESPONSE_DATA' => $projectid
                            ];
                        } else {
                            $status = $status . "\n| 3 | Gagal";
                            $project = $this->project->RemoveProject($projectid);
                            $projectitem = $this->project->RemoveProjectItem($projectid);
                        }
                    } else {
                        // Deelte project
                        $project = $this->project->RemoveProject($projectid);
                        $status = $status . "\n| 2 | Gagal";
                    }
                } else {
                    $status = "| 1 | Gagal";
                }


            } else {

                $szProjectid = $this->request->input('szProjectid');
            }
        } else {
            $data = [
                'RESPONSE_CODE' => '0002',
                'RESPONSE_DESC' => 'FAILED',
                'RESPONSE_DATA' => $status
            ];
        }
        return response()->json($data);
    }

    public function GetProjectReady(){
        $response = $this->project->GetProjectReady();
        $config = (object) config('config_url');
        $token = $config->token;
        // print_r($token);die;
        $data = $this->ControllerResponse($token, $response);
        return response()->json($data);
    }
    public function GetTotalAssigneed(){
        $token = $this->request->input('token');
        $reqData = $this->request->input('reqData');
        $response = $this->project->GetTotalAssigneed($reqData);

        $data = $this->ControllerResponse($token, $response);
        return response()->json($data);
    }
}
