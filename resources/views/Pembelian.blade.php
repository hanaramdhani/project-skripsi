@extends('layout.template')

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<style>
  /* Perkecil tinggi field form di halaman Pembelian (input & select terasa terlalu tinggi) */
  .content-wrapper .form-control,
  .content-wrapper select.form-control {
      height: calc(1.5em + 0.5rem + 2px);
      padding: 0.25rem 0.5rem;
      font-size: 0.875rem;
  }
  .content-wrapper .col-form-label {
      padding-top: calc(0.25rem + 1px);
      padding-bottom: calc(0.25rem + 1px);
      font-size: 0.875rem;
  }
  .content-wrapper .input-group-append .btn {
      padding: 0.25rem 0.5rem;
      font-size: 0.875rem;
  }
  /* Sejajarkan tinggi card kiri dan kanan */
  .card-outline {
      height: 100%;
      display: flex;
      flex-direction: column;
  }
  .card-outline .card-body {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
  }
</style>
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
                          <th class="text-center">SUPPLIER</th>
                          <th class="text-center">JUMLAH ITEM</th>
                          <th class="text-center">TOTAL DISKON</th>
                          <th class="text-center">TOTAL</th>
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

                      <!-- TOP: Pilih Barang (search box + hasil pencarian) | Tanggal/No.Transaksi/Supplier/Jatuh Tempo/No Order -->
                      <div class="row">
                        <div class="col-md-7">
                          <div class="card card-outline">
                            <div class="card-body">
                              <div class="form-group mb-0">
                                <label><strong>Pilih Barang</strong></label>
                                <div class="input-group">
                                  <input type="text" class="form-control form-control-lg" id="productSearchInput" placeholder="Ketik min. 3 huruf nama/kode barang, atau scan barcode" autocomplete="off">
                                  <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#tambahBarangModal" title="Tambah Barang Baru">
                                      <i class="bi bi-plus-lg"></i>
                                    </button>
                                  </div>
                                </div>
                                <small id="barcodeMsg" class="text-danger" style="display:none;"></small>

                                <!-- Hasil pencarian barang -->
                                <div id="productResultBox" class="border rounded mt-2" style="display:none;">
                                  <div style="max-height:260px; overflow-y:auto;">
                                    <table class="table table-sm table-hover mb-0">
                                      <thead class="thead-light">
                                        <tr>
                                          <th>Kode</th>
                                          <th>Nama Barang</th>
                                          <th>Satuan</th>
                                          <th class="text-right">Harga Jual</th>
                                          <th class="text-right">Harga Beli Terakhir</th>
                                        </tr>
                                      </thead>
                                      <tbody id="productResultBody"></tbody>
                                    </table>
                                  </div>
                                  <div id="productResultPaging" class="d-flex justify-content-between align-items-center border-top p-2" style="display:none;">
                                    <small id="productResultInfo" class="text-muted"></small>
                                    <div>
                                      <button type="button" class="btn btn-sm btn-outline-secondary" id="productResultPrev">&laquo; Sebelumnya</button>
                                      <button type="button" class="btn btn-sm btn-outline-secondary" id="productResultNext">Berikutnya &raquo;</button>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-5">
                          <div class="card card-outline">
                            <div class="card-body">
                              <div class="form-group row mb-2">
                                <label class="col-sm-4 col-form-label"><strong>Tanggal</strong></label>
                                <div class="col-sm-8">
                                  <input type="date" class="form-control" name="tanggal" id="tanggal" value="{{ date('Y-m-d') }}" required>
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

                      <!-- Modal pilih satuan (barcode dengan >1 satuan) -->
                      <div class="modal fade" id="satuanPickerModal" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h5 class="modal-title">Pilih Satuan — <span id="satuanPickerBarang"></span></h5>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            </div>
                            <div class="modal-body" id="satuanPickerBody"></div>
                          </div>
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
                                <label class="col-sm-5 col-form-label"><strong>Diskon (%)</strong></label>
                                <div class="col-sm-7">
                                  <input type="number" step="0.01" name="masterDiskon" id="masterDiskon" class="form-control" value="0" min="0" max="1" required>
                                  <small class="text-muted">Max 1 (100%)</small>
                                </div>
                              </div>
                              <div class="form-group row mb-2">
                                <label class="col-sm-5 col-form-label"><strong>Pajak (%)</strong></label>
                                <div class="col-sm-7">
                                  <input type="number" step="0.01" name="pajak" id="pajak" class="form-control" value="0" min="0" max="1" required>
                                  <small class="text-muted">Max 1 (100%)</small>
                                </div>
                              </div>
                              <div class="form-group row mb-2">
                                <label class="col-sm-5 col-form-label"><strong>PPNBM (%)</strong></label>
                                <div class="col-sm-7">
                                  <input type="number" step="0.01" name="ppnbm" id="ppnbm" class="form-control" value="0" min="0" max="1" required>
                                  <small class="text-muted">Max 1 (100%)</small>
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
                              <div class="text-right mr-4">
                                <p class="mb-1" style="font-size:18px;">Total</p>
                                <h1 class="font-weight-bold mb-0" id="totalDisplay" style="font-size:48px;">0</h1>
                              </div>
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

  <!-- Modal tambah barang baru (OUTSIDE form pembelian) -->
  <div class="modal fade" id="tambahBarangModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Tambah Barang Baru</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <form id="frm-tambah-barang">
          <div class="modal-body">
            <small id="tambahBarangMsg" class="text-danger d-none d-block mb-2"></small>
            <div class="form-group">
              <label>Kode Barang</label>
              <input type="text" class="form-control" name="kd_barang" id="tb_kd_barang">
            </div>
            <div class="form-group">
              <label>Nama Barang</label>
              <input type="text" class="form-control" name="nama" id="tb_nama" required>
            </div>
            <div class="form-group">
              <label>Satuan</label>
              <select class="form-control" name="kd_satuan" id="tb_kd_satuan" required>
                <option value="">-- Pilih Satuan --</option>
              </select>
            </div>
            <div class="form-group mb-0">
              <label>Harga Jual</label>
              <input type="number" class="form-control" name="harga_jual" id="tb_harga_jual" min="0" required>
            </div>
            <div class="form-group mb-0">
              <label>Keterangan</label>
              <input type="text" class="form-control" name="keterangan" id="tb_keterangan" value="-">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary" id="tb_submit"><i class="bi bi-save"></i> Simpan &amp; Tambahkan ke Daftar</button>
          </div>
        </form>
      </div>
    </div>
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
    return Number(angka || 0).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
}

$(document).ready(function () {
    // Tambah 1 baris barang ke tabel. data: {barang, satuan, kd_barang, kd_satuan, harga}
    function addProductRow(data) {
        // Cek apakah barang+satuan sudah ada di tabel
        let existingRow = null;
        $('#productTable tbody tr').each(function () {
            const barangInput = $(this).find('input[name*="[barang]"]');
            const satuanInput = $(this).find('input[name*="[satuan]"]');
            if (barangInput.val() === data.barang && satuanInput.val() === data.satuan) {
                existingRow = this;
                return false;
            }
        });

        if (existingRow) {
            // Jika sudah ada, tambah qty sebesar 1
            const qtyInput = $(existingRow).find('input.qty');
            const currentQty = parseFloat(qtyInput.val()) || 0;
            qtyInput.val(currentQty + 1).trigger('input');
            return;
        }

        // Jika belum ada, tambah baris baru dengan qty = 1
        let html = `
            <tr>
                <td><input class="form-control" type="text" name="products[${rowCount}][barang]" value="${data.barang}" readonly></td>
                <td><input class="form-control" type="text" name="products[${rowCount}][satuan]" value="${data.satuan}" readonly></td>
                <td><input class="form-control harga" type="number" name="products[${rowCount}][harga_beli]" value="${data.harga}" data-row="${rowCount}" required></td>
                <td><input class="form-control diskon_dt" value="0" type="number" name="products[${rowCount}][diskon_dt]" data-row="${rowCount}" required></td>
                <td><input class="form-control qty" type="number" name="products[${rowCount}][qty]" value="1" data-row="${rowCount}" required></td>
                <td><input class="form-control total_harga" type="text" name="products[${rowCount}][total]" data-row="${rowCount}" readonly></td>
                <td class="text-center">
                    <input type="hidden" name="products[${rowCount}][kd_barang]" value="${data.kd_barang}">
                    <input type="hidden" name="products[${rowCount}][kd_satuan]" value="${data.kd_satuan}">
                    <button class="btn btn-danger btn-sm removeRow" type="button"><i class="bi bi-trash"></i> Hapus</button>
                </td>
            </tr>`;

        $('#productTable tbody').append(html);
        rowCount++;
        updateGrandTotal();
    }

    // Ubah baris respons server -> bentuk yang dipakai addProductRow
    function mapServerItem(item) {
        return {
            barang: item.barang,
            satuan: item.satuan,
            kd_barang: item.kd_barang,
            kd_satuan: item.kd_satuan,
            harga: item.harga_beli
        };
    }

    // ===== PILIH BARANG: search box + kotak hasil =====
    // Default: daftar barang urut abjad (A-Z), 10 item/halaman, dengan navigasi
    // Sebelumnya/Berikutnya. Mengetik >= 3 huruf akan memfilter daftar (tetap
    // 10 item/halaman, halaman direset ke 1).
    let productSearchTerm = '';
    let productSearchPage = 1;
    let productSearchTimer = null;
    let productSearchRequest = null;

    function renderProductResults(items, total, page, perPage) {
        total = total || 0;
        page = page || 1;
        perPage = perPage || 10;

        if (!items || !items.length) {
            $('#productResultBody').empty().removeData('items');
            $('#productResultPaging').hide();
            $('#productResultBox').hide();
            return;
        }

        let rows = items.map(function (item, idx) {
            return `
                <tr class="product-result-row" data-idx="${idx}" style="cursor:pointer;">
                    <td>${item.kd_barang}</td>
                    <td>${item.barang}</td>
                    <td>${item.satuan}</td>
                    <td class="text-right">${formatRupiah(item.harga_jual)}</td>
                    <td class="text-right">-</td>
                </tr>`;
        }).join('');
        $('#productResultBody').html(rows).data('items', items);

        const totalPages = Math.max(1, Math.ceil(total / perPage));
        const start = (page - 1) * perPage + 1;
        const end = Math.min(page * perPage, total);
        $('#productResultInfo').text(`${start}-${end} dari ${total} barang`);
        $('#productResultPrev').prop('disabled', page <= 1);
        $('#productResultNext').prop('disabled', page >= totalPages);
        $('#productResultPaging').show();

        $('#productResultBox').show();
    }

    function fetchProductResults(term, page) {
        productSearchTerm = term;
        productSearchPage = page;
        if (productSearchRequest) productSearchRequest.abort();
        productSearchRequest = $.getJSON('/products-list-beli', { q: term, page: page }, function (res) {
            renderProductResults(
                (res && res.dataBarangSatuan) ? res.dataBarangSatuan : [],
                res ? res.total : 0,
                res ? res.page : page,
                res ? res.perPage : 10
            );
        });
    }

    $('#productResultBody').on('click', '.product-result-row', function () {
        const items = $('#productResultBody').data('items') || [];
        const item = items[$(this).data('idx')];
        if (!item) return;
        addProductRow(mapServerItem(item));
        $('#productSearchInput').val('').trigger('focus');
    });

    $('#productResultPrev').on('click', function () {
        if (productSearchPage > 1) fetchProductResults(productSearchTerm, productSearchPage - 1);
    });
    $('#productResultNext').on('click', function () {
        fetchProductResults(productSearchTerm, productSearchPage + 1);
    });

    $('#productSearchInput').on('input', function () {
        const term = $(this).val().trim();
        clearTimeout(productSearchTimer);
        // Kalau user hapus text atau kurang dari 3 karakter, tampilkan default list (top 10 asc)
        if (term.length > 0 && term.length < 3) {
            productSearchTimer = setTimeout(function () {
                fetchProductResults('', 1);
            }, 300);
            return;
        }
        productSearchTimer = setTimeout(function () {
            fetchProductResults(term, 1);
        }, 300);
    });

    // Sembunyikan kotak hasil kalau klik di luar search box / kotak hasil
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#productSearchInput, #productResultBox').length) {
            $('#productResultBox').hide();
        }
    });
    // Fokus search box -> tampilkan daftar (default urut abjad kalau belum pernah dimuat)
    $('#productSearchInput').on('focus', function () {
        if ($('#productResultBody').data('items')) {
            $('#productResultBox').show();
            $('#productResultPaging').show();
        } else {
            fetchProductResults('', 1);
        }
    });

    // ===== TAMBAH BARANG BARU (master barang + barang satuan sekaligus) =====
    let satuanLoaded = false;
    $('#tambahBarangModal').on('show.bs.modal', function () {
        if (satuanLoaded) return;
        $.getJSON('{{ route("edit.master.barang.satuan.data") }}', function (res) {
            const satuan = (res && res.satuan) ? res.satuan : [];
            const $select = $('#tb_kd_satuan');
            satuan.forEach(function (s) {
                $select.append(`<option value="${s.kd_satuan}">${s.nama}</option>`);
            });
            satuanLoaded = true;
        });
    });

    $('#frm-tambah-barang').on('submit', function (e) {
        e.preventDefault();
        const $btn = $('#tb_submit');
        const $msg = $('#tambahBarangMsg');
        $msg.addClass('d-none').text('');
        $btn.prop('disabled', true);

        $.ajax({
            url: '{{ route("input.barang.cepat") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                kd_barang: $('#tb_kd_barang').val(),
                nama: $('#tb_nama').val(),
                kd_satuan: $('#tb_kd_satuan').val(),
                harga_jual: $('#tb_harga_jual').val(),
                keterangan: $('#tb_keterangan').val()
            },
            success: function (res) {
                if (!res || res.success === false) {
                    $msg.removeClass('d-none').text((res && res.message) || 'Gagal menyimpan barang baru.');
                    $btn.prop('disabled', false);
                    return;
                }
                addProductRow(mapServerItem(res));
                $('#tambahBarangModal').modal('hide');
                $('#frm-tambah-barang')[0].reset();
                $('#tb_keterangan').val('-');
                $('#productSearchInput').trigger('focus');
            },
            error: function (xhr) {
                const res = xhr.responseJSON;
                const errorMsg = (res && res.message) ? res.message : ('Gagal menyimpan barang baru. Error: ' + xhr.status);
                console.error('AJAX Error:', xhr.status, res);
                $msg.removeClass('d-none').text(errorMsg);
                $btn.prop('disabled', false);
            }
        });
    });

    $('#tambahBarangModal').on('hidden.bs.modal', function () { openPicker(); });

    // ===== SCAN BARCODE (lewat kolom pencarian) =====
    const BARCODE_URL = '/barang-by-barcode-beli';

    function showBarcodeMsg(text) {
        $('#barcodeMsg').text(text).show();
        setTimeout(function () { $('#barcodeMsg').fadeOut(); }, 2500);
    }

    // Fokuskan kembali search box supaya siap menerima hasil scan berikutnya
    function openPicker() {
        try { $('#productSearchInput').trigger('focus'); } catch (e) {}
    }

    // Tampilkan pilihan satuan kalau 1 barcode punya >1 satuan
    function showSatuanPicker(rows) {
        $('#satuanPickerBarang').text(rows[0].barang);
        let body = '<div class="list-group">';
        rows.forEach(function (item, i) {
            body += `
                <button type="button" class="list-group-item list-group-item-action pick-satuan" data-idx="${i}">
                    <strong>${item.satuan}</strong>
                    <span class="float-right text-muted">Harga beli terakhir: ${formatRupiah(item.harga_beli)}</span>
                </button>`;
        });
        body += '</div>';
        $('#satuanPickerBody').html(body).data('rows', rows);
        $('#satuanPickerModal').modal('show');
    }

    $('#satuanPickerBody').on('click', '.pick-satuan', function () {
        const rows = $('#satuanPickerBody').data('rows') || [];
        const item = rows[$(this).data('idx')];
        if (item) addProductRow(mapServerItem(item));
        $('#satuanPickerModal').modal('hide');
    });
    // setelah modal ditutup, fokus lagi ke search box untuk scan berikutnya
    $('#satuanPickerModal').on('hidden.bs.modal', function () { openPicker(); });

    // Proses kode barcode hasil scan
    function handleBarcode(term, field) {
        $.getJSON(BARCODE_URL, { barcode: term }, function (res) {
            const rows = (res && res.dataBarangSatuan) ? res.dataBarangSatuan : [];
            if (rows.length === 1) {
                addProductRow(mapServerItem(rows[0]));
                $('#productSearchInput').val('');
                renderProductResults([]);
                setTimeout(openPicker, 60);          // siap scan berikutnya
            } else if (rows.length > 1) {
                $('#productSearchInput').val('');
                renderProductResults([]);
                showSatuanPicker(rows);              // pilih satuan dulu
            } else {
                showBarcodeMsg('Barcode "' + term + '" tidak ditemukan.');
                if (field) field.value = '';
            }
        }).fail(function () { showBarcodeMsg('Gagal mencari barcode.'); });
    }

    // Intercept Enter di kolom pencarian.
    // - input mirip barcode (angka, opsional diawali +) -> exact lookup barcode
    // - selain itu, kalau kotak hasil sedang terbuka -> pilih baris pertama
    const BARCODE_RE = /^\+?\d{6,}$/;
    $('#productSearchInput').on('keydown', function (e) {
        if (e.key !== 'Enter' && e.keyCode !== 13) return;
        const term = $(this).val().trim();
        if (!term) return;
        e.preventDefault();
        if (BARCODE_RE.test(term)) {
            handleBarcode(term, this);
            return;
        }
        const items = $('#productResultBody').data('items') || [];
        if (items.length) {
            addProductRow(mapServerItem(items[0]));
            $(this).val('');
            renderProductResults([]);
        }
    });

    // Auto-fokus search box saat pindah ke tab "Input Data"
    $('a[href="#settings"]').on('show.bs.tab', function (e) {
        if ($(e.target).attr('href') === '#settings') {
            setTimeout(function () { openPicker(); }, 100);
        }
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

        let diskonPersen = parseFloat($('#masterDiskon').val()) || 0;
        let pajakPersen  = parseFloat($('#pajak').val())  || 0;
        let ppnbmPersen  = parseFloat($('#ppnbm').val())  || 0;

        // Hitung diskon, pajak dan ppnbm dari persen
        let diskonNilai = subtotal * diskonPersen;
        let pajakNilai = subtotal * pajakPersen;
        let ppnbmNilai = subtotal * ppnbmPersen;

        let grand = subtotal - diskonNilai + pajakNilai + ppnbmNilai;

        $('#grandTotal').val(grand);
        $('#totalDisplay').text(formatRupiah(grand));
    }

    // Validasi diskon, pajak dan ppnbm tidak boleh lebih dari 1
    $('#masterDiskon, #pajak, #ppnbm').on('input change', function () {
        let val = parseFloat($(this).val());
        if (isNaN(val)) val = 0;
        if (val > 1) {
            $(this).val(1);
        } else if (val < 0) {
            $(this).val(0);
        }
        updateGrandTotal();
    });

    $('#productTable').on('click', '.removeRow', function () {
        $(this).closest('tr').remove();
        updateGrandTotal();
    });

    // Submit form pembelian via AJAX, hanya reload tab data pembelian
    $('#frm-input').on('submit', function (e) {
        e.preventDefault();
        const $form = $(this);
        const $btn = $form.find('button[type="submit"]');

        // Validasi: cek ada item dengan harga beli 0
        let hasZeroPrice = false;
        $('#productTable tbody tr').each(function () {
            const harga = parseFloat($(this).find('input.harga').val()) || 0;
            if (harga === 0) {
                hasZeroPrice = true;
                return false;
            }
        });

        if (hasZeroPrice) {
            alert('Mohon isi harga beli untuk semua item. Ada item dengan harga beli 0.');
            return;
        }

        $btn.prop('disabled', true);

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: $form.serialize(),
            success: function (res) {
                if (!res || res.success === false) {
                    alert((res && res.message) || 'Gagal menyimpan pembelian.');
                    $btn.prop('disabled', false);
                    return;
                }

                // Reset form
                $form[0].reset();
                $('#productTable tbody').empty();
                rowCount = 0;
                updateGrandTotal();

                // Switch ke tab "Data Pembelian"
                $('a[href="#activity"]').tab('show');

                // Reload tabel data pembelian dengan delay
                setTimeout(function () {
                    // Cari instance DataTable dari #example2
                    const $table = $('#example2');
                    if ($.fn.dataTable.isDataTable('#example2')) {
                        $table.DataTable().ajax.reload(null, false);
                    }
                }, 500);

                // Generate nomor transaksi baru
                const now = new Date();
                const ymd = now.getFullYear().toString().slice(-2) +
                           String(now.getMonth() + 1).padStart(2, '0') +
                           String(now.getDate()).padStart(2, '0');
                const currentNo = $('#no_transaksi').val();
                const lastNum = parseInt(currentNo.slice(-4)) + 1;
                const newNo = 'BB' + ymd + String(lastNum).padStart(4, '0');
                $('#no_transaksi').val(newNo);

                // Fokus ke search box
                $('#productSearchInput').trigger('focus');

                // Enable button kembali
                $btn.prop('disabled', false);
            },
            error: function (xhr) {
                const res = xhr.responseJSON;
                alert((res && res.message) || 'Gagal menyimpan pembelian.');
                $btn.prop('disabled', false);
            }
        });
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
      { data: 'tanggal', className: 'text-center' },
      { data: 'customer', className: 'text-center' },
      { data: 'jumlah_item', className: 'text-center' },
      { data: 'total_diskon', className: 'text-right', render: function(data) { console.log('DEBUG total_diskon:', data, 'type:', typeof data); const val = parseFloat(String(data).replace(',', '.')) || 0; console.log('DEBUG parsed val:', val); return 'Rp ' + val.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }); } },
      { data: 'total', className: 'text-right', render: function(data) { const val = parseFloat(String(data).replace(',', '.')) || 0; return 'Rp ' + val.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }); } },
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

  // Hapus transaksi pembelian (header + detail) via AJAX
  $('#example2 tbody').on('click', '.hapus-transaksi', function () {
    const noTransaksi = $(this).data('notransaksi');
    if (!confirm('Hapus transaksi ' + noTransaksi + ' beserta seluruh detailnya? Tindakan ini tidak dapat dibatalkan.')) {
      return;
    }
    const $btn = $(this);
    $btn.prop('disabled', true);
    $.ajax({
      url: "{{ route('hapus.pembelian') }}",
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
