<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Calendar extends MX_Controller {

	protected $filters;
	protected $ufilters;
	protected $distfilters;
	

	public function __Construct(){

		parent::__Construct();

		$this->load->model('calendar_model');
		$this->filters=Modules::run('filters/sessionfilters');
        //doesnt require a join on ihrisdata
        $this->ufilters=Modules::run('filters/universalfilters');
        // requires a join on ihrisdata with district level
        $this->distfilters=Modules::run('filters/districtfilters');

	}


	/*Get all Events */

	Public function getEvents()
	{
		$result=$this->calendar_model->getEvents($this->filters);
		echo json_encode($result);
	}
	Public function getattEvents()
	{
		// Set headers for streaming/optimization
		header('Content-Type: application/json');
		header('Cache-Control: no-cache, must-revalidate');
		
		// Increase execution time for large datasets
		set_time_limit(120);
		ini_set('memory_limit', '256M');
		
		// Clear output buffer for streaming
		if (ob_get_level()) {
			ob_end_clean();
		}
		
		try {
			$result = $this->calendar_model->getattEvents($this->filters);
			echo json_encode($result);
		} catch (Exception $e) {
			log_message('error', 'getattEvents error: ' . $e->getMessage());
			echo json_encode(array());
		}
	}
	
	/**
	 * Optimized streaming endpoint for attendance calendar
	 * Loads data in chunks to prevent hanging
	 */
	Public function getattEventsStream()
	{
		header('Content-Type: application/json');
		header('Cache-Control: no-cache, must-revalidate');
		
		set_time_limit(120);
		ini_set('memory_limit', '256M');
		
		if (ob_get_level()) {
			ob_end_clean();
		}
		
		try {
			$start = $this->input->get('start');
			$end = $this->input->get('end');
			
			// Limit date range to prevent huge queries
			$startDate = new DateTime($start);
			$endDate = new DateTime($end);
			$daysDiff = $startDate->diff($endDate)->days;
			
			// If range is too large, limit to 3 months
			if ($daysDiff > 90) {
				$end = date('Y-m-d', strtotime($start . ' +90 days'));
			}
			
			$result = $this->calendar_model->getattEventsOptimized($this->filters, $start, $end);
			echo json_encode($result);
		} catch (Exception $e) {
			log_message('error', 'getattEventsStream error: ' . $e->getMessage());
			echo json_encode(array());
		}
	}
	
	/**
	 * Optimized streaming endpoint for duty roster calendar
	 */
	Public function getEventsStream()
	{
		header('Content-Type: application/json');
		header('Cache-Control: no-cache, must-revalidate');
		
		set_time_limit(120);
		ini_set('memory_limit', '256M');
		
		if (ob_get_level()) {
			ob_end_clean();
		}
		
		try {
			$start = $this->input->get('start');
			$end = $this->input->get('end');
			
			// Limit date range to prevent huge queries
			$startDate = new DateTime($start);
			$endDate = new DateTime($end);
			$daysDiff = $startDate->diff($endDate)->days;
			
			// If range is too large, limit to 3 months
			if ($daysDiff > 90) {
				$end = date('Y-m-d', strtotime($start . ' +90 days'));
			}
			
			$result = $this->calendar_model->getEventsOptimized($this->filters, $start, $end);
			echo json_encode($result);
		} catch (Exception $e) {
			log_message('error', 'getEventsStream error: ' . $e->getMessage());
			echo json_encode(array());
		}
	}
	
	
	/*Add new event */
	Public function addEvent()
	{
		$result=$this->calendar_model->addEvent();
		
		echo $result;
	}
	Public function addleaveEvent()
	{
		$result=$this->calendar_model->addleaveEvent();
		
		echo $result;
	}
	/*Update Event */
	Public function updateEvent()
	{
		
		$result=$this->calendar_model->updateEvent();
		echo $result;
	}
	/*Delete Event*/
	Public function deleteEvent()
	{
		$result=$this->calendar_model->deleteEvent();
		echo $result;
	}
	Public function dragUpdateEvent()
	{	

		$result=$this->calendar_model->dragUpdateEvent();
		echo $result;
	}


	



	


}
