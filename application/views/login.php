<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <meta name="googlebot" content="noindex">
    <title>LOGIN MONITORING DAN EVALUASI KINERJA PENDAMPING SENTRA WISATA KULINER (SWK)</title>
    <link rel="stylesheet" href="<?=base_url();?>assets/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?=base_url();?>assets/vendor/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="<?=base_url();?>assets/vendor/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
    <link rel="stylesheet" href="<?=base_url();?>assets/css/adminlte.min.css">
    <style>
    html,
    body{
        height:100%;
        margin:0;
        font-family:'Source Sans Pro',sans-serif;
    }

    body{
        background:#f4f6f9;
    }

    .login-page{
        min-height:100vh;
    }

    .left-panel{
        min-height:100vh;
        background: linear-gradient(180deg, #ffffffe0, #0b8f3f40), url("<?=base_url('assets/img/bg_swk.jpg');?>") left center;
        background-size:cover;
        color:#333;
        display:flex;
        align-items:center;
        justify-content:center;
        padding:60px;
    }

    .left-content{
        max-width:600px;
    }

    .left-content h1{
        font-size:38px;
        font-weight:700;
        line-height:1.3;
    }

    .left-content p{
        font-size:18px;
        opacity:.9;
    }

    .left-content img{
        width:120px;
        margin-bottom:30px;
    }

    .right-panel{
        min-height:100vh;
        display:flex;
        align-items:center;
        justify-content:center;
        background: linear-gradient(88deg, #ffffffe0, #0b8f3f40), url("<?=base_url('assets/img/bg_login.jpg');?>") center right;
    }

    .login-card{
        width:100%;
        max-width:430px;
    }

    .logo{
        width:90px;
    }

    .card{
        border:none;
        border-radius:15px;
    }

    .card-body{
        padding:40px;
    }

    .input-group-text{
        cursor:pointer;
    }

    .login-title{
        font-weight:700;
        color:#198754;
    }

    @media(max-width:991px){

        .left-panel{
            display:none;
        }

        .right-panel{
            width:100%;
        }

    }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row login-page">
        <div class="col-lg-7 left-panel">
            <div class="left-content text-center">
                <h1>
                    Monitoring dan Evaluasi Kinerja<br>
                    Pendamping Sentra Wisata Kuliner
                </h1>
            </div>
        </div>

        <div class="col-lg-5 right-panel">
            
            <div class="login-card p-2">
                <div class="text-center mb-4">
                    <img src="<?=base_url('assets/img/logo_pemkot.png');?>" class="logo mb-3">
                    <h3 class="login-title">LOGIN</h3>
                    <p class="text-muted mb-0">Silakan masuk menggunakan akun Anda</p>
                </div>

                <?php
                $flash = $this->session->flashdata('pemberitahuan');
                $username = '';

                if (!empty($flash)) {
                    $flash = is_string($flash) ? json_decode($flash, true) : $flash;
                    $username = isset($flash['username']) ? htmlspecialchars($flash['username'], ENT_QUOTES, 'UTF-8') : '';
                }
                ?>

                <form id="login" method="post">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" name="input_username" placeholder="NIP" value="<?=$username;?>" required>
                        <div class="input-group-append"><span class="input-group-text"><i class="fas fa-user"></i></span></div>
                    </div>

                    <div class="input-group mb-3">
                        <input type="password" class="form-control" id="input_password" name="input_password" placeholder="Password" required>
                        <div class="input-group-append">
                            <span class="input-group-text" id="togglePassword"><i class="fas fa-eye" id="iconPassword"></i></span>
                        </div>
                    </div>

                    <div class="input-group mb-3">
                        <p class="mb-2 pr-2">
                            <?=$captcha;?>
                        </p>
                        <div class="input-group-append">
                            <input type="text" class="form-control" name="captcha" placeholder="Captcha" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <button class="btn btn-success btn-block" id="tombol_masuk"><i class="fas fa-sign-in-alt"></i> MASUK</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
<script src="<?=base_url();?>assets/vendor/jquery/jquery.min.js"></script>
<script src="<?=base_url();?>assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?=base_url();?>assets/vendor/sweetalert2/sweetalert2.min.js"></script>
<script type="text/javascript">
$('#togglePassword').click(function(){
    var input = $('#input_password');
    var icon  = $('#iconPassword');
    if(input.attr('type')=='password'){
        input.attr('type','text');
        icon.removeClass('fa-eye')
            .addClass('fa-eye-slash');
    }
    else{
        input.attr('type','password');
        icon.removeClass('fa-eye-slash')
            .addClass('fa-eye');
    }
});


$('#login').submit(function(){
    $('#tombol_masuk')
        .prop('disabled',true)
        .html('<i class="fas fa-spinner fa-spin"></i> Memproses...');
});

<?php if (!empty($flash)) : ?>
    Swal.fire({
        icon: '<?=htmlspecialchars($flash['status'], ENT_QUOTES, 'UTF-8');?>',
        text: '<?=htmlspecialchars($flash['pesan'], ENT_QUOTES, 'UTF-8');?>'
    });
<?php endif; ?>

$('#login').on('submit', function() {
    $('#tombol_masuk').html('<i class="fa fa-sync fa-spin"></i> Loading...');
    $('#tombol_masuk').prop('disabled', true);
});
</script>

</body>
</html>