<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/CreatorJwt.php';
require APPPATH . 'libraries/RestController.php';
require APPPATH . 'libraries/Format.php';

use chriskacerguis\RestServer\RestController;

class Attendance extends RestController
{

	function __construct()
	{
		// Construct the parent class
		parent::__construct();
		$this->objOfJwt = new CreatorJwt();
		$this->load->model('AttendanceModel');
	}




	public function index_get()
	{
		$model = new AttendanceModel;
		$result = $model->get();
		$this->response($result, RestController::HTTP_OK);
	}



	public function get_employee_logs_get()
	{
		$model = new AttendanceModel;

		$requestData = $this->input->get();
		$month = date('m', strtotime($requestData['date']));
		$year = date('Y', strtotime($requestData['date']));


		$employee_id = str_replace("-", "", $requestData['employee_id']);
		
		$data = array(
			'month' => $month,
			'year' => $year,
			'employee_id' => $employee_id
		);

		// Get the number of days in the specified month and year
		$numberOfDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);

		// Loop through each day of the month
		$attendance = [];
		for ($day = 1; $day <= $numberOfDays; $day++) {
			// Create a date object for the current day
			$date = new DateTime("$year-$month-$day");

			// Format the date to get the day of the week (full text)
			$dayOfWeek = $date->format('l'); // 'l' gives full textual representation of the day of the week

			// Initialize the attendance record for the current day
			$dailyAttendance = array(
				'day' => $day,
				'date' => $dayOfWeek,
				'login1' => '',
				'logout1' => '',
				'login2' => '',
				'logout2' => ''
			);

			// Get the attendance using whole date and employee id
			$data = array(
				'bio_date' => "$year-$month-$day",
				'employee_id' => 	$employee_id
			);

			$result = $model->get_employee_logs($data);

			if (!empty($result)) {
				foreach ($result as $row) {
					$bio_time = $row->bio_time;

					$timeType = $this->getTimeType($bio_time);
					if ($timeType == 'login1' && empty($dailyAttendance['login1'])) {
						$dailyAttendance['login1'] = $bio_time;
					} elseif ($timeType == 'logout1' && empty($dailyAttendance['logout1'])) {
						$dailyAttendance['logout1'] = $bio_time;
					} elseif ($timeType == 'login2' && empty($dailyAttendance['login2'])) {
						$dailyAttendance['login2'] = $bio_time;
					} elseif ($timeType == 'logout2' && empty($dailyAttendance['logout2'])) {
						$dailyAttendance['logout2'] = $bio_time;
					}
				}
			}

			// Add the daily attendance record to the array
			$attendance[] = $dailyAttendance;
		}

		$this->response($attendance, RestController::HTTP_OK);
	}


	private function getTimeType($bio_time)
	{
		// Convert $bio_time to a DateTime object
		$time = DateTime::createFromFormat('H:i:s', $bio_time);

		// Define the time ranges
		$login_morning_start = DateTime::createFromFormat('H:i:s', '00:00:00');
		$login_morning_end = DateTime::createFromFormat('H:i:s', '10:00:00');
		$logout_morning_start = DateTime::createFromFormat('H:i:s', '10:01:00');
		$logout_morning_end = DateTime::createFromFormat('H:i:s', '12:29:59');
		$login_afternoon_start = DateTime::createFromFormat('H:i:s', '12:30:00');
		$login_afternoon_end = DateTime::createFromFormat('H:i:s', '15:00:00');
		$logout_afternoon_start = DateTime::createFromFormat('H:i:s', '15:01:00');
		$logout_afternoon_end = DateTime::createFromFormat('H:i:s', '23:59:59');

		// Determine the time type
		if ($time >= $login_morning_start && $time <= $login_morning_end) {
			return 'login1';
		} elseif ($time >= $logout_morning_start && $time <= $logout_morning_end) {
			return 'logout1';
		} elseif ($time >= $login_afternoon_start && $time <= $login_afternoon_end) {
			return 'login2';
		} elseif ($time >= $logout_afternoon_start && $time <= $logout_afternoon_end) {
			return 'logout2';
		} else {
			return 'Unknown Time Type';
		}
	}



	public function login_post()
	{


		try {
			$userModel = new AttendanceModel;
			$requestData = json_decode($this->input->raw_input_stream, true);
			$userLogin = $userModel->login($requestData);

			if ($userLogin) {
				$tokenData['id'] = $userLogin->id;
				$tokenData['employee_id'] = $userLogin->employee_id;
				$tokenData['first_name'] = $userLogin->first_name;
				$tokenData['middle_name'] = $userLogin->middle_name;
				$tokenData['last_name'] = $userLogin->last_name;
				$tokenData['suffix'] = $userLogin->suffix;
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

	public function find_get($id)
	{

		$model = new AttendanceModel;
		$result = $model->find($id);
		$this->response($result, RestController::HTTP_OK);

	}


	public function insert_post()
	{

		$model = new AttendanceModel;
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


		$model = new AttendanceModel;
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
		$model = new AttendanceModel;
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


		$model = new AttendanceModel;
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
