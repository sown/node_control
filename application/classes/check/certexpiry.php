<?php
class Check_CertExpiry extends Check
{

	public function Check_CertExpiry($host)
	{
		$this->code = Check::OK;
		$this->message = "Certificate will expiry on sometime.";
		$certificate = openssl_x509_parse($host->certificate->publicKey);
		$valid_to = str_replace("Z", "", $certificate['validTo']);
		$valid_to = "20".substr($valid_to,4,2)."-".substr($valid_to,2,2)."-".substr($valid_to,4,2)." ".substr($valid_to,6,2).":".substr($valid_to,8,2).":".substr($valid_to,10,2);
		$unix_valid_to = strtotime($valid_to);
		$unix_30days_later = strtotime("+30 days");
		$unix_90days_later = strtotime("+90 days");
		
		if ($unix_30days_later > $unix_valid_to) {
	                $this->code = Check::CRITICAL;
		}
		elseif ($unix_90days_later > $unix_valid_to) {
			$this->code = Check::WARNING;
		}
		else {
			$this->code = Check::OK;
                }
		error_log($this->code." - ".$this->message);
		$this->message = "Certificate will expiry on $valid_to.";
	}
}
