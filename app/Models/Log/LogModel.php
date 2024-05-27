<?php

namespace App\Models\Log;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Validator, DB, DateTime, Session, Carbon\Carbon;

class LogModel extends Model
{
    private $needDump = false;
    public function GetDumpData($data)
    {
        DB::enableQueryLog();
        try {
            print_r(DB::getQueryLog());
            print_r("----------------RESULT DATA----------------\n");
            print_r($data);
            print_r("\n----------------RESULT DATA----------------");
            die;
        } catch (\Exception $e) {
            $data = '';
            print_r(DB::getQueryLog());

            print_r("----------------EXCEPTION----------------\n");
            print_r($e->getMessage());
            print_r("\n----------------EXCEPTION----------------");
            die;
        }
    }
    public function InsertLogService($dataLog)
    {
        try {
            $data = DB::connection('sqlsrv_confluence')
                ->table('INA_SD_LogService')
                ->insert($dataLog);

            if($this->needDump) {
                $this->GetDumpData($dataLog);
            }
        } catch (\Exception $e) {
            $data = '';
            if ($this->needDump) {
                $this->GetDumpData($dataLog);
            }
        }
        return $data;
    }
}
