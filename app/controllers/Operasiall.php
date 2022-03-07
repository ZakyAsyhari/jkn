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

class Operasiall extends Rest
{
    private $secretkey = 'bd5c6bfaf6d062a4a6f29012a050faeb';
    private $account;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('operasi_model', 'operasi');
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
            'tanggalawal'    => $this->post('tanggalawal'),
            'tanggalakhir'   => $this->post('tanggalakhir'),
            // 'kodepoli'       => $this->post('kodepoli'),
        );

        if ($this->jwt != 1) {
            $output =  array(
                'metadata' => [
                    'message' => "Token Expired.",
                    'code' => 201
                ]
            );
        } else if (validateRangeDate($this->post('tanggalawal'), $this->post('tanggalakhir'))) {
            $output =  array(
                'response' => null,
                'metadata' => array(
                    'message'           => 'range tanggal invalid',
                    'cause'             => 'tanggal awal lebih kecil dari tanggal akhir',
                    'code'              => 202
                ),
            );
        // } elseif (empty($data['kodepoli'])) {
        //     $output =  array(
        //         'response' => null,
        //         'metadata' => array(
        //             'message'           => 'Kodepoli Tidak Ditemukan',
        //             'cause'             => 'kode poli tidak sesuai atau null',
        //             'code'              => 202
        //         ),
        //     );
        } else {

            $data = $this->operasi->operasi_all($data);

            if ($data) {

                if (!empty($data)) {
                    $output =  array(
                        'response'      => $data,
                        'metadata'      => array(
                            'message'   => 'Ok',
                            'code'      => 200
                        ),
                    );
                }
            } else {
                $output =  array(
                    'response'      => $data,
                    'metadata'      => array(
                        'message'   => 'Data Operasi Tidak Ditemukan',
                        'code'      => 201
                    ),
                );
            }
        }

        $this->response($output, Rest::HTTP_OK);
    }
}
