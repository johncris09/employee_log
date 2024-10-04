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
		$this->load->model('UserModel');
	}




	public function index_get()
	{
		$model = new AttendanceModel;
		$result = $model->get();
		$this->response($result, RestController::HTTP_OK);
	}



	public function get_employee_logs_get()
	{
		$attendanceModel = new AttendanceModel;
		$userModel = new UserModel;

		$requestData = $this->input->get();

		$month = date('m', strtotime($requestData['date']));
		$year = date('Y', strtotime($requestData['date']));


		// get employee schedule
		$schedule = $userModel->get_schedule([
			'joidnum' => $requestData['employee_id'],
		]);

		if ($schedule) {

			if ($schedule->logsched == '4 log (5 days CENRO)') {

				$attendance = $this->generate_4_log(
					$employee_id = $requestData['employee_id'],
					$year,
					$month,
					$login1 = $schedule->timeinsched,
					$login1_start_range = '-30 minutes',
					$login1_end_range = '+1 hour +59 minutes',

					$lgoout1 = $schedule->timeoutsched,
					$logout1_start_range = '-2 hours',
					$logout1_end_range = '+20 minutes',


					$login2 = $schedule->timeinsched2,
					$login2_start_range = '-30 minutes',
					$login2_end_range = '+1 hour +59 minutes',


					$logout2 = $schedule->timeoutsched2,
					$logout2_start_range = '-2 hours',
					$logout2_end_range = '+30 minutes',
				);

				$this->response($attendance, RestController::HTTP_OK);


			} else if ($schedule->logsched == '2 log (8 hours)') {


				$attendance = $this->generate_2_log(
					$employee_id = $requestData['employee_id'],
					$year,
					$month,

					$login1 = '08:00:00',
					$login1_start_range = '-30 minutes',
					$login1_end_range = '+30 minutes',

					$logout2 = '17:00:00',
					$logout2_start_range = '-30 minutes',
					$logout2_end_range = '+30 minutes',

				);

				$this->response($attendance, RestController::HTTP_OK);
			} else if ($schedule->logsched == '2 log (night shift)') {
				
				$attendance = $this->generate_2_log(
					$employee_id = $requestData['employee_id'],
					$year,
					$month,

					$login1 = $schedule->timeinsched,
					$login1_start_range = '-30 minutes',
					$login1_end_range = '+30 minutes',

					$logout2 = $schedule->timeoutsched,
					$logout2_start_range = '-30 minutes',
					$logout2_end_range = '+30 minutes',

				);
				$this->response($attendance, RestController::HTTP_OK);

			} else {
 
				$attendance = $this->generate_2_log(
					$employee_id = $requestData['employee_id'],
					$year,
					$month,

					$login1 = $schedule->timeinsched,
					$login1_start_range = '-30 minutes',
					$login1_end_range = '+30 minutes',

					$logout2 = $schedule->timeoutsched,
					$logout2_start_range = '-30 minutes',
					$logout2_end_range = '+30 minutes',

				);
				$this->response($attendance, RestController::HTTP_OK);

			}
		} else {
			// Default Log
			$attendance = $this->generate_4_log(
				$employee_id = $requestData['employee_id'],
				$year,
				$month,
				$login1 = '08:00:00',
				$login1_start_range = '-30 minutes',
				$login1_end_range = '+1 hour +59 minutes',

				$lgoout1 = '12:00:00',
				$logout1_start_range = '-2 hours',
				$logout1_end_range = '+20 minutes',


				$login2 = '13:00:00',
				$login2_start_range = '-30 minutes',
				$login2_end_range = '+1 hour +59 minutes',


				$logout2 = '17:00:00',
				$logout2_start_range = '-2 hours',
				$logout2_end_range = '+30 minutes',
			);

			$this->response($attendance, RestController::HTTP_OK);


		}

		$this->response($schedule, RestController::HTTP_OK);

	}

	private function generate_4_log(
		$employee_id,
		$year,
		$month,

		$timeinsched,
		$timeinsched_start_range,
		$timeinsched_end_range,

		$timeoutsched,
		$timeoutsched_start_range,
		$timeoutsched_end_range,



		$timeinsched2,
		$timeinsched2_start_range,
		$timeinsched2_end_range,

		$timeoutsched2,
		$timeoutsched2_start_range,
		$timeoutsched2_end_range,
	) {
		$AttendanceModel = new AttendanceModel;

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
				'employee_id' => str_replace("-", "", $employee_id) // remove -
			);
			$result = $AttendanceModel->get_employee_logs($data);

			if (!empty($result)) {

				foreach ($result as $row) {
					$bio_time = $row->bio_time;

					$timeType = $this->get_time_type_4_log(
						$bio_time,


						$timeinsched,
						$timeinsched_start_range,
						$timeinsched_end_range,

						$timeoutsched,
						$timeoutsched_start_range,
						$timeoutsched_end_range,


						$timeinsched2,
						$timeinsched2_start_range,
						$timeinsched2_end_range,


						$timeoutsched2,
						$timeoutsched2_start_range,
						$timeoutsched2_end_range,
					);

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

		return $attendance;
	}



	private function generate_2_log(
		$employee_id,
		$year,
		$month,

		$timeinsched,
		$timeinsched_start_range,
		$timeinsched_end_range,

		$timeoutsched2,
		$timeoutsched2_start_range,
		$timeoutsched2_end_range,
	) {
		$AttendanceModel = new AttendanceModel;


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
				'employee_id' => str_replace("-", "", $employee_id) // remove -
			);
			$result = $AttendanceModel->get_employee_logs($data);

			if (!empty($result)) {

				foreach ($result as $row) {
					$bio_time = $row->bio_time;

					$timeType = $this->get_time_type_2_log(
						$bio_time,


						$timeinsched,
						$timeinsched_start_range,
						$timeinsched_end_range,


						$timeoutsched2,
						$timeoutsched2_start_range,
						$timeoutsched2_end_range,
					);

					if ($timeType == 'login1' && empty($dailyAttendance['login1'])) {
						$dailyAttendance['login1'] = $bio_time;
					} elseif ($timeType == 'logout2' && empty($dailyAttendance['logout2'])) {
						$dailyAttendance['logout2'] = $bio_time;
					}
				}
			}

			// Add the daily attendance record to the array
			$attendance[] = $dailyAttendance;
		}

		return $attendance;
	}




	private function get_time_type_4_log(
		$bio_time,


		$timeinsched,
		$timeinsched_start_range,
		$timeinsched_end_range,

		$timeoutsched,
		$timeoutsched_start_range,
		$timeoutsched_end_range,


		$timeinsched2,
		$timeinsched2_start_range,
		$timeinsched2_end_range,


		$timeoutsched2,
		$timeoutsched2_start_range,
		$timeoutsched2_end_range,


	) {


		// Convert $bio_time to a DateTime object
		$time = DateTime::createFromFormat('H:i:s', $bio_time);

		$timeinsched = DateTime::createFromFormat('H:i:s', $timeinsched);
		$timeoutsched = DateTime::createFromFormat('H:i:s', $timeoutsched);
		$timeinsched2 = DateTime::createFromFormat('H:i:s', $timeinsched2);
		$timeoutsched2 = DateTime::createFromFormat('H:i:s', $timeoutsched2);

		// Login 1
		$login_start_datetime = clone $timeinsched;
		$login_start_datetime->modify($timeinsched_start_range);

		$login_end_datetime = clone $timeinsched;
		$login_end_datetime->modify($timeinsched_end_range);


		// Logout 1
		$logout_start_datetime = clone $timeoutsched;
		$logout_start_datetime->modify($timeoutsched_start_range);

		$logout_end_datetime = clone $timeoutsched;
		$logout_end_datetime->modify($timeoutsched_end_range);



		// Login 2
		$login2_start_datetime = clone $timeinsched2;
		$login2_start_datetime->modify($timeinsched2_start_range);

		$login2_end_datetime = clone $timeinsched2;
		$login2_end_datetime->modify($timeinsched2_end_range);


		// Logout 2
		$logout2_start_datetime = clone $timeoutsched2;
		$logout2_start_datetime->modify($timeoutsched2_start_range);

		$logout2_end_datetime = clone $timeoutsched2;
		$logout2_end_datetime->modify($timeoutsched2_end_range);

		// Determine the time type
		if ($time >= $login_start_datetime && $time <= $login_end_datetime) {
			return 'login1';
		} elseif ($time >= $logout_start_datetime && $time <= $logout_end_datetime) {
			return 'logout1';
		} elseif ($time >= $login2_start_datetime && $time <= $login2_end_datetime) {
			return 'login2';
		} elseif ($time >= $logout2_start_datetime && $time <= $logout2_end_datetime) {
			return 'logout2';
		} else {
			return 'Unknown Time Type';
		}
	}


	private function get_time_type_2_log(
		$bio_time,

		$timeinsched,
		$timeinsched_start_range,
		$timeinsched_end_range,


		$timeoutsched2,
		$timeoutsched2_start_range,
		$timeoutsched2_end_range,


	) {


		// Convert $bio_time to a DateTime object
		$time = DateTime::createFromFormat('H:i:s', $bio_time);

		$timeinsched = DateTime::createFromFormat('H:i:s', $timeinsched);
		$timeoutsched2 = DateTime::createFromFormat('H:i:s', $timeoutsched2);

		// Login 1
		$login_start_datetime = clone $timeinsched;
		$login_start_datetime->modify($timeinsched_start_range);

		$login_end_datetime = clone $timeinsched;
		$login_end_datetime->modify($timeinsched_end_range);


		// Logout 2
		$logout2_start_datetime = clone $timeoutsched2;
		$logout2_start_datetime->modify($timeoutsched2_start_range);

		$logout2_end_datetime = clone $timeoutsched2;
		$logout2_end_datetime->modify($timeoutsched2_end_range);

		// Determine the time type
		if ($time >= $login_start_datetime && $time <= $login_end_datetime) {
			return 'login1';
		} elseif ($time >= $logout2_start_datetime && $time <= $logout2_end_datetime) {
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
