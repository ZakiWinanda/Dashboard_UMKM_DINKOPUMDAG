<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Input_harian extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_capaian_harian');
        $this->load->model('M_swk');
        $this->load->model('M_unit_usaha');
    }

    public function index()
    {
        if ($this->is_pimpinan || $this->is_admin) {
            $list_swk = $this->M_swk->get_all_as_array();
        } else {
            $list_swk = $this->M_swk->get_by_pendamping_array($this->nip);
        }

        $data = [
            'title'    => 'INPUT OMSET & KUNJUNGAN HARIAN (EXCEL)',
            'list_swk' => $list_swk,
        ];

        $this->load->view('header', $data);
        $this->load->view('input_harian/index', $data);
        $this->load->view('footer');
        $this->load->view('input_harian/script', $data);
    }

    /**
     * AJAX: Ambil daftar unit usaha & data omset/kunjungan untuk 1 TANGGAL
     */
    public function load_unit_usaha()
    {
        if (!$this->input->is_ajax_request()) show_404();

        $idswk_api   = trim($this->input->post('idswk_api'));
        $idswk_lokal = trim($this->input->post('idswk_lokal'));
        $tanggal     = trim($this->input->post('tanggal')); // YYYY-MM-DD

        // Resolve ID SWK Lokal & API dari m_swk
        $swkRow = $this->db->group_start()
            ->where('idswk', $idswk_lokal)
            ->or_where('api_swk_id', $idswk_lokal)
            ->or_where('idswk', $idswk_api)
            ->or_where('api_swk_id', $idswk_api)
            ->group_end()
            ->get('m_swk')->row();

        if ($swkRow) {
            $idswk_lokal = $swkRow->idswk;
            if (!empty($swkRow->api_swk_id)) $idswk_api = $swkRow->api_swk_id;
        }

        if (empty($tanggal)) {
            $tanggal = date('Y-m-d');
        }

        if (empty($idswk_api)) {
            echo json_encode(['status' => false, 'message' => 'SWK tidak valid.']);
            return;
        }

        $parts = explode('-', $tanggal);
        $tahun = (int)($parts[0] ?? date('Y'));
        $bulan = (int)($parts[1] ?? date('m'));

        $units = $this->M_unit_usaha->get_from_api($idswk_api);

        if (empty($units)) {
            echo json_encode(['status' => false, 'message' => 'Tidak ada unit usaha aktif untuk SWK ini.']);
            return;
        }

        $omset_map     = $this->M_unit_usaha->getOmsetBulan($idswk_lokal, $bulan, $tahun);
        $kunjungan_map = $this->M_unit_usaha->getKunjunganBulan($idswk_lokal, $bulan, $tahun);

        $total_omset     = $this->M_unit_usaha->totalOmsetSwkBulan($idswk_lokal, $bulan, $tahun);
        $total_kunjungan = $this->M_unit_usaha->totalKunjunganSwkBulan($idswk_lokal, $bulan, $tahun);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'          => true,
                'tanggal'         => $tanggal,
                'tahun'           => $tahun,
                'bulan'           => $bulan,
                'units'           => $units,
                'omset'           => $omset_map,
                'kunjungan'       => $kunjungan_map,
                'total_omset'     => $total_omset,
                'total_kunjungan' => $total_kunjungan,
            ]));
    }

    /**
     * Unduh Template Native Excel XML 2003 (.xls) tanpa Pop-Up Warning di MS Excel
     */
    public function download_template()
    {
        $idswk_lokal = trim($this->input->get('idswk_lokal'));
        $idswk_api   = trim($this->input->get('idswk_api'));
        $tanggal     = trim($this->input->get('tanggal'));

        if (empty($tanggal)) {
            $tanggal = date('Y-m-d');
        }

        $swkRow = $this->db->group_start()
            ->where('idswk', $idswk_lokal)
            ->or_where('api_swk_id', $idswk_lokal)
            ->or_where('idswk', $idswk_api)
            ->or_where('api_swk_id', $idswk_api)
            ->group_end()
            ->get('m_swk')->row();

        if ($swkRow) {
            $idswk_lokal = $swkRow->idswk;
            if (!empty($swkRow->api_swk_id)) $idswk_api = $swkRow->api_swk_id;
        }

        $parts = explode('-', $tanggal);
        $tahun = (int)($parts[0] ?? date('Y'));
        $bulan = (int)($parts[1] ?? date('m'));

        $units         = $this->M_unit_usaha->get_from_api($idswk_api);
        $omset_map     = $this->M_unit_usaha->getOmsetBulan($idswk_lokal, $bulan, $tahun);
        $kunjungan_map = $this->M_unit_usaha->getKunjunganBulan($idswk_lokal, $bulan, $tahun);

        $filename = "Template_Input_Harian_" . $tanggal . ".xls";

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
        echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        echo ' xmlns:o="urn:schemas-microsoft-com:office:office"' . "\n";
        echo ' xmlns:x="urn:schemas-microsoft-com:office:excel"' . "\n";
        echo ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        echo ' xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";
        echo ' <Styles>' . "\n";
        echo '  <Style ss:ID="Header">' . "\n";
        echo '   <Font ss:FontName="Calibri" ss:Size="11" ss:Color="#FFFFFF" ss:Bold="1"/>' . "\n";
        echo '   <Interior ss:Color="#1F4E78" ss:Pattern="Solid"/>' . "\n";
        echo '   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>' . "\n";
        echo '  </Style>' . "\n";
        echo '  <Style ss:ID="TextCell">' . "\n";
        echo '   <Font ss:FontName="Calibri" ss:Size="11"/>' . "\n";
        echo '   <NumberFormat ss:Format="@"/>' . "\n";
        echo '  </Style>' . "\n";
        echo '  <Style ss:ID="NumberCell">' . "\n";
        echo '   <Font ss:FontName="Calibri" ss:Size="11"/>' . "\n";
        echo '   <NumberFormat ss:Format="#,##0"/>' . "\n";
        echo '   <Alignment ss:Horizontal="Right"/>' . "\n";
        echo '  </Style>' . "\n";
        echo ' </Styles>' . "\n";

        echo ' <Worksheet ss:Name="Template Input">' . "\n";
        echo '  <Table>' . "\n";
        echo '   <Column ss:Width="250"/>' . "\n";
        echo '   <Column ss:Width="80"/>' . "\n";
        echo '   <Column ss:Width="110"/>' . "\n";
        echo '   <Column ss:Width="160"/>' . "\n";
        echo '   <Column ss:Width="160"/>' . "\n";
        echo '   <Column ss:Width="100"/>' . "\n";
        echo '   <Column ss:Width="100"/>' . "\n";
        echo '   <Column ss:Width="100"/>' . "\n";

        // Header Row
        echo '   <Row ss:StyleID="Header" ss:Height="24">' . "\n";
        echo '    <Cell><Data ss:Type="String">id_unit_usaha</Data></Cell>' . "\n";
        echo '    <Cell><Data ss:Type="String">kode_stand</Data></Cell>' . "\n";
        echo '    <Cell><Data ss:Type="String">kode_usaha</Data></Cell>' . "\n";
        echo '    <Cell><Data ss:Type="String">nama_usaha</Data></Cell>' . "\n";
        echo '    <Cell><Data ss:Type="String">nama_pedagang</Data></Cell>' . "\n";
        echo '    <Cell><Data ss:Type="String">tanggal</Data></Cell>' . "\n";
        echo '    <Cell><Data ss:Type="String">omset</Data></Cell>' . "\n";
        echo '    <Cell><Data ss:Type="String">kunjungan</Data></Cell>' . "\n";
        echo '   </Row>' . "\n";

        // Data Rows
        foreach ($units as $u) {
            $uid = $u['id'];
            $om  = $omset_map[$uid][$tanggal] ?? 0;
            $kj  = $kunjungan_map[$uid][$tanggal] ?? 0;

            echo '   <Row>' . "\n";
            echo '    <Cell ss:StyleID="TextCell"><Data ss:Type="String">' . htmlspecialchars($uid) . '</Data></Cell>' . "\n";
            echo '    <Cell ss:StyleID="TextCell"><Data ss:Type="String">' . htmlspecialchars($u['namaStand']) . '</Data></Cell>' . "\n";
            echo '    <Cell ss:StyleID="TextCell"><Data ss:Type="String">' . htmlspecialchars($u['kodeUsahaSwk']) . '</Data></Cell>' . "\n";
            echo '    <Cell ss:StyleID="TextCell"><Data ss:Type="String">' . htmlspecialchars($u['namaUsaha']) . '</Data></Cell>' . "\n";
            echo '    <Cell ss:StyleID="TextCell"><Data ss:Type="String">' . htmlspecialchars($u['namaPedagang']) . '</Data></Cell>' . "\n";
            echo '    <Cell ss:StyleID="TextCell"><Data ss:Type="String">' . htmlspecialchars($tanggal) . '</Data></Cell>' . "\n";
            echo '    <Cell ss:StyleID="NumberCell"><Data ss:Type="Number">' . (int)$om . '</Data></Cell>' . "\n";
            echo '    <Cell ss:StyleID="NumberCell"><Data ss:Type="Number">' . (int)$kj . '</Data></Cell>' . "\n";
            echo '   </Row>' . "\n";
        }

        echo '  </Table>' . "\n";
        echo ' </Worksheet>' . "\n";
        echo '</Workbook>' . "\n";
        exit;
    }

    /**
     * Upload & Process File Excel / CSV (.xlsx, .xls, .csv)
     */
    public function upload_excel()
    {
        if (!$this->input->is_ajax_request()) show_404();

        $monev       = $this->session->userdata('monev_swk');
        $nip         = $monev['nip'] ?? '';
        $idswk_lokal = trim($this->input->post('idswk_lokal'));
        $tanggal     = trim($this->input->post('tanggal')); // YYYY-MM-DD

        // Resolve ke local idswk yang konsisten
        $swkRow = $this->db->group_start()
            ->where('idswk', $idswk_lokal)
            ->or_where('api_swk_id', $idswk_lokal)
            ->group_end()
            ->get('m_swk')->row();

        if ($swkRow) {
            $idswk_lokal = $swkRow->idswk;
        }

        if (empty($idswk_lokal)) {
            echo json_encode(['status' => false, 'message' => 'SWK wajib dipilih.']);
            return;
        }

        if (empty($_FILES['file_excel']['tmp_name'])) {
            echo json_encode(['status' => false, 'message' => 'File Excel/CSV belum dipilih.']);
            return;
        }

        $tmpFile = $_FILES['file_excel']['tmp_name'];
        $rows    = $this->_parseFileToRows($tmpFile);

        if (empty($rows) || count($rows) < 2) {
            echo json_encode(['status' => false, 'message' => 'File kosong atau format file tidak dapat dibaca.']);
            return;
        }

        // Tentukan indeks kolom secara presisi
        $headerRowIdx = -1;
        $idCol        = 0;
        $standCol     = 1;
        $kodeCol      = 2;
        $nameCol      = 3;
        $tglCol       = 5;
        $omCol        = 6;
        $kjCol        = 7;

        // Cari lokasi baris header yang tepat di antara 5 baris pertama
        for ($r = 0; $r < min(5, count($rows)); $r++) {
            $rowCells = $rows[$r];
            if (!is_array($rowCells)) continue;

            foreach ($rowCells as $idx => $cellVal) {
                $clean = strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '', (string)$cellVal)));
                if ($clean === 'id_unit_usaha' || $clean === 'idunitusaha' || $clean === 'id_unit') {
                    $headerRowIdx = $r;
                    break 2;
                }
            }
        }

        // Jika baris header ditemukan, petakan indeks kolom secara presisi
        if ($headerRowIdx >= 0 && isset($rows[$headerRowIdx])) {
            foreach ($rows[$headerRowIdx] as $idx => $cellVal) {
                $clean = strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '', (string)$cellVal)));
                if ($clean === 'id_unit_usaha' || $clean === 'idunitusaha' || $clean === 'id_unit') {
                    $idCol = $idx;
                } elseif ($clean === 'kode_stand' || $clean === 'kodestand' || $clean === 'stand') {
                    $standCol = $idx;
                } elseif ($clean === 'kode_usaha' || $clean === 'kodeusaha') {
                    $kodeCol = $idx;
                } elseif ($clean === 'nama_usaha' || $clean === 'namausaha') {
                    $nameCol = $idx;
                } elseif ($clean === 'tanggal' || $clean === 'tgl') {
                    $tglCol = $idx;
                } elseif ($clean === 'omset') {
                    $omCol = $idx;
                } elseif ($clean === 'kunjungan' || $clean === 'jumlah') {
                    $kjCol = $idx;
                }
            }
        }

        $startDataIdx = ($headerRowIdx >= 0) ? ($headerRowIdx + 1) : 1;

        $batchOmset     = [];
        $batchKunjungan = [];
        $rowCount       = 0;

        // Loop data baris per baris
        for ($i = $startDataIdx; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (empty($row)) continue;

            $uid    = trim((string)($row[$idCol] ?? ''));
            $tglRow = isset($row[$tglCol]) ? trim((string)$row[$tglCol]) : $tanggal;

            // Validasi UID harus berupa UUID (panjang minimal 20 karakter)
            if (empty($uid) || strlen($uid) < 20) continue;

            $tglFormatted = $this->_parseDate($tglRow);
            if (!$tglFormatted) $tglFormatted = $tanggal;
            if (empty($tglFormatted)) continue;

            $stand = isset($row[$standCol]) ? trim((string)$row[$standCol]) : '';
            $kode  = isset($row[$kodeCol])  ? trim((string)$row[$kodeCol])  : '';
            $nama  = isset($row[$nameCol])  ? trim((string)$row[$nameCol])  : '';

            // Omset
            if (isset($row[$omCol])) {
                $rawOm = trim((string)$row[$omCol]);
                $omVal = (int)preg_replace('/[^\d]/', '', $rawOm);
                $batchOmset[] = [
                    'id_unit_usaha'  => $uid,
                    'nama_unit_usaha'=> $nama,
                    'kode_unit_usaha'=> $kode,
                    'nama_stand'     => $stand,
                    'idswk'          => $idswk_lokal,
                    'tanggal'        => $tglFormatted,
                    'omset'          => $omVal,
                    'created_by'     => $nip,
                ];
            }

            // Kunjungan
            if (isset($row[$kjCol])) {
                $rawKj = trim((string)$row[$kjCol]);
                $kjVal = (int)preg_replace('/[^\d]/', '', $rawKj);
                $batchKunjungan[] = [
                    'id_unit_usaha'  => $uid,
                    'nama_unit_usaha'=> $nama,
                    'kode_unit_usaha'=> $kode,
                    'nama_stand'     => $stand,
                    'idswk'          => $idswk_lokal,
                    'tanggal'        => $tglFormatted,
                    'jumlah'         => $kjVal,
                    'created_by'     => $nip,
                ];
            }

            $rowCount++;
        }

        $savedOmset = $this->M_unit_usaha->saveBatchOmset($batchOmset);
        $savedKj    = $this->M_unit_usaha->saveBatchKunjungan($batchKunjungan);

        $parts = explode('-', $tanggal);
        $tahun = (int)($parts[0] ?? date('Y'));
        $bulan = (int)($parts[1] ?? date('m'));

        $total_omset     = $this->M_unit_usaha->totalOmsetSwkBulan($idswk_lokal, $bulan, $tahun);
        $total_kunjungan = $this->M_unit_usaha->totalKunjunganSwkBulan($idswk_lokal, $bulan, $tahun);

        echo json_encode([
            'status'          => true,
            'message'         => 'Impor berhasil! Total ' . $rowCount . ' unit usaha diperbarui.',
            'total_omset'     => $total_omset,
            'total_kunjungan' => $total_kunjungan,
        ]);
    }

    /**
     * Reader Multi-Format (Native Excel XML, XLSX Zip, HTML Table, CSV)
     */
    private function _parseFileToRows($filePath)
    {
        $rows = [];
        $content = file_get_contents($filePath);

        if (empty($content)) return [];

        // 1. Coba XLSX (Zip XML Excel)
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            if ($zip->open($filePath) === TRUE) {
                $sharedStrings = [];
                $ssXml = $zip->getFromName('xl/sharedStrings.xml');
                if ($ssXml) {
                    $ssDom = @simplexml_load_string($ssXml);
                    if ($ssDom && isset($ssDom->si)) {
                        foreach ($ssDom->si as $si) {
                            if (isset($si->t)) {
                                $sharedStrings[] = (string)$si->t;
                            } elseif (isset($si->r)) {
                                $t = '';
                                foreach ($si->r as $r) {
                                    $t .= (string)$r->t;
                                }
                                $sharedStrings[] = $t;
                            } else {
                                $sharedStrings[] = '';
                            }
                        }
                    }
                }

                $sheetXmlName = 'xl/worksheets/sheet1.xml';
                if ($zip->locateName($sheetXmlName) === false) {
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $stat = $zip->statIndex($i);
                        if (strpos($stat['name'], 'xl/worksheets/sheet') !== false) {
                            $sheetXmlName = $stat['name'];
                            break;
                        }
                    }
                }

                $sheetXml = $zip->getFromName($sheetXmlName);
                if ($sheetXml) {
                    $sheetDom = @simplexml_load_string($sheetXml);
                    if ($sheetDom && isset($sheetDom->sheetData->row)) {
                        foreach ($sheetDom->sheetData->row as $rowNode) {
                            $row = [];
                            foreach ($rowNode->c as $cell) {
                                $rAttr = (string)($cell->attributes()['r'] ?? '');
                                $cIdx  = $rAttr ? $this->_colLetterToIdx($rAttr) : count($row);

                                $attr = $cell->attributes();
                                $type = (string)($attr['t'] ?? '');
                                $val  = (string)($cell->v ?? '');

                                if ($type === 's' && isset($sharedStrings[(int)$val])) {
                                    $val = $sharedStrings[(int)$val];
                                } elseif ($type === 'inlineStr' && isset($cell->is->t)) {
                                    $val = (string)$cell->is->t;
                                }

                                $row[$cIdx] = trim($val);
                            }

                            if (!empty($row)) {
                                $maxIdx = max(array_keys($row));
                                $cleanRow = [];
                                for ($k = 0; $k <= $maxIdx; $k++) {
                                    $cleanRow[$k] = $row[$k] ?? '';
                                }
                                $rows[] = $cleanRow;
                            }
                        }
                    }
                }
                $zip->close();
                if (!empty($rows)) return $rows;
            }
        }

        // 2. Coba XML / HTML Table (.xls / Native XML 2003)
        if (strpos($content, '<tr') !== false || strpos($content, '<table') !== false || strpos($content, '<Row') !== false || strpos($content, '<row') !== false) {
            $dom = new DOMDocument();
            @$dom->loadHTML('<?xml encoding="utf-8"?>' . $content);

            $trList = $dom->getElementsByTagName('row');
            if ($trList->length == 0) {
                $trList = $dom->getElementsByTagName('tr');
            }

            foreach ($trList as $tr) {
                $r = [];
                $cells = $tr->getElementsByTagName('cell');
                if ($cells->length == 0) {
                    $cells = $tr->getElementsByTagName('td');
                    if ($cells->length == 0) {
                        $cells = $tr->getElementsByTagName('th');
                    }
                }
                foreach ($cells as $cell) {
                    $r[] = trim($cell->nodeValue);
                }
                if (!empty($r)) $rows[] = $r;
            }
            if (!empty($rows)) return $rows;
        }

        // 3. Fallback: CSV / Text File
        $handle = fopen($filePath, 'r');
        if ($handle) {
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") rewind($handle);

            $firstLine = fgets($handle);
            rewind($handle);
            if ($bom === "\xEF\xBB\xBF") fread($handle, 3);

            $delimiter = (substr_count($firstLine, ';') >= substr_count($firstLine, ',')) ? ';' : ',';
            if (substr_count($firstLine, "\t") > substr_count($firstLine, $delimiter)) {
                $delimiter = "\t";
            }

            while (($data = fgetcsv($handle, 4096, $delimiter)) !== false) {
                if (!empty($data)) $rows[] = $data;
            }
            fclose($handle);
        }

        return $rows;
    }

    private function _colLetterToIdx($ref)
    {
        $col = strtoupper(preg_replace('/[^A-Z]/i', '', $ref));
        $idx = 0;
        for ($i = 0; $i < strlen($col); $i++) {
            $idx = $idx * 26 + (ord($col[$i]) - ord('A') + 1);
        }
        return max(0, $idx - 1);
    }

    private function _parseDate($dateStr)
    {
        $dateStr = trim((string)$dateStr);
        if (empty($dateStr)) return false;

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) {
            return $dateStr;
        }

        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $dateStr, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }

        if (is_numeric($dateStr) && (int)$dateStr > 30000) {
            return date('Y-m-d', ((int)$dateStr - 25569) * 86400);
        }

        $ts = strtotime($dateStr);
        return $ts ? date('Y-m-d', $ts) : false;
    }
}
