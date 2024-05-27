<?php

namespace App\Jobs\Confluence;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use DB;

class UpdateDataConfluence implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->UpdateData();
    }
    public function UpdateData()
    {
        try {
            $project = DB::connection('sqlsrv')
                ->table('INA_MD_Project')
                ->select(
                    DB::raw('
                    szProjectId,	    szUserCreatorId,	    dtmDSN,	        szDevLink,	        szBADTLink,	            szStressCaptureLink,
                    szProjectName,	    szJiraCode,	            szMIGNumber,	szSITTittle,	    szBADTCaptureTittle,	szBugTittle,
                    szApplicationId,	szDescription,	        dtmMIG,	        szSITLink,	        szBADTCaptureLink,	    szBugLink,
                    szStatusProjectId,	szJiraLink,	            szBAUATNumber,	szSITCaptureTittle,	szBAPTTittle,	        szTeamQA,
                    szLastInfo,	        szJiraKey,	            dtmBAUAT,	    szSITCaptureLink,	szBAPTLink,	            szTeamDev,
                    szTeamId,	        szUQAId,	            szAppFunction,	szUATTittle,	    szBAPTCaptureTittle,	szTeamUser,
                    dtmCreated,	        szJiraDSNkey,	        szAppModule,	szUATLink,	        szBAPTCaptureLink,	    szTeamOps,
                    dtmLastUpdated,	    szJiraTestPlanKey,	    szBRDTittle,	szUATCaptureTittle,	szStressTittle,         szDynamicLink,
                    szStatusDoc,	    szJiraTestExecutionId,	szBRDLink,	    szUATCaptureLink,	szStressLink,           szDynamicTittle,
                    szStatusMapping,	szDSNNumber,	        szDevTittle,	szBADTTittle,	    szStressCaptureTittle,  szDSNLink,
                    szDSNTittle,        szRegressionLink,       szRegressionTittle
                    ')
                )->take(2)
                ->where('szStatusMapping', '1')
                ->first();


            $dataPending = DB::connection('sqlsrv_confluence')
                ->table('INA_MD_ProjectPageItem')
                ->select(
                    DB::raw('szPageId, szProjectId')
                )->where('szProjectId', $project->szProjectId)
                ->where('szStatus', 1)
                ->get();

            foreach ($dataPending as $data) {
                $dataPage = DB::connection('sqlsrv_confluence')
                    ->table('INA_MD_ProjectPageItem')
                    ->select(
                        DB::raw('szPageid, szBody, szProjectId, szStatus')
                    )->where('szPageId', $data->szPageId)
                    ->where('szStatus', 1)
                    ->first();
                $bodyData = $dataPage->szBody;
                $replaceKey = [
                    '#JIRA_LINK#' => $project->szJiraLink,
                    '#JIRA_KEY#' => $project->szJiraCode,
                    '#UQA_KEY#' => '',
                    '#JIRA_DSN_KEY#' => '',
                    '#JIRA_TESTPLAN_KEY#' => '',
                    '#JIRA_TESTEXECUTION_KEY#' => '',
                    '#DSN_NUMBER#' => $project->szDSNNumber,
                    '#DSN_DATE#' => '',
                    '#MIG_NUMBER#' => $project->szMIGNumber,
                    '#MIG_DATE#' => '',
                    '#BAUAT_NUMBER#' => '',
                    '#BAUAT_DATE#' => '',
                    '#APPLICATION_FUNCTION#' => '',
                    '#PROJECT_DESCRIPTION#' => str_replace(array('&'), '&amp;', $project->szDescription),
                    '#PROJECT_NAME#' => str_replace(array('&'), '&amp;', $project->szProjectName),
                    '#APPLICATION_MODULE#' => '',
                    '#BRD_TITTLE#' => str_replace(array('&'), '&amp;', $project->szBRDTittle),
                    '#DEV_TITTLE#' => str_replace(array('&'), '&amp;', $project->szDevTittle),
                    '#BRD_LINK#' => $project->szBRDLink,
                    '#DEV_LINK#' => $project->szDevLink,
                    '#SIT_LINK#' => $project->szSITLink,
                    '#SIT_TITTLE#' => str_replace(array('&'), '&amp;', $project->szSITTittle),
                    '#SITCAPTURE_LINK#' => $project->szSITCaptureLink,
                    '#SITCAPTURE_TITTLE#' => str_replace(array('&'), '&amp;', $project->szSITCaptureTittle),
                    '#UAT_LINK#' => $project->szUATLink,
                    '#UAT_TITTLE#' => str_replace(array('&'), '&amp;', $project->szUATTittle),
                    '#UATCAPTURE_LINK#' => $project->szUATCaptureLink,
                    '#UATCAPTURE_TITTLE#' => str_replace(array('&'), '&amp;', $project->szUATCaptureTittle),
                    '#BADT_LINK#' => $project->szBADTLink,
                    '#BADT_TITTLE#' => str_replace(array('&'), '&amp;', $project->szBADTTittle),
                    '#BADTCAPTURE_LINK#' => $project->szBADTCaptureLink,
                    '#BADTCAPTURE_TITTLE#' => str_replace(array('&'), '&amp;', $project->szBADTCaptureTittle),
                    '#BAPT_LINK#' => $project->szBAPTLink,
                    '#BAPT_TITTLE#' => str_replace(array('&'), '&amp;', $project->szBAPTTittle),
                    '#BAPTCAPTURE_LINK#' => $project->szBAPTCaptureLink,
                    '#BAPTCAPTURE_TITTLE#' => str_replace(array('&'), '&amp;', $project->szBAPTCaptureTittle),
                    '#STRESS_LINK#' => $project->szStressLink,
                    '#STRESS_TITTLE#' => str_replace(array('&'), '&amp;', $project->szStressTittle),
                    '#STRESSCAPTURE_LINK#' => $project->szStressCaptureLink,
                    '#STRESSCAPTURE_TITTLE#' => str_replace(array('&'), '&amp;', $project->szStressCaptureTittle),
                    '#BUG_LINK#' => $project->szBugLink,
                    '#BUG_TITTLE#' => str_replace(array('&'), '&amp;', $project->szBugTittle),
                    '#TEAM_QA#' => $project->szTeamQA,
                    '#TEAM_DEV#' => $project->szTeamDev,
                    '#TEAM_USER#' => $project->szTeamUser,
                    '#TEAM_OPS#' => $project->szTeamOps,
                    '#ENVI_TYPE#' => '',
                    '#APPLICATION#' => '',
                    '#DYNAMIC_LINK#' => $project->szDynamicLink,
                    '#DYNAMIC_TITTLE#' => str_replace(array('&'), '&amp;', $project->szDynamicTittle),
                    '#DSN_LINK#' => $project->szDSNLink,
                    '#DSN_TITTLE#' => str_replace(array('&'), '&amp;', $project->szDSNTittle),
                    '#REGRESSION_LINK#' => $project->szRegressionLink,
                    '#REGRESSION_TITTLE#' => str_replace(array('&'), '&amp;', $project->szRegressionTittle)

                ];

                $appendedBody = str_replace(array_keys($replaceKey), $replaceKey, $bodyData);
                $inputUpdate = [
                    'szBody' => $appendedBody,
                    'szStatus' => 2
                ];

                $dataUpdate = DB::connection('sqlsrv_confluence')
                    ->table('INA_MD_ProjectPageItem')
                    ->where('szProjectId', $data->szProjectId)
                    ->where('szPageId', $data->szPageId)
                    ->update($inputUpdate);


                $statusMapping = DB::connection('sqlsrv_confluence')
                    ->table('INA_MD_ProjectPageItem')
                    ->select(DB::raw("COUNT(szPageId) as szPageId"))
                    ->where('szStatus', 1)
                    ->where('szProjectId', $project->szProjectId)
                    ->first();
                if ($statusMapping->szPageId < 1) {
                    $dataMapping = [
                        'szStatusMapping' => 2
                    ];
                    $dataMapping = DB::connection('sqlsrv')
                        ->table('INA_MD_Project')
                        ->where('szProjectId', $project->szProjectId)
                        ->update($dataMapping);
                }
            }
        } catch (\Exception $e) {

        }
    }
}
