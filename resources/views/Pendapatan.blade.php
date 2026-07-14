@extends('layout.template')

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>DATA MASTER PENDAPATAN</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">User Profile</li>
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
              <div class="card-header p-2">
                <ul class="nav nav-pills">
                  <li class="nav-item"><a class="nav-link active" href="#activity" data-toggle="tab">Data Master Pendapatan</a></li>
                  <li class="nav-item"><a class="nav-link" href="#settings" data-toggle="tab"><i class="bi bi-plus"></i>Input Data</a></li>
                </ul>
              </div><!-- /.card-header -->
              <div class="card-body">
                <div class="tab-content">
                  <div class="active tab-pane" id="activity">
                    <table id="example2" class="table table-bordered table-hover">
                      <thead>
                      <tr>
                          <th class="text-center">KODE PENDAPATAN</th>
                          <th class="text-center">NAMA</th>
                          <th class="text-center">STATUS</th>
                          <th class="text-center">KETERANGAN</th>
                          <th class="text-center">AKUN</th>
                          <th class="text-center">#</th>
                      </tr>
                      </thead>
                      <tbody>
                      </tbody>
                      <tfoot>
                      </tfoot>
                    </table>


                    <div class="modal fade" id="modalEdit" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                      <div class="modal-dialog" role="document">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Form Edit Akun</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                          </div>
                          <div class="modal-body">
                            <form class="form-horizontal" id="frm-edit" name="frm-edit" method="POST" action="{{ route('edit.master.pendapatan') }}">
                              @csrf
                              <div class="form-group">
                                <label for="recipient-name" class="col-form-label">Kode Pendapatan</label>
                                <input name="edit_kd_pendapatan" class="form-control" id="edit_kd_pendapatan" readonly>
                              </div>
                              <div class="form-group">
                                <label for="recipient-name" class="col-form-label">Nama Pendapatan</label>
                                <input name="edit_nama_pendapatan" type="text" class="form-control" id="edit_nama_pendapatan" required>
                              </div>
                              <div class="form-group">
                                <label for="recipient-name" class="col-form-label">Keterangan</label>
                                <input name="edit_keterangan_pendapatan" type="text" class="form-control" id="edit_keterangan_pendapatan" required>
                              </div>
                              <div class="form-group">
                                <label for="recipient-name" class="col-form-label">Status</label>
                                <select name="edit_status_pendapatan" class="form-control" id="edit_status_pendapatan" required>
                                    <option value="1">Aktif</option>
                                    <option value="0">Tidak Aktif</option>
                                </select>
                              </div>
                              <div class="form-group">
                                <label for="recipient-name" class="col-form-label">Akun</label>
                                <select class="form-control" name="edit_kd_akun" id="edit_kd_akun" required>
                                </select>
                                <!-- <input type="text" name="edit_kdjabatan_pegawai" id="edit_kdJabatan_pegawai"> -->
                              </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Kembali</button>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i>Simpan</button>
                          </div>
                          </form>
                        </div>
                      </div>
                    </div>
                    <div class="modal fade" id="modalHapus" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                      <div class="modal-dialog" role="document">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Hapus Item</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                          </div>
                          <div class="modal-body">
                            <h4>Apakah anda yakin ingin menghapus item ini ?</h4>
                            <form class="form-horizontal" id="frm-edit" name="frm-edit" method="POST" action="{{ route('hapus.master.pendapatan') }}">
                              @csrf
                                <input name="hapus_kd_pendapatan" type="hidden" class="form-control" id="hapus_kd_pendapatan" readonly>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Kembali</button>
                            <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i>Hapus</button>
                          </div>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="tab-pane" id="settings">                  
                    <form class="form-horizontal" id="frm-input" name="frm_input" method="POST" action="{{ route('input.master.pendapatan') }}">
                    <?php
                        date_default_timezone_set('Asia/Jakarta');
                        echo '<div class="col-sm-7"><h3 class="text-right">'.date('d/m/Y').'</h3></div>';
                    ?>
                      @csrf
                      <div class="form-group row">
                        <label for="inputName2" class="col-sm-2 col-form-label">Kode Pendapatan</label>
                        <div class="col-sm-5">
                          <input type="text" name="kd_pendapatan" id="kd_pendapatan" class="form-control" value="{{ old('kd_pendapatan', $kd_pendapatan) }}" readonly>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="inputName2" class="col-sm-2 col-form-label">Nama Pendapatan</label>
                        <div class="col-sm-5">
                          <input type="text" name="nama" id="nama" class="form-control" value="" required>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="inputName2" class="col-sm-2 col-form-label">Keterangan</label>
                        <div class="col-sm-5">
                          <input type="text" name="keterangan" id="keterangan" class="form-control" value="" required>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="inputName2" class="col-sm-2 col-form-label">Status</label>
                        <div class="col-sm-5">
                            <select name="status" class="form-control" required>
                                <option value="1">Aktif</option>
                                <option value="0">Tidak Aktif</option>
                            </select>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="inputName2" class="col-sm-2 col-form-label">Akun</label>
                        <div class="col-sm-5">
                          <select class="form-control" name="kd_akun" id="kd_akun">
                          <?php
                            foreach ($akun as $key => $value) {
                              echo '<option value="'.$value->kd_akun.'">'.$value->akun.'</option>';
                            };
                          ?>
                          </select>
                        </div>
                      </div>

                       <button type="submit" class="btn btn-success text-right"><i class="bi bi-save"></i>Simpan</button>
                    </form>

                  </div>
                  <!-- /.tab-pane -->
                </div>
                <!-- /.tab-content -->
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
<script src="{{ asset('lte/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('lte/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
<script src="{{ asset('lte/plugins/jszip/jszip.min.js') }}"></script>
<script src="{{ asset('lte/plugins/pdfmake/pdfmake.min.js') }}"></script>
<script src="{{ asset('lte/plugins/pdfmake/vfs_fonts.js') }}"></script>
<script src="{{ asset('lte/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
<script src="{{ asset('lte/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
<script src="{{ asset('lte/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>
<!-- Page specific script -->


<!-- SCRIPT UNTUK TABEL DATA -->
<script>
  const table = $('#example2').DataTable({
    processing: true, serverSide: true, paging: true, lengthChange: true,
    searching: true, ordering: true, info: true, autoWidth: false, responsive: true,
    ajax: { url: "{{ route('data.master.pendapatan') }}", type: "GET" },
    columns: [
      { data: 'kd_pendapatan', className: 'text-center' },
      { data: 'pendapatan', className: 'text-center' },
      { data: 'status', className: 'text-center', render: function (d) { return d == 1 ? 'Aktif' : 'Tidak Aktif'; } },
      { data: 'keterangan', className: 'text-center' },
      { data: 'akun', className: 'text-center' },
      { data: null, className: 'text-center', orderable: false, searchable: false,
        render: function () {
          return '<button type="button" class="btn btn-xs btn-warning edit-data" data-toggle="modal" data-target="#modalEdit"><i class="bi bi-pencil"></i>Edit</button> ' +
                 '<button type="button" class="btn btn-xs btn-danger hapus-data" data-toggle="modal" data-target="#modalHapus"><i class="bi bi-trash"></i>Hapus</button>';
        } }
    ]
  });

  function getRowData(el) {
    let tr = $(el).closest('tr');
    if (tr.hasClass('child')) { tr = tr.prev(); }
    return table.row(tr).data();
  }

  $('#example2 tbody').on('click', '.edit-data', function () {
        let row = getRowData(this);
        let kd_akun = row.kd_akun;
        $('#edit_kd_pendapatan').val(row.kd_pendapatan);
        $('#edit_nama_pendapatan').val(row.pendapatan);
        $('#edit_status_pendapatan').val(row.status);
        $('#edit_keterangan_pendapatan').val(row.keterangan);

        $('#edit_kd_akun').empty();
        $.ajax({
            url: '/get-akun-pendapatan',
            type: 'GET',
            success: function (response) {
                response.akun.forEach(function (item) {
                    let selected = item.kd_akun == kd_akun ? 'selected' : '';
                    $('#edit_kd_akun').append(
                        `<option value="${item.kd_akun}" ${selected}>${item.nama}</option>`
                    );
                });
            }
        });
  });

  $('#example2 tbody').on('click', '.hapus-data', function () {
        let row = getRowData(this);
        $('#hapus_kd_pendapatan').val(row.kd_pendapatan);
  });
 
</script>







@endsection