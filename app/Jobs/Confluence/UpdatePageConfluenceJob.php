<?php

namespace App\Jobs\Confluence;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use DB;
use Carbon\Carbon;

use App\Helper\Log\LogData;

class UpdatePageConfluenceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $logData = new LogData();

        try {
            $dataConfluence = DB::connection('sqlsrv_confluence')
                ->table('INA_MD_ProjectPage AS a')
                ->join('INA_MD_ProjectPageItem AS b', 'a.szPageId', 'b.szPageId')
                ->where('b.szStatus', 2)
                ->get();

            $dataLog = [
                'szModule' => 'Update Data Confluence',
                'szLogModule' => 'Get Data Prepare',
                'szLogProcess' => $dataConfluence,
                'szStatusTransaction' => 'Success',
                'dtmLastUpdated' => Carbon::now()->toDateTimeString()
            ];
            $logData->InsertLogData($dataLog);

            // print_r($dataConfluence);die;
            foreach ($dataConfluence as $key => $value) {

                $dataProject = DB::connection('sqlsrv')
                    ->table('INA_MD_Project AS a')
                    ->join('INA_MD_TeamMember AS b', 'a.szUserCreatorId', 'b.szConfluenceId')
                    ->where('szProjectId', $value->szProjectId)
                    ->first();

                if ($value->szCategory == "RootPage") {
                    $projectName = Carbon::now()->format('Ymd').' - '. $dataProject->szJiraCode.' - '.$dataProject->szProjectName;
                } else {
                    $projectName = $value->szTittleProject;
                }
                $dataLog = [
                    'szModule' => 'Update Data Confluence',
                    'szLogModule' => 'Processing Page',
                    'szLogProcess' => $projectName,
                    'szStatusTransaction' => 'Success',
                    'dtmLastUpdated' => Carbon::now()->toDateTimeString()
                ];
                $logData->InsertLogData($dataLog);
                // print_r($value);
                // die;
                $url = "/rest/api/content/" . $value->szPageConfluenceId;
                $body = preg_replace(array('/\n/', '/\r/'), '', $value->szBody);
                // print_r($body   );die;
                $version = $value->szVersion + 1;
                $request = "{\n    \"title\":\"$projectName\",\n    \"type\":\"page\",\n    \"version\":\n        {\n            \"number\":$version\n        },\n        \"body\":{\n            \"storage\":{\n                \"representation\":\"storage\",\n                \"value\":\"$body\"\n            }\n        }\n    \n}";
                // print_r(($request));die;
                $config = (object) config('config_url');
                $address = $config->confluence_host . $url;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $address);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Accept: application/json',
                    'Content-Type: application/json',
                    'Authorization: Basic ' . $dataProject->szAtlasianToken,
                ]);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

                $response = curl_exec($ch);
                $data = json_decode($response, true);
                curl_close($ch);
                // print_r($data);die;
                $dataInsert = [
                    'szRequest' => json_encode($request),
                    'szResponse' => json_encode($data),
                    'dtmHit' => Carbon::now()->toDateTimeString(),
                    'szUrl' => $url,
                    'szpageId' => $value->szPageId
                ];
                $dataLog = DB::connection('sqlsrv_confluence')
                    ->table('INA_SD_LogService')
                    ->insert($dataInsert);
                // $responseData = $data[0];
                if ($data["title"]) {
                    $dataVersion = [
                        'szVersion' => $version,
                        'szStatus' => 3
                    ];

                    $updateVersion = DB::connection('sqlsrv_confluence')
                        ->table('INA_MD_ProjectPageItem')
                        ->where('szPageId', $value->szPageId)
                        ->update($dataVersion);

                }
                $checkdata = DB::connection('sqlsrv_confluence')
                    ->table('INA_MD_ProjectPageItem')
                    ->select(DB::raw('szPageId'))
                    ->where('szStatus', 2)
                    ->where('szProjectId', $value->szProjectId)
                    ->get();
                // print_r(count($checkdata));die;
                if (count($checkdata) < 1) {
                    $dataMapping = [
                        'szStatusMapping'   => 3,
                        'szStatusDoc'       => 1
                    ];
                    $updateMapping = DB::connection('sqlsrv')
                        ->table('INA_MD_Project')
                        ->where('szProjectId', $value->szProjectId)
                        ->update($dataMapping);
                }
            }
        } catch (\Exception $e) {
            $dataLog = [
                'szModule' => 'Update Data Confluence',
                'szLogModule' => 'Exception',
                'szLogProcess' => $e->getMessage(),
                'szStatusTransaction' => 'Failed',
                'dtmLastUpdated' => Carbon::now()->toDateTimeString()
            ];
            $logData->InsertLogData($dataLog);
        }
    }
}
