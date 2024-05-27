<?php

namespace App\Models\Management\Task;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use DB;

class TaskModel
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
    public function GetAllTask($projectid)
    {
        $needDump = 0;
        DB::enableQueryLog();
        try {
            $data = DB::connection('sqlsrv')
                ->table('INA_MD_Project AS a')
                ->join('INA_MD_ProjectItem AS b', 'a.szProjectId', 'b.szProjectId')
                ->select(
                    DB::raw("a.szProjectId,
                    b.shItemNumber,
                    b.szItem,
                    b.szLink,
                    b.dtmPlan,
                    b.dtmProcess,
                    b.szStatus")
                )->where('a.szProjectId', $projectid)
                ->distinct()
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

    public function GetDetailTask($projectid, $itemnumber)
    {
        $needDump = 0;
        DB::enableQueryLog();
        try {
            $data = DB::connection('sqlsrv')
                ->table('INA_MD_ProjectItem AS a')
                ->select(
                    DB::raw("a.szItem,
                a.szLink,
                a.dtmPlan,
                a.dtmProcess,
                a.shItemNumber,
                a.szProjectId")
                )->where('a.szProjectId', $projectid)
                ->where('a.shItemNumber', $itemnumber)
                ->first();
            if ($needDump == 1) {
                print_r(DB::getQueryLog());
                $this->GetDumpData($data);
            }
        } catch (\Exception $e) {
            $data = $e;
        }
        return $data;
    }
}
