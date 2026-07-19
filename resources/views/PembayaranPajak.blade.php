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
                  <li class="nav-item"><a class="nav-link" href="#settings" data-toggle="tab"><i class="bi bi-plus"></i> Proses Pajak</a></li>
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
                          <th class="text-center">REFF</th>
                          <th class="text-center">#</th>
                      </tr>
                      </thead>
                      <?php
                        // Dipakai oleh modal Edit & form Input (select Periode).
                        $namaBulanID = [
                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                        ];
                      ?>
                      <tbody>
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
                    <!-- Aksi: Generate hutang pajak manual -->
                    <div class="d-flex justify-content-end mb-2">
                      <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalGenerate">
                        <i class="bi bi-gear"></i> Generate Hutang Pajak
                      </button>
                    </div>

                    <!-- Sub-tab: Belum Dibayar / Sudah Dibayar -->
                    <ul class="nav nav-tabs" id="prosesPajakTab" role="tablist">
                      <li class="nav-item">
                        <a class="nav-link active" id="tab-belum-link" data-toggle="tab" href="#tab-belum" role="tab">
                          <i class="bi bi-exclamation-circle text-danger"></i> Belum Dibayar
                        </a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link" id="tab-sudah-link" data-toggle="tab" href="#tab-sudah" role="tab">
                          <i class="bi bi-check-circle text-success"></i> Sudah Dibayar
                        </a>
                      </li>
                    </ul>
                    <div class="tab-content pt-3">
                      <!-- Belum Dibayar -->
                      <div class="tab-pane fade show active" id="tab-belum" role="tabpanel">
                        <table id="tbl-belum" class="table table-bordered table-hover" style="width:100%">
                          <thead>
                          <tr>
                            <th class="text-center">NO TRANSAKSI</th>
                            <th class="text-center">TGL PAJAK</th>
                            <th class="text-center">JATUH TEMPO</th>
                            <th class="text-center">JENIS PAJAK</th>
                            <th class="text-center">NOMINAL</th>
                            <th class="text-center">KETERANGAN</th>
                            <th class="text-center">#</th>
                          </tr>
                          </thead>
                          <tbody></tbody>
                        </table>
                      </div>
                      <!-- Sudah Dibayar -->
                      <div class="tab-pane fade" id="tab-sudah" role="tabpanel">
                        <table id="tbl-sudah" class="table table-bordered table-hover" style="width:100%">
                          <thead>
                          <tr>
                            <th class="text-center">NO TRANSAKSI</th>
                            <th class="text-center">TGL PAJAK</th>
                            <th class="text-center">JENIS PAJAK</th>
                            <th class="text-center">NOMINAL</th>
                            <th class="text-center">NTPN</th>
                            <th class="text-center">TGL BAYAR</th>
                          </tr>
                          </thead>
                          <tbody></tbody>
                        </table>
                      </div>
                    </div>

                    <!-- Modal Form Bayar (dipicu tombol Bayar pada tab Belum Dibayar) -->
                    <div class="modal fade" id="modalBayar" tabindex="-1" role="dialog" aria-hidden="true">
                      <div class="modal-dialog" role="document">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title">Form Pembayaran Pajak</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                          </div>
                          <form id="frm-input" name="frm_input" method="POST" action="{{ route('input.pembayaran.pajak') }}">
                            @csrf
                            <div class="modal-body">
                              <input type="hidden" name="reff_no" id="bayar_reff_no">
                              <div class="form-group">
                                <label class="col-form-label">No Transaksi Hutang</label>
                                <input type="text" id="bayar_reff_tampil" class="form-control" readonly>
                              </div>
                              <div class="form-group">
                                <label class="col-form-label">NTPN</label>
                                <input type="text" name="ntpn" id="bayar_ntpn" class="form-control" value="{{ old('ntpn', $ntpn) }}" readonly>
                              </div>
                              <div class="form-group">
                                <label class="col-form-label">Tanggal</label>
                                <input type="date" name="tanggal" id="bayar_tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                              </div>
                              <div class="form-group">
                                <label class="col-form-label">Masa Pajak</label>
                                <input type="month" name="masa_pajak" id="bayar_masa_pajak" class="form-control" value="{{ date('Y-m') }}" required>
                              </div>
                              <div class="form-group">
                                <label class="col-form-label">Jenis Pajak</label>
                                <input type="text" name="jenis_pajak" id="bayar_jenis_pajak" class="form-control" readonly required>
                              </div>
                              <div class="form-group">
                                <label class="col-form-label">Periode</label>
                                <div class="row">
                                  <div class="col-7">
                                    <select name="periode_bulan" id="bayar_periode_bulan" class="form-control" required>
                                      <?php foreach ($namaBulanID as $bln => $namaBln): ?>
                                        <option value="<?= str_pad($bln, 2, '0', STR_PAD_LEFT) ?>"><?= $namaBln ?></option>
                                      <?php endforeach; ?>
                                    </select>
                                  </div>
                                  <div class="col-5">
                                    <select name="periode_tahun" id="bayar_periode_tahun" class="form-control" required>
                                      <?php for ($th = (int) date('Y') + 1; $th >= 2020; $th--): ?>
                                        <option value="<?= $th ?>"><?= $th ?></option>
                                      <?php endfor; ?>
                                    </select>
                                  </div>
                                </div>
                                <!-- Dikirim ke backend sebagai date: YYYY-MM-01 -->
                                <input type="hidden" name="periode" id="bayar_periode">
                              </div>
                              <div class="form-group">
                                <label class="col-form-label">Nominal</label>
                                <input type="number" step="0.01" min="0" name="nominal" id="bayar_nominal" class="form-control" value="0" required>
                              </div>
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-dismiss="modal">Kembali</button>
                              <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Bayar</button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>

                    <!-- Modal Konfirmasi Generate Hutang Pajak -->
                    <div class="modal fade" id="modalGenerate" tabindex="-1" role="dialog" aria-hidden="true">
                      <div class="modal-dialog" role="document">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title">Generate Hutang Pajak</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                          </div>
                          <div class="modal-body text-center">
                            <i class="bi bi-exclamation-triangle text-warning" style="font-size:2.5rem;"></i>
                            <p class="mt-2 mb-0">Pastikan semua transaksi di bulan sebelumnya sudah selesai terinput.</p>
                            <small class="text-muted">Lanjutkan proses generate hutang pajak?</small>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-primary" id="btn-generate-lanjut"><i class="bi bi-play-circle"></i> Lanjutkan</button>
                          </div>
                        </div>
                      </div>
                    </div>
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
    processing: true,
    serverSide: true,
    paging: true,
    lengthChange: true,
    searching: true,
    ordering: true,
    info: true,
    autoWidth: false,
    responsive: true,
    order: [[0, 'desc']],
    ajax: { url: "{{ route('data.pembayaran.pajak.list') }}", type: "GET" },
    columns: [
      { data: 'tanggal_tampil', className: 'text-center' },
      { data: 'masa_pajak', className: 'text-center' },
      { data: 'periode_tampil', className: 'text-center', orderable: false, searchable: false },
      { data: 'jenis_pajak', className: 'text-center' },
      { data: 'nominal', className: 'text-right',
        render: function (d) {
          return d == null ? '' : Number(d).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        } },
      { data: 'ntpn', className: 'text-center' },
      { data: 'reff_no', className: 'text-center', orderable: false,
        render: function (d) { return d ? d : '-'; } },
      { data: null, className: 'text-center', orderable: false, searchable: false,
        render: function () {
          return '<button type="button" class="btn btn-xs btn-warning edit-data" data-toggle="modal" data-target="#modalEdit"><i class="bi bi-pencil"></i> Edit</button> ' +
                 '<button type="button" class="btn btn-xs btn-danger hapus-data" data-toggle="modal" data-target="#modalHapus"><i class="bi bi-trash"></i> Hapus</button>';
        } }
    ]
  });

  function getRowData(el) {
    let tr = $(el).closest('tr');
    if (tr.hasClass('child')) { tr = tr.prev(); }
    return table.row(tr).data();
  }

  // Gabung tahun + bulan jadi format date YYYY-MM-01 untuk dikirim ke backend.
  function buildPeriode(tahunSel, bulanSel) {
        const th = $(tahunSel).val();
        const bl = $(bulanSel).val();
        if (!th || !bl) return '';
        return th + '-' + bl + '-01';
  }

  // --- Helper format angka ---
  function fmtRupiah(d) {
        return d == null ? '' : Number(d).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  // --- Tabel Hutang Pajak: Belum Dibayar ---
  const tableBelum = $('#tbl-belum').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        order: [[1, 'desc']],
        ajax: { url: "{{ route('data.hutang.pajak.list') }}", type: "GET", data: { paid: 0 } },
        columns: [
          { data: 'no_transaksi', className: 'text-center' },
          { data: 'tgl_pajak_tampil', className: 'text-center' },
          { data: 'jatuh_tempo_tampil', className: 'text-center' },
          { data: 'jenis_pajak', className: 'text-center' },
          { data: 'nominal', className: 'text-right', render: fmtRupiah },
          { data: 'keterangan' },
          { data: null, className: 'text-center', orderable: false, searchable: false,
            render: function () {
              return '<button type="button" class="btn btn-xs btn-success bayar-data" data-toggle="modal" data-target="#modalBayar"><i class="bi bi-cash-coin"></i> Bayar</button>';
            } }
        ]
  });

  // --- Tabel Hutang Pajak: Sudah Dibayar ---
  const tableSudah = $('#tbl-sudah').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        order: [[1, 'desc']],
        ajax: { url: "{{ route('data.hutang.pajak.list') }}", type: "GET", data: { paid: 1 } },
        columns: [
          { data: 'no_transaksi', className: 'text-center' },
          { data: 'tgl_pajak_tampil', className: 'text-center' },
          { data: 'jenis_pajak', className: 'text-center' },
          { data: 'nominal', className: 'text-right', render: fmtRupiah },
          { data: 'ntpn', className: 'text-center', render: function (d) { return d ? d : '-'; } },
          { data: 'tanggal_bayar', className: 'text-center', render: function (d) { return d ? d : '-'; } }
        ]
  });

  function getHutangRowData(el, dt) {
        let tr = $(el).closest('tr');
        if (tr.hasClass('child')) { tr = tr.prev(); }
        return dt.row(tr).data();
  }

  // Perbaiki lebar kolom saat tab ditampilkan (DataTables di tab tersembunyi).
  $('a[data-toggle="tab"]').on('shown.bs.tab', function () {
        tableBelum.columns.adjust();
        tableSudah.columns.adjust();
  });

  // --- Isi modal Bayar dari baris hutang pajak yang dipilih ---
  function syncBayarPeriode() {
        $('#bayar_periode').val(buildPeriode('#bayar_periode_tahun', '#bayar_periode_bulan'));
  }
  $('#bayar_periode_tahun, #bayar_periode_bulan').on('change', syncBayarPeriode);
  $('#frm-input').on('submit', syncBayarPeriode);

  $('#tbl-belum tbody').on('click', '.bayar-data', function () {
        const row = getHutangRowData(this, tableBelum);
        if (!row) { return; }

        $('#bayar_reff_no').val(row.no_transaksi);
        $('#bayar_reff_tampil').val(row.no_transaksi);
        $('#bayar_jenis_pajak').val(row.jenis_pajak);
        $('#bayar_nominal').val(row.nominal);

        // periode_iso berformat YYYY-MM (dari tgl_pajak hutang).
        const parts = String(row.periode_iso || '').split('-');
        if (parts.length >= 2) {
              $('#bayar_periode_tahun').val(parts[0]);
              $('#bayar_periode_bulan').val(parts[1]);
              $('#bayar_masa_pajak').val(parts[0] + '-' + parts[1]);
        }
        syncBayarPeriode();
  });

  // --- Generate Hutang Pajak (panggil sp_GenerateHutangPPhFinal) ---
  $('#btn-generate-lanjut').on('click', function () {
        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Memproses...');
        $.ajax({
              url: "{{ route('generate.hutang.pajak') }}",
              type: 'POST',
              headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .done(function (res) {
              $('#modalGenerate').modal('hide');
              tableBelum.ajax.reload(null, false);
              tableSudah.ajax.reload(null, false);
              if (res && res.added > 0) {
                    alert('Generate selesai. ' + res.added + ' hutang pajak baru dibuat.');
              } else {
                    alert('Generate selesai. Tidak ada hutang pajak baru yang perlu dibuat.');
              }
        })
        .fail(function () {
              alert('Gagal generate hutang pajak. Coba lagi.');
        })
        .always(function () {
              $btn.prop('disabled', false).html('<i class="bi bi-play-circle"></i> Lanjutkan');
        });
  });

  $('#example2 tbody').on('click', '.edit-data', function () {
        let row = getRowData(this);
        $('#edit_id').val(row.id);
        $('#edit_ntpn').val(row.ntpn);
        $('#edit_tanggal').val(row.tanggal_iso);      // YYYY-MM-DD untuk input type=date
        $('#edit_masa_pajak').val(row.masa_pajak);     // YYYY-MM untuk input type=month
        $('#edit_jenis_pajak').val(row.jenis_pajak);
        $('#edit_nominal').val(row.nominal);

        // periode_iso berformat YYYY-MM (kosong jika belum ada).
        const periode = String(row.periode_iso || '');
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
        let row = getRowData(this);
        $('#hapus_id').val(row.id);
  });

</script>

@endsection
