<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Candidate_search extends CI_Controller {

    function __construct() {
        parent::__construct();

        $this->load->model('Candidate_searchdb', '', TRUE);
    }

    public function index() {
        
        $define_page_name = CANDIDATE_SEARCH;
        require_once APPPATH . 'php_include/common_header.php';

        //-------- start for seo --------//

        $data_msg['meta_tag'] = array('MetaTitle' => 'Candidate for jobs',
            'MetaDesc' => 'Get experience candidates',
            'MetaKeyword' => 'Candidate for jobs, Get experience worker'
        );
        $data_msg['top_menu'] = $define_page_name;

        //-------- end for seo --------//

        //------------- load classes ---------------//
        $this->load->library('form_validation');
        $this->load->model('Jobdb');
        //------------- load classes ---------------//
        
        //--- start initializing an array to handle values and messages during error or success ---//

        $initial_array = array(
            'keyword' => '',
            'location' => '',
            'key_skills'=>'',
            'key_skill_array'=>'',
            'expertise' => '',
            'salary'=>'',
            'experience'=>'',
            'emp_status'=>'',
            'error_msg' => ''
        );

        //--- end initializing an array to handle values and messages during error or success ---//
        
        //----------------- start populating city list ----------------//
         $city_list= $this->all_function->get_allcity();
         $data_msg['city_list']=$city_list;

        //----------------- end populating city list ----------------//
        
          //---------------- start for fetching all expertise area list ---------------//
         $expertise_list= $this->all_function->get_allexpertise();
         $data_msg['expertise_list']=$expertise_list;
         //---------------- end for fetching all expertise area list ---------------//
        
        
        //------------ start for error and success message --------------//

        if ($this->session->userdata('error_msg') != "") {
            $data_msg['error_msg'] = $this->session->userdata('error_msg');
            $this->session->unset_userdata('error_msg');    
        }
        
        if ($this->session->userdata('err_msg') != "") {
            $data_msg['err_msg'] = $this->session->userdata('err_msg');
            $this->session->unset_userdata('err_msg');
            
        }
        
        if ($this->session->userdata('user_id') != "") {
            $data_msg['user_id'] = $this->session->userdata('user_id');
            $this->session->unset_userdata('user_id');
            
        }
        
        if ($this->session->userdata('success_msg') != "") {

            $data_msg['success_msg'] = $this->session->userdata('success_msg');
            $this->session->unset_userdata('success_msg');
        }

        //------------ end for error and success message --------------//
        
        //------ Start fetch get values ----------//
        $keyword=$this->input->get('keyword') && $this->input->get('keyword')!=''?$this->input->get('keyword'):'';
        $location=$this->input->get('location') && $this->input->get('location')!=''?$this->input->get('location'):'';
        $key_skills=$this->input->get('key_skills') && count($this->input->get('key_skills')) >0 ?$this->input->get('key_skills'):'';
        
        $key_skills = trim(strip_tags($this->input->get('key_skills')));
        $key_skill_array = explode(',', $key_skills,-1);
        
        $expertise=$this->input->get('expertise') && $this->input->get('expertise')!=''?explode("|", $this->input->get('expertise')):'';
        $salary=$this->input->get('salary') && $this->input->get('salary')!=''?$this->input->get('salary'):'';
        $experience=$this->input->get('experience') && $this->input->get('experience')!=''?$this->input->get('experience'):'';
        $emp_status=$this->input->get('emp_status') && $this->input->get('emp_status')!=''?$this->input->get('emp_status'):'';
        //------ End fetch get values ----------//
        
        //------------ start for search ----------//
        if ($this->input->post('btn_candidatesearch')) {
            
            //----------- start fetching post data -------------------//

            $keyword = trim(strip_tags($this->input->post('keyword')));
            $location = trim(strip_tags($this->input->post('location')));
            $key_skills = trim(strip_tags($this->input->post('key_skills')));
            $key_skill_array = explode(',', $key_skills,-1);
            $expertise = $this->input->post('expertise');
            
            $salary=trim(strip_tags($this->input->post('salary')));
            $experience      =   trim(strip_tags($this->input->post('experience')));
            $emp_status      = trim(strip_tags($this->input->post('emp_status')));
            //----------- end fetching post data ---------------------//
            
        }
        //------------ end for search ----------//
        
        $error_msg = "";
        
        //--------- Validating City ----------//			

            if (!empty($location))
                $valid_job_loc = $this->all_function->valid_city($location); 
           
            //------------Validating functional expertise----------//
            
            if(!empty ($expertise))
                $valid_expertise = $this->all_function->valid_multiple_values(TABLE_EXPERTISE_AREA,'Name','Name',$expertise);            
            
            //------------End validating functional expertise----------//
            
            //-----------Start validating key skills--------------------//
            if(!empty ($key_skill_array))
                $valid_key_skills = $this->all_function->valid_multiple_values(TABLE_KEY_SKILLS,'SkillName','SkillName',$key_skill_array);
            //---------------End validating key skills------------------//
            
            //----------Start validating experience-----------//
            
            if(!empty($experience))
                $valid_experience = $this->Jobdb->valid_experience($experience);
            
            //----------End validating experience-----------//
            
            if ($location != '' && $valid_job_loc == '0') {
                $error_msg = "Please select a valid job location.";
            }elseif($key_skills !='' && strpos($key_skills, ",")!==FALSE && isset ($valid_key_skills) && $valid_key_skills == FALSE){
                $error_msg = "Please enter a valid skill.";
            }elseif(isset($expertise) && count($expertise) > 0 && isset ($valid_expertise) && $valid_expertise == false){
                $error_msg = "Please select valid functional expertise.";
            }elseif($salary!='' && !$this->form_validation->float($salary)){
                $error_msg = "Please enter valid salary.";
            }elseif($experience!='' && $valid_experience==false){
                $error_msg = "Please select a valid experience";
            }elseif($emp_status!='' && $emp_status!='E' && $emp_status!='F'){
                $error_msg = "Please select a valid employee status.";
            }


            if ($error_msg == "" && $this->input->post('btn_candidatesearch')) {
                $query_str="";
                
                if($keyword!=''){
                $query_str.="keyword=".$keyword;
                }
                
                if($keyword=='' && $location!=''){
                $query_str.="location=".$location;
                }elseif($keyword!='' && $location!=''){
                    $query_str.="&location=".$location;
                }
                
                if($keyword=='' && $location=='' && $key_skills!=''){
                $query_str.="key_skills=".$key_skills;
                }elseif(($keyword!='' || $location!='') && $key_skills!=''){
                    $query_str.="&key_skills=".$key_skills;
                }
                
                if($keyword=='' && $location=='' && $key_skills=='' && is_array($expertise)){
                $query_str="expertise=".implode("|", $expertise);
                }elseif(($keyword!='' || $location!='' || $key_skills!='') && is_array($expertise)){
                    $query_str.="&expertise=".implode("|", $expertise);
                }
                
                if($keyword=='' && $location=='' && $key_skills=='' && is_array($expertise)===false && $salary!=''){
                $query_str="salary=".$salary;
                }elseif(($keyword!='' || $location!='' || $key_skills!='' || is_array($expertise)) && $salary!=''){
                    $query_str.="&salary=".$salary;
                }
                
                if($keyword=='' && $location=='' && $key_skills=='' && is_array($expertise)===false && $salary=='' && $experience!=''){
                $query_str="experience=".$experience;
                }elseif(($keyword!='' || $location!='' || $key_skills!='' || is_array($expertise) || $salary!='') && $experience!=''){
                    $query_str.="&experience=".$experience;
                }
                
                if($keyword=='' && $location=='' && $key_skills=='' && is_array($expertise)===false && $salary=='' && $experience=='' && $emp_status!=''){
                $query_str="emp_status=".$emp_status;
                }elseif(($keyword!='' || $location!='' || $key_skills!='' || is_array($expertise) || $salary!='' || $experience!='') && $emp_status!=''){
                    $query_str.="&emp_status=".$emp_status;
                }
                
                redirect(base_url().'candidate-search?'.$query_str);
            }

            //-------- starting setting values to the variables ------//
            
                $sess_array = array();
                foreach ($initial_array as $key => $v) {
                    // Collect input value if it is defined...
                    if (isset($$key)) {
                        $sess_array[$key] = $$key;
                    }
                }
                $this->session->set_userdata($sess_array);
                if ($error_msg != "") {
                $this->session->set_userdata('error_msg',$error_msg);
                }
            //-------- end setting values to the variables ------//
        
        //---------- start fetch result from database --------------//
        $search_array=array(
                    'Designation'=>$keyword,
                    'ResumeDesc'=>$keyword,
                    'PreferredLoc'=>$this->all_function->get_name(TABLE_CITY,'CityName','CityId',$location),
                    'KeySkill'=>  $key_skill_array,
                    'FunctionalExpertise'=> $expertise,
                    'ExpectedSal'=>$salary,
                    'Experience'=>$experience,
                    'EmpStatus'=>$emp_status
                );
                $qry_result = $this->Candidate_searchdb->candidate_search($search_array);

                if ($qry_result->num_rows != 0) {
                    $result_arr = $qry_result->result_array();
                    $search_result=$result_arr;
                    
                }
                else {
                    $search_result='';
                }
        //---------- end fetch result from database --------------//
                
        foreach ($initial_array as $v => $key) {
                if ($this->session->userdata($v)) {

                    $data_msg[$v] = $this->session->userdata($v);
                }
            }
            $this->session->unset_userdata($initial_array);        
                
        $data_msg['search_result']=$search_result;
        
        if($this->input->post('contact_btn'))
        {
            require_once APPPATH.'php_include/common_header.php';
         //----------- checking for authentication -------------//
         if($recruiter_id=="0")
         {
             $query_str = $_SERVER['QUERY_STRING'] != '' ? '?' . $_SERVER['QUERY_STRING'] : '';
             redirect(base_url() . '?redirect_url=' . urlencode(base_url() . 'candidate-search'.$query_str));
         }
        
            $err_msg="";
            $user_id="";
            $contact_msg=trim(strip_tags($this->input->post('contact_msg')));
            $user_id=trim(strip_tags($this->input->post('user_id')));
            if($contact_msg=='')
                $err_msg="Please enter message.";
            if($err_msg!=''){
                $this->session->set_userdata('err_msg',$err_msg);
                $this->session->set_userdata('user_id',$user_id);
            }else{
                $insert_data=array(
                    'ContacId'=>$this->all_function->rand_string(8),
                    'RecruiterId'=>$recruiter_id,
                    'UserId'=>$user_id,
                    'Message'=>$contact_msg,
                    'AddedDate'=>date('Y-m-d H:i:s'),
                    'Status'=>'1'
                );
                $this->Candidate_searchdb->insert_contact($insert_data);
                
                //--- start  fetch company details ---//

                $recruiter_email_detail=$this->all_function->get_recruiter_emaildetail($recruiter_id);
                $company_name = $recruiter_email_detail['organization'];
                $company_email = $recruiter_email_detail['email'];

                //--- end fetch company details---//
                //--- start  fetch user details ---//

                $user_email_detail=$this->all_function->get_user_emaildetail($user_id);
                $user_name = $user_email_detail['fullname'];
                $email = $user_email_detail['email'];

                //--- end fetch user details---//
                
                // Build mail configuration...
                $support_email = SUPPORT_EMAIL;
                $support_name = SUPPORT_NAME;

                

                // Extract email template...
                $email_content = file_get_contents(base_url() . '../upload_media/email_templates/email_template.html');

                $email_content = str_replace("###image_folder###", base_url() . 'image/mail_images', $email_content);
                $email_content = str_replace("###user_name###", 'Hi '.$user_name, $email_content);
                $email_content = str_replace("###footer_text###", '&copy; '.date("Y") . " all rights reserverd." , $email_content);

                //--- start for constructing subject and message of email to writer ---//
                $result_array = $this->all_function->fetch_email_content('contact_candidate');
                $subject = $result_array['Subject'];
                $email_msg = $result_array['Body'];
                $email_msg = str_replace("{SITEMGR_EMAIL}", $support_email, $email_msg);
                $email_msg = str_replace("{COMPANY_NAME}", $company_name, $email_msg);
                $email_msg = str_replace("{MESSAGE}", $contact_msg, $email_msg);
                $email_content = str_replace("###email_message###", $email_msg, $email_content);

                //---------------- start shooting email -----------------------------//

                $this->load->library('Send_email');

                $this->send_email->shoot_email($support_name, $support_email, $email, $subject, $email_content);

                //------------------ end for shooting email ---------------------------//
                
                $this->session->set_userdata('success_msg','message is successfully submitted.');
            }
            $query_str = $_SERVER['QUERY_STRING'] != '' ? '?' . $_SERVER['QUERY_STRING'] : '';
                redirect(base_url().'candidate-search'.$query_str);
        }
        
        $query_str = $_SERVER['QUERY_STRING'] != '' ? '?' . $_SERVER['QUERY_STRING'] : '';
                $data_msg['current_url']=base_url().'candidate-search'.$query_str;
                
        $this->load->view('candidate_search/candidate_search', $data_msg);
    }

    

}

