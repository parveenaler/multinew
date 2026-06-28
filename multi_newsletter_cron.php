<?php
  ini_set('display_errors',0);
ini_set('display_startup_errors', 0);
error_reporting(0);
set_time_limit(55);
$exestarttime = microtime(true);
date_default_timezone_set('Europe/Berlin');
  $currentHour = date('G');
 $totalCount=0;
 $randomIdentifier = time().'_'.rand(111111,999999);

$bulkMailBatch = array();
$allChatData=array();
$newsLetterRowIds=array();
$allUserMailData=array();
$allLogData=array();

$startTime = microtime(true);

require_once('assets/includes/core.php');

//die;


$rowsPerCall =5000;

     $userRowsSql = "SELECT
    ns.id,
    ns.fake_user_id,
    ns.active,
    ru.name AS realName,
    ru.city AS realcity,
    ru.username AS realusername,
    ru.email AS realEmail,
    ru.age AS realAge,
    ru.s_gender AS realGender,
	ru.online_day as online_day,
    u.name AS fakeName,
    nq.id AS ntu_id,
    nq.newsletter_id,
    nq.user_id AS receiver_id,
    nq.execution_status,
    nm.message_number,
    nm.msg_type,
    nm.message_text
FROM
    newsletter_settings ns
INNER JOIN
    newsletter_queue nq ON ns.id = nq.newsletter_id
INNER JOIN
    newsletter_messages nm ON nq.newsletter_id = nm.newsletter_id AND nq.msg_num = nm.message_number
JOIN
    users ru ON ru.id = nq.user_id
LEFT JOIN
    users u ON u.id = ns.fake_user_id
WHERE
   	 nq.execute_by < NOW()
	
	 AND 
     nq.execution_status = 0 LIMIT ".$rowsPerCall; 


    $userRowsSqlCall = $mysqli->query($userRowsSql);
    $NLUserslist =    $userRowsSqlCall->fetch_all(MYSQLI_ASSOC);
 
    if(!empty($NLUserslist) && is_array($NLUserslist)){

        foreach($NLUserslist as $oneRowData){
          
        if( !empty($oneRowData['ntu_id']) ){
    
         
			$eachDueUser = $oneRowData;
			$type = $eachDueUser['msg_type'];
			$toUserId = $eachDueUser['receiver_id'];         
			$newsletterId   = intval($eachDueUser['id']);
			$dueRowId = $eachDueUser['ntu_id'];
			$fromUserId = $eachDueUser['fake_user_id'];
			$fromUserName = $eachDueUser['fakeName'];
			$online_day = $eachDueUser['online_day'];
			$receiverName=$eachDueUser['realusername'];
			$receiverCity=$eachDueUser['realcity'];
            $time = time();

				if($type=='text_image'){

                $temp_message= json_decode( $eachDueUser['message_text'], true);

						if(isset($temp_message['text'])){

							$text_message=$temp_message['text'];
							
						    $text_message=str_replace("{{username}}", $receiverName,$text_message);
                            $text_message=str_replace("{{city}}", $receiverCity,$text_message);


							$chatData = array(
							's_id' => $fromUserId,
							'r_id' => $toUserId,
							'time' => $time,
							'message' => $text_message,
							'fake' => 0,
							'photo'=>0,
							'online_day' => $online_day,
							 'message_number' => $eachDueUser['message_number'],
							'newsletter_id' =>  $eachDueUser['newsletter_id']
							);

			allChatDataArray($newsletterId,$toUserId,$message, $chatData,$dueRowId);


						}

						if(isset($temp_message['image'])){
							$text_message=$temp_message['image'];

							$chatData = array(
							's_id' => $fromUserId,
							'r_id' => $toUserId,
							'time' => $time,
							'message' => str_replace($_SERVER['DOCUMENT_ROOT'].'/',$site_url,$text_message),
							'fake' => 0,
							'online_day' => $online_day,
								 'message_number' =>$eachDueUser['message_number'],
							'photo' =>  getMediaType($text_message),   
							'newsletter_id' =>  $eachDueUser['newsletter_id']
							);
			
						allChatDataArray($newsletterId,$toUserId,$message, $chatData,$dueRowId);

						}
				
				}

			if($type=='text'){

				$temp_message= json_decode( $eachDueUser['message_text'], true);

				if(isset($temp_message['text'])){

					$text_message=$temp_message['text'];
					
					 $text_message=str_replace("{{username}}", $receiverName,$text_message);
                      $text_message=str_replace("{{city}}", $receiverCity,$text_message);

					$chatData = array(
					's_id' => $fromUserId,
					'r_id' => $toUserId,
					'time' => $time,
					'message' => $text_message,
					'fake' => 0,
					'photo'=>0,
						 'message_number' => $eachDueUser['message_number'],
					'online_day' => $online_day,
					'newsletter_id' =>  $eachDueUser['newsletter_id']
					);
				allChatDataArray($newsletterId,$toUserId,$message, $chatData,$dueRowId);

				}



				}

			if($type=='image'){

                $temp_message= json_decode( $eachDueUser['message_text'], true);


		    if(isset($temp_message['image'])){
				    $text_message=$temp_message['image'];

				$chatData = array(
					's_id' => $fromUserId,
					'r_id' => $toUserId,
					'time' => $time,
					'message' =>str_replace($_SERVER['DOCUMENT_ROOT'].'/',$site_url,$text_message),
					'fake' => 0,
					'online_day' => $online_day,
					 'message_number' => $eachDueUser['message_number'],
					'photo' => getMediaType($text_message),
					'newsletter_id' =>  $eachDueUser['newsletter_id']
					);

				allChatDataArray($newsletterId,$toUserId,$message, $chatData,$dueRowId);
			}
       

				}


            }  

        
   
        }
echo '<pre>test';


            
  echo '<pre>';

   insertChatRecords();            
    $exelapsedtime = microtime(true) - $exestarttime;    
    echo 'Total executed time is '.$exelapsedtime.'</br>'; 
    }

$mysqli->close();

function getMediaType($filePath) {
    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'tiff'];
    $videoExtensions = ['mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv', 'flv'];
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    if (in_array($extension, $imageExtensions)) return 1;
    elseif (in_array($extension, $videoExtensions)) return 2;
    return 0;
}


function generateInsertForChatDataBulk($chatData) {
    global $mysqli; 

    // Ensure $chatData is an array and not empty
    if (!is_array($chatData) || empty($chatData)) {
        return false; // Or throw an exception
    }
               
    // Number of rows
    $numRows = count($chatData);

    // Build the VALUES part of the query
    $values = [];
    foreach ($chatData as $row) {
        // Sanitize each value to prevent SQL injection
        $s_id = $row['s_id'];
        $r_id = $row['r_id'];
        $time = $row['time'];
        $message =secureEncode($row['message']);
        $fake = $row['fake'];
        $online_day = $row['online_day'];
        $photo = $row['photo'];

        // Add sanitized values as a tuple
        $values[] = "('$s_id', '$r_id', '$time', '$message', '$fake', '$online_day', '$photo')";
    }

    // Convert values array to string
    $valuesString = implode(', ', $values);

    // Construct the full query
    $query = "INSERT INTO chat (s_id, r_id, time, message, fake, online_day, photo) VALUES $valuesString";

    // Execute the query
    if ($mysqli->query($query) === false) {
       // throw new Exception("Query failed: " . $mysqli->error);
    }

}



function generateInsertForChatData($chatData){
    
	global $mysqli;


$data=$chatData;
//$chat_ids=[];

foreach ($data as $chatData) {

$values = [];

    $values[] = "('".$chatData['s_id']."', '".$chatData['r_id']."', '".$chatData['time']."', '".$chatData['message']."', '".$chatData['fake']."', '".$chatData['online_day']."', '".$chatData['photo']."')";


$valuesString = implode(", ", $values);

 $sqlChat = "INSERT INTO chat (s_id, r_id, time, message, fake, online_day, photo) VALUES " . $valuesString;   
$mysqli->query($sqlChat);
 
 
  $chat_id= $mysqli->insert_id; 
	$newsletter_id=  $chatData['newsletter_id'];	
	$user_id=  $chatData['r_id'];
	$msg_num=  $chatData['message_number'];



	  $sql = "
		UPDATE newsletter_queue 
		SET chat_id = $chat_id
		WHERE execution_status = 1 AND msg_num = $msg_num  AND newsletter_id=$newsletter_id  AND user_id =$user_id"; 

	

$mysqli->query($sql) ;

}

 //return $chat_ids;
}



function insertChatRecords(){
    
	global $mysqli, $allChatData,$newsLetterRowIds;    

    if(!empty($allChatData) && is_array($allChatData)){
    foreach($allChatData as $eachClId => $eachClSet){
             
         $dueRowIn=implode(",",$newsLetterRowIds[$eachClId]['rowids']);

    $sql="UPDATE newsletter_queue SET execution_status=1 WHERE execution_status = 0 AND id  IN($dueRowIn) ";
  $mysqli->query($sql);
  
     $chat_ids= generateInsertForChatData($eachClSet['chatdata']);
	
              
   $sql="UPDATE newsletter_queue SET execution_status=2 WHERE execution_status = 1 AND id  IN($dueRowIn) ";
   $mysqli->query($sql);
      
    }
    
    }


  
}



function allChatDataArray($newsletterId,$user_id,$email_message,$chatData,$dueRowId){
	global $mysqli, $totalCount,$randomIdentifier,$allChatData,$newsLetterRowIds ; 

			$totalCount++;
			if($totalCount>500)
			{
				$totalCount=0;//reset counter
				$randomIdentifier = time().'-'.rand(111111,999999);
				
			}
			$allChatData[$randomIdentifier]['chatdata'][]=$chatData;
			$newsLetterRowIds[$randomIdentifier]['rowids'][]=$dueRowId;
	 

}


?>
