{{--
  Modal rincian per barang (server-side pagination) untuk Laporan Laba Rugi.
  Dipakai dua kali dari function yang sama (dbo.fn_LabaRugi_PerBarang), hanya kolom yang tampil yang beda:
    Pendapatan Penjualan (401) -> kolom Penjualan, HPP (503) -> kolom HPP.
  Cara pakai (hanya di-include kalau barisnya ada di hasil laporan):
    @include('partials.modal-labarugi-barang', [
        'id'          => 'Penjualan',                          // Penjualan | Hpp (jadi #modalLabaRugiPenjualan / #modalLabaRugiHpp)
        'judul'       => 'Rincian Pendapatan Penjualan per Barang',
        'url'         => route('data.labarugi.penjualan'),
        'nilaiKey'    => 'nilai_penjualan',                    // nilai_penjualan | nilai_hpp
        'nilaiLabel'  => 'Penjualan',
        'akunNama'    => 'Pendapatan Penjualan',
        'nilaiLaporan'=> 156755529.82,                         // nilai baris di laporan
        'tgl_awal'    => $tgl_awal,
        'tgl_akhir'   => $tgl_akhir,
    ])
  Pemicu: elemen dengan data-toggle="modal" data-target="#modalLabaRugiPenjualan".
--}}
<div class="modal fade" id="modalLabaRugi{{ $id }}" tabindex="-1" role="dialog" aria-labelledby="modalLabaRugi{{ $id }}Label" aria-hidden="true"
     data-awal="{{ $tgl_awal }}" data-akhir="{{ $tgl_akhir }}" data-url="{{ $url }}"
     data-key="{{ $nilaiKey }}" data-laporan="{{ $nilaiLaporan }}" data-akun="{{ $akunNama }}">
  <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title" id="modalLabaRugi{{ $id }}Label">
          <i class="bi bi-box-seam"></i> {{ $judul }}
          <small class="ml-2">{{ \Carbon\Carbon::parse($tgl_awal)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($tgl_akhir)->format('d/m/Y') }}</small>
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">

        <div class="row mb-3">
          <div class="col-md-6 mb-2">
            <div class="border rounded p-2 h-100">
              <div class="text-muted small">Total {{ $nilaiLabel }}</div>
              <div class="h5 mb-0 js-total">-</div>
              <div class="small js-ket">&nbsp;</div>
            </div>
          </div>
          <div class="col-md-6 mb-2">
            <div class="border rounded p-2 h-100">
              <div class="text-muted small">Jumlah barang terjual</div>
              <div class="h5 mb-0 js-jumlah">-</div>
            </div>
          </div>
        </div>

        <div class="alert alert-danger py-2 d-none js-error">Gagal memuat data rincian. Coba tutup lalu buka lagi.</div>

        <div class="table-responsive">
          <table id="tableLr{{ $id }}" class="table table-bordered table-hover table-sm w-100">
            <thead class="bg-light">
              <tr>
                <th class="text-center">Kode</th>
                <th class="text-center">Nama Barang</th>
                <th class="text-center">Satuan Terkecil</th>
                <th class="text-center">Qty Jual</th>
                @if ($nilaiKey === 'nilai_hpp')
                <th class="text-center">Harga Beli Terakhir (avg)</th>
                @endif
                <th class="text-center">{{ $nilaiLabel }}</th>
              </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
              {{-- total seluruh hasil pencarian (bukan hanya halaman ini); muncul hanya saat sedang mencari --}}
              <tr class="bg-info d-none js-foot-filter">
                <th colspan="3" class="text-right js-foot-filter-label">TOTAL HASIL PENCARIAN</th>
                <th class="text-right" data-k="qty_jual" data-d="0"></th>
                @if ($nilaiKey === 'nilai_hpp')
                <th></th>
                @endif
                <th class="text-right" data-k="{{ $nilaiKey }}"></th>
              </tr>
              <tr class="bg-light js-foot-all">
                <th colspan="3" class="text-right js-foot-all-label">TOTAL SEMUA BARANG</th>
                <th class="text-right" data-k="qty_jual" data-d="0"></th>
                @if ($nilaiKey === 'nilai_hpp')
                <th></th>
                @endif
                <th class="text-right" data-k="{{ $nilaiKey }}"></th>
              </tr>
            </tfoot>
          </table>
        </div>
        <small class="text-muted">
          @if ($nilaiKey === 'nilai_hpp')
            HPP = qty terjual x harga beli rata-rata (nilai beli / qty beli), sama dengan sumber jurnal akun 503. Harga Beli (avg) = HPP / qty jual.
          @else
            Penjualan = qty x harga jual, sama dengan sumber jurnal akun 401.
          @endif
          Baris total menjumlahkan seluruh barang pada periode ini, bukan hanya halaman yang tampil.
          Qty dalam satuan terkecil masing-masing barang (pcs, kg, dll), jadi total qty hanya angka bantu.
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
    var $modal = $('#modalLabaRugi{{ $id }}');
    if (!$modal.length) return;

    var awal    = String($modal.data('awal'));
    var akhir   = String($modal.data('akhir'));
    var url     = String($modal.data('url'));
    var key     = String($modal.data('key'));             // nilai_penjualan | nilai_hpp
    var akun    = String($modal.data('akun'));
    var laporan = parseFloat($modal.attr('data-laporan')) || 0;
    var table   = null;

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
        return '<span' + (parseFloat(data) < 0 ? ' class="text-danger"' : '') + '>' + fmt(data, min, max) + '</span>';
      };
    }
    function isiTotal($row, t) {
      $row.find('[data-k]').each(function () {
        var $c = $(this);
        var v = parseFloat(t[String($c.data('k'))]) || 0;
        var d = $c.attr('data-d') === '0' ? 0 : 2;
        $c.text(fmt(v, d, 2)).toggleClass('text-danger', v < 0);
      });
    }

    function updateRingkasan(json) {
      isiTotal($modal.find('.js-foot-all'), json.total);
      $modal.find('.js-foot-all-label').text('TOTAL SEMUA BARANG (' + fmt(json.recordsTotal, 0, 0) + ')');
      if (json.recordsFiltered !== json.recordsTotal) {
        isiTotal($modal.find('.js-foot-filter'), json.filtered);
        $modal.find('.js-foot-filter-label').text('TOTAL HASIL PENCARIAN (' + fmt(json.recordsFiltered, 0, 0) + ')');
        $modal.find('.js-foot-filter').removeClass('d-none');
      } else {
        $modal.find('.js-foot-filter').addClass('d-none');
      }

      var total = parseFloat(json.total[key]) || 0;
      var selisih = laporan - total;
      $modal.find('.js-total').text('Rp ' + fmt(total, 2, 2));
      $modal.find('.js-jumlah').text(fmt(json.recordsTotal, 0, 0) + ' barang');
      if (Math.abs(selisih) < 10) {
        $modal.find('.js-ket').attr('class', 'small text-success js-ket')
          .text('Laporan ' + akun + ': ' + fmt(laporan, 2, 2) + ' (selisih pembulatan ' + fmt(selisih, 2, 2) + ')');
      } else {
        $modal.find('.js-ket').attr('class', 'small text-danger js-ket')
          .text('Laporan ' + akun + ': ' + fmt(laporan, 2, 2) + ' (beda ' + fmt(selisih, 2, 2) + ', coba Refresh Jurnal)');
      }
    }

    // DataTables baru dibuat saat modal pertama kali dibuka, supaya halaman laporan tetap ringan
    $modal.on('shown.bs.modal', function () {
      if (table) return;

      table = $('#tableLr{{ $id }}').DataTable({
        processing: true,
        serverSide: true,
        searchDelay: 400,
        autoWidth: false,
        order: [[{{ $nilaiKey === 'nilai_hpp' ? 5 : 4 }}, 'desc']],
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        ajax: {
          url: url,
          type: 'GET',
          data: function (d) { d.tgl_awal = awal; d.tgl_akhir = akhir; }
        },
        columns: [
          { data: 'kd_barang', className: 'text-center', render: esc },
          { data: 'nama', render: esc },
          { data: 'satuan_terkecil', className: 'text-center', render: esc },
          { data: 'qty_jual', className: 'text-right', render: num(0, 2) },
          @if ($nilaiKey === 'nilai_hpp')
          { data: null, className: 'text-right', orderable: false, searchable: false,
            render: function (data, type, row) {
              var q = parseFloat(row.qty_jual) || 0;
              var avg = q ? (parseFloat(row.nilai_hpp) || 0) / q : 0;
              return type === 'display' ? '<span>' + fmt(avg, 2, 2) + '</span>' : avg;
            } },
          @endif
          { data: key, className: 'text-right font-weight-bold', render: num(2, 2) }
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
          $modal.find('.js-error').addClass('d-none');
          updateRingkasan(json);
        }
      });
      table.on('error.dt', function () {
        $modal.find('.js-error').removeClass('d-none');
      });
    });
  });
</script>
@endpush
