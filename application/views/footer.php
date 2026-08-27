<footer class="main-footer">
    <div class="footer-bottom clearfix">
        <div class="copyright small">
            <div class="float-right">
                <span>&copy; <?=date('Y');?> - Pemerintah Kota Surabaya</span>
            </div>
        </div>
    </div>
</footer>

<div class="modal" id="modalgantipassword">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ubah Password</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <form enctype="multipart/form-data" id="ubahpassword">
                    <div class="form-group row mb-0">
                        <label class="col-sm-5 col-form-label">Password saat ini</label>
                        <div class="col-sm-7">
                            <input type="password" class="form-control form-control-sm" id="passwordsekarang" name="passwordsekarang" placeholder="Password saat ini" autocomplete="off">
                        </div>
                    </div>
                    <hr>
                    <div class="form-group row mb-0">
                        <label class="col-sm-5 col-form-label">Password baru</label>
                        <div class="col-sm-7">
                            <input type="password" class="form-control form-control-sm" id="passwordbaru1" name="passwordbaru1" placeholder="Password baru" autocomplete="off">
                        </div>
                    </div>
                    <div class="form-group row mb-0">
                        <label class="col-sm-5 col-form-label">Ulangi password baru</label>
                        <div class="col-sm-7">
                            <input type="password" class="form-control form-control-sm" id="passwordbaru2" name="passwordbaru2" placeholder="Ulangi password baru" autocomplete="off">
                        </div>
                    </div>
                    <hr>
                    <div class="row justify-content-between mt-3">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</div>
<script src="<?=base_url();?>assets/vendor/jquery/jquery.min.js"></script>
<script src="<?=base_url();?>assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?=base_url();?>assets/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="<?=base_url();?>assets/vendor/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="<?=base_url();?>assets/vendor/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="<?=base_url();?>assets/vendor/moment/moment.min.js"></script>
<script src="<?=base_url();?>assets/vendor/inputmask/jquery.inputmask.min.js""></script>
<script src="<?=base_url();?>assets/vendor/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<script src="<?=base_url();?>assets/vendor/sweetalert2/sweetalert2.min.js"></script>
<script src="<?=base_url();?>assets/vendor/select2/js/select2.min.js"></script>
<script src="<?=base_url();?>assets/vendor/ckeditor/ckeditor.js"></script>
<script src="<?=base_url();?>assets/vendor/jquery-validation/jquery.validate.min.js"></script>
<script src="<?=base_url();?>assets/vendor/fancybox/jquery.fancybox.min.js"></script>
<script src="<?=base_url();?>assets/vendor/flot/jquery.flot.js"></script>
<script src="<?=base_url();?>assets/vendor/flot/plugins/jquery.flot.resize.js"></script>
<script src="<?=base_url();?>assets/vendor/flot/plugins/jquery.flot.pie.js"></script>
<script src="<?=base_url();?>assets/js/tableToExcel.js"></script>
<script src="<?=base_url();?>assets/js/adminlte.min.js"></script>
<script type="text/javascript">
    console.warn = function() {};
    // Inisialisasi Select2 hanya jika belum diinisialisasi (cegah double container)
    $('select.select2').each(function() {
        if (!$(this).data('select2')) {
            $(this).select2({ width: '100%' });
        }
    });
    $('.uang').inputmask('decimal', {
        alias: 'numeric',
        groupSeparator: '.',
        radixPoint: ',',
        digits: 0,
        autoGroup: true,
        removeMaskOnSubmit: true,
        rightAlign: false
    });

    function logout() {
        Swal.fire({
            title: 'Anda yakin ingin keluar aplikasi?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '<?=base_url('logout');?>';
            }
        });
    }

    $('#ubahpassword').on('submit', function(event) {
        event.preventDefault();
        if(!$(this).valid()) return;
        var formData = new FormData(this);
        $.ajax({
            type: 'POST',
            url: '<?=base_url('dashboard/ubahpassword');?>',
            dataType: 'json',
            cache: false,
            contentType: false,
            processData: false,
            data: formData,
            success: function(response) {
                if(response.status==='success') {
                    $('#ubahpassword')[0].reset();
                    $('#modalgantipassword').modal('toggle');
                }
                Swal.fire({
                    icon: response.status,
                    text: response.pesan,
                    title: response.status,
                });
            }
        });
    });

    $('#ubahpassword').validate({
        ignore: [],
        debug: false,
        rules: {
            passwordsekarang: { required: true, minlength: 5},
            passwordbaru1: { required: true, minlength: 5, equalTo: "#passwordbaru2 "},
            passwordbaru2: { required: true, minlength: 5, equalTo: "#passwordbaru1" },
        },
        errorPlacement: function (error, element) {
            error.addClass('invalid-feedback');
        },
        highlight: function (element, errorClass, validClass) {
            $(element).addClass('is-invalid');
        },
        unhighlight: function (element, errorClass, validClass) {
            $(element).removeClass('is-invalid');
        }
    });
</script>
