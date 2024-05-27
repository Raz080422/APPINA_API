<?php

namespace App\Models\Management\Document;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use DB;

class DocumentModel
{
    public function GetDumpData($data)
    {
        try {
            print_r($data);
            die;
        } catch (\Exception $e) {
            $data = '';
        }
    }
    public function GetAllDocument($projectid)
    {
        $needDump = 0;
        DB::enableQueryLog();
        try {
            $data = DB::connection('sqlsrv_confluence')
                ->table('INA_MD_ProjectPage AS a')
                ->join('INA_MD_ProjectPageItem AS b','a.szPageId','b.szPageId')
                ->select(
                    DB::raw("a.szProjectId,
                    a.szTittleProject,
                    a.szShortLink,
                    a.szCategory,
                    a.szParentConfluenceId,
                    a.szPageConfluenceId,
                    a.szSpaceKey,
                    b.szStatus,
                    a.szPageId")
                )->where('a.szProjectId', $projectid)
                ->get();
            if ($needDump == 1) {
                print_r(DB::getQueryLog());
                $this->GetDumpData($projectid);
            }
        } catch (\Exception $e) {
            $data = '';
        }
        return $data;
    }

    public function GetMappingDocument($projectid){
        $needDump = 0;
        DB::enableQueryLog();
        try {
            $data = DB::connection('sqlsrv_confluence')
            ->table('INA_SD_DocumentData')
            ->select(
                DB::raw('szDocId,
                szProjectId,
                szJiraLink,
                szBRDLink,
                szBRDTittle,
                szDevLink,
                szDevTittle,
                szSITLink,
                szSITTittle,
                szUATLink,
                szUATTittle,
                szDeployLink,
                szDeployTittle,
                szJiraDSNKey,
                szQATeam,
                szDevTeam,
                szUserTeam,
                szOpsTeam,
                szTypeEnvi,
                szTestPlanKey,
                szTestExecutionKey,
                szSITCaptureLink,
                szSITCaptureTittle,
                szBugLink,
                szBugTittle,
                szDeployCaptureLink,
                szDeployCaptureTittle,
                szPilotCaptureLink,
                szPilotCaptureTittle,
                szJiraKey,
                szProjectName,
                szApplicationName,
                szApplicationModule,
                szProjectDesc,
                szDSNNumber,
                dtmDSN,
                szMIGNumber,
                dtmMIG,
                szApplicationFunction,
                szBAUATNumber,
                dtmBAUAT')
            )->where('szProjectId', $projectid)
            ->first();
        } catch (\Exception $e) {
            $data = '';
        }
        return $data;

    }
    public function GetAllTemplate(){
        try{
            $data = DB::connection('sqlsrv_confluence')
            ->table('INA_MD_TemplateConfluence')
            ->select(
                DB::raw('szTemplateId, szTitle, szRootPage, szCategory, szStatus, szLinkTemplate')
            )
            ->orderBy('szTemplateId', 'ASC')
            ->get();
        }catch(\Exception $e){
            $data = $e->getMessage();
        }
        return $data;
    }
    public function GetDetailTemplate($templateId){
        try{
            $data = DB::connection('sqlsrv_confluence')
            ->table('INA_MD_TemplateConfluence')
            ->select(
                DB::raw('szTemplateId, szTitle, szRootPage, szBodyValue, szLinkTemplate, szPrevBodyValue, szTemplateName, szStatus, szCategory')
            )->where('szTemplateId', $templateId)
            ->first();
        }catch(\Exception $e){
            $data = $e->getMessage();
        }
        return $data;
    }
    public function GetRootTemplate(){
        try{
            $data = DB::connection('sqlsrv_confluence')
            ->table('INA_MD_TemplateConfluence')
            ->select(DB::raw('szTemplateId, szTitle'))
            ->get();
        }catch(\Exception $e){
            $data = $e->getMessage();
        }
        return $data;
    }
    public function UpdateTemplate($templateId, $dataUpdate){
        try{
            $data = DB::connection('sqlsrv_confluence')
            ->table('INA_MD_TemplateConfluence')
            ->where('szTemplateId', $templateId)
            ->update($dataUpdate);
        }catch(Exception $e){
            $data = $e->getMessage();
        }
        return $data;
    }

}
