<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><?=$title?></h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <?php $this->load->view('dashboard/pimpinan/filter');?>
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-info">Dashboard terakhir diperbarui : <strong id="last_update">-</strong></div>
                </div>
            </div>
            <?php $this->load->view('dashboard/pendamping/kpi');?>
            <div class="row">
                <div class="col-lg-7">
                    <?php $this->load->view('dashboard/pendamping/swk_saya');?>
                </div>
                <div class="col-lg-5">
                    <?php $this->load->view('dashboard/pendamping/monitoring');?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('footer');?>
<?php $this->load->view('dashboard/pimpinan/script');?>
</body>
</html>