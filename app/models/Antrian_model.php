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
                                        and status in (1,2) and tanggalperiksa = '$data[tanggalperiksa]' and lower(kodepoli) = lower('$data[kodepoli]') and jampraktek = '$data[jampraktek]'")->row();
        // debug($get_antrian_masuk);
        if (!empty($get_antrian_masuk)) {
            $solve = array('id'=>$get_antrian_masuk->id ,'code' => '2');
            return $solve;
        }


        // GET data Poli
        $get_polis = $this->db->query("SELECT mp.nama,mp.poli,muser.lokal_id as iddokter,muser.nm_user as dokter,mr_j.kondisi as kehadiran,muser.nik
                                        from mr_jadwal_hfis as mr_j
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
        $get_jadwal = $this->db->query("SELECT min(awal) as mulai from mr_jadwal_hfis where hari=$no_hari and poli='$idPoli'")->row();
        $date = date_create($get_jadwal->mulai);
        $jam_mulai = date_format($date, "H:i:s.u");

        // generate no antrian ori
        $tanggalperiksa = $data['tanggalperiksa'];
        //$no_antrian = $this->db->query("SELECT max(mr_periksa.nourut)+1 as no,max(antrian_jkn.estimasidilayani) as estimasidilayani  
        //                                from mr_periksa
        //                                left join antrian_jkn on antrian_jkn.norm = mr_periksa.rm
        //                                where mr_periksa.tglperiksa='$tanggalperiksa'")->row();
                                        // upper(mr_periksa.poli)=upper('$idPoli') and
        
        // generate no antrian query psi 20230711
        $no_antrian = $this->db->query("SELECT max(mr_periksa.nourut)+1 as no,max(antrian_jkn.estimasidilayani) as estimasidilayani  
        from mr_periksa
        left join antrian_jkn on antrian_jkn.norm = mr_periksa.rm
        where mr_periksa.tanggal='$tanggalperiksa' && mr_periksa.kode_dok='$idDokterrs'")->row();


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
        $this->db->trans_start();
        $this->db->insert($this->table, $data);
        $newid = $this->db->query("SELECT max(id)+1 as id from mr_periksa")->row();
		$waktuPeriksa = $this->db->query("select waktu from mr_jadwal_hfis where poli='$idPoli' && dokter='$idDokterrs' && hari=$no_hari && CONCAT_WS('-',awal,akhir) = '$data[jampraktek]'")->row();//edit psi 20230703
        $insPeriksa = array( 'id'           => $newid->id,
                             'waktu'        => $waktuPeriksa->waktu,
                             'tanggal'      => $data['tanggalperiksa'],
                             'pukul'        => '',
                             'poli'         => $idPoli,
                             'rm'           => $data['norm'],
                             'kode_dok'     => $idDokterrs,
                             'nourut'       => $data['noantrian'],
                            //  'catatan'      => '',
                            //  'last'         => '',
                             'relasi'       => 'BPJS',
                            //  'hubungan'     => '',
                            //  'RelasiNo'     => '',
                            //  'RelasiCtt'    => '',
                             'tgldaftar'    => $data['tglinsert'],
                             'id_user'      => 'MJKN',
                            //  'tglkeluarmr'  => null,
                            //  'tglkembalimr' => '',
                            //  'nonota'       => '',
                            //  'kondisi'      => '',
                            //  'pasien'       => '',
                            //  'ruper'        => '',      
                            //  'ambil'        => '',
                            //  'oleh'         => '',
                            //  'cttambil'     => '',
                            //  'keterangan'   => '',
                            //  'konsulan'     => '',
                            //  'telp'         => '',
                             'tglperiksa'   => $data['tanggalperiksa']
                            //  'tglclose'     => '',
                            //  'id_close'     => '',
                            //  'piutang'      => '',
                            //  'karyawan'     => '',
                            //  'nokunjungan'  => '',
                            //  'onedit'       => '',
                            //  'ondelete'     => ''
    );
        $this->db->insert('mr_periksa', $insPeriksa);
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            $solve = array('code' => '3');            
        }else{
            $return = $this->db->query("SELECT max(id) as id from antrian_jkn")->row();
            $solve = array('id' => $return->id, 'code' => '1');
        }
        
        return $solve;
    }


    public function cek_waktu_daftar($data)
    {
        $days_now = date("D", strtotime(date('Y-m-d', strtotime($data['tanggalperiksa']))));
        $date_time_now = date('d-m-Y H:i:s');
        $dates_now = date('Y-m-d');
        $days_num = no_hari($days_now);

        $get_polis = $this->db->query("SELECT mr_j.akhir as jamselesai,mp.nama as nama 
                                    from mr_jadwal_hfis as mr_j
                                    join muser on muser.nik = mr_j.dokter
                                    join mpoli mp on mp.poli = mr_j.poli
                                    where mp.s_name is not null 
                                        and mr_j.hari = $days_num
                                        and upper(mp.s_name)=upper('$data[kodepoli]')
                                        and muser.id_extPass = $data[iddokter]
                                        and CONCAT_WS('-',ltrim(mr_j.awal),ltrim(mr_j.akhir)) = '$data[jampraktek]'
                                        -- and kehadiran=1
                                    order by mr_j.dokter")->result_array();

        if (!empty($get_polis)) {
            $random_keys    = array_rand($get_polis, 1);
            $get_poli       = $get_polis[$random_keys];
            $batas_regist   = date('H:i:s', strtotime($get_poli['jamselesai']));
            $jam_regist     = date('H:i:s', strtotime($date_time_now));
            if ($jam_regist > $batas_regist and $data['tanggalperiksa'] == $dates_now) {
                $cek_jadwal = array('code' => '9');
                $cek_jadwal['jam'] = date('g:i a', strtotime($batas_regist));
                $cek_jadwal['namapoli'] = isset($get_poli) ? $get_poli['nama'] : null;
                return $cek_jadwal;
            }
        }else{
            $cek_jadwal = array('code' => '10');
        } 
    }

    public function reset_jadwal($data)
    {
        $days_now = date("D", strtotime(date('Y-m-d', strtotime($data['tanggalperiksa']))));
        $time_now = date('H:i');
        $date_time_now = date('d-m-Y H:i:s');
        $date_now = date('Y-m-d');
        $days_num = no_hari($days_now);
        $where = "";
        if($data['tanggalperiksa'] == $date_now){
            $where .= " and '$time_now' between trim(mr_j.awal) and trim(mr_j.akhir)  ";
        }
        // debug($days_num);
        $get_polis = $this->db->query("SELECT mr_j.kondisi as kehadiran,mr_j.hari,muser.nm_user as dokter
                                    from mr_jadwal_hfis as mr_j
                                    join muser on muser.nik = mr_j.dokter
                                    join mpoli on mpoli.poli = mr_j.poli
                                    where mpoli.s_name is not null 
                                        and mr_j.hari = $days_num
                                        $where
                                        and muser.id_extPass = $data[iddokter]
                                        and CONCAT_WS('-',mr_j.awal,mr_j.akhir) = '$data[jampraktek]'
                                        and upper(mpoli.s_name)=upper('$data[kodepoli]')
                                    order by mr_j.dokter")->result_array();
        // debug($get_polis);
        // die();
        if (!empty($get_polis)) {
            $random_keys    = array_rand($get_polis, 1);
            $get_poli       = $get_polis[$random_keys];

            // if ($get_poli['kehadiran'] == 0) {
            //     $reset_jadwal = array('code' => 7);
            //     $reset_jadwal['namadokter'] = isset($get_poli) ? $get_poli['dokter'] : null;
            //     return $reset_jadwal;
            // }
        } else {
            $cek_jadwal = array('code' => 7,
                                'namadokter' => isset($get_poli) ? $get_poli['dokter'] : null);
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
            ->join('mr_jadwal_hfis mr_j','mr_j.dokter = muser.nik')
            ->join('mpoli','mpoli.poli = mr_j.poli')
            ->like('mr_p.tglperiksa', $tgl_antrian, 'both')
            ->where('muser.id_extPass', $data['iddokter'])
            ->where('mpoli.s_name', $data['kodepoli'])
            ->where("CONCAT_WS('-',mr_j.awal,mr_j.akhir)",$data['jampraktek'])
            // ->where('status','1')
            ->get()
            ->first_row();
        $antrian_total = $total_antrian->total;
        $dokter = $this->db->select('mr_jadwal_hfis.*')
            ->from('mr_jadwal_hfis')
            ->join('mpoli','mpoli.poli=mr_jadwal_hfis.poli')
            ->join('muser','muser.nik=mr_jadwal_hfis.dokter')
            ->where('muser.id_extPass',$data['iddokter'])
            ->where('mr_jadwal_hfis.hari',$getdays)
            ->where('mpoli.s_name', $data['kodepoli'])
            ->where("CONCAT_WS('-',mr_jadwal_hfis.awal,mr_jadwal_hfis.akhir)",$data['jampraktek'])
            ->get()->first_row();
        // $dokter = $this->db->get_where('mr_jadwal_hfis', ['dokter' => $data['iddokter'],'poli' => $data['kode']])->first_row();
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

    public function get_non_jkn(){
        $tglsekarang = date('Y-m-d');
        $dnonjkn = $this->db->query("SELECT DISTINCT mrp.id as id_mrp,mrp.rm as norm,mrk.ktp as nik ,mmr.hp,mrp.tanggal as tanggalperiksa,mrp.nourut,
					muser.nm_user as nm_dokter,muser.id_extpass as kode_dokter,
					mpoli.s_name as kodepoli,mpoli.poli,muser.nik as iddokter,mpoli.nama as nama_poli,mrk.jkn
					from mr_periksa as mrp
					join mmr on mmr.rm = mrp.rm
					join mr_ktp mrk on mrk.rm = mmr.rm
					join muser on muser.nik = mrp.kode_dok
					join mpoli on mpoli.poli = mrp.poli
                    WHERE muser.id_extpass is not null and mpoli.s_name is not null
					-- AND NOT EXISTS (select norm,nik,kodepoli,iddokter from antrian_jkn)
                    AND mrp.tanggal = '$tglsekarang' and mrp.flag_antrian = '0'
                    order by mrp.id asc 
                    LIMIT 20")->result_array();
                    // debug($dnonjkn);
                    // exit();
        foreach ($dnonjkn as $key => $val) {
            $tanggalperiksa = date('Y-m-d', strtotime($val['tanggalperiksa']));
            $cekdatajkn = $this->db->query("SELECT * from antrian_jkn where norm = '$val[norm]' and nik = '$val[nik]' and kodepoli ='$val[kodepoli]' and iddokter= '$val[iddokter]' and tanggalperiksa = '$tanggalperiksa'")->row(); 
            if($cekdatajkn == null){
                // $generate = $this->db->query("SELECT UNIX_TIMESTAMP(NOW()) as id")->row();
                // $kodebook = $generate->id;
                $days_now = date("D", strtotime(date('Y-m-d', strtotime($val['tanggalperiksa']))));
                $days_num = no_hari($days_now);
                // cek kode booking
                // $cekkode = $this->db->query("SELECT id from antrian_jkn where id = $kodebook")->row();
                // if(!empty($cekkode)){
                    $newcode = $this->db->query("SELECT max(id) as id from antrian_jkn")->row();
                    $kodebook = $newcode->id+1;
                // }
                
                // get jam praktek
                $jam = $this->db->query("SELECT CONCAT_WS('-',ltrim(awal),ltrim(akhir)) as jam from mr_jadwal_hfis where poli = $val[poli] and dokter = $val[iddokter] and hari = $days_num")->row();
                // get no urut antrian jkn
                $no_antrian = $this->db->query("SELECT max(mr_periksa.nourut)+1 as no,max(antrian_jkn.estimasidilayani) as estimasidilayani  
                                            from mr_periksa
                                            left join antrian_jkn on antrian_jkn.norm = mr_periksa.rm
                                            where mr_periksa.tglperiksa='$tanggalperiksa'")->row();
                $data = array(
                    'id'                => $kodebook,
                    'noantrian'         => $val['nourut'],
                    'nomorkartu'        => $val['jkn'],
                    'nik'               => $val['nik'],
                    'norm'              => $val['norm'],
                    'notelp'            => $val['hp'],
                    'tanggalperiksa'    => $val['tanggalperiksa'],
                    'kodepoli'          => $val['kodepoli'],
                    'nomorreferensi'    => '',
                    'jeniskunjungan'    => 1,
                    'iddokter'          => $val['iddokter'],
                    'jampraktek'        => $jam->jam ,
                    'status'            => 1,
                    'tglinsert'         => date('Y-m-d H:i:s'),
                    'namapoli'          => $val['nama_poli'],
                    'namadokter'        => $val['nm_dokter']

                );
                if (!empty($no_antrian->no)) {
                    $estimasi = (int)$no_antrian->estimasidilayani + (3600 * 100);
                    $data['estimasidilayani'] = $estimasi;
                    // $data['noantrian'] = $no_antrian->no;
                } else {
                    // $estimasi = $data['tanggalperiksa'] . ' ' . $jam_mulai;
                    // $data['noantrian'] = 1;
                    $data['estimasidilayani'] =  3600 * 100;
                }
                $this->db->insert($this->table, $data);
                $this->db->update('mr_periksa', ['flag_antrian' => '1'], ['id' => $val['id_mrp']]);
                echo " $kodebook || $val[norm] <br>";
            }else{
                $this->db->update('mr_periksa', ['flag_antrian' => '1'], ['id' => $val['id_mrp']]);
            }
        }
    }
}
