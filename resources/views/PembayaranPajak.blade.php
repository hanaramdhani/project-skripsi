@extends('layout.template')

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>DATA PEMBAYARAN PAJAK</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item">Transaksi</li>
              <li class="breadcrumb-item active">Pembayaran Pajak</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">

          <div class="col-md-12">
            <div class="card">
              <div class="card-header p-2">
                <ul class="nav nav-pills">
                  <li class="nav-item"><a class="nav-link active" href="#activity" data-toggle="tab">Data Pembayaran Pajak</a></li>
                  <li class="nav-item"><a class="nav-link" href="#settings" data-toggle="tab"><i class="bi bi-plus"></i> Input Data</a></li>
                </ul>
              </div><!-- /.card-header -->
              <div class="card-body">
                <div class="tab-content">
                  <div class="active tab-pane" id="activity">
                    <table id="example2" class="table table-bordered table-hover" style="width:100%">
                      <thead>
                      <tr>
                          <th class="text-center">TANGGAL</th>
                          <th class="text-center">MASA PAJAK</th>
                          <th class="text-center">PERIODE</th>
                          <th class="text-center">JENIS PAJAK</th>
                          <th class="text-center">NOMINAL</th>
                          <th class="text-center">NTPN</th>
                          <th class="text-center">#</th>
                      </tr>
                      </thead>
                      <tbody>
                      <?php
                        $namaBulanID = [
                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                        ];
                        // Tampilkan periode (date YYYY-MM-01) sebagai "Bulan YYYY".
                        $formatPeriode = function ($p) use ($namaBulanID) {
                            if (empty($p)) return '-';
                            $ts = strtotime($p);
                            if ($ts === false) return e($p);
                            return $namaBulanID[(int) date('n', $ts)] . ' ' . date('Y', $ts);
                        };
                      ?>
                      <?php foreach ($data as $key => $value): ?>
                          <?php $periodeIso = !empty($value->periode ?? null) ? date('Y-m', strtotime($value->periode)) : ''; ?>
                          <tr class="data-row">
                            <td class="text-center"><?= $value->tanggal_tampil ?></td>
                            <td class="text-center"><?= $value->masa_pajak ?></td>
                            <td class="text-center"><?= $formatPeriode($value->periode ?? null) ?></td>
                            <td class="text-center"><?= $value->jenis_pajak ?></td>
                            <td class="text-right"><?= number_format((float) $value->nominal, 2, ',', '.') ?></td>
                            <td class="text-center"><?= $value->ntpn ?></td>
                            <td class="text-center">
                              <button
                                    type="button"
                                    class="btn btn-xs btn-warning edit-data"
                                    data-toggle="modal"
                                    data-target="#modalEdit"
                                    data-id="<?= $value->id ?>"
                                    data-tanggal="<?= $value->tanggal_iso ?>"
                                    data-masa-pajak="<?= $value->masa_pajak ?>"
                                    data-periode="<?= $periodeIso ?>"
                                    data-jenis-pajak="<?= e($value->jenis_pajak) ?>"
                                    data-nominal="<?= $value->nominal ?>"
                                    data-ntpn="<?= $value->ntpn ?>"
                              ><i class="bi bi-pencil"></i> Edit</button>
                              <button
                                    type="button"
                                    class="btn btn-xs btn-danger hapus-data"
                                    data-toggle="modal"
                                    data-target="#modalHapus"
                                    data-id="<?= $value->id ?>"
                              ><i class="bi bi-trash"></i> Hapus</button>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                      <tfoot>
                      </tfoot>
                    </table>

                    <div class="modal fade" id="modalEdit" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                      <div class="modal-dialog" role="document">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Form Edit Pembayaran Pajak</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                          </div>
                          <div class="modal-body">
                            <form class="form-horizontal" id="frm-edit" name="frm-edit" method="POST" action="{{ route('edit.pembayaran.pajak') }}">
                              @csrf
                              <input name="edit_id" type="hidden" id="edit_id">
                              <div class="form-group">
                                <label class="col-form-label">NTPN</label>
                                <input name="edit_ntpn" class="form-control" id="edit_ntpn" readonly>
                              </div>
                              <div class="form-group">
                                <label class="col-form-label">Tanggal</label>
                                <input name="edit_tanggal" type="date" class="form-control" id="edit_tanggal" required>
                              </div>
                              <div class="form-group">
                                <label class="col-form-label">Masa Pajak</label>
                                <input name="edit_masa_pajak" type="month" class="form-control" id="edit_masa_pajak" required>
                              </div>
                              <div class="form-group">
                                <label class="col-form-label">Jenis Pajak</label>
                                <select name="edit_jenis_pajak" class="form-control" id="edit_jenis_pajak" required>
                                  <option value="PPh 21">PPh 21</option>
                                  <option value="PPh 22">PPh 22</option>
                                  <option value="PPh 23">PPh 23</option>
                                  <option value="PPh 25">PPh 25</option>
                                  <option value="PPh 4 ayat 2">PPh 4 ayat 2</option>
                                  <option value="PPN">PPN</option>
                                </select>
                              </div>
                              <div class="form-group">
                                <label class="col-form-label">Periode</label>
                                <div class="row">
                                  <div class="col-7">
                                    <select name="edit_periode_bulan" class="form-control" id="edit_periode_bulan" required>
                                      <?php foreach ($namaBulanID as $bln => $namaBln): ?>
                                        <option value="<?= str_pad($bln, 2, '0', STR_PAD_LEFT) ?>"><?= $namaBln ?></option>
                                      <?php endforeach; ?>
                                    </select>
                                  </div>
                                  <div class="col-5">
                                    <select name="edit_periode_tahun" class="form-control" id="edit_periode_tahun" required>
                                      <?php for ($th = (int) date('Y') + 1; $th >= 2020; $th--): ?>
                                        <option value="<?= $th ?>"><?= $th ?></option>
                                      <?php endfor; ?>
                                    </select>
                                  </div>
                                </div>
                                <!-- Dikirim ke backend sebagai date: YYYY-MM-01 -->
                                <input name="edit_periode" type="hidden" id="edit_periode">
                              </div>
                              <div class="form-group">
                                <label class="col-form-label">Nominal</label>
                                <input name="edit_nominal" type="number" step="0.01" min="0" class="form-control" id="edit_nominal" required>
                              </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Kembali</button>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
                          </div>
                          </form>
                        </div>
                      </div>
                    </div>
                    <div class="modal fade" id="modalHapus" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                      <div class="modal-dialog" role="document">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Hapus Item</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                          </div>
                          <div class="modal-body">
                            <h4>Apakah anda yakin ingin menghapus item ini ?</h4>
                            <form class="form-horizontal" id="frm-hapus" name="frm-hapus" method="POST" action="{{ route('hapus.pembayaran.pajak') }}">
                              @csrf
                                <input name="hapus_id" type="hidden" class="form-control" id="hapus_id" readonly>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Kembali</button>
                            <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i> Hapus</button>
                          </div>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="tab-pane" id="settings">
                    <form class="form-horizontal" id="frm-input" name="frm_input" method="POST" action="{{ route('input.pembayaran.pajak') }}">
                    <?php
                        date_default_timezone_set('Asia/Jakarta');
                        echo '<div class="col-sm-7"><h3 class="text-right">'.date('d/m/Y').'</h3></div>';
                    ?>
                      @csrf
                      <div class="form-group row">
                        <label class="col-sm-2 col-form-label">NTPN</label>
                        <div class="col-sm-5">
                          <input type="text" name="ntpn" id="ntpn" class="form-control" value="{{ old('ntpn', $ntpn) }}" readonly>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Tanggal</label>
                        <div class="col-sm-5">
                          <input type="date" name="tanggal" id="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Masa Pajak</label>
                        <div class="col-sm-5">
                          <input type="month" name="masa_pajak" id="masa_pajak" class="form-control" value="{{ date('Y-m') }}" required>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Jenis Pajak</label>
                        <div class="col-sm-5">
                          <select name="jenis_pajak" class="form-control" required>
                            <option value="PPh 21">PPh 21</option>
                            <option value="PPh 22">PPh 22</option>
                            <option value="PPh 23">PPh 23</option>
                            <option value="PPh 25">PPh 25</option>
                            <option value="PPh 4 ayat 2">PPh 4 ayat 2</option>
                            <option value="PPN">PPN</option>
                          </select>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Periode</label>
                        <div class="col-sm-5">
                          <div class="row">
                            <div class="col-5">
                              <select name="periode_bulan" id="periode_bulan" class="form-control" required>
                                <?php foreach ($namaBulanID as $bln => $namaBln): ?>
                                  <option value="<?= str_pad($bln, 2, '0', STR_PAD_LEFT) ?>" <?= $bln == (int) date('n') ? 'selected' : '' ?>><?= $namaBln ?></option>
                                <?php endforeach; ?>
                              </select>
                            </div>
                            <div class="col-4">
                              <select name="periode_tahun" id="periode_tahun" class="form-control" required>
                                <?php for ($th = (int) date('Y') + 1; $th >= 2020; $th--): ?>
                                  <option value="<?= $th ?>" <?= $th == (int) date('Y') ? 'selected' : '' ?>><?= $th ?></option>
                                <?php endfor; ?>
                              </select>
                            </div>
                            <div class="col-3">
                              <button type="button" class="btn btn-info btn-block" id="btn-cek-nominal"><i class="bi bi-search"></i> Cek</button>
                            </div>
                          </div>
                          <!-- Dikirim ke backend sebagai date: YYYY-MM-01 -->
                          <input type="hidden" name="periode" id="periode">
                        </div>
                      </div>
                      <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Nominal</label>
                        <div class="col-sm-5">
                          <input type="number" step="0.01" min="0" name="nominal" id="nominal" class="form-control" value="0" required>
                        </div>
                      </div>

                       <button type="submit" class="btn btn-success text-right"><i class="bi bi-save"></i> Simpan</button>
                    </form>

                  </div>
                  <!-- /.tab-pane -->
                </div>
                <!-- /.tab-content -->
              </div><!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>

@endsection

@section('scripts')
<!-- DataTables  & Plugins -->
<script src="{{ asset('lte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('lte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('lte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('lte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<script src="{{ asset('lte/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('lte/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
<script src="{{ asset('lte/plugins/jszip/jszip.min.js') }}"></script>
<script src="{{ asset('lte/plugins/pdfmake/pdfmake.min.js') }}"></script>
<script src="{{ asset('lte/plugins/pdfmake/vfs_fonts.js') }}"></script>
<script src="{{ asset('lte/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
<script src="{{ asset('lte/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
<script src="{{ asset('lte/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>
<!-- Page specific script -->


<!-- SCRIPT UNTUK TABEL DATA -->
<script>
  const table = $('#example2').DataTable({
    paging: true,
    lengthChange: true,
    searching: true,
    ordering: false,
    info: true,
    autoWidth: false,
    responsive: true
  });

  // Gabung tahun + bulan jadi format date YYYY-MM-01 untuk dikirim ke backend.
  function buildPeriode(tahunSel, bulanSel) {
        const th = $(tahunSel).val();
        const bl = $(bulanSel).val();
        if (!th || !bl) return '';
        return th + '-' + bl + '-01';
  }

  // --- Form Input ---
  function syncPeriodeInput() {
        $('#periode').val(buildPeriode('#periode_tahun', '#periode_bulan'));
  }
  syncPeriodeInput();
  $('#periode_tahun, #periode_bulan').on('change', syncPeriodeInput);
  $('#frm-input').on('submit', syncPeriodeInput);

  // Tombol Cek Nominal: ambil nominal hutang pajak dari t_hutang_pajak.
  $('#btn-cek-nominal').on('click', function () {
        syncPeriodeInput();
        const $btn        = $(this);
        const periode     = $('#periode').val();           // YYYY-MM-01
        const jenis_pajak = $('select[name="jenis_pajak"]').val();

        $btn.prop('disabled', true);
        $.get("{{ route('cek.hutang.pajak') }}", { periode: periode, jenis_pajak: jenis_pajak })
          .done(function (res) {
                $('#nominal').val(res.nominal);
                if (!res.found) {
                      alert('Tidak ada data hutang pajak untuk periode & jenis pajak tersebut.');
                }
          })
          .fail(function () {
                alert('Gagal mengambil nominal hutang pajak. Coba lagi.');
          })
          .always(function () {
                $btn.prop('disabled', false);
          });
  });

  $('#example2 tbody').on('click', '.edit-data', function () {
        $('#edit_id').val($(this).data('id'));
        $('#edit_ntpn').val($(this).data('ntpn'));
        $('#edit_tanggal').val($(this).data('tanggal'));
        $('#edit_masa_pajak').val($(this).data('masa-pajak'));
        $('#edit_jenis_pajak').val($(this).data('jenis-pajak'));
        $('#edit_nominal').val($(this).data('nominal'));

        // data-periode berformat YYYY-MM (kosong jika belum ada).
        const periode = String($(this).data('periode') || '');
        const parts   = periode.split('-');
        if (parts.length >= 2) {
              $('#edit_periode_tahun').val(parts[0]);
              $('#edit_periode_bulan').val(parts[1]);
        }
        $('#edit_periode').val(buildPeriode('#edit_periode_tahun', '#edit_periode_bulan'));
  });

  $('#edit_periode_tahun, #edit_periode_bulan').on('change', function () {
        $('#edit_periode').val(buildPeriode('#edit_periode_tahun', '#edit_periode_bulan'));
  });
  $('#frm-edit').on('submit', function () {
        $('#edit_periode').val(buildPeriode('#edit_periode_tahun', '#edit_periode_bulan'));
  });

  $('#example2 tbody').on('click', '.hapus-data', function () {
        $('#hapus_id').val($(this).data('id'));
  });

</script>

@endsection
