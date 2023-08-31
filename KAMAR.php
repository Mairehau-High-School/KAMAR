<?php
	header("Content-type: application/json");

	$serviceName = 'KAMAR <--> FreePBX';
	$serviceVersion = '1.0';

	$username = 'username'; //Change These
	$password = 'password';
	$authcheck = "Basic ". base64_encode( $username .':'. $password );
		
	$data = file_get_contents('php://input');

	//Enable mod_basic_auth & SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1 in .htaccess if using the FreePBX provided OS.
  //Check Apache2 Documentation if you're confused. 
	$auth = $_SERVER['HTTP_AUTHORIZATION'];

	if( $auth != $authcheck ) {
		$string = '{"SMSDirectoryData": {';
		$string.= '  "error": 403,';
		$string.= '  "result": "Authentication Failed",';
		$string.= '  "service": "'.$serviceName.'"';
		$string.= '  "version": "'.$serviceVersion.'"';
		$string.= '}}';

		echo $string;
	}
	
	
	
	elseif( !isset( $data )) {
		$string = '{"SMSDirectoryData": {';
		$string.= '  "error": 401,';
		$string.= '  "result": "No Data",';
		$string.= '  "service": "'.$serviceName.'"';
		$string.= '  "version": "'.$serviceVersion.'"';
		$string.= '}}';

		echo $string;
	}
	
	
	
	elseif( stripos( $data, '"sync": "check"') > 0 ) {
		$string = '{"SMSDirectoryData": {';
		$string.= '  "error": 0,';
		$string.= '  "result": "OK",';
		$string.= '  "service": "'.$serviceName.'"';
		$string.= '  "version": "'.$serviceVersion.'"';
		$string.= '  "status": "Ready",';
		$string.= '  "infourl": "",'; //Put your URL
		$string.= '  "privacystatement": "No Identifiable Student Data Is Collected. Staff Calendars are stored on the PBX Server",'; //You're not asking for Student or Staff details, just timetables.
		$string.= '  "options": {';
		$string.= '    "ics": true,';
		$string.= '    "students": {';
		$string.= '      "details": false,';
		$string.= '      "passwords": false,';
		$string.= '      "photos": false,';
		$string.= '      "groups": false,';
		$string.= '      "awards": false,';
		$string.= '      "timetables": false,';
		$string.= '      "attendance": false,';
		$string.= '      "assessments": false,';
		$string.= '      "pastoral": false,';
		$string.= '      "learningsupport": false,';
		$string.= '      "fields": {';
		$string.= '        "required": "",';
		$string.= '        "optional": ""';
		$string.= '        }';
		$string.= '      },';
		$string.= '    "staff": {';
		$string.= '      "details": true,';
		$string.= '      "passwords": false,';
		$string.= '      "photos": false,';
		$string.= '      "timetables": true,';
		$string.= '      "fields": {';
		$string.= '        "required": "uniqueid",';
		$string.= '        "optional": ""';
		$string.= '        }';
		$string.= '      },';
		$string.= '    "common": {';
		$string.= '      "subjects": false,';
		$string.= '      "notices": false,';
		$string.= '      "calendar": false,';
		$string.= '      "bookings": false';
		$string.= '      }';
		$string.= '    }';
		$string.= '  }';
		$string.= '}}';

		echo $string;
	}

	else {
		$data = json_decode($data, true)["SMSDirectoryData"]["timetables"];
		for($X = 0; $X < $data["count"]; $X++)
		{
      //Optionally only save TTs that are from staff you want to track using their uniqueid as a reference.
			$file = fopen('ical/TT_'.$data["data"][$X]["id"].".ics", "w") or die("Unable to open file!"); //Ensure the ical dir is owned by Asterisk or the user has write access.
				
			fwrite($file, $data["data"][$X]["timetable"]);
			fclose($file);
		}

		$string = '{"SMSDirectoryData": {';
		$string.= '  "error": 0,';
		$string.= '  "result": "OK",';
		$string.= '}';

		echo $string;
	}
?>
