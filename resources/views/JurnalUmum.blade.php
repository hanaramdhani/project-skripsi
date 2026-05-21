@extends('layout.template')

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>JURNAL UMUM</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item">Laporan Keuangan</li>
              <li class="breadcrumb-item active">Jurnal Umum</li>
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
                <form class="form-inline" method="GET" action="{{ url('jurnal-umum') }}">
                  <div class="form-group mr-2 mb-2">
                    <label class="mr-2">Tanggal Awal</label>
                    <input type="date" name="tgl_awal" class="form-control" value="{{ $tgl_awal }}" required>
                  </div>
                  <div class="form-group mr-2 mb-2">
                    <label class="mr-2">Tanggal Akhir</label>
                    <input type="date" name="tgl_akhir" class="form-control" value="{{ $tgl_akhir }}" required>
                  </div>
                  <button type="submit" class="btn btn-primary mb-2 mr-2"><i class="bi bi-search"></i> Tampilkan</button>
                  <a href="{{ url('jurnal-umum') }}" class="btn btn-default mb-2"><i class="bi bi-arrow-counterclockwise"></i> Reset</a>
                </form>
              </div>
            </div>

            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
                  Data Jurnal Umum
                  <small class="text-muted ml-2">
                    Periode: {{ \Carbon\Carbon::parse($tgl_awal)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($tgl_akhir)->format('d/m/Y') }}
                  </small>
                </h3>
              </div>
              <div class="card-body table-responsive">
                <table id="tbl-jurnal-umum" class="table table-bordered table-hover table-striped">
                  <thead class="bg-light">
                    <tr>
                      <th class="text-center" style="width: 50px;">NO</th>
                      <th class="text-center">TANGGAL</th>
                      <th class="text-center">NO. BUKTI</th>
                      <th class="text-center">KODE COA</th>
                      <th class="text-center">SUMBER</th>
                      <th class="text-center">DEBIT</th>
                      <th class="text-center">KREDIT</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse ($data as $key => $row)
                      <tr>
                        <td class="text-center">{{ $key + 1 }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($row->tgl_jurnal)->format('d/m/Y') }}</td>
                        <td class="text-center">{{ $row->no_bukti }}</td>
                        <td class="text-center">{{ $row->coa_kode }}</td>
                        <td>{{ $row->sumber }}</td>
                        <td class="text-right">{{ number_format($row->debit, 2, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($row->kredit, 2, ',', '.') }}</td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="7" class="text-center text-muted">Tidak ada data pada periode ini.</td>
                      </tr>
                    @endforelse
                  </tbody>
                  <tfoot class="bg-light">
                    <tr>
                      <th colspan="5" class="text-right">TOTAL</th>
                      <th class="text-right">{{ number_format($total_debit, 2, ',', '.') }}</th>
                      <th class="text-right">{{ number_format($total_kredit, 2, ',', '.') }}</th>
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
<script src="{{ asset('lte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('lte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('lte/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('lte/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
<script src="{{ asset('lte/plugins/jszip/jszip.min.js') }}"></script>
<script src="{{ asset('lte/plugins/pdfmake/pdfmake.min.js') }}"></script>
<script src="{{ asset('lte/plugins/pdfmake/vfs_fonts.js') }}"></script>
<script src="{{ asset('lte/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
<script src="{{ asset('lte/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
<script>
  $(function () {
    $('#tbl-jurnal-umum').DataTable({
      paging: true,
      lengthChange: true,
      searching: true,
      ordering: true,
      info: true,
      autoWidth: false,
      responsive: true,
      dom: 'Bfrtip',
      buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
    });
  });
</script>
@endsection
