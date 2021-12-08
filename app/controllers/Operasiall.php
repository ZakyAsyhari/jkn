<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Members
 * Create an members account and give access to
 * members area.
 *
 * @author Eddy Subratha <eddy.subratha@gmail.com>
 * @version 1.0
 * @package api
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
        );

        if(validateRangeDate($this->post('tanggalawal'),$this->post('tanggalakhir'))){
            $output =  array(
                'response' => null,
                'metadata' => array(
                    'message'           =>'range tanggal invalid',
                    'cause'             =>'tanggal awal lebih kecil dari tanggal akhir',
                    'code'              =>202
                ),                                                      
            );
        } else {

            $data=$this->operasi->operasi_all($data);
            if($data){

                if(!empty($data)) {
                    $output=  array(
                        'response'      => $data,
                        'metadata'      => array(
                            'message'   =>'Ok',
                            'code'      =>200
                        ),                                                      
                    );
                }
            }else{
                $output=  array(
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
