{{--
  Modal rincian per transaksi (server-side pagination) untuk Laporan Laba Rugi:
  Pendapatan Lain (402), Beban Operasional (501), Beban Pajak Final (502).
  Satu modal dipakai bersama; isinya mengikuti link yang diklik.
  Cara pakai (hanya di-include kalau salah satu baris 402/501/502 ada di hasil laporan):
    @include('partials.modal-labarugi-transaksi', ['tgl_awal' => $tgl_awal, 'tgl_akhir' => $tgl_akhir])
  Pemicu: elemen dengan data-toggle="modal" data-target="#modalLabaRugiTrx" dan
    data-coa="501" data-nama="Beban Operasional" data-nilai="5276240.00" (nilai baris di laporan).
  Data diambil dari route data.labarugi.transaksi -> dbo.fn_LabaRugi_Transaksi(@TglAwal, @TglAkhir, @CoaKode).
--}}
<div class="modal fade" id="modalLabaRugiTrx" tabindex="-1" role="dialog" aria-labelledby="modalLabaRugiTrxLabel" aria-hidden="true"
     data-awal="{{ $tgl_awal }}" data-akhir="{{ $tgl_akhir }}" data-url="{{ route('data.labarugi.transaksi') }}">
  <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title" id="modalLabaRugiTrxLabel">
          <i class="bi bi-receipt"></i> Rincian <span id="lrtNama">-</span>
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
              <div class="text-muted small">Total rincian</div>
              <div class="h5 mb-0" id="lrtTotal">-</div>
              <div class="small text-muted" id="lrtJumlah">&nbsp;</div>
            </div>
          </div>
          <div class="col-md-6 mb-2">
            <div class="border rounded p-2 h-100">
              <div class="text-muted small">Nilai di laporan</div>
              <div class="h5 mb-0" id="lrtLaporan">-</div>
              <div class="small" id="lrtKet">&nbsp;</div>
            </div>
          </div>
        </div>

        <div class="alert alert-danger py-2 d-none" id="lrtError">Gagal memuat data rincian. Coba tutup lalu buka lagi.</div>

        <div class="table-responsive">
          <table id="tableLrTrx" class="table table-bordered table-hover table-sm w-100">
            <thead class="bg-light">
              <tr>
                <th class="text-center">Tanggal</th>
                <th class="text-center">No. Bukti</th>
                <th class="text-center">Keterangan</th>
                <th class="text-center">Nilai</th>
              </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
              {{-- total seluruh hasil pencarian (bukan hanya halaman ini); muncul hanya saat sedang mencari --}}
              <tr id="lrtFootFilter" class="bg-info d-none">
                <th colspan="3" class="text-right" id="lrtFootFilterLabel">TOTAL HASIL PENCARIAN</th>
                <th class="text-right" data-k="nilai"></th>
              </tr>
              <tr id="lrtFootAll" class="bg-light">
                <th colspan="3" class="text-right" id="lrtFootAllLabel">TOTAL SEMUA TRANSAKSI</th>
                <th class="text-right" data-k="nilai"></th>
              </tr>
            </tfoot>
          </table>
        </div>
        <small class="text-muted">
          Diambil dari jurnal umum pada periode laporan, terbaru di atas.
          Baris total menjumlahkan seluruh transaksi, bukan hanya halaman yang tampil.
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
    var $modal = $('#modalLabaRugiTrx');
    if (!$modal.length) return;

    var awal  = String($modal.data('awal'));
    var akhir = String($modal.data('akhir'));
    var url   = String($modal.data('url'));
    var table = null;
    var coa = '', lastCoa = null, nama = '', nilaiLaporan = 0;   // lastCoa = akun yang datanya sedang tampil

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
    function isiTotal($row, t) {
      $row.find('[data-k]').each(function () {
        var v = parseFloat(t[String($(this).data('k'))]) || 0;
        $(this).text(fmt(v, 2, 2)).toggleClass('text-danger', v < 0);
      });
    }

    function updateRingkasan(json) {
      isiTotal($('#lrtFootAll'), json.total);
      $('#lrtFootAllLabel').text('TOTAL SEMUA TRANSAKSI (' + fmt(json.recordsTotal, 0, 0) + ')');
      if (json.recordsFiltered !== json.recordsTotal) {
        isiTotal($('#lrtFootFilter'), json.filtered);
        $('#lrtFootFilterLabel').text('TOTAL HASIL PENCARIAN (' + fmt(json.recordsFiltered, 0, 0) + ')');
        $('#lrtFootFilter').removeClass('d-none');
      } else {
        $('#lrtFootFilter').addClass('d-none');
      }

      var selisih = nilaiLaporan - (parseFloat(json.total.nilai) || 0);
      $('#lrtTotal').text('Rp ' + fmt(json.total.nilai, 2, 2));
      $('#lrtJumlah').text(fmt(json.recordsTotal, 0, 0) + ' transaksi');
      $('#lrtLaporan').text('Rp ' + fmt(nilaiLaporan, 2, 2));
      if (Math.abs(selisih) < 0.01) {
        $('#lrtKet').attr('class', 'small text-success').text('Sama dengan nilai di laporan');
      } else {
        $('#lrtKet').attr('class', 'small text-danger').text('Beda Rp ' + fmt(selisih, 2, 2) + ' dari laporan, coba Refresh Jurnal');
      }
    }

    function resetTampilan() {
      $('#lrtNama').text(nama);
      $('#lrtTotal, #lrtLaporan').text('-');
      $('#lrtJumlah, #lrtKet').attr('class', 'small').html('&nbsp;');
      $('#lrtFootFilter').addClass('d-none');
      $('#lrtError').addClass('d-none');
    }

    // isi modal mengikuti link yang diklik (Pendapatan Lain / Beban Operasional / Beban Pajak Final)
    $modal.on('show.bs.modal', function (e) {
      var $t = $(e.relatedTarget);
      var coaBaru = String($t.data('coa'));
      nama = String($t.data('nama'));
      nilaiLaporan = parseFloat($t.data('nilai')) || 0;
      $('#lrtNama').text(nama);
      if (coaBaru !== coa) {
        coa = coaBaru;
        resetTampilan();
        if (table) { table.search('').order([0, 'desc']); }
      }
    });

    // DataTables baru dibuat saat modal pertama kali dibuka; setelah itu cukup reload kalau akunnya ganti
    $modal.on('shown.bs.modal', function () {
      if (table) {
        if (lastCoa !== coa) table.draw();   // akun berganti: muat ulang dari halaman pertama
        return;
      }

      table = $('#tableLrTrx').DataTable({
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
          data: function (d) { d.tgl_awal = awal; d.tgl_akhir = akhir; d.coa = coa; lastCoa = coa; }
        },
        columns: [
          { data: 'tgl_jurnal', className: 'text-center text-nowrap', render: function (d, t) { return t === 'display' ? tglIndo(d) : d; } },
          { data: 'no_bukti', className: 'text-nowrap', render: esc },
          { data: 'keterangan', render: esc },
          { data: 'nilai', className: 'text-right font-weight-bold', render: function (d, t) {
              if (t !== 'display') return d;
              return '<span' + (parseFloat(d) < 0 ? ' class="text-danger"' : '') + '>' + fmt(d, 2, 2) + '</span>';
          } }
        ],
        language: {
          search: 'Cari:',
          searchPlaceholder: 'No. bukti / keterangan...',
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
          $('#lrtError').addClass('d-none');
          updateRingkasan(json);
        }
      });
      table.on('error.dt', function () {
        $('#lrtError').removeClass('d-none');
      });
    });
  });
</script>
@endpush
