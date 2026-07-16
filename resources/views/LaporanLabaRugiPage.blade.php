@extends('layout.template')

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>LAPORAN LABA RUGI</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item">Laporan Keuangan</li>
              <li class="breadcrumb-item active">Laporan Laba Rugi</li>
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
                <h3 class="card-title"><i class="bi bi-funnel"></i> Filter Periode</h3>
              </div>
              <div class="card-body">
                <form class="form-inline" method="GET" action="{{ url('laporan-laba-rugi') }}">
                  <div class="form-group mr-2 mb-2">
                    <label class="mr-2">Tanggal Awal</label>
                    <input type="date" name="tgl_awal" class="form-control" value="{{ $tgl_awal }}" required>
                  </div>
                  <div class="form-group mr-2 mb-2">
                    <label class="mr-2">Tanggal Akhir</label>
                    <input type="date" name="tgl_akhir" class="form-control" value="{{ $tgl_akhir }}" required>
                  </div>
                  <button type="submit" class="btn btn-primary mb-2 mr-2"><i class="bi bi-search"></i> Tampilkan</button>
                  <a href="{{ url('laporan-laba-rugi') }}" class="btn btn-default mb-2"><i class="bi bi-arrow-counterclockwise"></i> Reset</a>
                </form>
              </div>
            </div>

            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
                  Laporan Laba Rugi
                  <small class="text-muted ml-2">
                    Periode: {{ \Carbon\Carbon::parse($tgl_awal)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($tgl_akhir)->format('d/m/Y') }}
                  </small>
                </h3>
                <div class="card-tools">
                  <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#modalDownloadLaporan">
                    <i class="bi bi-file-earmark-excel"></i> Download Laporan
                  </button>
                  <button type="button" class="btn btn-sm btn-default" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
                </div>
              </div>
              <div class="card-body table-responsive">
                <table class="table table-bordered">
                  <thead class="bg-light">
                    <tr>
                      <th class="text-center" style="width: 130px;">KODE COA</th>
                      <th class="text-center">NAMA AKUN</th>
                      <th class="text-center" style="width: 200px;">NILAI</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse ($grouped as $kategori => $group)
                      <tr class="bg-light">
                        <td colspan="2"><strong>{{ strtoupper($kategori) }}</strong></td>
                        <td></td>
                      </tr>
                      @foreach ($group['items'] as $item)
                        <tr>
                          <td class="text-center">{{ $item->coa_kode }}</td>
                          <td class="pl-4">{{ $item->coa_nama }}</td>
                          <td class="text-right">{{ number_format($item->nilai, 2, ',', '.') }}</td>
                        </tr>
                      @endforeach
                      <tr>
                        <td colspan="2" class="text-right"><em>Subtotal {{ $kategori }}</em></td>
                        <td class="text-right"><strong>{{ number_format($group['subtotal'], 2, ',', '.') }}</strong></td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="3" class="text-center text-muted">Tidak ada data pada periode ini.</td>
                      </tr>
                    @endforelse
                  </tbody>
                  <tfoot>
                    <tr class="bg-light">
                      <th colspan="2" class="text-right">TOTAL PENDAPATAN</th>
                      <th class="text-right">{{ number_format($total_pendapatan, 2, ',', '.') }}</th>
                    </tr>
                    <tr class="bg-light">
                      <th colspan="2" class="text-right">TOTAL BEBAN</th>
                      <th class="text-right">{{ number_format($total_beban, 2, ',', '.') }}</th>
                    </tr>
                    <tr class="{{ $laba_bersih >= 0 ? 'bg-success' : 'bg-danger' }} text-white">
                      <th colspan="2" class="text-right">
                        {{ $laba_bersih >= 0 ? 'LABA BERSIH' : 'RUGI BERSIH' }}
                      </th>
                      <th class="text-right">{{ number_format($laba_bersih, 2, ',', '.') }}</th>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  @include('partials.modal-download-laporan', [
      'current'   => 'laba_rugi',
      'tgl_awal'  => $tgl_awal,
      'tgl_akhir' => $tgl_akhir,
  ])

@endsection

@section('scripts')
<script></script>
@endsection
