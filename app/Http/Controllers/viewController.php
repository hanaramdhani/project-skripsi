<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class viewController extends Controller
{
    public function Dashboard(){
        $recentSales = $this->getRecentSales();

        // Rentang 7 hari terakhir, dihitung dari tanggal jurnal terbaru
        // (konsisten dgn pola LaporanController yg memakai MAX(tgl_jurnal)).
        $latest    = DB::select("SELECT MAX(tgl_jurnal) AS tgl FROM jurnal_umum");
        $tglAkhir  = date('Y-m-d', strtotime($latest[0]->tgl ?? date('Y-m-d')));
        $tglAwal   = date('Y-m-d', strtotime($tglAkhir . ' -6 days'));

        $trend = $this->getLabaRugiTrend($tglAwal, $tglAkhir);

        $trendLabels = [];
        $trendIncome = [];
        $trendCost   = [];
        $trendProfit = [];
        foreach ($trend as $r) {
            $trendLabels[] = date('Y-m-d', strtotime($r->tgl_jurnal));
            $trendIncome[] = (float) $r->income;
            $trendCost[]   = (float) $r->cost;
            $trendProfit[] = (float) $r->selisih;
        }

        return view('Dashboard', [
            'recentSales' => $recentSales,
            'trendLabels' => $trendLabels,
            'trendIncome' => $trendIncome,
            'trendCost'   => $trendCost,
            'trendProfit' => $trendProfit,
            'trendAwal'   => $tglAwal,
            'trendAkhir'  => $tglAkhir,
        ]);
    }

    /**
     * Ambil data tren laba/rugi harian dari fn_LabaRugiPivot.
     * Kolom hasil: tgl_jurnal, income, cost, selisih.
     */
    private function getLabaRugiTrend(string $tglAwal, string $tglAkhir){
        return DB::select(
            "SELECT * FROM fn_LabaRugiPivot(?, ?) ORDER BY tgl_jurnal",
            [$tglAwal, $tglAkhir]
        );
    }

    /**
     * Ambil 5 transaksi penjualan terbaru dari view v_dashboard_recent_sales.
     */
    private function getRecentSales(){
        return DB::select("SELECT TOP 5
                                no_transaksi,
                                tanggal,
                                customer,
                                items,
                                amount,
                                status
                            FROM v_dashboard_recent_sales
                            ORDER BY tanggal DESC");
    }

    public function Pages(){
        $data = DB::select("SELECT * FROM view_data_ultah");
        return view('Pages', ['data' => $data]);
    }

    /**
     * Endpoint AJAX untuk mengisi 4 stat card di Dashboard.
     * Memanggil sp_DashboardPOS(?, n) untuk n = 1..4:
     *   1 -> Sales Today        : tanggal, nominal, changingPercentage
     *   2 -> MTD Revenue        : ActualSalesMTD, PlanSalesMTD, Achievement
     *   3 -> Orders             : tanggal, jumlahTransaksi, AvgPerTransaction
     *   4 -> Profit (Laba/Rugi) : tgl_jurnal, income, cost, selisih
     */
    public function dashboardStats(Request $request){
        // Tanggal acuan: transaksi penjualan terakhir di t_penjualan,
        // fallback ke hari ini kalau tabel kosong.
        $last = DB::select("SELECT TOP 1 CONVERT(varchar(10), tanggal, 120) AS tanggal
                            FROM t_penjualan ORDER BY tanggal DESC");
        $tanggal = $last[0]->tanggal ?? date('Y-m-d');

        $salesToday = DB::select("EXEC sp_DashboardPOS ?, 1", [$tanggal]);
        $mtdRevenue = DB::select("EXEC sp_DashboardPOS ?, 2", [$tanggal]);
        $orders     = DB::select("EXEC sp_DashboardPOS ?, 3", [$tanggal]);
        $profit     = DB::select("EXEC sp_DashboardPOS ?, 4", [$tanggal]);

        return response()->json([
            'tanggal'      => $tanggal,
            'sales_today'  => $salesToday[0] ?? null,
            'mtd_revenue'  => $mtdRevenue[0] ?? null,
            'orders'       => $orders[0] ?? null,
            'profit'       => $profit[0] ?? null,
        ]);
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