<?php

namespace App\Models\Management\Menu;

use Illuminate\Http\Request;

use Validator, DB, Redirect, Hash, Auth, Session;
use App\Models\Model;

class MenuModel
{
    public function GetMenu($userId){
        try {
            DB::enableQueryLog();
            $data = DB::table('INA_SD_MenuAccess AS a')
                ->join('INA_SD_Menu AS b', 'a.szMenuId', 'b.szMenuId')
                ->join('INA_MD_TeamMember AS c', 'a.szUserLevel', 'c.szTeamRole')
                ->join('INA_MD_User AS d', 'c.szMemberId', 'd.szTeamMemberId')
                ->where('d.szUserLogin', $userId)
                ->orderby('b.szMenuId', 'asc')
                ->select('b.*')
                ->get();
            // var_dump(DB::getQueryLog());var_dump($data);die;
        } catch (\Exception $e) {
            $data = '';
        }

        return $data;
    }
}
