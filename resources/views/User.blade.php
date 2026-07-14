@extends('layout.template')

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>DATA MASTER USER</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item">Master</li>
              <li class="breadcrumb-item active">User</li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        @if ($errors->any())
          <div class="alert alert-danger">
            <ul class="mb-0">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header p-2">
                <ul class="nav nav-pills">
                  <li class="nav-item"><a class="nav-link active" href="#activity" data-toggle="tab">Data Master User</a></li>
                  <li class="nav-item"><a class="nav-link" href="#settings" data-toggle="tab"><i class="bi bi-plus"></i> Input Data</a></li>
                </ul>
              </div>
              <div class="card-body">
                <div class="tab-content">
                  <div class="active tab-pane" id="activity">
                    <table id="example2" class="table table-bordered table-hover">
                      <thead>
                      <tr>
                          <th class="text-center">KODE USER</th>
                          <th class="text-center">USERNAME</th>
                          <th class="text-center">GROUP</th>
                          <th class="text-center">KETERANGAN</th>
                          <th class="text-center">STATUS</th>
                          <th class="text-center">#</th>
                      </tr>
                      </thead>
                      <tbody></tbody>
                    </table>

                    <!-- Modal Edit -->
                    <div class="modal fade" id="modalEdit" tabindex="-1" role="dialog" aria-hidden="true">
                      <div class="modal-dialog" role="document">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title">Form Edit User</h5>
                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                          </div>
                          <form id="frm-edit" method="POST" action="{{ route('edit.master.user') }}">
                            @csrf
                            <div class="modal-body">
                              <div class="form-group">
                                <label>Kode User</label>
                                <input name="edit_kd_user" class="form-control" id="edit_kd_user" readonly>
                              </div>
                              <div class="form-group">
                                <label>Username</label>
                                <input name="edit_username" type="text" class="form-control" id="edit_username" required>
                              </div>
                              <div class="form-group">
                                <label>Password Baru
                                  <small class="text-muted">(kosongkan jika tidak ingin mengubah)</small>
                                </label>
                                <input name="edit_password" type="password" class="form-control" id="edit_password" autocomplete="new-password">
                              </div>
                              <div class="form-group">
                                <label>Group</label>
                                <select name="edit_kd_group" class="form-control" id="edit_kd_group" required>
                                  @foreach ($groups as $g)
                                    <option value="{{ $g->kd_group }}">{{ $g->kd_group }} - {{ $g->nama }}</option>
                                  @endforeach
                                </select>
                              </div>
                              <div class="form-group">
                                <label>Keterangan</label>
                                <input name="edit_keterangan" type="text" class="form-control" id="edit_keterangan">
                              </div>
                              <div class="form-group">
                                <label>Status</label>
                                <select name="edit_status" class="form-control" id="edit_status" required>
                                  <option value="1">Aktif</option>
                                  <option value="0">Tidak Aktif</option>
                                </select>
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

                    <!-- Modal Hapus -->
                    <div class="modal fade" id="modalHapus" tabindex="-1" role="dialog" aria-hidden="true">
                      <div class="modal-dialog" role="document">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title">Hapus User</h5>
                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                          </div>
                          <form method="POST" action="{{ route('hapus.master.user') }}">
                            @csrf
                            <div class="modal-body">
                              <p>Apakah anda yakin ingin menghapus user <strong id="hapus_username"></strong>?</p>
                              <input name="hapus_kd_user" type="hidden" id="hapus_kd_user">
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
                    <form id="frm-input" method="POST" action="{{ route('input.master.user') }}" autocomplete="off">
                      @csrf
                      <?php date_default_timezone_set('Asia/Jakarta'); ?>
                      <div class="col-sm-7"><h3 class="text-right">{{ date('d/m/Y') }}</h3></div>

                      <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Kode User</label>
                        <div class="col-sm-5">
                          <input type="text" name="kd_user" class="form-control" value="{{ old('kd_user', $kd_user) }}" readonly>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Username</label>
                        <div class="col-sm-5">
                          <input type="text" name="username" class="form-control" value="{{ old('username') }}" required maxlength="50">
                        </div>
                      </div>
                      <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Password</label>
                        <div class="col-sm-5">
                          <input type="password" name="password" class="form-control" required minlength="6" autocomplete="new-password">
                          <small class="text-muted">Minimal 6 karakter</small>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Group</label>
                        <div class="col-sm-5">
                          <select name="kd_group" class="form-control" required>
                            <option value="">-- Pilih Group --</option>
                            @foreach ($groups as $g)
                              <option value="{{ $g->kd_group }}" {{ old('kd_group') == $g->kd_group ? 'selected' : '' }}>
                                {{ $g->kd_group }} - {{ $g->nama }}
                              </option>
                            @endforeach
                          </select>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Keterangan</label>
                        <div class="col-sm-5">
                          <input type="text" name="keterangan" class="form-control" value="{{ old('keterangan') }}">
                        </div>
                      </div>
                      <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Status</label>
                        <div class="col-sm-5">
                          <select name="status" class="form-control" required>
                            <option value="1">Aktif</option>
                            <option value="0">Tidak Aktif</option>
                          </select>
                        </div>
                      </div>

                      <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Simpan</button>
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
    ajax: { url: "{{ route('data.master.user') }}", type: "GET" },
    columns: [
      { data: 'kd_user', className: 'text-center' },
      { data: 'username', className: 'text-center' },
      { data: 'group_nama', className: 'text-center' },
      { data: 'keterangan', className: 'text-center' },
      { data: 'status', className: 'text-center', render: function (d) { return d == 1 ? 'Aktif' : 'Tidak Aktif'; } },
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
    let row = getRowData(this);
    $('#edit_kd_user').val(row.kd_user);
    $('#edit_username').val(row.username);
    $('#edit_kd_group').val(row.kd_group);
    $('#edit_keterangan').val(row.keterangan);
    $('#edit_status').val(row.status);
    $('#edit_password').val('');
  });

  $('#example2 tbody').on('click', '.hapus-data', function () {
    let row = getRowData(this);
    $('#hapus_kd_user').val(row.kd_user);
    $('#hapus_username').text(row.username);
  });
</script>
@endsection
