<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Antrian_model extends CI_Model
{
    private $table = 'mr_periksa';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function set_kuota($data)
    {
        $tgl_antrian = date('d-M-y', strtotime($data['tglperiksa']));
        $tgl_antrian = strtoupper($tgl_antrian);

        # total antrian
        // $total_antrian = $this->db->select('count(ID) as total')
        //     ->like('tglmasuk', $tgl_antrian, 'both')
        //     ->where('lokal_Id', $data['idDokter'])
        //     ->get('mr_periksa')
        //     ->first_row();
        // $antrian_total = $total_antrian->total;

        $dokter = $this->db->get_where('mr_jadwal_tetap', ['dokter' => $data['idDokter']])->first_row();
        $kuotajkn = !empty($dokter_unit->batas) ? $dokter_unit->batas : 0;
        $kuotanonjkn = !empty($dokter_unit->batas) ? $dokter_unit->batas : 0;
        $set_kuota = array(
            'kuotajkn' => $kuotajkn,
            'kuotanonjkn' => $kuotanonjkn,
            // 'SISAKUOTAJKN' => 100 - $antrian_total,
            'sisajkn' => $kuotajkn,
            'sisanonjkn' => $kuotajkn
        );

        return $set_kuota;
    }
}
