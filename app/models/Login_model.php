<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login_model extends CI_Model
{
    private $table = 'akses_jkn';

    public function __construct()
    {
        parent::__construct();
    }

    public function is_valid($email)
    {
        $this->db->where('username', $email);
        $query = $this->db->get($this->table);
        return $query->row();
    }

    public function is_valid_num($email){
        $this->db->where('username',$email);
        $query = $this->db->get($this->table);
        return $query->num_rows();
    }

    /** Cek apakah akun member sudah diaktifkan */
    public function member_is_valid($email)
    {
        $this->db->where('username', $email);
        $query = $this->db->get('akses_jkn');
        // print_r( $query->row()); exit();
        return $query->row();
    }

}