<?php

namespace App\Helper\Log;


use Exception;
use DB;
class LogData
{
    public function InsertLogData($dataLog){
        try{
            $data = DB::connection('sqlsrv')
            ->table('INA_SD_Log')
            ->Insert($dataLog);
        }catch(Exception $e){

        }
    }
}
