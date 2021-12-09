<?php defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Members
 * Create an members account and give access to
 * members area.
 *
 * @author 
 * @version 
 * @package 
 */

require_once APPPATH . 'controllers/Rest.php';

use \Firebase\JWT\JWT;

class Antrian extends Rest
{
    private $secretkey = 'bd5c6bfaf6d062a4a6f29012a050faeb';
    private $account;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('antrian_model', 'antrian');
        $this->account = $this->read_token();
        $this->jwt = $this->validate_token();
    }

    public function index_get()
    {
        $this->validate_token();
    }

    /**
     * Register new account
     * @param $account_id
     * @return string
     */


    public function status_post()
    {

        $id_dokter = $this->post('kodedokter');
        $kode_poli = $this->post('kodepoli');
        $tanggal   = $this->post('tanggalperiksa');
        $jam_praktek = $this->post('jampraktek');

        if (empty($id_dokter)) {
            $pesan_gagal = "id dokter tidak boleh kosong";
        } else if (empty($kode_poli)) {
            $pesan_gagal = "kode poli tidak boleh kosong";
        } else if (empty($tanggal)) {
            $pesan_gagal = "tanggal tidak boleh kosong";
        } else if (empty($jam_praktek)) {
            $pesan_gagal = "Jam praktek tidak boleh kosong";
        } else if (!validateFormatDate($tanggal)) {
            $pesan_gagal = "Format Tanggal Tidak Sesuai, format yang benar adalah yyyy-mm-dd";
        } else if (validateBackDate($tanggal)) {
            $pesan_gagal = "Tanggal Periksa Tidak Berlaku";
        }

        if ($this->jwt != 1) {
            $this->response([
                'metadata' => [
                    'message' => "Token Expired.",
                    'code' => 201
                ]
            ], 200);
        } else if (!empty($pesan_gagal)) {

            $this->response([
                'response' => null,
                'metadata' => [
                    'message' => $pesan_gagal,
                    'code' => 201
                ]
            ], 200);
        } else {

            # ambil data poli
            $poli = $this->db->get_where('mpoli', ['poli' => $kode_poli])->first_row();
            // print_r($poli);
            // exit();

            if (empty($poli)) {
                $this->response([
                    // 'response' => null,
                    'metadata' => [
                        'message' => "Poli Tidak Ditemukan",
                        'code' => 201
                    ]
                ], 200);
            } else {
                # AMBIL DATA DOKTER
                $dokter = $this->db->get_where('muser', ['lokal_Id' => $id_dokter])->first_row();

                # ambil data dokter unit
                $data = array(
                    'tglperiksa' => $tanggal,
                    'idDokter'       => $id_dokter
                );
                $kuota = $this->antrian->set_kuota($data);
                // $dokter_unit = $this->db->get_where('DOKTER_UNIT', ['ID_DOKTER' => $id_dokter])->first_row();

                $tgl_antrian = date('d-M-y', strtotime($tanggal));
                $tgl_antrian = strtoupper($tgl_antrian);

                # total antrian
                $total_antrian = $this->db->select('count(ID) as total')
                    ->like('tglmasuk', $tgl_antrian, 'both')
                    ->where('lokal_id', $data['idDokter'])
                    ->get('mr_periksa')
                    ->first_row();
                $antrian_total = $total_antrian->total;

                # data estimasi antrian
                
                #end

                $this->response([
                    'response' => [
                        'namapoli' => $poli->nama,
                        'namadokter' => $dokter->nm_user,
                        'totalantrean' => $antrian_total,
                        'sisaantrean' => '0',
                        'antreanpanggil' => 'A-21',
                        'sisakuotajkn' => $kuota['sisajkn'],
                        'kuotajkn' => $kuota['kuotajkn'],
                        'sisakuotanonjkn' => $kuota['sisanonjkn'],
                        'kuotanonjkn' => $kuota['kuotanonjkn'],
                        'keterangan' => ''
                    ],
                    'metadata' => [
                        'message' => 'Ok',
                        'code' => 200
                    ]
                ], 200);

                // $this->response([
                //     'response' => [
                //         'namapoli' => $poli->nama,
                //         'namadokter' => $dokter->nm_user,
                //         'totalantrean' => '100',
                //         'sisaantrean' => '100',
                //         'antreanpanggil' => 'A-21',
                //         'sisakuotajkn' => '100',
                //         'kuotajkn' => '100',
                //         'sisakuotanonjkn' => '100',
                //         'kuotanonjkn' => '100',
                //         'keterangan' => ''
                //     ],
                //     'metadata' => [
                //         'message' => 'Ok',
                //         'code' => 200
                //     ]
                // ], 200);

            }
        }
    }

    public function sisah_post()
    {

        $kode_booking = $this->post('kodebooking');

        if ($this->jwt != 1) {
            $this->response([
                'metadata' => [
                    'message' => "Token Expired.",
                    'code' => 201
                ]
            ], 200);
        } else if (empty($kode_booking)) {
            $this->response([
                'response' => null,
                'metadata' => [
                    'message' => "Kode booking tidak boleh kosong",
                    'code' => 201
                ]
            ], 200);
        } else {

            # ambil data appointment
            $appointment = $this->db->select('*')
                ->from('mr_periksa')
                ->where('id', $kode_booking)
                // ->where('status !=', 5)
                ->get();
            // ->first_row();

            if ($appointment->num_rows() > 0) {
                $appointment = $appointment->first_row();
            } else {
                return $this->response([
                    'metadata' => [
                        'message' => 'Antrean Tidak Ditemukan',
                        'code' => 201
                    ]
                ], 200);
            }


            # ambil data antrian yang lagi berjalan
            $this->response([
                'response' => [
                    'nomorantrean' => 'A' ,
                    'namapoli' => '',
                    'namadokter' => '',
                    'sisaantrean' => '',
                    'antreanpanggil' => '',
                    'waktutunggu' => '',
                    'keterangan' => ''
                ],
                'metadata' => [
                    'message' => 'Ok',
                    'code' => 200
                ]
            ], 200);
        }
    }
}
