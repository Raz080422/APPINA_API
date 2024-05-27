<?php

namespace App\Models\Atlasian\Confluence;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


use Validator, DB, DateTime, Session, Carbon\Carbon;

class ConfluenceModel extends Model
{
    private $needDump = false;
    public function GetDumpData($data)
    {
        DB::enableQueryLog();
        try {
            print_r(DB::getQueryLog());
            print_r($data);
            die;
        } catch (Exception $e) {
            $data = '';
            print_r(DB::getQueryLog());
            print_r($e->getMessage());
            die;
        }
    }
    public function UpdateDataConfluence($dataProjectPage, $pageid, $dataparent, $rootPage, $projectId)
    {
        DB::enableQueryLog();
        try {

            // Update ShortLink dan Page Confluence
            $data = DB::connection('sqlsrv_confluence')
                ->table('INA_MD_ProjectPage')
                ->where('szPageId', $pageid)
                ->update($dataProjectPage);


            if ($data) {
                $data = DB::connection('sqlsrv_confluence')
                    ->table('INA_MD_ProjectPage')
                    ->where('szRootPage', $rootPage)
                    ->where('szProjectId', $projectId)
                    ->update($dataparent);
                $data = DB::connection('sqlsrv_confluence')
                    ->table('INA_MD_ProjectPageItem')
                    ->where('szPageId', $pageid)
                    ->update(array('szStatus' => '1'));
            }
            if ($data) {
                $data = [
                    "RESPONSE_CODE" => "0001",
                    "RESPONSE_DESCRIPTION" => "Transaction Success"
                ];
            } else {
                $data = [
                    "RESPONSE_CODE" => "0002",
                    "RESPONSE_DESCRIPTION" => "Transaction Failed"
                ];
            }

        } catch (\Exception $e) {
            $data = '';
        }
        return $data;

    }
    public function GetTemplateId($pageId)
    {
        DB::enableQueryLog();
        try {
            $data = DB::connection('sqlsrv_confluence')
                ->table('INA_MD_ProjectPage')
                ->select(
                    DB::raw("szTemplateId, szShortLink, szProjectId")
                )->where('szPageId', $pageId)
                ->first();
        } catch (\Exception $e) {
            $data = '';
        }
        return $data;
    }
    public function GetDataConfluence($pageid)
    {
        DB::enableQueryLog();
        try {
            $data = DB::connection('sqlsrv_confluence')
                ->table('INA_MD_ProjectPage AS a')
                ->join('INA_MD_ProjectPageItem AS b', 'a.szPageId', 'b.szPageId')
                ->select(
                    DB::raw(
                        "a.szTittleProject, a.szCategory, a.szParentConfluenceId, a.szSpaceKey,
                            b.szBody, b.szProjectId, a.szTemplateId"
                    )
                )
                ->where('a.szPageId', $pageid)
                ->first();

        } catch (\Exception $e) {
            $data = '';
        }
        return $data;
    }
    public function GetDocument($projectId)
    {
        DB::enableQueryLog();
        try {
            $data = DB::connection('sqlsrv_confluence')
                ->table('INA_MD_ProjectPageItem AS a')
                ->join('INA_MD_ProjectPage AS b', 'a.szPageid', 'b.szPageId')
                ->select(
                    DB::raw('a.szPageId, a.szBody, a.szProjectId, a.szStatus, b.szTemplateId, b.szTittleProject')
                )->where('a.szProjectId', $projectId)
                ->where('a.szStatus', '0')
                ->orderBy('a.szPageId', 'asc')
                ->get();
            // var_dump(DB::getQueryLog());die;
            if ($this->needDump) {
                $this->GetDumpData($data);
            }
        } catch (\Exception $e) {
            $data = '';
            if ($this->needDump) {
                $this->GetDumpData($e->getMessage());
            }
        }
        return $data;
    }


    public function UpdateDocumentData($dataInput, $projectId)
    {
        try {
            $data = DB::connection('sqlsrv_confluence')
                ->table('INA_SD_DocumentData')
                ->where('szProjectId', $projectId)
                ->update($dataInput);
            if ($this->needDump) {
                $this->GetDumpData($data);
            }

        } catch (\Exception $e) {
            $data = '';
            if ($this->needDump) {
                $this->GetDumpData($e->getMessage());
            }
        }
        return $data;
    }
    public function UpdateDocumentStatus($docData, $projectId)
    {
        try {
            $data = DB::connection('sqlsrv_confluence')
                ->table('INA_SD_DocumentData')
                ->where('szProjectId', $projectId)
                ->update($docData);
        } catch (\Exception $e) {
            $data = '';
        }
        return $data;
    }

    public function GetConfluenceReadypage($projectId)
    {
        try {
            $data = DB::connection('sqlsrv_confluence')
                ->table('INA_MD_ProjectPageItem')
                ->select(
                    DB::raw("szPageId")
                )
                ->where('szStatus', 0)
                ->where('szProjectId', $projectId)
                ->get();
        } catch (\Exception $e) {
            $data = '';
        }
        return $data;
    }
    public function CheckConfluenceDocumentIsDone(){
        try {
            $data = DB::connection('sqlsrv_confluence')
                ->table('INA_MD_ProjectPage')
                ->select(
                    DB::raw("szProjectId")
                )
                ->where('szStatusMapping', 0)
                ->get();

                if ($data->num_rows() > 0) {
                    $data = false;
                } else {
                    $data = true;
                }
        } catch (\Exception $e) {
            $data = '';
        }
        return $data;
    }

    public function UpdateTemplatedata($templateid, $dataUpdate){
        try{
            $data = DB::connection('sqlsrv_confluence')
            ->table('INA_MD_TemplateConfluence')
            ->where('szTemplateId', $templateid)
            ->update($dataUpdate);
        }catch (\Exception $e) {
            $data = '';
        }
        return $data;
    }
    public function UpdateParentConfluence($action, $dataParent){
        try{
            if($action == 'ADD'){
                $data = DB::connection('sqlsrv_confluence')
                ->table('INA_SD_ParentConfluence')
                ->insert($dataParent);
            }else if($action == 'EDIT'){
                $data = DB::connection('sqlsrv_confluence')
                ->table('INA_SD_ParentConfluence')
                ->where('szParentId', $dataParent->szParentId)
                ->update($dataParent);
            }

        }catch(\Exception $e){
            $data = $e->getMessage();
        }
        return $data;
    }
    public function GetParentById($parentId){
        try{
            $data = DB::connection('sqlsrv_confluence')
                ->table('INA_SD_ParentConfluence')
                ->select(
                    DB::raw("COUNT(szParentId) as szParentId")
                )->where('szParentId', $parentId)
                ->first();
        }catch(\Exception $e){
            $data = $e->getMessage();
        }
        return $data;
    }
}
