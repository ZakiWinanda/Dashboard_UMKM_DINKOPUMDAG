<script type="text/javascript">
    $(function () {
        $('.select2').select2();
        $("#btnCari").on("click", function (e) {
            e.preventDefault();
            loadDashboard();
        });
        loadDashboard();
    });


    function loadDashboard()
    {
        $.ajax({
            url: "<?=site_url('dashboard/loadDashboard');?>",
            type: "POST",
            dataType: "json",
            data: {
                tahun: $("#tahun").val(),
                bulan: $("#bulan").val()
            },
            beforeSend: function () {
                $("#btnCari")
                    .prop("disabled", true)
                    .html('<i class="fa fa-spinner fa-spin"></i> Loading');
                $("#tblSwk tbody").html('<tr><td colspan="5" class="text-center"><i class="fa fa-spinner fa-spin"></i> Memuat data...</td></tr>');
                $("#tblMonitoring").html('<tr><td colspan="3" class="text-center"><i class="fa fa-spinner fa-spin"></i> Memuat data...</td></tr>');
            },
            success: function (res) {
                $("#btnCari")
                    .prop("disabled", false)
                    .html('<i class="fa fa-search"></i> Tampilkan');

                if (!res || res.status === false) {
                    alert(res ? res.message : "Data tidak dapat dibaca.");
                    return;
                }

                isiKPI(res);
                isiSwk(res.swk || [], res.is_kecamatan);
                isiMonitoring(res.monitoring || []);
            },
            error: function () {
                $("#btnCari")
                    .prop("disabled", false)
                    .html('<i class="fa fa-search"></i> Tampilkan');
                alert("Terjadi kesalahan pada server.");
            }
        });
    }

    function isiKPI(r)
    {
        var progress = parseFloat(r.progress);
        if (isNaN(progress)) progress = 0;
        if (progress > 100) progress = 100;

        var nilaikinerja = parseFloat(r.nilaikinerja);
        if (isNaN(nilaikinerja)) nilaikinerja = 0;
        if (nilaikinerja > 100) nilaikinerja = 100;

        if(r.label_wilayah) {
            $("#label_total_wilayah").text(r.label_wilayah);
            $("#label_daftar_wilayah").text("Daftar " + r.label_wilayah);
            $("#th_nama_wilayah").text("Nama " + r.label_wilayah);
            if(r.is_kecamatan) {
                $("#icon_total_wilayah").removeClass('fa-store').addClass('fa-map-marker-alt');
                $("#th_stan").hide();
            } else {
                $("#icon_total_wilayah").removeClass('fa-map-marker-alt').addClass('fa-store');
                $("#th_stan").show();
            }
        }

        $("#total_swk").text(r.total_swk);
        $("#sudah").text(r.sudah);
        $("#belum").text(r.belum);
        $("#progress").text(progress);
        $("#nilaikinerja").text(nilaikinerja);
        $("#barProgress")
            .css("width", nilaikinerja + "%")
            .text(nilaikinerja + "%");
    }

    function isiSwk(data, is_kecamatan)
    {
        var html='';
        if(data.length===0){
            html+='<tr>';
            html+='<td colspan="5" class="text-center text-muted">';
            html+='Belum ada data ' + (is_kecamatan ? 'Kecamatan' : 'SWK');
            html+='</td>';
            html+='</tr>';
        }
        else{
            $.each(data,function(i,row){
                var badge='';

                if(parseInt(row.status)===1){
                    badge='<span class="badge badge-success">Sudah</span>';
                }
                else{
                    badge='<span class="badge badge-danger">Belum</span>';
                }
                
                html+='<tr>';
                html+='<td class="text-center">'+(i+1)+'</td>';
                html+='<td>';
                html+='<strong>'+(row.nama_kecamatan || row.nama_swk)+'</strong>';
                
                if(row.alamat && row.alamat !== '-'){
                    html+='<br><small class="text-muted">'+row.alamat+'</small>';
                }

                html+='</td>';
                if(!is_kecamatan) {
                    html+='<td class="text-center">'+row.stan+'</td>';
                }
                html+='<td class="text-center">'+badge+'</td>';
                html+='<td class="text-center">'+row.persentase+'%</td>';
                html+='</tr>';
            });
        }
        $("#tblSwk tbody").html(html);
    }

    function isiMonitoring(data)
    {
        var html='';
        if(data.length===0) {
            html+='<tr>';
            html+='<td colspan="3" class="text-center text-muted">';
            html+='Belum ada monitoring';
            html+='</td>';
            html+='</tr>';
        }
        else{
            $.each(data,function(i,row){
                html+='<tr>';
                html+='<td>'+formatTanggal(row.created_at)+'</td>';
                html+='<td>'+row.nama_swk+'</td>';
                html+='<td>'+namaBulan(row.bulan)+' '+row.tahun+'</td>';
                html+='</tr>';
            });
        }
        $("#tblMonitoring").html(html);
    }

    function formatTanggal(datetime)
    {
        if(!datetime) return "-";
        var t=datetime.split(" ");
        var d=t[0].split("-");
        return d[2]+"-"+d[1]+"-"+d[0];
    }

    function namaBulan(bln)
    {
        var bulan=['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des' ];
        return bulan[parseInt(bln)];
    }

    $(document).on("click",".btn-input",function(){
        var idswk=$(this).data("idswk");
        console.log("Input Monitoring :",idswk);
    });
    $(document).on("click",".btn-edit",function(){
        var idperform=$(this).data("idperform");
        console.log("Edit Monitoring :",idperform);
    });
</script>