<?php

namespace App\Jobs\Confluence;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

use DB;
use Carbon\Carbon;

class ConfluenceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $this->ProcessData();
    }
    public function UpdateProject($projectData, $projectId)
    {
        try {
            $data = DB::connection('sqlsrv')
                ->table('INA_MD_Project')
                ->where('szProjectId', $projectId)
                ->update($projectData);
        } catch (\Exception $e) {
            $data = '';
        }
        return $data;
    }
    public function ProcessData()
    {
        try {
            //-----------------------------Inquiry Data  -----------------------------
            $dataPending = DB::connection('sqlsrv')
                ->table('INA_MD_Project AS a')
                ->select(
                    DB::raw("a.szProjectId")
                )
                ->where('a.szStatusMapping', 0)
                ->take(2)
                ->orderBy('szProjectId', 'asc')
                ->first();

            $projectId = $dataPending->szProjectId;
            // print_r($dataPending->szProjectId);die;
            $dataPending = DB::connection('sqlsrv_confluence')
                ->table('INA_MD_ProjectPageItem')
                ->select(
                    DB::raw('szPageId')
                )->where('szStatus', 0)
                ->where('szProjectId', $projectId)
                ->get();
            // print_r($detailuser);die;
            foreach ($dataPending as $key => $value) {
                $dataConfluence = DB::connection('sqlsrv_confluence')
                    ->table('INA_MD_ProjectPageItem AS a')
                    ->join('INA_MD_ProjectPage AS b', 'a.szPageid', 'b.szPageId')
                    ->select(
                        DB::raw(
                            "a.szPageId, a.szBody, a.szProjectId, a.szStatus, b.szTemplateId, b.szTittleProject, b.szCategory, b.szSpaceKey, b.szParentConfluenceId"
                        )
                    )
                    ->where('a.szPageId', $value->szPageId)
                    ->orderBy('a.szPageId', 'asc')
                    ->first();

                $projectDetail = DB::connection('sqlsrv')
                    ->table('INA_MD_Project AS a')
                    ->join('INA_SD_ConfigItem AS b', 'a.szStatusProjectId', 'b.shitemNumber')
                    ->select(
                        DB::raw("
                            a.szProjectId,          a.szProjectName,        a.szApplicationId,      a.szStatusProjectId,        a.szLastInfo,
                            a.szTeamId,             a.dtmCreated,           a.dtmLastUpdated,       a.szStatusDoc,              a.szStatusMapping,
                            a.szUserCreatorId,      a.szJiraCode,           a.szDescription,        a.szJiraLink,               a.szJiraKey,
                            a.szUQAId,              a.szJiraDSNkey,         a.szJiraTestPlanKey,    a.szJiraTestExecutionId,    a.szDSNNumber,
                            a.dtmDSN,               a.szMIGNumber,          a.dtmMIG,               a.szBAUATNumber,            a.dtmBAUAT,
                            a.szAppFunction,        a.szAppModule,          a.szBRDTittle,          a.szBRDLink,                a.szDevTittle,
                            a.szDevLink,            a.szSITTittle,          a.szSITLink,            a.szSITCaptureTittle,       a.szSITCaptureLink,
                            a.szUATTittle,          a.szUATLink,            a.szUATCaptureTittle,   a.szUATCaptureLink,         a.szBADTTittle,
                            a.szBADTLink,           a.szBADTCaptureTittle,  a.szBADTCaptureLink,    a.szBAPTTittle,             a.szBAPTLink,
                            a.szBAPTCaptureTittle,  a.szBAPTCaptureLink,    a.szStressTittle,       a.szStressLink,             a.szStressCaptureTittle,
                            a.szstressCaptureLink,  a.szBugTittle,          a.szBugLink,            a.szTeamQA,                 a.szTeamDev,
                            a.szTeamUser,           a.szTeamOps,
                            b.szConfigValue")
                    )
                    ->where('a.szProjectId', $projectId)
                    ->where('b.szItem', 'Project_Status')
                    ->first();

                // print_r($dataConfluence->szCategory );die;


                $detailuser = DB::table('INA_MD_User AS a')
                    ->join('INA_MD_TeamMember AS c', 'a.szTeamMemberId', 'c.szMemberId')
                    ->join('INA_MD_Team AS d', 'c.szTeamId', 'd.szTeamId')
                    ->join('INA_MD_TeamRole AS e', 'c.szTeamRole', 'e.szRoleId')
                    ->select(
                        DB::raw('c.szMemberName, a.szUserLogin, a.szUserId,  a.szTeamMemberId, a.szIsLogin, a.szStatus,
                            c.*,
                            d.szTeamName,
                            e.szRoleName')
                    )
                    ->where('a.szUserLogin', $projectDetail->szUserCreatorId)
                    ->first();
                // print_r($value->szCategory);die;
                if ($dataConfluence->szCategory == "RootPage") {
                    $projectName = Carbon::now()->format('Ymd').' - '. $projectDetail->szJiraCode.' - '.$projectDetail->szProjectName;
                } else {
                    $projectName = $dataConfluence->szTittleProject;
                }
                // Hit Service ------
                $token = $detailuser->szAtlasianToken;
                $url = "/rest/api/content";
                $space = $dataConfluence->szSpaceKey;
                $title = $projectName;
                $ancestors = $dataConfluence->szParentConfluenceId;
                $body = str_replace(array("\n", "\r"), '', $dataConfluence->szBody);

                $request = "{\n    \"space\": {\n        \"key\": \"$space\"\n    },\n    \"type\": \"page\",\n    \"title\": \"$title\",\n    \"ancestors\": [\n        {\n            \"id\": $ancestors\n        }\n    ],\n    \"body\": {\n        \"storage\": {\n            \"value\": \"$body\",\n            \"representation\": \"storage\"\n        }\n    }\n}";
                // print_r($request);die;
                $config = (object) config('config_url');
                $address = $config->confluence_host . $url;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $address);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Accept: application/json',
                    'Content-Type: application/json',
                    'Authorization: Basic ' . $token,
                ]);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

                $response = curl_exec($ch);
                $data = json_decode($response, true);
                curl_close($ch);
                // Close Hit ----
// $data = $this->confluenceService->post_confluence_service($url, $token, $request);
// print_r($data);die;
// print_r($log);die;

                // Insert LOG
                $dataInsert = [
                    'szRequest' => json_encode($request),
                    'szResponse' => json_encode($data),
                    'dtmHit' => Carbon::now()->toDateTimeString(),
                    'szUrl' => $url,
                    'szpageId' => $value->szPageId
                ];
                $dataLog = DB::connection('sqlsrv_confluence')
                    ->table('INA_SD_LogService')
                    ->insert($dataInsert);

                // Insert LOG

                // Prepare Mapping Data
                $links = $data['_links'];
                $config = (object) config('config_url');
                $base_url = $config->confluence_host;
                $shortLink = $base_url . $links['tinyui'];
                // End Prepare

                $dataProjectPage = [
                    "szShortLink" => $shortLink,
                    "szPageConfluenceId" => $data['id']
                ];
                $dataParentProject = [
                    'szParentConfluenceId' => $data['id']
                ];
                $pageId = $dataConfluence->szPageId;
                $data = DB::connection('sqlsrv_confluence')
                    ->table('INA_MD_ProjectPage')
                    ->where('szPageId', $pageId)
                    ->update($dataProjectPage);
                $data = DB::connection('sqlsrv_confluence')
                    ->table('INA_MD_ProjectPage')
                    ->where('szRootPage', $dataConfluence->szTemplateId)
                    ->where('szProjectId', $projectId)
                    ->update($dataParentProject);
                $data = DB::connection('sqlsrv_confluence')
                    ->table('INA_MD_ProjectPageItem')
                    ->where('szPageId', $pageId)
                    ->update(array('szStatus' => '1'));

                if ($response) {
                    if ($dataConfluence->szTemplateId == 'INATMP-0001' || $dataConfluence->szTemplateId == 'INATMP-0014' || $dataConfluence->szTemplateId == 'INATMP-0019' || $dataConfluence->szTemplateId == 'INATMP-0017') {

                        $data = DB::connection('sqlsrv')
                            ->table('INA_MD_ProjectItem')
                            ->where('szProjectId', $projectId)
                            ->where('szTemplateId', $dataConfluence->szTemplateId)
                            ->update(['szLink' => $shortLink]);
                    }
                }

                // $log = $this->models_confluence->InsertLog($request, $data, $url);

                if ($dataConfluence->szTemplateId == 'INATMP-0005') {
                    $docData = [
                        'szBUGLink' => $shortLink,
                        'szBUGTittle' => $projectName
                    ];
                    $data = $this->UpdateProject($docData, $projectId);
                }
                // -----------BUGLIST------------

                // -----------SIT------------
                else if ($dataConfluence->szTemplateId == 'INATMP-0006') {
                    $docData = [
                        'szSITLink' => $shortLink,
                        'szSITTittle' => $projectName
                    ];
                    $data = $this->UpdateProject($docData, $projectId);
                } else if ($dataConfluence->szTemplateId == 'INATMP-0012') {
                    $docData = [
                        'szSITCaptureLink' => $shortLink,
                        'szSITCaptureTittle' => $projectName,
                        'szUATCaptureLink' => $shortLink,
                        'szUATCaptureTittle' => $projectName
                    ];
                    $data = $this->UpdateProject($docData, $projectId);
                }
                // -----------SIT------------

                // -----------BAUAT------------
                else if ($dataConfluence->szTemplateId == 'INATMP-0007') {
                    $docData = [
                        'szUATLink' => $shortLink,
                        'szUATTittle' => $projectName
                    ];
                    $data = $this->UpdateProject($docData, $projectId);
                }
                // -----------BAUAT------------

                // -----------BADT------------
                else if ($dataConfluence->szTemplateId == 'INATMP-0009') {
                    $docData = [
                        'szBADTLink' => $shortLink,
                        'szBADTTittle' => $projectName
                    ];
                    $data = $this->UpdateProject($docData, $projectId);
                }
                else if ($dataConfluence->szTemplateId == 'INATMP-0020') {
                    $docData = [
                        'szBADTCaptureLink' => $shortLink,
                        'szBADTCcaptureTittle' => $projectName
                    ];
                    $data = $this->UpdateProject($docData, $projectId);
                }
                // -----------BADT------------

                // -----------BAPT------------
                else if ($dataConfluence->szTemplateId == 'INATMP-0010') {
                    $docData = [
                        'szBAPTLink' => $shortLink,
                        'szBAPTTittle' => $projectName
                    ];
                    $data = $this->UpdateProject($docData, $projectId);
                } else if ($dataConfluence->szTemplateId == 'INATMP-0022') {
                    $docData = [
                        'szBAPTCaptureLink' => $shortLink,
                        'szBAPTCaptureTittle' => $projectName
                    ];
                    $data = $this->UpdateProject($docData, $projectId);
                }
                // -----------BAPT------------

                // -----------Stress------------
                else if ($dataConfluence->szTemplateId == 'INATMP-0008') {
                    $docData = [
                        'szStressLink' => $shortLink,
                        'szStressTittle' => $projectName
                    ];
                    $data = $this->UpdateProject($docData, $projectId);
                }else if($dataConfluence->szTemplateId == 'INATMP-0018'){
                    $docData = [
                        'szStressCaptureLink'=> $shortLink,
                        'szStressCaptureTittle'=> $projectName
                    ];
                    $data = $this->UpdateProject($docData, $projectId);
                }
                // ----------------Dynamic--------------------------
                else if($dataConfluence->szTemplateId == 'INATMP-0003'){
                    $docData = [
                        'szDynamicLink'=> $shortLink,
                        'szDynamicTittle'=> $projectName
                    ];
                    $data = $this->UpdateProject($docData, $projectId);
                }
                // -------------------Dynamic -----------------------

                // ------------------- Regression -----------------------
                else if($dataConfluence->szTemplateId == 'INATMP-0011'){
                    $docData = [
                        'szRegressionLink'=> $shortLink,
                        'szRegressionTittle'=> $projectName
                    ];
                    $data = $this->UpdateProject($docData, $projectId);
                }
                // -------------------Dynamic -----------------------
                $dataItem = DB::connection('sqlsrv_confluence')->table('INA_MD_ProjectPageItem')
                    ->select(
                        DB::raw("COUNT(szPageId) AS DataCount")
                    )->where('szStatus', 0)
                    ->where('szProjectId', $projectId)
                    ->first();
                if ($dataItem->DataCount < 1) {
                    $data = DB::connection('sqlsrv')->table('INA_MD_Project')
                        ->where('szProjectId', $projectId)
                        ->update(['szStatusMapping' => 1]);
                }
            }
        } catch (\Exception $e) {
        }
    }

}
