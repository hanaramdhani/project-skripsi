@extends('layout.template')

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>LAPORAN ARUS KAS</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item">Laporan Keuangan</li>
              <li class="breadcrumb-item active">Laporan Arus Kas</li>
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
                <form class="form-inline" method="GET" action="{{ url('laporan-arus-kas') }}">
                  <div class="form-group mr-2 mb-2">
                    <label class="mr-2">Tanggal Awal</label>
                    <input type="date" name="tgl_awal" class="form-control" value="{{ $tgl_awal }}" required>
                  </div>
                  <div class="form-group mr-2 mb-2">
                    <label class="mr-2">Tanggal Akhir</label>
                    <input type="date" name="tgl_akhir" class="form-control" value="{{ $tgl_akhir }}" required>
                  </div>
                  <button type="submit" class="btn btn-primary mb-2 mr-2"><i class="bi bi-search"></i> Tampilkan</button>
                  <a href="{{ url('laporan-arus-kas') }}" class="btn btn-default mb-2"><i class="bi bi-arrow-counterclockwise"></i> Reset</a>
                </form>
              </div>
            </div>

            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
                  Laporan Arus Kas
                  <small class="text-muted ml-2">
                    Periode: {{ \Carbon\Carbon::parse($tgl_awal)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($tgl_akhir)->format('d/m/Y') }}
                  </small>
                </h3>
                <div class="card-tools">
                  <button type="button" class="btn btn-sm btn-default" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
                </div>
              </div>
              <div class="card-body table-responsive">
                <table class="table table-bordered">
                  <thead class="bg-light">
                    <tr>
                      <th class="text-center">AKTIVITAS</th>
                      <th class="text-center" style="width: 220px;">TOTAL</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse ($grouped as $aktivitas => $group)
                      <tr class="bg-secondary text-white">
                        <td><strong>AKTIVITAS {{ strtoupper($aktivitas) }}</strong></td>
                        <td></td>
                      </tr>
                      @foreach ($group['items'] as $item)
                        <tr>
                          <td class="pl-4">{{ $item->label }}</td>
                          <td class="text-right {{ $item->total < 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format($item->total, 2, ',', '.') }}
                          </td>
                        </tr>
                      @endforeach
                      <tr class="bg-light">
                        <td class="text-right"><em>Kas Bersih dari Aktivitas {{ $aktivitas }}</em></td>
                        <td class="text-right">
                          <strong class="{{ $group['subtotal'] < 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format($group['subtotal'], 2, ',', '.') }}
                          </strong>
                        </td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="2" class="text-center text-muted">Tidak ada data pada periode ini.</td>
                      </tr>
                    @endforelse
                  </tbody>
                  <tfoot>
                    <tr class="{{ $grand_total >= 0 ? 'bg-success' : 'bg-danger' }} text-white">
                      <th class="text-right">
                        {{ $grand_total >= 0 ? 'KENAIKAN KAS BERSIH' : 'PENURUNAN KAS BERSIH' }}
                      </th>
                      <th class="text-right">{{ number_format($grand_total, 2, ',', '.') }}</th>
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
