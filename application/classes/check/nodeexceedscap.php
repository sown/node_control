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
			if ($host->currentDeployment->capExceeded == false)
			{
				$deployment = $host->currentDeployment;
				$deployment->capExceeded = true;
				$deployment->save();
			}
		}
		else if(false && $host->currentDeployment->approachesCap)
		{
			$this->code = Check::WARNING;
		}
		else
		{
			$this->code = Check::OK;
			if ($host->currentDeployment->capExceeded == true)
                        {
                                $deployment = $host->currentDeployment;
                                $deployment->capExceeded = false;
                                $deployment->save();
                        }
		}
		if($cap == 0)
		{
			$this->message = "Current consumption is " . RadAcctUtils::byteUnits($con*1024*1024, true) . ", no limit set.";
		}
		else
		{
			$this->message = "Current consumption is " . RadAcctUtils::byteUnits($con*1024*1024, true) . ", limit is " . RadAcctUtils::byteUnits($cap*1024*1024, true) . " (" . floor(100*$con/$cap) . "%).";
		}
	}
}
