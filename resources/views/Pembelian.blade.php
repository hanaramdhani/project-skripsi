@extends('layout.template')

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>DATA TRANSAKSI PEMBELIAN</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item">Transaksi</li>
              <li class="breadcrumb-item active">Pembelian</li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        <div class="row">

          <div class="col-md-12">
            <div class="card">
              <div class="card-header p-2">
                <ul class="nav nav-pills">
                  <li class="nav-item"><a class="nav-link active" href="#activity" data-toggle="tab">Data Pembelian</a></li>
                  <li class="nav-item"><a class="nav-link" href="#settings" data-toggle="tab"><i class="bi bi-plus"></i> Input Data</a></li>
                </ul>
              </div>
              <div class="card-body">
                <div class="tab-content">
                  <div class="active tab-pane" id="activity">
                    <!-- Filter Tanggal From/To -->
                    <div class="row mb-3">
                      <div class="col-md-3">
                        <label for="filter_from" class="mb-1"><strong>Dari Tanggal</strong></label>
                        <input type="date" class="form-control" id="filter_from" value="{{ $last_purchase_date }}">
                      </div>
                      <div class="col-md-3">
                        <label for="filter_to" class="mb-1"><strong>Sampai Tanggal</strong></label>
                        <input type="date" class="form-control" id="filter_to" value="{{ $last_purchase_date }}">
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
                          <th class="text-center">JATUH TEMPO</th>
                          <th class="text-center">SUPPLIER</th>
                          <th class="text-center">DISKON</th>
                          <th class="text-center">#</th>
                      </tr>
                      </thead>
                      <tbody></tbody>
                      <tfoot></tfoot>
                    </table>

                    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                      <div class="modal-dialog" role="document">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Form Edit Detail Pembelian</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                          </div>
                          <div class="modal-body">
                            <form class="form-horizontal" id="frm-edit" name="frm-edit" method="POST" action="{{ route('edit.pembelian') }}">
                              @csrf
                              <div class="form-group">
                                <label class="col-form-label">Barang</label>
                                <input type="text" class="form-control" id="dt_barang" readonly>
                              </div>
                              <div class="form-group">
                                <label class="col-form-label">Satuan</label>
                                <input type="text" class="form-control" id="dt_satuan" readonly>
                              </div>
                              <div class="form-group">
                                <label class="col-form-label">QTY</label>
                                <input name="qty" class="form-control" id="dt_qty">
                              </div>
                              <div class="form-group">
                                <label class="col-form-label">Diskon</label>
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

                  <?php date_default_timezone_set('Asia/Jakarta'); ?>
                  <div class="tab-pane" id="settings">
                    <form class="form-horizontal" id="frm-input" name="frm_input" method="POST" action="{{ route('input.pembelian') }}">
                      @csrf

                      <!-- TOP: Tanggal/No.Transaksi/Supplier/Jatuh Tempo/No Order | Pilih Barang | Total -->
                      <div class="row">
                        <div class="col-md-5">
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
                                <label class="col-sm-4 col-form-label"><strong>Supplier</strong></label>
                                <div class="col-sm-8">
                                  <select class="form-control" name="kd_supplier" id="supplier" required>
                                    @foreach ($supplier as $value)
                                      <option value="{{ $value->kd_supplier }}">{{ $value->supplier }}</option>
                                    @endforeach
                                  </select>
                                </div>
                              </div>
                              <div class="form-group row mb-2">
                                <label class="col-sm-4 col-form-label"><strong>Jatuh Tempo</strong></label>
                                <div class="col-sm-8">
                                  <input type="date" name="tanggal_jatuh_tempo" id="tanggal_jatuh_tempo" class="form-control" value="{{ date('Y-m-d', strtotime('+7 days')) }}" required>
                                </div>
                              </div>
                              <div class="form-group row mb-2">
                                <label class="col-sm-4 col-form-label"><strong>No. Order</strong></label>
                                <div class="col-sm-8">
                                  <input type="text" name="no_order" id="no_order" class="form-control" value="-">
                                </div>
                              </div>
                              <div class="form-group row mb-0">
                                <label class="col-sm-4 col-form-label"><strong>Keterangan</strong></label>
                                <div class="col-sm-8">
                                  <input type="text" name="keterangan" id="keterangan" class="form-control" value="-">
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

                        <div class="col-md-3">
                          <div class="card card-outline">
                            <div class="card-body text-right">
                              <p class="mb-1" style="font-size:18px;">Total</p>
                              <h1 class="font-weight-bold mb-0" id="totalDisplay" style="font-size:48px;">0</h1>
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
                                <th class="text-center">Harga Beli</th>
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

                      <!-- BOTTOM: Subtotal/Diskon/Pajak/PPNBM/Grand Total | Simpan -->
                      <div class="row">
                        <div class="col-md-5">
                          <div class="card card-outline">
                            <div class="card-body">
                              <div class="form-group row mb-2">
                                <label class="col-sm-5 col-form-label"><strong>Subtotal</strong></label>
                                <div class="col-sm-7">
                                  <input id="totalPembelian" type="number" class="form-control" style="background-color: #e0e0e0;" readonly>
                                </div>
                              </div>
                              <div class="form-group row mb-2">
                                <label class="col-sm-5 col-form-label"><strong>Diskon</strong></label>
                                <div class="col-sm-7">
                                  <input type="number" name="masterDiskon" id="masterDiskon" class="form-control" value="0" required>
                                </div>
                              </div>
                              <div class="form-group row mb-2">
                                <label class="col-sm-5 col-form-label"><strong>Pajak</strong></label>
                                <div class="col-sm-7">
                                  <input type="number" name="pajak" id="pajak" class="form-control" value="0" required>
                                </div>
                              </div>
                              <div class="form-group row mb-2">
                                <label class="col-sm-5 col-form-label"><strong>PPNBM</strong></label>
                                <div class="col-sm-7">
                                  <input type="number" name="ppnbm" id="ppnbm" class="form-control" value="0" required>
                                </div>
                              </div>
                              <div class="form-group row mb-0">
                                <label class="col-sm-5 col-form-label"><strong>Grand Total</strong></label>
                                <div class="col-sm-7">
                                  <input id="grandTotal" type="number" class="form-control" style="background-color: #e0e0e0;" readonly>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-7">
                          <div class="card card-outline">
                            <div class="card-body d-flex align-items-center justify-content-end" style="height: 100%;">
                              <button type="submit" class="btn btn-success btn-lg"><i class="bi bi-save"></i> Simpan</button>
                            </div>
                          </div>
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
<script src="{{ asset('lte/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('lte/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
<script src="{{ asset('lte/plugins/jszip/jszip.min.js') }}"></script>
<script src="{{ asset('lte/plugins/pdfmake/pdfmake.min.js') }}"></script>
<script src="{{ asset('lte/plugins/pdfmake/vfs_fonts.js') }}"></script>
<script src="{{ asset('lte/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
<script src="{{ asset('lte/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
<script src="{{ asset('lte/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>

<!-- SCRIPT UNTUK INPUT DATA -->
<script>
let rowCount = 0;

function formatRupiah(angka) {
    return Number(angka || 0).toLocaleString('id-ID');
}

$(document).ready(function () {
    $('#productSelect').select2({
        placeholder: 'Cari nama barang',
        ajax: {
            url: '/products-list-beli',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { q: params.term };
            },
            processResults: function (data) {
                return {
                    results: data.dataBarangSatuan.map(function (item) {
                        return {
                            id: item.kd_barang + '-' + item.kd_satuan,
                            text: item.barang + ' / ' + item.satuan,
                            kd_barang: item.kd_barang,
                            barang: item.barang,
                            kd_satuan: item.kd_satuan,
                            satuan: item.satuan,
                            harga: item.harga_beli
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
                <td><input class="form-control harga" type="number" name="products[${rowCount}][harga_beli]" value="${data.harga}" data-row="${rowCount}" required></td>
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

        // Reset select2 ke kosong supaya bisa pilih barang lain
        $('#productSelect').val(null).trigger('change');
    });

    // Recalculate row + grand total when qty/diskon/harga changes
    $('#productTable').on('input', '.qty, .diskon_dt, .harga', function () {
        let row = $(this).data('row');
        let qty = parseFloat($(`input.qty[data-row="${row}"]`).val()) || 0;
        let diskon_dt = parseFloat($(`input.diskon_dt[data-row="${row}"]`).val()) || 0;
        let harga = parseFloat($(`input.harga[data-row="${row}"]`).val()) || 0;
        let total = (qty * harga) - (diskon_dt * qty);
        $(`input.total_harga[data-row="${row}"]`).val(total);

        updateGrandTotal();
    });

    function updateGrandTotal() {
        let subtotal = 0;
        $('.total_harga').each(function () {
            subtotal += parseFloat($(this).val()) || 0;
        });
        $('#totalPembelian').val(subtotal);

        let diskon = parseFloat($('#masterDiskon').val()) || 0;
        let pajak  = parseFloat($('#pajak').val())  || 0;
        let ppnbm  = parseFloat($('#ppnbm').val())  || 0;
        let grand = subtotal - diskon + pajak + ppnbm;

        $('#grandTotal').val(grand);
        $('#totalDisplay').text(formatRupiah(grand));
    }

    $('#masterDiskon, #pajak, #ppnbm').on('input change', updateGrandTotal);

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
      url: "{{ route('data.pembelian') }}",
      type: 'GET',
      data: function (d) {
        d.date_from = $('#filter_from').val();
        d.date_to   = $('#filter_to').val();
      }
    },
    columns: [
      { data: 'no_transaksi', className: 'text-center' },
      { data: 'tanggal_pembelian', className: 'text-center' },
      { data: 'tanggal_jatuh_tempo', className: 'text-center' },
      { data: 'supplier', className: 'text-center' },
      { data: 'diskon', className: 'text-center' },
      {
        data: null,
        orderable: false,
        searchable: false,
        className: 'text-center',
        render: function () {
          return '<button type="button" class="btn btn-xs btn-primary toggle-child"><i class="bi bi-eye"></i> Lihat</button>';
        }
      }
    ],
    createdRow: function (row, data) {
      $(row).addClass('data-row').attr('data-notransaksi', data.no_transaksi);
    }
  });

  $('#btn_filter').on('click', function () { table.ajax.reload(); });
  $('#btn_reset_filter').on('click', function () {
    $('#filter_from').val('');
    $('#filter_to').val('');
    table.ajax.reload();
  });
  $('#filter_from, #filter_to').on('change', function () { table.ajax.reload(); });

  // Build & render the detail child row for a transaction
  function loadDetailPembelian(row, tr, btn) {
    const noTransaksi = tr.data('notransaksi');
    return $.ajax({
        url: '/detail-pembelian',
        type: 'GET',
        data: { no_transaksi: noTransaksi },
        success: function (response) {
          let detailRows = response.dataDetail.map(function (item) {
            return `
              <tr>
                <td>${item.barang}</td>
                <td class="text-center">${item.satuan}</td>
                <td class="text-right">${formatQty(item.qty)}</td>
                <td class="text-right">${formatRupiah(item.harga_beli)}</td>
                <td class="text-right">${formatRupiah(item.diskon)}</td>
                <td class="text-right">${formatRupiah(item.total)}</td>
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
                    <i class="bi bi-pencil"></i> Edit
                  </button>
                </td>
              </tr>
            `;
          }).join('');

          const childHtml = `
            <div class="detail-child p-3">
              <div class="card shadow-sm border-0 mb-0">
                <div class="card-header bg-primary text-white py-2 d-flex align-items-center">
                  <i class="bi bi-bag-check mr-2"></i>
                  <strong>Detail Transaksi ${noTransaksi}</strong>
                </div>
                <div class="table-responsive">
                  <table class="table table-sm table-hover table-striped align-middle mb-0">
                    <thead class="table-light">
                      <tr>
                        <th>Barang</th>
                        <th class="text-center">Satuan</th>
                        <th class="text-right">Qty</th>
                        <th class="text-right">Harga Beli</th>
                        <th class="text-right">Diskon</th>
                        <th class="text-right">Total</th>
                        <th class="text-center">#</th>
                      </tr>
                    </thead>
                    <tbody>${detailRows}</tbody>
                  </table>
                </div>
              </div>
            </div>
          `;

          row.child(childHtml).show();
          tr.addClass('shown');
          if (btn) btn.html('<i class="bi bi-eye-slash"></i> Sembunyi');
        },
        error: function () { alert('Gagal mengambil data detail.'); }
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
      loadDetailPembelian(row, tr, btn);
    }
  });

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
          loadDetailPembelian(row, tr, tr.find('.toggle-child'));
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
