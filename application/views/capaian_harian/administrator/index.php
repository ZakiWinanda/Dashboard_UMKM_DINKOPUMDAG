<div class="content-wrapper pb-5">
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
            <?php $this->load->view('capaian_harian/koordinator_pendamping/filter');?>
            <?php $this->load->view('capaian_harian/pendamping/content', array('hide_content_filter' => true));?>
        </div>
    </div>
</div>

</body>
</html>
