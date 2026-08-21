@extends('layout.template')

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
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
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">

          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Data Stok Barang</h3>
              </div><!-- /.card-header -->
              <div class="card-body">
                <table id="table-laporan-stok" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                      <th class="text-center">Kode</th>
                      <th class="text-center">Item</th>
                      <th class="text-center">Stok Akhir</th>
                      <th class="text-center">Satuan Terkecil</th>
                      <th class="text-center">Harga Beli Terakhir</th>
                  </tr>
                  </thead>
                  <tbody>
                  </tbody>
                </table>
              </div><!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>

@endsection

@section('scripts')
<!-- DataTables  & Plugins -->
<script src="{{ asset('lte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('lte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('lte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('lte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

<!-- SCRIPT UNTUK TABEL DATA -->
<script>
  $('#table-laporan-stok').DataTable({
    processing: true,
    serverSide: true,
    paging: true,
    lengthChange: true,
    searching: true,
    ordering: true,
    info: true,
    autoWidth: false,
    responsive: true,
    order: [[1, 'asc']],
    ajax: {
      url: "{{ route('data.laporan.stok') }}",
      type: "GET"
    },
    columns: [
      { data: 'kd_barang', className: 'text-center' },
      { data: 'nama', className: 'text-center' },
      { data: 'stok', className: 'text-center' },
      { data: 'satuan_terkecil', className: 'text-center' },
      { data: 'harga_beli', className: 'text-right', render: function(data) { const val = parseFloat(String(data).replace(',', '.')) || 0; return 'Rp ' + val.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }); } }
    ]
  });
</script>
@endsection
