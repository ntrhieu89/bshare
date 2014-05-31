<?php 
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

$config = array(
	'register' => array(
		array(
			'field' => 'username',
			'label' => 'Username',
			'rules' => 'trim|required|max_length[30]|xss_clean|',
			''	
		),
	),
    'login' => array(
        array(
            'field' => 'username',
            'label' => 'Username',
            'rules' => 'trim|required|max_length[128]|xss_clean'
        ),
        array(
            'field' => 'password',
            'label' => 'Password',
            'rules' => 'trim|required|max_length[128]|xss_clean|callback__authenticate_user'
        )
    ),
    'account' => array(
        array(
            'field' => 'username',
            'label' => 'Username',
            'rules' => 'trim|required|min_length[3]|max_length[128]|xss_clean'
        ),
        array(
            'field' => 'email',
            'label' => 'Email',
            'rules' => 'trim|required|valid_email|max_length[128]|xss_clean'
        ),
        array(
            'field' => 'password',
            'label' => 'Password',
            'rules' => 'trim|required|max_length[128]|xss_clean|matches[repeatpw]|callback__user_exists|callback__create_user'
        ),
        array(
            'field' => 'repeatpw',
            'label' => 'Repeat Password',
            'rules' => 'trim|required|max_length[128]|xss_clean'
        )
    ),
    'change_password' => array(
        array(
            'field' => 'oldpassword',
            'label' => 'Old Password',
            'rules' => 'trim|required|max_length[128]|xss_clean'
        ),
        array(
            'field' => 'password',
            'label' => 'Password',
            'rules' => 'trim|required|max_length[128]|xss_clean|matches[repeatpw]|callback__change_password'
        ),
        array(
            'field' => 'repeatpw',
            'label' => 'Repeat Password',
            'rules' => 'trim|required|max_length[128]|xss_clean'
        )
    ),
    'forgot_password' => array(
        array(
            'field' => 'email',
            'label' => 'Email',
            'rules' => 'trim|required|valid_email|max_length[128]|xss_clean|callback__no_email_exists|callback__generate_reset_password'
        )
    ),
    'reset_password' => array(
        array(
            'field' => 'password',
            'label' => 'Password',
            'rules' => 'trim|required|max_length[128]|xss_clean|matches[repeatpw]|callback__reset_password'
        ),
        array(
            'field' => 'repeatpw',
            'label' => 'Repeat Password',
            'rules' => 'trim|required|max_length[128]|xss_clean'
        )
    )
);
/* End of file form_validation.php */
/* Location: ./application/config/form_validation.php */