@extends('layout.template')

@section('content')


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
                        <th>NO. TRANSAKSI</th>
                        <th>TANGGAL</th>
                        <th>BARANG</th>
                        <th>SATUAN</th>
                        <th>HARGA JUAL</th>
                        <th>QTY</th>
                        <th>TOTAL</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php 
                        foreach ($data as $key => $value) {
                                echo '<tr>';
                                echo '<td>' . ($value->no_transaksi) . '</td>';
                                echo '<td>' . ($value->tanggal) . '</td>';
                                echo '<td>' . ($value->barang) . '</td>';
                                echo '<td>' . ($value->satuan) . '</td>';
                                echo '<td>' . ($value->harga_jual) . '</td>';
                                echo '<td>' . ($value->qty) . '</td>';
                                echo '<td>' . ($value->total) . '</td>';
                                echo '</tr>';
                        }
                    ?>
                    </tbody>
                    <tfoot>
                    <!-- <tr>
                        <th>Rendering engine</th>
                        <th>Browser</th>
                        <th>Platform(s)</th>
                        <th>Engine version</th>
                        <th>CSS grade</th>
                        <th>CSS grade</th>
                    </tr> -->
                    </tfoot>
                    </table>
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

                  <div class="tab-pane" id="settings">
                    <form class="form-horizontal">
                      <div class="form-group row">
                        <label for="inputName" class="col-sm-2 col-form-label">Nomor Transaksi</label>
                        <div class="col-sm-10">
                          <input type="email" class="form-control" id="inputName" placeholder="Name">
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="inputEmail" class="col-sm-2 col-form-label">Customer</label>
                        <div class="col-sm-10">
                          <input type="email" class="form-control" id="inputEmail" placeholder="Email">
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="inputName2" class="col-sm-2 col-form-label">Pegawai</label>
                        <div class="col-sm-10">
                          <input type="text" class="form-control" id="inputName2" placeholder="Name">
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="inputExperience" class="col-sm-2 col-form-label">Tanggal</label>
                        <div class="col-sm-10">
                          <textarea class="form-control" id="inputExperience" placeholder="Experience"></textarea>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="inputSkills" class="col-sm-2 col-form-label">Diskon</label>
                        <div class="col-sm-10">
                          <input type="text" class="form-control" id="inputSkills" placeholder="Skills">
                        </div>
                      </div>
                      
                      <h4>Pilih Barang <span><select class="form-control" id="productSelect"></select></span></h4>  
                      
                      <table id="productTable" class=" table stripped-table">
                            <thead>
                                <tr>
                                    <th>Barang</th><th>Satuan</th><th>Qty</th><th>Harga</th><th>Total</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                      </table>

                      <h1 class="text-right">Total Penjualan <span><input id="totalPenjualan" type="text" width="50%"></span></h1>

                      <div class="form-group row">
                        <div class="offset-sm-2 col-sm-10">
                          <button type="submit" class="btn btn-success">Submit</button>
                        </div>
                      </div>

                    </form>

                    <!-- <form id="productForm" class="form-group">
                        
                        <button type="submit">Submit</button>
                    </form> -->


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

<script>
let rowCount = 0;

$(document).ready(function () {
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
                <td><input class="form-control qty" type="number" name="products[${rowCount}][qty]" data-row="${rowCount}"></td>
                <td><input class="form-control harga" type="text" name="products[${rowCount}][price]" value="${data.harga}" data-row="${rowCount}" readonly></td>
                <td><input class="form-control total_harga" type="text" name="products[${rowCount}][total]" data-row="${rowCount}" readonly></td>
                <td><button class="btn btn-danger btn-sm removeRow" type="button">Hapus</button></td>
            </tr>`;

        $('#productTable tbody').append(html);
        rowCount++;
    });

    // When quantity changes
    $('#productTable').on('input', '.qty', function () {
        let row = $(this).data('row');

        // Get quantity and harga for this row
        let qty = parseFloat($(this).val()) || 0;
        let harga = parseFloat($(`input.harga[data-row="${row}"]`).val()) || 0;

        // Calculate total and update the input
        let total = qty * harga;
        $(`input.total_harga[data-row="${row}"]`).val(total);

        // Update grand total
        updateGrandTotal();
    });

    // Calculate grand total
    function updateGrandTotal() {
        let grandTotal = 0;

        $('.total_harga').each(function () {
            let total = parseFloat($(this).val()) || 0;
            grandTotal += total;
        });

        $('#totalPenjualan').val(grandTotal);
    }

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





@endsection