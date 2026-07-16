{{--
  Modal & tombol download laporan keuangan (Excel multi-sheet).
  Cara pakai di halaman laporan:
    @include('partials.modal-download-laporan', [
        'current'   => 'jurnal_umum',   // jurnal_umum | laba_rugi | neraca
        'tgl_awal'  => $tgl_awal,
        'tgl_akhir' => $tgl_akhir,
    ])
  Tombol pemicu (letakkan di mana saja pada halaman):
    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalDownloadLaporan">
      <i class="bi bi-file-earmark-excel"></i> Download Laporan
    </button>
--}}
@php
    $current   = $current   ?? 'jurnal_umum';
    $tgl_awal  = $tgl_awal  ?? date('Y-m-d');
    $tgl_akhir = $tgl_akhir ?? date('Y-m-d');
    $opsi = [
        'jurnal_umum' => 'Jurnal Umum',
        'laba_rugi'   => 'Laporan Laba Rugi',
        'neraca'      => 'Laporan Neraca',
    ];
@endphp

<div class="modal fade" id="modalDownloadLaporan" tabindex="-1" role="dialog" aria-labelledby="modalDownloadLaporanLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form method="GET" action="{{ route('laporan.export') }}" target="_blank" id="formDownloadLaporan">
        <div class="modal-header bg-success">
          <h5 class="modal-title" id="modalDownloadLaporanLabel">
            <i class="bi bi-file-earmark-excel"></i> Download Laporan (Excel)
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Tanggal Awal</label>
              <input type="date" name="tgl_awal" class="form-control" value="{{ $tgl_awal }}" required>
            </div>
            <div class="form-group col-md-6">
              <label>Tanggal Akhir</label>
              <input type="date" name="tgl_akhir" class="form-control" value="{{ $tgl_akhir }}" required>
            </div>
          </div>

          <label class="mt-2">Laporan yang akan diunduh</label>
          @foreach ($opsi as $val => $label)
            <div class="custom-control custom-checkbox mb-2">
              <input type="checkbox" class="custom-control-input chk-laporan" id="chk_{{ $val }}"
                     name="reports[]" value="{{ $val }}" {{ $current === $val ? 'checked' : '' }}>
              <label class="custom-control-label" for="chk_{{ $val }}">{{ $label }}</label>
            </div>
          @endforeach
          <small class="text-muted d-block mt-1">Setiap laporan akan menjadi sheet terpisah dalam satu file Excel.</small>
          <div class="invalid-feedback d-none" id="chkLaporanError">Pilih minimal satu laporan.</div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success">
            <i class="bi bi-download"></i> Download
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Form tersembunyi untuk Refresh Jurnal (dipicu tombol di card-tools) --}}
<form id="formRefreshJurnal" method="POST" action="{{ route('jurnal.refresh') }}" class="d-none">
  @csrf
</form>

@push('scripts')
<script>
  $(function () {
    $('#btnRefreshJurnal').on('click', function () {
      if (confirm('Generate ulang jurnal umum untuk 2 hari terakhir sampai sekarang?')) {
        var $b = $(this);
        $b.prop('disabled', true).html('<i class="bi bi-arrow-repeat"></i> Memproses...');
        $('#formRefreshJurnal').submit();
      }
    });

    $('#formDownloadLaporan').on('submit', function (e) {
      if ($('.chk-laporan:checked').length === 0) {
        e.preventDefault();
        $('#chkLaporanError').removeClass('d-none');
        return false;
      }
      $('#chkLaporanError').addClass('d-none');
      // tutup modal setelah submit (download berjalan di tab baru)
      $('#modalDownloadLaporan').modal('hide');
    });
    $('.chk-laporan').on('change', function () {
      if ($('.chk-laporan:checked').length > 0) $('#chkLaporanError').addClass('d-none');
    });
  });
</script>
@endpush
