<?php 
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

$config = array(
	'signup' => array(
		array(
			'field' => 'username',
			'label' => 'Username',
			'rules' => 'trim|required|max_length[30]|xss_clean|is_unique[users.username]|alpha_dash',	
		),
		array(
			'field' => 'password',
			'label' => 'Password',
			'rules' => 'trim|required|max_length[128]|xss_clean|alpha_dash'	
		),
		array(
			'field' => 'email',
			'label' => 'Email',
			'rules' => 'trim|required|valid_email|max_length[50]|xss_clean|is_unique[users.email]'
		),
	),
    'login_username' => array(
        array(
            'field' => 'username',
            'label' => 'Username',
            'rules' => 'trim|required|max_length[50]|xss_clean'
        ),
        array(
            'field' => 'password',
            'label' => 'Password',
            'rules' => 'trim|required|max_length[128]|xss_clean|alpha_dash'
        ),
    ),
	'login_email' => array(
		array(
			'field'	=> 'email',
			'label' => 'Email',
			'rules' => 'trim|required|valid_email|max_length[50]|xss_clean|is_unique[users.email]'
		),
		array(
			'field' => 'password',
			'label' => 'Password',
			'rules' => 'trim|required|max_length[128]|xss_clean|alpha_dash'			
		)
	),
	'set_avatar' => array(
		array(
			'field' => 'avatar',
			'label' => 'Avatar',
			'rules' => 'required|xss_clean|'	
		)		
	),
	'set_alias' => array(
		array(
			'field' => 'alias',
			'label' => 'Alias',
			'rules' => 'trim|required|xss_clean|max_length[50]'	
		)
	),
	'change_password' => array(
		array(
			'field' => 'oldpass',
			'label' => 'Old Password',
			'rules' => 'trim|required|max_length[128]|xss_clean|alpha_dash'
		),
		array(
			'field' => 'newpass',
			'label' => 'New Password',
			'rules' => 'trim|required|max_length[128]|xss_clean|alpha_dash'			
		)
	),
    'set_password' => array(
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
    'forget_password_email' => array(
        array(
            'field' => 'email',
            'label' => 'Email',
            'rules' => 'trim|required|valid_email|max_length[128]|xss_clean|'
        )
    ),
	'forget_password_username' => array(
		array(
			'field' => 'username',
			'label' => 'Username',
			'rules' => 'trim|required|max_length[128]|xss_clean|'
		)	
	),
    'reset_password' => array(
        array(
            'field' => 'password',
            'label' => 'Password',
            'rules' => 'trim|required|max_length[128]|xss_clean|'
        ),
    ),
	'invite_friend' => array(
		array(
			'field' => 'inviteeid',
			'label' => 'Invitee ID',
			'rules' => 'required|integer|greater_than[0]'
		),
	),
	'accept_reject_friend' => array(
		array(
			'field' => 'inviterid',
			'label' => 'Inviter ID',
			'rules' => 'required|integer|greater_than[0]'	
		),
	),
	'unfriend' => array(
		array(
			'field' => 'friendid',
			'label' => 'Friend ID',
			'rules' => 'required|integer|greater_than[0]'			
		),
	),
);

/* End of file form_validation.php */
/* Location: ./application/config/form_validation.php */