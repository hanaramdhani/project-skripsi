@extends('layout.template')

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>DATA MASTER BIAYA</h1>
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
                  <li class="nav-item"><a class="nav-link active" href="#activity" data-toggle="tab">Data Master Biaya</a></li>
                  <li class="nav-item"><a class="nav-link" href="#settings" data-toggle="tab"><i class="bi bi-plus"></i>Input Data</a></li>
                </ul>
              </div><!-- /.card-header -->
              <div class="card-body">
                <div class="tab-content">
                  <div class="active tab-pane" id="activity">
                    <table id="example2" class="table table-bordered table-hover">
                      <thead>
                      <tr>
                          <th class="text-center">KODE BIAYA</th>
                          <th class="text-center">NAMA</th>
                          <th class="text-center">STATUS</th>
                          <th class="text-center">KETERANGAN</th>
                          <th class="text-center">AKUN</th>
                          <th class="text-center">#</th>
                      </tr>
                      </thead>
                      <tbody>
                      <?php foreach ($data as $key => $value): ?>
                          <tr class="data-row">
                            <td class="text-center"><?= $value->kd_biaya ?></td>
                            <td class="text-center"><?= $value->biaya ?></td>
                            <td class="text-center"><?= $value->status == 1 ? 'Aktif' : 'Tidak Aktif' ?></td>
                            <td class="text-center"><?= $value->keterangan ?></td>
                            <td class="text-center"><?= $value->akun ?></td>
                            <td class="text-center">
                              <button 
                                    type="button" 
                                    class="btn btn-xs btn-warning edit-data"
                                    data-toggle="modal" 
                                    data-target="#modalEdit"
                                    data-kd-biaya="<?= $value->kd_biaya ?>"
                                    data-nama-biaya="<?= $value->biaya ?>"
                                    data-status-biaya="<?= $value->status ?>"
                                    data-keterangan-biaya="<?= $value->keterangan ?>"
                                    data-kd-akun="<?= $value->kd_akun ?>"
                                    data-akun="<?= $value->akun ?>"
                              ><i class="bi bi-pencil"></i>Edit</button>
                              </button>
                              <button 
                                    type="button" 
                                    class="btn btn-xs btn-danger hapus-data"
                                    data-toggle="modal" 
                                    data-target="#modalHapus"
                                    data-kd-biaya="<?= $value->kd_biaya ?>"
                              ><i class="bi bi-trash"></i>Hapus</button>
                              </button>
                            </td>
                          </tr>
                        <?php endforeach; ?>
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
                            <form class="form-horizontal" id="frm-edit" name="frm-edit" method="POST" action="{{ route('edit.master.biaya') }}">
                              @csrf
                              <div class="form-group">
                                <label for="recipient-name" class="col-form-label">Kode Biaya</label>
                                <input name="edit_kd_biaya" class="form-control" id="edit_kd_biaya" readonly>
                              </div>
                              <div class="form-group">
                                <label for="recipient-name" class="col-form-label">Nama Biaya</label>
                                <input name="edit_nama_biaya" type="text" class="form-control" id="edit_nama_biaya" required>
                              </div>
                              <div class="form-group">
                                <label for="recipient-name" class="col-form-label">Keterangan</label>
                                <input name="edit_keterangan_biaya" type="text" class="form-control" id="edit_keterangan_biaya" required>
                              </div>
                              <div class="form-group">
                                <label for="recipient-name" class="col-form-label">Status</label>
                                <select name="edit_status_biaya" class="form-control" id="edit_status_biaya" required>
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
                            <form class="form-horizontal" id="frm-edit" name="frm-edit" method="POST" action="{{ route('hapus.master.biaya') }}">
                              @csrf
                                <input name="hapus_kd_biaya" type="hidden" class="form-control" id="hapus_kd_biaya" readonly>
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
                    <form class="form-horizontal" id="frm-input" name="frm_input" method="POST" action="{{ route('input.master.biaya') }}">
                    <?php
                        date_default_timezone_set('Asia/Jakarta');
                        echo '<div class="col-sm-7"><h3 class="text-right">'.date('d/m/Y').'</h3></div>';
                    ?>
                      @csrf
                      <div class="form-group row">
                        <label for="inputName2" class="col-sm-2 col-form-label">Kode Biaya</label>
                        <div class="col-sm-5">
                          <input type="text" name="kd_biaya" id="kd_biaya" class="form-control" value="{{ old('kd_biaya', $kd_biaya) }}" readonly>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="inputName2" class="col-sm-2 col-form-label">Nama Biaya</label>
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

  <!-- jQuery -->
  
<script src="{{ asset('lte/plugins/jquery/jquery.min.js') }}"></script>
<!-- Bootstrap 4 -->
<script src="{{ asset('lte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
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
<!-- AdminLTE App -->
<script src="{{ asset('lte/dist/js/adminlte.min.js') }}"></script>
<!-- AdminLTE for demo purposes -->
<script src="{{ asset('lte/dist/js/demo.js') }}"></script>
<!-- Page specific script -->


<!-- SCRIPT UNTUK TABEL DATA -->
<script>
  const table = $('#example2').DataTable({
    paging: true,
    lengthChange: true,
    searching: true,
    ordering: false,
    info: true,
    autoWidth: false,
    responsive: true
  });


    
  $('#example2 tbody').on('click', '.edit-data', function () {
        let kd_biaya = $(this).data('kd-biaya');
        let nama_biaya = $(this).data('nama-biaya');
        let status = $(this).data('status-biaya');
        let keterangan = $(this).data('keterangan-biaya');
        let kd_akun = $(this).data('kd-akun');
        $('#edit_kd_biaya').val(kd_biaya);
        $('#edit_nama_biaya').val(nama_biaya);
        $('#edit_status_biaya').val(status);
        $('#edit_keterangan_biaya').val(keterangan);
        $('#edit_kd_akun').val(kd_akun)

        $.ajax({
            url: '/get-akun',
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
        let kd_biaya = $(this).data('kd-biaya');
        $('#hapus_kd_biaya').val(kd_biaya);
  });
 
</script>







@endsection