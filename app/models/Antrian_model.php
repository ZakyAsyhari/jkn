<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Antrian_model extends CI_Model
{
    private $table = 'antrian_jkn';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function cek_antrian($nik)
    {

        $tgl_sekarang = date('Y-m-d');

        $sql = "SELECT id,kodepoli,nomorkartu,nik,tanggalperiksa,tglinsert
                from antrian_jkn where nik = '$nik' and status in (1,2) and tglinsert = '$tgl_sekarang'";
        $data  = $this->db->query($sql)->row_array();
        return $data;
    }

    public function cek_pasien($norm)
    {

        $sql = "SELECT *
                from 
                    mmr
                where 
                    rm = $norm";
        $data  = $this->db->query($sql)->row_array();
        return $data;
    }

    public function cek_jadwal_dokter($norm)
    {

        $sql = "SELECT *
                from 
                mr_periksa
                where 
                    id = '$norm'";
        $data  = $this->db->query($sql)->row_array();
        return $data;
    }

    public function antrian_insert($data)
    {
        // $days_now = date("D",strtotime(date('d-m-Y')));
        $days_now = date("D", strtotime(date('Y-m-d', strtotime($data['tanggalperiksa']))));
        $time_now = date('01-01-2000 H:i:s');
        $date_time_now = date('d-m-Y H:i:s');
        $date_now = date('d-m-Y');
        $days_num = no_hari($days_now);
        $id_dokter = $data['iddokter'];

        // CHECK PASIEN TELAH DIDAFTARKAN PADA TANGGAL PERIKSA
        $get_antrian_masuk = $this->db->query("SELECT * from antrian_jkn where nomorkartu = '$data[nomorkartu]'
                                        and status in (1,2) and tanggalperiksa = '$data[tanggalperiksa]'")->row();
        if (!empty($get_antrian_masuk)) {
            $solve = array('code' => '2');
            return $solve;
        }


        // GET data Poli
        $get_polis = $this->db->query("SELECT mp.nama,mp.poli,muser.lokal_id as iddokter,muser.nm_user as dokter,mr_j.kondisi as kehadiran,muser.nik
                                        from mr_jadwal_tetap as mr_j
                                        join muser on muser.lokal_id = mr_j.dokter
                                        join mpoli mp on mp.poli = mr_j.poli
                                        where mp.s_name is not null 
                                                and mr_j.hari = $days_num
                                                    and muser.id_extPass = $id_dokter
                                            and upper(mp.s_name)=upper('$data[kodepoli]')
                                        order by mr_j.dokter")->result_array();

        if (empty($get_polis)) {
            $solve = array('code' => '3');
            return $solve;
        } else {
            $random_keys    = array_rand($get_polis, 1);
            $get_poli       = $get_polis[$random_keys];
        }
        $jadwal_nama_poli   = isset($get_poli) ? $get_poli['nama'] : null;
        $idPoli             = isset($get_poli) ? $get_poli['poli'] : null;
        $idDokterrs         = isset($get_poli) ? $get_poli['nik'] : null;

        $data['namapoli']   = isset($get_poli) ? $get_poli['nama'] : null;
        $data['namadokter'] = isset($get_poli) ? $get_poli['dokter'] : null;
        $data['iddokter']   = isset($get_poli) ? $get_poli['iddokter'] : null;

        // generate esitimasi waktu
        $tgl = DateTime::createFromFormat('Y-m-d', $data['tanggalperiksa']);
        $day = $tgl->format('D');
        $no_hari = no_hari($day);
        $get_jadwal = $this->db->query("SELECT min(awal) as mulai from mr_jadwal_tetap where hari=$no_hari and poli='$idPoli'")->row();
        $date = date_create($get_jadwal->mulai);
        $jam_mulai = date_format($date, "H:i:s.u");

        // generate no antrian 
        $tanggalperiksa = $data['tanggalperiksa'];
        $no_antrian = $this->db->query("SELECT max(mr_periksa.nourut)+1 as no,max(antrian_jkn.estimasidilayani) as estimasidilayani  
                                        from mr_periksa
                                        left join antrian_jkn on antrian_jkn.norm = mr_periksa.rm
                                        where upper(mr_periksa.poli)=upper('$idPoli') and mr_periksa.tglperiksa='$tanggalperiksa'")->row();
        if (!empty($no_antrian->no)) {
            $estimasi = (int)$no_antrian->estimasidilayani + (3600 * 100);
            $data['estimasidilayani'] = $estimasi;
            $data['noantrian'] = $no_antrian->no;
        } else {
            // $estimasi = $data['tanggalperiksa'] . ' ' . $jam_mulai;
            $data['noantrian'] = 1;
            $data['estimasidilayani'] =  3600 * 100;
        }

        $data['tanggalperiksa'] = $tanggalperiksa;
        $data['status'] = 1;
        $data['tglinsert'] = date('Y-m-d h:i:s');

        $this->db->insert($this->table, $data);
        $newid = $this->db->query("SELECT max(id)+1 as id from mr_periksa")->row();
        $insPeriksa = array( 'id'           => $newid->id,
                             'tanggal'      => date('Y-m-d'),
                             'poli'         => $idPoli,
                             'rm'           => $data['norm'],
                             'kode_dok'     => $idDokterrs,
                             'nourut'       => $data['noantrian'],
                             'tgldaftar'    => date('Y-m-d H:i:s'),
                             'tglperiksa'   => $data['tanggalperiksa']
    );
        $this->db->insert('mr_periksa', $insPeriksa);
        $return = $this->db->query("SELECT max(id) as id from antrian_jkn")->row();

        $solve = array('id' => $return->id, 'code' => '1');
        return $solve;
    }


    public function cek_waktu_daftar($data)
    {
        $days_now = date("D", strtotime(date('Y-m-d', strtotime($data['tanggalperiksa']))));
        $date_time_now = date('d-m-Y H:i:s');
        $days_num = no_hari($days_now);

        $get_polis = $this->db->query("SELECT MAX(mr_j.akhir) as jamselesai, MAX(mp.nama) as nama 
                                    from mr_jadwal_tetap as mr_j
                                    join muser on muser.nik = mr_j.dokter
                                    join mpoli mp on mp.poli = mr_j.poli
                                    where mp.s_name is not null 
                                        and mr_j.hari = $days_num
                                        and upper(mp.s_name)=upper('$data[kodepoli]')
                                        -- and kehadiran=1
                                    order by mr_j.dokter")->result_array();

        $random_keys    = array_rand($get_polis, 1);
        $get_poli       = $get_polis[$random_keys];

        // if (isset($get_poli['JAMSELESAI'])) {
        $batas_regist   = date('H:i:s', strtotime($get_poli['jamselesai']));
        $jam_regist     = date('H:i:s', strtotime($date_time_now));
        if ($jam_regist > $batas_regist) {
            $cek_jadwal = array('code' => '9');
            $cek_jadwal['jam'] = date('g:i a', strtotime($batas_regist));
            $cek_jadwal['namapoli'] = isset($get_poli) ? $get_poli['nama'] : null;
            return $cek_jadwal;
        }
        // } 
    }

    public function reset_jadwal($data)
    {
        $days_now = date("D", strtotime(date('Y-m-d', strtotime($data['tanggalperiksa']))));
        $time_now = date('H:i');
        $date_time_now = date('d-m-Y H:i:s');
        $date_now = date('d-m-Y');
        $days_num = no_hari($days_now);
        // debug($days_num);
        $get_polis = $this->db->query("SELECT mr_j.kondisi as kehadiran,mr_j.hari,muser.nm_user as dokter
                                    from mr_jadwal_tetap as mr_j
                                    join muser on muser.nik = mr_j.dokter
                                    join mpoli on mpoli.poli = mr_j.poli
                                    where mpoli.s_name is not null 
                                        and mr_j.hari = $days_num
                                        and '$time_now' between trim(mr_j.awal) and trim(mr_j.akhir) 
                                        and muser.id_extPass = $data[iddokter]
                                        and CONCAT_WS('-',mr_j.awal,mr_j.akhir) = '$data[jampraktek]'
                                        and upper(mpoli.s_name)=upper('$data[kodepoli]')
                                    order by mr_j.dokter")->result_array();
        // debug($get_polis);
        // die();
        if (!empty($get_polis)) {
            $random_keys    = array_rand($get_polis, 1);
            $get_poli       = $get_polis[$random_keys];

            if ($get_poli['kehadiran'] == 0) {
                $reset_jadwal = array('code' => 7);
                $reset_jadwal['namadokter'] = isset($get_poli) ? $get_poli['dokter'] : null;
                return $reset_jadwal;
            }
        } else {
            $cek_jadwal = array('code' => 10);
            return $cek_jadwal;
        }
    }



    public function antrian_get($id)
    {
        $this->db->select('*');
        $this->db->where('ID', $id);
        $query = $this->db->get($this->table);
        return $query->row();
    }

    public function set_kuota($data)
    {
        $tgl_antrian = date('Y-m-d', strtotime($data['tanggalperiksa']));
        $getdays     = no_hari(date('D', strtotime($data['tanggalperiksa'])));
        $tgl_antrian = strtoupper($tgl_antrian);

        # total antrian
        $total_antrian = $this->db->select('count(mr_p.ID) as total')
            ->from('mr_periksa mr_p')
            ->join('muser','muser.nik = mr_p.kode_dok')
            ->join('mr_jadwal_tetap mr_j','mr_j.dokter = muser.nik')
            ->join('mpoli','mpoli.poli = mr_j.poli')
            ->like('mr_p.tglperiksa', $tgl_antrian, 'both')
            ->where('muser.id_extPass', $data['iddokter'])
            ->where('mpoli.s_name', $data['kodepoli'])
            ->where("CONCAT_WS('-',mr_j.awal,mr_j.akhir)",$data['jampraktek'])
            // ->where('status','1')
            ->get()
            ->first_row();
        $antrian_total = $total_antrian->total;
        $dokter = $this->db->select('mr_jadwal_tetap.*')
            ->from('mr_jadwal_tetap')
            ->join('mpoli','mpoli.poli=mr_jadwal_tetap.poli')
            ->join('muser','muser.nik=mr_jadwal_tetap.dokter')
            ->where('muser.id_extPass',$data['iddokter'])
            ->where('mr_jadwal_tetap.hari',$getdays)
            ->where('mpoli.s_name', $data['kodepoli'])
            ->where("CONCAT_WS('-',mr_jadwal_tetap.awal,mr_jadwal_tetap.akhir)",$data['jampraktek'])
            ->get()->first_row();
        // $dokter = $this->db->get_where('mr_jadwal_tetap', ['dokter' => $data['iddokter'],'poli' => $data['kode']])->first_row();
        $kuotajkn = !empty($dokter->batas) ? $dokter->batas : 0;
        $kuotanonjkn = !empty($dokter->batas) ? $dokter->batas : 0;
        $set_kuota = array(
            'kuotajkn' => $kuotajkn,
            'kuotanonjkn' => $kuotajkn,
            // 'SISAKUOTAJKN' => 100 - $antrian_total,
            'sisajkn' => $kuotanonjkn - $antrian_total,
            'sisanonjkn' => $kuotanonjkn - $antrian_total
        );

        return $set_kuota;
    }
}
