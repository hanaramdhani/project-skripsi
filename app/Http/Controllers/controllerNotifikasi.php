<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class controllerNotifikasi extends Controller
{
    public function getNotifikasi(Request $request)
    {
        $unreadCount = DB::select("SELECT COUNT(*) AS c FROM t_notifikasi WHERE is_read = 0")[0]->c;

        $data = DB::select("SELECT TOP 10
                                id, tipe, kd_barang, no_transaksi, judul, pesan, is_read, created_at
                            FROM t_notifikasi
                            ORDER BY created_at DESC, id DESC");

        return response()->json([
            'unread_count' => (int) $unreadCount,
            'data' => $data,
        ]);
    }

    public function bacaNotifikasi(Request $request)
    {
        $id = $request->id;

        if ($id) {
            DB::update("UPDATE t_notifikasi SET is_read = 1 WHERE id = ?", [$id]);
        } else {
            DB::update("UPDATE t_notifikasi SET is_read = 1 WHERE is_read = 0");
        }

        return response()->json(['success' => true]);
    }
}
