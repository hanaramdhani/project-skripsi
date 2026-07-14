<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class controllerUser extends Controller
{
    /**
     * Threshold level yang diizinkan akses menu User.
     * 0 = Superadmin, 1 = Admin, 2 = Kasir.
     * Hanya level <= 1 (Superadmin & Admin) yang boleh mengelola user.
     */
    private const MAX_MANAGER_LEVEL = 1;

    private function currentLevel(): int
    {
        return (int) (session('user.group_level') ?? 99);
    }

    /**
     * Blok user yang tidak punya hak (kasir / level > 1).
     * Dipanggil di awal setiap action menu User.
     */
    private function guardAccess()
    {
        if ($this->currentLevel() > self::MAX_MANAGER_LEVEL) {
            abort(403, 'Anda tidak memiliki akses ke menu User.');
        }
    }

    /**
     * Ambil group_level dari kd_user target.
     * Return null jika user tidak ditemukan / kd_group invalid.
     */
    private function targetUserLevel(string $kd_user): ?int
    {
        $row = DB::select("
            SELECT g.group_level
            FROM m_user u
            LEFT JOIN m_group g ON u.kd_group = g.kd_group
            WHERE u.kd_user = ?
        ", [$kd_user]);
        return empty($row) ? null : (int) $row[0]->group_level;
    }

    /**
     * Ambil group_level dari kd_group. Null jika tidak ditemukan.
     */
    private function groupLevel(string $kd_group): ?int
    {
        $row = DB::select("SELECT group_level FROM m_group WHERE kd_group = ?", [$kd_group]);
        return empty($row) ? null : (int) $row[0]->group_level;
    }

    public function viewMasterUser()
    {
        $this->guardAccess();
        $currentLevel = $this->currentLevel();

        // Dropdown group: hanya group dengan level di bawah currentLevel
        $groups = DB::select("
            SELECT kd_group, nama, group_level
            FROM m_group
            WHERE status = 1 AND group_level > ?
            ORDER BY group_level, kd_group
        ", [$currentLevel]);

        // Generate next kd_user: UAA + 3-digit counter
        $last = DB::select("SELECT TOP 1 kd_user FROM m_user ORDER BY kd_user DESC");
        if (!empty($last)) {
            $last_seq = substr($last[0]->kd_user, -3);
            $incremented = str_pad((int)$last_seq + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $incremented = '000';
        }
        $kd_user = 'UAA' . $incremented;

        return view('User', [
            'groups'  => $groups,
            'kd_user' => $kd_user,
        ]);
    }

    public function getDataUser(Request $request)
    {
        $this->guardAccess();
        $currentLevel = $this->currentLevel();

        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = $request->input('search.value', '');

        $orderColumnIndex = (int) $request->input('order.0.column', 0);
        $orderDir = strtolower($request->input('order.0.dir', 'asc')) === 'desc' ? 'DESC' : 'ASC';
        $columnsMap = [
            0 => 'u.kd_user',
            1 => 'u.username',
            2 => 'g.nama',
            3 => 'u.keterangan',
            4 => 'u.status',
        ];
        $orderColumn = $columnsMap[$orderColumnIndex] ?? 'u.kd_user';
        if ($length <= 0) { $length = 10; }

        $from = "FROM m_user u LEFT JOIN m_group g ON u.kd_group = g.kd_group";

        // Access-control filter: only users with a group level below current user's level.
        $where    = ["g.group_level > ?"];
        $bindings = [$currentLevel];

        if (!empty($search)) {
            $where[] = "(u.kd_user LIKE ? OR u.username LIKE ? OR g.nama LIKE ? OR u.keterangan LIKE ?)";
            $bindings[] = "%$search%";
            $bindings[] = "%$search%";
            $bindings[] = "%$search%";
            $bindings[] = "%$search%";
        }
        $whereSql = 'WHERE ' . implode(' AND ', $where);

        // recordsTotal reflects the always-applied access filter (no search).
        $recordsTotal    = DB::select("SELECT COUNT(*) AS c $from WHERE g.group_level > ?", [$currentLevel])[0]->c;
        $recordsFiltered = DB::select("SELECT COUNT(*) AS c $from $whereSql", $bindings)[0]->c;

        $sql = "SELECT
                    u.kd_user    AS kd_user,
                    u.username   AS username,
                    g.nama       AS group_nama,
                    u.kd_group   AS kd_group,
                    u.keterangan AS keterangan,
                    u.status     AS status
                $from $whereSql
                ORDER BY $orderColumn $orderDir
                OFFSET $start ROWS FETCH NEXT $length ROWS ONLY";
        $data = DB::select($sql, $bindings);

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function inputUser(Request $request)
    {
        $this->guardAccess();

        $request->validate([
            'kd_user'  => 'required|string',
            'kd_group' => 'required|string',
            'username' => 'required|string|max:50',
            'password' => 'required|string|min:6',
            'status'   => 'required|in:0,1',
        ]);

        $currentLevel = $this->currentLevel();

        // Validasi: kd_group target harus DI BAWAH currentLevel
        $targetGroupLevel = $this->groupLevel($request->kd_group);
        if ($targetGroupLevel === null || $targetGroupLevel <= $currentLevel) {
            return back()
                ->withInput()
                ->withErrors(['kd_group' => 'Tidak diizinkan membuat user pada group ini.']);
        }

        // Cek username unik
        $exists = DB::select("SELECT 1 FROM m_user WHERE username = ?", [$request->username]);
        if (!empty($exists)) {
            return back()
                ->withInput()
                ->withErrors(['username' => 'Username sudah digunakan.']);
        }

        $hash = Hash::make($request->password);

        DB::insert("INSERT INTO m_user (kd_user, kd_group, username, password, keterangan, status)
                    VALUES (?, ?, ?, ?, ?, ?)", [
            $request->kd_user,
            $request->kd_group,
            $request->username,
            $hash,
            $request->keterangan ?: '-',
            (int) $request->status,
        ]);

        return redirect()->route('index.master.user');
    }

    public function editUser(Request $request)
    {
        $this->guardAccess();

        $request->validate([
            'edit_kd_user'  => 'required|string',
            'edit_kd_group' => 'required|string',
            'edit_username' => 'required|string|max:50',
            'edit_status'   => 'required|in:0,1',
        ]);

        $currentLevel = $this->currentLevel();

        // Target user harus level di bawah currentLevel
        $targetLevel = $this->targetUserLevel($request->edit_kd_user);
        if ($targetLevel === null || $targetLevel <= $currentLevel) {
            return back()->withErrors(['edit' => 'Tidak diizinkan mengedit user ini.']);
        }

        // Group tujuan juga harus di bawah currentLevel
        $newGroupLevel = $this->groupLevel($request->edit_kd_group);
        if ($newGroupLevel === null || $newGroupLevel <= $currentLevel) {
            return back()->withErrors(['edit_kd_group' => 'Tidak diizinkan memindahkan user ke group ini.']);
        }

        // Cek username unik (kecuali untuk dirinya sendiri)
        $exists = DB::select("SELECT 1 FROM m_user WHERE username = ? AND kd_user <> ?",
                             [$request->edit_username, $request->edit_kd_user]);
        if (!empty($exists)) {
            return back()->withErrors(['username' => 'Username sudah digunakan.']);
        }

        if ($request->filled('edit_password')) {
            $hash = Hash::make($request->edit_password);
            DB::update("UPDATE m_user
                        SET kd_group = ?, username = ?, password = ?, keterangan = ?, status = ?
                        WHERE kd_user = ?", [
                $request->edit_kd_group,
                $request->edit_username,
                $hash,
                $request->edit_keterangan ?: '-',
                (int) $request->edit_status,
                $request->edit_kd_user,
            ]);
        } else {
            DB::update("UPDATE m_user
                        SET kd_group = ?, username = ?, keterangan = ?, status = ?
                        WHERE kd_user = ?", [
                $request->edit_kd_group,
                $request->edit_username,
                $request->edit_keterangan ?: '-',
                (int) $request->edit_status,
                $request->edit_kd_user,
            ]);
        }

        return redirect()->route('index.master.user');
    }

    public function hapusUser(Request $request)
    {
        $this->guardAccess();

        $kd_user = $request->hapus_kd_user;

        // Cegah hapus diri sendiri
        if (session('user.kd_user') === $kd_user) {
            return back()->withErrors(['hapus' => 'Tidak bisa menghapus user yang sedang login.']);
        }

        // Target harus level di bawah currentLevel
        $currentLevel = $this->currentLevel();
        $targetLevel  = $this->targetUserLevel($kd_user);
        if ($targetLevel === null || $targetLevel <= $currentLevel) {
            return back()->withErrors(['hapus' => 'Tidak diizinkan menghapus user ini.']);
        }

        DB::delete("DELETE FROM m_user WHERE kd_user = ?", [$kd_user]);
        return redirect()->route('index.master.user');
    }
}
