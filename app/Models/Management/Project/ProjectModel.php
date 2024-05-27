<?php

namespace App\Models\Management\Project;

use Illuminate\Http\Request;

use Validator, DB, Redirect, Hash, Auth, Session;
use App\Models\Model;

class ProjectModel
{
    private $needDump = false;
    public function GetDumpData($data)
    {
        try {
            print_r($data);
            die;
        } catch (\Exception $e) {
            $data = '';
        }
    }
    public function setDumpData($isNeedDump)
    {
        $this->needDump = $isNeedDump;
    }
    public function GetMainDashboard()
    {
        DB::enableQueryLog();
        try {
            $data = DB::table('INA_INV_Application AS a')
                ->join('INA_MD_Project AS b', 'a.szApplicationId', 'b.szApplicationId')
                ->select(
                    DB::raw("DISTINCT a.szApplicationName AS AppName"),
                    DB::raw("COUNT (b.szApplicationId) AS TotalProject"),
                    DB::raw("COUNT (CASE WHEN b.szStatusProjectId IN (2,3,4,5,6,7,8,9,10) THEN 1 END) AS DoneProject"),
                    DB::raw("COUNT (CASE WHEN b.szStatusProjectId IN (0,1) THEN 1 END) AS ProgressProject"),
                    DB::raw("COUNT (CASE WHEN b.szStatusProjectId IN (11) THEN 1 END) AS CanceledProject")
                )
                // ->where('b.szGroupId', 'APPINA-0002')
                ->groupby('a.szApplicationName', 'a.szApplicationId')->get();
            // var_dump(DB::getQueryLog());die;
            if ($this->needDump) {
                print_r(DB::getQueryLog());
                $this->GetDumpData($data);
            }
        } catch (\Exception $e) {
            $data = '';
            if ($this->needDump) {
                print_r(DB::getQueryLog());
                $this->GetDumpData($e);
            }
        }
        return $data;
    }

    public function GetActiveProject($team)
    {
        DB::enableQueryLog();
        try {
            $filter = $team;
            if ($team == 'APPINA-0000') {
                $filter = [
                    'APPINA-0000',
                    'APPINA-0001',
                    'APPINA-0002',
                    'APPINA-0003',
                    'APPINA-0004',
                    'APPINA-0005',
                    'APPINA-0006',
                    'APPINA-0007',
                    'APPINA-0008',
                    'APPINA-0009',
                    'APPINA-0010',
                    'APPINA-0011',
                    'APPINA-0012',
                    'APPINA-0013',
                    'APPINA-0014'
                ];
            } else {
                $filter = [$team];
            }

            $data = DB::table('INA_MD_Project AS a')
                ->join('INA_MD_ProjectItem AS b', 'a.szProjectId', 'b.szProjectId')
                ->join('INA_INV_Application as c', 'a.szApplicationId', 'c.szApplicationId')
                ->selectRaw('DISTINCT c.szApplicationName AS AppName, a.szProjectName AS ProjectName, a.szStatusDoc AS DocStatus, a.szProjectId AS ProjectId,
                            COUNT(CASE WHEN b.szStatus NOT IN (2) THEN 1 END) AS ProgressTask,
                            COUNT(CASE WHEN b.szStatus IN (2) THEN 1 END) AS DoneTask, a.dtmCreated,
                            a.szJiraCode')
                ->whereIn('a.szTeamId', $filter)
                ->groupby('a.szProjectName', 'c.szApplicationName', 'a.szStatusDoc', 'a.szProjectId', 'a.dtmCreated', 'a.szJiraCode')
                ->orderBy('a.dtmCreated', 'DESC')
                // ->orderByDesc('a.dtmCreated')
                ->get();
            if ($this->needDump) {
                print_r(DB::getQueryLog());
                $this->GetDumpData($filter);
            }
        } catch (\Exception $e) {
            $data = $e->getMessage();
            if ($this->needDump) {
                print_r(DB::getQueryLog());
                $this->GetDumpData($e);
            }
        }
        return $data;
    }
    public function GetTotalAssigneed($team){
        try {
            $filter = $team;
            if ($team == 'APPINA-0000') {
                $filter = [
                    'APPINA-0000',
                    'APPINA-0001',
                    'APPINA-0002',
                    'APPINA-0003',
                    'APPINA-0004',
                    'APPINA-0005',
                    'APPINA-0006',
                    'APPINA-0007',
                    'APPINA-0008',
                    'APPINA-0009',
                    'APPINA-0010',
                    'APPINA-0011',
                    'APPINA-0012',
                    'APPINA-0013',
                    'APPINA-0014'
                ];
            } else {
                $filter = [$team];
            }

            $data = DB::table('INA_MD_UQA AS a')
                ->join('INA_MD_Project AS b', 'a.szProjectId', 'b.szProjectId')
                ->select(
                    DB::raw('COUNT(a.szAssigneeId) AS szAssignee, a.szProjectId')
                )
                ->whereIn('b.szTeamId', $filter)
                ->groupBy('a.szProjectId')
                // ->orderByDesc('a.dtmCreated')
                ->get();
            if ($this->needDump) {
                print_r(DB::getQueryLog());
                $this->GetDumpData($filter);
            }
        } catch (\Exception $e) {
            $data = $e->getMessage();
            if ($this->needDump) {
                print_r(DB::getQueryLog());
                $this->GetDumpData($e);
            }
        }
        return $data;
    }
    public function GetDetailProject($projectId)
    {
        DB::enableQueryLog();
        try {
            $data = DB::connection('sqlsrv')
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
            if ($this->needDump) {
                print_r(DB::getQueryLog());
                $this->GetDumpData($data);
            }
        } catch (\Exception $e) {
            $data = '';
            if ($this->needDump) {
                print_r(DB::getQueryLog());
                $this->GetDumpData($e);
            }
        }
        return $data;
    }
    public function GetRootPage()
    {
        DB::enableQueryLog();
        try {
            $data = DB::connection('sqlsrv_confluence')
                ->table('INA_SD_ParentConfluence')
                ->select(
                    DB::raw("szParentId, szParentName, szAncestorsId, szSpaceId")
                )
                ->get();
            if ($this->needDump) {
                print_r(DB::getQueryLog());
                $this->GetDumpData($data);
            }
        } catch (\Exception $e) {
            $data = '';
            if ($this->needDump) {
                print_r(DB::getQueryLog());
                $this->GetDumpData($e);
            }
        }
        return $data;
    }
    public function GetStatusProject()
    {
        DB::enableQueryLog();
        try {
            $data = DB::connection('sqlsrv')
                ->table('INA_SD_ConfigItem AS a')
                ->select(
                    DB::raw("a.shItemNumber, a.szConfigValue")
                )
                ->where('a.szItem', 'Project_Status')
                ->get();
            if ($this->needDump) {
                print_r(DB::getQueryLog());
                $this->GetDumpData($data);
            }
        } catch (\Exception $e) {
            $data = '';
            if ($this->needDump) {
                print_r(DB::getQueryLog());
                $this->GetDumpData($e);
            }
        }
        return $data;
    }
    public function GetParentPage($projectId)
    {
        DB::enableQueryLog();
        try {
            $data = DB::connection('sqlsrv_confluence')
                ->table('INA_MD_ProjectPage AS a')
                ->select(
                    DB::raw("a.szParentConfluenceId")
                )->where('a.szprojectId', $projectId)
                ->where('a.szCategory', 'RootPage')
                ->first();
            if ($this->needDump) {
                print_r(DB::getQueryLog());
                $this->GetDumpData($data);
            }
        } catch (\Exception $e) {
            $data = '';
            if ($this->needDump) {
                print_r(DB::getQueryLog());
                $this->GetDumpData($e);
            }
        }
        return $data;
    }
    public function GetParentConfluence($pageId)
    {
        DB::enableQueryLog();
        try {
            $data = DB::connection('sqlsrv_confluence')
                ->table('INA_SD_ParentConfluence')
                ->select(
                    DB::raw("szParentId, szParentname, szAncestorsId, szSpaceId")
                )
                ->where('szParentId', $pageId)
                ->first();
            if ($this->needDump) {
                print_r(DB::getQueryLog());
                $this->GetDumpData($data);
            }
        } catch (\Exception $e) {
            $data = '';
            if ($this->needDump) {
                print_r(DB::getQueryLog());
                $this->GetDumpData($e->getMessage());
            }
        }
        return $data;
    }
    public function InsertProject($projectPage)
    {
        DB::beginTransaction();
        DB::enableQueryLog();
        // var_dump($projectPage);die;
        try {
            $data = DB::connection('sqlsrv')
                ->table('INA_MD_Project')
                ->insert($projectPage);
            if ($this->needDump) {
                print_r(DB::getQueryLog());
                $this->GetDumpData($data);
            }
            if ($data) {
                DB::commit();
            } else {
                DB::rollback();
            }
        } catch (\Exception $e) {
            DB::rollback();
            $data = '';
            var_dump($e->getMessage());die;
            if ($this->needDump) {
                print_r(DB::getQueryLog());
                $this->GetDumpData($e->getMessage());
            }
            // var_dump($ex);die;
        }
        // var_dump(DB::getQueryLog());die;
        return $data;
    }
    public function GetTemplatePageitem()
    {
        DB::enableQueryLog();
        try {
            $data = DB::connection('sqlsrv')
                ->table('INA_SD_ConfigItem')
                ->select(
                    DB::raw("shItemNumber, szConfigValue, szConfigModule")
                )
                ->where('szItem', 'PageItem')
                ->get();
            if ($this->needDump) {
                print_r(DB::getQueryLog());
                $this->GetDumpData($data);
            }
        } catch (\Exception $e) {
            $data = '';
            if ($this->needDump) {
                print_r(DB::getQueryLog());
                $this->GetDumpData($e);
            }
        }
        // var_dump(DB::getQueryLog());die;
        return $data;
    }
    public function InsertProjectItem($dataProjectItem)
    {
        DB::enableQueryLog();
        DB::beginTransaction();
        try {

            // var_dump($dataProjectItem);die;
            $data = DB::connection('sqlsrv')
                ->table('INA_MD_ProjectItem')
                ->insert($dataProjectItem);
            if ($this->needDump) {
                print_r(DB::getQueryLog());
                $this->GetDumpData($data);
            }
            if ($data) {
                DB::commit();
            } else {
                DB::rollback();
            }
        } catch (\Exception $e) {
            DB::rollback();
            if ($this->needDump) {
                print_r(DB::getQueryLog());
                $this->GetDumpData($e);
            }
        }
        return $data;
    }
    public function InsertProjectPage($dataProjectPage, $dataPageItem)
    {
        DB::enableQueryLog();
        DB::beginTransaction();
        try {
            $data = DB::connection('sqlsrv_confluence')
                ->table('INA_MD_ProjectPage')
                ->insert($dataProjectPage);
            if ($data) {
                $data = DB::connection('sqlsrv_confluence')
                    ->table('INA_MD_ProjectPageItem')
                    ->insert($dataPageItem);
            }

            if ($data) {
                DB::commit();
            } else {
                DB::rollback();
            }
            if ($this->needDump) {
                print_r(DB::getQueryLog());
                $this->GetDumpData($data);
            }
        } catch (\Exception $e) {
            DB::rollback();
            if ($this->needDump) {
                print_r(DB::getQueryLog());
                $this->GetDumpData($e);
            }
        }
        return $data;
    }
    public function RemoveProject($projectId)
    {
        DB::enableQueryLog();
        DB::beginTransaction();
        try {
            $data = DB::connection('sqlsrv')
                ->table('INA_MD_Project AS a')
                ->where('a.szProjectId', $projectId)
                ->delete();
            if ($this->needDump) {
                print_r(DB::getQueryLog());
                $this->GetDumpData($data);
            }
        } catch (\Exception $e) {
            DB::rollback();
            if ($this->needDump) {
                print_r(DB::getQueryLog());
                $this->GetDumpData($e);
            }
        }
        return $data;
    }
    public function RemoveProjectItem($projectId)
    {
        DB::enableQueryLog();
        DB::beginTransaction();
        try {
            $data = DB::connection('sqlsrv')
                ->table('INA_MD_ProjectItem AS a')
                ->where('a.szProjectId', $projectId)
                ->delete();
            if ($this->needDump) {
                print_r(DB::getQueryLog());
                $this->GetDumpData($data);
            }
        } catch (\Exception $e) {
            DB::rollback();
            if ($this->needDump) {
                print_r(DB::getQueryLog());
                $this->GetDumpData($e);
            }
            return $data;
        }
    }
    public function RemoveProjectPage($projectId)
    {
        DB::enableQueryLog();
        DB::beginTransaction();
        try {
            $data = DB::connection('sqlsrv_confluence')
                ->table('INA_MD_ProjectPage')
                ->where('szProjectId', $projectId)
                ->delete();
            if ($data) {
                $data = DB::connection('sqlsrv_confluence')
                    ->table('INA_MD_ProjectPageItem')
                    ->where('szProjectId', $projectId)
                    ->delete();
            }

            if ($data) {
                DB::commit();
                if ($this->needDump) {
                    print_r(DB::getQueryLog());
                    $this->GetDumpData($data);
                }
            } else {
                DB::rollback();
                if ($this->needDump) {
                    print_r(DB::getQueryLog());
                    $this->GetDumpData($data);
                }
            }
        } catch (\Exception $e) {
            DB::rollback();
            if ($this->needDump) {
                print_r(DB::getQueryLog());
                $this->GetDumpData($e);
            }
        }
    }
    public function GetTemplateProjectPage()
    {
        DB::enableQueryLog();
        try {
            $data = DB::connection('sqlsrv_confluence')
                ->table('INA_MD_TemplateConfluence AS a')
                ->select(
                    DB::raw("a.szTemplateId, a.szTitle, a.szCategory, a.szRootPage")
                )->orderBy('szTemplateId','asc')
                ->get();
            if ($this->needDump) {
                print_r(DB::getQueryLog());
                $this->GetDumpData($data);
            }
        } catch (\Exception $e) {
            $data = '';
            if ($this->needDump) {
                print_r(DB::getQueryLog());
                $this->GetDumpData($e);
            }
        }
        return $data;
    }
    public function GetTemplatePageBody($templateId)
    {
        DB::enableQueryLog();
        try {
            $data = DB::connection('sqlsrv_confluence')
                ->table('INA_MD_TemplateConfluence AS a')
                ->where('a.szTemplateId', $templateId)
                ->value('a.szBodyValue');
        } catch (\Exception $e) {
            $data = '';
            if ($this->needDump) {
                print_r(DB::getQueryLog());
                $this->GetDumpData($e);
            }
        }
        return $data;
    }
    public function UpdateLinkPageItem($projectId, $templateId, $link)
    {
        DB::enableQueryLog();
        try {
            $data = DB::connection('sqlsrv')
                ->table('INA_MD_ProjectItem')
                ->where('szProjectId', $projectId)
                ->where('szTemplateId', $templateId)
                ->update(['szLink' => $link]);
            // print_r(DB::getQueryLog());die;
        } catch (\Exception $e) {
            $data = '';
        }
        return $data;
    }
    public function UpdateProject($projectData, $projectId){
        try{
            $data = DB::connection('sqlsrv')
                ->table('INA_MD_Project')
                ->where('szProjectId', $projectId)
                ->update($projectData);
        }catch (\Exception $e) {
            $data = '';
        }
        return $data;
    }

    public function GetProjectReady(){
        try{
            $dataPending = DB::connection('sqlsrv')
                ->table('INA_MD_Project AS a')
                ->select(
                    DB::raw("a.szProjectId")
                )
                ->where('a.szStatusMapping', 0)
                ->take(1)
                ->orderBy('szProjectId','asc')
                ->first();
                $projectId = $dataPending->szProjectId;
                // print_r($dataPending->szProjectId);die;

            // -----------------------------Inquiry Data project -----------------------------
            $dataProjectPage = DB::connection('sqlsrv_confluence')
                ->table('INA_MD_ProjectPageItem AS a')
                ->join('INA_MD_ProjectPage AS b', 'a.szPageid', 'b.szPageId')
                ->select(
                    DB::raw(
                        "a.szPageId, a.szBody, a.szProjectId, a.szStatus, b.szTemplateId, b.szTittleProject"
                    )
                )
                ->where('a.szStatus', 0)
                ->where('b.szProjectId', $projectId)
                ->take(1)
                ->orderBy('a.szPageId','asc')
                ->get();
                // print_r($dataProjectPage);die;
            // $data = DB::connection('sqlsrv_confluence')
            // ->table('INA_MD_ProjectPageItem AS a')
            // ->join('INA_MD_ProjectPage AS b', 'a.szPageid', 'b.szPageId')
            // ->select(
            //     DB::raw(
            //         "a.szPageId, a.szBody, a.szProjectId, a.szStatus, b.szTemplateId, b.szTittleProject"
            //     )
            // )
            // ->where('szStatus',0)
            // ->wherein('szProjectId',$data1['szProjectId'])
            // ->get();
            // $data = $data1;
        }catch (\Exception $e){
            $data = $e->getMessage();
        }
        return $data;
    }

}
