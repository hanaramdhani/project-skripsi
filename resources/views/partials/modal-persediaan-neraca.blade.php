{{--
  Modal rincian persediaan per barang (server-side pagination) untuk Laporan Neraca.
  Cara pakai (hanya di-include kalau baris Persediaan ada di hasil neraca):
    @include('partials.modal-persediaan-neraca', [
        'tgl_akhir' => $tgl_akhir,
        'saldo'     => $persediaan->saldo,   // saldo Persediaan (COA 103) di neraca
    ])
  Pemicu: elemen apa pun dengan data-toggle="modal" data-target="#modalDetailPersediaan".
  Data diambil dari route data.persediaan.neraca -> dbo.fn_Persediaan_Barang(@TglAkhir).
--}}
<div class="modal fade" id="modalDetailPersediaan" tabindex="-1" role="dialog" aria-labelledby="modalDetailPersediaanLabel" aria-hidden="true"
     data-tgl="{{ $tgl_akhir }}" data-saldo="{{ $saldo }}" data-url="{{ route('data.persediaan.neraca') }}">
  <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title" id="modalDetailPersediaanLabel">
          <i class="bi bi-box-seam"></i> Rincian Persediaan per Barang
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

        <div class="alert alert-danger py-2 d-none" id="persError">Gagal memuat data rincian persediaan. Coba tutup lalu buka lagi.</div>

        <div class="table-responsive">
          <table id="tablePersediaan" class="table table-bordered table-hover table-sm w-100">
            <thead class="bg-light">
              <tr>
                <th class="text-center">Kode</th>
                <th class="text-center">Nama Barang</th>
                <th class="text-center">Satuan Terkecil</th>
                <th class="text-center">Qty Beli</th>
                <th class="text-center">Nilai Beli</th>
                <th class="text-center">Qty Jual</th>
                <th class="text-center">Nilai Jual (HPP)</th>
                <th class="text-center">Qty Sisa</th>
                <th class="text-center">Nilai Persediaan</th>
              </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
              {{-- total seluruh hasil pencarian (bukan hanya halaman ini); muncul hanya saat sedang mencari --}}
              <tr id="persFootFilter" class="bg-info d-none">
                <th colspan="3" class="text-right" id="persFootFilterLabel">TOTAL HASIL PENCARIAN</th>
                <th class="text-right" data-k="qty_beli"></th>
                <th class="text-right" data-k="nilai_beli"></th>
                <th class="text-right" data-k="qty_jual"></th>
                <th class="text-right" data-k="nilai_hpp"></th>
                <th class="text-right" data-k="qty_sisa"></th>
                <th class="text-right" data-k="nilai_persediaan"></th>
              </tr>
              <tr id="persFootAll" class="bg-light">
                <th colspan="3" class="text-right" id="persFootAllLabel">TOTAL SEMUA BARANG</th>
                <th class="text-right" data-k="qty_beli"></th>
                <th class="text-right" data-k="nilai_beli"></th>
                <th class="text-right" data-k="qty_jual"></th>
                <th class="text-right" data-k="nilai_hpp"></th>
                <th class="text-right" data-k="qty_sisa"></th>
                <th class="text-right" data-k="nilai_persediaan"></th>
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
    var $modal = $('#modalDetailPersediaan');
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

    // DataTables baru dibuat saat modal pertama kali dibuka, supaya halaman neraca tetap ringan
    $modal.on('shown.bs.modal', function () {
      if (table) return;

      table = $('#tablePersediaan').DataTable({
        processing: true,
        serverSide: true,
        searchDelay: 400,
        autoWidth: false,
        order: [[8, 'desc']],
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        ajax: {
          url: url,
          type: 'GET',
          data: function (d) { d.tgl_akhir = tgl; }
        },
        columns: [
          { data: 'kd_barang', className: 'text-center', render: esc },
          { data: 'nama', render: esc },
          { data: 'satuan_terkecil', className: 'text-center', render: esc },
          { data: 'qty_beli', className: 'text-right', render: num(0, 2) },
          { data: 'nilai_beli', className: 'text-right', render: num(2, 2) },
          { data: 'qty_jual', className: 'text-right', render: num(0, 2) },
          { data: 'nilai_hpp', className: 'text-right', render: num(2, 2) },
          { data: 'qty_sisa', className: 'text-right', render: num(0, 2) },
          { data: 'nilai_persediaan', className: 'text-right font-weight-bold', render: num(2, 2) }
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
        if (json) {
          $('#persError').addClass('d-none');
          updateRingkasan(json);
        }
      });
      table.on('error.dt', function () {
        $('#persError').removeClass('d-none');
      });
    });
  });
</script>
@endpush
