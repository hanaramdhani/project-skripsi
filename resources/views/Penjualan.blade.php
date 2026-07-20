@extends('layout.template')

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>DATA TRANSAKSI PENJUALAN</h1>
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
                  <li class="nav-item"><a class="nav-link active" href="#activity" data-toggle="tab">Data Penjualan</a></li>
                  <!-- <li class="nav-item"><a class="nav-link" href="#timeline" data-toggle="tab">Pasca Operasi</a></li> -->
                  <li class="nav-item"><a class="nav-link" href="#settings" data-toggle="tab"><i class="bi bi-plus"></i>Input Data</a></li>
                </ul>
              </div><!-- /.card-header -->
              <div class="card-body">
                <div class="tab-content">
                  <div class="active tab-pane" id="activity">
                    <!-- Filter Tanggal From/To -->
                    <div class="row mb-3">
                      <div class="col-md-3">
                        <label for="filter_from" class="mb-1"><strong>Dari Tanggal</strong></label>
                        <input type="date" class="form-control" id="filter_from" value="{{ $last_sale_date }}">
                      </div>
                      <div class="col-md-3">
                        <label for="filter_to" class="mb-1"><strong>Sampai Tanggal</strong></label>
                        <input type="date" class="form-control" id="filter_to" value="{{ $last_sale_date }}">
                      </div>
                      <div class="col-md-3 d-flex align-items-end">
                        <button type="button" class="btn btn-primary mr-2" id="btn_filter"><i class="bi bi-funnel"></i> Filter</button>
                        <button type="button" class="btn btn-secondary" id="btn_reset_filter"><i class="bi bi-arrow-counterclockwise"></i> Reset</button>
                      </div>
                    </div>

                    <table id="example2" class="table table-bordered table-hover" style="width:100%">
                      <thead>
                      <tr>
                          <th class="text-center">NO. TRANSAKSI</th>
                          <th class="text-center">TANGGAL</th>
                          <th class="text-center">DISKON</th>
                          <th class="text-center">CUSTOMER</th>
                          <th class="text-center">#</th>
                      </tr>
                      </thead>
                      <tbody></tbody>
                      <tfoot>
                      </tfoot>
                    </table>


                    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                      <div class="modal-dialog" role="document">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Form Edit Barang</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                          </div>
                          <div class="modal-body">
                            <form class="form-horizontal" id="frm-edit" name="frm-edit" method="POST" action="{{ route('edit.penjualan') }}">
                              @csrf
                              <div class="form-group">
                                <label for="recipient-name" class="col-form-label">Barang</label>
                                <input type="text" class="form-control" id="dt_barang" readonly>
                              </div>
                              <div class="form-group">
                                <label for="recipient-name" class="col-form-label">Satuan</label>
                                <input type="text" class="form-control" id="dt_satuan" readonly>
                              </div>
                              <div class="form-group">
                                <label for="recipient-name" class="col-form-label">QTY</label>
                                <input name="qty" class="form-control" id="dt_qty">
                              </div>
                              <div class="form-group">
                                <label for="recipient-name" class="col-form-label">Diskon</label>
                                <input name="diskon" type="text" class="form-control" id="dt_diskon">
                              </div>
                              <div class="form-group">
                                <input type="hidden" name="kd_barang" class="form-control" id="dt_kd_barang" readonly>
                                <input type="hidden" name="kd_satuan" class="form-control" id="dt_kd_satuan" readonly>
                                <input type="hidden" name="no_transaksi" class="form-control" id="dt_no_transaksi" readonly>
                              </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                          </div>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!-- /.tab-pane -->
                  <div class="tab-pane" id="timeline">
                    <!-- The timeline -->
                    <div class="timeline timeline-inverse">
                      <!-- timeline time label -->
                      <div class="time-label">
                        <span class="bg-danger">
                          10 Feb. 2014
                        </span>
                      </div>
                      <!-- /.timeline-label -->
                      <!-- timeline item -->
                      <div>
                        <i class="fas fa-envelope bg-primary"></i>

                        <div class="timeline-item">
                          <span class="time"><i class="far fa-clock"></i> 12:05</span>

                          <h3 class="timeline-header"><a href="#">Support Team</a> sent you an email</h3>

                          <div class="timeline-body">
                            Etsy doostang zoodles disqus groupon greplin oooj voxy zoodles,
                            weebly ning heekya handango imeem plugg dopplr jibjab, movity
                            jajah plickers sifteo edmodo ifttt zimbra. Babblely odeo kaboodle
                            quora plaxo ideeli hulu weebly balihoo...
                          </div>
                          <div class="timeline-footer">
                            <a href="#" class="btn btn-primary btn-sm">Read more</a>
                            <a href="#" class="btn btn-danger btn-sm">Delete</a>
                          </div>
                        </div>
                      </div>
                      <!-- END timeline item -->
                      <!-- timeline item -->
                      <div>
                        <i class="fas fa-user bg-info"></i>

                        <div class="timeline-item">
                          <span class="time"><i class="far fa-clock"></i> 5 mins ago</span>

                          <h3 class="timeline-header border-0"><a href="#">Sarah Young</a> accepted your friend request
                          </h3>
                        </div>
                      </div>
                      <!-- END timeline item -->
                      <!-- timeline item -->
                      <div>
                        <i class="fas fa-comments bg-warning"></i>

                        <div class="timeline-item">
                          <span class="time"><i class="far fa-clock"></i> 27 mins ago</span>

                          <h3 class="timeline-header"><a href="#">Jay White</a> commented on your post</h3>

                          <div class="timeline-body">
                            Take me to your leader!
                            Switzerland is small and neutral!
                            We are more like Germany, ambitious and misunderstood!
                          </div>
                          <div class="timeline-footer">
                            <a href="#" class="btn btn-warning btn-flat btn-sm">View comment</a>
                          </div>
                        </div>
                      </div>
                      <!-- END timeline item -->
                      <!-- timeline time label -->
                      <div class="time-label">
                        <span class="bg-success">
                          3 Jan. 2014
                        </span>
                      </div>
                      <!-- /.timeline-label -->
                      <!-- timeline item -->
                      <div>
                        <i class="fas fa-camera bg-purple"></i>

                        <div class="timeline-item">
                          <span class="time"><i class="far fa-clock"></i> 2 days ago</span>

                          <h3 class="timeline-header"><a href="#">Mina Lee</a> uploaded new photos</h3>

                          <div class="timeline-body">
                          </div>
                        </div>
                      </div>
                      <!-- END timeline item -->
                      <div>
                        <i class="far fa-clock bg-gray"></i>
                      </div>
                    </div>
                  </div>
                  <!-- /.tab-pane -->

                  <?php
                        date_default_timezone_set('Asia/Jakarta');
                        $today = date('d/m/Y');
                        // print_r($no_transaksi);
                  ?>
                  <div class="tab-pane" id="settings">
                    <form class="form-horizontal" id="frm-input" name="frm_input" method="POST" action="{{ route('input.penjualan') }}">
                      @csrf

                      <!-- TOP: Tanggal/No.Transaksi/Customer/Pegawai | Pilih Barang | Total -->
                      <div class="row">
                        <div class="col-md-4">
                          <div class="card card-outline">
                            <div class="card-body">
                              <div class="form-group row mb-2">
                                <label class="col-sm-4 col-form-label"><strong>Tanggal</strong></label>
                                <div class="col-sm-8">
                                  <input type="text" class="form-control" value="{{ date('d/m/Y') }}" readonly>
                                </div>
                              </div>
                              <div class="form-group row mb-2">
                                <label class="col-sm-4 col-form-label"><strong>No. Transaksi</strong></label>
                                <div class="col-sm-8">
                                  <input class="form-control" type="text" id="no_transaksi" name="no_transaksi" value="{{ old('no_transaksi', $no_transaksi) }}" readonly>
                                </div>
                              </div>
                              <div class="form-group row mb-2">
                                <label class="col-sm-4 col-form-label"><strong>Customer</strong></label>
                                <div class="col-sm-8">
                                  <select class="form-control" name="kd_customer" id="customer">
                                    <?php
                                      foreach ($customer as $key => $value) {
                                        echo '<option value="'.$value->kd_customer.'">'.$value->customer.'</option>';
                                      };
                                    ?>
                                  </select>
                                </div>
                              </div>
                              <div class="form-group row mb-0">
                                <label class="col-sm-4 col-form-label"><strong>Pegawai</strong></label>
                                <div class="col-sm-8">
                                  <input type="text" name="kd_pegawai" id="kd_pegawai" class="form-control" value="" required>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-4">
                          <div class="card card-outline">
                            <div class="card-body">
                              <div class="form-group row mb-0">
                                <label class="col-sm-3 col-form-label"><strong>Pilih Barang</strong></label>
                                <div class="col-sm-9">
                                  <select class="form-control form-control-lg" id="productSelect"></select>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-4">
                          <div class="card card-outline">
                            <div class="card-body text-right">
                              <p class="mb-1" style="font-size:18px;">Total</p>
                              <h1 class="font-weight-bold mb-0" id="totalDisplay" style="font-size:54px;">0</h1>
                            </div>
                          </div>
                        </div>
                      </div>

                      <!-- PRODUCT TABLE -->
                      <div class="card card-outline">
                        <div class="card-body p-2">
                          <table id="productTable" class="table table-hover mb-0">
                            <thead>
                              <tr>
                                <th class="text-center">Barang</th>
                                <th class="text-center">Satuan</th>
                                <th class="text-center">Harga</th>
                                <th class="text-center">Diskon</th>
                                <th class="text-center">Qty</th>
                                <th class="text-center">Total</th>
                                <th class="text-center" style="width:120px;">#</th>
                              </tr>
                            </thead>
                            <tbody></tbody>
                          </table>
                        </div>
                      </div>

                      <!-- BOTTOM: Total/Diskon/Total Setelah Diskon | Cash/Kembalian | Simpan -->
                      <div class="row">
                        <div class="col-md-4">
                          <div class="card card-outline">
                            <div class="card-body">
                              <div class="form-group row mb-2">
                                <label class="col-sm-5 col-form-label"><strong>Total</strong></label>
                                <div class="col-sm-7">
                                  <input id="totalPenjualan" type="number" class="form-control" style="background-color: #e0e0e0;" readonly>
                                </div>
                              </div>
                              <div class="form-group row mb-2">
                                <label class="col-sm-5 col-form-label"><strong>Diskon</strong></label>
                                <div class="col-sm-7">
                                  <input type="number" name="masterDiskon" id="masterDiskon" class="form-control" value="0" required>
                                </div>
                              </div>
                              <div class="form-group row mb-0">
                                <label class="col-sm-5 col-form-label"><strong>Total Setelah Diskon</strong></label>
                                <div class="col-sm-7">
                                  <input id="totalPenjualanSetelahDiskon" type="number" class="form-control" style="background-color: #e0e0e0;" readonly>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-4">
                          <div class="card card-outline">
                            <div class="card-body">
                              <div class="form-group row mb-2">
                                <label class="col-sm-4 col-form-label"><strong>Cash</strong></label>
                                <div class="col-sm-8">
                                  <input id="cash" type="number" class="form-control" required>
                                </div>
                              </div>
                              <div class="form-group row mb-0">
                                <label class="col-sm-4 col-form-label"><strong>Kembalian</strong></label>
                                <div class="col-sm-8">
                                  <input id="kembalian" type="number" class="form-control" style="background-color: #e0e0e0;" readonly>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-4">
                          <div class="card card-outline">
                            <div class="card-body d-flex align-items-center justify-content-end" style="height: 100%;">
                              <button type="submit" class="btn btn-success btn-lg"><i class="bi bi-save"></i> Simpan</button>
                            </div>
                          </div>
                        </div>
                      </div>
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






<!-- SCRIPT UNTUK INPUT DATA -->
<script>

let rowCount = 0;

function formatRupiah(angka) {
    return Number(angka || 0).toLocaleString('id-ID');
}

$(document).ready(function () {
    $('#kd_customer').val('');
    $('#kd_pegawai').val('');
    $('#cash').val('');
    $('#totalPenjualan').val('');
    $('#kembalian').val('');
    $('#totalPenjualanSetelahDiskon').val('');


    $('#productSelect').select2({
        placeholder: 'Search for a product',
        ajax: {
            url: '/products-list',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term
                };
            },
            processResults: function (data) {
                return {
                    results: data.dataBarangSatuan.map(function (item) {
                        return {
                            id: item.kd_barang,
                            text: item.barang + ` / ` + item.satuan,
                            kd_barang: item.kd_barang,
                            barang: item.barang,
                            kd_satuan: item.kd_satuan,
                            satuan: item.satuan,
                            harga: item.harga_jual
                        };
                    })
                };
            },
            cache: true
        }
    });

    $('#productSelect').on('select2:select', function (e) {
        const data = e.params.data;
        let html = `
            <tr>
                <td><input class="form-control" type="text" name="products[${rowCount}][barang]" value="${data.barang}" readonly></td>
                <td><input class="form-control" type="text" name="products[${rowCount}][satuan]" value="${data.satuan}" readonly></td>
                <td><input class="form-control harga" type="text" name="products[${rowCount}][harga_jual]" value="${data.harga}" data-row="${rowCount}" readonly></td>
                <td><input class="form-control diskon_dt" value="0" type="number" name="products[${rowCount}][diskon_dt]" data-row="${rowCount}" required></td>
                <td><input class="form-control qty" type="number" name="products[${rowCount}][qty]" data-row="${rowCount}" required></td>
                <td><input class="form-control total_harga" type="text" name="products[${rowCount}][total]" data-row="${rowCount}" readonly></td>
                <td class="text-center">
                    <input type="hidden" name="products[${rowCount}][kd_barang]" value="${data.kd_barang}">
                    <input type="hidden" name="products[${rowCount}][kd_satuan]" value="${data.kd_satuan}">
                    <button class="btn btn-danger btn-sm removeRow" type="button"><i class="bi bi-trash"></i> Hapus</button>
                </td>
            </tr>`;

        $('#productTable tbody').append(html);
        rowCount++;
    });

    // Recalculate row + grand total when qty or diskon changes
    $('#productTable').on('input', '.qty, .diskon_dt', function () {
        let row = $(this).data('row');
        let qty = parseFloat($(`input.qty[data-row="${row}"]`).val()) || 0;
        let diskon_dt = parseFloat($(`input.diskon_dt[data-row="${row}"]`).val()) || 0;
        let harga = parseFloat($(`input.harga[data-row="${row}"]`).val()) || 0;
        let total = (qty * harga) - (diskon_dt * qty);
        $(`input.total_harga[data-row="${row}"]`).val(total);

        updateGrandTotal();
    });

    // Calculate grand total
    function updateGrandTotal() {
        let grandTotal = 0;
        $('.total_harga').each(function () {
            grandTotal += parseFloat($(this).val()) || 0;
        });
        $('#totalPenjualan').val(grandTotal);

        let diskon = parseFloat($('#masterDiskon').val()) || 0;
        let grandAfter = grandTotal - diskon;
        $('#totalPenjualanSetelahDiskon').val(grandAfter);
        $('#totalDisplay').text(formatRupiah(grandAfter));
    }

    // Master discount
    $('#masterDiskon').on('input change', function () {
        updateGrandTotal();
    });

    // Cash -> Kembalian
    $('#cash').on('blur input', function () {
        let cash = parseFloat($('#cash').val()) || 0;
        let grandAfter = parseFloat($('#totalPenjualanSetelahDiskon').val()) || 0;
        $('#kembalian').val(cash - grandAfter);
    });

    // Remove row
    $('#productTable').on('click', '.removeRow', function () {
        $(this).closest('tr').remove();
        updateGrandTotal();
    });
});

</script>


<!-- SCRIPT UNTUK TABEL DATA -->
<script>
  function formatRupiah(value) {
    const num = Number(value);
    if (isNaN(num)) return value;
    return 'Rp ' + num.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
  }
  function formatQty(value) {
    const num = Number(value);
    if (isNaN(num)) return value;
    return num.toLocaleString('id-ID', { maximumFractionDigits: 2 });
  }

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
    order: [[1, 'desc']],
    ajax: {
      url: "{{ route('data.penjualan') }}",
      type: 'GET',
      data: function (d) {
        d.date_from = $('#filter_from').val();
        d.date_to   = $('#filter_to').val();
      }
    },
    columns: [
      { data: 'no_transaksi', className: 'text-center' },
      { data: 'tanggal_penjualan', className: 'text-center' },
      { data: 'diskon', className: 'text-center' },
      { data: 'customer', className: 'text-center' },
      {
        data: null,
        orderable: false,
        searchable: false,
        className: 'text-center',
        render: function (data, type, row) {
          return '<button type="button" class="btn btn-xs btn-primary toggle-child"><i class="bi bi-eye"></i> Lihat</button> '
               + '<button type="button" class="btn btn-xs btn-danger hapus-transaksi" data-notransaksi="' + row.no_transaksi + '"><i class="bi bi-trash"></i> Hapus</button>';
        }
      }
    ],
    createdRow: function (row, data) {
      $(row).addClass('data-row').attr('data-notransaksi', data.no_transaksi);
    }
  });

  // Filter tanggal
  $('#btn_filter').on('click', function () {
    table.ajax.reload();
  });
  $('#btn_reset_filter').on('click', function () {
    $('#filter_from').val('');
    $('#filter_to').val('');
    table.ajax.reload();
  });
  $('#filter_from, #filter_to').on('change', function () {
    table.ajax.reload();
  });


    // Build & render the detail child row for a transaction
    function loadDetailPenjualan(row, tr, btn) {
      const noTransaksi = tr.data('notransaksi');
      return $.ajax({
        url: '/detail-penjualan',
        type: 'GET',
        data: { no_transaksi: noTransaksi },
        success: function (response) {
          let detailRows = response.dataDetail.map(function (item) {
            return `
              <tr>
                <td>${item.barang}</td>
                <td class="text-center">${item.satuan}</td>
                <td class="text-right">${formatQty(item.qty)}</td>
                <td class="text-right">${formatRupiah(item.harga_jual)}</td>
                <td class="text-right">${formatRupiah(item.diskon)}</td>
                <td class="text-center">
                  <button class="btn btn-warning btn-sm edit_detail"
                  data-diskon="${item.diskon}"
                  data-qty="${item.qty}"
                  data-transaksi="${noTransaksi}"
                  data-kd_barang="${item.kd_barang}"
                  data-barang="${item.barang}"
                  data-kd_satuan="${item.kd_satuan}"
                  data-satuan="${item.satuan}"
                  type="button"
                  data-toggle="modal"
                  data-target="#exampleModal">
                  <i class="bi bi-pencil"></i> Edit</button>
                </td>
              </tr>
            `;
          }).join('');

          const childHtml = `
            <div class="detail-child p-3">
              <div class="card shadow-sm border-0 mb-0">
                <div class="card-header bg-primary text-white py-2 d-flex align-items-center">
                  <i class="bi bi-receipt mr-2"></i>
                  <strong>Detail Transaksi ${noTransaksi}</strong>
                </div>
                <div class="table-responsive">
                  <table class="table table-sm table-hover table-striped align-middle mb-0">
                    <thead class="table-light">
                      <tr>
                        <th>Barang</th>
                        <th class="text-center">Satuan</th>
                        <th class="text-right">Qty</th>
                        <th class="text-right">Harga Jual</th>
                        <th class="text-right">Diskon</th>
                        <th class="text-center">#</th>
                      </tr>
                    </thead>
                    <tbody>
                      ${detailRows}
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          `;

          row.child(childHtml).show();
          tr.addClass('shown');
          if (btn) btn.html('<i class="bi bi-eye-slash"></i> Sembunyi');
        },
        error: function () {
          alert('Gagal mengambil data detail.');
        }
      });
    }

    $('#example2 tbody').on('click', '.toggle-child', function () {
      const tr = $(this).closest('tr');
      const row = table.row(tr);
      const btn = $(this);

      if (row.child.isShown()) {
        row.child.hide();
        tr.removeClass('shown');
        btn.html('<i class="bi bi-eye"></i> Lihat');
      } else {
        loadDetailPenjualan(row, tr, btn);
      }
    });

    // Hapus transaksi penjualan (header + detail) via AJAX
    $('#example2 tbody').on('click', '.hapus-transaksi', function () {
      const noTransaksi = $(this).data('notransaksi');
      if (!confirm('Hapus transaksi ' + noTransaksi + ' beserta seluruh detailnya? Tindakan ini tidak dapat dibatalkan.')) {
        return;
      }
      const $btn = $(this);
      $btn.prop('disabled', true);
      $.ajax({
        url: "{{ route('hapus.penjualan') }}",
        type: 'POST',
        data: { no_transaksi: noTransaksi, _token: '{{ csrf_token() }}' },
        success: function (res) {
          if (res && res.success === false) {
            alert(res.message || 'Gagal menghapus transaksi.');
            $btn.prop('disabled', false);
            return;
          }
          table.ajax.reload(null, false);
        },
        error: function () {
          alert('Gagal menghapus transaksi.');
          $btn.prop('disabled', false);
        }
      });
    });

    // Isi form modal saat tombol Edit diklik (delegated, tidak rebinding)
    $('#example2 tbody').on('click', '.edit_detail', function () {
      $('#dt_barang').val($(this).data('barang'));
      $('#dt_satuan').val($(this).data('satuan'));
      $('#dt_kd_barang').val($(this).data('kd_barang'));
      $('#dt_kd_satuan').val($(this).data('kd_satuan'));
      $('#dt_no_transaksi').val($(this).data('transaksi'));
      $('#dt_diskon').val($(this).data('diskon'));
      $('#dt_qty').val($(this).data('qty'));
    });

    // Submit form edit via AJAX supaya halaman tidak reload & detail tetap terbuka
    $('#frm-edit').on('submit', function (e) {
      e.preventDefault();
      const noTransaksi = $('#dt_no_transaksi').val();
      const $btn = $(this).find('button[type="submit"]');
      $btn.prop('disabled', true);

      $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: $(this).serialize(),
        success: function () {
          $('#exampleModal').modal('hide');
          // Refresh hanya detail yang sedang terbuka, tanpa reload halaman
          const tr = $('#example2 tbody tr.data-row[data-notransaksi="' + noTransaksi + '"]');
          const row = table.row(tr);
          if (row.node()) {
            loadDetailPenjualan(row, tr, tr.find('.toggle-child'));
          }
        },
        error: function () {
          alert('Gagal menyimpan perubahan.');
        },
        complete: function () {
          $btn.prop('disabled', false);
        }
      });
    });
</script>







@endsection