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
        $data = DB::select("SELECT * from r_laba_rugi_harian WHERE tanggal BETWEEN '2025-04-03' AND '2025-04-08'");
        // $data = DB::select("CALL GetLabaRugi('$awal','$akhir')");
        // return response()->json(['data' => $data]);
        return view('LaporanLabaRugi', ['data'=>$data]);
    }
}
