{{--
  Modal rincian Kas / buku besar akun 101 (server-side pagination) untuk Laporan Neraca.
  Cara pakai (hanya di-include kalau baris Kas ada di hasil neraca):
    @include('partials.modal-kas-neraca', [
        'tgl_akhir' => $tgl_akhir,
        'saldo'     => $kas->saldo,   // saldo Kas (COA 101) di neraca
    ])
  Pemicu: elemen apa pun dengan data-toggle="modal" data-target="#modalDetailKas".
  Data diambil dari route data.kas.neraca -> dbo.fn_Kas_Detail(@TglAkhir).
--}}
<div class="modal fade" id="modalDetailKas" tabindex="-1" role="dialog" aria-labelledby="modalDetailKasLabel" aria-hidden="true"
     data-tgl="{{ $tgl_akhir }}" data-saldo="{{ $saldo }}" data-url="{{ route('data.kas.neraca') }}">
  <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title" id="modalDetailKasLabel">
          <i class="bi bi-cash-stack"></i> Rincian Kas
          <small class="ml-2">s/d {{ \Carbon\Carbon::parse($tgl_akhir)->format('d/m/Y') }}</small>
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">

        <div class="row mb-3">
          <div class="col-md-4 mb-2">
            <div class="border rounded p-2 h-100">
              <div class="text-muted small">Total kas masuk (debit)</div>
              <div class="h5 mb-0" id="kasTotalDebit">-</div>
              <div class="small text-muted" id="kasJumlahTrx">&nbsp;</div>
            </div>
          </div>
          <div class="col-md-4 mb-2">
            <div class="border rounded p-2 h-100">
              <div class="text-muted small">Total kas keluar (kredit)</div>
              <div class="h5 mb-0" id="kasTotalKredit">-</div>
            </div>
          </div>
          <div class="col-md-4 mb-2">
            <div class="border rounded p-2 h-100">
              <div class="text-muted small">Saldo kas (masuk - keluar)</div>
              <div class="h5 mb-0" id="kasSaldoRincian">-</div>
              <div class="small" id="kasSelisihKet">&nbsp;</div>
            </div>
          </div>
        </div>

        <div class="alert alert-danger py-2 d-none" id="kasError">Gagal memuat data rincian kas. Coba tutup lalu buka lagi.</div>

        <div class="table-responsive">
          <table id="tableKas" class="table table-bordered table-hover table-sm w-100">
            <thead class="bg-light">
              <tr>
                <th class="text-center">Tanggal</th>
                <th class="text-center">No. Bukti</th>
                <th class="text-center">Sumber</th>
                <th class="text-center">Debit (Masuk)</th>
                <th class="text-center">Kredit (Keluar)</th>
                <th class="text-center">Saldo Berjalan</th>
              </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
              {{-- total seluruh hasil pencarian (bukan hanya halaman ini); muncul hanya saat sedang mencari --}}
              <tr id="kasFootFilter" class="bg-info d-none">
                <th colspan="3" class="text-right" id="kasFootFilterLabel">TOTAL HASIL PENCARIAN</th>
                <th class="text-right" data-k="debit"></th>
                <th class="text-right" data-k="kredit"></th>
                <th class="text-right" data-k="saldo"></th>
              </tr>
              <tr id="kasFootAll" class="bg-light">
                <th colspan="3" class="text-right" id="kasFootAllLabel">TOTAL SEMUA TRANSAKSI</th>
                <th class="text-right" data-k="debit"></th>
                <th class="text-right" data-k="kredit"></th>
                <th class="text-right" data-k="saldo"></th>
              </tr>
            </tfoot>
          </table>
        </div>
        <small class="text-muted">
          Buku besar akun Kas (101) dari jurnal umum, terbaru di atas. Baris total menjumlahkan seluruh transaksi, bukan hanya halaman yang tampil.
          Saldo berjalan selalu saldo kas sebenarnya pada transaksi itu, walau tabel sedang dicari atau diurutkan ulang.
          Dalam satu tanggal, kas masuk dihitung lebih dulu daripada kas keluar.
        </small>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
@include('partials.datatables-js')
<script>
  $(function () {
    var $modal = $('#modalDetailKas');
    if (!$modal.length) return;

    var tgl   = String($modal.data('tgl'));
    var url   = String($modal.data('url'));
    var saldo = parseFloat($modal.data('saldo')) || 0;
    var table = null;

    function fmt(v, min, max) {
      return (parseFloat(v) || 0).toLocaleString('id-ID', { minimumFractionDigits: min, maximumFractionDigits: max });
    }
    function esc(s) {
      return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
      });
    }
    function tglIndo(s) {           // 2026-07-19 -> 19/07/2026
      var p = String(s || '').split('-');
      return p.length === 3 ? p[2] + '/' + p[1] + '/' + p[0] : esc(s);
    }
    function uang(showZero) {       // angka rupiah; nol ditampilkan "-" agar kolom masuk/keluar mudah dibaca
      return function (data, type) {
        if (type !== 'display') return data;
        var v = parseFloat(data) || 0;
        if (v === 0 && !showZero) return '<span class="text-muted">-</span>';
        return '<span' + (v < 0 ? ' class="text-danger"' : '') + '>' + fmt(v, 2, 2) + '</span>';
      };
    }

    function isiTotal($row, t) {
      $row.find('[data-k]').each(function () {
        var $c = $(this);
        var v = parseFloat(t[String($c.data('k'))]) || 0;
        $c.text(fmt(v, 2, 2)).toggleClass('text-danger', v < 0);
      });
    }

    function updateRingkasan(json) {
      isiTotal($('#kasFootAll'), json.total);
      $('#kasFootAllLabel').text('TOTAL SEMUA TRANSAKSI (' + fmt(json.recordsTotal, 0, 0) + ')');
      if (json.recordsFiltered !== json.recordsTotal) {
        isiTotal($('#kasFootFilter'), json.filtered);
        $('#kasFootFilterLabel').text('TOTAL HASIL PENCARIAN (' + fmt(json.recordsFiltered, 0, 0) + ')');
        $('#kasFootFilter').removeClass('d-none');
      } else {
        $('#kasFootFilter').addClass('d-none');
      }

      var selisih = saldo - (parseFloat(json.total.saldo) || 0);
      $('#kasTotalDebit').text('Rp ' + fmt(json.total.debit, 2, 2));
      $('#kasJumlahTrx').text(fmt(json.recordsTotal, 0, 0) + ' transaksi');
      $('#kasTotalKredit').text('Rp ' + fmt(json.total.kredit, 2, 2));
      $('#kasSaldoRincian').text('Rp ' + fmt(json.total.saldo, 2, 2));
      if (Math.abs(selisih) < 0.01) {
        $('#kasSelisihKet').attr('class', 'small text-success').text('Sama dengan saldo Kas di neraca');
      } else {
        $('#kasSelisihKet').attr('class', 'small text-danger')
          .text('Beda Rp ' + fmt(selisih, 2, 2) + ' dari neraca, coba Refresh Jurnal');
      }
    }

    // DataTables baru dibuat saat modal pertama kali dibuka, supaya halaman neraca tetap ringan
    $modal.on('shown.bs.modal', function () {
      if (table) return;

      table = $('#tableKas').DataTable({
        processing: true,
        serverSide: true,
        searchDelay: 400,
        autoWidth: false,
        order: [[0, 'desc']],
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        ajax: {
          url: url,
          type: 'GET',
          data: function (d) { d.tgl_akhir = tgl; }
        },
        columns: [
          { data: 'tgl_jurnal', className: 'text-center text-nowrap', render: function (d, t) { return t === 'display' ? tglIndo(d) : d; } },
          { data: 'no_bukti', className: 'text-nowrap', render: esc },
          { data: 'sumber', render: esc },
          { data: 'debit', className: 'text-right', render: uang(false) },
          { data: 'kredit', className: 'text-right', render: uang(false) },
          { data: 'saldo_berjalan', className: 'text-right font-weight-bold', render: uang(true) }
        ],
        language: {
          search: 'Cari:',
          searchPlaceholder: 'No. bukti / sumber...',
          lengthMenu: 'Tampilkan _MENU_',
          info: 'Menampilkan _START_ - _END_ dari _TOTAL_ transaksi',
          infoEmpty: 'Tidak ada data',
          infoFiltered: '(dari _MAX_ transaksi)',
          zeroRecords: 'Transaksi tidak ditemukan',
          processing: 'Memuat...',
          paginate: { first: '«', last: '»', next: '›', previous: '‹' }
        },
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 text-right"f>> rt <"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
      });

      table.on('xhr.dt', function (e, settings, json) {
        if (json) {
          $('#kasError').addClass('d-none');
          updateRingkasan(json);
        }
      });
      table.on('error.dt', function () {
        $('#kasError').removeClass('d-none');
      });
    });
  });
</script>
@endpush
