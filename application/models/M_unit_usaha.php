<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_unit_usaha extends CI_Model
{
    private $api_base_url = 'https://dinkop-umkm.live-energeek.id/api';

    // ──────────────────────────────────────────────
    //  API HELPERS
    // ──────────────────────────────────────────────

    private function _api_token()
    {
        $payload = json_encode([
            'username' => 'api_integration',
            'password' => 'Integration@2026!'
        ]);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => $this->api_base_url . '/auth/login',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json','Accept: application/json'],
        ]);

        $response = curl_exec($curl);
        $err      = curl_error($curl);
        curl_close($curl);

        if ($err || empty($response)) {
            log_message('error', 'M_unit_usaha API login error: ' . $err);
            return false;
        }

        $result = json_decode($response, true);
        return $result['data']['token']['accessToken'] ?? false;
    }

    /**
     * Ambil daftar unit usaha milik satu SWK dari API eksternal.
     * @param string $swk_id  UUID SWK di sistem API
     * @param int    $per_page Jumlah per halaman (default 200)
     * @return array  [ ['id'=>..., 'namaUsaha'=>..., 'kodeUsahaSwk'=>..., 'namaStand'=>...], ... ]
     */
    public function get_from_api($swk_id, $per_page = 200)
    {
        $token = $this->_api_token();
        if (!$token) return [];

        $all   = [];
        $page  = 1;

        do {
            $url = $this->api_base_url . '/integration/swk-usaha?' . http_build_query([
                'swk_id'   => $swk_id,
                'per_page' => $per_page,
                'page'     => $page,
            ]);

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL            => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_CUSTOMREQUEST  => 'GET',
                CURLOPT_HTTPHEADER     => [
                    'Authorization: Bearer ' . $token,
                    'Accept: application/json',
                ],
            ]);

            $response = curl_exec($curl);
            curl_close($curl);

            $result   = json_decode($response, true);
            $items    = $result['data']['data'] ?? [];
            $lastPage = $result['data']['pagination']['lastPage'] ?? 1;

            foreach ($items as $item) {
                if (!($item['statusAktif'] ?? true)) continue;
                $all[] = [
                    'id'              => $item['id'],
                    'kodeUsahaSwk'    => $item['kodeUsahaSwk'] ?? '',
                    'namaUsaha'       => $item['namaUsaha'] ?? '',
                    'namaStand'       => $item['stand']['namaStand'] ?? '',
                    'namaPedagang'    => $item['pedagang']['nama'] ?? '',
                    'kategoriProduk'  => $item['kategoriProduk'] ?? '',
                ];
            }

            $page++;
        } while ($page <= $lastPage && count($all) < 500);

        // Urutkan berdasarkan nomor stand
        usort($all, function($a, $b) {
            return strnatcmp($a['namaStand'], $b['namaStand']);
        });

        return $all;
    }

    // ──────────────────────────────────────────────
    //  OMSET PER UNIT USAHA
    // ──────────────────────────────────────────────

    public function saveOmset($data)
    {
        $cek = $this->db
            ->where('id_unit_usaha', $data['id_unit_usaha'])
            ->where('tanggal', $data['tanggal'])
            ->get('t_omset_unit_usaha')
            ->row();

        if ($cek) {
            $this->db
                ->where('id', $cek->id)
                ->update('t_omset_unit_usaha', [
                    'omset'      => $data['omset'],
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
        } else {
            $this->db->insert('t_omset_unit_usaha', $data);
        }
    }

    /** Ambil omset per unit usaha untuk 1 SWK, 1 bulan
     *  Returns: [ 'id_unit_usaha' => [ 'YYYY-MM-DD' => nominal, ... ], ... ] */
    public function getOmsetBulan($idswk, $bulan, $tahun)
    {
        $rows = $this->db
            ->select('id_unit_usaha, tanggal, omset')
            ->where('idswk', $idswk)
            ->where('MONTH(tanggal)', (int)$bulan, false)
            ->where('YEAR(tanggal)',  (int)$tahun,  false)
            ->where('omset >', 0)
            ->get('t_omset_unit_usaha')
            ->result();

        $map = [];
        foreach ($rows as $r) {
            $map[$r->id_unit_usaha][$r->tanggal] = (int)$r->omset;
        }
        return $map;
    }

    /** Total omset seluruh unit usaha dalam 1 SWK untuk 1 bulan */
    public function totalOmsetSwkBulan($idswk, $bulan, $tahun)
    {
        $q = $this->db
            ->select_sum('omset')
            ->where('idswk', $idswk)
            ->where('MONTH(tanggal)', (int)$bulan, false)
            ->where('YEAR(tanggal)',  (int)$tahun,  false)
            ->get('t_omset_unit_usaha')
            ->row();
        return (float)($q->omset ?? 0);
    }

    // ──────────────────────────────────────────────
    //  KUNJUNGAN PER UNIT USAHA
    // ──────────────────────────────────────────────

    public function saveKunjungan($data)
    {
        $cek = $this->db
            ->where('id_unit_usaha', $data['id_unit_usaha'])
            ->where('tanggal', $data['tanggal'])
            ->get('t_kunjungan_unit_usaha')
            ->row();

        if ($cek) {
            $this->db
                ->where('id', $cek->id)
                ->update('t_kunjungan_unit_usaha', [
                    'jumlah'     => $data['jumlah'],
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
        } else {
            $this->db->insert('t_kunjungan_unit_usaha', $data);
        }
    }

    /** Returns: [ 'id_unit_usaha' => [ 'YYYY-MM-DD' => jumlah, ... ], ... ] */
    public function getKunjunganBulan($idswk, $bulan, $tahun)
    {
        $rows = $this->db
            ->select('id_unit_usaha, tanggal, jumlah')
            ->where('idswk', $idswk)
            ->where('MONTH(tanggal)', (int)$bulan, false)
            ->where('YEAR(tanggal)',  (int)$tahun,  false)
            ->where('jumlah >', 0)
            ->get('t_kunjungan_unit_usaha')
            ->result();

        $map = [];
        foreach ($rows as $r) {
            $map[$r->id_unit_usaha][$r->tanggal] = (int)$r->jumlah;
        }
        return $map;
    }

    public function totalKunjunganSwkBulan($idswk, $bulan, $tahun)
    {
        $q = $this->db
            ->select_sum('jumlah')
            ->where('idswk', $idswk)
            ->where('MONTH(tanggal)', (int)$bulan, false)
            ->where('YEAR(tanggal)',  (int)$tahun,  false)
            ->get('t_kunjungan_unit_usaha')
            ->row();
        return (int)($q->jumlah ?? 0);
    }

    public function saveBatchOmset($batch)
    {
        if (empty($batch)) return 0;
        $count = 0;
        foreach ($batch as $data) {
            $this->saveOmset($data);
            $count++;
        }
        return $count;
    }

    public function saveBatchKunjungan($batch)
    {
        if (empty($batch)) return 0;
        $count = 0;
        foreach ($batch as $data) {
            $this->saveKunjungan($data);
            $count++;
        }
        return $count;
    }
}

