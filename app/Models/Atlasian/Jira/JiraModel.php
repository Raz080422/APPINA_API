<?php

namespace App\Models\Atlasian\Jira;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use DB, Exception;
class JiraModel extends Model
{
    public function GetAssigneeUser($projectId){
        try{
            $data = DB::connection('sqlsrv')
            ->table('INA_MD_UQA AS a')
            ->join('INA_MD_TeamMember AS b', 'a.szAssigneeId', 'b.szJiraId')
            ->select(
                DB::raw('a.szJiraCode, a.szReporterId, a.szAssigneeId, a.szUQASummary, a.szUQADescription, a.szJiraStatus, a.szProjectId, a.szUQAId,
                b.szMemberName, b.szTeamId')
            )
            ->where('szProjectId', $projectId)
            ->get();

        }catch(Exception $e){
            $data = $e->getMessage();
        }
        return $data;
    }
    public function CheckUQA($uqaid, $assignee, $jiracode){
        try{
            $data = DB::connection('sqlsrv')
            ->table('INA_MD_UQA')
            ->select(
                DB::raw('szUQAKey, szJiraCode, szReporterId, szAssigneeId, szUQASummary, szUQADescription, szJiraStatus, szProjectId, szUQAId')
            )
            ->where('szUQAId', $uqaid)
            ->where('szAssigneeId', $assignee)
            ->where('szJiraCode', $jiracode)
            ->first();
        }catch(Exception $e){
            $data = $e->getMessage();
        }
        return $data;
    }
    public function InsertAssignee($dataAssignee){
        try{
            $data = DB::connection('sqlsrv')
            ->table('INA_MD_UQA')
            ->insert($dataAssignee);
        }catch(Exception $e)
        {
            $data = $e->getMessage();
        }
        // print_r($data);die;
        return $data;

    }
    public function UpdateAssignee($uqaid, $dataUpdate){
        try{
            $data = DB::connection('sqlsrv')
            ->table('INA_MD_UQA')
            ->where('szUQAId', $uqaid)
            ->update($dataUpdate);
        }catch(Exception $e){
            $data = $e->getMessage();
        }
        // print_r($data);die;

        return $data;
    }
}
