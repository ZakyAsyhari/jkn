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

    public function index_post()
    {
        $date       = new DateTime();
        $data = array(
            'nomorkartu'        => $this->post('nomorkartu'),
            'nik'               => $this->post('nik'),
            'norm'              => $this->post('norm'),
            'notelp'            => $this->post('nohp'),
            'tanggalperiksa'    => $this->post('tanggalperiksa'),
            'kodepoli'          => $this->post('kodepoli'),
            'nomorreferensi'    => $this->post('nomorreferensi'),
            'jeniskunjungan'    => $this->post('jeniskunjungan'),
            'iddokter'          => $this->post('kodedokter'),
            'jampraktek'        => $this->post('jampraktek'),
            // 'jenisrequest'      => $this->post('jenisrequest'),
            // 'polieksekutif'     => $this->post('polieksekutif'),
        );

        $output         = [];
        $keys_kosong    = null;
        $keys_tgl       = null;
        $keys_range     = null;
        $keys_length    = null;
        foreach ($data as $key => $value) {
            if ($value == '' || $value == null) {
                if ($key == 'nomorkartu' || $key == 'tanggalperiksa') {
                    $keys_kosong = $key;
                    break;
                }
            }

            if ($key == 'nomorkartu') {
                if (strlen($value) != 13) {
                    $keys_length = $key;
                }
            }

            // if ($key == 'jenisrequest') {
            //     if ($value != 1 && $value != 2) {
            //         $keys_kosong = $key;
            //     }
            // }
            if ($key == 'jeniskunjungan') {
                if ($value != 1 && $value != 2) {
                    $keys_kosong = $key;
                }
            }

            if ($key == 'tanggalperiksa') {
                if (validateFormatDate($value)) {
                    if (validateBackDate($value)) {
                        $keys_tgl = $key;
                    }
                } else {
                    $keys_tgl = $key;
                }

                if (validateInRangeDate($value, 90)) {
                    $keys_range = $key;
                }
            }
        }

        if (($this->jwt != 1) && ($this->jwt != 19)) {
            $output =  array(
                'metadata' => [
                    'message' => "Token Expired.",
                    'code' => 201
                ]
            );
        } else if (empty($keys_kosong)) {
            if ($keys_tgl != null) {
                // Validasi tanggal format gagal
                $output =  array(
                    'response' => null,
                    'metadata' => array(
                        'message'           => 'data antrian gagal dimasukkan',
                        'cause'             => 'format tanggal salah atau backdate',
                        'column'            => strtolower($keys_tgl),
                        'code'              => 202
                    ),
                );
            } else if ($keys_range != null) {
                //validasi panjang no kartu
                $output =  array(
                    'response' => null,
                    'metadata' => array(
                        'message'           => 'data antrian gagal dimasukkan',
                        'cause'             => 'tanggal antrian yang diminta melebihi 90 hari',
                        'column'            => strtolower($keys_range),
                        'code'              => 202
                    ),
                );
            } else if ($keys_length != null) {

                //validasi panjang no kartu
                $output =  array(
                    'response' => null,
                    'metadata' => array(
                        'message'           => 'data antrian gagal dimasukkan',
                        'cause'             => 'panjang value kolom tidak sesuai',
                        'column'            => strtolower($keys_length),
                        'code'              => 202
                    ),
                );
            } else {

                # cek Antrean Hanya Dapat Diambil 1 Kali Pada Tanggal Yang Sama
                $cek_antrean = $this->antrian->cek_antrian($data['nik']);
                $cek_pasien = $this->antrian->cek_pasien($data['norm']);
                $cek_jadwal = $this->antrian->cek_waktu_daftar($data);
                $reset_jadwal = $this->antrian->reset_jadwal($data);
                // $reset_jadwal['code'] = null;



                if (!empty($cek_antrean)) {

                    $output =  array(
                        'metadata' => [
                            'message' => "Nomor Antrean Hanya Dapat Diambil 1 Kali Pada Tanggal Yang Sama.",
                            'code' => 201
                        ]
                    );
                } else if (empty($cek_pasien)) {

                    $output =  array(
                        'metadata' => [
                            'message' => "Data pasien ini tidak ditemukan, silahkan Melakukan Registrasi Pasien Baru",
                            'code' => 202
                        ]
                    );
                } else if ($cek_jadwal['code'] == 9) {
                    // waktu pendaftaran > dari waktu buka poli
                    $output =  array(
                        'response' => null,
                        'metadata' => array(
                            'message'   => 'Pendaftaran Ke Poli ' . $cek_jadwal['namapoli'] . ' Sudah Tutup Jam ' . $cek_jadwal['jam'],
                            'code'      => 202
                        ),
                    );
                } else if ($cek_jadwal['code'] == 10) {
                    // Pendaftaran ke Poli Ini Sedang Tutup
                    $output =  array(
                        'response' => null,
                        'metadata' => array(
                            'message'   => 'Pendaftaran ke Poli Ini Sedang Tutup',
                            'code'      => 202
                        ),
                    );
                } else if ($reset_jadwal['code'] == 7) {
                    // Pendaftaran ke Poli Ini Sedang Tutup
                    $output =  array(
                        'response' => null,
                        'metadata' => array(
                            'message'           => 'data antrian gagal dimasukkan',
                            'cause'             => 'Jadwal Dokter ' . $reset_jadwal['namadokter'] . ' Tersebut Belum Tersedia, Silahkan Reschedule Tanggal dan Jam Praktek Lainnya',
                            'code'              => 202
                        ),
                    );
                } else {

                    $solve = $this->antrian->antrian_insert($data);
                    if ($solve['code'] == 1) {
                        // Respon Ok
                        $return = $this->antrian->antrian_get($solve['id']);
                        $kuota = $this->antrian->set_kuota($data);

                        $output = array(
                            'response'      => array(
                                'nomorantrean'      => $return->noantrian,
                                'angkaantrean'      => $return->noantrian,
                                'kodeboking'        => $return->id,
                                'norm'              => $return->norm,
                                'namapoli'          => $return->namapoli,
                                'namadokter'        => $return->namadokter,
                                'estimasidilayani'  => (int)$return->estimasidilayani,
                                'sisakuotajkn'      => (int)$kuota['sisanonjkn'],
                                'kuotajkn'          => (int)$kuota['kuotajkn'],
                                'sisakuotanonjkn'   => (int)$kuota['sisanonjkn'],
                                'kuotanonjkn'       => (int)$kuota['kuotanonjkn'],
                                'keterangan'        => 'Peserta harap 6 menit lebih awal guna pencatatan administrasi.'
                            ),
                            'metadata'      => array(
                                'message'       => 'Ok',
                                'code'          => 200
                            ),
                        );
                    } else if ($solve['code'] == 2) {
                        // telah mendaftar pada hari yang sama
                        $output =  array(
                            'response' => null,
                            'metadata' => array(
                                'message'           => 'data antrian gagal dimasukkan',
                                'cause'             => 'pasien telah didaftarkan',
                                'code'              => 202
                            ),
                        );
                    } else {
                        // Poli tidak ditemukan
                        $output =  array(
                            'response' => null,
                            'metadata' => array(
                                'message'           => 'Pendaftaran ke Poli Ini Sedang Tutup',
                                'cause'             => 'kode poli kosong atau tidak sesuai jadwal',
                                'code'              => 201
                            ),
                        );
                    }
                }
            }
        } else {
            // Jika ada variabel yang kosong
            $output =  array(
                'response' => null,
                'metadata' => array(
                    'message'           => 'data antrian gagal dimasukkan',
                    'cause'             => 'data kosong atau tidak sesuai',
                    'column'            => strtolower($keys_kosong),
                    'code'              => 202
                ),
            );
        }

        $this->response($output, Rest::HTTP_OK);
    }

    public function batal_antrian_post()
    {

        $input = $this->post();
        $kodebooking = $input['kodebooking'];
        $alasan      = $input['keterangan'];

        if ($this->jwt != 1) {
            $this->response([
                'metadata' => [
                    'message' => "Token Expired.",
                    'code' => 201
                ]
            ], 200);
        } else if (empty($kodebooking)) {

            $this->response([
                'response' => null,
                'metadata' => [
                    'message' => 'kode booking tidak boleh kosong',
                    'code'    => 201
                ]
            ], 200);
        } else {


            $antrian = $this->db->order_by('id', 'desc')->get_where('antrian_jkn', ['id' => $kodebooking])->first_row();

            if (!empty($antrian)) {
                $_start_date =new DateTime ($antrian->tanggalperiksa);

                $_start_validate = $_start_date->format('Y-m-d');
                // print_r($_start_validate);

                if (empty($kodebooking) || validateBackDate($_start_validate)) {
                    $pesan_gagal = 'Data Antrean tidak ditemukan';
                } else if ($antrian->status == 5) {
                    $pesan_gagal = 'Antrean Tidak Ditemukan atau Sudah Dibatalkan';
                } else if ($antrian->status == 2) {
                    $pesan_gagal = 'Pasien Sudah Dilayani, Antrean Tidak Dapat Dibatalkan';
                }

                if (!empty($pesan_gagal)) {
                    $this->response([
                        'metadata' => [
                            'message' => $pesan_gagal,
                            'code' => 201
                        ]
                    ], 200);
                } else {

                    $this->db->trans_start();

                    $this->db->update('antrian_jkn', ['status' => 5, 'keterangan' => $alasan], ['id' => $kodebooking]);
                    $this->db->trans_complete();

                    if ($this->db->trans_status() === false) {

                        $this->response([
                            'metadata' => [
                                'message' => 'gagal batal appointment',
                                'code' => 201
                            ]
                        ], 200);
                    } else {

                        $this->response([
                            'metadata' => [
                                'message' => 'Ok',
                                'code' => 200
                            ]
                        ], 200);
                    }
                }
            } else {
                $this->response([
                    'metadata' => [
                        'message' => 'Antrean Tidak Ditemukan',
                        'code' => 201
                    ]
                ], 200);
            }
        }
    }


    public function checkin_post()
    {

        $input = $this->post();

        if ($this->jwt != 1) {
            $this->response([
                'metadata' => [
                    'message' => "Token Expired.",
                    'code' => 201
                ]
            ], 200);
        } else if (empty($input['kodebooking'])) {

            $this->response([
                'metadata' => [
                    'message' => 'kode booking tidak boleh kosong',
                    'code' => 201
                ]
            ], 200);
        } else {

            $appointment = $this->db->get_where('antrian_jkn', ['id' => $input['kodebooking']])->first_row();

            if (empty($appointment)) {

                $this->response([
                    'metadata' => [
                        'message' => 'Data antrian tidak ditemukan',
                        'code' => 201
                    ]
                ], 200);
            } else {

                $this->db->trans_start();
                $seconds = $input['waktu'] / 1000;
                $timestamp = date('Y-m-d H:i:s', $seconds);
                // print_r($timestamp); exit();
                $this->db->update('antrian_jkn', ['status' => 2, 'checkin' => $timestamp], ['id' => $input['kodebooking']]);

                $this->db->trans_complete();

                if ($this->db->trans_status()) {
                    $this->response([
                        'metadata' => [
                            'message' => 'Ok',
                            'code' => 200
                        ]
                    ], 200);
                } else {
                    $this->response([
                        'metadata' => [
                            'message' => 'Gagal melakukan check in',
                            'code' => 201
                        ]
                    ], 200);
                }
            }
        }
    }


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
                    'tanggalperiksa' => $tanggal,
                    'iddokter'       => $id_dokter,
                    'kodepoli'           => $kode_poli,
                    'jampraktek'      => $jam_praktek
                );
                $kuota = $this->antrian->set_kuota($data);
                // $dokter_unit = $this->db->get_where('DOKTER_UNIT', ['ID_DOKTER' => $id_dokter])->first_row();

                # total antrian
                // $total_antrian = $this->db->select('count(ID) as total')
                //     ->like('', $tgl_antrian, 'both')
                //     ->where('lokal_id', $data['iddokter'])
                //     ->where('status','1')
                //     ->get('mr_periksa')
                //     ->first_row();

                    $total_antrian = $this->db->select('count(ID) as total')
                                    ->like('tanggalperiksa', $data['tanggalperiksa'], 'both')
                                    ->where('iddokter', $data['iddokter'])
                                    ->where('kodepoli', $data['kodepoli'])
                                    ->where('jampraktek',$data['jampraktek'])
                                    ->where('status','1')
                                    ->get('antrian_jkn')
                                    ->first_row();
                    // print_r($total_antrian);
                $antrian_total = $total_antrian->total;
                $sisa_antrian = $kuota['kuotajkn'] - $total_antrian->total;

                # data estimasi antrian
                
                #end

                $this->response([
                    'response' => [
                        'namapoli' => $poli->nama,
                        'namadokter' => $dokter->nm_user,
                        'totalantrean' => $antrian_total,
                        'sisaantrean' => $sisa_antrian,
                        'antreanpanggil' => 'A-1',
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

    public function sisa_post()
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
                ->from('antrian_jkn')
                ->where('id', $kode_booking)
                ->where('status !=', 5)
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
            $sql = "SELECT * from  antrian_jkn
                    where 
                    status in (2) and tanggalperiksa like '%" . $appointment->tanggalperiksa . "%'
                    order by id asc";

            $antrian_sekarang = $this->db->query($sql)->result();

            if (empty($antrian_sekarang)) {
                return $this->response([
                    'metadata' => [
                        'message'   => 'Belum ada antrian berjalan',
                        'code' => 201
                    ]
                ], 200);
            }

            # sisah antrian
            $sisah_antrian = $this->db->select('count(id) as sisah_antrian')
                ->from('antrian_jkn')
                ->where('STATUS', 1)
                ->like('tanggalperiksa', $appointment->tanggalperiksa, 'both')
                ->get()
                ->first_row();

            $sisah_antrian_min_1 = sizeof($antrian_sekarang) - 1;

            if ($sisah_antrian_min_1 <= 1) {
                $sisah_antrian_min_1 = 1;
            } else {
                $sisah_antrian_min_1;
            }

            $waktu_antrian = strtotime($appointment->tanggalperiksa);

            $this->response([
                'response' => [
                    'nomorantrean' => $appointment->noantrian ,
                    'namapoli' => $appointment->namapoli,
                    'namadokter' => $appointment->namadokter,
                    'sisaantrean' => $sisah_antrian_min_1,
                    'antreanpanggil' => '',
                    'waktutunggu' => '',
                    'keterangan' => $appointment->keterangan
                ],
                'metadata' => [
                    'message' => 'Ok',
                    'code' => 200
                ]
            ], 200);
        }
    }
}
