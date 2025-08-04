<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;


class LaporanController extends Controller
{
    public function index()
    {
        # code...
    }
    public function getLaporanLabaRugi(Request $req)
    {
        $awal = $req->awal;
        $akhir = $req->akhir;
        $data = DB::select("CALL GetLabaRugi('2025-04-15','2025-04-15')");
        // $data = DB::select("CALL GetLabaRugi('$awal','$akhir')");
        // return response()->json(['data' => $data]);
        return view('LaporanLabaRugi', ['data'=>$data]);
    }
}
