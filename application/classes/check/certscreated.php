<?php
class Check_CertsCreated extends Check
{

	public function Check_CertsCreated($host)
	{
		$this->code = Check::OK;
		$this->message = "All certificates present";

		$certs = Doctrine::em()->getRepository('Model_Certificate')->findAll();
		$ids = array();
		foreach ($certs as $cert) 
		{
			if ($cert->publicKey == null && $cert->current)
			{
				$ids[] = $cert->id;
			}
		}
		if (sizeof($ids) > 0)
		{	
			$this->code = Check::CRITICAL;
			$this->message = "There are ".sizeof($ids)." certificates missing with the following IDs: ".implode(", ", $ids);
		}
	}
}
