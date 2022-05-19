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

class Operasi extends Rest
{
    private $secretkey = 'e78eabacc9a866b3af284be1fe864f76';
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
            'nopeserta'    => $this->post('nopeserta'),
        );

        if ($this->jwt != 1) {
            $output =  array(
                'metadata' => [
                    'message' => "Token Expired.",
                    'code' => 201
                ]
            );
        } else if ($this->post('nopeserta') == null || $this->post('nopeserta') == '') {
            $output =  array(
                'response' => null,
                'metadata' => array(
                    'message'           => 'nopeserta invalid / null',
                    'code'              => 202
                ),
            );
        } else {
            $data = $this->operasi->operasi_peserta($data);
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
                        'message'   => 'Data Operasi Pasien Tidak Ditemukan',
                        'code'      => 201
                    ),
                );
            }
        }

        $this->response($output, Rest::HTTP_OK);
    }
}
