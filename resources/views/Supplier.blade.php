@extends('layout.template')

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>DATA MASTER SUPPLIER</h1>
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
                  <li class="nav-item"><a class="nav-link active" href="#activity" data-toggle="tab">Data Master Supplier</a></li>
                  <!-- <li class="nav-item"><a class="nav-link" href="#timeline" data-toggle="tab">Pasca Operasi</a></li> -->
                  <li class="nav-item"><a class="nav-link" href="#settings" data-toggle="tab"><i class="bi bi-plus"></i>Input Data</a></li>
                </ul>
              </div><!-- /.card-header -->
              <div class="card-body">
                <div class="tab-content">
                  <div class="active tab-pane" id="activity">
                    <table id="example2" class="table table-bordered table-hover">
                      <thead>
                      <tr>
                          <th class="text-center">KODE</th>
                          <th class="text-center">NAMA</th>
                          <th class="text-center">ALAMAT</th>
                          <th class="text-center">NO. HP</th>
                          <th class="text-center">E-MAIL</th>
                          <th class="text-center">#</th>
                      </tr>
                      </thead>
                      <tbody>
                      <?php foreach ($data as $key => $value): ?>
                          <tr class="data-row">
                            <td class="text-center"><?= $value->kd_supplier ?></td>
                            <td class="text-center"><?= $value->nama ?></td>
                            <td class="text-center"><?= $value->alamat ?></td>
                            <td class="text-center"><?= $value->no_hp ?></td>
                            <td class="text-center"><?= $value->email ?></td>
                            <td class="text-center">
                              <button 
                                    type="button" 
                                    class="btn btn-xs btn-warning edit-data"
                                    data-toggle="modal" 
                                    data-target="#modalEdit"
                                    data-kd-supplier="<?= $value->kd_supplier ?>"
                                    data-nama-supplier="<?= $value->nama ?>"
                                    data-alamat-supplier="<?= $value->alamat ?>"
                                    data-nohp-supplier="<?= $value->no_hp ?>"
                                    data-email-supplier="<?= $value->email ?>"
                              ><i class="bi bi-pencil"></i>Edit</button>
                              </button>
                              <button 
                                    type="button" 
                                    class="btn btn-xs btn-danger hapus-data"
                                    data-toggle="modal" 
                                    data-target="#modalHapus"
                                    data-kd-supplier="<?= $value->kd_supplier ?>"
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
                            <h5 class="modal-title" id="exampleModalLabel">Form Edit Supplier</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                          </div>
                          <div class="modal-body">
                            <form class="form-horizontal" id="frm-edit" name="frm-edit" method="POST" action="{{ route('edit.master.supplier') }}">
                              @csrf
                              <div class="form-group">
                                <label for="recipient-name" class="col-form-label">Kode Supplier</label>
                                <input name="edit_kd_supplier" class="form-control" id="edit_kd_supplier" readonly>
                              </div>
                              <div class="form-group">
                                <label for="recipient-name" class="col-form-label">Nama Supplier</label>
                                <input name="edit_nama_supplier" type="text" class="form-control" id="edit_nama_supplier" required>
                              </div>
                              <div class="form-group">
                                <label for="recipient-name" class="col-form-label">Alamat</label>
                                <input name="edit_alamat_supplier" type="text" class="form-control" id="edit_alamat_supplier" required>
                              </div>
                              <div class="form-group">
                                <label for="recipient-name" class="col-form-label">No. HP</label>
                                <input name="edit_nohp_supplier" type="text" class="form-control" id="edit_nohp_supplier" required>
                              </div>
                              <div class="form-group">
                                <label for="recipient-name" class="col-form-label">E-mail</label>
                                <input name="edit_email_supplier" type="text" class="form-control" id="edit_email_supplier" required>
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
                            <form class="form-horizontal" id="frm-edit" name="frm-edit" method="POST" action="{{ route('hapus.master.supplier') }}">
                              @csrf
                                <input name="hapus_kd_supplier" type="hidden" class="form-control" id="hapus_kd_supplier" readonly>
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
                    <form class="form-horizontal" id="frm-input" name="frm_input" method="POST" action="{{ route('input.master.supplier') }}">
                    <?php
                        date_default_timezone_set('Asia/Jakarta');
                        echo '<div class="col-sm-7"><h3 class="text-right">'.date('d/m/Y').'</h3></div>';
                    ?>
                      @csrf
                      <div class="form-group row">
                        <label for="inputName2" class="col-sm-2 col-form-label">Kode</label>
                        <div class="col-sm-5">
                          <input type="text" name="kd_supplier" id="kd_supplier" class="form-control" value="{{ old('kd_supplier', $kd_supplier) }}" readonly>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="inputName2" class="col-sm-2 col-form-label">Nama</label>
                        <div class="col-sm-5">
                          <input type="text" name="nama" id="nama" class="form-control" value="" required>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="inputName2" class="col-sm-2 col-form-label">Alamat</label>
                        <div class="col-sm-5">
                          <input type="text" name="alamat" id="alamat" class="form-control" value="" required>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="inputName2" class="col-sm-2 col-form-label">No. HP</label>
                        <div class="col-sm-5">
                          <input type="text" name="no_hp" id="no_hp" class="form-control" value="" required>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="inputName2" class="col-sm-2 col-form-label">E-mail</label>
                        <div class="col-sm-5">
                          <input type="text" name="email" id="email" class="form-control" value="" required>
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
        let kd_supplier = $(this).data('kd-supplier');
        let nama_supplier = $(this).data('nama-supplier');
        let alamat = $(this).data('alamat-supplier');
        let nohp = $(this).data('nohp-supplier');
        let email = $(this).data('email-supplier');
        $('#edit_kd_supplier').val(kd_supplier);
        $('#edit_nama_supplier').val(nama_supplier);
        $('#edit_alamat_supplier').val(alamat);
        $('#edit_nohp_supplier').val(nohp);
        $('#edit_email_supplier').val(email);
  });

  $('#example2 tbody').on('click', '.hapus-data', function () {
        let kd_supplier = $(this).data('kd-supplier');
        $('#hapus_kd_supplier').val(kd_supplier);
  });
 
</script>







@endsection