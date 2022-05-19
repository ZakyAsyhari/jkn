<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Operasi_model extends CI_Model
{
    private $table = 'ok_jadwal_operasi';
    private $db2;

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        // $db2 = $this->load->database('second', TRUE);
        $this->db2 = $this->load->database('second', TRUE);
    }
    public function operasi_all($data){
        $operasi = $this->db2->query("SELECT * from ok_jadwal_operasi where tglOperasi  between 
                                    '$data[tanggalawal]' and '$data[tanggalakhir]'")->result_array();
        // print_r(($operasi));
        // exit;

        if(empty($operasi)) return FALSE;
        foreach ($operasi as $key=> $value) {
            $return['list'][$key]['kodebooking']    = $value['kodeBooking'];
            $return['list'][$key]['tanggaloperasi'] = $value['tglOperasi'];
            $return['list'][$key]['jenistindakan']  = $value['jenisTindakan'];
            $return['list'][$key]['kodepoli']       = $value['kodePoli'];
            $return['list'][$key]['namapoli']       = $value['namaPoli'];
            $return['list'][$key]['terlaksana']     = $value['terlaksana']=='Pengajuan'?0:1;
            $return['list'][$key]['nopeserta']      = $value['noJKN'];
            $return['list'][$key]['lastupdate']     = (int)strtotime(date($value['lastUpdate']))*1000;
        }
 
        return $return;
    }

    public function operasi_peserta($data){
        // print_r($data);
        // exit;
        $operasi = $this->db2->query("SELECT * from ok_jadwal_operasi where noJKN  = '$data[nopeserta]'")->result_array();
  
        if(empty($operasi)) return FALSE;
        foreach ($operasi as $key=> $value) {
            $return['list'][$key]['kodebooking']    = $value['kodeBooking'];
            $return['list'][$key]['tanggaloperasi'] = $value['tglOperasi'];
            $return['list'][$key]['jenistindakan']  = $value['jenisTindakan'];
            $return['list'][$key]['kodepoli']       = $value['kodePoli'];
            $return['list'][$key]['namapoli']       = $value['namaPoli'];
            $return['list'][$key]['terlaksana']     = empty($value['terlaksana'])?0:1;
        }
 
        return $return;
    }
}
