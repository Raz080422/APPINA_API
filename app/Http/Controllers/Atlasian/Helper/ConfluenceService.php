<?php

namespace App\Http\Controllers\Atlasian\Helper;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ConfluenceService extends Controller
{
    public function post_confluence_service($url, $auth, $request)
    {
        $config = (object) config('config_url');
        $address = $config->confluence_host . $url;
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $address);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Basic ' . $auth,
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            $response = curl_exec($ch);
            $data = json_decode($response, true);
            curl_close($ch);
        } catch (\Exception $e) {
            $data = '';

        }
        return $data;
    }
    public function get_confluence_service($url, $auth, $request){
        $config = (object) config('config_url');
        $address = $config->confluence_host . $url;
        // print_r($address);die;
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $address);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Basic ' . $auth,
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            $response = curl_exec($ch);
            // print_r(json_decode($response, true));die;
            $data = json_decode($response, true);
            curl_close($ch);
        } catch (\Exception $e) {
            $data = '';

        }
        return $data;
    }
    public function put_confluence_service($url, $auth, $request){
        $config = (object) config('config_url');
        $address = $config->confluence_host . $url;
        // print_r($address);die;
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $address);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Basic ' . $auth,
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            $response = curl_exec($ch);
            // print_r(json_decode($response, true));die;
            $data = json_decode($response, true);
            curl_close($ch);
        } catch (\Exception $e) {
            $data = '';

        }
        return $data;
    }
}
