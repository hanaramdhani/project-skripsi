@extends('layout.template')

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>DATA PENDAPATAN</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item">Transaksi</li>
              <li class="breadcrumb-item active">Pendapatan</li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        @include('partials.flash')
        @if ($errors->any())
          <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif
        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header p-2">
                <ul class="nav nav-pills">
                  <li class="nav-item"><a class="nav-link active" href="#activity" data-toggle="tab">Data Pendapatan</a></li>
                  <li class="nav-item"><a class="nav-link" href="#settings" data-toggle="tab"><i class="bi bi-plus"></i> Input Pendapatan</a></li>
                </ul>
              </div>
              <div class="card-body">
                <div class="tab-content">
                  <div class="active tab-pane" id="activity">
                    <table id="example2" class="table table-bordered table-hover" style="width:100%">
                      <thead>
                      <tr>
                          <th class="text-center">TANGGAL</th>
                          <th class="text-center">NO TRANSAKSI</th>
                          <th class="text-center">PENDAPATAN</th>
                          <th class="text-center">NOMINAL</th>
                          <th class="text-center">#</th>
                      </tr>
                      </thead>
                      <tbody></tbody>
                    </table>

                    <div class="modal fade" id="modalEdit" tabindex="-1" role="dialog" aria-hidden="true">
                      <div class="modal-dialog" role="document">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title">Form Edit Pendapatan</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                          </div>
                          <form class="form-horizontal" id="frm-edit" method="POST" action="{{ route('edit.trans.pendapatan') }}">
                            @csrf
                            <div class="modal-body">
                              <div class="form-group">
                                <label class="col-form-label">No Transaksi</label>
                                <input name="edit_no_transaksi" class="form-control" id="edit_no_transaksi" readonly>
                              </div>
                              <div class="form-group">
                                <label class="col-form-label">Tanggal</label>
                                <input name="edit_tanggal" type="date" class="form-control" id="edit_tanggal" required>
                              </div>
                              <div class="form-group">
                                <label class="col-form-label">Pendapatan</label>
                                <select name="edit_kd_pendapatan" class="form-control" id="edit_kd_pendapatan" required>
                                  @foreach ($pendapatan as $r)<option value="{{ $r->kd_pendapatan }}">{{ $r->nama }}</option>@endforeach
                                </select>
                              </div>
                              <div class="form-group">
                                <label class="col-form-label">Nominal</label>
                                <input name="edit_nominal" type="number" step="0.01" min="0" class="form-control" id="edit_nominal" required>
                              </div>
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-dismiss="modal">Kembali</button>
                              <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>

                    <div class="modal fade" id="modalHapus" tabindex="-1" role="dialog" aria-hidden="true">
                      <div class="modal-dialog" role="document">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title">Hapus Item</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                          </div>
                          <form class="form-horizontal" id="frm-hapus" method="POST" action="{{ route('hapus.trans.pendapatan') }}">
                            @csrf
                            <div class="modal-body">
                              <h4>Apakah anda yakin ingin menghapus item ini ?</h4>
                              <input name="hapus_no_transaksi" type="hidden" id="hapus_no_transaksi">
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-dismiss="modal">Kembali</button>
                              <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i> Hapus</button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="tab-pane" id="settings">
                    <form class="form-horizontal" id="frm-input" method="POST" action="{{ route('input.trans.pendapatan') }}">
                      @csrf
                      <div class="form-group row">
                        <label class="col-sm-2 col-form-label">No Transaksi</label>
                        <div class="col-sm-6"><input class="form-control" value="{{ $no_transaksi }}" readonly></div>
                      </div>
                      <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Tanggal</label>
                        <div class="col-sm-6"><input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required></div>
                      </div>
                      <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Pendapatan</label>
                        <div class="col-sm-6">
                          <select name="kd_pendapatan" class="form-control" required>
                            @foreach ($pendapatan as $r)<option value="{{ $r->kd_pendapatan }}">{{ $r->nama }}</option>@endforeach
                          </select>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Nominal</label>
                        <div class="col-sm-6"><input type="number" step="0.01" min="0" name="nominal" class="form-control" value="0" required></div>
                      </div>
                      <div class="form-group row">
                        <div class="offset-sm-2 col-sm-6">
                          <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Simpan</button>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
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
<script src="{{ asset('lte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('lte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<script>
  const table = $('#example2').DataTable({
    processing: true,
    serverSide: true,
    paging: true,
    lengthChange: true,
    searching: true,
    ordering: true,
    info: true,
    autoWidth: false,
    responsive: true,
    order: [[0, 'desc']],
    ajax: { url: "{{ route('data.trans.pendapatan') }}", type: "GET" },
    columns: [
      { data: 'tanggal_tampil', className: 'text-center' },
      { data: 'no_transaksi', className: 'text-center' },
      { data: 'pendapatan', className: 'text-center' },
      { data: 'nominal', className: 'text-right',
        render: function (d) {
          return d == null ? '' : Number(d).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        } },
      { data: null, className: 'text-center', orderable: false, searchable: false,
        render: function () {
          return '<button type="button" class="btn btn-xs btn-warning edit-data" data-toggle="modal" data-target="#modalEdit"><i class="bi bi-pencil"></i> Edit</button> ' +
                 '<button type="button" class="btn btn-xs btn-danger hapus-data" data-toggle="modal" data-target="#modalHapus"><i class="bi bi-trash"></i> Hapus</button>';
        } }
    ]
  });

  function getRowData(el) {
    let tr = $(el).closest('tr');
    if (tr.hasClass('child')) { tr = tr.prev(); }
    return table.row(tr).data();
  }

  $('#example2 tbody').on('click', '.edit-data', function () {
    const row = getRowData(this);
    $('#edit_no_transaksi').val(row.no_transaksi);
    $('#edit_tanggal').val(row.tanggal_iso);
    $('#edit_kd_pendapatan').val(row.kd_pendapatan);
    $('#edit_nominal').val(row.nominal);
  });

  $('#example2 tbody').on('click', '.hapus-data', function () {
    $('#hapus_no_transaksi').val(getRowData(this).no_transaksi);
  });
</script>
@endsection
