<?php

// a php client implementation of the Erlang HMAC_SHA api signing code implemented here:
// https://github.com/mochi/mochiweb/tree/master/examples/hmac_api

////////////////////////////////////////////////////////////////////////////////
///
/// Reference values for testing against Amazon documents ///
///
/// These need to be changed in production! ///
///
////////////////////////////////////////////////////////////////////////////////
define('ERLANG_SCHEMA', "AWS");
// defines the prefix for headers to be included in the signature
define('ERLANG_HEADER_PREFIX', "x-amz-");
// defines the date header
define('ERLANG_DATE_HEADER', "x-amz-date");

////////////////////////////////////////////////////////////////////////////////
///
/// Default values for defining a generic API ///
///
/// Only change these if you alter the canonicalisation ///
///
////////////////////////////////////////////////////////////////////////////////
// define('ERLANG_SCHEMA',        "MOCHIAPI");
// define('ERLANG_HEADER_PREFIX', "x-mochiapi-");
// define('ERLANG_DATE_HEADER',   "x-mochiapi-date");

// a couple of keys for testing
// these are taken from the document
// % http://docs.amazonwebservices.com/AmazonS3/latest/dev/index.html?RESTAuthentication.html
// they are not valid keys!
define('ERLANG_PUBLIC_KEY',  "0PN5J17HBGZHT7JJ3X82");
define('ERLANG_PRIVATE_KEY', "uV3F3YluFJax1cknvbcGwgjvx4QpvB+leU8dUj2o");

class erlang_hmac_api_lib {

	public function sign($privateKey, $method, $url, $headers, $contentType) {
		echo $privateKey . "\n";
		echo $method . "\n";
		echo $url . "\n";
		echo $headers . "\n";
		echo $contentType . "\n";
		// in Erlang normalise headers turns atoms into strings...
		// not a problemo in PHP, ma man...
		// HOWEVER it also lowercases/rectifies all header keys
		// which we kinda need to do...
		$ContentMD5 = $this->get_header($Headers, "content-md5");
		$Date = $this->get_header("Headers", "date");
		return ok;
	}

	public function authorize_request($request) {
		echo $request;
	}

	// exposed for unit testing
	protected function sign_data($PrivateKey, $Signature) {
		$Str = $this->make_signature_string($Signature);
		return $this->sign2($PrivateKey, $Str);
	}

	protected function sign2($privateKey, $str) {
		$hash = mhash(MHASH_SHA1, $str, $privateKey);
		return base64_encode($hash);
	}

	protected function make_signature_string($sig) {
		//echo $sig['method'] . "\n";
		//echo $sig['contentmd5'] . "\n";
		//echo $sig['contenttype'] . "\n";
		//echo $sig['date'] . "\n";
		//print_r($sig['headers']);
		//echo $sig['resource'] . "\n";
		$Date = $this->get_date($sig['headers'], $sig['date']);
		$Signature = strtoupper($sig['method']) . "\n"
				     . $sig['contentmd5'] . "\n"
				     . $sig['contenttype'] . "\n"
				     . $Date . "\n"
				     . $this->canonicalise_headers($sig['headers'])
				     . $this->canonicalise_resource($sig['resource']);

		echo "signature is " . $Signature . "\n"; 
		return $Signature;
	}

	private function get_header($Array, $Key) {
		$NewArray = array();
		foreach ($Array as $K => $V) {
			$NewArray[strtolower($this->rectify($K))]=trim($V);
		}
		if (in_array($Key, $NewArray)) {
			return $NewArray($Key);
		} else {
			return "";
		}
	}

	private function canonicalise_headers($headers) {
		if ($headers == array()) {
			return "\n";
		} else {
			krsort($headers);
			$can = "";
			foreach ($headers as $key => $val) {
				if ($this->matches($key, ERLANG_HEADER_PREFIX)) {
					$can .= strtolower($this->rectify($key)) . ":" . $this->reorder($val) . "\n";
				}
			}
			return $can;
		}
	}

	private function matches($str, $prefix) {
		$len = strlen($prefix);
		if (strncmp(strtolower($str), $prefix, $len) == 0) {
			return true;
		} else {
			return false;
		}
	}

	private function reorder($header) {
		$vals = explode(";", $header);
		foreach ($vals as $k => $v) {
			$vals[$k] = $this->rectify($v);
		}
		sort($vals);
		$newheader = implode(";", $vals);
		return $newheader;
	}

	private function rectify($str) {
		$Re = '/[\x20* | \t*]+/';
		return trim(preg_replace($Re, " ", $str));
	}

	private function canonicalise_resource($resource) {
		if (strncmp($resource, "http://", 7) == 0) {
			$r = substr($resource, 7, strlen($resource) - 7);
		} else if (strncmp($resource, "https://", 8) == 0) {
			$r = substr($resouces, 8, strlen($resource) - 8);
		}
		$toks = explode("/", $r);
		$path = implode("/", array_slice($toks, 1));
		return "/" . strtolower($path);
	}

	private function get_date($headers, $date) {

		// if there is a date header use that, otherwise take the date
		$flag = false;
		foreach ($headers as $key => $value) {
			if (strtolower($key) == ERLANG_DATE_HEADER) {
				$flag = true;
			}
		}
		if ($flag) {
			return "";
		} else {
			return $date;
		}
	}

	private function dump($Msg, $Binary) {
		echo $Msg . ":";
		$Len = strlen($Binary);
		for ($i = 0; $i < $Len; $i = $i + 1) {
			echo ord($Binary[$i]) . " ";
		}
		echo "\n";
	}

}

?>