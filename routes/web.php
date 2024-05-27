<?php

use App\Http\Controllers\Atlasian\Jira\JiraController;
use App\Http\Controllers\Management\User\UserController;
use App\Http\Controllers\Management\Menu\MenuController;
use App\Http\Controllers\Management\Project\ProjectController;
use App\Http\Controllers\Management\Application\ApplicationController;
use App\Http\Controllers\Management\Document\DocumentController;
use App\Http\Controllers\Management\Task\TaskController;
use App\Http\Controllers\Atlasian\Confluence\ConfluenceController;

use App\Http\Controllers\Atlasian\Confluence\TestConfluence;
use App\Jobs\BackendJobs;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
$router->post('dashboard/12345', 'App\Http\Controllers\MainController@getHashing');

Route::namespace('project')->group(function(){
    // Route::get('project/get-project',[ProjectController::class,'GetDetailProject']);
});
Route::namespace('user')->group(function(){
    Route::post('user/post-login',[UserController::class,'GetLoginDetail']);
    Route::get('user/get-alluser',[UserController::class,'GetAllUser']);
    Route::get('user/get-detailuser',[UserController::class,'GetDetailUser']);
    Route::get('user/get-allteam',[UserController::class,'GetTeam']);

});
Route::namespace('menu')->group(function(){
    Route::get('menu/get-menu',[MenuController::class,'GetMenu']);
});

Route::namespace('project')->group(function(){
    Route::get('project/get-maindashboard',[ProjectController::class,'GetMainDashboard']);
    Route::get('project/get-activeproject',[ProjectController::class,'GetActiveProject']);
    Route::get('project/get-totalassignee',[ProjectController::class,'GetTotalAssigneed']);

    Route::get('project/get-detailproject',[ProjectController::class,'GetDetailProject']);

    Route::get('project/get-rootpage',[ProjectController::class,'GetRootPage']);
    Route::get('project/get-parentpage',[ProjectController::class,'GetParentPage']);
    Route::get('project/get-statusproject',[ProjectController::class,'GetStatusProject']);

    Route::get('/project/get-readyproject',[ProjectController::class,'GetProjectReady']);

    Route::post('project/post-submitproject',[ProjectController::class,'PostProject']);

});

Route::namespace('application')->group(function(){
    Route::get('application/get-allapplication',[ApplicationController::class,'GetAllApplication']);
    Route::get('application/get-detailapplication',[ApplicationController::class,'GetDetailApplication']);
});

Route::namespace('document')->group(function(){
    Route::get('document/get-documentproject',[DocumentController::class,'GetAllDocument']);
    Route::get('document/get-mappingdocument',[DocumentController::class,'GetMappingDocument']);

    Route::get('document/get-template',[DocumentController::class,'GetTemplate']);
    Route::get('document/get-detailtemplate',[DocumentController::class,'GetDetailTemplate']);
    Route::get('document/get-roottemplate',[DocumentController::class,'GetRootTemplate']);
    Route::post('document/post-template',[DocumentController::class,'UpdateTemplate']);
});
Route::namespace('task')->group(function(){
    Route::get('task/get-alltask',[TaskController::class,'GetAllTask']);
    Route::get('task/get-detailtask',[TaskController::class,'GetDetailTask']);
});
Route::namespace('confluence')->group(function(){
    Route::post('confluence/post-submitpage',[ConfluenceController::class,'CreateConfluencePage']);
    Route::post('confluence/post-generatedocument',[ConfluenceController::class,'GenerateDocument']);
    Route::get('confluence/get-contentpage',[ConfluenceController::class,'GetConfluencePage']);
    Route::post('confluence/post-templateconfluence',[ConfluenceController::class,'SetConfluenceTemplate']);
    Route::post('confluence/post-parentpage',[ConfluenceController::class,'SetParentConfluence']);


});
Route::namespace('jira')->group(function(){
    Route::get('jira/get-assigneduser', [JiraController::class,'GetAssigneeUser']);
    Route::post('jira/post-assignee', [JiraController::class,'SetAssignee']);
});

Route::namespace('testing')->group(function(){
    Route::get('confluence/testhit',[TestConfluence::class,'TestingPostPage']);
});
