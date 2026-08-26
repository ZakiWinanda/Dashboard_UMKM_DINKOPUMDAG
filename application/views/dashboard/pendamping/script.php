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
                isiSwk(res.swk || []);
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

        $("#total_swk").text(r.total_swk);
        $("#sudah").text(r.sudah);
        $("#belum").text(r.belum);
        $("#progress").text(progress);
        $("#nilaikinerja").text(nilaikinerja);
        $("#barProgress")
            .css("width", nilaikinerja + "%")
            .text(nilaikinerja + "%");
    }

    function isiSwk(data)
    {
        var html='';
        if(data.length===0){
            html+='<tr>';
            html+='<td colspan="5" class="text-center text-muted">';
            html+='Belum ada data SWK';
            html+='</td>';
            html+='</tr>';
        }
        else{
            $.each(data,function(i,row){
                var badge='';
                var tombol='';

                if(parseInt(row.status)===1){
                    badge='<span class="badge badge-success">Sudah</span>';
                    tombol=
                    '<button class="btn btn-warning btn-sm btn-edit" '+
                    'data-idperform="'+row.idperform+'" '+
                    'data-idswk="'+row.idswk+'">'+
                    '<i class="fa fa-edit"></i> Edit'+
                    '</button>';

                }
                else{
                    badge='<span class="badge badge-danger">Belum</span>';
                    tombol=
                    '<button class="btn btn-primary btn-sm btn-input" '+
                    'data-idswk="'+row.idswk+'">'+
                    '<i class="fa fa-plus"></i> Isi'+
                    '</button>';
                }
                
                html+='<tr>';
                html+='<td>'+(i+1)+'</td>';
                html+='<td>';
                html+='<strong>'+row.nama_swk+'</strong>';
                
                if(row.alamat){
                    html+='<br><small class="text-muted">'+row.alamat+'</small>';
                }

                html+='</td>';
                html+='<td class="text-center">'+row.stan+'</td>';
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