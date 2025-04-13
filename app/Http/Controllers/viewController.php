<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class viewController extends Controller
{
    public function Dashboard(){
        return view('Dashboard');
    }

    public function Pages(){
        $data = DB::select("SELECT * FROM view_data_ultah");
        return view('Pages', ['data' => $data]);
    }

    public function sendMessage(){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.whatsapp.com/send?phone=+6287864649100&text=Hi%
                        20there!%20Thank%20you%20for%20reaching
                        %20out.%20How%20can%20we%
                        20assist%20you%20today?');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);
    }
}