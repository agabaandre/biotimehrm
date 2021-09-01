<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Calendar extends MX_Controller {

	
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
		$result=$this->calendar_model->getattEvents($this->filters);
		echo json_encode($result);
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
