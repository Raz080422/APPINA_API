<?php

namespace App\Models\Management\Config;

use Illuminate\Http\Request;

use Validator, DB, Redirect, Hash, Auth, Session;
use App\Models\Model;

class ConfigModel
{
    public static function GetConfigValue(string $configName, string $configItem)
    {
        try {
            $data = DB::table('INA_SD_Config AS a')
                ->join('INA_SD_ConfigItem AS b', 'a.szConfigId', 'b.szConfigId')
                ->where('a.szConfigName', $configName)
                ->where('b.szItem', $configItem)
                ->value('b.szConfigValue AS ConfigValue');
        } catch (\Exception $e) {
            $data = '';
        }
        return $data;
    }


    public function UpdateConfigValue($configName, $configItem, $value)
    {
        DB::enableQueryLog();
        DB::beginTransaction();
        try {
            $data = DB::connection('sqlsrv')
                ->table('INA_SD_ConfigItem')
                ->where('szConfigModule', $configName)
                ->where('szItem', $configItem)
                ->update(array('szConfigValue' => $value));

                DB::commit();
                // if($data){
                // }else{
                //     DB::rollBack();
                // }
        } catch (\Exception $e) {
            $data = '';
            DB::rollBack();
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
        } catch (\Exception $e) {
            $data = '';
        }
        return $data;
    }
}
