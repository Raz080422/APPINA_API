<?php

namespace App\Models\Management\Application;

use Validator, DB, Redirect, Hash, Auth, Session;

class ApplicationModel
{
    public function GetAllApplication()
    {
        DB::enableQueryLog();
        try {
            $data = DB::connection('sqlsrv')
                ->table('INA_INV_Application')
                ->select(
                    DB::raw("szApplicationId, szApplicationName, szTeamId, szEnvironmentId")
                )->get();
        } catch (\Exception $e) {
            $data = '';
        }
        return $data;
    }

    public function GetDetailApplication($applicationid)
    {
        DB::enableQueryLog();
        try {
            $data = DB::connection('sqlsrv')
                ->table('INA_INV_Application')
                ->select(
                    DB::raw("szApplicationId, szApplicationName, szTeamId, szEnvironmentId, szQATeam, szDevTeam, szUserTeam, szOpsTeam")
                )
                ->where('szApplicationId', $applicationid)
                ->first();
        } catch (\Exception $e) {
            $data = '';
        }
        return $data;
    }
}
