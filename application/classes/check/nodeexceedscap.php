<?php
class Check_NodeExceedsCap extends Check
{
	public function Check_NodeExceedsCap($host)
	{
		$cap = $host->currentDeployment->cap;
		$con = $host->currentDeployment->consumption;
		if($host->currentDeployment->exceedsCap)
		{
			$this->code = Check::CRITICAL;
		}
		else if(false && $host->currentDeployment->approachesCap)
		{
			$this->code = Check::WARNING;
		}
		else
		{
			$this->code = Check::OK;
		}
		if($cap == 0)
		{
			$this->message = "Current consumption is " . byte_units($con*1024*1024, true) . ", no limit set.";
		}
		else
		{
			$this->message = "Current consumption is " . byte_units($con*1024*1024, true) . ", limit is " . byte_units($cap*1024*1024, true) . " (" . floor(100*$con/$cap) . "%).";
		}
	}
}
