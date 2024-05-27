<?php

namespace App\Http\Controllers\Atlasian\Jira;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Authentification\CheckAuth;

use App\Models\Atlasian\Jira\JiraModel;
use Exception;

class JiraController extends Controller
{
    public function __construct(Request $request, CheckAuth $auth, JiraModel $jira)
    {
        $this->jira = $jira;

        $this->request = $request;
        $this->auth = $auth;
    }
    public function GetAssigneeUser()
    {
        $token = $this->request->input('token');
        $reqData = $this->request->input('reqData');
        if ($this->auth->getAuth($token)) {
            $data = $this->jira->GetAssigneeUser($reqData);
            if (count($data) > 0) {
                $data = [
                    'RESPONSE_CODE' => '0001',
                    'RESPONSE_DESC' => 'SUCCESS',
                    'RESPONSE_DATA' => $data
                ];
            } else {
                $data = [
                    'RESPONSE_CODE' => '0002',
                    'RESPONSE_DESC' => 'FAILED',
                    'RESPONSE_DATA' => ''
                ];
            }
        } else {
            $data = [
                'RESPONSE_CODE' => '0002',
                'RESPONSE_DESC' => 'FAILED',
                'RESPONSE_DATA' => 'NOT AUTHORIZE'
            ];
        }
        return $data;
    }
    public function SetAssignee()
    {
        $token = $this->request->input('token');
        $uqaId = $this->request->input('uqaid');
        $uqakey = $this->request->input('uqakey');
        $jiracode = $this->request->input('jiracode');
        $reporter = $this->request->input('reporter');
        $assignee = $this->request->input('assignee');
        $summary = $this->request->input('summary');
        $description = $this->request->input('description');
        $status = $this->request->input('status');
        $startDate = $this->request->input('startDate');
        $finishDate = $this->request->input('finishDate');
        $projectId = $this->request->input('projectId');
        $action = $uqaId == '' ? 'ADD' : 'EDIT';

        $dataAssingee = [
            'szUQAKey' => $uqakey,
            'szJiraCode' => $jiracode,
            'szReporterId' => $reporter,
            'szAssigneeId' => $assignee,
            'szUQASummary' => $summary,
            'szUQADescription' => $description,
            'szJiraStatus' => $status,
            'dtmStartDate' => $startDate,
            'dtmFinishDate' => $finishDate,
            'szProjectId' => $projectId
        ];
        if ($this->auth->getAuth($token)) {
            $data = $this->jira->CheckUQA($uqaId, $assignee, $jiracode);
            if ($action == 'ADD') {

                $data = $this->jira->InsertAssignee($dataAssingee);
                if ($data) {
                    $data = [
                        'RESPONSE_CODE' => '0001',
                        'RESPONSE_DESC' => 'SUCCESS',
                        'RESPONSE_DATA' => $data
                    ];
                } else {
                    $data = [
                        'RESPONSE_CODE' => '0002',
                        'RESPONSE_DESC' => 'FAILED',
                        'RESPONSE_DATA' => ''
                    ];
                }
            } else if($action == 'EDIT'){
                $data = $this->jira->UpdateAssignee($uqakey, $dataAssingee);
                if ($data) {
                    $data = [
                        'RESPONSE_CODE' => '0001',
                        'RESPONSE_DESC' => 'SUCCESS',
                        'RESPONSE_DATA' => $data
                    ];
                } else {
                    $data = [
                        'RESPONSE_CODE' => '0002',
                        'RESPONSE_DESC' => 'FAILED',
                        'RESPONSE_DATA' => ''
                    ];
                }
            }
        } else {
            $data = [
                'RESPONSE_CODE' => '0002',
                'RESPONSE_DESC' => 'FAILED',
                'RESPONSE_DATA' => 'NOT AUTHORIZE'
            ];
        }
        return $data;
    }
}
