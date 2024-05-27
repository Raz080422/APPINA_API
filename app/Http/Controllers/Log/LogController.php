<?php

namespace App\Http\Controllers\Log;

use App\Http\Controllers\Controller;

use App\Models\Log\LogModel;

use Illuminate\Http\Request;
use Carbon\Carbon;

class LogController extends Controller
{
    private $needDump = false;
    public function __construct(LogModel $log)
    {
        $this->log = $log;
    }
    public function GetDumpData($data)
    {
        try {
            print_r("----------------RESULT DATA----------------\n");
            print_r($data);
            print_r("\n----------------RESULT DATA----------------");
            die;
        } catch (\Exception $e) {
            $data = '';

            print_r("----------------EXCEPTION----------------\n");
            print_r($e->getMessage());
            print_r("\n----------------EXCEPTION----------------");
            die;
        }
    }
    public function InsertLogService($request, $response, $url, $pageId){
        try{
            $dataInsert = [
                'szRequest' => json_encode($request),
                'szResponse'=> json_encode($response),
                'dtmHit'=> Carbon::now()->toDateTimeString(),
                'szUrl'=> $url,
                'szpageId'=> $pageId
            ];
            $data = $this->log->InsertLogService($dataInsert);
            if($this->needDump){
                $this->GetDumpData($dataInsert);
            }
        }catch(\Exception $e){
            return $data = '';
            if($this->needDump){
                $this->GetDumpData($e->getMessage());
            }
        }
        return $data;
    }
}
