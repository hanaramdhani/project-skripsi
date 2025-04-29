@extends('layout.template')

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>DATA MASTER BARANG SATUAN</h1>
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
                  <li class="nav-item"><a class="nav-link active" href="#activity" data-toggle="tab">Data Master Barang Satuan</a></li>
                  <li class="nav-item"><a class="nav-link" href="#settings" data-toggle="tab"><i class="bi bi-plus"></i>Input Data</a></li>
                </ul>
              </div><!-- /.card-header -->
              <div class="card-body">
                <div class="tab-content">
                  <div class="active tab-pane" id="activity">
                    <table id="example2" class="table table-bordered table-hover">
                      <thead>
                      <tr>
                          <th class="text-center">BARANG</th>
                          <th class="text-center">SATUAN</th>
                          <th class="text-center">HARGA JUAL</th>
                          <th class="text-center">KETERANGAN</th>
                          <th class="text-center">STATUS</th>
                          <th class="text-center">#</th>
                      </tr>
                      </thead>
                      <tbody>
                      <?php foreach ($data as $key => $value): ?>
                          <tr class="data-row">
                            <td class="text-center"><?= $value->barang ?></td>
                            <td class="text-center"><?= $value->satuan ?></td>
                            <td class="text-center"><?= $value->harga_jual ?></td>
                            <td class="text-center"><?= $value->keterangan ?></td>
                            <td class="text-center"><?= $value->status == 1 ? 'Aktif' : 'Tidak Aktif' ?></td>
                            <td class="text-center">
                              <button 
                                    type="button" 
                                    class="btn btn-xs btn-warning edit-data"
                                    data-toggle="modal" 
                                    data-target="#modalEdit"
                                    data-kd-barang="<?= $value->kd_barang ?>"
                                    data-barang="<?= $value->barang ?>"
                                    data-kd-satuan="<?= $value->kd_satuan ?>"
                                    data-satuan="<?= $value->satuan ?>"
                                    data-harga-jual="<?= $value->harga_jual ?>"
                                    data-keterangan="<?= $value->keterangan ?>"
                                    data-status="<?= $value->status ?>"
                              ><i class="bi bi-pencil"></i>Edit</button>
                              </button>
                              <button 
                                    type="button" 
                                    class="btn btn-xs btn-danger hapus-data"
                                    data-toggle="modal" 
                                    data-target="#modalHapus"
                                    data-kd-barang="<?= $value->kd_barang ?>"
                                    data-kd-satuan="<?= $value->kd_satuan ?>"
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
                            <h5 class="modal-title" id="exampleModalLabel">Form Edit Pegawai</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                          </div>
                          <div class="modal-body">
                            <form class="form-horizontal" id="frm-edit" name="frm-edit" method="POST" action="{{ route('edit.master.barang.satuan') }}">
                              @csrf
                              <div class="form-group">
                                <label for="recipient-name" class="col-form-label">Barang</label>
                                <input type="hidden" name="edit_kd_barang" class="form-control" id="edit_kd_barang" readonly>
                                <input name="edit_barang" class="form-control" id="edit_barang" readonly>
                              </div>
                              <div class="form-group">
                                <label for="recipient-name" class="col-form-label">Satuan</label>
                                <input type="hidden" name="edit_kd_satuan" class="form-control" id="edit_kd_satuan" readonly>
                                <input name="edit_satuan" class="form-control" id="edit_satuan" readonly>
                              </div>
                              <div class="form-group">
                                <label for="recipient-name" class="col-form-label">Harga Jual</label>
                                <input name="edit_harga_jual" class="form-control" id="edit_harga_jual" required>
                              </div>
                              <div class="form-group">
                                <label for="recipient-name" class="col-form-label">Keterangan</label>
                                <input name="edit_keterangan" type="text" class="form-control" id="edit_keterangan" required>
                              </div>
                              <div class="form-group">
                                <label for="recipient-name" class="col-form-label">Status</label>
                                <select name="edit_status" class="form-control" id="edit_status" required>
                                    <option value="1">Aktif</option>
                                    <option value="0">Tidak Aktif</option>
                                </select>
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
                            <form class="form-horizontal" id="frm-edit" name="frm-edit" method="POST" action="{{ route('hapus.master.barang.satuan') }}">
                              @csrf
                                <input name="hapus_kd_barang" type="hidden" class="form-control" id="hapus_kd_barang" readonly>
                                <input name="hapus_kd_satuan" type="hidden" class="form-control" id="hapus_kd_satuan" readonly>
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
                    <form class="form-horizontal" id="frm-input" name="frm_input" method="POST" action="{{ route('input.master.barang.satuan') }}">
                    <?php
                        date_default_timezone_set('Asia/Jakarta');
                        echo '<div class="col-sm-7"><h3 class="text-right">'.date('d/m/Y').'</h3></div>';
                    ?>
                      @csrf
                      <div class="form-group row">
                        <label for="inputName2" class="col-sm-2 col-form-label">Barang</label>
                        <div class="col-sm-5">
                          <select class="form-control" name="kd_barang" id="kd_barang">
                          <?php
                            foreach ($barang as $key => $value) {
                              echo '<option value="'.$value->kd_barang.'">'.$value->barang.'</option>';
                            };
                          ?>
                          </select>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="inputName2" class="col-sm-2 col-form-label">Satuan</label>
                        <div class="col-sm-5">
                          <select class="form-control" name="kd_satuan" id="kd_satuan">
                          <?php
                            foreach ($satuan as $key => $value) {
                              echo '<option value="'.$value->kd_satuan.'">'.$value->satuan.'</option>';
                            };
                          ?>
                          </select>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="inputName2" class="col-sm-2 col-form-label">Harga Jual</label>
                        <div class="col-sm-5">
                          <input type="text" name="harga_jual" id="harga_jual" class="form-control" value="" required>
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
        let kd_barang = $(this).data('kd-barang');
        let barang = $(this).data('barang');
        let kd_satuan = $(this).data('kd-satuan');
        let satuan = $(this).data('satuan');
        let harga_jual = $(this).data('harga-jual');
        let keterangan = $(this).data('keterangan');
        let status = $(this).data('status');
        $('#edit_kd_barang').val(kd_barang);
        $('#edit_barang').val(barang);
        $('#edit_kd_satuan').val(kd_satuan);
        $('#edit_satuan').val(satuan);
        $('#edit_harga_jual').val(harga_jual);
        $('#edit_keterangan').val(keterangan);
        $('#edit_status').val(status);        
  });

  $('#example2 tbody').on('click', '.hapus-data', function () {
        let kd_barang = $(this).data('kd-barang');
        let kd_satuan = $(this).data('kd-satuan');
        $('#hapus_kd_barang').val(kd_barang);
        $('#hapus_kd_satuan').val(kd_satuan);
  });
 
</script>







@endsection