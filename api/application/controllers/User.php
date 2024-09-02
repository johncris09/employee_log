<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/CreatorJwt.php';
require APPPATH . 'libraries/RestController.php';
require APPPATH . 'libraries/Format.php';

use chriskacerguis\RestServer\RestController;

class User extends RestController
{

	function __construct()
	{
		// Construct the parent class
		parent::__construct();
		$this->objOfJwt = new CreatorJwt();
		$this->load->model('UserModel');
		$this->load->helper('crypto_helper');
	}




	public function index_get()
	{
		$user = new UserModel;
		$result = $user->get();

		$this->response($result, RestController::HTTP_OK);

	}



	public function login_post()
	{


		try {
			$userModel = new UserModel;
			$requestData = json_decode($this->input->raw_input_stream, true);
			$userLogin = $userModel->login($requestData);

			if ($userLogin) {
				$data = array(
					'JOIDNum' => $userLogin->employee_id
				);
				// get employe data from another database
				$employee_data = $userModel->get_empployee_data($data);
				$latest_contract = $userModel->get_latest_contract($data);

				$tokenData['id'] = $userLogin->id;
				$tokenData['employee_id'] = $userLogin->employee_id;
				$tokenData['first_name'] = $employee_data->Firstname;
				$tokenData['middle_name'] = $employee_data->MiddleName;
				$tokenData['last_name'] = $employee_data->LastName;
				$tokenData['suffix'] = $employee_data->Suffix;
				$tokenData['date_from'] = $latest_contract->DateFrom;
				$tokenData['date_to'] = $latest_contract->DateTo;

				$tokenData['expiresIn'] = "1000";
				$jwtToken = $this->objOfJwt->GenerateToken($tokenData);


				$this->response([
					'status' => true,
					'token' => $jwtToken,
					'message' => 'Login Successfully',
				], RestController::HTTP_OK);

			} else {

				$this->response([
					'status' => false,
					'message' => 'Invalid Username/Password. Please Try Again!',
				], RestController::HTTP_OK);
			}


		} catch (Exception $e) {
			// Handle other exceptions here


			$this->response([
				'status' => false,
				"message" => "Invalid Username/Password"
			], 500);



		}

	}



	public function change_password_put($id)
	{


		$model = new UserModel;
		$requestData = json_decode($this->input->raw_input_stream, true);

		$data = array(
			'password' => md5($requestData['password']),
		);

		$update_result = $model->update($id, $data);

		if ($update_result > 0) {
			$this->response([
				'status' => true,
				'message' => 'Successfully Updated.'
			], RestController::HTTP_OK);
		} else {

			$this->response([
				'status' => false,
				'message' => 'Failed to update.'
			], RestController::HTTP_BAD_REQUEST);

		}
	}



}
