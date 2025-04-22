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
                  <li class="nav-item"><a class="nav-link" href="#settings" data-toggle="tab">Input Data</a></li>
                </ul>
              </div><!-- /.card-header -->
              <div class="card-body">
                <div class="tab-content">
                  <div class="active tab-pane" id="activity">
                    <table id="example2" class="table table-bordered table-hover">
                      <thead>
                      <tr>
                          <th class="text-center">NO. TRANSAKSI</th>
                          <th class="text-center">TANGGAL</th>
                          <th class="text-center">DISKON</th>
                          <th class="text-center">CUSTOMER</th>
                          <th class="text-center">#</th>
                      </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($data as $key => $value): ?>
                          <tr class="data-row" data-notransaksi="<?= $value->no_transaksi ?>">
                            <td class="text-center"><?= $value->no_transaksi ?></td>
                            <td class="text-center"><?= $value->tanggal_penjualan ?></td>
                            <td class="text-center"><?= $value->diskon ?></td>
                            <td class="text-center"><?= $value->customer ?></td>
                            <td class="text-center">
                              <button type="button" class="btn btn-xs btn-primary toggle-child">
                                <i class="bi bi-eye"></i> Lihat
                              </button>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
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
                    <?php
                        date_default_timezone_set('Asia/Jakarta');
                        echo '<h3 class="text-right">'.date('d/m/Y').'</h3>';
                    ?>
                      @csrf
                      <div class="form-group row">
                        <label for="inputName" class="col-sm-2 col-form-label">Nomor Transaksi</label>
                        <div class="col-sm-10">
                        <input class="form-control" type="text" id="no_transaksi" name="no_transaksi" value="{{ old('no_transaksi', $no_transaksi) }}" readonly>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="inputEmail" class="col-sm-2 col-form-label">Customer</label>
                        <div class="col-sm-10">
                          <select class="form-control" name="kd_customer" id="customer">
                            <?php
                              foreach ($customer as $key => $value) {
                                echo '<option value="'.$value->kd_customer.'">'.$value->customer.'</option>';
                              };
                            ?>
                          </select>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="inputName2" class="col-sm-2 col-form-label">Pegawai</label>
                        <div class="col-sm-10">
                          <input type="text" name="kd_pegawai" id="kd_pegawai" class="form-control" value="" required>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="inputSkills" class="col-sm-2 col-form-label">Pilih Barang</label>
                        <div class="col-sm-10">
                          <select class="form-control" id="productSelect" required></select>
                        </div>
                      </div>                      
                      <!-- <h4>Pilih Barang <span><select class="form-control" id="productSelect"></select></span></h4>   -->
                      <table id="productTable" class=" table stripped-table">
                        <thead>
                          <tr>
                            <th>Barang</th><th>Satuan</th><th>Harga</th><th>Diskon</th><th>Qty</th><th>Total</th>
                          </tr>
                        </thead>
                        <tbody>
                          
                          </tbody>
                        </table>
                      <!-- <input type="text" id="tgl_temporary" value="{{ date('d/m/Y') }}"> -->
                      
                      <h3 class="text-right">Total <span><input id="totalPenjualan" type="number" style="background-color: #e0e0e0; border: 1px solid #ccc; color: #333;" readonly></span></h3>
                      <h3 class="text-right">Diskon <span><input type="number" name="masterDiskon" id="masterDiskon" required></span></h3>
                      <h3 class="text-right">Total Setelah Diskon <span><input id="totalPenjualanSetelahDiskon" type="number" style="background-color: #e0e0e0; border: 1px solid #ccc; color: #333;" readonly></span></h3>
                      <h3 class="text-right">Cash <span><input id="cash" type="number" width="50%" required></span></h3>
                      <h3 class="text-right">Kembalian <span><input id="kembalian" type="number" style="background-color: #e0e0e0; border: 1px solid #ccc; color: #333;" readonly></span></h3>
                        
                       <button type="submit" class="btn btn-success text-right">Simpan</button>
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






<!-- SCRIPT UNTUK INPUT DATA -->
<script>

let rowCount = 0;

$(document).ready(function () {
    // $('#no_transaksi').val('');
    $('#kd_customer').val('');
    $('#kd_pegawai').val('');
    $('#diskon').val('');
    $('#cash').val('');
    $('#totalPenjualan').val('');
    $('#kembalian').val('');
    $('#masterDiskon').val('');
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
            <td><input class="form-control diskon_dt" id="diskon_dt" value="0" type="number" name="products[${rowCount}][diskon_dt]" data-row="${rowCount}" required></td>
            <td><input class="form-control qty" type="number" name="products[${rowCount}][qty]" data-row="${rowCount}" required></td>
            <td><input class="form-control total_harga" type="text" name="products[${rowCount}][total]" data-row="${rowCount}" readonly></td>
            <td><input type="hidden" name="products[${rowCount}][kd_barang]" value="${data.kd_barang}" readonly></td>
            <td><input type="hidden" name="products[${rowCount}][kd_satuan]" value="${data.kd_satuan}" readonly></td>
                <td><button class="btn btn-danger btn-sm removeRow" type="button">Hapus</button></td>
            </tr>`;

        $('#productTable tbody').append(html);
        rowCount++;
    });

    // When quantity changes
    $('#productTable').on('input', '.qty',  function () {
        let row = $(this).data('row');

        // Get quantity and harga for this row
        let qty = parseFloat($(this).val()) || 0;
        let diskon_dt = parseFloat($(`input.diskon_dt[data-row="${row}"]`).val()) || 0;
        let harga = parseFloat($(`input.harga[data-row="${row}"]`).val()) || 0;

        // Calculate total and update the input
        let total = qty * harga;
        let total_setelah_diskon = total - (diskon_dt*qty);
        $(`input.total_harga[data-row="${row}"]`).val(total_setelah_diskon);

        // Update grand total
        updateGrandTotal();
    });

    // function totalDt(){
      
    // }

    // Calculate grand total
    function updateGrandTotal() {
        let grandTotal = 0;

        $('.total_harga').each(function () {
            let total = parseFloat($(this).val()) || 0;
            grandTotal += total;
        });

        $('#totalPenjualan').val(grandTotal);
    }

    // hitung master detail
    $('#masterDiskon').on('change', function() {
      $('#totalPenjualanSetelahDiskon').val($('#totalPenjualan').val()-$('#masterDiskon').val());
    })

    

    // kembalian
    $('#cash').on('blur', function() {
        $('#kembalian').val($('#cash').val()-$('#totalPenjualanSetelahDiskon').val());
    })

    // Remove row and update total
    $('#productTable').on('click', '.removeRow', function () {
        $(this).closest('tr').remove();
        updateGrandTotal();
    });
});

$(document).on('click', '.removeRow', function() {
    $(this).closest('tr').remove();
});

</script>


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


    $('#example2 tbody').on('click', '.toggle-child', function () {
      const tr = $(this).closest('tr');
      const row = table.row(tr);

      if (row.child.isShown()) {
        row.child.hide();
        tr.removeClass('shown');
        $(this).html('<i class="bi bi-eye"></i> Lihat');
      } else {
        const price = 'Test 2';
        const noTransaksi = tr.data('notransaksi');

        $.ajax({
          url: '/detail-penjualan', 
          type: 'GET',
          data: { no_transaksi: noTransaksi },
          success: function (response) {
            // Build a table for multiple detail rows
            let detailRows = response.dataDetail.map(function (item) {
              return `
                <tr>
                  <td>${item.barang}</td>
                  <td>${item.satuan}</td>
                  <td>${item.qty}</td>
                  <td>${item.harga_jual}</td>
                  <td>${item.diskon}</td>
                  <td>
                    <button class="btn btn-warning btn-xs edit_detail" 
                    id="edit_detail" 
                    data-diskon=${item.diskon} 
                    data-qty=${item.qty} 
                    data-transaksi=${noTransaksi} 
                    data-kd_barang=${item.kd_barang} 
                    data-barang='${item.barang}' 
                    data-kd_satuan=${item.kd_satuan} 
                    data-satuan=${item.satuan} 
                    type="button" 
                    data-toggle="modal" 
                    data-target="#exampleModal">
                    <i class="bi bi-pencil"></i>Edit</button>
                  </td>
                </tr>
              `;
            }).join('');

            const childHtml = `
              <table cellpadding="5" cellspacing="0" border="1" style="margin-left:1000px;">
                <thead>
                  <tr>
                    <th>Barang</th>
                    <th>Satuan</th>
                    <th>Qty</th>
                    <th>Harga Jual</th>
                    <th>Diskon</th>
                    <th>#</th>
                  </tr>
                </thead>
                <tbody>
                  ${detailRows}
                </tbody>
              </table>
            `;

            row.child(childHtml).show();
            tr.addClass('shown');
            $(this).html('<i class="bi bi-eye-slash"></i> Sembunyi');
          },
          error: function () {
            alert('Gagal mengambil data detail.');
          }
        });
      }

      $('#example2 tbody').on('click', '.edit_detail', function () {
        let barang = $(this).data('barang');
        let satuan = $(this).data('satuan');
        let kd_barang = $(this).data('kd_barang');
        let kd_satuan = $(this).data('kd_satuan');
        let no_transaksi = $(this).data('transaksi');
        let diskon = $(this).data('diskon');
        let qty = $(this).data('qty');

        $('#dt_barang').val(barang);
        $('#dt_satuan').val(satuan);
        $('#dt_kd_barang').val(kd_barang);
        $('#dt_kd_satuan').val(kd_satuan);
        $('#dt_no_transaksi').val(no_transaksi);
        $('#dt_diskon').val(diskon);
        $('#dt_qty').val(qty);
      });
    });
</script>







@endsection