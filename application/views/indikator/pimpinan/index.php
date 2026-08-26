  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark"><?=$title;?></h1>
          </div>
        </div>
      </div>
    </div>
    <div class="content">
      <div class="col pb-5">
        <?php $this->load->view('indikator/koordinator_pendamping/filter');?>
		<div class="row">
		<?php $this->load->view('indikator/pendamping/content');?>
		</div>
      </div>
    </div>
  </div>
<?php $this->load->view('footer');?>
<?php $this->load->view('indikator/koordinator_pendamping/script');?>
</body>
</html>
