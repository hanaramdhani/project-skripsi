@extends('layout.template')

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>LAPORAN NERACA</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item">Laporan Keuangan</li>
              <li class="breadcrumb-item active">Laporan Neraca</li>
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
                <form class="form-inline" method="GET" action="{{ url('laporan-neraca') }}">
                  <div class="form-group mr-2 mb-2">
                    <label class="mr-2">Tanggal Awal</label>
                    <input type="date" name="tgl_awal" class="form-control" value="{{ $tgl_awal }}" required>
                  </div>
                  <div class="form-group mr-2 mb-2">
                    <label class="mr-2">Tanggal Akhir</label>
                    <input type="date" name="tgl_akhir" class="form-control" value="{{ $tgl_akhir }}" required>
                  </div>
                  <button type="submit" class="btn btn-primary mb-2 mr-2"><i class="bi bi-search"></i> Tampilkan</button>
                  <a href="{{ url('laporan-neraca') }}" class="btn btn-default mb-2"><i class="bi bi-arrow-counterclockwise"></i> Reset</a>
                </form>
              </div>
            </div>

            @php
              // Label & urutan penyajian neraca
              $tipeLabels = [
                'ASSET'     => 'ASET',
                'LIABILITY' => 'LIABILITAS (KEWAJIBAN)',
                'EQUITY'    => 'EKUITAS (MODAL)',
              ];

              $totalAset      = $grouped['ASSET']['saldo']     ?? 0;
              $totalLiabilitas= $grouped['LIABILITY']['saldo'] ?? 0;
              $totalEkuitas   = $grouped['EQUITY']['saldo']    ?? 0;
              $totalPasiva    = $totalLiabilitas + $totalEkuitas;   // Liabilitas + Ekuitas
              $isBalance      = abs($totalAset - $totalPasiva) < 0.01;
            @endphp

            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
                  Laporan Neraca (Balance Sheet)
                  <small class="text-muted ml-2">
                    Periode: {{ \Carbon\Carbon::parse($tgl_awal)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($tgl_akhir)->format('d/m/Y') }}
                  </small>
                </h3>
                <div class="card-tools">
                  <button type="button" class="btn btn-sm btn-default" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
                </div>
              </div>
              <div class="card-body table-responsive">
                <table class="table table-bordered table-hover">
                  <thead class="bg-light">
                    <tr>
                      <th class="text-center" style="width: 130px;">KODE COA</th>
                      <th class="text-center">NAMA AKUN</th>
                      <th class="text-center" style="width: 200px;">JUMLAH</th>
                    </tr>
                  </thead>
                  <tbody>
                    @php $adaData = false; @endphp

                    {{-- ========================= ASET ========================= --}}
                    @if (!empty($grouped['ASSET']))
                      @php $adaData = true; @endphp
                      <tr class="bg-secondary text-white">
                        <td colspan="3"><strong>{{ $tipeLabels['ASSET'] }}</strong></td>
                      </tr>
                      @foreach ($grouped['ASSET']['items'] as $item)
                        <tr>
                          <td class="text-center">{{ $item->coa_kode }}</td>
                          <td class="pl-4">{{ $item->coa_nama }}</td>
                          <td class="text-right {{ $item->saldo < 0 ? 'text-danger' : '' }}">{{ number_format($item->saldo, 2, ',', '.') }}</td>
                        </tr>
                      @endforeach
                      <tr class="bg-light">
                        <td colspan="2" class="text-right"><strong>TOTAL ASET</strong></td>
                        <td class="text-right"><strong>{{ number_format($totalAset, 2, ',', '.') }}</strong></td>
                      </tr>
                    @endif

                    {{-- ====================== LIABILITAS ====================== --}}
                    @if (!empty($grouped['LIABILITY']))
                      @php $adaData = true; @endphp
                      <tr class="bg-secondary text-white">
                        <td colspan="3"><strong>{{ $tipeLabels['LIABILITY'] }}</strong></td>
                      </tr>
                      @foreach ($grouped['LIABILITY']['items'] as $item)
                        <tr>
                          <td class="text-center">{{ $item->coa_kode }}</td>
                          <td class="pl-4">{{ $item->coa_nama }}</td>
                          <td class="text-right {{ $item->saldo < 0 ? 'text-danger' : '' }}">{{ number_format($item->saldo, 2, ',', '.') }}</td>
                        </tr>
                      @endforeach
                      <tr class="bg-light">
                        <td colspan="2" class="text-right"><strong>TOTAL LIABILITAS</strong></td>
                        <td class="text-right"><strong>{{ number_format($totalLiabilitas, 2, ',', '.') }}</strong></td>
                      </tr>
                    @endif

                    {{-- ======================== EKUITAS ======================= --}}
                    @if (!empty($grouped['EQUITY']))
                      @php $adaData = true; @endphp
                      <tr class="bg-secondary text-white">
                        <td colspan="3"><strong>{{ $tipeLabels['EQUITY'] }}</strong></td>
                      </tr>
                      @foreach ($grouped['EQUITY']['items'] as $item)
                        <tr>
                          <td class="text-center">{{ $item->coa_kode }}</td>
                          <td class="pl-4">{{ $item->coa_nama }}</td>
                          <td class="text-right {{ $item->saldo < 0 ? 'text-danger' : '' }}">{{ number_format($item->saldo, 2, ',', '.') }}</td>
                        </tr>
                      @endforeach
                      <tr class="bg-light">
                        <td colspan="2" class="text-right"><strong>TOTAL EKUITAS</strong></td>
                        <td class="text-right"><strong>{{ number_format($totalEkuitas, 2, ',', '.') }}</strong></td>
                      </tr>
                    @endif

                    @unless ($adaData)
                      <tr>
                        <td colspan="3" class="text-center text-muted">Tidak ada data pada periode ini.</td>
                      </tr>
                    @endunless
                  </tbody>
                  <tfoot>
                    <tr class="bg-light">
                      <th colspan="2" class="text-right">TOTAL LIABILITAS + EKUITAS</th>
                      <th class="text-right">{{ number_format($totalPasiva, 2, ',', '.') }}</th>
                    </tr>
                    <tr class="{{ $isBalance ? 'bg-primary' : 'bg-danger' }} text-white">
                      <th colspan="2" class="text-right">
                        BALANCE SHEET
                        <small class="ml-2">
                          @if ($isBalance)
                            <i class="bi bi-check-circle"></i> Seimbang (Aset = Liabilitas + Ekuitas)
                          @else
                            <i class="bi bi-exclamation-triangle"></i> Tidak seimbang (selisih {{ number_format($totalAset - $totalPasiva, 2, ',', '.') }})
                          @endif
                        </small>
                      </th>
                      <th class="text-right">{{ number_format($totalAset, 2, ',', '.') }}</th>
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

@endsection

@section('scripts')
<script></script>
@endsection
