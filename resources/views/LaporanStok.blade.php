@extends('layout.template')

@section('content')

<style>
  #tablePersediaan th.col-highlight, #tablePersediaan td.col-highlight { background-color: #fff3cd !important; }
  #tablePersediaan thead th.col-highlight { background-color: #ffe08a !important; }
</style>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>LAPORAN STOK BARANG</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Laporan Stok</li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-12">

            <div class="card card-outline card-primary">
              <div class="card-header">
                <h3 class="card-title"><i class="bi bi-funnel"></i> Filter Tanggal</h3>
              </div>
              <div class="card-body">
                <form class="form-inline" method="GET" action="{{ url('laporan-stok') }}">
                  <div class="form-group mr-2 mb-2">
                    <label class="mr-2">Per Tanggal</label>
                    <input type="date" name="tgl_akhir" class="form-control" value="{{ $tgl_akhir }}" required>
                  </div>
                  <button type="submit" class="btn btn-primary mb-2 mr-2"><i class="bi bi-search"></i> Tampilkan</button>
                  <a href="{{ url('laporan-stok') }}" class="btn btn-default mb-2"><i class="bi bi-arrow-counterclockwise"></i> Reset</a>
                </form>
              </div>
            </div>

            <div class="card">
              <div class="card-header bg-primary">
                <h3 class="card-title">
                  <i class="bi bi-box-seam"></i> Rincian Persediaan per Barang
                  <small class="ml-2">s/d {{ \Carbon\Carbon::parse($tgl_akhir)->format('d/m/Y') }}</small>
                </h3>
                <div class="card-tools">
                  <button type="button" class="btn btn-sm btn-light" id="btn-refresh-harga-beli" title="Refresh harga beli terakhir">
                    <i class="fas fa-sync-alt"></i> Refresh Harga Beli
                  </button>
                </div>
              </div>
              <div class="card-body">

                <div class="row mb-3">
                  <div class="col-md-4 mb-2">
                    <div class="border rounded p-2 h-100">
                      <div class="text-muted small">Total rincian barang</div>
                      <div class="h5 mb-0" id="persTotalRincian">-</div>
                      <div class="small text-muted" id="persJumlahBarang">&nbsp;</div>
                    </div>
                  </div>
                  <div class="col-md-4 mb-2">
                    <div class="border rounded p-2 h-100">
                      <div class="text-muted small">Saldo Persediaan di neraca</div>
                      <div class="h5 mb-0" id="persSaldoNeraca">-</div>
                    </div>
                  </div>
                  <div class="col-md-4 mb-2">
                    <div class="border rounded p-2 h-100">
                      <div class="text-muted small">Selisih (neraca - rincian)</div>
                      <div class="h5 mb-0" id="persSelisih">-</div>
                      <div class="small" id="persSelisihKet">&nbsp;</div>
                    </div>
                  </div>
                </div>

                <div class="alert alert-danger py-2 d-none" id="persError">Gagal memuat data rincian persediaan. Coba muat ulang halaman.</div>

                <div class="table-responsive">
                  <table id="tablePersediaan" class="table table-bordered table-hover table-sm w-100">
                    <thead class="bg-light">
              <tr>
                <th class="text-center">Kode</th>
                <th class="text-center">Nama Barang</th>
                <th class="text-center">Satuan Terkecil</th>
                <th class="text-center">Harga Beli Terakhir</th>
                <th class="text-center">Harga Jual (now)</th>
                <th class="text-center">Qty Beli</th>
                <th class="text-center">Harga Beli (avg)</th>
                <th class="text-center">Nilai Beli</th>
                <th class="text-center">Qty Jual</th>
                <th class="text-center">HPP (avg)</th>
                <th class="text-center">Nilai Jual (HPP)</th>
                <th class="text-center col-highlight">Stok Akhir</th>
                <th class="text-center col-highlight">Nilai Persediaan</th>
              </tr>
            </thead>
                    <tbody></tbody>
                    <tfoot>
                      <tr id="persFootFilter" class="bg-info d-none">
                <th colspan="5" class="text-right" id="persFootFilterLabel">TOTAL HASIL PENCARIAN</th>
                <th class="text-right" data-k="qty_beli"></th>
                <th></th>
                <th class="text-right" data-k="nilai_beli"></th>
                <th class="text-right" data-k="qty_jual"></th>
                <th></th>
                <th class="text-right" data-k="nilai_hpp"></th>
                <th class="text-right col-highlight" data-k="qty_sisa"></th>
                <th class="text-right col-highlight" data-k="nilai_persediaan"></th>
              </tr>
                      <tr id="persFootAll" class="bg-light">
                <th colspan="5" class="text-right" id="persFootAllLabel">TOTAL SEMUA BARANG</th>
                <th class="text-right" data-k="qty_beli"></th>
                <th></th>
                <th class="text-right" data-k="nilai_beli"></th>
                <th class="text-right" data-k="qty_jual"></th>
                <th></th>
                <th class="text-right" data-k="nilai_hpp"></th>
                <th class="text-right col-highlight" data-k="qty_sisa"></th>
                <th class="text-right col-highlight" data-k="nilai_persediaan"></th>
              </tr>
                    </tfoot>
                  </table>
                </div>
                <small class="text-muted">
                  Nilai persediaan = nilai beli - nilai jual (HPP, harga beli terakhir), sama dengan sumber jurnal akun 103.
                  Baris total menjumlahkan seluruh barang, bukan hanya halaman yang tampil.
                  Qty dalam satuan terkecil masing-masing barang (pcs, kg, dll), jadi total qty hanya sebagai angka bantu.
                </small>
              </div>
            </div>

          </div>
        </div>
      </div>
    </section>
  </div>

@endsection

@section('scripts')
<script src="{{ asset('lte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('lte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script>
  $(function () {
    var tgl   = @json($tgl_akhir);
    var url   = "{{ route('data.persediaan.stok') }}";
    var saldo = parseFloat(@json($saldo)) || 0;

    function fmt(v, min, max) {
      return (parseFloat(v) || 0).toLocaleString('id-ID', { minimumFractionDigits: min, maximumFractionDigits: max });
    }
    function esc(s) {
      return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
      });
    }
    function num(min, max) {
      return function (data, type) {
        if (type !== 'display') return data;
        var cls = parseFloat(data) < 0 ? ' class="text-danger"' : '';
        return '<span' + cls + '>' + fmt(data, min, max) + '</span>';
      };
    }
    function isiTotal($row, t) {
      $row.find('[data-k]').each(function () {
        var $c = $(this);
        var k = String($c.data('k'));
        var v = parseFloat(t[k]) || 0;
        $c.text(fmt(v, k.indexOf('qty') === 0 ? 0 : 2, 2)).toggleClass('text-danger', v < 0);
      });
    }
    function updateRingkasan(json) {
      isiTotal($('#persFootAll'), json.total);
      $('#persFootAllLabel').text('TOTAL SEMUA BARANG (' + fmt(json.recordsTotal, 0, 0) + ')');
      if (json.recordsFiltered !== json.recordsTotal) {
        isiTotal($('#persFootFilter'), json.filtered);
        $('#persFootFilterLabel').text('TOTAL HASIL PENCARIAN (' + fmt(json.recordsFiltered, 0, 0) + ')');
        $('#persFootFilter').removeClass('d-none');
      } else {
        $('#persFootFilter').addClass('d-none');
      }

      var total = parseFloat(json.total_nilai) || 0;
      var selisih = saldo - total;
      $('#persTotalRincian').text('Rp ' + fmt(total, 2, 2));
      $('#persJumlahBarang').text(fmt(json.recordsTotal, 0, 0) + ' barang');
      $('#persSaldoNeraca').text('Rp ' + fmt(saldo, 2, 2));
      $('#persSelisih').text('Rp ' + fmt(selisih, 2, 2));
      if (Math.abs(selisih) < 10) {
        $('#persSelisihKet').attr('class', 'small text-success').text('Selisih pembulatan (wajar)');
      } else {
        $('#persSelisihKet').attr('class', 'small text-danger').text('Selisih besar, coba Refresh Jurnal');
      }
    }

    var table = $('#tablePersediaan').DataTable({
      processing: true,
      serverSide: true,
      searchDelay: 400,
      autoWidth: false,
      order: [[12, 'desc']],
      pageLength: 10,
      lengthMenu: [10, 25, 50, 100],
      ajax: { url: url, type: 'GET', data: function (d) { d.tgl_akhir = tgl; } },
      columns: [
        { data: 'kd_barang', className: 'text-center', render: esc },
        { data: 'nama', render: esc },
        { data: 'satuan_terkecil', className: 'text-center', render: esc },
        { data: 'harga_beli_terakhir', className: 'text-right', render: num(2, 2) },
        { data: 'harga_jual', className: 'text-right', render: num(2, 2) },
        { data: 'qty_beli', className: 'text-right', render: num(0, 2) },
        { data: 'harga_beli', className: 'text-right', render: num(2, 2) },
        { data: 'nilai_beli', className: 'text-right', render: num(2, 2) },
        { data: 'qty_jual', className: 'text-right', render: num(0, 2) },
        { data: 'harga_jual_avg', className: 'text-right', render: num(2, 2) },
        { data: 'nilai_hpp', className: 'text-right', render: num(2, 2) },
        { data: 'qty_sisa', className: 'text-right col-highlight font-weight-bold', render: num(0, 2) },
        { data: 'nilai_persediaan', className: 'text-right col-highlight font-weight-bold', render: num(2, 2) }
      ],
      language: {
        search: 'Cari:',
        searchPlaceholder: 'Nama barang / kode...',
        lengthMenu: 'Tampilkan _MENU_',
        info: 'Menampilkan _START_ - _END_ dari _TOTAL_ barang',
        infoEmpty: 'Tidak ada data',
        infoFiltered: '(dari _MAX_ barang)',
        zeroRecords: 'Barang tidak ditemukan',
        processing: 'Memuat...',
        paginate: { first: '«', last: '»', next: '›', previous: '‹' }
      },
      dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 text-right"f>> rt <"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
    });

    table.on('xhr.dt', function (e, settings, json) {
      if (json) { $('#persError').addClass('d-none'); updateRingkasan(json); }
    });
    table.on('error.dt', function () { $('#persError').removeClass('d-none'); });

    // Refresh harga beli terakhir (mempengaruhi nilai HPP), lalu muat ulang tabel.
    $('#btn-refresh-harga-beli').on('click', function () {
      var $btn = $(this), $icon = $btn.find('i');
      $btn.prop('disabled', true);
      $icon.addClass('fa-spin');
      $.ajax({
        url: "{{ route('refresh.harga.beli.terakhir') }}",
        type: 'POST',
        data: { _token: '{{ csrf_token() }}' },
        success: function (res) {
          if (res && res.success === true) {
            table.ajax.reload(null, false);
            if (typeof Swal !== 'undefined') { Swal.fire('Sukses', 'Data berhasil direfresh', 'success'); }
            else { alert('Data berhasil direfresh'); }
          } else {
            var msg = (res && res.message) || 'Gagal refresh harga beli terakhir.';
            if (typeof Swal !== 'undefined') { Swal.fire('Error', msg, 'error'); } else { alert(msg); }
          }
        },
        error: function (xhr) {
          var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Gagal refresh harga beli terakhir.';
          if (typeof Swal !== 'undefined') { Swal.fire('Error', msg, 'error'); } else { alert(msg); }
        },
        complete: function () { $btn.prop('disabled', false); $icon.removeClass('fa-spin'); }
      });
    });
  });
</script>
@endsection
