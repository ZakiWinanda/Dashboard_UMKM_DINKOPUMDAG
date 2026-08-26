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
            <?php $this->load->view('dashboard/pendamping/filter');?>
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
<script src="<?=base_url()?>assets/vendor/chart.js/Chart.min.js"></script>
<?php $this->load->view('dashboard/pendamping/script');?>
</body>
</html>