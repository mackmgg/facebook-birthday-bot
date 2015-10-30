<?php
	$access_token = '';                                         // Facebook API Access Token
	$username = '';                                             // Facebook username (or ID number)
	$database = '';                                             // MySQL database for storing replies
	$dbuser = '';                                               // MySQL username
	$dbpass = '';                                               // MySQL password
	$timedelay = 10;                                            // Delay between checks (in seconds)
	$messages = array('Thanks!', 'Thank you!')                  // Message to reply to people
	$regex = '/(b(irth|-)(day)?)|((h)?bd)|(b(irth|-)?day)/i';   // Regex to determine birthday messages
	
	for(;;) {
	  $feed = file_get_contents("https://graph.facebook.com/".$username."/feed?access_token=".$access_token);
	  $data = json_decode($feed);
	  foreach($data->data as $datas) {
	    if(isset($datas->message)) {
	      if(preg_match($regex,$datas->message)) {
		        $db = new PDO("mysql:host=localhost;dbname=".$database,$dbuser,$dbpass);
		        $statement = $db->prepare("SELECT fbid FROM replies WHERE fbid = ?");
		        $statement->execute(array($datas->id));
		        if($statement->fetch()==FALSE) {
		          echo "Not Found! ".$datas->message."\n";
		          $qry = $db->prepare("INSERT INTO replies (fbid, message, time) VALUES (?, ?, ?)");
	          $qry->execute(array($datas->id, $datas->message, time()));
	          $curlurl = "https://graph.facebook.com/".$datas->id."/comments\n";
	          $attachment = array(
	            'access_token' => $access_token,
	            'message'      => $messages[rand(0,count($messages)-1)]
            );
	          $ch = curl_init();
	          curl_setopt($ch, CURLOPT_URL, $curlurl);
	          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	          curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	          curl_setopt($ch, CURLOPT_POST, true);
	          curl_setopt($ch, CURLOPT_POSTFIELDS, $attachment);
	          curl_setopt($ch, CURLOPT_HEADER, 0);
	          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	          $comment = curl_exec($ch);
	          curl_close ($ch);
	        }
	      }
	    }
	  }
	  sleep($timedelay);
	}
?>
