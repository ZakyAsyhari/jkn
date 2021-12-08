<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;
use \Firebase\JWT\JWT;

class Rest extends REST_Controller
{
    private $secretkey = 'bd5c6bfaf6d062a4a6f29012a050faeb';
    public $data = array();
    public $perpage = 5;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('login_model');
    }

    //method untuk not found 404
    public function not_found($message)
    {
        $this->response(array(
            'status'    => FALSE,
            'message'   => $message,
            'data'      => array()
        ), REST_Controller::HTTP_NOT_FOUND);
    }

    //method untuk bad request 400
    public function bad_req($message)
    {
        $this->response(array(
            'status'    => FALSE,
            'message'   => $message,
            'data'      => array()
        ), REST_Controller::HTTP_BAD_REQUEST);
    }

    //method untuk melihat token pada user
    public function view_token_post()
    {
        $date       = new DateTime();
        $username   = $this->post('username');
        $password   = $this->post('password');
        $get_admin  = $this->login_model->is_valid($username);

        if ($get_admin) {
            if (md5($password) === $get_admin->password) {
                $payload['id']          = $get_admin->id_user;
                $payload['name']        = $get_admin->nama;
                $payload['username']    = $get_admin->username;
                $payload['iat']         = $date->getTimestamp();
                $payload['exp']         = $date->getTimestamp() + 2629746; //satu bulan
                $output                 =  array(
                    'status'    => TRUE,
                    'message'   => 'Login accepted.',
                    'data'      => array(
                        'id'        => $get_admin->id_user,
                        'token'     => JWT::encode($payload, $this->secretkey)
                    )
                );
                $this->response($output, REST_Controller::HTTP_OK);
            } else {
                $this->view_token_fail($username, $password);
            }
        } else {
            $this->view_token_fail($nama, $pass);
        }
    }

    //method untuk jika view token diatas fail
    public function view_token_fail($username, $password, $mode = 0)
    {
        switch ($mode) {
            case '0':
                // $message = 'Member not activated yet.';
                $message = 'Username atau Password Tidak Sesuai';
                break;

            default:
                // $message = 'Wrong username or password.';
                $message = 'Username atau Password Tidak Sesuai';
                break;
        }

        $this->response([
            'metadata' => [
                'message' => $message,
                'code'    => 201
            ]
        ], REST_Controller::HTTP_UNAUTHORIZED);
    }

    //method untuk mengecek token setiap melakukan post, put, etc
    public function validate_token()
    {
        $this->load->model('Login_model');
        $jwt = $this->input->request_headers()['x-token'];

        try {
            $decode = JWT::decode($jwt, $this->secretkey, array('HS256'));

            if (strtotime(date('Y-m-d H:i:s')) > $decode->exp && !empty($decode->exp)) {
                return 19;
            } else if ($this->login_model->is_valid_num($decode->username) > 0) {
                return true;
            }
        } catch (Exception $e) {
            // exit('No authorization. Please contact our customer service.');
            return 0;
        }
    }

    //method untuk melihat token pada user
    public function gettoken_post()
    {
        $date       = new DateTime();
        $email      = $this->post('username');
        $password   = $this->post('password');
        $get_member  = $this->login_model->member_is_valid($email);
        if ($get_member) {
            // if (password_verify($password, $get_member->password)) {
            if ($password == $get_member->password) {
                $payload['id']          = $get_member->id;
                $payload['username']    = $get_member->username;
                $payload['iat']         = $date->getTimestamp();
                $payload['exp']         = $date->getTimestamp() + 2629746; //satu bulan 2629746
                $output                 =  array(
                    'response'      => array(
                        'token'     => JWT::encode($payload, $this->secretkey)
                    ),
                    'metadata'      => array(
                        'message'     => 'Ok',
                        'code'     => 200
                    ),
                );
                $this->response($output, REST_Controller::HTTP_OK);
            } else {
                $this->view_token_fail($email, $password, 1);
            }
        } else {
            $this->view_token_fail($email, $password);
        }
    }

    public function read_token()
    {

        $headers = $this->input->request_headers();

        if (empty($headers['x-username'])) {
            exit('No authorization x-username. Please contact our customer service.');
        } else if (empty($headers['x-token'])) {
            exit('No authorization x-token. Please contact our customer service.');
        }

        $jwt = $headers['x-token'];
        $jwt_user = $headers['x-username'];

        try {
            $decode = JWT::decode($jwt, $this->secretkey, array('HS256'));
            if ($decode->username == $jwt_user) {
                return $decode;
            } else {
                exit('x-username not same with x-token. Please contact our customer service.');
            }
        } catch (Exception $e) {
            // exit('No authorization. Please contact our customer service.');
            return 0;
        }
    }
}
