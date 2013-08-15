<?php
class Check_CertExpiry extends Check
{

	public function Check_CertExpiry($host)
	{
		$this->code = Check::OK;
		$this->message = "Certificate will expiry on sometime.";
		$certificate = openssl_x509_parse($host->certificate->publicKey);
		$unix_valid_to = $certificate['validTo_time_t'];
		$valid_to = date("Y-m-d H:i:s", $unix_valid_to);
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
		$this->message = "Certificate expires on $valid_to.";
	}
}
