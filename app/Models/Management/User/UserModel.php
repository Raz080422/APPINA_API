<?php

namespace App\Models\Management\User;

use Illuminate\Http\Request;

use Validator, DB, Redirect, Hash, Auth, Session;
class UserModel
{
    public function GetUser(){
        try{
        DB::enableQueryLog();
        $data = DB::table('INA_MD_User AS a')
                ->join('INA_MD_TeamMember AS c','a.szTeamMemberId','c.szMemberId')
                ->join('INA_MD_Team AS d','c.szTeamId','d.szTeamId')
                ->join('INA_MD_TeamRole AS e','c.szTeamRole','e.szRoleId')
                ->select(
                    DB::raw('a.szUserLogin, e.szRoleName, c.szMemberName, c.szJiraId,
                    c.szConfluenceId, d.szTeamName, e.szRoleName,
                    a.szUserId,  a.szTeamMemberId, c.szTeamId, c.szTeamRole, a.szPassword, a.szIsLogin'),
                DB::raw("(CASE WHEN a.szStatus = 0 THEN 'danger' WHEN a.szStatus = 1 THEN 'primary' END) AS BADGE"),
                DB::raw("(CASE WHEN a.szStatus = 0 THEN 'No Active' WHEN a.szStatus = 1 THEN 'Active' END) AS STATUS_USER"))
                ->get();
        // var_dump(DB::getQueryLog());var_dump($username);die;
        }
        catch(\Exception $ex){
            $data = '';
        }
        return $data;
    }
    public function GetDetailUser($userid){
        DB::enableQueryLog();
        try{
            $data = explode('|',$userid);
            $type = $data[0];
            $user = $data[1];

            if(strtoupper($type) == 'USERID'){
                $data = DB::table('INA_MD_User AS a')
                ->join('INA_MD_TeamMember AS c','a.szTeamMemberId','c.szMemberId')
                ->join('INA_MD_Team AS d','c.szTeamId','d.szTeamId')
                ->join('INA_MD_TeamRole AS e','c.szTeamRole','e.szRoleId')
                ->select(
                    DB::raw('c.szMemberName, a.szUserLogin, a.szUserId,  a.szTeamMemberId, a.szIsLogin, a.szStatus,
                    c.*,
                    d.szTeamName,
                    e.szRoleName'))
                ->where('a.szUserId', $user)
                ->first();
            }else if(strtoupper($type) == 'USERLOGIN'){
                $data = DB::table('INA_MD_User AS a')
                ->join('INA_MD_TeamMember AS c','a.szTeamMemberId','c.szMemberId')
                ->join('INA_MD_Team AS d','c.szTeamId','d.szTeamId')
                ->join('INA_MD_TeamRole AS e','c.szTeamRole','e.szRoleId')
                ->select(
                    DB::raw('c.szMemberName, a.szUserLogin, a.szUserId,  a.szTeamMemberId, a.szIsLogin, a.szStatus,
                    c.*,
                    d.szTeamName,
                    e.szRoleName'))
                ->where('a.szUserLogin', $user)
                ->first();
            }
            // var_dump(DB::getQueryLog());var_dump($user);die;
        }
        catch(\Exception $ex){
            $data = '';
            // var_dump(DB::getQueryLog());var_dump($user);die;
        }
        return $data;
    }

    public function GetLoginDetail($userLogin){
        try{
        $data = DB::table('INA_MD_User AS a')
                ->join('INA_MD_TeamMember AS c','a.szTeamMemberId','c.szMemberId')
                ->join('INA_MD_Team AS d','c.szTeamId','d.szTeamId')
                ->join('INA_MD_TeamRole AS e','c.szTeamRole','e.szRoleId')
                ->where('szUserLogin', $userLogin)
                ->selectRaw('a.szUserLogin, e.szRoleName, c.szMemberName, c.szJiraId,
                c.szConfluenceId, d.szTeamName, e.szRoleName,
                a.szUserId, a.szTeamMemberId, c.szTeamId, c.szTeamRole, a.szPassword, a.szIsLogin')
                ->get();
        // var_dump(DB::getQueryLog());var_dump($username);die;
        }catch(\Exception $ex){
            $data = '';
        }
        return $data;
    }

    public function GetTeam(){
        $data = DB::table('INA_MD_Team')->get();

        return $data;
    }

    public function CheckUser($dataReq){
        DB::enableQueryLog();
        try{
            $data = explode('|',$dataReq);
            $type = $data[0];
            $user = $data[1];
            if(strtoupper($type) == 'USERID'){
                $data = DB::table('INA_MD_User AS a')
                    ->join('INA_MD_TeamMember AS c','a.szTeamMemberId','c.szMemberId')
                    ->select(
                        DB::raw('*'))
                    ->where('a.szUserId', $user)
                    ->count();
            }else if(strtoupper($type) == 'USERLOGIN'){
                $data = DB::table('INA_MD_User AS a')
                    ->join('INA_MD_TeamMember AS c','a.szTeamMemberId','c.szMemberId')
                    ->select(
                        DB::raw('*'))
                    ->where('a.szUserLogin', $user)
                    ->count();
            }
        }catch(\Exception $ex){
            $data = '';
        }
        // var_dump(DB::getQueryLog());var_dump($userLogin);die;
        // var_dump($data);die;
        return $data;
    }

    public function InsertUser($dataUser, $dataMember){
        DB::enableQueryLog();
        DB::beginTransaction();
        try{
            $data = DB::table('INA_MD_User')->insert($dataUser);
            // var_dump(DB::getQueryLog());var_dump($data);die;

            if($data){
                $data = DB::table('INA_MD_TeamMember')->insert($dataMember);
            // var_dump(DB::getQueryLog());var_dump($data);die;
            }else{
                DB::rollback();
                $data = false;
            }

            if($data){
                DB::commit();
                $data = true;
            }else{
                DB::rollback();
            }
        }catch (\Exception $e) {

            DB::rollback();
            $data = false;
        }
        return $data;
    }
}
