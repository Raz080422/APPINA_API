<?php

namespace App\Models\Jobs;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use DB;

class ProjectJobs extends Model
{
    public function Updateproject($projectId){
        try{
            $data = DB::connection('sqlsrv')
                    ->table('INA_MD_Project')
                    ->where ('szProjectId',$projectId)
                    ->update(['szStatusMapping'=> '1']);

        }catch(\Exception $e){
            $data = '';
        }
        return $data;
    }
}
