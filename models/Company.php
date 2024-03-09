<?php
class Company
{
    protected $db;
    protected $today;
    protected $close_db;

    public function __construct()
    {
        $conn = new Connection();
        $this->db = $conn->openConnection();
        // $conn->closeConnection();
        $this->today = date('d-m-Y');
    }

    public function index(){

    }

    // company resources
    public function get_company_details(){
        $query_rsSetting =  $this->db->prepare("SELECT * FROM `tbl_company_settings`");
        $query_rsSetting->execute();
        $row_rsSetting = $query_rsSetting->fetch();
        $totalRows_rsSetting = $query_rsSetting->rowCount();
        if($totalRows_rsSetting > 0){
            return $row_rsSetting;
        }else{
            return false;
        }
    }
}
