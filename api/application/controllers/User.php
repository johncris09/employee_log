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
				$schedule = $userModel->get_schedule($data);

				

				$tokenData = array(
					'id' => $userLogin->id,
					'employee_id' => $userLogin->employee_id,
					'first_name' => $employee_data->Firstname,
					'middle_name' => $employee_data->MiddleName,
					'last_name' => $employee_data->LastName,
					'suffix' => $employee_data->Suffix,
					'date_from' => $latest_contract->DateFrom,
					'date_to' => $latest_contract->DateTo,
				); 


				
				// set to default 
				if($schedule) {
				
					$tokenData['logsched'] =  $schedule->logsched;
					$tokenData['timeinsched'] = $schedule->timeinsched;
					$tokenData['timeoutsched'] = $schedule->timeoutsched;
					$tokenData['timeinsched2'] = $schedule->timeinsched2;
					$tokenData['timeoutsched2'] = $schedule->timeoutsched2;
					$tokenData['office_assigned'] = $schedule->officeassigned;
				}else{
					$tokenData['logsched'] = 'default';
				}



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

	public function find_get($id)
	{

		$model = new UserModel;
		$result = $model->find($id);
		$this->response($result, RestController::HTTP_OK);

	}


	public function insert_post()
	{

		$model = new UserModel;
		$requestData = json_decode($this->input->raw_input_stream, true);


		$data = array(
			'employee_id' => $requestData['employee_id'],
			'first_name' => $requestData['first_name'],
			'last_name' => $requestData['last_name'],
			'middle_name' => $requestData['middle_name'],
			'suffix' => $requestData['suffix'],
			'username' => $requestData['username'],
			'password' => md5($requestData['password']),
		);


		$result = $model->insert($data);

		if ($result > 0) {
			$this->response([
				'status' => true,
				'message' => 'Successfully Inserted.'
			], RestController::HTTP_OK);
		} else {

			$this->response([
				'status' => false,
				'message' => 'Failed to create new user.'
			], RestController::HTTP_BAD_REQUEST);
		}
	}

	public function update_put($id)
	{


		$model = new UserModel;
		$requestData = json_decode($this->input->raw_input_stream, true);
		if (isset($requestData['employee_id'])) {
			$data['employee_id'] = $requestData['employee_id'];
		}
		if (isset($requestData['first_name'])) {
			$data['first_name'] = $requestData['first_name'];
		}
		if (isset($requestData['last_name'])) {
			$data['last_name'] = $requestData['last_name'];
		}
		if (isset($requestData['middle_name'])) {
			$data['middle_name'] = $requestData['middle_name'];
		}
		if (isset($requestData['suffix'])) {
			$data['suffix'] = $requestData['suffix'];
		}
		if (isset($requestData['username'])) {
			$data['username'] = $requestData['username'];
		}



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


	public function delete_delete($id)
	{
		$model = new UserModel;
		$result = $model->delete($id);
		if ($result > 0) {
			$this->response([
				'status' => true,
				'message' => 'Successfully Deleted.'
			], RestController::HTTP_OK);
		} else {

			$this->response([
				'status' => false,
				'message' => 'Failed to delete.'
			], RestController::HTTP_BAD_REQUEST);

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
