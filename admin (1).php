<?php
ini_set("memory_limit",'256M');

header('Content-Type: application/json');
require_once('../assets/includes/core.php');

require_once('../assets/includes/activation_queue.php');
require_once('../assets/includes/pagination.class.php');
$sm['admin_ajax'] = true;
	error_reporting(1);
ini_set("display_errors","On");
$newAccountFreeCreditValue=intval(getHardCodedSettingVal("rewards", "newAccountFreeCredit"));

     //  make_thumb($filepath, $thumbpath, 200);
function get_image_type( $filename ) {
    $img = getimagesize( $filename );
    if ( !empty( $img[2] ) )
        return image_type_to_mime_type( $img[2] );
    return false;
}
 function make_thumb($src, $dest, $desired_width) {
    $imgType = get_image_type($src);
    if(strpos($imgType, 'png') !== false) {
       $source_image = imagecreatefrompng($src);
    } else {
       $source_image = imagecreatefromjpeg($src); 
    }   
    $width = imagesx($source_image);
    $height = imagesy($source_image);
    $desired_height = floor($height * ($desired_width / $width));
    $virtual_image = imagecreatetruecolor($desired_width, $desired_height);
    imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);
    imagejpeg($virtual_image, $dest);
}


function upload_welcome_image($src,$imageprefix='welcome')
{
	//print_r($_FILES);
	$name = $_FILES[$src]['name'];
	
	$ext = pathinfo($name, PATHINFO_EXTENSION);	
	$ext=strtolower($ext);
	  $allowedExts = array("gif", "jpeg", "jpg", "png");
   if(!in_array($ext, $allowedExts)) 
        { 
            die('Iamge Extension not allowed');
        }
	$rand_name  = $imageprefix."_".rand(1000000,100000000).".".$ext;
	
	  $destination =  $_SERVER['DOCUMENT_ROOT'].'/assets/sources/'.$rand_name;
	 move_uploaded_file($_FILES[$src]['tmp_name'],$destination);	
	 return $rand_name;
}
if(!isAdminLoggedIn()){ echo 'Request not authorized!'; die();}
if(isset($_GET['action'])){

switch ($_GET['action']) {
    
    
    
       
//auto message intrest....

        case 'intrest_stats':
                
        $perPage = new PerPage();
        $perPage->perpage=100;
        
        $search_date=secureEncode($_GET['search_date']);
        $from='';
        $till='';
        $dateFilter='';
        $intrest_id=secureEncode($_GET['intrest_id']);

        if(!empty($search_date)){
            
            $date = explode(' to ', $search_date);
            $date = array_map(function($el) {
            $el = trim($el);
            $el = explode('/', $el);
            $el = array($el[2], $el[0], $el[1]);
            $el = implode('-', $el);
            return $el;
            }, $date);
            
            if (count($date) == 2) {
            $from = strtotime($date[0] . ' 00:00:00');
            $till = strtotime($date[1] . ' 23:59:59');
            }else{
            
            $date = $date[0];
            if(empty($date))
            $date=date('Y/m/d');
            $from = strtotime($date . ' 00:00:00');
            $till = strtotime($date. ' 23:59:59');
            
            }
            
         $dateFilter ="  AND  ( CAST(UNIX_TIMESTAMP(intrest_fake_user_time) AS SIGNED) >= $from AND  CAST(UNIX_TIMESTAMP(intrest_fake_user_time) AS SIGNED) <= $till)";
         
           $fromdate=$from;
            $tilldate=$till;
  
        }else{
             $date=date('Y/m/d');
            $fromdate = strtotime($date . ' 00:00:00');
            $tilldate = strtotime($date. ' 23:59:59'); 
            
        $dateFilter ="  AND  ( CAST(UNIX_TIMESTAMP(intrest_fake_user_time) AS SIGNED) >= $fromdate AND  CAST(UNIX_TIMESTAMP(intrest_fake_user_time) AS SIGNED) <= $tilldate)";

            
        }
        
        $action=secureEncode($_GET['action']);
        
         if($action=='intrest_stats')
        $action='intrest';
        
    

	   $sqlMsg1   = "SELECT `user_id` FROM `auto_intrest_campaign_list` WHERE $action"."_1 > 0 AND intrest_id=$intrest_id  AND $action"."_message_1 !='' AND 
	   ( CAST(UNIX_TIMESTAMP($action"."_message_1_send_date) AS SIGNED) >= $fromdate AND  CAST(UNIX_TIMESTAMP($action"."_message_1_send_date) AS SIGNED) <= $tilldate)";

     $statsMsg1Count = $mysqli->query($sqlMsg1 );
     $statsMsg1Count = $statsMsg1Count->num_rows;
          
     $sqlMsg2   =  "SELECT `user_id` FROM `auto_intrest_campaign_list` WHERE $action"."_2 > 0 AND intrest_id=$intrest_id  AND $action"."_message_2 !=''
     AND  ( CAST(UNIX_TIMESTAMP($action"."_message_2_send_date) AS SIGNED) >= $fromdate AND  CAST(UNIX_TIMESTAMP($action"."_message_2_send_date) AS SIGNED) <= $tilldate)";
     $statsMsg2Count = $mysqli->query($sqlMsg2 );
     $statsMsg2Count = $statsMsg2Count->num_rows;
     
          
     $sqlMsg3   =  "SELECT `user_id` FROM `auto_intrest_campaign_list` WHERE $action"."_3 > 0 AND intrest_id=$intrest_id  AND $action"."_message_3 !=''
     AND  ( CAST(UNIX_TIMESTAMP($action"."_message_3_send_date) AS SIGNED) >= $fromdate AND  CAST(UNIX_TIMESTAMP($action"."_message_3_send_date) AS SIGNED) <= $tilldate)";
     $statsMsg3Count = $mysqli->query($sqlMsg3 );
     $statsMsg3Count = $statsMsg3Count->num_rows;
     
          
     $sqlMsg4  =  "SELECT `user_id` FROM `auto_intrest_campaign_list` WHERE $action"."_4 > 0 AND intrest_id=$intrest_id  AND $action"."_message_4 !=''
     AND  ( CAST(UNIX_TIMESTAMP($action"."_message_4_send_date) AS SIGNED) >= $fromdate AND  CAST(UNIX_TIMESTAMP($action"."_message_4_send_date) AS SIGNED) <= $tilldate)";
     $statsMsg4Count = $mysqli->query($sqlMsg4 );
     $statsMsg4Count = $statsMsg4Count->num_rows;
     

       $sql   = 'SELECT  DATE_FORMAT(intrest_fake_user_time,"%d-%m-%Y %h:%i %p") as reg_date ,
           DATE_FORMAT('.$action.'_message_1_send_date,"%h:%i %p") as '.$action.'_message_1_sent_date ,
       DATE_FORMAT('.$action.'_message_2_send_date,"%h:%i %p") as '.$action.'_message_2_sent_date ,
        DATE_FORMAT('.$action.'_message_3_send_date,"%h:%i %p") as '.$action.'_message_3_sent_date ,
                 DATE_FORMAT('.$action.'_message_4_send_date,"%h:%i %p") as '.$action.'_message_4_sent_date ,
     auto_intrest_campaign_list.*  FROM `auto_intrest_campaign_list` WHERE   intrest_id='.$intrest_id  .' '. $dateFilter .' ORDER BY intrest_fake_user_time DESC' ;

    $paginationlink = "/requests/admin.php?action=".$action."_stats&page=";	
  
    $pagination_setting = $_GET["pagination_setting"];
        
        $page = 1;
    if(!empty($_GET["page"])) {
    $page = $_GET["page"];
    }
    
    $start = ($page-1)*$perPage->perpage;
    if($start < 0) $start = 0;
    
     $query =  $sql . " limit " . $start . "," . $perPage->perpage; 
  
   
    $stats = $mysqli->query($query );
    
  //  if(empty($_GET["rowcount"])) {
    $statsCount = $mysqli->query($sql );
    $_GET["rowcount"] = $statsCount->num_rows;
    
   // }
   // var_dump($statsCount->num_rows);die;
    if($pagination_setting == "prev-next") {
    	$perpageresult = $perPage->getPrevNext($_GET["rowcount"], $paginationlink,$pagination_setting);	
    } else {
    	$perpageresult = $perPage->getAllPageLinks($_GET["rowcount"], $paginationlink,$pagination_setting);	
    }
    
    
    $output = '';
ob_start();

    ?>
    
      <div class="card-group" id="results">
      <div class="card card-body text-center">
          <div class="d-flex flex-row align-items-center">
              <div class="card-header__title m-0">Total Msg #1 Sent</div>
              <div class="text-amount ml-auto font16"><?= $statsMsg1Count;?></div>
          </div>
      </div>
      <div class="card card-body text-center">
          <div class="d-flex flex-row align-items-center">
              <div class="card-header__title m-0">Total Msg #2 Sent</div>
              <div class="text-amount ml-auto font16"><?= $statsMsg2Count;?></div>
          </div>
      </div>      
      <div class="card card-body text-center">
          <div class="d-flex flex-row align-items-center">
              <div class="card-header__title m-0">Total Msg #3 Sent</div>
              <div class="text-amount ml-auto font16"><?= $statsMsg3Count;?></div>
          </div>
      </div>    
     <div class="card card-body text-center">
          <div class="d-flex flex-row align-items-center">
              <div class="card-header__title m-0">Total Msg #4 Sent</div>
              <div class="text-amount ml-auto font16"><?= $statsMsg4Count;?></div>
          </div>
      </div> 
      </div>
    
     <table style="font-size: 12px;width:100%;" class="table mb-0 thead-border-top-0">
              <thead>
                  <tr style="background: #fff">
                      <th>#</th>
                      <th>Join Date </th>
                      <th>Fakeuser </th>
                      <th>RealUser </th>
                  
                      <th>Msg 1 Sent </th>
                      <th>Msg 1 Seen </th>
                      <th>Msg 1 Ans </th>
                    
                    <th>Msg 2 Sent </th>
                    <th>Msg 2 Seen </th>
                    <th>Msg 2 Ans </th>
                    
                    
                    <th>Msg 3 Sent </th>
                    <th>Msg 3 Seen </th>
                    <th>Msg 3  Ans </th>
                    
                    
                    <th>Msg 4 Sent </th>
                    <th>Msg 4 Seen </th>
                    <th>Msg 4 Ans </th>
                    
                  
                  </tr>
              </thead>
              <tbody class="list" id="statsTable" style="overflow-y: scroll">
              <?php 
                if ($stats->num_rows > 0) { 
                    
                    if($page>1)
                    $counter=1+($page-1)*$perPage->perpage;
                    else
                    $counter=1;
                
                $param0=$action.'_fake_user';
                $param1=$action.'_1_chat_id';
                $param2=$action.'_2_chat_id';
                $param3=$action.'_3_chat_id';
                $param4=$action.'_4_chat_id';
                
                $param5=$action.'_message_1_sent_date';
                $param6=$action.'_1_chat_id';
                $param7=$action.'_message_2_sent_date';
                $param8=$action.'_2_chat_id';
                $param9=$action.'_message_3_sent_date';
                $param10=$action.'_3_chat_id';
                $param11=$action.'_message_4_sent_date';
                $param12=$action.'_4_chat_id';
                    
                  while($stat = $stats->fetch_object()) { 
                  
               $rowStats=   messageSeenStats($stat->user_id,$stat->$param0,$stat->$param1,$stat->$param2,$stat->$param3,$stat->$param4);
        
                  ?>
                  <tr  style="outline: thin solid">
                      <td><?php echo  $counter;  $counter++; ?></td>
                      <td><?php echo $stat->reg_date; ?></td>
                    
                    
                     <td><?php
                      
                      if( $stat->$param0==null) {
                      echo "No $action";}
                      else 
                      {
                      
                          if(isUserDeleted( $stat->$param0))
                        echo '<span style="text-decoration: line-through;">'. $stat->$param0.'</span>';
                        else 
                       echo '<span>'. $stat->$param0.'</span>';
                      }
                       
                       ?>
                         </td>
                    
                  
                                                    
                       <td>
                             <?php
                          if(isUserDeleted( $stat->user_id))
                        echo '<span style="text-decoration: line-through;">'. $stat->user_id.'</span>';
                        else 
                       echo '<span>'. $stat->user_id.'</span>';
                       ?>
                         </td>
                                    
                      
                      <td><?php echo $stat->$param5; ?></td>
                      <td><?=$rowStats[$stat->$param6]['seen'];?></td>
                           <td>
                           <?
                           if($rowStats[$stat->$param6]['answer_time']!='No'){
                               
                               if($rowStats[$stat->$param6]['answer_time']!=NULL)
                                     echo @date("d-m-Y h:i:sA",$rowStats[$stat->$param6]['answer_time']);
                          
                           }
                           else
                           echo $rowStats[$stat->$param6]['answer_time'];
                           
                           ?></td>
                           
                             <td><?php echo $stat->$param7; ?></td>
                        <td><?=$rowStats[$stat->$param8]['seen'];?></td>
                   <td>
                           <?
                           if($rowStats[$stat->$param8]['answer_time']!='No'){
                               
                               if($rowStats[$stat->$param8]['answer_time']!=NULL)
                                     echo @date("d-m-Y h:i:sA",$rowStats[$stat->$param8]['answer_time']);
                          
                           }
                           else
                           echo $rowStats[$stat->$param8]['answer_time'];
                           
                           ?></td>
                           
                        
                        
                          <td><?php echo $stat->$param9; ?></td>
                     <td><?=$rowStats[$stat->$param10]['seen'];?></td>
                         <td>
                           <?
                           if($rowStats[$stat->$param10]['answer_time']!='No'){
                               
                               if($rowStats[$stat->$param10]['answer_time']!=NULL)
                                     echo @date("d-m-Y h:i:sA",$rowStats[$stat->$param10]['answer_time']);
                          
                           }
                           else
                           echo $rowStats[$stat->$param10]['answer_time'];
                           
                           ?></td>
                           
                           
                          <td><?php echo $stat->$param11; ?></td>
                      <td><?=$rowStats[$stat->$param12]['seen'];?></td>
                        <td>
                           <?
                           if($rowStats[$stat->$param12]['answer_time']!='No'){
                               
                               if($rowStats[$stat->$param12]['answer_time']!=NULL)
                                     echo @date("d-m-Y h:i:sA",$rowStats[$stat->$param12]['answer_time']);
                          
                           }
                           else
                           echo $rowStats[$stat->$param12]['answer_time'];
                           
                           ?></td>
                           
               
                        
                    
                  </tr>
              <?php } } ?>    
              </tbody>
          </table>
         <?php
         
$output = ob_get_contents();
ob_end_clean();

 $output .= '<input type="hidden" id="rowcount" name="rowcount" value="' . $_GET["rowcount"] . '" />';

 if(!empty($perpageresult)) {
    $output .= '<div id="pagination">' . $perpageresult . '</div>';
    }

echo $output;
    
    break;
    
    
    
    
    
    
    
//auto message ....
	  case 'welcome_stats':
        case 'like_stats':
        case 'visit_stats':
        case 'match_stats':
                
        $perPage = new PerPage();
        $perPage->perpage=100;
        
        $search_date=secureEncode($_GET['search_date']);
        $from='';
        $till='';
        $dateFilter='';
        
        if(!empty($search_date)){
            
            $date = explode(' to ', $search_date);
            $date = array_map(function($el) {
            $el = trim($el);
            $el = explode('/', $el);
            $el = array($el[2], $el[0], $el[1]);
            $el = implode('-', $el);
            return $el;
            }, $date);
            
            if (count($date) == 2) {
            $from = strtotime($date[0] . ' 00:00:00');
            $till = strtotime($date[1] . ' 23:59:59');
            }else{
            
            $date = $date[0];
            if(empty($date))
            $date=date('Y/m/d');
            $from = strtotime($date . ' 00:00:00');
            $till = strtotime($date. ' 23:59:59');
            
            }
            
         $dateFilter ="  AND  ( CAST(UNIX_TIMESTAMP(welcome_fake_user_time) AS SIGNED) >= $from AND  CAST(UNIX_TIMESTAMP(welcome_fake_user_time) AS SIGNED) <= $till)";
         
           $fromdate=$from;
            $tilldate=$till;
  
        }else{
             $date=date('Y/m/d');
            $fromdate = strtotime($date . ' 00:00:00');
            $tilldate = strtotime($date. ' 23:59:59'); 
            
        $dateFilter ="  AND  ( CAST(UNIX_TIMESTAMP(welcome_fake_user_time) AS SIGNED) >= $fromdate AND  CAST(UNIX_TIMESTAMP(welcome_fake_user_time) AS SIGNED) <= $tilldate)";

            
        }
        
        $action=secureEncode($_GET['action']);
        
         if($action=='welcome_stats')
        $action='welcome';
        
        if($action=='visit_stats')
        $action='visit';
        
        if($action=='like_stats')
        $action='like';
        
        if($action=='match_stats')
        $action='match';

	   $sqlMsg1   = "SELECT `user_id` FROM `auto_campaign_list` WHERE $action"."_1 > 0 AND $action"."_message_1 !='' AND 
	   ( CAST(UNIX_TIMESTAMP($action"."_message_1_send_date) AS SIGNED) >= $fromdate AND  CAST(UNIX_TIMESTAMP($action"."_message_1_send_date) AS SIGNED) <= $tilldate)";

     $statsMsg1Count = $mysqli->query($sqlMsg1 );
     $statsMsg1Count = $statsMsg1Count->num_rows;
          
     $sqlMsg2   =  "SELECT `user_id` FROM `auto_campaign_list` WHERE $action"."_2 > 0 AND $action"."_message_2 !=''
     AND  ( CAST(UNIX_TIMESTAMP($action"."_message_2_send_date) AS SIGNED) >= $fromdate AND  CAST(UNIX_TIMESTAMP($action"."_message_2_send_date) AS SIGNED) <= $tilldate)";
     $statsMsg2Count = $mysqli->query($sqlMsg2 );
     $statsMsg2Count = $statsMsg2Count->num_rows;
     
          
     $sqlMsg3   =  "SELECT `user_id` FROM `auto_campaign_list` WHERE $action"."_3 > 0 AND $action"."_message_3 !=''
     AND  ( CAST(UNIX_TIMESTAMP($action"."_message_3_send_date) AS SIGNED) >= $fromdate AND  CAST(UNIX_TIMESTAMP($action"."_message_3_send_date) AS SIGNED) <= $tilldate)";
     $statsMsg3Count = $mysqli->query($sqlMsg3 );
     $statsMsg3Count = $statsMsg3Count->num_rows;
     
          
     $sqlMsg4  =  "SELECT `user_id` FROM `auto_campaign_list` WHERE $action"."_4 > 0 AND $action"."_message_4 !=''
     AND  ( CAST(UNIX_TIMESTAMP($action"."_message_4_send_date) AS SIGNED) >= $fromdate AND  CAST(UNIX_TIMESTAMP($action"."_message_4_send_date) AS SIGNED) <= $tilldate)";
     $statsMsg4Count = $mysqli->query($sqlMsg4 );
     $statsMsg4Count = $statsMsg4Count->num_rows;
     
   
       $sql   = 'SELECT  DATE_FORMAT(welcome_fake_user_time,"%d-%m-%Y %h:%i %p") as reg_date ,
           DATE_FORMAT('.$action.'_message_1_send_date,"%h:%i %p") as '.$action.'_message_1_sent_date ,
       DATE_FORMAT('.$action.'_message_2_send_date,"%h:%i %p") as '.$action.'_message_2_sent_date ,
        DATE_FORMAT('.$action.'_message_3_send_date,"%h:%i %p") as '.$action.'_message_3_sent_date ,
                 DATE_FORMAT('.$action.'_message_4_send_date,"%h:%i %p") as '.$action.'_message_4_sent_date ,
     auto_campaign_list.*  FROM `auto_campaign_list` WHERE  1  '. $dateFilter .' ORDER BY welcome_fake_user_time DESC' ;

    $paginationlink = "/requests/admin.php?action=".$action."_stats&page=";	
  
    $pagination_setting = $_GET["pagination_setting"];
        
        $page = 1;
    if(!empty($_GET["page"])) {
    $page = $_GET["page"];
    }
    
    $start = ($page-1)*$perPage->perpage;
    if($start < 0) $start = 0;
    
     $query =  $sql . " limit " . $start . "," . $perPage->perpage; 
  
   
    $stats = $mysqli->query($query );
    
  //  if(empty($_GET["rowcount"])) {
    $statsCount = $mysqli->query($sql );
    $_GET["rowcount"] = $statsCount->num_rows;
    
   // }
   // var_dump($statsCount->num_rows);die;
    if($pagination_setting == "prev-next") {
    	$perpageresult = $perPage->getPrevNext($_GET["rowcount"], $paginationlink,$pagination_setting);	
    } else {
    	$perpageresult = $perPage->getAllPageLinks($_GET["rowcount"], $paginationlink,$pagination_setting);	
    }
    
    
    $output = '';
ob_start();

    ?>
    
      <div class="card-group" id="results">
      <div class="card card-body text-center">
          <div class="d-flex flex-row align-items-center">
              <div class="card-header__title m-0">Total Msg #1 Sent</div>
              <div class="text-amount ml-auto font16"><?= $statsMsg1Count;?></div>
          </div>
      </div>
      <div class="card card-body text-center">
          <div class="d-flex flex-row align-items-center">
              <div class="card-header__title m-0">Total Msg #2 Sent</div>
              <div class="text-amount ml-auto font16"><?= $statsMsg2Count;?></div>
          </div>
      </div>      
      <div class="card card-body text-center">
          <div class="d-flex flex-row align-items-center">
              <div class="card-header__title m-0">Total Msg #3 Sent</div>
              <div class="text-amount ml-auto font16"><?= $statsMsg3Count;?></div>
          </div>
      </div>    
     <div class="card card-body text-center">
          <div class="d-flex flex-row align-items-center">
              <div class="card-header__title m-0">Total Msg #4 Sent</div>
              <div class="text-amount ml-auto font16"><?= $statsMsg4Count;?></div>
          </div>
      </div> 
      </div>
    
     <table style="font-size: 12px;width:100%;" class="table mb-0 thead-border-top-0">
              <thead>
                  <tr style="background: #fff">
                      <th>#</th>
                      <th>Join Date </th>
                      <th>Fakeuser </th>
                      <th>RealUser </th>
                  
                      <th>Msg 1 Sent </th>
                      <th>Msg 1 Seen </th>
                      <th>Msg 1 Ans </th>
                    
                    <th>Msg 2 Sent </th>
                    <th>Msg 2 Seen </th>
                    <th>Msg 2 Ans </th>
                    
                    
                    <th>Msg 3 Sent </th>
                    <th>Msg 3 Seen </th>
                    <th>Msg 3  Ans </th>
                    
                    
                    <th>Msg 4 Sent </th>
                    <th>Msg 4 Seen </th>
                    <th>Msg 4 Ans </th>
                    
                  
                  </tr>
              </thead>
              <tbody class="list" id="statsTable" style="overflow-y: scroll">
              <?php 
                if ($stats->num_rows > 0) { 
                    
                    if($page>1)
                    $counter=1+($page-1)*$perPage->perpage;
                    else
                    $counter=1;
                
                $param0=$action.'_fake_user';
                $param1=$action.'_1_chat_id';
                $param2=$action.'_2_chat_id';
                $param3=$action.'_3_chat_id';
                $param4=$action.'_4_chat_id';
                
                $param5=$action.'_message_1_sent_date';
                $param6=$action.'_1_chat_id';
                $param7=$action.'_message_2_sent_date';
                $param8=$action.'_2_chat_id';
                $param9=$action.'_message_3_sent_date';
                $param10=$action.'_3_chat_id';
                $param11=$action.'_message_4_sent_date';
                $param12=$action.'_4_chat_id';
                    
                  while($stat = $stats->fetch_object()) { 
                  
               $rowStats=   messageSeenStats($stat->user_id,$stat->$param0,$stat->$param1,$stat->$param2,$stat->$param3,$stat->$param4);
        
                  ?>
                  <tr  style="outline: thin solid">
                      <td><?php echo  $counter;  $counter++; ?></td>
                      <td><?php echo $stat->reg_date; ?></td>
                    
                    
                     <td><?php
                      
                      if( $stat->$param0==null) {
                      echo "No $action";}
                      else 
                      {
                      
                          if(isUserDeleted( $stat->$param0))
                        echo '<span style="text-decoration: line-through;">'. $stat->$param0.'</span>';
                        else 
                       echo '<span>'. $stat->$param0.'</span>';
                      }
                       
                       ?>
                         </td>
                    
                  
                                                    
                       <td>
                             <?php
                          if(isUserDeleted( $stat->user_id))
                        echo '<span style="text-decoration: line-through;">'. $stat->user_id.'</span>';
                        else 
                       echo '<span>'. $stat->user_id.'</span>';
                       ?>
                         </td>
                                    
                      
                      <td><?php echo $stat->$param5; ?></td>
                      <td><?=$rowStats[$stat->$param6]['seen'];?></td>
                           <td>
                           <?
                           if($rowStats[$stat->$param6]['answer_time']!='No'){
                               
                               if($rowStats[$stat->$param6]['answer_time']!=NULL)
                                     echo @date("d-m-Y h:i:sA",$rowStats[$stat->$param6]['answer_time']);
                          
                           }
                           else
                           echo $rowStats[$stat->$param6]['answer_time'];
                           
                           ?></td>
                           
                             <td><?php echo $stat->$param7; ?></td>
                        <td><?=$rowStats[$stat->$param8]['seen'];?></td>
                   <td>
                           <?
                           if($rowStats[$stat->$param8]['answer_time']!='No'){
                               
                               if($rowStats[$stat->$param8]['answer_time']!=NULL)
                                     echo @date("d-m-Y h:i:sA",$rowStats[$stat->$param8]['answer_time']);
                          
                           }
                           else
                           echo $rowStats[$stat->$param8]['answer_time'];
                           
                           ?></td>
                           
                        
                        
                          <td><?php echo $stat->$param9; ?></td>
                     <td><?=$rowStats[$stat->$param10]['seen'];?></td>
                         <td>
                           <?
                           if($rowStats[$stat->$param10]['answer_time']!='No'){
                               
                               if($rowStats[$stat->$param10]['answer_time']!=NULL)
                                     echo @date("d-m-Y h:i:sA",$rowStats[$stat->$param10]['answer_time']);
                          
                           }
                           else
                           echo $rowStats[$stat->$param10]['answer_time'];
                           
                           ?></td>
                           
                           
                          <td><?php echo $stat->$param11; ?></td>
                      <td><?=$rowStats[$stat->$param12]['seen'];?></td>
                        <td>
                           <?
                           if($rowStats[$stat->$param12]['answer_time']!='No'){
                               
                               if($rowStats[$stat->$param12]['answer_time']!=NULL)
                                     echo @date("d-m-Y h:i:sA",$rowStats[$stat->$param12]['answer_time']);
                          
                           }
                           else
                           echo $rowStats[$stat->$param12]['answer_time'];
                           
                           ?></td>
                           
               
                        
                    
                  </tr>
              <?php } } ?>    
              </tbody>
          </table>
         <?php
         
$output = ob_get_contents();
ob_end_clean();

 $output .= '<input type="hidden" id="rowcount" name="rowcount" value="' . $_GET["rowcount"] . '" />';

 if(!empty($perpageresult)) {
    $output .= '<div id="pagination">' . $perpageresult . '</div>';
    }

echo $output;
    
    break;


	 case 'fakeimport':
	//	print_r($_FILES);


		$data = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcefghijklmnopqrstuvwxyz';
				$new_password= substr(str_shuffle($data), 0, 17);


	 $handle = fopen($_FILES['file']['tmp_name'], "r");
		$headers = fgetcsv($handle, 0, ";");
		while (($data = fgetcsv($handle, 0, ";")) !== FALSE) 
		{
	///	print_r($data);


            $new_password='L0ilEV;RT_yE';

			$username = secureEncode($data[0]);
			$name=$username;
			$email = $username.'@freunden.org';
			$password =  $new_password;
		
			$gender = 2;
			$city =  secureEncode($data[1]);
			$country = secureEncode($data[2]);
			$dob=explode(".",$data[3]);

			$city=trim($city);
			$country=trim($country);
            
			$day=$dob[0];
			$month=$dob[1];
			$year=$dob[2];

			$lat = '' ;
			$lng =  '';
			$looking = 1;




		/*	$url="http://api.positionstack.com/v1/forward?access_key=5eb7fe58749081331b5da2da5ba8817f&query=$city,$country";

			$data=file_get_contents($url);

			$data=json_decode($data,true);
			
			foreach($data['data'] as $key=>$value){

			// print_r($value);

			$scity=$value['locality'];
			$scou=$value['country'];

			if($scity==$city && $country==$scou){

			$lat=$value['latitude'];
			$lng=$value['longitude'];
			break;
			}


			}

			*/

			$url="https://api.opencagedata.com/geocode/v1/json?q=$city,$country&key=59fd37e031484b8ba77d8824a1dc2fbc";

			$dataGeo=file_get_contents($url);

			$dataGeo=json_decode($dataGeo,true);
			foreach($dataGeo['results'] as $key=>$value){
					$lat=$value['geometry']['lat'];
					$lng=$value['geometry']['lng'];
			  break;
				
			}


			$date = date('m/d/Y', time());


			$referido='';
			$photo = secureEncode($_POST['photo']);


			if($city == "" || $city == NULL){
				$city = $country;	
			}
			
				// get the users Date of Birth
				$BirthDay=$day;
				if($day<9)
				$BirthDay   = '0'.$day;
         
				$BirthMonth = '01';

				$BirthMonth=$month;
				if($month<9)
				$BirthMonth   = '0'.$month;

				$BirthYear  = $year;

				//convert the users DoB into UNIX timestamp
				$stampBirth = mktime(0, 0, 0, $BirthMonth, $BirthDay, $BirthYear);

				// fetch the current date (minus 18 years)
				$today['day']   = date('d');
				$today['month'] = date('m');
				$today['year']  = date('Y') - 18;

				// generate todays timestamp
				$stampToday = mktime(0, 0, 0, $today['month'], $today['day'], $today['year']);

				if ($stampBirth < $stampToday) {
					//echo 'User is 18 years or older!';
				} else {
				//	echo 'Error'.$sm['lang'][1023]['text'];
						////exit;	
						echo 't1';
						continue;
				}

			$photo = secureEncode($_POST['photo']);

			$referido='import';

									
			$birthday = date('F', mktime(0, 0, 0, $month, 10)).' '.secureEncode($day).', '.secureEncode($year);
			$age = date('Y') - $year;	

			$bio = $sm['lang'][322]['text']." ".$name.", ".$age." ".$sm['lang'][323]['text']." ".$city." ".$country;
            // print_r($data);
          //   die;
			$bio=secureEncode($data[4]);
			
			$ip = getUserIpAddr();

			$sage = '18,99,1';
			$checkUsername = checkIfExist('users','username',$username);
			if($checkUsername == 1){
					echo 't4';
			     continue;				
			}
			if(validate_username($username) == 0){
					echo 't2';
				continue;				
			}
			if(substr($username, -1) == '.'){
					echo 't3';
				continue;				
			}

		
				$sage = 18;
				$age2=99;
				$sage = $sage.','.$age2.',1';							
			
				
			//CHECK IF USER EXIST
			$email_check = $mysqli->query("SELECT email FROM users WHERE email = '".$email."'");	
			if($email_check->num_rows == 1 ){
			                  continue;
			} else {
				$salt = base64_encode($name.$email);
				$pswd = crypt($password,$salt);
				$lang = getData('languages','id','WHERE id = '.$_SESSION['lang']);
				if($lang == 'noData'){
					$lang = $sm['plugins']['settings']['defaultLang'];
				}

				$user_type =1; //fake
				$fakeVerified=1;
				$city='';

				 $query = "INSERT INTO users (verified,fake,name,referido,email,pass,age,birthday,gender,city,country,lat,lng,looking,lang,join_date,bio,s_gender,s_age,credits,online_day,password,ip,last_access,username,join_date_time)
										VALUES ('".$fakeVerified."','".$user_type."','".$name."', '".$referido."', '".$email."','".$pswd."','".$age."','".$birthday."','".$gender."','".$city."','".$country."','".$lat."','".$lng."','".$looking."','".$lang."','".$date."','".$bio."','".$looking."','".$sage."',0,0,'".$password."','".$ip."','".time()."','".$username."','".time()."')";				
										
										//die;
			if ($mysqli->query($query) === TRUE) {
					$last_id = $mysqli->insert_id;
					
					//$mysqli->query("INSERT INTO users_videocall (u_id) VALUES ('".$last_id."')");

					//free premium
					$free_premium = 0;
					$allG = count(siteGenders($lang));
					$allG = $allG + 1;					
					if($sm['plugins']['rewards']['freePremiumGender'] == $gender || $sm['plugins']['rewards']['freePremiumGender'] == $allG){
						$free_premium = $sm['plugins']['rewards']['freePremium'];
					}
					$time = time();	
					$extra = 86400 * $free_premium;
					$premium = $time + $extra;
					$mysqli->query("INSERT INTO users_premium (uid,premium) VALUES ('".$last_id."','".$premium."')");

					if($photo != ''){
						$query2 = "INSERT INTO users_photos (u_id,photo,profile,thumb,approved) VALUES ('".$last_id."','".$photo."',1,'".$photo."',1)";
						$mysqli->query($query2);
					}
					$mysqli->query("INSERT INTO users_notifications (uid) VALUES ('".$last_id."')");
					$mysqli->query("INSERT INTO users_extended (uid) VALUES ('".$last_id."')");	
				
						////	echo $last_id;

						/////


          /////Now add random profile intrestes......

       $randlimit=rand(5,10);
 
       $intrSql=" SELECT * FROM `interest` WHERE 1  ORDER BY RAND()  LIMIT 0,$randlimit";


  	  $intrests = $mysqli->query($intrSql);

       if ($intrests->num_rows > 0) { 
					while($int_row = $intrests->fetch_object()){
			$i_id = $int_row->id;		
			$mysqli->query("INSERT INTO users_interest (i_id,u_id) VALUES ('".$i_id."','".$last_id."')");
			
					}
        }
            

			/// personal settings.....


			  $profileQA=array();

			  $profileQA['8']=array('Ja','Nein','keine Angabe','Gelegentlich','Partyraucher');
			  $profileQA['9']=array('Ja','keine Angabe','Busch','rasiert','Strich');
			  $profileQA['5']=array('keine Angabe','Angestellte/r','Freiberufler/in','Student/in','Arbeitssuchend','Selbständig');
			  $profileQA['1']=array('keine Angabe','verheiratet','Single','Beziehung','geschieden');
			  $profileQA['6']=array('keine Angabe','dünn','athletisch','normal','muskulös','kurvig','mollig');
			  $profileQA['7']=array('keine Angabe','Griechisch','Normale Erotik','Rollenspiele','Piercings','Doggy Style','Französisch','Dirty Talk','Outdoor','Swinging','Tattoos','Voyerismus','Kuschelerotik');
			
			   srand(@mktime());

 
            $q8=$profileQA['8'][rand(0, count($profileQA['8']) - 1)];
			$mysqli->query("INSERT INTO users_profile_questions (uid,qid,answer) VALUES ('".$last_id."',8,'".$q8."')");

            $q9=$profileQA['9'][rand(0, count($profileQA['9']) - 1)];
			$mysqli->query("INSERT INTO users_profile_questions (uid,qid,answer) VALUES ('".$last_id."',9,'".$q9."')");

            $q5=$profileQA['5'][rand(0, count($profileQA['8']) - 1)];
			$mysqli->query("INSERT INTO users_profile_questions (uid,qid,answer) VALUES ('".$last_id."',5,'".$q5."')");

            $q1=$profileQA['1'][rand(0, count($profileQA['1']) - 1)];
				$mysqli->query("INSERT INTO users_profile_questions (uid,qid,answer) VALUES ('".$last_id."',1,'".$q1."')");

            $q6=$profileQA['6'][rand(0, count($profileQA['6']) - 1)];
			$mysqli->query("INSERT INTO users_profile_questions (uid,qid,answer) VALUES ('".$last_id."',6,'".$q6."')");

            $q7=$profileQA['7'][rand(0, count($profileQA['7']) - 1)];
			$mysqli->query("INSERT INTO users_profile_questions (uid,qid,answer) VALUES ('".$last_id."',7,'".$q7."')");



           ///Now import images..............



	$docroot=$_SERVER['DOCUMENT_ROOT'];
	$domain= $sm['config']['site_url'].'work_images/users/uploads';   
	$userDir= $_SERVER['DOCUMENT_ROOT'].'/profileImport/';  
	
	  $userName=$username;
	  $userId= $last_id;	
	  echo "Last ID:$last_id"."<bR>";
      $userDir=$userDir.$userName;


      $files = array_diff(scandir($userDir), array('.', '..'));
   $sql="SELECT *  FROM `users_photos`  WHERE u_id='$userId' AND profile=1";
		$currentUserP = $mysqli->query($sql);
            	if ($currentUserP->num_rows > 0) { //if user has primary photo do nothing...
					//echo $userId.'user already has primary photo.skipping <br>';
            	}else{
							foreach($files as $k=>$v){                        
							$photo=$domain.'/'.$userId.'/'.$v;
							$thumb=$domain.'/'.$userId.'/thumb_'.$v;		
							$target1=  $docroot."/work_images/users/uploads/".$userId;
							if (!is_dir($target1)) {
							mkdir($target1, 0777, true);

							}				
							$targetFile=$target1.'/'.$v;
						$sourceFile= $_SERVER['DOCUMENT_ROOT'].'/profileImport/'.$userName.'/'.$v;
							if(!file_exists($targetFile)){
							copy($sourceFile,$targetFile);
							}
							$targetThumb=$target1.'/thumb_'.$v;
							if(!file_exists($targetThumb)){
							make_thumb($sourceFile, $targetThumb, 200);
							}
							//  echo $photo;
							// echo '<bR>';		   
							$query2 = "INSERT INTO users_photos (u_id,photo,profile,thumb,approved) VALUES ('".$userId."','".$photo."',1,'".$thumb."',1)";
							$mysqli->query($query2);
							break;
							} //for loop
		   } 


            	///import other profile photos

						$i=1;

		foreach($files as $k=>$v){

				if($i==1){
				$i++;
				continue;
				}

		$photo=$domain.'/'.$userId.'/'.$v;
		$thumb=$domain.'/'.$userId.'/thumb_'.$v;
		////
		$target1=  $docroot."/work_images/users/uploads/".$userId;

		$sql="SELECT *  FROM `users_photos`  WHERE photo='$photo'";
		$currentUserPhoto = $mysqli->query($sql);
		if ($currentUserPhoto->num_rows > 0) {
		//echo "$photo already exists. Skipping! <br>";
		continue;
		}

						if (!is_dir($target1)) {
						mkdir($target1, 0777, true);

						}

						$targetFile=$target1.'/'.$v;
						$sourceFile= $_SERVER['DOCUMENT_ROOT'].'/profileImport/'.$userName.'/'.$v;

						if(!file_exists($targetFile)){
						copy($sourceFile,$targetFile);
						}

						$targetThumb=$target1.'/thumb_'.$v;

						if(!file_exists($targetThumb)){
						make_thumb($sourceFile, $targetThumb, 200);
						}
						//   echo $thumb;
						// echo '<bR>';
		
			$query2 = "INSERT INTO users_photos (u_id,photo,profile,thumb,approved) VALUES ('".$userId."','".$photo."',0,'".$thumb."',1)";
			$mysqli->query($query2);


				}

		   /////////////////////////////
                 
				} // if user record successfully inserts
					

////////////////////


		} // if user does not exists....

		} //while loop of csv 
fclose($handle);

	echo $new_password;

	break;
  
  case 'sendPush':
		$rid = secureEncode($_GET['rid']);
		$mid = secureEncode($_GET['mid']);
sendMessageOnePush($mid,$rid);
break;



}

}


switch ($_POST['action']) {
/*
   case 'send_text_message_reported':
  $uid =secureEncode($_POST['s_id']);
   $s_id =secureEncode($_POST['s_id']);
    $r_id =secureEncode($_POST['r_id']);
    
  $moderatorUserName='admin';
  $moderatorID='';
          $online_day = getData('users','online_day','where id ='.$r_id);
          $fake = getData('users','fake','where id ='.$r_id);
        $mysqli->query("UPDATE users SET last_access = '".$time."' WHERE id = '".$s_id."'");

        $photos_base = "/assets/sources/uploads/";
        $allowedExts = array("gif", "jpeg", "jpg", "png");
        $time = time();
       
      $ext = strtolower(pathinfo($_POST['filename'], PATHINFO_EXTENSION));
        
        $file = 'uploads/'.$uid.$time.'.jpg';
        
        // Save image 
     file_put_contents($_SERVER['DOCUMENT_ROOT'].$photos_base.$uid.$time.'.jpg', file_get_contents($_POST['filename']));
        
        $photo = $sm['config']['site_url'].'assets/sources/'.$file;

        
        if(in_array($ext, $allowedExts)) 
        { 
           $outfileName=$photo;
           move_uploaded_file($tmp, $_SERVER['DOCUMENT_ROOT'].'/assets/sources/'.$file);	
        $sql="INSERT INTO chat (modusername,moduser,s_id,r_id,time,message,fake,online_day,photo) VALUES ('".$moderatorUserName."','".$moderatorID."','".$s_id."','".$r_id."','".$time."','".$photo."','".$fake."','".$online_day."',1)";

	
		$mysqli->query($sql);


	   $sqlSeen=	"UPDATE chat set seen = 1 where s_id = '$r_id' and r_id = '$s_id'";
        	$mysqli->query($sqlSeen);
		
	   $sqlremoveban=	"UPDATE reports_chats set viewed = 1 where user_from = '$r_id' and user_to = '$s_id'";
        	$mysqli->query($sqlremoveban);
        }
        
        
        
		break;

*/

   case 'send_text_message_reported':
  $uid =secureEncode($_POST['s_id']);
   $s_id =secureEncode($_POST['s_id']);
    $r_id =secureEncode($_POST['r_id']);

	   $msgtype =secureEncode($_POST['qtype']);
	     $message =secureEncode($_POST['message']);
    
  $moderatorUserName='admin';
  $moderatorID='1516536564';

    	if( $sm['user']['moderator'] == 'Admin Moderator') { 
		    $moderatorID= $sm['user']['id'];
			$moderatorUserName=$sm['user']['username'];		
		}

		
		$query2check = $mysqli->query("SELECT user_from FROM reports_chats where user_from = '$r_id' and user_to = '$s_id' AND  viewed = 0");
	if($query2check->num_rows == 0){
		$arr['success'] = 0;
		$arr['message'] = 'Chat does not exist in reported list';
     echo json_encode($arr);
				exit;
	}

          $online_day = getData('users','online_day','where id ='.$r_id);
          $fake = getData('users','fake','where id ='.$r_id);
        $mysqli->query("UPDATE users SET last_access = '".$time."' WHERE id = '".$s_id."'");

		if(  $msgtype=='message'){

		  $sql="INSERT INTO chat (modusername,moduser,s_id,r_id,time,message,fake,online_day,photo) VALUES ('".$moderatorUserName."','".$moderatorID."','".$s_id."','".$r_id."','".$time."','".$message."','".$fake."','".$online_day."',0)";
         $mysqli->query($sql);

	   $sqlSeen=	"UPDATE chat set seen = 1 where s_id = '$r_id' and r_id = '$s_id'";
        	$mysqli->query($sqlSeen);
		
	   $sqlremoveban=	"UPDATE reports_chats set viewed = 1 where user_from = '$r_id' and user_to = '$s_id'";
        	$mysqli->query($sqlremoveban);
        	
        	  $mysqli->begin_transaction(); 
	     	$sqlDelQ="DELETE FROM chat_queue WHERE s_id=$r_id AND r_id=$s_id";
		     $mysqli->query($sqlDelQ);
		       $mysqli->commit();


		}else{

				if( $sm['user']['moderator'] == 'Admin Moderator') {
					$arr['success'] = 0;
					$arr['message'] = 'No allowed';
					 echo json_encode($arr);
					exit;
				}

        $photos_base = "/assets/sources/uploads/";
        $allowedExts = array("gif", "jpeg", "jpg", "png");
        $time = time();
       
      $ext = strtolower(pathinfo($_POST['filename'], PATHINFO_EXTENSION));
        
        $file = 'uploads/'.$uid.$time.'.jpg';
        
        // Save image 
     file_put_contents($_SERVER['DOCUMENT_ROOT'].$photos_base.$uid.$time.'.jpg', file_get_contents($_POST['filename']));
        
        $photo = $sm['config']['site_url'].'assets/sources/'.$file;

        
        if(in_array($ext, $allowedExts)) 
        { 
           $outfileName=$photo;
           move_uploaded_file($tmp, $_SERVER['DOCUMENT_ROOT'].'/assets/sources/'.$file);	
        $sql="INSERT INTO chat (modusername,moduser,s_id,r_id,time,message,fake,online_day,photo) VALUES ('".$moderatorUserName."','".$moderatorID."','".$s_id."','".$r_id."','".$time."','".$photo."','".$fake."','".$online_day."',1)";

	
		$mysqli->query($sql);


	   $sqlSeen=	"UPDATE chat set seen = 1 where s_id = '$r_id' and r_id = '$s_id'";
        	$mysqli->query($sqlSeen);
		
	   $sqlremoveban=	"UPDATE reports_chats set viewed = 1 where user_from = '$r_id' and user_to = '$s_id'";
        	$mysqli->query($sqlremoveban);
        	
        	  $mysqli->begin_transaction(); 
	     	$sqlDelQ="DELETE FROM chat_queue WHERE s_id=$r_id AND r_id=$s_id";
		     $mysqli->query($sqlDelQ);
		       $mysqli->commit();
        }
        
        }
        
		break;



    case 'clear_search_message':
	$_SESSION['mod_message']='';
		break;


		  case 'updateZone':
		      
    $zone1 = secureEncode($_POST['zone1']);	
    $zone2 = secureEncode($_POST['zone2']);	
     $zone3 = secureEncode($_POST['zone3']);	
     
     		      
    $zone1paid = secureEncode($_POST['zonepaid1']);	
    $zone2paid = secureEncode($_POST['zonepaid2']);	
     $zone3paid = secureEncode($_POST['zonepaid3']);	


    $zoneupdate=	"UPDATE zones SET is_paid='$zone1paid',is_enabled='$zone1' where zone_name='zone1'";
    $mysqli->query($zoneupdate);
    
     $zoneupdate=	"UPDATE zones SET is_paid='$zone2paid',is_enabled='$zone2' where zone_name='zone2'";
    $mysqli->query($zoneupdate);
    
     $zoneupdate=	"UPDATE zones SET is_paid='$zone3paid',is_enabled='$zone3' where zone_name='zone3'";
    $mysqli->query($zoneupdate);
    
    
    
    	$arr['success'] = 1;
        		echo json_encode($arr);
				exit;
		break;


 case 'del_banner_campaign':

			$cid = secureEncode($_POST['cid']);	
	$arr['success'] = 1;

	$query = "DELETE FROM banner_campaigns  WHERE id=$cid ";	
	$mysqli->query($query);
	echo json_encode($arr);
				exit;
		break;

case 'edit_banner_campaign':
       
        $time = time();
        
    
  

			$banner_name = secureEncode($_POST['banner_name']);	
			$landing_url = secureEncode($_POST['banner_url']);
		    $banner_zone = secureEncode($_POST['banner_zone_edit']);
		    $bid = secureEncode($_POST['bid']);
		    $is_enabled_edit = secureEncode($_POST['is_enabled_edit']);


			$banner_name=trim($banner_name);
			$landing_url=trim($landing_url);

		if($banner_name == "" || $banner_name == NULL  ){
				$arr['success'] = 0;
				$arr['errors'] = array('message'=>'Please enter banner name.');
				echo json_encode($arr);
				exit;	
			}

		if($landing_url == "" || $landing_url == NULL  ){
				$arr['success'] = 0;
				$arr['errors'] = array('message'=>'Please enter Landing page url.');
				echo json_encode($arr);
				exit;	
			}



			$campaign_check = $mysqli->query("SELECT id FROM banner_campaigns WHERE id!=$bid  AND banner_name = '".$banner_name."'");	

			if($campaign_check->num_rows == 1 ){
				$arr['success'] = 0;
				$arr['errors']= array('message'=>'Banner name already exists!');
				echo json_encode($arr);
				exit;
			}


			$name = $_FILES['banner_file']['name'];
        $banner_destination='';
			if($name!=''){
	
	$ext = pathinfo($name, PATHINFO_EXTENSION);	
	$ext=strtolower($ext);
	  $allowedExts = array("gif", "jpeg", "jpg", "png");
   if(!in_array($ext, $allowedExts)) 
        { 
        $arr['success'] = 0;
        $arr['errors'] = array('message'=>'Image extension not allowed.');
		echo json_encode($arr);
				exit;	
        }
	$rand_name  = "banner_".rand(1000000,100000000).".".$ext;
	
	  $destination =  $_SERVER['DOCUMENT_ROOT'].'/assets/sources/'.$rand_name;
	  $banner_destination='/assets/sources/'.$rand_name;
	 move_uploaded_file($_FILES['banner_file']['tmp_name'],$destination);	


			}
			
			if(!empty($banner_destination))
			 $query = "UPDATE banner_campaigns SET banner_name='$banner_name',banner_url='$banner_destination',landing_url='$landing_url',zone_name='$banner_zone',is_enabled='$is_enabled_edit' WHERE id=$bid";	
			 else
        $query = "UPDATE banner_campaigns SET banner_name='$banner_name',landing_url='$landing_url',zone_name='$banner_zone',is_enabled='$is_enabled_edit' WHERE id=$bid";	
		if ($mysqli->query($query) === TRUE) {


				$arr['id']=$bid;
				$arr['banner_name']=$banner_name;
				$arr['banner_image']=$banner_destination;
				$arr['banner_zone']=$banner_zone;

				$arr['landing_url']=$landing_url;

				$arr['caction']='Edit';
				$arr['success'] = 1;
				$arr['errors']= array('message'=>'Banner edited successfully!');
					echo json_encode($arr);	
						exit;	


		}
									
					echo json_encode($arr);	
						exit;	
	
		break;


   case 'add_banner_campaign':
       
        $time = time();
        
    
  

			$banner_name = secureEncode($_POST['banner_name']);	
			$landing_url = secureEncode($_POST['banner_url']);
		    $banner_zone = secureEncode($_POST['banner_zone']);

			$banner_name=trim($banner_name);
			$landing_url=trim($landing_url);

		if($banner_name == "" || $banner_name == NULL  ){
				$arr['success'] = 0;
				$arr['errors'] = array('message'=>'Please enter banner name.');
				echo json_encode($arr);
				exit;	
			}

		if($landing_url == "" || $landing_url == NULL  ){
				$arr['success'] = 0;
				$arr['errors'] = array('message'=>'Please enter Landing page url.');
				echo json_encode($arr);
				exit;	
			}

			$campaign_check = $mysqli->query("SELECT id FROM banner_campaigns WHERE banner_name = '".$banner_name."'");	

			if($campaign_check->num_rows == 1 ){
				$arr['success'] = 0;
				$arr['errors']= array('message'=>'Banner name already exists!');
				echo json_encode($arr);
				exit;
			}


			$name = $_FILES['banner_file']['name'];
	
	$ext = pathinfo($name, PATHINFO_EXTENSION);	
	$ext=strtolower($ext);
	  $allowedExts = array("gif", "jpeg", "jpg", "png");
   if(!in_array($ext, $allowedExts)) 
        { 
        $arr['success'] = 0;
        $arr['errors'] = array('message'=>'Image extension not allowed.');
		echo json_encode($arr);
				exit;	
        }
	$rand_name  = "banner_".rand(1000000,100000000).".".$ext;
	
	  $destination =  $_SERVER['DOCUMENT_ROOT'].'/assets/sources/'.$rand_name;
	  $banner_destination='/assets/sources/'.$rand_name;
	 move_uploaded_file($_FILES['banner_file']['tmp_name'],$destination);	

	 $query = "INSERT INTO banner_campaigns (banner_name,banner_url,landing_url,zone_name) VALUES ('".$banner_name."', '".$banner_destination."','".$landing_url."', '".$banner_zone."')";	


		if ($mysqli->query($query) === TRUE) {
						$last_id = $mysqli->insert_id;

				$arr['id']=$last_id;
				$arr['banner_name']=$banner_name;
				$arr['banner_image']="<img src='$banner_destination' height='100px' width='100px'>";
				$arr['banner_zone']=$banner_zone;

				$arr['landing_url']=$landing_url;
				$arr['created_date']=date('d F Y, H:i:s',time());
				$arr['caction']='Edit';
				$arr['success'] = 1;
				$arr['errors']= array('message'=>'Banner created successfully!');
					echo json_encode($arr);	
						exit;	


		}
									
					echo json_encode($arr);	
						exit;	
	
		break;





    case 'del_campaign':

			$cid = secureEncode($_POST['cid']);	
	$arr['success'] = 1;

	$query = "DELETE FROM campaigns  WHERE id=$cid ";	
	$mysqli->query($query);
	echo json_encode($arr);
				exit;
		break;


   case 'edit_campaign':

			$cid = secureEncode($_POST['cid']);	
			$cname = secureEncode($_POST['cname']);	

	$arr['success'] = 1;

	$query = "UPDATE campaigns SET campaign_name='$cname'  WHERE id=$cid LIMIT 1 ";	
	$mysqli->query($query);
	echo json_encode($arr);
				exit;
		break;

 case 'chat_count_queue':

	
	$arr['success'] = 1;

//	$queryQ = $mysqli->query("SELECT DISTINCT s_id,r_id FROM chat ch WHERE fake = 1 AND s_id != '0' AND (SELECT COUNT(1) FROM chat WHERE s_id = ch.r_id AND r_id = ch.s_id AND id > ch.id) = 0");
	$queryQ = $mysqli->query("SELECT DISTINCT s_id,r_id FROM chat where fake = 1 and s_id > 0 and seen=0 ");




	$arr['chatcount'] =$queryQ->num_rows;

	echo json_encode($arr);
				exit;
		break;


  case 'set_def_promo':

        $pid=intval($_POST['pid']);
		 $mysqli->query("UPDATE promos SET is_default='Yes'  WHERE id=$pid");	
         if( $mysqli->affected_rows == 1 ){
             	 $mysqli->query("UPDATE promos SET is_default='No'  WHERE id!=$pid");	
             	 
         }
         	$arr['success'] = 1;
				$arr['errors']= array('message'=>'Promo updated successfully!');
				
	echo json_encode($arr);
				exit;
  	break;


    case 'add_promo':


			$pname = secureEncode($_POST['pname']);	
			$pdiscount = secureEncode($_POST['pdiscount']);
			$pexpiry = secureEncode($_POST['pexpiry']);


			$pdiscount=trim($pdiscount);
			$pname=trim($pname);

		if($pname == "" || $pname == NULL  ){
				$arr['success'] = 0;
				$arr['errors'] = array('message'=>'Please enter promo name.');
				echo json_encode($arr);
				exit;	
			}


		 if($pdiscount == "" || $pdiscount == NULL  ){
				$arr['success'] = 0;
				$arr['errors'] = array('message'=>'Please enter promo discount.');
				echo json_encode($arr);
				exit;	
			}
			$pdiscount=intval($pdiscount);
			if($pdiscount >90  ){
				$arr['success'] = 0;
				$arr['errors'] = array('message'=>'Discount cannot be greater than 90.');
				echo json_encode($arr);
				exit;	
			}
			
            if($pexpiry == "" || $pexpiry == NULL  ){
				$arr['success'] = 0;
				$arr['errors'] = array('message'=>'Please enter promo expiry date.');
				echo json_encode($arr);
				exit;	
			}

			$promo_check = $mysqli->query("SELECT id FROM promos WHERE promo_name = '".$pname."'");	
			if($promo_check->num_rows == 1 ){
				$arr['success'] = 0;
				$arr['errors']= array('message'=>'Promo name already exists!');
				echo json_encode($arr);
				exit;
			}


$query = "INSERT INTO promos (promo_name, promo_discount, expiry_date) 
          VALUES ('".$pname."', '".$pdiscount."', '".$pexpiry."')";


		if ($mysqli->query($query) === TRUE) {
						$last_id = $mysqli->insert_id;

				$arr['id']=$last_id;
				$arr['pname']=$pname;
				$arr['pdiscount']=$pdiscount;
		    	$arr['cexpiry']="$pexpiry Day(s)";
				$arr['cdate']=date('d F Y, H:i:s',time());
				$arr['caction']="<button data-id='$last_id'  class='btn btn-xs btn-primary'  id='editButton' >Set Default</button>"  ;

				$arr['success'] = 1;
				$arr['errors']= array('message'=>'Promo created successfully!');

					echo json_encode($arr);	
						exit;	


		}

			$arr['success'] = 0;
			$arr['errors'] = array('message'=>'failed to add promo.');								
					echo json_encode($arr);	
						exit;	
	
		break;


    case 'add_campaign':


			$cname = secureEncode($_POST['cname']);	
			$cslug = secureEncode($_POST['cslug']);

			$cslug=trim($cslug);

		if($cname == "" || $cname == NULL  ){
				$arr['success'] = 0;
				$arr['errors'] = array('message'=>'Please enter campaign name.');
				echo json_encode($arr);
				exit;	
			}


				if($cslug == "" || $cslug == NULL  ){
				$arr['success'] = 0;
				$arr['errors'] = array('message'=>'Please enter campaign slug.');
				echo json_encode($arr);
				exit;	
			}

			$campaign_check = $mysqli->query("SELECT id FROM campaigns WHERE campaign_name = '".$cname."'");	
			if($campaign_check->num_rows == 1 ){
				$arr['success'] = 0;
				$arr['errors']= array('message'=>'Campaign name already exists!');
				echo json_encode($arr);
				exit;
			}


	$campaign_check = $mysqli->query("SELECT id FROM campaigns WHERE campaign_slug = '".$cslug."'");	
			if($campaign_check->num_rows == 1 ){
				$arr['success'] = 0;
				$arr['errors']= array('message'=>'Campaign Slig already exists!');
				echo json_encode($arr);
				exit;
			}
//date('d F Y, H:i:s')
	$query = "INSERT INTO campaigns (campaign_name,campaign_slug,created_at) VALUES ('".$cname."', '".$cslug."','".time()."')";	


		if ($mysqli->query($query) === TRUE) {
						$last_id = $mysqli->insert_id;

				$arr['id']=$last_id;
				$arr['cname']=$cname;
				$arr['cslug']=$cslug;
				$arr['curl']="https://www.freunden.org/goto.php?c=$cslug";
				$arr['cdate']=date('d F Y, H:i:s',time());
				$arr['caction']='Edit';
				$arr['success'] = 1;
					echo json_encode($arr);	
						exit;	


		}

	
											
					echo json_encode($arr);	
						exit;	
	
		break;


    case 'del_modgroup':

			$group_id = secureEncode($_POST['group_id']);	
	$arr['success'] = 1;

	$query = "DELETE FROM moderator_groups  WHERE id=$group_id ";	
	$mysqli->query($query);
	echo json_encode($arr);
				exit;
		break;

case 'edit_mod_group':
     
			$group_name = secureEncode($_POST['group_name']);	
		    $gid = secureEncode($_POST['gid']);
			$group_name=trim($group_name);

		if($group_name == "" || $group_name == NULL  ){
				$arr['success'] = 0;
				$arr['errors'] = array('message'=>'Please enter group name.');
				echo json_encode($arr);
				exit;	
			}

			$group_check = $mysqli->query("SELECT id FROM moderator_groups WHERE id!=$gid  AND group_name = '".$group_name."'");	

			if($group_check->num_rows == 1 ){
				$arr['success'] = 0;
				$arr['errors']= array('message'=>'Group name already exists!');
				echo json_encode($arr);
				exit;
			}


        $query = "UPDATE moderator_groups SET group_name='$group_name'  WHERE id=$gid";	
		if ($mysqli->query($query) === TRUE) {
				$arr['id']=$gid;
				$arr['group_name']=$group_name;
				$arr['caction']='Edit';
				$arr['success'] = 1;
				$arr['errors']= array('message'=>'Group name edited successfully!');
					echo json_encode($arr);	
						exit;	

		}
									
					echo json_encode($arr);	
						exit;	
	
		break;


  case 'add_modgroup':


			$group_name = secureEncode($_POST['group_name']);	
			$group_name=trim($group_name);

		if($group_name == "" || $group_name == NULL  ){
				$arr['success'] = 0;
				$arr['errors'] = array('message'=>'Please enter mod group name.');
				echo json_encode($arr);
				exit;	
			}
		$group_check = $mysqli->query("SELECT id FROM moderator_groups WHERE group_name = '".$group_name."'");	
			if($group_check->num_rows == 1 ){
				$arr['success'] = 0;
				$arr['errors']= array('message'=>'Group name already exists!');
				echo json_encode($arr);
				exit;
			}

//date('d F Y, H:i:s')
	 $query = "INSERT INTO moderator_groups (group_name) VALUES ('".$group_name."')";	
			

		if ($mysqli->query($query) === TRUE) {
						$last_id = $mysqli->insert_id;

				$arr['id']=$last_id;
				$arr['group_name']=$group_name;
				$arr['created_date']=date('d F Y, H:i:s',time());
				$cactionData= "<button data-id='$last_id'  data-group_name='$group_name'  data-created_date='".$arr['created_date']."'
		 class='btn btn-xs btn-primary'  id='editButton' >Edit</button>  
		 <button data-id='$last_id'  class='btn btn-xs btn-primary' id='delButton' >Delete</button>";
				
				$arr['caction']=$cactionData;
				$arr['success'] = 1;
				$arr['errors']= array('message'=>'Mod Group added successfully!');
					echo json_encode($arr);	
						exit;	


		}
					
					echo json_encode($arr);	
						exit;	
	
		break;





    case 'add_moderator_user':

     $arr=array();

        if(secureEncode($_POST['fid']) == 'add_mod'){

			$email = secureEncode($_POST['email']);	
			$password = secureEncode($_POST['password']);
			$cpassword = secureEncode($_POST['cpassword']);
		    $date = date('m/d/Y', time());
			$name = secureEncode($_POST['name']);

			$status = secureEncode($_POST['status']);
			$m_type = secureEncode($_POST['m_type']);
			
			$m_group = secureEncode($_POST['mod_group']);
		   $m_allowcp = secureEncode($_POST['mod_allow_cp']);
	
			if( $sm['user']['moderator'] == 'Admin Moderator') { 
		     	$m_group =$sm['user']['mod_group_id'];
		     	
		     if($m_type == 'Admin Moderator')
		          $m_type='Chat Moderator	';
		     	
		}

			$gender = 1;
			$looking = 2;		
		
			if(isset($_GET['dID'])){
				$dID = secureEncode($_GET['dID']);	
			} else {
				$dID = 0;
			}


			$city = 'Berlin';
			$country = 'Germany';
			$lat = '52.52437';
			$lng = '13.41053';
			$username = secureEncode($_POST['uname']);


		if($name == "" || $name == NULL ){
		$arr['error'] = 1;
		$arr['errors'] = array('Name'=>'Please enter name');
		echo json_encode($arr);
		exit;	
		}	

		if($username == "" || $username == NULL ){
				$arr['error'] = 1;
				$arr['errors'] = array('Username'=>'Please enter username');
				echo json_encode($arr);
				exit;	
			}	

	
		
            $age=18;
			$birthday ='January 1, 2002	';

			$time = time();		
		
			$arr['error'] = 0;

			$ip = getUserIpAddr();


		if($email == "" || $email == NULL  ){
				$arr['error'] = 1;
				$arr['errors'] = array('Email'=>'Please enter email address');
				echo json_encode($arr);
				exit;	
			}

			$sage = '18,30,1';
			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$arr['error'] = 1;		
				$arr['errors'] = array('Email'=>'Please enter valid email address');					
				echo json_encode($arr);
				exit;	
			}		
					

		if($password == "" || $password == NULL ){
				$arr['error'] = 1;
				$arr['errors'] = array('Password'=>'Please enter password');
				echo json_encode($arr);
				exit;	
			}	

			
		if($cpassword == "" || $cpassword == NULL ){
				$arr['error'] = 1;
				$arr['errors'] = array('CPassword'=>'Please enter confirm password');
				echo json_encode($arr);
				exit;	
			}	

		if($cpassword != $password ){
				$arr['error'] = 1;
				$arr['errors'] = array('CPassword'=>'Passsword and Confirm Password are not same');
				echo json_encode($arr);
				exit;	
			}	



		if($status==2 || $status=='' ){
				$arr['error'] = 1;
				$arr['errors'] = array('Status'=>'Please select status');
				echo json_encode($arr);
				exit;	
			}	


	if($m_group=='' ){
				$arr['error'] = 1;
				$arr['errors'] = array('Group'=>'Please select moderator group');
				echo json_encode($arr);
				exit;	
			}
			
						
 	if($m_allowcp=='' ){
				$arr['error'] = 1;
				$arr['errors'] = array('CPaste'=>'Please select allow copy/paste option');
				echo json_encode($arr);
				exit;	
			}
			$bio = $sm['lang'][322]['text']." ".$name.", ".$age." ".$sm['lang'][323]['text']." ".$city." ".$country;

			//CHECK IF USER EXIST
			$email_check = $mysqli->query("SELECT email FROM users WHERE email = '".$email."'");	
			if($email_check->num_rows == 1 ){
				$arr['error'] = 1;
				$arr['errors']= array('Email'=>$sm['lang'][188]['text']);
				echo json_encode($arr);
				exit;
			} else {

				$salt = base64_encode($name.$email);
				$pswd = crypt($password,$salt);

				$lang = getData('languages','id','WHERE id = '.$_SESSION['lang']);
				if($lang == 'noData'){
					$lang = $sm['plugins']['settings']['defaultLang'];
				}

				$query = "INSERT INTO users
				(mod_allow_cp,verified,admin,mod_group_id,moderator,name,email,pass,age,birthday,gender,city,country,lat,lng,looking,lang,join_date,bio,s_gender,s_age,
				credits,online_day,password,ip,last_access,username,join_date_time,app_id) VALUES ($m_allowcp,1,'2',$m_group,'".$m_type."','".$name."', '".$email."','".$pswd."','".$age."','".$birthday."','".$gender."','".$city."','".$country."','".$lat."','".$lng."','".$looking."','".$lang."','".$date."','".$bio."','".$looking."','18,35,1',0,0,'".$password."','".$ip."','".time()."','".$username."','".time()."','".$dID."')";	
				if ($mysqli->query($query) === TRUE) {
					$last_id = $mysqli->insert_id;
					//$mysqli->query("INSERT INTO users_videocall (u_id) VALUES ('".$last_id."')");	

					//free premium
					$free_premium = 0;
					$allG = count(siteGenders($lang));
					$allG = $allG + 1;					
					if($sm['plugins']['rewards']['freePremiumGender'] == $gender || $sm['plugins']['rewards']['freePremiumGender'] == $allG){
						$free_premium = $sm['plugins']['rewards']['freePremium'];
					}
					$time = time();	
					$extra = 86400 * $free_premium;
					$premium = $time + $extra;
					$mysqli->query("INSERT INTO users_premium (uid,premium) VALUES ('".$last_id."','".$premium."')");

					$mysqli->query("INSERT INTO users_notifications (uid) VALUES ('".$last_id."')");
					$mysqli->query("INSERT INTO users_extended (uid,field1) VALUES ('".$last_id."','".$sm['lang'][224]['text']."')");

			$_SESSION['mod_message']='Moderator details added Successfully.';
											
					echo json_encode($arr);	
						exit;	
				}		
				//


			}
			
		}else if(secureEncode($_POST['fid']) == 'edit_mod'){


		   $email = secureEncode($_POST['email']);	
			$password = secureEncode($_POST['password']);
			$cpassword = secureEncode($_POST['cpassword']);
			$name = secureEncode($_POST['name']);
			$status = secureEncode($_POST['status']);
			$username = secureEncode($_POST['uname']);
            $m_id= secureEncode($_POST['m_id']);
			$created_at= secureEncode($_POST['created_at']);
			$m_type = secureEncode($_POST['m_type']);
			
				$m_group = secureEncode($_POST['m_group']);
				
				 $m_allowcp = secureEncode($_POST['e_mod_allow_cp']);
				
				
		if( $sm['user']['moderator'] == 'Admin Moderator') { 
		     	$m_group =$sm['user']['mod_group_id'];
		     	
		     if($m_type == 'Admin Moderator')
		          $m_type='Chat Moderator	';
		     	
		}
	

		 $extraValues=  " id=$m_id, moderator='$m_type'";

			if($name == "" || $name == NULL ){
				$arr['error'] = 1;
				$arr['errors'] = array('Name'=>'Please enter name');
			echo json_encode($arr);
			exit;	
			}	

     		 $extraValues.=  " , name='$name'";
 
		if($username == "" || $username == NULL ){
				$arr['error'] = 1;
				$arr['errors'] = array('Username'=>'Please enter username');
				echo json_encode($arr);
				exit;	
			}	

			 $extraValues.=  " , username='$username'";

		if($email == "" || $email == NULL  ){
				$arr['error'] = 1;
				$arr['errors'] = array('Email'=>'Please enter email address');
				echo json_encode($arr);
				exit;	
			}

     		

	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$arr['error'] = 1;		
				$arr['errors'] = array('Email'=>'Please enter valid email address');					
				echo json_encode($arr);
				exit;	
			}		
				

	$email_check = $mysqli->query("SELECT email FROM users WHERE id!=$m_id  AND  email = '".$email."'");	
			if($email_check->num_rows == 1 ){
				$arr['error'] = 1;
				$arr['errors']= array('Email'=>'Email address already in use!');
				echo json_encode($arr);
				exit;
			} 

	$extraValues.=  " , email='$email'";

		if($password != ""  ){
			
			if($cpassword != $password ){
					$arr['error'] = 1;
					$arr['errors'] = array('CPassword'=>'Passsword and Confirm Password are not same');
					echo json_encode($arr);
					exit;	
				}	
				$salt = base64_encode($name.$email);
				$pswd = crypt($password,$salt);
		$extraValues.=  " , pass='$pswd'";
			}	

		if($status==2 || $status==''){
				$arr['error'] = 1;
				$arr['errors'] = array('Status'=>'Please select status');
				echo json_encode($arr);
				exit;	
			}	


		$extraValues.=  " , is_active='$status'";
		
					
			
	if($m_allowcp=='' ){
				$arr['error'] = 1;
				$arr['errors'] = array('ECPaste'=>'Please select allow copy/paste option');
				echo json_encode($arr);
				exit;	
			}
			
		$extraValues.=  " , mod_allow_cp='$m_allowcp'";
			
	if($m_group=='' ){
				$arr['error'] = 1;
				$arr['errors'] = array('Group'=>'Please select moderator group');
				echo json_encode($arr);
				exit;	
			}
			
			$extraValues.=  " , mod_group_id=$m_group";
		
   $sql="UPDATE users SET $extraValues WHERE id = '".$m_id."'  LIMIT 1";

		$mysqli->query($sql);		


		$arr['id']=$m_id;
		$arr['name']=$name;
		$arr['username']=$username;
		$arr['email']=$email;

		if($status==1)
			$status='Active';
		else
		$status='Banned';

		$arr['status']=$status;
		$arr['created_at']=$created_at;

        $_SESSION['mod_message']='Moderator details updated Successfully.';
		echo json_encode($arr);die;
		exit;	

		}
		break;	

  case 'search_moderators_history_performance':
      
      
    $arr = array('status' => false, 'data' => '');
   $mid=intval($_POST['mid']);
    
	$timestamp = time();
  	$dw = date( "w", $timestamp);
  	$date = isset($_POST['date']) ? $_POST['date'] : date('m/d/Y');
  	$date = explode(' to ', $date);
  	$date = array_map(function($el) {
    	$el = trim($el);
    	$el = explode('/', $el);
    	$el = array($el[2], $el[0], $el[1]);
    	$el = implode('-', $el);
    	return $el;
  	}, $date);
  	
  	
  		if (count($date) == 2) {
  		$from = strtotime($date[0] . ' 00:00:00');
  		$till = strtotime($date[1] . ' 23:59:59');
  		}
  		else{
  		 	$date = $date[0];

		if(empty($date))
			$date=date('Y/m/d');

		$from = strtotime($date . ' 00:00:00');
  		$till = strtotime($date. ' 23:59:59');   
  		    
  		}
  		
  		if($mid=='all'){
  		
  $sql="SELECT (select username from users where id =cd.mid) as username  ,cd.chat_id, cd.mid, cd.s_id,cd.r_id , (select count(c.id) from chat c where cd.s_id=c.r_id and cd.r_id=c.s_id and c.id>cd.chat_id ) as rc
  		FROM `chat_moderate_history_data` cd WHERE time>=$from and time <=$till ";
  		}
  		else{
  		
  	$sql="SELECT (select username from users where id =cd.mid) as username  , cd.chat_id, cd.mid, cd.s_id,cd.r_id , (select count(c.id) from chat c where cd.s_id=c.r_id and cd.r_id=c.s_id and c.id>cd.chat_id ) as rc
  		FROM `chat_moderate_history_data` cd WHERE time>=$from and time <=$till AND mid=$mid ";
  		
  		}
	$records=array();
  		
  		$replied=0;
  		$not_replied=0;
  		$total=0;
  		
  			$queryStats = $mysqli->query($sql);
		if ($queryStats->num_rows > 0) { 
      		while($valueStat= $queryStats->fetch_object()){
      		    
      		    
      		    if(!isset($records[$valueStat->mid]))
      		      $records[$valueStat->mid]=array();
      		      
      		       $records[$valueStat->mid]['username']=$valueStat->username;
      		    
        if(!isset( $records[$valueStat->mid]['not_replied']))
                 $records[$valueStat->mid]['not_replied']=0;
                 
           if(!isset( $records[$valueStat->mid]['replied']))
                 $records[$valueStat->mid]['replied']=0;
                 
                $records[$valueStat->mid]['total']=     $records[$valueStat->mid]['total']+1;
              
        	 if($valueStat->rc==0)
        	  $records[$valueStat->mid]['not_replied']=     $records[$valueStat->mid]['not_replied']+1;
        	else if($valueStat->rc>0)
        	     $records[$valueStat->mid]['replied']=     $records[$valueStat->mid]['replied']+1;
        		
      		}
      		
      		
		}

    

  		$jsonoutput1=array();
  		$temp2=array();
  		
  		if(!empty( $records)){
  		    $i=1;
  		foreach($records as $key=>$value){
  		    
  		 
  		  		$temp=array();
        $temp['replied']=$value['replied'];
        $temp['not_replied']=$value['not_replied'];
        $temp['total']=$value['total'];
        $temp['chatmod']=$key.'('.$value['username'].')';
        
        if($value['total'] >0)
        $temp['replied_percent']= round(( ($value['replied']/=$value['total'])*100 ),2). '%';
        else
        $temp['replied_percent']='Not Available';
        
        
        
        $temp2[$i]=$temp;
        $i++;
  		}
        
  		}
$jsonoutput1['customers']=$temp2;
$arr['data']= $jsonoutput1;
    echo json_encode($arr); 
      
      break;

  case 'search_moderators_history':


    $arr = array('status' => false, 'data' => '');
    
	$timestamp = time();
  	$dw = date( "w", $timestamp);
  	$date = isset($_POST['date']) ? $_POST['date'] : date('m/d/Y');
  	$date = explode(' to ', $date);
  	$date = array_map(function($el) {
    	$el = trim($el);
    	$el = explode('/', $el);
    	$el = array($el[2], $el[0], $el[1]);
    	$el = implode('-', $el);
    	return $el;
  	}, $date);
  	
  	
  	if($sm['user']['moderator'] == 'Admin Moderator') {
  	    
      	 $postedModDetails = getArray('users',"where id = '".intval($_POST['mid'])."'",'id desc','limit 1'); 
         if( ($postedModDetails[0]['mod_group_id'] !=$sm['user']['mod_group_id']) || $sm['user']['mod_group_id'] =='' ){
                echo json_encode($arr); 
                 break;
         }
         
  	}
      

		$queryOnline = $mysqli->query("SELECT id FROM users WHERE id!=1  AND moderator!='Admin Moderator'  AND admin IN(1,2) AND CAST(last_access AS SIGNED) > UNIX_TIMESTAMP(NOW()) - 12");
		$mIDOnlines = array();
		if ($queryOnline->num_rows > 0) { 
  		while($valueModOnline = $queryOnline->fetch_object()){
    		$mIDOnlines[] = $valueModOnline->id;
  		}
		}

  
		if (count($date) == 2) {
  		$from = strtotime($date[0] . ' 00:00:00');
  		$till = strtotime($date[1] . ' 23:59:59');
 


 //Check moderator chat history...

      $sql2 = "select distinct s_id,r_id  from chat_moderate_history_data  WHERE  (CAST(time AS SIGNED) >= $from AND CAST(time AS SIGNED) <= $till) AND mid=".$_POST['mid'];
 
  		$query2 = $mysqli->query($sql2);

       $chatHistoryRecords  = $query2->fetch_all(MYSQLI_ASSOC);
        $chat_history_sender_rec=array();
		foreach($chatHistoryRecords as $hkey=>$hvalue){
				 $chat_history_sender_rec[]=array('s_id'=>$hvalue['s_id'],'r_id'=>$hvalue['r_id']);

		}





		} else {
  		$date = $date[0];

		if(empty($date))
			$date=date('Y/m/d');

		$from = strtotime($date . ' 00:00:00');
  		$till = strtotime($date. ' 23:59:59');



	 //Check moderator chat history...

      $sql2 = "select distinct s_id,r_id from chat_moderate_history_data  WHERE (CAST(time AS SIGNED) >= $from AND 
   CAST(time AS SIGNED) <= $till) AND mid=".$_POST['mid'];
  		$query2 = $mysqli->query($sql2);

       $chatHistoryRecords  = $query2->fetch_all(MYSQLI_ASSOC);
         $chat_history_sender_rec=array();
		foreach($chatHistoryRecords as $hkey=>$hvalue){
				 $chat_history_sender_rec[]=array('s_id'=>$hvalue['s_id'],'r_id'=>$hvalue['r_id']);

		}


		
 
		} //end if 
		



	

            foreach ($chat_history_sender_rec as $chs) {
	  $sid = $chs['s_id'];
  	  $rid = $chs['r_id'];

	$sql="SELECT chat.*, chat_id,mid,(select name from users where users.id=mid) as moderator,
	(select name from users where users.id=chat.s_id) as s_name,(select name from users where users.id=chat.r_id) as r_name
	
	FROM chat 
			LEFT JOIN chat_moderate_history_data
				ON chat.id = chat_moderate_history_data.chat_id
			WHERE ((chat.s_id =$sid and chat.r_id=$rid) OR (chat.r_id =$sid and chat.s_id=$rid) ) AND (CAST(chat.time AS SIGNED) >= $from AND CAST(chat.time AS SIGNED) <= $till)  ORDER BY chat.id ASC";
//echo '<br>';
		$spotlight = $mysqli->query($sql);


	if ($spotlight->num_rows > 0) { 

		while($spotl = $spotlight->fetch_object()){
		    
		    	$hiderow=0;
		    	
	if($sm['user']['moderator'] == 'Admin Moderator') {
		  if (!in_array(intval($spotl->mid), $mIDs))
		    	$hiderow=1;
		    	
	}

		    	
			$m = $spotl->message;
			$m = clearMessageBR($m);
     
	         $temp=array(
				 'cid'=>$spotl->id,
				  'hiderow'=>$hiderow,
				 'fake'=>$spotl->fake,
				  'time'=>$spotl->time,
				 'chat_id'=>$spotl->chat_id,
				's_id'=>$spotl->s_id,
				's_name'=>$spotl->s_name,
				'r_id'=>$spotl->r_id,
				'r_name'=>$spotl->r_name,
				'mid'=>$spotl->mid,
				'modname'=>$spotl->moderator
				 );

		
	
			$message = $spotl->message;
	

            $content = $m;

			$doMask=true;

            if($spotl->photo == 1){
					$doMask=false;
                $content = '
	            <a href="'.$m.'" class="avatar avatar-xxl avatar-4by3 mt-2 s-lightbox" data-s-lightbox-group="gallery" data-s-lightbox-caption="">
	                <img src="'.$m.'" alt="image" class="avatar-img rounded" style="width:150px">
	            </a>';
            }

            if($spotl->gift >= 1){
					$doMask=false;
                $content = '<img src="'.$m.'" alt="image" class="avatar-img rounded" style="width:150px">';
            }                

            if($spotl->gif == 1){
					$doMask=false;
                $content = '<img src="'.$m.'" alt="image" class="avatar-img rounded" style="width:150px">';
            }
          
            if($spotl->story > 0){
					$doMask=false;
            	$story = getDataArray('users_story','id = '.$spotl->story);
                if($story['storyType'] == 'video'){
                    $content = '<div class="message__pic_"  style="cursor:pointer;width:150px>
						<video data-chat-video-story="'.$story['id'].'" src="'.$story['story'].'" type="video/mp4" muted preload style="position:absolute;top:0;left:0;width:100%;height:100%"></video>
                    </div>
                    <span style="opacity:.6;font-size:11px;margin-bottom:10px">
                    	'.$sm['lang'][663]['text'].'</span><br>
                    '.$m;
                } else {
                    $content = '<img src="'.$story['story'].'" class="avatar-img rounded" style="width:150px"><br>
                    <span style="opacity:.6;font-size:11px;margin-bottom:10px">
                    	'.$sm['lang'][663]['text'].'</span><br>
                    '.$m;
                }
            }
			

			//	if($sm['user']['admin']==2){ //if user is moderator..
		if($sm['user']['moderator'] != '') {
	         if($doMask == true){
             $m= maskForChatModerator($m);
			$content =maskForChatModerator($content);
			 }

			}





				 $temp['message']=$content;
			$chatModHistory[]=$temp;
		}	
	}


	  }


	
		$chatModHistoryHTML=array();

	//	file_put_contents("debugtest.txt",print_r($chatModHistory,true));


	//	var_dump($chatModHistory);
           
		foreach($chatModHistory as $key=>$value){
 
           if($value['mid']== $_POST['mid'] && $value['fake'] ==0  ){

            //get previous chat in conversation.......

 
            if(isset($chatModHistory[$key-1])){
              $tempRecord=$chatModHistory[$key-1];

 			  if($value['s_id']==$tempRecord['r_id'] && $value['r_id']==$tempRecord['s_id'] && $tempRecord['fake']==1  &&  $tempRecord['chat_id'] ==null)
				{
                  $chatModHistory[$key-1]['mod_message']=$chatModHistory[$key]['message'];
                  $chatModHistory[$key-1]['mod_name']=$chatModHistory[$key]['modname'];
					 $chatModHistory[$key-1]['mod_mid']=$chatModHistory[$key]['mid'];
					  $chatModHistory[$key-1]['mod_reply_time']=$chatModHistory[$key]['time'];
				$chatModHistory[$key-1]['mod_reply_chat_id']=$chatModHistory[$key]['chat_id'];
	  $chatModHistory[$key-1]['hiderow']=$chatModHistory[$key]['hiderow'];
					array_push($chatModHistoryHTML, $chatModHistory[$key-1]);
				}else   if($value['s_id']==$tempRecord['s_id'] && $value['r_id']==$tempRecord['r_id'] && $tempRecord['fake']==0 ){
				    
				     $chatModHistory[$key-1]['mod_message']=$chatModHistory[$key]['message'];
                  $chatModHistory[$key-1]['mod_name']=$chatModHistory[$key]['modname'];
					 $chatModHistory[$key-1]['mod_mid']=$chatModHistory[$key]['mid'];
					  $chatModHistory[$key-1]['mod_reply_time']=$chatModHistory[$key]['time'];
				$chatModHistory[$key-1]['mod_reply_chat_id']=$chatModHistory[$key]['chat_id'];
					  $chatModHistory[$key-1]['hiderow']=$chatModHistory[$key]['hiderow'];
                $chatModHistory[$key-1]['message']='(React)'.$chatModHistory[$key-1]['message'];
                 // $chatModHistory[$key-1]['mod_message']='';
                     $tempR=$chatModHistory[$key-1]['r_name'];
                   $tempS=$chatModHistory[$key-1]['s_name'];
                   
                      $chatModHistory[$key-1]['s_name']=$tempR;
                      
                          $chatModHistory[$key-1]['r_name']=$tempS;
					array_push($chatModHistoryHTML, $chatModHistory[$key-1]);  
				    
				}
				
				
				
				
			}
            
		   }
		   
		   
		   
		   

		}



        $jsonoutput=array();
    $i=1;
	$temp=array();
     foreach($chatModHistoryHTML as $key=>$value){ 
    //    $arr['data'] = $arr['data'] . '<tr dataid="' . $sm['user']['id'] . ' a' . $sm['user']['admin'] . '"><td>'. date('d F Y, H:i:s ',$value['mod_reply_time']) .'</td><td>'. $value['mod_name'] .'</td><td>'. $value['s_name']  .'</td><td>'. $value['r_name']  .'</td><td>'. $value['message']  .'</td><td>'. $value['mod_message']  .'</td></tr>';

	 if(in_array( $value['mod_mid'],$mIDOnlines))
           $value['mod_name']=$value['mod_name'].'<font color="green">(Online)</font>';
		 else
		   $value['mod_name']=$value['mod_name'].'<font color="red">(Offline)</font>';

   if( $value['hiderow']==1){
		        $value['message']='*****';
		         $value['mod_message']='*****';
		   }

       
        $temp['chat'.$i]=array('chattime'=>date('d F Y, H:i:s ',$value['mod_reply_time']),'hiderow'=>$value['hiderow'] ,'chatmod'=>$value['mod_name'] ,'realuser'=>$value['s_name'],'fakeuser'=> $value['r_name'],'incoming'=>$value['message'],'outgoing'=>$value['mod_message'] );
	//$arr['data']['customers'][]=$temp;
//	array_push($jsonoutput,  $temp);
	
		$i++;
      }


$jsonoutput1=array();
$jsonoutput1['customers']=$temp;

$arr['data']= $jsonoutput1;
    echo json_encode($arr); 
  break;






  case 'search_moderators_history_all':

    $arr = array('status' => false, 'data' => '');
    
		$date = date('Y/m/d');
		//$date='2020/07/31';

		$from = strtotime($date . ' 00:00:00');
  		$till = strtotime($date. ' 23:59:59');
 //AND (CAST(ch.time AS SIGNED) >= $from AND CAST(ch.time AS SIGNED) <= $till)
	 

	$queryOnline = $mysqli->query("SELECT id FROM users WHERE id!=1  AND moderator!='Admin Moderator'  AND admin IN(1,2) AND CAST(last_access AS SIGNED) > UNIX_TIMESTAMP(NOW()) - 12");
		$mIDOnlines = array();
		if ($queryOnline->num_rows > 0) { 
  		while($valueModOnline = $queryOnline->fetch_object()){
    		$mIDOnlines[] = $valueModOnline->id;
  		}
		}
		
		$extraModCondition='';

  	if($sm['user']['moderator'] == 'Admin Moderator') {
  	    
     	$extraModCondition='  AND mod_group_id='.$sm['user']['mod_group_id'];
         
  	}
	 
  	// All chat MOD ids.....
  	$allmodsql="SELECT id,name FROM users WHERE id!=1 AND moderator!='Admin Moderator' $extraModCondition  AND admin IN(1,2)  ";
  $queryMod = $mysqli->query($allmodsql);
		$mIDs = array();
		if ($queryMod->num_rows > 0) { 
  		while($valueMod = $queryMod->fetch_object()){
    		$mIDs[$valueMod->id] = $valueMod->id;
  		}
		}
		
	
		//die;
	 //Check moderator chat history for each moderator...
		$ichat=1;
	$tempHistory=array();
	 foreach($mIDs as $mkey=>$moderatorID){

		$allChatRecords=$return['chs'];

      $sql2 = "select DISTINCT s_id,r_id  from chat_moderate_history_data  WHERE  (CAST(time AS SIGNED) >= $from AND CAST(time AS SIGNED) <= $till)  AND mid=".$moderatorID.'  ORDER BY chat_id  DESC' ;
	
  		$query2 = $mysqli->query($sql2);

       $chatHistoryRecords  = $query2->fetch_all(MYSQLI_ASSOC);


		foreach($chatHistoryRecords as $hkey=>$hvalue){
			 $chat_history_sender_rec[$hvalue['s_id']+$hvalue['r_id']]=array('s_id'=>$hvalue['s_id'],'r_id'=>$hvalue['r_id']);
		}	

	 } //all  moderators for loop
	
	//var_dump($chat_history_sender_rec);
				$chatModHistoryAll=array();

            foreach ($chat_history_sender_rec as $chs) {
		$chatModHistory=array();
  	  $sid = $chs['s_id'];
  	  $rid = $chs['r_id'];

	 $sql="SELECT chat.time as chattime, chat.*, chat_id,mid,(select name from users where users.id=mid) as moderator,
	(select name from users where users.id=chat.s_id) as s_name,(select name from users where users.id=chat.r_id) as r_name
	
	FROM chat 
			LEFT JOIN chat_moderate_history_data
				ON chat.id = chat_moderate_history_data.chat_id
		WHERE    ((chat.s_id =$sid and chat.r_id=$rid) OR (chat.r_id =$sid and chat.s_id=$rid) ) AND (CAST(chat.time AS SIGNED) >= $from AND CAST(chat.time AS SIGNED) <= $till)  ORDER BY chat.id ASC";
		//print_r($mIDs);
//die;
		$spotlight = $mysqli->query($sql);


	if ($spotlight->num_rows > 0) { 

		while($spotl = $spotlight->fetch_object()){
		    	$hiderow=0;
		
		   // var_dump($spotl->mid);
	if($sm['user']['moderator'] == 'Admin Moderator') {
		  if (!in_array(intval($spotl->mid), $mIDs))
		    	$hiderow=1;
		    	
	}

			$m = $spotl->message;
			$m = clearMessageBR($m);
     
	         $temp=array(
				 'cid'=>$spotl->id,
				 'fake'=>$spotl->fake,
				 	 'hiderow'=>$hiderow,
				  'time'=>$spotl->time,
				 'chat_id'=>$spotl->chat_id,
				's_id'=>$spotl->s_id,
				's_name'=>$spotl->s_name,
				'r_id'=>$spotl->r_id,
				'r_name'=>$spotl->r_name,
				'mid'=>$spotl->mid,
				'modname'=>$spotl->moderator,
				 'chattime'=>$spotl->chattime
				 );

		
	
			$message = $spotl->message;
	
        
            $content = $m;

            if($spotl->photo == 1){
                $content = '
	            <a href="'.$m.'" class="avatar avatar-xxl avatar-4by3 mt-2 s-lightbox" data-s-lightbox-group="gallery" data-s-lightbox-caption="">
	                <img src="'.$m.'" alt="image" class="avatar-img rounded" style="width:150px">
	            </a>';
            }

            if($spotl->gift >= 1){
                $content = '<img src="'.$m.'" alt="image" class="avatar-img rounded" style="width:150px">';
            }                

            if($spotl->gif == 1){
                $content = '<img src="'.$m.'" alt="image" class="avatar-img rounded" style="width:150px">';
            }
          
			//	if($sm['user']['admin']==2){ //if user is moderator..
			
				if($sm['user']['moderator'] != '') {
	
             $m= maskForChatModerator($m);
			$content =maskForChatModerator($content);

			}

            if($spotl->story > 0){
            	$story = getDataArray('users_story','id = '.$spotl->story);
                if($story['storyType'] == 'video'){
                    $content = '<div class="message__pic_"  style="cursor:pointer;width:150px>
						<video data-chat-video-story="'.$story['id'].'" src="'.$story['story'].'" type="video/mp4" muted preload style="position:absolute;top:0;left:0;width:100%;height:100%"></video>
                    </div>
                    <span style="opacity:.6;font-size:11px;margin-bottom:10px">
                    	'.$sm['lang'][663]['text'].'</span><br>
                    '.$m;
                } else {
                    $content = '<img src="'.$story['story'].'" class="avatar-img rounded" style="width:150px"><br>
                    <span style="opacity:.6;font-size:11px;margin-bottom:10px">
                    	'.$sm['lang'][663]['text'].'</span><br>
                    '.$m;
                }
            }    
				 $temp['message']=$content;
			$chatModHistory[]=$temp;
		}	
	}

			$chatModHistoryAll[]=$chatModHistory;
	  }
	
		$chatModHistoryHTML=array();
		
		
	//	var_dump($chatModHistoryAll);
		
		
    $chatModHistory=null;

           		foreach($chatModHistoryAll as $key_a=>$chatModHistory){
           		    


		foreach($chatModHistory as $key=>$value){
 
          if( $value['fake'] ==0  ){

 
            if(isset($chatModHistory[$key-1])){
              $tempRecord=$chatModHistory[$key-1];

 			  if($value['s_id']==$tempRecord['r_id'] && $value['r_id']==$tempRecord['s_id'] && $tempRecord['fake']==1  &&  $tempRecord['chat_id'] ==null)
				{
                  $chatModHistory[$key-1]['mod_message']=$chatModHistory[$key]['message'];
                  $chatModHistory[$key-1]['mod_name']=$chatModHistory[$key]['modname'];
					 $chatModHistory[$key-1]['mod_mid']=$chatModHistory[$key]['mid'];
					  $chatModHistory[$key-1]['mod_reply_time']=$chatModHistory[$key]['time'];
				$chatModHistory[$key-1]['mod_reply_chat_id']=$chatModHistory[$key]['chat_id'];
			$chatModHistory[$key-1]['chattime']=$chatModHistory[$key]['chattime'];

             $chatModHistory[$key-1]['hiderow']=$chatModHistory[$key]['hiderow'];

					array_push($chatModHistoryHTML, $chatModHistory[$key-1]);
				}else   if($value['s_id']==$tempRecord['s_id'] && $value['r_id']==$tempRecord['r_id'] && $tempRecord['fake']==0 ){
				    
				     $chatModHistory[$key-1]['mod_message']=$chatModHistory[$key]['message'];
                  $chatModHistory[$key-1]['mod_name']=$chatModHistory[$key]['modname'];
					 $chatModHistory[$key-1]['mod_mid']=$chatModHistory[$key]['mid'];
					  $chatModHistory[$key-1]['mod_reply_time']=$chatModHistory[$key]['time'];
				$chatModHistory[$key-1]['mod_reply_chat_id']=$chatModHistory[$key]['chat_id'];
				
				$chatModHistory[$key-1]['hiderow']=$chatModHistory[$key]['hiderow'];

                $chatModHistory[$key-1]['message']='(React)'.$chatModHistory[$key-1]['message'];
                 // $chatModHistory[$key-1]['mod_message']='';
                  $tempR=$chatModHistory[$key-1]['r_name'];
                   $tempS=$chatModHistory[$key-1]['s_name'];
                   
                      $chatModHistory[$key-1]['s_name']=$tempR;
                      
                          $chatModHistory[$key-1]['r_name']=$tempS;
                          
					array_push($chatModHistoryHTML, $chatModHistory[$key-1]);  
				    
				}
			}
            
		   }

		}

				}

    
     foreach($chatModHistoryHTML as $key=>$value){ 


		 if(in_array( $value['mod_mid'],$mIDOnlines))
           $value['mod_name']=$value['mod_name'].'<font color="green">(Online)</font>';
		 else
		   $value['mod_name']=$value['mod_name'].'<font color="red">(Offline)</font>';
		   
		   if( $value['hiderow']==1){
		        $value['message']='*****';
		         $value['mod_message']='*****';
		   }
        
        $tempHistory['chat'.$ichat]=array('chattime'=>date('d F Y, H:i:s ',$value['mod_reply_time']),'hiderow'=>$value['hiderow'] ,'chatmod'=>$value['mod_name'] ,'realuser'=>$value['s_name'],'fakeuser'=> $value['r_name'],'incoming'=>$value['message'],'outgoing'=>$value['mod_message'],'chattime1'=> $value['chattime']);	
		$ichat++;
      }







//echo '<pre>';
//print_r($tempHistory);



$jsonoutput1=array();

$chattime1 = array_column($tempHistory, 'chattime1');

array_multisort($chattime1, SORT_DESC, $tempHistory);


$jsonoutput1['customers']=$tempHistory;

$arr['data']= $jsonoutput1;

//file_put_contents("allchat.txt",json_encode($arr));die;
    echo json_encode($arr); 
	die;
  break;


 
  case 'search_moderators_stats_sales':
      
        $arr = array('status' => false, 'data' => '');
   $mid=intval($_POST['mid']);
    
	$timestamp = time();
  	$dw = date( "w", $timestamp);
  	$date = isset($_POST['date']) ? $_POST['date'] : date('m/d/Y');
  	$date = explode(' to ', $date);
  	$date = array_map(function($el) {
    	$el = trim($el);
    	$el = explode('/', $el);
    	$el = array($el[2], $el[0], $el[1]);
    	$el = implode('-', $el);
    	return $el;
  	}, $date);
  	
  	
  		if (count($date) == 2) {
  		$from = strtotime($date[0] . ' 00:00:00');
  		$till = strtotime($date[1] . ' 23:59:59');
  		}
  		else{
  		 	$date = $date[0];

		if(empty($date))
			$date=date('Y/m/d');

		$from = strtotime($date . ' 00:00:00');
  		$till = strtotime($date. ' 23:59:59');   
  		    
  		}
   
        if ($sm['user']['admin'] == 1 ) {
            
  $sqlCheck="select mid,count(mid) as cSales,(select username from users where id =mid) as username  FROM chat_moderate_history_data where time IN( SELECT max(time) FROM `chat_moderate_history_data` WHERE r_id IN (SELECT u_id from sales where time IN (SELECT min(time) FROM `sales`  GROUP BY u_id )) GROUP BY r_id ) AND  time>=$from and time <=$till  GROUP BY mid";
   
  	
  		$recordsSales=array();
  		
  			$allStats = $mysqli->query($sqlCheck);
    	 
        if ($allStats->num_rows > 0) { 
          while($stat = $allStats->fetch_object()) {
              $arr['data'] = $arr['data'] . '<tr dataid="' . $sm['user']['id'] . ' a' . $sm['user']['admin'] . '"><td style="vertical-align:baseline !important;">'. $stat->mid .'</td><td style="vertical-align:baseline !important;">'. $stat->username .'</td><td style="vertical-align:baseline !important;">'. $stat->cSales .'</td></tr>';
          }
        }
 
		}

 
    echo json_encode($arr); 
  break;
  
 
  case 'search_moderators_stats':
    $arr = array('status' => false, 'data' => '');
    $date = secureEncode($_POST['date']);
    
  //var_dump($sm['user']['admin'] );die;
    
    $date = $date == null || $date == false || $date === '' ? date('m/d/Y', time()) : $date;
    $arr = array('status' => false, 'data' => '', 'req' => $date);
    $date2 = explode(' to ', $date);
    if (count($date2) == 2) {
      if ($date2[0] === $date2[1]) {
        $timestamp = DateTime::createFromFormat('m/d/Y', $date2[0])->getTimestamp();
        $timestamp = date('Y-m-d', $timestamp);
       
        if ($sm['user']['admin'] == 1 ){
         $arr['sql'] = "SELECT cs.mid AS id, users.name AS name, COUNT(*) AS num FROM chat_stats cs INNER JOIN users ON users.id = cs.mid WHERE DATE(cs.time) = '$timestamp' GROUP BY cs.mid ORDER BY num DESC";
      
          $allStatsSQLTime="
            SELECT 
    employee_id,
    SUM(CASE WHEN answered = 1 AND open = 0 THEN TIMESTAMPDIFF(MINUTE, created_at, updated_at) ELSE 0 END) / SUM(answered = 1 AND open = 0) AS answered_avg_time,
    SUM(CASE WHEN answered = 0 AND open = 0 THEN TIMESTAMPDIFF(MINUTE, created_at, updated_at) ELSE 0 END) / SUM(answered = 0 AND open = 0) AS not_answered_avg_time
     ,SUM(answered = 0 AND open = 0) AS total_count_not_answered_avg_time,
	 SUM(answered = 1 AND open = 0) AS total_count_answered_avg_time
FROM 
    fake_conversations
     WHERE 
            date(created_at)=  '$timestamp'
GROUP BY 
    employee_id ";
      
        $statsAutoLogout=" SELECT count(uid) as logcount,uid FROM `auto_logout_log` WHERE auto_log=1 AND  date(created_at)=  '$timestamp' GROUP BY 
    uid";
      
        } 
       else if($sm['moderator']['ChatFakeAdmin'] == 'Yes' ) 
        {
         $arr['sql'] = "SELECT cs.mid AS id, users.name AS name, COUNT(*) AS num FROM chat_stats cs INNER JOIN users ON users.id = cs.mid AND users.mod_group_id=".$sm['user']['mod_group_id']." WHERE DATE(cs.time) = '$timestamp' GROUP BY cs.mid ORDER BY num DESC";
        } 
        else {
          $arr['sql'] = "SELECT cs.mid AS id, users.name AS name, COUNT(*) AS num FROM chat_stats cs INNER JOIN users ON users.id = cs.mid WHERE DATE(cs.time) = '$timestamp' AND cs.mid='" . $sm['user']['id'] . "' GROUP BY cs.mid ORDER BY num DESC";
        }
        
        $allStats = $mysqli->query($arr['sql']);  
      } else {
        $timestamp = DateTime::createFromFormat('m/d/Y', $date2[0])->getTimestamp();
        $timestamp = date('Y-m-d', $timestamp);
        $timestamp2 = DateTime::createFromFormat('m/d/Y', $date2[1])->getTimestamp();
        $timestamp2 = date('Y-m-d', $timestamp2);
        if ($sm['user']['admin'] == 1 ) {
          $arr['sql'] = "SELECT cs.mid AS id, users.name AS name, COUNT(*) AS num FROM chat_stats cs INNER JOIN users ON users.id = cs.mid WHERE DATE(cs.time) >= '$timestamp' AND DATE(cs.time) <= '$timestamp2' GROUP BY cs.mid ORDER BY num DESC";
            $allStatsSQLTime="
            SELECT 
    employee_id,
    SUM(CASE WHEN answered = 1 AND open = 0 THEN TIMESTAMPDIFF(MINUTE, created_at, updated_at) ELSE 0 END) / SUM(answered = 1 AND open = 0) AS answered_avg_time,
    SUM(CASE WHEN answered = 0 AND open = 0 THEN TIMESTAMPDIFF(MINUTE, created_at, updated_at) ELSE 0 END) / SUM(answered = 0 AND open = 0) AS not_answered_avg_time
     ,SUM(answered = 0 AND open = 0) AS total_count_not_answered_avg_time,
	 SUM(answered = 1 AND open = 0) AS total_count_answered_avg_time
FROM 
    fake_conversations
     WHERE 
             date(created_at) >= '$timestamp' AND  date(created_at) <= '$timestamp2'
GROUP BY 
    employee_id ";
           $statsAutoLogout=" SELECT count(uid) as logcount,uid FROM `auto_logout_log` WHERE auto_log=1 AND   date(created_at) >= '$timestamp' AND  date(created_at) <= '$timestamp2' GROUP BY 
    uid";
        }
         else if($sm['moderator']['ChatFakeAdmin'] == 'Yes' ) 
        {
          $arr['sql'] = "SELECT cs.mid AS id, users.name AS name, COUNT(*) AS num FROM chat_stats cs INNER JOIN users ON users.id = cs.mid AND users.mod_group_id=".$sm['user']['mod_group_id']." WHERE DATE(cs.time) >= '$timestamp' AND DATE(cs.time) <= '$timestamp2' GROUP BY cs.mid ORDER BY num DESC";
        }
        else {
          $arr['sql'] = "SELECT cs.mid AS id, users.name AS name, COUNT(*) AS num FROM chat_stats cs INNER JOIN users ON users.id = cs.mid WHERE DATE(cs.time) >= '$timestamp' AND DATE(cs.time) <= '$timestamp2' AND cs.mid='" . $sm['user']['id'] . "' GROUP BY cs.mid ORDER BY num DESC";
        }
        $allStats = $mysqli->query($arr['sql']);
      }
    } else {
      $timestamp = DateTime::createFromFormat('m/d/Y', $date)->getTimestamp();
      $timestamp = date('Y-m-d', $timestamp);
      if ($sm['user']['admin'] == 1 ) {
        $arr['sql'] = "SELECT cs.mid AS id, users.name AS name, COUNT(*) AS num FROM chat_stats cs INNER JOIN users ON users.id = cs.mid WHERE DATE(cs.time) = '$timestamp' GROUP BY cs.mid ORDER BY num DESC";
     
               $allStatsSQLTime="
            SELECT 
    employee_id,
    SUM(CASE WHEN answered = 1 AND open = 0 THEN TIMESTAMPDIFF(MINUTE, created_at, updated_at) ELSE 0 END) / SUM(answered = 1 AND open = 0) AS answered_avg_time,
    SUM(CASE WHEN answered = 0 AND open = 0 THEN TIMESTAMPDIFF(MINUTE, created_at, updated_at) ELSE 0 END) / SUM(answered = 0 AND open = 0) AS not_answered_avg_time
     ,SUM(answered = 0 AND open = 0) AS total_count_not_answered_avg_time,
	 SUM(answered = 1 AND open = 0) AS total_count_answered_avg_time
FROM 
    fake_conversations
     WHERE 
             date(created_at) = '$timestamp'
GROUP BY 
    employee_id ";
     
         $statsAutoLogout=" SELECT count(uid) as logcount,uid FROM `auto_logout_log` WHERE auto_log=1 AND  date(created_at)=  '$timestamp' GROUP BY 
    uid";  
     
      } 
       else if($sm['moderator']['ChatFakeAdmin'] == 'Yes' ) 
        {
        $arr['sql'] = "SELECT cs.mid AS id, users.name AS name, COUNT(*) AS num FROM chat_stats cs INNER JOIN users ON users.id = cs.mid  AND users.mod_group_id=".$sm['user']['mod_group_id']." WHERE DATE(cs.time) = '$timestamp' GROUP BY cs.mid ORDER BY num DESC";

        }
      else {
        $arr['sql'] = "SELECT cs.mid AS id, users.name AS name, COUNT(*) AS num FROM chat_stats cs INNER JOIN users ON users.id = cs.mid WHERE DATE(cs.time) = '$timestamp' AND cs.mid='" . $sm['user']['id'] . "' GROUP BY cs.mid ORDER BY num DESC";
      }
      
      $allStats = $mysqli->query($arr['sql']);
    }
    $statTimeData=array();
    $autoLogData=array();
    if ($allStats->num_rows > 0) { 
        
     if ($sm['user']['admin'] == 1 ){    
    $allStatsTime = $mysqli->query($allStatsSQLTime);
         if ($allStatsTime->num_rows > 0) { 
                      while($statTime = $allStatsTime->fetch_object()) { 
                          
                          if($statTime->answered_avg_time>0 && $statTime->answered_avg_time<1)
                            $statTime->answered_avg_time= round($statTime->answered_avg_time*60,2) .' Sec';
                            else
                             $statTime->answered_avg_time=round($statTime->answered_avg_time,2) .' Min';
                            
                               if($statTime->not_answered_avg_time>0 && $statTime->not_answered_avg_time<1)
                            $statTime->not_answered_avg_time=round($statTime->not_answered_avg_time*60,2) .' Sec';
                             else
                             $statTime->not_answered_avg_time=round($statTime->not_answered_avg_time,2) .' Min';
                          
                          
                        $statTimeData[$statTime->employee_id]=array('answered_avg_time'=>$statTime->answered_avg_time,
                        'not_answered_avg_time'=>$statTime->not_answered_avg_time,
                        'total_count_answered_avg_time'=>$statTime->total_count_answered_avg_time,
                        'total_count_not_answered_avg_time'=>$statTime->total_count_not_answered_avg_time
                        );
                      }
         }
         
         
      $allAutoLogCount = $mysqli->query($statsAutoLogout);
         if ($allAutoLogCount->num_rows > 0) { 
                      while($autLog = $allAutoLogCount->fetch_object()) { 
                        $autoLogData[$autLog->uid]=$autLog->logcount;
                      }
         }
            
         
         
     } //if admin
        
      while($stat = $allStats->fetch_object()) {
       
         if ($sm['user']['admin'] == 1 )
       $arr['data'] = $arr['data'] . '<tr dataid="' . $sm['user']['id'] . ' a' . $sm['user']['admin'] . '"><td>'. $stat->id .'</td><td>'. $stat->name .'</td><td>'. $stat->num  .'</td><td>'. $statTimeData[$stat->id]['answered_avg_time'].'('.$statTimeData[$stat->id]['total_count_answered_avg_time'].')' .'</td>' .'<td>'. $statTimeData[$stat->id]['not_answered_avg_time'].'('.$statTimeData[$stat->id]['total_count_not_answered_avg_time'].')' .'</td><td>'. $autoLogData[$stat->id] .'</td></tr>';
        else
        $arr['data'] = $arr['data'] . '<tr dataid="' . $sm['user']['id'] . ' a' . $sm['user']['admin'] . '"><td>'. $stat->id .'</td><td>'. $stat->name .'</td><td>'. $stat->num .'</td></tr>';
      }
    }
    // $arr['sql']='';
   // echo $allStatsSQLTime;die;
     $arr['sqlavg']=$allStatsSQLTime;
    echo json_encode($arr); 
  break;
  
  case 'newsletterMsg':
    $arr = array('status' => false, 'total' => 0);
    $type = secureEncode($_POST['type']);
    $fid = secureEncode($_POST['fid']);
    $msg = $_POST['msg'];
    $sent_msg_count = $_POST['sent_msg_count'];
    $to   = $_POST['to'];
    
    $execute_by = strtotime($_POST['scheduleDate']);
     $is_bulk = secureEncode($_POST['is_bulk']);

    if($execute_by > 0){}else{$execute_by = strtotime("now");}
    $execute_by = date('Y-m-d H:i:s', $execute_by);
    if (in_array($type, array('news_letter_vid','vid','gif', 'pic', 'msg','msg_mx_st_msg','msg_mx_st_gif','msg_mx_st_pic','free_coins','adv_letter','react_letter_msg','react_letter_gif','react_letter_pic','news_letter_msg','news_letter_gif','news_letter_pic')) && is_array($to) && count($to) > 0) {
      $num = ceil( count($_POST['to']) / 30 );
    //  $num = 5;
      $idss = implode(',', $_POST['to']);
        $emptyVal = '';
        $freeCoinText='';
        $free_coins=0;
        
        if($type=='free_coins'){
            $sent_msg_count=explode("|",$sent_msg_count);
            $free_coins=$sent_msg_count[0];
            if(empty($free_coins))
                $free_coins=$newAccountFreeCreditValue;
            $freeCoinText=$sent_msg_count[1];
            if(empty( $freeCoinText))
                 $freeCoinText='Free Coins Credited!';
                 
              $sent_msg_count=0;   
        }
        
        
              
        if (strpos($type, 'news_letter') !== false) {
        
               $sql="INSERT INTO newslettercron_react (ids,type,num,msg,fid,newsletter_type) VALUES ('$emptyVal','$type','$num','$msg','$fid','normal')";
            
              $mysqli->query($sql);
        $newsletter_id = $mysqli->insert_id;
        if(!empty($_POST['to']) &&  $mysqli->affected_rows ==1 ){
            foreach($_POST['to'] as $eachTargetUser){
                $executionStatus = 0;
                
                $sql="INSERT INTO  newsletter_target_users_react (newsletter_id,user_id,fake_user_id,execution_status, execute_by,newsletter_type) VALUES 
                ('$newsletter_id','$eachTargetUser','$fid','$executionStatus','$execute_by','normal')";
            
           
                $mysqli->query($sql);
            }
			 $arr['total'] = 1;
			  $arr['status'] = true;
        }   
        } elseif (strpos($type, 'react_letter') !== false) {
        
               $sql="INSERT INTO newslettercron_react (ids,type,num,msg,fid) VALUES ('$emptyVal','$type','$num','$msg','$fid')";
            
              $mysqli->query($sql);
        $newsletter_id = $mysqli->insert_id;
        if(!empty($_POST['to'])  &&  $mysqli->affected_rows ==1){
            foreach($_POST['to'] as $eachTargetUser){
                $executionStatus = 0;
                $temp=explode("-",$eachTargetUser);
                $from=$temp[0];
                $to=$temp[1];
                 $hash_s_r= md5(trim($eachTargetUser));
               
                $mysqli->query("INSERT INTO newsletter_target_users_react (newsletter_id,user_id,fake_user_id,hash_s_r,execution_status, execute_by) VALUES 
                ('$newsletter_id','$from','$to','$hash_s_r','$executionStatus','$execute_by')");
            }
			 $arr['total'] = 1;
			  $arr['status'] = true;
        }   
        }else{
            
            if (!in_array($type, array('gif', 'pic','vid', 'msg')))
                $is_bulk='off';
            //
         $mysqli->query("INSERT INTO newslettercron (ids,type,num,msg,fid,sent_msg_count,is_bulk) VALUES ('$emptyVal','$type','$num','$msg','$fid','$sent_msg_count','$is_bulk')");
        $newsletter_id = $mysqli->insert_id;
        if(!empty($_POST['to']) &&  $mysqli->affected_rows ==1){
            foreach($_POST['to'] as $eachTargetUser){
                $executionStatus = 0;
                $mysqli->query("INSERT INTO newsletter_target_users (newsletter_id,user_id,execution_status, execute_by,free_coins,free_coins_text) VALUES 
                ('$newsletter_id','$eachTargetUser','$executionStatus','$execute_by','$free_coins','$freeCoinText')");
            }
			 $arr['total'] = 1;
			  $arr['status'] = true;
        }
            ///
        }
        
     
    }

    echo json_encode($arr);
  break;
  case 'sendNewsLetter':
    // set gender=2 for newsletter page if required
    $arr = array('status' => false, 'total' => 0);
    $type = secureEncode($_POST['type']);
    $to   = $_POST['to'];
    if (in_array($type, array('random', 'like', 'view', 'match', 'matchcron', 'likecron', 'viewcron', 'randomcron')) && is_array($to) && count($to) > 0) {
      ///$users = getArrayDSelected('age','users','where country ="'.$country.'"');


//Execute this query when its random,like,view,match as $obj is only used when this conditon matches

if (in_array($type, array('random', 'like', 'view', 'match')) && is_array($to) && count($to) > 0) {

      $users = getSelectedArray('id,name,email,age,s_gender','users',"WHERE id IN('". implode("', '", $to)."')", 'id DESC');
      $obj = array();
      foreach ($users as $user) {
        $obj[] = array(
          'to' => $user,
          'from' => getSelectedArray('id,name,email','users',"WHERE fake='1' AND gender='". $user['s_gender'] ."' AND age BETWEEN '".($user['age'] - 10)."' AND '".($user['age'] + 2)."'", 'RAND()', 'LIMIT 1')[0]
        );
      }

}
      //$arr['obj'] = $obj;
      $arr['status'] = true;
      
      switch($type) {
        case 'matchcron':
          $idss = implode(',', $_POST['to']);
          $num = ceil( count($_POST['to']) / 30 );
          $mysqli->query("INSERT INTO newslettercron (ids,type,num) VALUES ('$idss','match','$num')");	
            $newsletter_id = $mysqli->insert_id;
            $execute_by = strtotime($_POST['scheduleDate']);
            if($execute_by > 0){}else{$execute_by = strtotime("now");}
            $execute_by = date('Y-m-d H:i:s', $execute_by);
              
            if(!empty($_POST['to'])){
                foreach($_POST['to'] as $eachTargetUser){
                    $executionStatus = 0;
                    $mysqli->query("INSERT INTO newsletter_target_users (newsletter_id,user_id,execution_status, execute_by) VALUES ('$newsletter_id','$eachTargetUser','$executionStatus','$execute_by')");
                }
            }
              
          $arr['total'] = 1;
        break;
        case 'likecron':
          $idss = implode(',', $_POST['to']);
          $num = ceil( count($_POST['to']) / 30 );
          $mysqli->query("INSERT INTO newslettercron (ids,type,num) VALUES ('$idss','like','$num')");	
            $newsletter_id = $mysqli->insert_id;
            $execute_by = strtotime($_POST['scheduleDate']);
            if($execute_by > 0){}else{$execute_by = strtotime("now");}
            $execute_by = date('Y-m-d H:i:s', $execute_by);
              
            if(!empty($_POST['to'])){
                foreach($_POST['to'] as $eachTargetUser){
                    $executionStatus = 0;
                    $mysqli->query("INSERT INTO newsletter_target_users (newsletter_id,user_id,execution_status, execute_by) VALUES ('$newsletter_id','$eachTargetUser','$executionStatus','$execute_by')");
                }
            }
          $arr['total'] = 1;
        break;
        case 'viewcron':
          $idss = implode(',', $_POST['to']);
          $num = ceil( count($_POST['to']) / 30 );
          $mysqli->query("INSERT INTO newslettercron (ids,type,num) VALUES ('$idss','view','$num')");	
            $newsletter_id = $mysqli->insert_id;
            $execute_by = strtotime($_POST['scheduleDate']);
            if($execute_by > 0){}else{$execute_by = strtotime("now");}
            $execute_by = date('Y-m-d H:i:s', $execute_by);
              
            if(!empty($_POST['to'])){
                foreach($_POST['to'] as $eachTargetUser){
                    $executionStatus = 0;
                    $mysqli->query("INSERT INTO newsletter_target_users (newsletter_id,user_id,execution_status, execute_by) VALUES ('$newsletter_id','$eachTargetUser','$executionStatus','$execute_by')");
                }
            }
          $arr['total'] = 1;
        break;
        case 'randomcron':
          $idss = implode(',', $_POST['to']);
          $num = ceil( count($_POST['to']) / 30 );
          $mysqli->query("INSERT INTO newslettercron (ids,type,num) VALUES ('$idss','random','$num')");	
            $newsletter_id = $mysqli->insert_id;
            $execute_by = strtotime($_POST['scheduleDate']);
            if($execute_by > 0){}else{$execute_by = strtotime("now");}
            $execute_by = date('Y-m-d H:i:s', $execute_by);
              
            if(!empty($_POST['to'])){
                foreach($_POST['to'] as $eachTargetUser){
                    $executionStatus = 0;
                    $mysqli->query("INSERT INTO newsletter_target_users (newsletter_id,user_id,execution_status, execute_by) VALUES ('$newsletter_id','$eachTargetUser','$executionStatus','$execute_by')");
                }
            }
          $arr['total'] = 1;
        break;
        
      case 'random':
        
          $log = '';
          foreach ($obj as $o) {
            getUserInfo($o['from']['id']);
            $uid1 = $o['from']['id'];
      			$uid2 = $o['to']['id'];
      			$action = 1;		
      			$time = time() - rand(10, 3600);
      			switch (rand(1,3)) {
        			case 1:
        			  //match
        			  $mysqli->query("UPDATE users_likes SET love = '$action' where u1 = '$uid1' and u2 = '$uid2'");		
          			$sm['profile_notifications'] = userNotifications($uid2);
          			if($sm['profile_notifications']['fan']['email'] == 1){
          				if($sm['plugins']['email']['enabled'] == 'Yes'){
          					$log = matchMailNotification($uid2, true);
          					$arr['total'] = $arr['total'] + 1;
          				}
          			} 
        			break;
        			case 2:
        			  //like
        			  $mysqli->query("UPDATE users_likes SET love = '$action' where u1 = '$uid1' and u2 = '$uid2'");		
          			$mysqli->query("INSERT INTO users_likes (u1,u2,love,time) VALUES ('$uid1','$uid2','$action','$time')");
          			$sm['profile_notifications'] = userNotifications($uid2);
          			if($sm['profile_notifications']['fan']['email'] == 1){
          				if($sm['plugins']['email']['enabled'] == 'Yes'){
          					fanMailNotification($uid2);
          					$arr['total'] = $arr['total'] + 1;
          				}
          			} 
        			break;
        			case 3:
        			  //view
        			  if($sm['plugins']['email']['enabled'] == 'Yes'){
          			  visit($o['from']['id'], $o['to']['id'], profilePhoto($o['from']['id']) , $o['from']['name']);
                  viewMailNotification($uid2);
                  $arr['total'] = $arr['total'] + 1;
                }
        			break;
      			}
          }
        
        break;
        case 'match':
          //matchMailNotification($sexy_id)
          $log = '';
          foreach ($obj as $o) {
            getUserInfo($o['from']['id']);
            $uid1 = $o['from']['id'];
      			$uid2 = $o['to']['id'];
      			$action = 1;		
      			$time = time() - rand(10, 3600);
      			$mysqli->query("UPDATE users_likes SET love = '$action' where u1 = '$uid1' and u2 = '$uid2'");		
      			//$mysqli->query("INSERT INTO users_likes (u1,u2,love,time) VALUES ('$uid1','$uid2','$action','$time')");
      			$sm['profile_notifications'] = userNotifications($uid2);
      			if($sm['profile_notifications']['fan']['email'] == 1){
      				if($sm['plugins']['email']['enabled'] == 'Yes'){
      					$log = matchMailNotification($uid2, true);
      					//activity('system',implode('. ', $log),'match newsletter');	
      					$arr['total'] = $arr['total'] + 1;
      				}
      			}  
          }
        break;
        case 'like':
          foreach ($obj as $o) {
            getUserInfo($o['from']['id']);
            $uid1 = $o['from']['id'];
      			$uid2 = $o['to']['id'];
      			$action = 1;		
      			$time = time() - rand(10, 3600);
      			$mysqli->query("UPDATE users_likes SET love = '$action' where u1 = '$uid1' and u2 = '$uid2'");		
      			$mysqli->query("INSERT INTO users_likes (u1,u2,love,time) VALUES ('$uid1','$uid2','$action','$time')");
      			$sm['profile_notifications'] = userNotifications($uid2);
      			if($sm['profile_notifications']['fan']['email'] == 1){
      				if($sm['plugins']['email']['enabled'] == 'Yes'){
      					fanMailNotification($uid2);
      					$arr['total'] = $arr['total'] + 1;
      				}
      			}  
          }
        break;
        case 'view':
          foreach ($obj as $o) {
            getUserInfo($o['from']['id']);
            $uid1 = $o['from']['id'];
            $uid2 = $o['to']['id'];
            visit($o['from']['id'], $o['to']['id'], profilePhoto($o['from']['id']) , $o['from']['name']);
            viewMailNotification($uid2);
            $arr['total'] = $arr['total'] + 1;
          }
        break;
      }
      
      //$arr['users'] = $users;
    }
		echo json_encode($arr); 
  break;

	case 'updatePlugin':
		$plugin = secureEncode($_POST['plugin']);

		$setting = $_POST['setting'];
		$premium = secureEncode($_POST['premium']);

		$val = $_POST['val'];
		$val = str_replace("'", "''", $val);

		$mysqli->query("UPDATE plugins_settings SET setting_val = '".$val."' where plugin = '".$plugin."' and setting = '".$setting."'");	

		$mysqli->query("INSERT INTO plugins_settings_values(plugin,setting,setting_val) VALUES ('".$plugin."','".$setting."','".$val."') ON DUPLICATE KEY UPDATE setting_val = '".$val."'");			

		if($setting == 'enabled'){
			if($val == 'Yes'){
				$val = 1;				
			} else {
				$val = 0;
			}
			$mysqli->query("UPDATE plugins SET enabled = '".$val."' where name = '".$plugin."'");		
		}
		if($setting == 'moderators'){
			$moderators = explode(',',$val);
			$moderationList = getArray('moderation_list','','moderation ASC');

			foreach ($moderators as $moderator) {
				$mysqli->query("INSERT INTO moderators (id) VALUES ('".$moderator."')");
				foreach ($moderationList as $data) {
					$mysqli->query("INSERT INTO moderators_permission (id,setting,setting_val)
					VALUES ('".$moderator."','".$data['moderation']."','No')");
				}																								
			}
		}		
	break;

	case 'updatePricing':
		$id = secureEncode($_POST['id']);
		$type = secureEncode($_POST['type']);
		$col = secureEncode($_POST['col']);
		$val = secureEncode($_POST['val']);

		if($type == 'credits'){
			$mysqli->query("UPDATE config_credits SET $col = '".$val."' where id = '".$id."'");	
		}
		if($type == 'premium'){
			$mysqli->query("UPDATE config_premium SET $col = '".$val."' where id = '".$id."'");		
		}
		if($type == 'feature'){
			$mysqli->query("UPDATE config_prices SET price = '".$val."' where feature = '".$id."'");		
		}		
	break;

	case 'updateModeratorPermission':
		$id = secureEncode($_POST['id']);
		$setting = secureEncode($_POST['setting']);
		$val = secureEncode($_POST['val']);
		$mysqli->query("INSERT INTO moderators_permission (id,setting,setting_val) 
		VALUES ('".$id."','".$setting."','".$val."') ON DUPLICATE KEY UPDATE setting_val = '".$val."'");
			
	break;

	case 'updateDataProfile':
		$uid = secureEncode($_POST['uid']);
		$method = secureEncode($_POST['method']);
		$col = secureEncode($_POST['col']);
		$custom = secureEncode($_POST['custom']);
		$val = secureEncode($_POST['val']);
		$time = time();	

		if($method == 'addPremium'){
			$premiumDays = $val;
			$val = 1;
			$extra = 86400 * $premiumDays;
			$premium = $time + $extra;
			$mysqli->query("UPDATE users_premium set premium = '".$premium."' where uid = '".$uid."' ");	
		}

		$extraValues = '';
		if($method == 'setAdministrator'){
			$extraValues = ',moderator = "'.$custom.'"';
		}

		if($method == 'setFakeUser'){
			$mysqli->query("UPDATE users_photos SET $col = '".$val."' WHERE u_id = '".$uid."'");
		}

		if($method == 'addToSpotlight'){
			getUserInfo($uid,1);
			$lat = $sm['profile']['lat'];
			$lng = $sm['profile']['lng'];
			$photo = $sm['profile']['profile_photo'];
			$lang = $sm['profile']['lang'];	

			$query = "INSERT INTO spotlight (u_id,time,lat,lng,photo,lang,country)
			 VALUES ('".$uid."', '".$time."', '".$lat."', '".$lng."', '".$photo."', '".$lang."', '".$sm['profile']['country']."') ON DUPLICATE KEY UPDATE time = '".$time."'";
			$mysqli->query($query);			
			break;
		}

		$mysqli->query("UPDATE users SET $col = '".$val."' $extraValues WHERE id = '".$uid."'");		
	break;		

	case 'updateAccounts':
		$id = secureEncode($_POST['id']);
		$type = secureEncode($_POST['type']);
		$col = secureEncode($_POST['col']);
		$val = secureEncode($_POST['val']);
		$mysqli->query("UPDATE config_accounts SET $col = '".$val."' where type = '".$id."'");		
	break;	
	
	case 'manageGift':
		$id = secureEncode($_POST['id']);
		$icon = secureEncode($_POST['icon']);
		$name = secureEncode($_POST['name']);
		$price = secureEncode($_POST['price']);

		if($id > 0){
			$mysqli->query('UPDATE gifts SET gift = "'.$name.'", price = "'.$price.'", icon = "'.$icon.'" 
				WHERE id = "'.$id.'" ');
		} else {
			$mysqli->query('INSERT INTO gifts (gift,price,icon) VALUES ("'.$name.'","'.$price.'","'.$icon.'")');
		}
	    $arr = array();
	    $arr['gifts'] = getGiftsAdmin();
		echo json_encode($arr); 		
	break;

	case 'addLanguage':
		$name = secureEncode($_POST['name']);
		$prefix = secureEncode($_POST['prefix']);
		$name = ucfirst($name);
		$prefix = strtolower($prefix);

		$query = 'INSERT INTO languages (name,prefix) VALUES ("'.$name.'","'.$prefix.'")';
  		if ($mysqli->query($query) === TRUE) {
  			$last_id = $mysqli->insert_id;

  			$langTables = ['site_lang','app_lang','email_lang','landing_lang','seo_lang','config_genders','config_profile_questions','config_profile_answers'];
  			foreach ($langTables as $table) {
	  			$english = getArray($table,'where lang_id = 1','id desc');
				foreach ($english as $lang) {
					if($table == 'config_genders'){
						$mysqli->query('INSERT INTO '.$table.' (id,lang_id,name,sex) 
						VALUES ('.$lang['id'].','.$last_id.',"'.$lang['name'].'","'.$lang['sex'].'")');
					} else if($table == 'config_profile_questions'){
						$mysqli->query('INSERT INTO '.$table.' (id,lang_id,question,method,q_order) 
						VALUES ('.$lang['id'].','.$last_id.',"'.$lang['question'].'","'.$lang['method'].'","'.$lang['q_order'].'")');
					} else if($table == 'config_profile_answers'){
						$mysqli->query('INSERT INTO '.$table.' (id,lang_id,answer,qid) 
						VALUES ('.$lang['id'].','.$last_id.',"'.$lang['answer'].'","'.$lang['qid'].'")');
					} else if($table == 'seo_lang'){
						$mysqli->query('INSERT INTO '.$table.' (id,lang_id,text,page) 
						VALUES ('.$lang['id'].','.$last_id.',"'.$lang['text'].'","'.$lang['page'].'")');
					} else if($table == 'landing_lang'){
						$mysqli->query('INSERT INTO '.$table.' (id,lang_id,text,theme,preset) 
						VALUES ('.$lang['id'].','.$last_id.',"'.$lang['text'].'","'.$lang['theme'].'","'.$lang['preset'].'")');
					} else {
						$mysqli->query('INSERT INTO '.$table.' (id,lang_id,text) 
						VALUES ('.$lang['id'].','.$last_id.',"'.$lang['text'].'")');
					}
				} 
  			} 			
  		}			
	    $arr = array();
		echo json_encode($arr); 		
	break;	

	case 'deleteLanguage':
		$id = secureEncode($_POST['id']);
		$mysqli->query('DELETE FROM languages WHERE id = "'.$id.'"');
		$mysqli->query('DELETE FROM site_lang WHERE lang_id = "'.$id.'"');
		$mysqli->query('DELETE FROM app_lang WHERE lang_id = "'.$id.'"');
		$mysqli->query('DELETE FROM email_lang WHERE lang_id = "'.$id.'"');
		$mysqli->query('DELETE FROM config_profile_answers WHERE lang_id = "'.$id.'"');
		$mysqli->query('DELETE FROM config_genders WHERE lang_id = "'.$id.'"');
		$mysqli->query('DELETE FROM config_profile_questions WHERE lang_id = "'.$id.'"');
		$mysqli->query('DELETE FROM seo_lang WHERE lang_id = "'.$id.'"');
		$mysqli->query('DELETE FROM landing_lang WHERE lang_id = "'.$id.'"');		
		$mysqli->query('UPDATE users set lang = 1 WHERE lang = "'.$id.'"');
	    $arr = array();
		echo json_encode($arr); 		
	break;

	case 'removeFromSpotlight':
		$time = secureEncode($_POST['time']);
		$mysqli->query('DELETE FROM spotlight WHERE time = "'.$time.'"');
	    $arr = array();
		echo json_encode($arr); 		
	break;	
	
	    case 'updateVerifyStatus':
        $userid = secureEncode($_POST['uid']);
        $mysqli->query('UPDATE users SET verified = 1 WHERE id = "'.$userid.'"');
        $arr = array();
        echo json_encode($arr); 	
    break;

	case 'updateOnlineDay':
		$uid = secureEncode($_POST['uid']);
		$mon = secureEncode($_POST['monday']);
		$tue = secureEncode($_POST['tuesday']);
		$wed = secureEncode($_POST['wednesday']);
		$thu = secureEncode($_POST['thursday']);
		$fri = secureEncode($_POST['friday']);
		$sat = secureEncode($_POST['saturday']);
		$sun = secureEncode($_POST['sunday']);

		$mysqli->query('INSERT INTO users_online_day (uid,mon,tue,wed,thu,fri,sat,sun) VALUES
		("'.$uid.'","'.$mon.'","'.$tue.'","'.$wed.'","'.$thu.'","'.$fri.'","'.$sat.'","'.$sun.'")
		ON DUPLICATE KEY UPDATE mon = "'.$mon.'",tue = "'.$tue.'",wed = "'.$wed.'",thu = "'.$thu.'",fri = "'.$fri.'",sat = "'.$sat.'", sun ="'.$sun.'"');

		$today = date('w');
		$today = 'day'.$today;
		cronUpdateOnlineDay($today);
		
	    $arr = array();
		echo json_encode($arr); 	
	break;

	case 'updateOnlineDayCron':
		$today = date('w');
		$today = 'day'.$today;
		cronUpdateOnlineDay($today);
		
	    $arr = array();
		echo json_encode($arr); 	
	break;

	case 'removeGift':
		$id = secureEncode($_POST['id']);
		$mysqli->query('DELETE FROM gifts WHERE id = "'.$id.'" ');
	    $arr = array();
	    $arr['gifts'] = getGiftsAdmin();
		echo json_encode($arr); 		
	break;

	case 'deleteChatMessage':
		$id = secureEncode($_POST['id']);
		$mysqli->query('DELETE FROM chat WHERE id = "'.$id.'" ');
		echo json_encode($arr); 		
	break;	

	case 'deleteVideocall':
		$videocall = secureEncode($_POST['videocall']);
		

		$video1 = getData('videocall','c_id_video','where call_date = "'.$videocall.'"');
		if($video1 != 'noData'){
			$video1 = str_replace($sm['config']['site_url'], '../', $video1);
			unlink($video1);
		}

		$video2 = getData('videocall','r_id_video','where call_date = "'.$videocall.'"');
		if($video2 != 'noData'){
			$video2 = str_replace($sm['config']['site_url'], '../', $video2);
			unlink($video2);
		}

		error_log($video2);
		error_log($video1);

		$mysqli->query('DELETE FROM videocall WHERE call_date = "'.$videocall.'" ');
	    $arr = array();
		echo json_encode($arr); 		
	break;		

	case 'manageInterest':
		$id = secureEncode($_POST['id']);
		$icon = secureEncode($_POST['icon']);
		$name = secureEncode($_POST['name']);

		if($id > 0){
			$mysqli->query('UPDATE interest SET name = "'.$name.'", icon = "'.$icon.'" 
				WHERE id = "'.$id.'" ');
		} else {
			$mysqli->query('INSERT INTO interest (name,icon) VALUES ("'.$name.'","'.$icon.'")');
		}
	    $arr = array();
	    $arr['interest'] = getInterestsAdmin();
		echo json_encode($arr); 		
	break;

	case 'removeInterest':
		$id = secureEncode($_POST['id']);
		$mysqli->query('DELETE FROM interest WHERE id = "'.$id.'" ');
	    $arr = array();
	    $arr['interest'] = getInterestsAdmin();
		echo json_encode($arr); 		
	break;		

	case 'updatePreset':
		$theme = secureEncode($_POST['theme']);
		$preset = secureEncode($_POST['preset']);
		$type = secureEncode($_POST['themeType']);
	    if($type == 'Desktop'){
	      $mysqli->query("UPDATE settings SET setting_val = '".$preset."' where setting = 'desktopThemePreset'");
	    }
	    if($type == 'Landing'){
	      $mysqli->query("UPDATE settings SET setting_val = '".$preset."' where setting = 'landingThemePreset'");
	      $mysqli->query("UPDATE settings SET setting_val = '".$theme."' where setting = 'landingTheme'");
	    }	    		
	break;
	case 'removeModerator':
		$mod = secureEncode($_POST['val']);
	    $mysqli->query("DELETE FROM moderators where id = '".$mod."'");	
	    $mysqli->query("DELETE FROM moderators_permission where id = '".$mod."'");	
	    
	break;	
	case 'editCurrentPreset':
		$preset = secureEncode($_POST['preset']);
		$action = secureEncode($_POST['editAction']);
		$val = secureEncode($_POST['val']);
		$time = time();
	    if($action == 'rename'){
	      $mysqli->query("UPDATE theme_preset SET preset_alias = '".$val."',theme_modification = '".$time."' where preset = '".$preset."'");
	    }
	    $arr = array();

	    if($action == 'duplicate'){
	      $alias = $val.' Clone';
	      $newPreset = $preset.'-'.rand(0,100);
	      $presetFilter = 'WHERE preset = "'.$preset.'"';
	      $base = getData('theme_preset','preset_base',$presetFilter);
	      $data = getData('theme_preset','theme_settings',$presetFilter);
	      $theme = getData('theme_preset','theme',$presetFilter);
	      $landing = getData('theme_preset','landing',$presetFilter);
		  $mysqli->query("INSERT INTO theme_preset (preset,preset_alias,preset_base,theme,theme_settings,author,theme_modification,landing)
		    	VALUES ('".$newPreset."','".$alias."','".$base."','".$theme."','".$data."','".$sm['user']['name']."','".time()."','".$landing."')");

		  if($landing == 1){
		  	$arr['reload'] = 'Landing';
		  } else {
		  	$arr['reload'] = 'Desktop';
		  }
		  
		  $fonts = getArray('theme_preset_fonts',$presetFilter,'font DESC');
		  foreach ($fonts as $font) {
			$mysqli->query("INSERT INTO theme_preset_fonts (preset,font,setting) VALUES ('".$newPreset."','".$font['font']."','".$font['setting']."')");		  	
		  }

		  if($landing == 1){
			  $lang = getArray('landing_lang','WHERE theme = "'.$theme.'"','id ASC');
			  foreach ($lang as $l) {
				$mysqli->query("INSERT INTO landing_lang (id,lang_id,preset,theme,text) VALUES ('".$l['id']."','".$l['lang_id']."','".$newPreset."','".$theme."','".$l['text']."')");		  	
			  }		  	
		  }

		  echo json_encode($arr);
	    }
	    if($action == 'delete'){
	    	$mysqli->query("DELETE FROM theme_preset where preset = '".$preset."'");
	    }    		
	break;	
	case 'addPreset':
		$theme = secureEncode($_POST['theme']);
		$preset = secureEncode($_POST['preset']);
		$base = secureEncode($_POST['base']);


		if(!empty($_POST['data'])){
			$data = $_POST['data'];
		} else {
			$data = getData('theme_preset','theme_settings','WHERE preset = "'.$theme.'"');	
		}
		
		$landing = getData('theme_preset','landing','WHERE preset = "'.$theme.'"');
		
		if($landing == 'noData'){
			$landing = 0;
		}

		$alias = secureEncode($_POST['alias']);
	    $mysqli->query("INSERT INTO theme_preset (preset,preset_alias,preset_base,theme,theme_settings,author,theme_modification,landing)
	    	VALUES ('".$preset."','".$alias."','".$base."','".$theme."','".$data."','".$sm['user']['name']."','".time()."','".$landing."')");
	    
		$fonts = getArray('theme_preset_fonts','WHERE preset = "'.$theme.'"','font DESC');
		foreach ($fonts as $font) {
			$mysqli->query("INSERT INTO theme_preset_fonts (preset,font,setting) VALUES ('".$preset."','".$font['font']."','".$font['setting']."')");		  	
		}

		if($landing == 1){
		  $lang = getArray('landing_lang','WHERE theme = "'.$theme.'"','id ASC');
		  foreach ($lang as $l) {
			$mysqli->query("INSERT INTO landing_lang (id,lang_id,preset,theme,text) VALUES ('".$l['id']."','".$l['lang_id']."','".$preset."','".$theme."','".$l['text']."')");		  	
		  }		  	
		}

		echo $landing;
    		
	break;

	case 'exportJSON':
		$preset = secureEncode($_POST['preset']);
		$themeFilter = 'preset = "'.$preset.'"';
		$fileName = secureEncode($_POST['name']);
		$fileName = $fileName.time();
		$data = array();
		$arr = array();
		$data = getDataArray('theme_preset',$themeFilter);
		$fonts = getArray('theme_preset_fonts','WHERE '.$themeFilter,'font DESC');
		$data['fonts'] = $fonts;
		$data = json_encode($data);
		file_put_contents('../assets/sources/presets/'.$fileName.'.json', $data);

		$arr['url'] = $sm['config']['site_url'].'assets/sources/presets/'.$fileName.'.json';
		$arr['name'] = $fileName;
		echo json_encode($arr);		   		
	break;

	case 'exportJSONLanguage':
		$id = secureEncode($_POST['id']);
		$name = secureEncode($_POST['name']);
		$prefix = secureEncode($_POST['prefix']);
		$langFilter = 'WHERE lang_id = '.$id;
		$langFilterLanding = 'WHERE lang_id = '.$id.' AND preset = "'.$sm['settings']['landingThemePreset'].'"';
		$order = 'id ASC';
		$fileName = secureEncode($name);
		$fileName = $fileName.time();
		$data = array();
		$arr = array();

		$data['name'] = $name;
		$data['prefix'] = $prefix;
		$data['site_lang'] = getArray('site_lang',$langFilter,$order);
		$data['app_lang'] = getArray('app_lang',$langFilter,$order);
		$data['email_lang'] = getArray('email_lang',$langFilter,$order);
		$data['seo_lang'] = getArray('seo_lang',$langFilter,$order);
		
		$data['questions_lang'] = getArray('config_profile_questions',$langFilter,$order);
		$data['answer_lang'] = getArray('config_profile_answers',$langFilter,$order);
		$data['gender_lang'] = getArray('config_genders',$langFilter,$order);

		$data['landing_lang'] = getArray('landing_lang',$langFilterLanding,$order);

		$data = json_encode($data,JSON_UNESCAPED_UNICODE);
		file_put_contents('../assets/sources/presets/'.$fileName.'.json', $data);

		$arr['url'] = $sm['config']['site_url'].'assets/sources/presets/'.$fileName.'.json';
		$arr['name'] = $fileName;
		echo json_encode($arr);		   		
	break;


	case 'updateTheme':
		$theme = secureEncode($_POST['theme']);
		$setting = secureEncode($_POST['setting']);
		$type = secureEncode($_POST['type']);
		$preset = secureEncode($_POST['preset']);
		$val = secureEncode($_POST['val']);
		$time = time();

		$themeFilter = 'WHERE theme = "'.$theme.'" AND preset = "'.$preset.'"';
		$sm['preset'] = json_decode(getData('theme_preset','theme_settings',$themeFilter),true);

		$themeSettingsVal = $val;
		if (strpos($val, ':') !== false && $type == 'font') {
			$valArr = explode(':', $val);
			$themeSettingsVal = $valArr[0];
		} 
		$mysqli->query("UPDATE theme_settings SET setting_val = '".$themeSettingsVal."' where theme = '".$theme."' and setting = '".$setting."'");

		if(isset($_POST['gradient'])){
			$gradient = secureEncode($_POST['gradient']);
			if (strpos($val, 'gradient') !== false) {
			    $mysqli->query("UPDATE theme_settings SET setting_val = 'Yes' where theme = '".$theme."' and setting = '".$gradient."'");
				$sm['preset'][$gradient]['val'] = 'Yes';			    
			} else {
				$mysqli->query("UPDATE theme_settings SET setting_val = 'No' where theme = '".$theme."' and setting = '".$gradient."'");
				$sm['preset'][$gradient]['val'] = 'No';			
			}
		}

		if($val == 'Left-Menu'){
			$wide = secureEncode($_POST['wide']);
			$mysqli->query("UPDATE theme_settings SET setting_val = 'No' where theme = '".$theme."' and setting = 'design_style_wide'");
			$sm['preset']['design_style_wide']['val'] = 'No';
		}

		if($val == 'Top-Menu'){
			$wide = secureEncode($_POST['wide']);
			$mysqli->query("UPDATE theme_settings SET setting_val = 'Yes' where theme = '".$theme."' and setting = 'design_style_wide'");
			$sm['preset']['design_style_wide']['val'] = 'Yes';
		}		


		//update preset
		$sm['preset'][$setting]['val'] = $themeSettingsVal;
		$preset_val = json_encode($sm['preset']);

		
		$themeTablesFilter = 'WHERE theme = "'.$theme.'"';
		$themeTables = getSelectedArray('setting,setting_val','theme_settings',$themeTablesFilter,'setting');
		foreach ($themeTables as $check) {
			if(!array_key_exists($check['setting'],$sm['preset'])){
				$sm['preset'][$check['setting']]['val'] = $check['setting_val'];
			}
		}
		$preset_val = json_encode($sm['preset']);

		$mysqli->query("INSERT INTO theme_preset (preset,theme,theme_settings,author) VALUES ('".$preset."','".$theme."','".$preset_val."','".$sm['user']['id']."') ON DUPLICATE KEY UPDATE theme_settings = '".$preset_val."',theme_modification = '".$time."'");

		if($type == 'font'){
			$mysqli->query("DELETE FROM theme_preset_fonts WHERE preset = '".$preset."' AND setting = '".$setting."'");	
			$mysqli->query("INSERT INTO theme_preset_fonts (preset,font,setting) VALUES ('".$preset."','".$val."','".$setting."')");
		}

	break;	
	case 'changePage':	
		$page = secureEncode($_POST['page']);
		$plugin = secureEncode($_POST['plugin']);
		$category = secureEncode($_POST['category']);
		if(!empty($plugin)){
			$_GET['plugin'] = $plugin;
			$_GET['category'] = $category;
		}
		if(!empty($category)){
			$_GET['category'] = $category;
		}
		if($page == 'themes'){
			$_GET['type'] = $plugin;
		}
		if($page == 'editLanguage'){
			$filter = 'id = '.$plugin;
			$sm['editLang'] = getDataArray('languages',$filter);
			$sm['editLang']['prefix'] = getData('languages','prefix','WHERE id = '.$plugin);
			if($category == 'site_lang'){
				$sm['editLang']['table'] = $category;
				$sm['editLang']['title'] = 'Website';
			}
			if($category == 'app_lang'){
				$sm['editLang']['table'] = $category;
				$sm['editLang']['title'] = 'Mobile';
			}
			if($category == 'email_lang'){
				$sm['editLang']['table'] = $category;
				$sm['editLang']['title'] = 'Email';
			}
			if($category == 'gender'){
				$sm['editLang']['table'] = $category;
				$sm['editLang']['title'] = 'Gender';
			}
			if($category == 'questions'){
				$sm['editLang']['table'] = $category;
				$sm['editLang']['title'] = 'Profile Questions';
			}
			if($category == 'landing_lang'){
				$sm['editLang']['table'] = $category;
				$sm['editLang']['title'] = 'Landing '.$sm['settings']['landingTheme'];
			}	
			if($category == 'seo_lang'){
				$sm['editLang']['table'] = $category;
				$sm['editLang']['title'] = 'Seo Pages';
			}																		
			
		}						
		$sm['content'] = requestAdministratorPage($page);
		echo $sm['content'];		
	break;

	case 'changePagePlugin':	
		$sm['content'] = requestAdministratorPage($page);
		echo $sm['content'];		
	break;		

	case 'getCitiesByCountry':
		$arr=array();	
		$time = time()-300;
		$country = secureEncode($_POST['country']);
		$cities = getArrayDSelected('city','users','where country ="'.$country.'"');
		$i=0;
		foreach ($cities as $val) { 
			$arr[$i]['city'] = $val['city'];
			$i++;
		}
		echo json_encode($arr);
	break;
	
	
    case 'json_search_users_newsletter_react_ai':
    $data = array();
    $arr = array();
    $time = time() - 300;

    // Sanitize inputs
    $gender = secureEncode($_POST['gender']);
    $age1 = secureEncode($_POST['age1']);
    $age2 = secureEncode($_POST['age2']);
    $order = secureEncode($_POST['order']);
    $date = secureEncode($_POST['date']);
    $dateEnabled = secureEncode($_POST['dateEnabled']);
    $fake = secureEncode($_POST['fake']);
    $real = secureEncode($_POST['realUser']);
    $premium = secureEncode($_POST['premium']);
    $online = secureEncode($_POST['online']);
    $searchInput = secureEncode($_POST['search']);
    $verified = secureEncode($_POST['verified']);
    $withStory = secureEncode($_POST['withStory']);
    $withProfilePicture = secureEncode($_POST['withProfilePicture']);
    $notpaid = secureEncode($_POST['notpaid']);
    $is_paid = secureEncode($_POST['is_paid']);
    $country = secureEncode($_POST['country']);
    $city = secureEncode($_POST['city']);
    
    // Parse date range
    $date = str_replace(' ', '', $date);
    $date = explode('to', $date);
    $date1 = $date[0];
    $date2 = $date[1];
    
    // Build filter conditions
    $filter = 'AND u.age BETWEEN ' . $age1 . ' AND ' . $age2;

    if($searchInput != '') {
        $filter .= " AND (u.id = '" . $searchInput . "' OR u.name LIKE '%$searchInput%' OR u.email LIKE '%$searchInput%' OR u.ip LIKE '%$searchInput%')";
    }
    if($gender != 'all') {
        $filter .= ' AND u.gender = ' . $gender;
    }
    if($online == 'on') {
        $filter .= ' AND u.last_access >= ' . $time;
    }
    if($premium == 'on') {
        $filter .= ' AND u.premium = 1';
    }
    if($verified == 'on') {
        $filter .= ' AND u.verified = 1';
    }

    // Fake/Real user filter
    if($fake == 'on' || $real == 'on' || $fake == 'off' || $real == 'off') {
        if($fake == 'on' && $real == 'on') {
            $filter .= ' AND (u.fake = 0 OR u.fake = 1)';
        } else if($fake == 'off' && $real == 'on') {
            $filter .= ' AND u.fake = 0';
        } else if($fake == 'on' && $real == 'off') {
            $filter .= ' AND u.fake = 1';
        } else {
            $filter .= ' AND u.fake = -1';
        }
    }

    // Date filter
    if($dateEnabled == 'on') {
        $filter .= " AND str_to_date(u.join_date, '%m/%d/%Y') BETWEEN STR_TO_DATE('$date1','%m/%d/%Y') AND STR_TO_DATE('$date2','%m/%d/%Y')";
    }

    if($country != 'all') {
        $filter .= ' AND u.country = "' . $country . '"';
    }
    if($city != 'all') {
        $filter .= ' AND u.city = "' . $city . '"';
    }

    $filter .= ' AND u.admin = 0';

    // Last online filters
    if($_POST['filter_last_online'] == '6') {
        $lastseen = strtotime('-180 days');
        $filter .= ' AND CAST(u.last_access AS SIGNED) > ' . $lastseen;
    } else if($_POST['filter_last_online'] == '3') {
        $lastseen = strtotime('-90 days');
        $filter .= ' AND CAST(u.last_access AS SIGNED) > ' . $lastseen;
    } else if($_POST['filter_last_online'] == '1') {
        $lastseen = strtotime('-30 days');
        $filter .= ' AND CAST(u.last_access AS SIGNED) > ' . $lastseen;
    } else if($_POST['filter_last_online'] == 'l7') {
        $lastseen = strtotime('-7 days');
        $filter .= ' AND CAST(u.last_access AS SIGNED) > ' . $lastseen;
    } else if($_POST['filter_last_online'] == 'A') {
        $filter .= ' AND CAST(u.last_access AS SIGNED) >= UNIX_TIMESTAMP(NOW() - INTERVAL 12 MONTH) AND CAST(u.last_access AS SIGNED) < UNIX_TIMESTAMP(NOW() - INTERVAL 6 MONTH)';
    } else if($_POST['filter_last_online'] == 'B') {
        $filter .= ' AND CAST(u.last_access AS SIGNED) >= UNIX_TIMESTAMP(NOW() - INTERVAL 18 MONTH) AND CAST(u.last_access AS SIGNED) < UNIX_TIMESTAMP(NOW() - INTERVAL 12 MONTH)';
    } else if($_POST['filter_last_online'] == 'C') {
        $filter .= ' AND CAST(u.last_access AS SIGNED) >= UNIX_TIMESTAMP(NOW() - INTERVAL 24 MONTH) AND CAST(u.last_access AS SIGNED) < UNIX_TIMESTAMP(NOW() - INTERVAL 18 MONTH)';
    } else if($_POST['filter_last_online'] == 'D') {
        $filter .= ' AND CAST(u.last_access AS SIGNED) >= UNIX_TIMESTAMP(NOW() - INTERVAL 36 MONTH) AND CAST(u.last_access AS SIGNED) < UNIX_TIMESTAMP(NOW() - INTERVAL 24 MONTH)';
    } else if($_POST['filter_last_online'] == 'E') {
        $filter .= ' AND CAST(u.last_access AS SIGNED) >= UNIX_TIMESTAMP(NOW() - INTERVAL 42 MONTH) AND CAST(u.last_access AS SIGNED) < UNIX_TIMESTAMP(NOW() - INTERVAL 36 MONTH)';
    } else if($_POST['filter_last_online'] == 'F') {
        $filter .= ' AND CAST(u.last_access AS SIGNED) >= UNIX_TIMESTAMP(NOW() - INTERVAL 48 MONTH) AND CAST(u.last_access AS SIGNED) < UNIX_TIMESTAMP(NOW() - INTERVAL 42 MONTH)';
    } else if($_POST['filter_last_online'] == 'G') {
        $filter .= ' AND CAST(u.last_access AS SIGNED) >= UNIX_TIMESTAMP(NOW() - INTERVAL 54 MONTH) AND CAST(u.last_access AS SIGNED) < UNIX_TIMESTAMP(NOW() - INTERVAL 48 MONTH)';
    } else if($_POST['filter_last_online'] == 'H') {
        $filter .= ' AND CAST(u.last_access AS SIGNED) >= UNIX_TIMESTAMP(NOW() - INTERVAL 60 MONTH) AND CAST(u.last_access AS SIGNED) < UNIX_TIMESTAMP(NOW() - INTERVAL 54 MONTH)';
    }

    // Max moderator messages filter
    $max_number_of_moderator_messages_per_chat = 4;
    if(!empty($_POST['filter_fake_msg_count'])) {
        $max_number_of_moderator_messages_per_chat = intval($_POST['filter_fake_msg_count']);
    }

    // User interest filter
    $filter_two = "";
    if(!empty($_POST['filter_user_intrest'])) {
        $userIntrest = implode(",", $_POST['filter_user_intrest']);
        $sqluserIntrest = "SELECT DISTINCT u_id FROM `users_interest` WHERE i_id IN($userIntrest)";
        $filter_two = " AND u.id IN( $sqluserIntrest )";
    }

    $search = array();

    // Newsletter users filter
    if(!empty($_REQUEST['filter_usersOfNl'])) {
        if(!empty($_REQUEST['filter_usersOfFl'])) {
            $nlsUsers = $mysqli->query("SELECT u.id,u.name,u.email,u.age,u.city,u.country,u.fake,u.admin,u.last_access,u.credits,u.premium,u.ip,u.online_day,u.verified,u.moderator, (SELECT COUNT(um.id) FROM users_mails um WHERE um.to = u.id AND DAY(um.ts) = DAY(NOW()) ) as nlEmailCount, (SELECT up.thumb FROM users_photos up WHERE up.u_id = u.id and up.profile = 1 ORDER BY up.id ASC LIMIT 1) as thumbnail , (SELECT MAX(s.id) FROM sales s WHERE (s.u_id = u.id and gateway='stripe' and s.payment_status='paid' AND s.amount>0) OR (s.u_id = u.id and s.amount>0 and s.gateway!='stripe') ) AS saleId FROM newsletter_target_users_react ntu JOIN users u ON u.id = ntu.user_id WHERE ntu.newsletter_id = '" . intval($_REQUEST['filter_usersOfNl']) . "' AND ntu.`free_coins_claimed` =0 GROUP BY ntu.user_id");
        } else {
            $sqlnl = "SELECT ntu.fake_user_id as r_id,u.id,u.id as s_id,u.name,u.email,u.age,u.city,u.country,u.fake,u.admin,u.last_access,u.credits,u.premium,u.ip,u.online_day,u.verified,u.moderator,(select username from users where id =ntu.fake_user_id) as fakeusername , (SELECT COUNT(um.id) FROM users_mails um WHERE um.to = u.id AND DAY(um.ts) = DAY(NOW()) ) as nlEmailCount, (SELECT up.thumb FROM users_photos up WHERE up.u_id = u.id and up.profile = 1 ORDER BY up.id ASC LIMIT 1) as thumbnail , (SELECT MAX(s.id) FROM sales s WHERE (s.u_id = u.id and gateway='stripe' and s.payment_status='paid' AND s.amount>0) OR (s.u_id = u.id and s.amount>0 and s.gateway!='stripe') ) AS saleId FROM newsletter_target_users_react ntu LEFT JOIN chat ch ON ch.newsletter_id = ntu.newsletter_id AND ntu.user_id = ch.r_id JOIN users u ON u.id = ntu.user_id WHERE ntu.newsletter_id = '" . intval($_REQUEST['filter_usersOfNl']) . "' AND ch.replied <> 1 GROUP BY ntu.user_id";
            $nlsUsers = $mysqli->query($sqlnl);
        }
        $search = $nlsUsers->fetch_all(MYSQLI_ASSOC);
    } else {
        // Use optimized query
        $from_date_filter_time = strtotime('2025/01/01 00:00:00');
        $hours_age_filter = !empty($_POST['filter_no_reply']) ? $_POST['filter_no_reply'] : 8;

        // OPTIMIZED QUERY FOR MYSQL 5.7 AND BELOW (NO CTEs)
        $optimizedSql = "
        SELECT 
            cmain.id, 
            cmain.time, 
            cmain.r_id, 
            cmain.s_id,
            u.id,
            u.name,
            u.email,
            u.age,
            u.city,
            u.country,
            u.fake,
            u.admin,
            u.last_access,
            ru.username as fakeusername,
            u.credits,
            u.premium,
            u.ip,
            u.online_day,
            u.verified,
            u.moderator,
            up.thumb as thumbnail,
            COALESCE(um.nlEmailCount, 0) as nlEmailCount,
            (SELECT MAX(s.id) FROM sales s WHERE (s.u_id = u.id and gateway='stripe' and s.payment_status='paid' AND s.amount>0) OR (s.u_id = u.id and s.amount>0 and s.gateway!='stripe')) AS saleId,
            MD5(CONCAT(cmain.s_id, '-', cmain.r_id)) AS unique_hash
        FROM chat cmain 
        INNER JOIN users u ON u.id = cmain.s_id
        LEFT JOIN users ru ON ru.id = cmain.r_id
        LEFT JOIN (
            SELECT 
                up1.u_id, 
                up1.thumb
            FROM users_photos up1
            INNER JOIN (
                SELECT u_id, MIN(id) as min_id
                FROM users_photos 
                WHERE profile = 1 
                GROUP BY u_id
            ) up2 ON up1.u_id = up2.u_id AND up1.id = up2.min_id
            WHERE up1.profile = 1
        ) up ON up.u_id = u.id
        LEFT JOIN (
            SELECT 
                um.to,
                COUNT(um.id) as nlEmailCount
            FROM users_mails um
            WHERE DAY(um.ts) = DAY(NOW())
            GROUP BY um.to
        ) um ON um.to = u.id
        WHERE cmain.id IN ( 
            SELECT MAX(c.id) 
            FROM chat c
            INNER JOIN users u2 ON u2.id = c.s_id 
            WHERE u2.fake = 0  
              AND c.fake = 1
              AND c.time > $from_date_filter_time
              AND c.time < UNIX_TIMESTAMP(NOW() - INTERVAL $hours_age_filter HOUR)
              $filter 
              $filter_two
            GROUP BY c.s_id, c.r_id 
        ) 
        AND cmain.time > $from_date_filter_time  
        AND cmain.time < UNIX_TIMESTAMP(NOW() - INTERVAL $hours_age_filter HOUR)
        AND NOT EXISTS (
            SELECT 1 FROM chat cp 
            WHERE cp.r_id = cmain.s_id 
              AND cp.s_id = cmain.r_id 
              AND cp.id > cmain.id 
              AND cp.time > UNIX_TIMESTAMP(NOW() - INTERVAL $hours_age_filter HOUR)
        ) 
        AND NOT EXISTS (
            SELECT 1 FROM reports_chats rc 
            WHERE rc.user_to = cmain.r_id 
              AND rc.user_from = cmain.s_id 
              AND rc.viewed = 0
        ) 
        AND (
            SELECT COUNT(*) FROM chat cp 
            WHERE cp.r_id = cmain.s_id 
              AND cp.s_id = cmain.r_id 
              AND cp.id > cmain.id 
              AND cp.modusername IS NOT NULL
        ) = $max_number_of_moderator_messages_per_chat
        AND NOT EXISTS (
            SELECT 1 FROM newsletter_target_users_react nr
            WHERE nr.fake_user_id = cmain.r_id
              AND nr.user_id = cmain.s_id
              AND nr.execution_status = 0
        )
        AND NOT EXISTS (
            SELECT 1 FROM newsletter_target_users_react nr
            WHERE nr.user_id = cmain.s_id
              AND nr.execute_by >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
        )
        ORDER BY u.$order DESC";
echo $optimizedSql;die;
        $nlAllUsers = $mysqli->query($optimizedSql);
        $search = $nlAllUsers->fetch_all(MYSQLI_ASSOC);
    }

    $i = 0;
    
    if(isset($search)) {
        foreach ($search as $value) {
            // Payment filters
            if($notpaid == 'on') {
                if($value['saleId'] > 0) {
                    if($is_paid == 'off')
                        continue;
                }
            }
            
            if($is_paid == 'on') {
                if($value['saleId'] <= 0) {
                    if($notpaid == 'off')     
                        continue;
                }
            }
            
            // Profile picture filters
            if($withProfilePicture == 'on') {
                if(empty($value['thumbnail'])) {
                    continue;
                }
            } 
            if($withProfilePicture == 'off') {
                if(!empty($value['thumbnail'])) {
                    continue;
                }
            }

            $today = date('w');
            $onlineNow = '';
            $userPremium = 'No';
            $ip = $value['ip'];
            
            if($value['last_access'] >= $time || $value['fake'] == 1 && $value['online_day'] == $today) {
                $onlineNow = 'avatar-online lg';
            }
            
            if($value['premium'] == 1) {
                $userPremium = 'Yes';
            }

            if(!empty($value['thumbnail'])) {
                $photo = $value['thumbnail'];
            } else {
                $photo = $sm['config']['theme_url'] . '/images/no_user.png';
            }

            $badge = intval($value['nlEmailCount']);

            $data[$i] = array(
                'userId' => $value['id'],
                'md5' => md5($value['s_id']),
                'c_rid' => isset($value['c_rid']) ? $value['c_rid'] : null,
                'fake_user_id' => $value['r_id'],
                'fakeusername' => $value['fakeusername'],
                'userName' => $value['name'],
                'userEmail' => $value['email'],
                'userAge' => $value['age'],
                'userCity' => $value['city'],
                'userCountry' => $value['country'],
                'userCredits' => $value['credits'],
                'userPremium' => $userPremium,
                'userPhoto' => $photo,
                'userVerified' => intval($value['verified']),
                'userStatusClass' => $onlineNow,
                'userLastAccess' => time_elapsed_string($value['last_access']),
                'checkStory' => isset($checkStory) ? $checkStory : '',
                'storyEl' => "'.asd'",
                'siteUrl' => $sm['config']['site_url'],
                'newsletterMailCount' => intval($value['nlEmailCount']),
                'newsletterMailBadge' => $badge,
            );

            $i++;
        }
    }
    
    $my_array = remove_duplicateKeys("md5", $data);

    $arr['data'] = $my_array;
    $arr['total'] = count($my_array);
    
    echo json_encode($arr);
break;



	case 'json_search_users_newsletter_react':
	    //$exeStart = microtime(true);
//	error_reporting(E_ALL);
//ini_set("display_errrors","On");
		$data = array();
		$arr=array();
		$time = time()-300;

		$gender = secureEncode($_POST['gender']);
		$age1 = secureEncode($_POST['age1']);
		$age2 = secureEncode($_POST['age2']);
		$order = secureEncode($_POST['order']);
		$date = secureEncode($_POST['date']);
		$dateEnabled = secureEncode($_POST['dateEnabled']);
		$fake = secureEncode($_POST['fake']);
		$real = secureEncode($_POST['realUser']);
		$premium = secureEncode($_POST['premium']);
		$online = secureEncode($_POST['online']);
		$searchInput = secureEncode($_POST['search']);
		$verified = secureEncode($_POST['verified']);
		$withStory = secureEncode($_POST['withStory']);
		$withProfilePicture = secureEncode($_POST['withProfilePicture']);
		
        $notpaid = secureEncode($_POST['notpaid']);
        $is_paid = secureEncode($_POST['is_paid']);



		$country = secureEncode($_POST['country']);
		$city = secureEncode($_POST['city']);
		$date = str_replace(' ', '', $date);
		$date = explode('to',$date);
		$date1 = $date[0];
		$date2 = $date[1];
		$filter = 'AND u.age BETWEEN '.$age1.' AND '.$age2;

		if($searchInput != ''){
			$filter.=" AND u.id = '".$searchInput."' OR u.name LIKE '%$searchInput%' OR u.email LIKE '%$searchInput%' OR u.ip LIKE '%$searchInput%'";
		}
		if($gender != 'all'){
			$filter.=' AND u.gender ='.$gender;
		}
		if($online == 'on'){
			$filter.=' AND u.last_access >='.$time;
		}
		if($premium == 'on'){
			$filter.=' AND u.premium = 1';
		}
		if($verified == 'on'){
			$filter.=' AND u.verified = 1';
		}
				
		if($fake == 'on' || $real == 'on' || $fake == 'off' || $real == 'off'){
			if($fake == 'on' && $real == 'on'){
				$filter.=' AND (u.fake = 0 || u.fake=1) ';
			} else if($fake == 'off' && $real == 'on'){
				$filter.=' AND u.fake = 0';
			} else if($fake == 'on' && $real == 'off'){
				$filter.=' AND u.fake=1';
			} else {
				$filter.=' AND u.fake = -1';
			}	
		}		
		if($dateEnabled == 'on'){
		//	$filter.=' AND join_date BETWEEN "'.$date1.'" AND "'.$date2.'"';
					$filter.=" AND str_to_date(u.join_date, '%m/%d/%Y') BETWEEN    STR_TO_DATE('$date1','%m/%d/%Y') AND  STR_TO_DATE( '$date2' ,'%m/%d/%Y')  ";       




		}

		if($country != 'all'){
			$filter.=' AND u.country = "'.$country.'"';
		}

		if($city != 'all'){
			$filter.=' AND u.city = "'.$city.'"';
		}							

		//if($sm['user']['admin'] == 2){
			$filter.=' AND u.admin = 0';
		//}
		
    if($_POST['filter_last_online'] == '6'){
    		$lastseen = strtotime('-180 days');
    		$filter .= ' AND  CAST(u.last_access AS SIGNED)  > '.$lastseen.'';
    	}else if($_POST['filter_last_online'] == '3'){
    		$lastseen = strtotime('-90 days');
    		$filter .= ' AND  CAST(u.last_access AS SIGNED)  > '.$lastseen.'';
    	}else if($_POST['filter_last_online'] == '1'){
    		$lastseen = strtotime('-30 days');
    		$filter .= ' AND  CAST(u.last_access AS SIGNED)  > '.$lastseen.'';
    	}else if($_POST['filter_last_online'] == 'l7'){
    		$lastseen = strtotime('-7 days');
    		$filter .= ' AND  CAST(u.last_access AS SIGNED)  > '.$lastseen.'';
    	}    else if($_POST['filter_last_online'] == 'A'){
		
    		$filter .= ' AND  CAST(u.last_access AS SIGNED) >= UNIX_TIMESTAMP(NOW() - INTERVAL 12 MONTH) AND CAST(u.last_access AS SIGNED) < UNIX_TIMESTAMP(NOW() - INTERVAL 6 MONTH) ';
    	}else if($_POST['filter_last_online'] == 'B'){
    	
    		$filter .= ' AND     CAST(u.last_access AS SIGNED) >= UNIX_TIMESTAMP(NOW() - INTERVAL 18 MONTH) AND CAST(u.last_access AS SIGNED) < UNIX_TIMESTAMP(NOW() - INTERVAL 12 MONTH) ';
    	}else if($_POST['filter_last_online'] == 'C'){
    	
    		$filter .= ' AND     CAST(u.last_access AS SIGNED) >= UNIX_TIMESTAMP(NOW() - INTERVAL 24 MONTH) AND CAST(u.last_access AS SIGNED) < UNIX_TIMESTAMP(NOW() - INTERVAL 18 MONTH)';
    	}else if($_POST['filter_last_online'] == 'D'){
    	
    		$filter .= '  AND    CAST(u.last_access AS SIGNED) >= UNIX_TIMESTAMP(NOW() - INTERVAL 36 MONTH) AND CAST(u.last_access AS SIGNED) < UNIX_TIMESTAMP(NOW() - INTERVAL 24 MONTH) ';
    	}
    	else if($_POST['filter_last_online'] == 'E'){
    		
    		$filter .= ' AND     CAST(u.last_access AS SIGNED) >= UNIX_TIMESTAMP(NOW() - INTERVAL 42 MONTH) AND CAST(u.last_access AS SIGNED) < UNIX_TIMESTAMP(NOW() - INTERVAL 36 MONTH) ';
    	}
    	else if($_POST['filter_last_online'] == 'F'){
    		
    		$filter .= ' AND     CAST(u.last_access AS SIGNED) >= UNIX_TIMESTAMP(NOW() - INTERVAL 48 MONTH) AND CAST(u.last_access AS SIGNED) < UNIX_TIMESTAMP(NOW() - INTERVAL 42 MONTH) ';
    	}
    	else if($_POST['filter_last_online'] == 'G'){
    		
    		$filter .= '  AND    CAST(u.last_access AS SIGNED) >= UNIX_TIMESTAMP(NOW() - INTERVAL 54 MONTH) AND CAST(u.last_access AS SIGNED) < UNIX_TIMESTAMP(NOW() - INTERVAL 48 MONTH) ';
    	}else if($_POST['filter_last_online'] == 'H'){
           
    		$filter .= ' AND     CAST(u.last_access AS SIGNED) >= UNIX_TIMESTAMP(NOW() - INTERVAL 60 MONTH) AND CAST(u.last_access AS SIGNED) < UNIX_TIMESTAMP(NOW() - INTERVAL 54 MONTH) ';

    	}
    	
    	
    	
        $filter_one='';
        $max_number_of_moderator_messages_per_chat=4;
        if(!empty($_POST['filter_fake_msg_count'])){
          $max_number_of_moderator_messages_per_chat=intval($_POST['filter_fake_msg_count']);
        
      }
        /*
      if(!empty($_POST['filter_fake_msg_count'])){
          $filter_fake_msg_count=intval($_POST['filter_fake_msg_count']);
          $filter_one .= "  (SELECT c.r_id as c_rid FROM chat c WHERE c.s_id= u.id AND c.fake=1 GROUP BY c.s_id,c.r_id HAVING count(c.id) >=$filter_fake_msg_count
          ORDER BY count(c.id) DESC LIMIT 1 ) as c_rid, ";
      }


      if(!empty($_POST['filter_no_reply'])){
              $filter_one='';
          $filter_no_reply_days=intval($_POST['filter_no_reply']);
          $today              = strtotime("today 12:00");
          $filter_no_reply_day_timestamp          = strtotime('-'.$filter_no_reply_days.' day', $today);
          $filter_one .= " (SELECT c.time FROM chat c WHERE
          c.r_id=u.id AND c.fake=0 GROUP BY c.s_id, c.r_id HAVING
           CAST(c.time AS SIGNED)>=$filter_no_reply_day_timestamp ORDER BY c.time DESC LIMIT 1) as reply_chat_time,
                    ";
      }

*/

        $search = array();
   
        
        if(!empty($_REQUEST['filter_usersOfNl']) ){
            
          if(!empty($_REQUEST['filter_usersOfFl']) ){
            $nlsUsers = $mysqli->query("SELECT u.id,u.name,u.email,u.age,u.city,u.country,u.fake,u.admin,u.last_access,u.credits,u.premium,u.ip,u.online_day,u.verified,u.moderator, (SELECT COUNT(um.id) FROM users_mails um WHERE um.to = u.id AND DAY(um.ts) = DAY(NOW()) ) as nlEmailCount, (SELECT up.thumb FROM users_photos up WHERE up.u_id = u.id and up.profile = 1 ORDER BY up.id ASC LIMIT 1) as thumbnail , (SELECT MAX(s.id) FROM sales s  WHERE  (s.u_id = u.id and gateway='stripe' and s.payment_status='paid' AND s.amount>0) OR (s.u_id = u.id  and s.amount>0 and s.gateway!='stripe') ) AS saleId FROM newsletter_target_users_react ntu JOIN users u ON u.id = ntu.user_id WHERE ntu.newsletter_id = '".intval($_REQUEST['filter_usersOfNl'])."' AND ntu.`free_coins_claimed` =0 GROUP BY ntu.user_id");
            
          }else{
            
            $sqlnl="SELECT  ntu.fake_user_id as r_id,u.id,u.id as s_id,u.name,u.email,u.age,u.city,u.country,u.fake,u.admin,u.last_access,u.credits,u.premium,u.ip,u.online_day,u.verified,u.moderator,(select username from users where id =ntu.fake_user_id) as fakeusername  , (SELECT COUNT(um.id) FROM users_mails um WHERE um.to = u.id AND DAY(um.ts) = DAY(NOW()) ) as nlEmailCount, (SELECT up.thumb FROM users_photos up WHERE up.u_id = u.id and up.profile = 1 ORDER BY up.id ASC LIMIT 1) as thumbnail , (SELECT MAX(s.id) FROM sales s  WHERE  (s.u_id = u.id and gateway='stripe' and s.payment_status='paid' AND s.amount>0) OR (s.u_id = u.id  and s.amount>0 and s.gateway!='stripe') ) AS saleId  FROM newsletter_target_users_react ntu LEFT JOIN chat ch ON ch.newsletter_id = ntu.newsletter_id AND ntu.user_id = ch.r_id  JOIN users u ON u.id = ntu.user_id WHERE ntu.newsletter_id = '".intval($_REQUEST['filter_usersOfNl'])."' AND  ch.replied <> 1  GROUP BY ntu.user_id ";
            $nlsUsers = $mysqli->query($sqlnl);
          }
            
            $search = $nlsUsers->fetch_all(MYSQLI_ASSOC); 
        }else{ 
            
            $filter_two="";
            
            if(!empty($_POST['filter_user_intrest'])){
             $userIntrest=implode(",",$_POST['filter_user_intrest']);
            
            $sqluserIntrest="SELECT DISTINCT u_id FROM `users_interest` WHERE i_id IN($userIntrest)";
             $filter_two=" AND u.id IN( $sqluserIntrest ) ";
            }
          
          $sqlSearch="SELECT u.id,u.name,u.email,u.age,u.city,u.country,u.fake,u.admin,u.last_access,
          u.credits,u.premium,u.ip,u.online_day,u.verified,u.moderator,
          (SELECT COUNT(um.id) FROM users_mails um WHERE um.to = u.id AND DAY(um.ts) = DAY(NOW()) ) as nlEmailCount,
          $filter_one
          (SELECT up.thumb FROM users_photos up WHERE up.u_id = u.id and up.profile = 1 ORDER BY up.id ASC LIMIT 1) as thumbnail , 
          (SELECT MAX(s.id) FROM sales s  WHERE  (s.u_id = u.id and gateway='stripe' and s.payment_status='paid' AND s.amount>0) OR (s.u_id = u.id  and s.amount>0 and s.gateway!='stripe')  ) AS saleId  FROM users u ".$filter." $filter_two ORDER BY u.".$order." DESC ";
          
           
        //ALTER FROM HERE FOR FILTERS
        $from_date_filter = '2025/01/01'; //just date in '2021/01/01' format enclosed with single quotes must
        $from_date_filter_time = strtotime($from_date_filter.'00:00:00');
        
        if(!empty($_POST['filter_no_reply']))
        $hours_age_filter = $_POST['filter_no_reply'];  
        else
           $hours_age_filter = 8;

        $sqlSearch= ' SELECT SQL_NO_CACHE  cmain.id, cmain.time, cmain.r_id, cmain.s_id,u.id,u.name,u.email,u.age,u.city,u.country,u.fake,u.admin,u.last_access,(select username from users where id =cmain.r_id) as fakeusername  ,
          u.credits,u.premium,u.ip,u.online_day,u.verified,u.moderator ,
          (SELECT up.thumb FROM users_photos up WHERE up.u_id = u.id and up.profile = 1 ORDER BY up.id ASC LIMIT 1) as thumbnail ,
           (SELECT COUNT(um.id) FROM users_mails um WHERE um.to = u.id AND DAY(um.ts) = DAY(NOW()) ) as nlEmailCount,
            MD5(CONCAT(cmain.s_id, "-", cmain.r_id)) AS unique_hash
          FROM chat cmain 
       JOIN users u ON u.id = cmain.s_id

WHERE cmain.id IN ( 
                    SELECT MAX(c.id) FROM users u JOIN chat c on u.id = c.s_id WHERE u.fake = 0  '.$filter.' '.$filter_two  .'AND c.fake =1 GROUP BY c.s_id, c.r_id 
                    ) AND cmain.time > '.$from_date_filter_time.'  AND cmain.time  < UNIX_TIMESTAMP(NOW() - INTERVAL '.$hours_age_filter.' HOUR)
    AND ( SELECT count(*) FROM chat cp WHERE cp.r_id = cmain.s_id and cp.s_id = cmain.r_id AND cp.id > cmain.id and cp.time > UNIX_TIMESTAMP(NOW() - INTERVAL '.$hours_age_filter.' HOUR) ) = 0 
    AND ( SELECT count(*) FROM reports_chats rc WHERE rc.user_to = cmain.r_id and rc.user_from = cmain.s_id AND rc.viewed=0 ) = 0 ' .
    $extra_welcome_mod_sql .'
    AND ( SELECT count(*) FROM chat cp WHERE cp.r_id = cmain.s_id and cp.s_id = cmain.r_id AND cp.id > cmain.id and cp.modusername IS NOT NULL ) = '.$max_number_of_moderator_messages_per_chat.'  '.'AND(
     SELECT COUNT(*)
    FROM newsletter_target_users_react nr
    WHERE nr.fake_user_id = cmain.r_id
        AND nr.user_id = cmain.s_id
        AND nr.execution_status =0
)=0   AND(
    SELECT COUNT(*)
FROM newsletter_target_users_react nr
WHERE 
     nr.user_id = cmain.s_id
    AND nr.execute_by >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
)=0  ';
                        


            $nlAllUsers = $mysqli->query($sqlSearch);
            $search = $nlAllUsers->fetch_all(MYSQLI_ASSOC); 
		  //$search = getSelectedArray('id,name,email,age,city,country,fake,admin,last_access,credits,premium,ip,online_day,verified,moderator','users',$filter,$order.' desc','');
        }

		$i=0;
       //
		if(isset($search)){
			foreach ($search as $value) { 
            
  
            if($notpaid=='on'){
                if ( $value['saleId'] > 0 )
                {
                if($is_paid=='off')
                 continue;
                }
            }
            
            if($is_paid=='on'){
                if ( $value['saleId'] <= 0 )
                {
               if($notpaid=='off')     
                 continue;
                }
            }
                
            
             // if(!empty($_POST['filter_no_reply'])){
                
               // if(intval($value['reply_chat_time'] ) <= 0)
                 //  continue;
            //}
                
            if($withProfilePicture == 'on'){
			     if(empty($value['thumbnail'])){
                     continue;
                 }
		      } 
            if($withProfilePicture == 'off'){
			     if(!empty($value['thumbnail'])){
                     continue;
                 }
		      }
				
				$today = date('w');
				$onlineNow = '';
				$userPremium = 'No';
				$ip = $value['ip'];
				if($value['last_access'] >= $time || $value['fake'] == 1 && $value['online_day'] == $today){
					$onlineNow = 'avatar-online lg';
				}
								
				if($value['premium'] == 1){
					$userPremium = 'Yes';
				}


				if(!empty($value['thumbnail'])){
				$photo = $value['thumbnail'];
				}else{
				   $photo =  $sm['config']['theme_url'].'/images/no_user.png';
				}
				
				$banEmail = "'".'email,'.$value['email'].','.$value['ip']."'";
				$banIP = "'".'ip,'.$value['ip']."'";	


				$onClickStories = '';
				$storyEl = "'.asd'";

				$badge =intval($value['nlEmailCount']);

                $data[$i]= array(
                                    'userId' => $value['id'],
                                    'md5'=>md5($value['s_id']),
                                    'c_rid' => $value['c_rid'],
                                    'fake_user_id'=> $value['r_id'],
                                      'fakeusername'=> $value['fakeusername'],
                                    'userName' => $value['name'],
                                    'userEmail' => $value['email'],
                                    'userAge' => $value['age'],
                                    'userCity' => $value['city'],
                                    'userCountry' => $value['country'],
                                    'userCredits' => $value['credits'],
                                    'userPremium' => $userPremium,
                                    'userPhoto' => $photo,
                                    'userVerified' => intval($value['verified']),
                                    'userStatusClass' => $onlineNow,
                                    'userLastAccess' => time_elapsed_string($value['last_access']),
                                    'checkStory' => $checkStory,
                                    'storyEl' => $storyEl,
                                    'siteUrl' => $sm['config']['site_url'],
                                    'newsletterMailCount' => intval($value['nlEmailCount']),
                                    'newsletterMailBadge' => $badge,
                                    );

			      $i++;
			}
		}
     $my_array = remove_duplicateKeys("md5",$data);

		$arr['data'] = $my_array;
		$arr['total'] = count($my_array);
       // ob_start('ob_gzhandler');
		echo json_encode($arr);
	break;

	case 'json_search_users_newsletter':
	    //$exeStart = microtime(true);
//	error_reporting(E_ALL);
//ini_set("display_errrors","On");
		$data = array();
		$arr=array();
		$time = time()-300;

		$gender = secureEncode($_POST['gender']);
		$age1 = secureEncode($_POST['age1']);
		$age2 = secureEncode($_POST['age2']);
		$order = secureEncode($_POST['order']);
		$date = secureEncode($_POST['date']);
		$dateEnabled = secureEncode($_POST['dateEnabled']);
		$fake = secureEncode($_POST['fake']);
		$real = secureEncode($_POST['realUser']);
		$premium = secureEncode($_POST['premium']);
		$online = secureEncode($_POST['online']);
		$searchInput = secureEncode($_POST['search']);
		$verified = secureEncode($_POST['verified']);
		$withStory = secureEncode($_POST['withStory']);
		$withProfilePicture = secureEncode($_POST['withProfilePicture']);
		
        $notpaid = secureEncode($_POST['notpaid']);
        $is_paid = secureEncode($_POST['is_paid']);



		$country = secureEncode($_POST['country']);
		$city = secureEncode($_POST['city']);
		$date = str_replace(' ', '', $date);
		$date = explode('to',$date);
		$date1 = $date[0];
		$date2 = $date[1];
		$filter = 'WHERE u.age BETWEEN '.$age1.' AND '.$age2;

		if($searchInput != ''){
			$filter.=" AND u.id = '".$searchInput."' OR u.name LIKE '%$searchInput%' OR u.email LIKE '%$searchInput%' OR u.ip LIKE '%$searchInput%'";
		}
		if($gender != 'all'){
			$filter.=' AND u.gender ='.$gender;
		}
		if($online == 'on'){
			$filter.=' AND u.last_access >='.$time;
		}
		if($premium == 'on'){
			$filter.=' AND u.premium = 1';
		}
		if($verified == 'on'){
			$filter.=' AND u.verified = 1';
		}
				
		if($fake == 'on' || $real == 'on' || $fake == 'off' || $real == 'off'){
			if($fake == 'on' && $real == 'on'){
				$filter.=' AND  (u.fake = 0 || u.fake=1) ';
			} else if($fake == 'off' && $real == 'on'){
				$filter.=' AND u.fake = 0';
			} else if($fake == 'on' && $real == 'off'){
				$filter.=' AND u.fake=1';
			} else {
				$filter.=' AND u.fake = -1';
			}	
		}		
		if($dateEnabled == 'on'){
		//	$filter.=' AND join_date BETWEEN "'.$date1.'" AND "'.$date2.'"';
					$filter.=" AND str_to_date(u.join_date, '%m/%d/%Y') BETWEEN    STR_TO_DATE('$date1','%m/%d/%Y') AND  STR_TO_DATE( '$date2' ,'%m/%d/%Y')  ";       




		}

		if($country != 'all'){
			$filter.=' AND u.country = "'.$country.'"';
		}

		if($city != 'all'){
			$filter.=' AND u.city = "'.$city.'"';
		}							

		//if($sm['user']['admin'] == 2){
			$filter.=' AND u.admin = 0';
		//}
		
        if($_POST['filter_last_online'] == '6'){
    		$lastseen = strtotime('-180 days');
    		$filter .= ' AND  CAST(u.last_access AS SIGNED)  > '.$lastseen.'';
    	}else if($_POST['filter_last_online'] == '3'){
    		$lastseen = strtotime('-90 days');
    		$filter .= ' AND  CAST(u.last_access AS SIGNED)  > '.$lastseen.'';
    	}else if($_POST['filter_last_online'] == '1'){
    		$lastseen = strtotime('-30 days');
    		$filter .= ' AND  CAST(u.last_access AS SIGNED)  > '.$lastseen.'';
    	}else if($_POST['filter_last_online'] == 'l7'){
    		$lastseen = strtotime('-7 days');
    		$filter .= ' AND  CAST(u.last_access AS SIGNED)  > '.$lastseen.'';
    	}    else if($_POST['filter_last_online'] == 'A'){
		
    		$filter .= ' AND  CAST(u.last_access AS SIGNED) >= UNIX_TIMESTAMP(NOW() - INTERVAL 12 MONTH) AND CAST(u.last_access AS SIGNED) < UNIX_TIMESTAMP(NOW() - INTERVAL 6 MONTH) ';
    	}else if($_POST['filter_last_online'] == 'B'){
    	
    		$filter .= ' AND     CAST(u.last_access AS SIGNED) >= UNIX_TIMESTAMP(NOW() - INTERVAL 18 MONTH) AND CAST(u.last_access AS SIGNED) < UNIX_TIMESTAMP(NOW() - INTERVAL 12 MONTH) ';
    	}else if($_POST['filter_last_online'] == 'C'){
    	
    		$filter .= ' AND     CAST(u.last_access AS SIGNED) >= UNIX_TIMESTAMP(NOW() - INTERVAL 24 MONTH) AND CAST(u.last_access AS SIGNED) < UNIX_TIMESTAMP(NOW() - INTERVAL 18 MONTH)';
    	}else if($_POST['filter_last_online'] == 'D'){
    	
    		$filter .= '  AND    CAST(u.last_access AS SIGNED) >= UNIX_TIMESTAMP(NOW() - INTERVAL 36 MONTH) AND CAST(u.last_access AS SIGNED) < UNIX_TIMESTAMP(NOW() - INTERVAL 24 MONTH) ';
    	}
    	else if($_POST['filter_last_online'] == 'E'){
    		
    		$filter .= ' AND     CAST(u.last_access AS SIGNED) >= UNIX_TIMESTAMP(NOW() - INTERVAL 42 MONTH) AND CAST(u.last_access AS SIGNED) < UNIX_TIMESTAMP(NOW() - INTERVAL 36 MONTH) ';
    	}
    	else if($_POST['filter_last_online'] == 'F'){
    		
    		$filter .= ' AND     CAST(u.last_access AS SIGNED) >= UNIX_TIMESTAMP(NOW() - INTERVAL 48 MONTH) AND CAST(u.last_access AS SIGNED) < UNIX_TIMESTAMP(NOW() - INTERVAL 42 MONTH) ';
    	}
    	else if($_POST['filter_last_online'] == 'G'){
    		
    		$filter .= ' AND     CAST(u.last_access AS SIGNED) >= UNIX_TIMESTAMP(NOW() - INTERVAL 54 MONTH) AND CAST(u.last_access AS SIGNED) < UNIX_TIMESTAMP(NOW() - INTERVAL 48 MONTH) ';
    	}else if($_POST['filter_last_online'] == 'H'){
           
    		$filter .= ' AND     CAST(u.last_access AS SIGNED) >= UNIX_TIMESTAMP(NOW() - INTERVAL 60 MONTH) AND CAST(u.last_access AS SIGNED) < UNIX_TIMESTAMP(NOW() - INTERVAL 54 MONTH) ';

    	}
    	
    	
        $filter_one='';
      if(!empty($_POST['filter_fake_msg_count'])){
          $filter_fake_msg_count=intval($_POST['filter_fake_msg_count']);
          $filter_one .= "  (SELECT c.r_id as c_rid FROM chat c WHERE c.s_id= u.id AND c.fake=1 GROUP BY c.s_id,c.r_id HAVING count(c.id) >=$filter_fake_msg_count
          ORDER BY count(c.id) DESC LIMIT 1 ) as c_rid, ";
      }


      if(!empty($_POST['filter_no_reply'])){
              $filter_one='';
          $filter_no_reply_days=intval($_POST['filter_no_reply']);
          $today              = strtotime("today 12:00");
          $filter_no_reply_day_timestamp          = strtotime('-'.$filter_no_reply_days.' day', $today);
          $filter_one .= " (SELECT c.time FROM chat c WHERE
          c.r_id=u.id AND c.fake=0 GROUP BY c.s_id, c.r_id HAVING
           CAST(c.time AS SIGNED)>=$filter_no_reply_day_timestamp ORDER BY c.time DESC LIMIT 1) as reply_chat_time,
                    ";
      }



        $search = array();
        if(!empty($_REQUEST['filter_usersOfNl']) ){
            $table_append='';
            
            if(intval($_REQUEST['filter_usersOfNl']) >= 1073741823)
              $table_append='_react';
            
          if(!empty($_REQUEST['filter_usersOfFl']) ){
            $nlsUsers = $mysqli->query("SELECT u.id,u.name,u.email,u.age,u.city,u.country,u.fake,u.admin,u.last_access,u.credits,u.premium,u.ip,u.online_day,u.verified,u.moderator, (SELECT COUNT(um.id) FROM users_mails um WHERE um.to = u.id AND DAY(um.ts) = DAY(NOW()) ) as nlEmailCount, (SELECT up.thumb FROM users_photos up WHERE up.u_id = u.id and up.profile = 1 ORDER BY up.id ASC LIMIT 1) as thumbnail , (SELECT MAX(s.id) FROM sales s  WHERE  (s.u_id = u.id and gateway='stripe' and s.payment_status='paid' AND s.amount>0) OR (s.u_id = u.id  and s.amount>0 and s.gateway!='stripe') ) AS saleId FROM newsletter_target_users$table_append ntu JOIN users u ON u.id = ntu.user_id WHERE ntu.newsletter_id = '".intval($_REQUEST['filter_usersOfNl'])."' AND ntu.`free_coins_claimed` =0 GROUP BY ntu.user_id");
            
          }else{
              
              
         
            
            $nlsUsers = $mysqli->query("SELECT u.id,u.name,u.email,u.age,u.city,u.country,u.fake,u.admin,u.last_access,u.credits,u.premium,u.ip,u.online_day,u.verified,u.moderator, (SELECT COUNT(um.id) FROM users_mails um WHERE um.to = u.id AND DAY(um.ts) = DAY(NOW()) ) as nlEmailCount, (SELECT up.thumb FROM users_photos up WHERE up.u_id = u.id and up.profile = 1 ORDER BY up.id ASC LIMIT 1) as thumbnail , (SELECT MAX(s.id) FROM sales s  WHERE  (s.u_id = u.id and gateway='stripe' and s.payment_status='paid' AND s.amount>0) OR (s.u_id = u.id  and s.amount>0 and s.gateway!='stripe') ) AS saleId  FROM newsletter_target_users$table_append ntu LEFT JOIN chat ch ON ch.newsletter_id = ntu.newsletter_id AND ntu.user_id = ch.r_id  JOIN users u ON u.id = ntu.user_id WHERE ntu.newsletter_id = '".intval($_REQUEST['filter_usersOfNl'])."' AND  ch.replied <> 1  GROUP BY ntu.user_id ");
          }
            
            $search = $nlsUsers->fetch_all(MYSQLI_ASSOC); 
        }else{ 

			 $filter_two="";
            
            if(!empty($_POST['filter_user_intrest'])){
             $userIntrest=implode(",",$_POST['filter_user_intrest']);
            
            $sqluserIntrest="SELECT DISTINCT u_id FROM `users_interest` WHERE i_id IN($userIntrest)";
             $filter_two=" AND u.id IN( $sqluserIntrest ) ";
            }
            
             $filter_micro="";
            
            if(!empty($_POST['is_micro']) && $_POST['is_micro']=='on' ){
            
            $sqluserMicro="SELECT DISTINCT uid  FROM `micropayment`  WHERE complete=1 ";
             $filter_micro=" AND u.id IN( $sqluserMicro ) ";
            }
            
            
            
            

          
          $sqlSearch="SELECT u.join_date_time,u.id,u.name,u.email,u.age,u.city,u.country,u.fake,u.admin,u.last_access,
          u.credits,u.premium,u.ip,u.online_day,u.verified,u.moderator,
          (SELECT COUNT(um.id) FROM users_mails um WHERE um.to = u.id AND DAY(um.ts) = DAY(NOW()) ) as nlEmailCount,
          $filter_one
          (SELECT up.thumb FROM users_photos up WHERE up.u_id = u.id and up.profile = 1 ORDER BY up.id ASC LIMIT 1) as thumbnail , 
          (SELECT MAX(s.id) FROM sales s  WHERE  (s.u_id = u.id and gateway='stripe' and s.payment_status='paid' AND s.amount>0) OR (s.u_id = u.id  and s.amount>0 and s.gateway!='stripe') ) AS saleId FROM users u ".$filter." $filter_two $filter_micro ORDER BY u.".$order." DESC ";
        
     //   echo $sqlSearch;die;
            $nlAllUsers = $mysqli->query($sqlSearch);
            $search = $nlAllUsers->fetch_all(MYSQLI_ASSOC); 
		  //$search = getSelectedArray('id,name,email,age,city,country,fake,admin,last_access,credits,premium,ip,online_day,verified,moderator','users',$filter,$order.' desc','');
        }

		$i=0;
		$micropayment_start_date1=strtotime($micropayment_start_date);
	//	$micropayment_test_mode_check=false;
	
	
       //
		if(isset($search)){
			foreach ($search as $value) { 
           
         
            if($notpaid=='on'){
                if ( $value['saleId'] > 0 )
                {
                if($is_paid=='off')
                 continue;
                }
            }
            
            if($is_paid=='on'){
                if ( $value['saleId'] <= 0 )
                {
               if($notpaid=='off')     
                 continue;
                }
            }
              
             
            	if($micropayment_test_mode){
            	   
                		    if(strtotime($value['join_date_time'])> $micropayment_start_date1)
                		    {
                		       if ( $value['saleId'] <= 0 )
                            	          continue; // if user registered after test mode is on and no sale skip that user
                		      }
		          	}
            
            if(!empty($_POST['filter_fake_msg_count'])){
                
                if(intval($value['c_rid'] ) <= 0)
                   continue;
            }
            
              if(!empty($_POST['filter_no_reply'])){
                
                if(intval($value['reply_chat_time'] ) <= 0)
                   continue;
            }
                
            if($withProfilePicture == 'on'){
			     if(empty($value['thumbnail'])){
                     continue;
                 }
		      } 
            if($withProfilePicture == 'off'){
			     if(!empty($value['thumbnail'])){
                     continue;
                 }
		      }
				
				$today = date('w');
				$onlineNow = '';
				$userPremium = 'No';
				$ip = $value['ip'];
				if($value['last_access'] >= $time || $value['fake'] == 1 && $value['online_day'] == $today){
					$onlineNow = 'avatar-online lg';
				}
								
				if($value['premium'] == 1){
					$userPremium = 'Yes';
				}


				if(!empty($value['thumbnail'])){
				$photo = $value['thumbnail'];
				}else{
				   $photo =  $sm['config']['theme_url'].'/images/no_user.png';
				}
				
				$banEmail = "'".'email,'.$value['email'].','.$value['ip']."'";
				$banIP = "'".'ip,'.$value['ip']."'";	


				$onClickStories = '';
				$storyEl = "'.asd'";

				
				$badge =intval($value['nlEmailCount']);

                $data[$i]= array(
                                    'userId' => $value['id'],
                                    'c_rid' => $value['c_rid'],
                                    'userName' => $value['name'],
                                    'userEmail' => $value['email'],
                                    'userAge' => $value['age'],
                                    'userCity' => $value['city'],
                                    'userCountry' => $value['country'],
                                    'userCredits' => $value['credits'],
                                    'userPremium' => $userPremium,
                                    'userPhoto' => $photo,
                                    'userVerified' => intval($value['verified']),
                                    'userStatusClass' => $onlineNow,
                                    'userLastAccess' => time_elapsed_string($value['last_access']),
                                    'checkStory' => $checkStory,
                                    'storyEl' => $storyEl,
                                    'siteUrl' => $sm['config']['site_url'],
                                    'newsletterMailCount' => intval($value['nlEmailCount']),
                                    'newsletterMailBadge' => $badge,
                                    );

			      $i++;
			}
		}

		$arr['data'] = $data;
		$arr['total'] = $i;
       // ob_start('ob_gzhandler');
		echo json_encode($arr);
	break;
	case 'search_users_newsletter':
		$data = array();
		$arr=array();
		$time = time()-300;

		$gender = secureEncode($_POST['gender']);
		$age1 = secureEncode($_POST['age1']);
		$age2 = secureEncode($_POST['age2']);
		$order = secureEncode($_POST['order']);
		$date = secureEncode($_POST['date']);
		$dateEnabled = secureEncode($_POST['dateEnabled']);
		$fake = secureEncode($_POST['fake']);
		$real = secureEncode($_POST['realUser']);
		$premium = secureEncode($_POST['premium']);
		$online = secureEncode($_POST['online']);
		$searchInput = secureEncode($_POST['search']);
		$verified = secureEncode($_POST['verified']);
		$withStory = secureEncode($_POST['withStory']);
		$withProfilePicture = secureEncode($_POST['withProfilePicture']);
		
				$notpaid = secureEncode($_POST['notpaid']);



		$country = secureEncode($_POST['country']);
		$city = secureEncode($_POST['city']);
		$date = str_replace(' ', '', $date);
		$date = explode('to',$date);
		$date1 = $date[0];
		$date2 = $date[1];
		$filter = 'WHERE u.age BETWEEN '.$age1.' AND '.$age2;

		if($searchInput != ''){
			$filter.=" AND u.id = '".$searchInput."' OR u.name LIKE '%$searchInput%' OR u.email LIKE '%$searchInput%' OR u.ip LIKE '%$searchInput%'";
		}
		if($gender != 'all'){
			$filter.=' AND u.gender ='.$gender;
		}
		if($online == 'on'){
			$filter.=' AND u.last_access >='.$time;
		}
		if($premium == 'on'){
			$filter.=' AND u.premium = 1';
		}
		if($verified == 'on'){
			$filter.=' AND u.verified = 1';
		}
		if($withProfilePicture == 'on'){
			$filter.=' AND up.photo <> \'\' ';
		}		
		if($fake == 'on' || $real == 'on' || $fake == 'off' || $real == 'off'){
			if($fake == 'on' && $real == 'on'){
				$filter.=' AND  (u.fake = 0 || u.fake=1)';
			} else if($fake == 'off' && $real == 'on'){
				$filter.=' AND u.fake = 0';
			} else if($fake == 'on' && $real == 'off'){
				$filter.=' AND u.fake=1';
			} else {
				$filter.=' AND u.fake = -1';
			}	
		}		
		if($dateEnabled == 'on'){
		//	$filter.=' AND join_date BETWEEN "'.$date1.'" AND "'.$date2.'"';
					$filter.=" AND str_to_date(u.join_date, '%m/%d/%Y') BETWEEN    STR_TO_DATE('$date1','%m/%d/%Y') AND  STR_TO_DATE( '$date2' ,'%m/%d/%Y')  ";       




		}

		if($country != 'all'){
			$filter.=' AND u.country = "'.$country.'"';
		}

		if($city != 'all'){
			$filter.=' AND u.city = "'.$city.'"';
		}							

		//if($sm['user']['admin'] == 2){
			$filter.=' AND u.admin = 0';
		//}

        $search = array();
        if(!empty($_REQUEST['filter_usersOfNl']) ){
            $nlsUsers = $mysqli->query("SELECT u.id,u.name,u.email,u.age,u.city,u.country,u.fake,u.admin,u.last_access,u.credits,u.premium,u.ip,u.online_day,u.verified,u.moderator FROM newsletter_target_users ntu JOIN users u ON u.id = ntu.user_id WHERE ntu.newsletter_id = '".intval($_REQUEST['filter_usersOfNl'])."' ");
            $search = $nlsUsers->fetch_all(MYSQLI_ASSOC); 
        }else{ 
            $nlAllUsers = $mysqli->query("SELECT u.id,u.name,u.email,u.age,u.city,u.country,u.fake,u.admin,u.last_access,u.credits,u.premium,u.ip,u.online_day,u.verified,u.moderator FROM users u ".$filter." ORDER BY u.".$order." DESC ");
            $search = $nlAllUsers->fetch_all(MYSQLI_ASSOC); 
		  //$search = getSelectedArray('id,name,email,age,city,country,fake,admin,last_access,credits,premium,ip,online_day,verified,moderator','users',$filter,$order.' desc','');
        }
		$nlmails = getSelectedArray('id,name,email,age,city,country,fake,admin,last_access,credits,premium,ip,online_day,verified,moderator','users_mails',$filter,$order.' desc','');
		$nlmails = array();
		$query = $mysqli->query("SELECT umail.to AS id, users.name AS name, COUNT(*) AS num FROM users_mails umail INNER JOIN users ON users.id = umail.to WHERE DAY(umail.ts) = DAY(NOW()) GROUP BY umail.to");
		if ($query->num_rows > 0) { 
			while($re = $query->fetch_object()){ 
  			$nlmails[$re->id] = $re->num;
  		}
		}
		
        $paidUsers=array();
        $queryp = $mysqli->query("SELECT distinct u_id from sales s  WHERE  (gateway='stripe' and s.payment_status='paid' AND s.amount>0) OR (s.amount>0 and s.gateway!='stripe');");
        if ($queryp->num_rows > 0) {
        while($rep = $queryp->fetch_object()){
        $paidUsers[$rep->u_id] = $rep->u_id;
        }
        }
		
		$i=0;
		if(isset($search)){
			foreach ($search as $value) { 
            
          /*  if($notpaid=='on'){
                if (in_array($value['id'], $paidUsers))
                {
                continue;
                }
            }
            
            */
                
         
            if($notpaid=='on'){
                if (in_array($value['id'], $paidUsers) )
                {
                if($is_paid=='off')
                 continue;
                }
            }
            
            if($is_paid=='on'){
                if (!in_array($value['id'], $paidUsers) )
                {
               if($notpaid=='off')     
                 continue;
                }
            }
              
            
            
            
            
				
				$today = date('w');
				$onlineNow = '';
				$userPremium = 'No';
				$ip = $value['ip'];
				if($value['last_access'] >= $time || $value['fake'] == 1 && $value['online_day'] == $today){
					$onlineNow = 'avatar-online lg';
				}
				if($value['fake'] == 1){
					$badge = '<span class="badge badge-info">Fake user</span>';
					$ip = 'ip.fake.user';
				} else if($value['fake'] == 2){
					$badge = '<span class="badge badge-info">Dummy Fake user</span>';
					$ip = 'ip.fake.user';
				}else {
					$badge = '<span class="badge badge-success">User</span>';
				}

				if($value['admin'] == 1){
					$badge = '<span class="badge badge-warning">ADMIN</span>';
				}
				if($value['admin'] == 2){
					$badge = '<span class="badge badge-dark">'.$value['moderator'].'</span>';
				}				
				if($value['premium'] == 1){
					$userPremium = 'Yes';
				}

				$userVerified = '';
				if($value['verified'] == 1){
					$userVerified = '<i class="material-icons" style="color:#1CC5F7;font-size:13px">verified_user</i>';
				}

				$photo = profilePhoto($value['id']);
				
				$banEmail = "'".'email,'.$value['email'].','.$value['ip']."'";
				$banIP = "'".'ip,'.$value['ip']."'";	


				$storyFrom = $sm['plugins']['story']['days'];
				$time = time();	
				$extra = 86400 * $storyFrom;
				$storyFrom = $time - $extra;
				$storiesFilter = 'where uid = '.$value['id'].' and storyTime >'.$storyFrom.' and deleted = 0';

				$checkStory = selectC('users_story',$storiesFilter);
				$userStories = json_encode(getUserStories($value['name'],$photo,$storiesFilter,'storyTime ASC'),JSON_UNESCAPED_UNICODE);

				$onClickStories = '';
				$storyEl = "'.asd'";
				$storyBorder = '';
				if($checkStory > 0){
					$storyBorder = 'style="border:3px solid #7F17A9;cursor:pointer"';
					$onClickStories = 'onclick="openStoryDiscover('.$storyEl.','.$value['id'].',true);" ';
				}

				if($withStory == 'on'){
					if($checkStory == 0){
						continue;
					}
				}
				
				$badge = isset($nlmails[$value['id']]) ? $nlmails[$value['id']] : 0;

				$goToEditMediaUser = "goTo('mediaPhotos','All',".$value['id'].")";
				$data[$i]='
			      <tr>
			          <td>
			              <div class="custom-control custom-checkbox">
			                  <input type="checkbox" onclick="checkUser(this,'.$value['id'].','.$photo.')" class="custom-control-input" data-check-user="'.$value['id'].'" data-check-user-photo="'.$photo.'" id="checkuser_'.$value['id'].'">
			                  <label class="custom-control-label" style="cursor: pointer;" for="checkuser_'.$value['id'].'">
			                  <span class="text-hide">Check</span></label>
			              </div>
			          </td>

			          <td style="max-width:230px;overflow-x:hidden">
			              <div class="media align-items-center" >
			                  <div class="avatar avatar-md mr-3 '.$onlineNow.'" '.$onClickStories.'  style="width:55px;height:55px">
			                      <img src="'.$photo.'" class="avatar-img rounded-circle avatar-online box-shadow" '.$storyBorder.'>
			                  </div>
			                  <div class="media-body">
			                      <strong class="js-lists-values-employee-name"><a href="'.$sm['config']['site_url'].'index.php?page=admin&p=user&id='.$value['id'].'"  style="color:#333">'.$value['name'].' ,'.$value['age'].' '.$userVerified.'</a></strong><br>
			                      <span class="text-muted js-lists-values-employee-title">
			                      ID: '.$value['id'].'</span><br>
			                      <span class="text-muted js-lists-values-employee-title">'.$value['email'].'</span>			                      
			                  </div>
			              </div>
			          </td>
			          <td style="min-width:150px;">
			              <div class="media align-items-center">
			                  <div class="media-body">
			                      <strong>'.$value['city'].'</strong>
			                      <br>
			                      <span class="text-muted">'.$value['country'].'</span>
			                  </div>
			              </div>
			          </td>                                                    
			          <td data-totalm="' . count($nlmails) . '">'.$badge.'</td>
			          <td style="min-width:130px;"><small class="text-muted">'.time_elapsed_string($value['last_access']).'</small></td>
			          <td>'.$value['credits'].'</td>
			          <td><strong>'.$userPremium.'</strong></td>                          
			          <td>
			              <div class="dropdown ml-auto" data-table-dropdown>
			                  <a href="#" data-toggle="dropdown" data-caret="false" class="btn btn-light text-muted"><i class="material-icons">more_vert</i></a>
			                  <div class="dropdown-menu dropdown-menu-right">
			                      <a class="dropdown-item" style="font-size: 13px" target="_blank" href="'.$sm['config']['site_url'].'@'.$value['id'].'">Open live profile</a>
			                      <div class="dropdown-divider"></div>
			                      <a class="dropdown-item" style="font-size: 13px" href="javascript:;"
			                      onclick="newsletter(\'random\', '.$value['id'].')">Send random</a>
			                      <a class="dropdown-item" style="font-size: 13px" href="javascript:;"
			                      onclick="newsletter(\'like\', '.$value['id'].')">Send Like</a>
			                      <a class="dropdown-item" style="font-size: 13px" href="javascript:;"
			                      onclick="newsletter(\'match\', '.$value['id'].')">Send Match</a>
			                      <a class="dropdown-item" style="font-size: 13px" href="javascript:;"
			                      onclick="newsletter(\'view\', '.$value['id'].')">Send View</a>
			                                         
			                  </div>
			              </div>
			          </td>
			      </tr>';

			      $i++;
			}
		} else {
			$data = 'Nothing found';
		}

		$arr['data'] = $data;
		$arr['total'] = $i;
		echo json_encode($arr);
	break;

	case 'search_users_newsletter_fk':
		$data = array();
		$arr=array();
		$time = time()-300;

		$age1 = secureEncode($_POST['age1']);
		$age2 = secureEncode($_POST['age2']);
		$searchInput = secureEncode($_POST['search']);
		$country = secureEncode($_POST['country']);
		$city = secureEncode($_POST['city']);

		$filter = 'WHERE age BETWEEN '.$age1.' AND '.$age2;

		if($searchInput != ''){
			$filter.=" AND id = '".$searchInput."' OR name LIKE '%$searchInput%' OR email LIKE '%$searchInput%' OR ip LIKE '%$searchInput%'";
		}

		$filter.=' AND fake = 1';

		if($country != 'all'){
			$filter.=' AND country = "'.$country.'"';
		}

		if($city != 'all'){
			$filter.=' AND city = "'.$city.'"';
		}							

		$filter.=' AND admin = 0';

		$search = getSelectedArray('id,name,email,age,city,country,fake,admin,last_access,credits,premium,ip,online_day,verified,moderator','users',$filter, 'name ASC','');
		
		$nlmails = getSelectedArray('id,name,email,age,city,country,fake,admin,last_access,credits,premium,ip,online_day,verified,moderator','users_mails',$filter,$order.' desc','');
		$nlmails = array();
		$query = $mysqli->query("SELECT umail.to AS id, users.name AS name, COUNT(*) AS num FROM users_mails umail INNER JOIN users ON users.id = umail.to WHERE DAY(umail.ts) = DAY(NOW()) GROUP BY umail.to");
		if ($query->num_rows > 0) { 
			while($re = $query->fetch_object()){ 
  			$nlmails[$re->id] = $re->num;
  		}
		}
		
		$i=0;
		if(isset($search)){
			foreach ($search as $value) { 
				
				$today = date('w');
				$onlineNow = '';
				$userPremium = 'No';
				$ip = $value['ip'];
				if($value['last_access'] >= $time || $value['fake'] == 1 && $value['online_day'] == $today){
					$onlineNow = 'avatar-online lg';
				}
				if($value['fake'] == 1){
					$badge = '<span class="badge badge-info">Fake user</span>';
					$ip = 'ip.fake.user';
				}else if($value['fake'] == 2){
					$badge = '<span class="badge badge-info">Dummy Fake user</span>';
					$ip = 'ip.fake.user';
				} else {
					$badge = '<span class="badge badge-success">User</span>';
				}

				if($value['admin'] == 1){
					$badge = '<span class="badge badge-warning">ADMIN</span>';
				}
				if($value['admin'] == 2){
					$badge = '<span class="badge badge-dark">'.$value['moderator'].'</span>';
				}				
				if($value['premium'] == 1){
					$userPremium = 'Yes';
				}

				$userVerified = '';
				if($value['verified'] == 1){
					$userVerified = '<i class="material-icons" style="color:#1CC5F7;font-size:13px">verified_user</i>';
				}

				$photo = "'".profilePhoto($value['id'])."'";
				
				$banEmail = "'".'email,'.$value['email'].','.$value['ip']."'";
				$banIP = "'".'ip,'.$value['ip']."'";	


				$storyFrom = $sm['plugins']['story']['days'];
				$time = time();	
				$extra = 86400 * $storyFrom;
				$storyFrom = $time - $extra;
				$storiesFilter = 'where uid = '.$value['id'].' and storyTime >'.$storyFrom.' and deleted = 0';

				$checkStory = selectC('users_story',$storiesFilter);
				$userStories = json_encode(getUserStories($value['name'],profilePhoto($value['id']),$storiesFilter,'storyTime ASC'),JSON_UNESCAPED_UNICODE);

				$onClickStories = '';
				$storyEl = "'.asd'";
				$storyBorder = '';
				if($checkStory > 0){
					$storyBorder = 'style="border:3px solid #7F17A9;cursor:pointer"';
					$onClickStories = 'onclick="openStoryDiscover('.$storyEl.','.$value['id'].',true);" ';
				}

				if($withStory == 'on'){
					if($checkStory == 0){
						continue;
					}
				}
				
				$badge = isset($nlmails[$value['id']]) ? $nlmails[$value['id']] : 0;

				$goToEditMediaUser = "goTo('mediaPhotos','All',".$value['id'].")";
				$proPhoto = profilePhoto($value['id']);
				$data[$i]='
			      <tr>

			          <td style="max-width:230px;overflow-x:hidden">
			              <div class="media align-items-center" >
			                  <div class="avatar avatar-md mr-3 '.$onlineNow.'" '.$onClickStories.'  style="width:55px;height:55px">
			                      <img src="'.$proPhoto.'" class="avatar-img rounded-circle avatar-online box-shadow" '.$storyBorder.'>
			                  </div>
			                  <div class="media-body">
			                      <strong class="js-lists-values-employee-name"><a href="'.$sm['config']['site_url'].'index.php?page=admin&p=user&id='.$value['id'].'"  style="color:#333">'.$value['name'].' ,'.$value['age'].' '.$userVerified.'</a></strong><br>
			                      <span class="text-muted js-lists-values-employee-title">
			                      ID: '.$value['id'].'</span><br>
			                      <span class="text-muted js-lists-values-employee-title">'.$value['email'].'</span>			                      
			                  </div>
			              </div>
			          </td>
			          <td style="min-width:150px;">
			              <div class="media align-items-center">
			                  <div class="media-body">
			                      <strong>'.$value['city'].'</strong>
			                      <br>
			                      <span class="text-muted">'.$value['country'].'</span>
			                  </div>
			              </div>
			          </td>
			          <td style="min-width:130px;"><small class="text-muted">'.time_elapsed_string($value['last_access']).'</small></td>
			          <td>'.$value['credits'].'</td>
			          <td><strong>'.$userPremium.'</strong></td>                          
			          <td>
			              <div class="dropdown ml-auto" data-table-dropdown>
			                  <a href="#" data-toggle="dropdown" data-caret="false" class="btn btn-light text-muted"><i class="material-icons">more_vert</i></a>
			                  <div class="dropdown-menu dropdown-menu-right">
			                      <a class="dropdown-item" style="font-size: 13px" href="javascript:;"
			                      onclick="newsletterFk('.$value['id'].', \''.$value['name'].'\',\'' . $proPhoto . '\')">Send message from '.$value['name'].'</a>
			                  </div>
			              </div>
			          </td>
			      </tr>';

			      $i++;
			}
		} else {
			$data = 'Nothing found';
		}

		$arr['data'] = $data;
		$arr['total'] = $i;
		echo json_encode($arr);
	break;
		case 'json_search_email_verify_history':
		$data = array();
		$arr=array();
		$time = time()-300;


        $extraFilter = '';
        $_POST['userFilter'] = trim($_POST['userFilter']);
        if(!empty($_POST['userFilter'])){
            $extraFilter = " AND ( ip LIKE '%".$_POST['userFilter']."%' OR email LIKE '%".$_POST['userFilter']."%' ) ";
        }
        
        
        $_POST['dateFilter'] = trim($_POST['dateFilter']);
        $_POST['dateFilterToggle'] = trim($_POST['dateFilterToggle']);
        if($_POST['dateFilterToggle'] == 'on' && !empty($_POST['dateFilter'])){
            $dates = explode(' to ', $_POST['dateFilter']);
            $from = strtotime($dates[0] . ' 00:00:00');
            $till = strtotime($dates[1] . ' 23:59:59');
            
            $extraFilter.="  AND  (  time BETWEEN $from AND  $till)";
        }

            
        
        $payingUsers = $mysqli->query("SELECT * from email_verify_logs WHERE 1 ".$extraFilter." ORDER BY time DESC LIMIT 1000 ");
        $payingUserData = $payingUsers->fetch_all(MYSQLI_ASSOC); 
        
        $i=0;
		if(isset($payingUserData)){
			foreach ($payingUserData as $value) { 
			    
			    if($value['status']==0)
			     $value['status']='Rejected';
			     else
			      $value['status']='Approved';
			      
			      $data_json=json_decode($value['response'],true);
			      
			      $value['status']= $value['status'].'(' .$data_json['result'].')';

				$data[$i]='<tr style="background: #fff">
                              <td style="width: 200px;">'.$value['email'].'</td>
                              <td style="width: 100px;">'.$value['ip'].'</td>
                                <td style="width: 100px;">'.$value['status'].'</td>
                              <td style="width: 100px;">'.date("Y-m-d h:i", $value['time']).'</td>
                          </tr>';

			      $i++;
			}
		} else {
			$data = 'Nothing found';
		}

		$arr['data'] = $data;
		$arr['total'] = $i;
		echo json_encode($arr);
	break;
	
		
		case 'json_search_ip_check_history':
		$data = array();
		$arr=array();
		$time = time()-300;


        $extraFilter = '';
        $_POST['userFilter'] = trim($_POST['userFilter']);
        if(!empty($_POST['userFilter'])){
            $extraFilter = " AND ( ip LIKE '%".$_POST['userFilter']."%' OR email LIKE '%".$_POST['userFilter']."%' ) ";
        }
        
        
        $_POST['dateFilter'] = trim($_POST['dateFilter']);
        $_POST['dateFilterToggle'] = trim($_POST['dateFilterToggle']);
        if($_POST['dateFilterToggle'] == 'on' && !empty($_POST['dateFilter'])){
            $dates = explode(' to ', $_POST['dateFilter']);
            $from = strtotime($dates[0] . ' 00:00:00');
            $till = strtotime($dates[1] . ' 23:59:59');
            
            $extraFilter.="  AND  (  time BETWEEN $from AND  $till)";
        }

            
        
        $payingUsers = $mysqli->query("SELECT * from ip_check_logs WHERE 1 ".$extraFilter." ORDER BY time DESC LIMIT 1000 ");
        $payingUserData = $payingUsers->fetch_all(MYSQLI_ASSOC); 
        
        $i=0;
		if(isset($payingUserData)){
			foreach ($payingUserData as $value) { 
			    
		

				$data[$i]='<tr style="background: #fff">
                              <td style="width: 200px;">'.$value['email'].'</td>
                              <td style="width: 100px;">'.$value['ip'].'</td>
                              <td style="width: 100px;">'.date("Y-m-d h:i", $value['time']).'</td>
                          </tr>';

			      $i++;
			}
		} else {
			$data = 'Nothing found';
		}

		$arr['data'] = $data;
		$arr['total'] = $i;
		echo json_encode($arr);
	break;
	
	
	
	
	
	case 'json_search_userPayments':
		$data = array();
		$arr=array();
		$time = time()-300;


        $extraFilter = '';
        $_POST['userFilter'] = trim($_POST['userFilter']);
        if(!empty($_POST['userFilter'])){
            $extraFilter = " AND ( u.name LIKE '%".$_POST['userFilter']."%' OR u.email LIKE '%".$_POST['userFilter']."%' ) ";
        }
        
        
        $_POST['dateFilter'] = trim($_POST['dateFilter']);
        $_POST['dateFilterToggle'] = trim($_POST['dateFilterToggle']);
        if($_POST['dateFilterToggle'] == 'on' && !empty($_POST['dateFilter'])){
            $dates = explode(' to ', $_POST['dateFilter']);
            $from = strtotime($dates[0] . ' 00:00:00');
            $till = strtotime($dates[1] . ' 23:59:59');
            
            $extraFilter.="  AND  ( s.time BETWEEN $from AND  $till)";
        }

            
         
         
            
            
        
        $payingUsers = $mysqli->query("SELECT u.id,u.name,u.email, ROUND(SUM(CASE WHEN s.gateway = 'micropayment' THEN s.amount/100 ELSE s.amount END),2) as paidAmount, MIN(s.time) as firstPaid, MAX(s.time) as latestPaid FROM users u JOIN sales s ON s.u_id = u.id WHERE u.id > 0 ".$extraFilter." GROUP BY u.id ORDER BY paidAmount DESC LIMIT 1000 ");
        $payingUserData = $payingUsers->fetch_all(MYSQLI_ASSOC); 
        
        $i=0;
		if(isset($payingUserData)){
			foreach ($payingUserData as $value) { 

				$data[$i]='<tr style="background: #fff">
                              <td  style="width: 200px;">'.$value['name'].'</td>
                              <td style="width: 200px;">'.$value['email'].'</td>
                              <td style="width: 100px;">'.$value['paidAmount'].'</td>
                              <td style="width: 100px;">'.date("Y-m-d h:i", $value['firstPaid']).'</td>
                              <td style="width: 100px;">'.date("Y-m-d h:i", $value['latestPaid']).'</td>
                          </tr>';

			      $i++;
			}
		} else {
			$data = 'Nothing found';
		}

		$arr['data'] = $data;
		$arr['total'] = $i;
		echo json_encode($arr);
	break;
	case 'search_users':
		$data = array();
		$arr=array();
		$time = time()-300;

		$gender = secureEncode($_POST['gender']);
		$age1 = secureEncode($_POST['age1']);
		$age2 = secureEncode($_POST['age2']);
		$order = secureEncode($_POST['order']);
		$order_sort=' desc';
		if($order=='unverified'){
              $order='verified';
			$order_sort=' asc';
		}
		$date = secureEncode($_POST['date']);
		$dateEnabled = secureEncode($_POST['dateEnabled']);
		$fake = secureEncode($_POST['fake']);
		$real = secureEncode($_POST['realUser']);
		$premium = secureEncode($_POST['premium']);
		$online = secureEncode($_POST['online']);
		$searchInput = secureEncode($_POST['search']);
		$verified = secureEncode($_POST['verified']);
		$un_verified = secureEncode($_POST['unverified']);
		$withStory = secureEncode($_POST['withStory']);
		$reg_today = secureEncode($_POST['reg_today']);

		$country = secureEncode($_POST['country']);
		$city = secureEncode($_POST['city']);
		$date = str_replace(' ', '', $date);
		$date = explode('to',$date);
		$date1 = $date[0];
		$date2 = $date[1];
		 if($date2=='')
		    $date2 =$date1;
		    
		$filter = 'WHERE age BETWEEN '.$age1.' AND '.$age2;

		if($searchInput != ''){
			$filter.=" AND id = '".$searchInput."' OR name LIKE '%$searchInput%' OR email LIKE '%$searchInput%' OR ip LIKE '%$searchInput%'";
		}
		if($gender != 'all'){
			$filter.=' AND gender ='.$gender;
		}
		if($online == 'on'){
			$filter.=' AND last_access >='.$time;
		}
	if($reg_today == 'on'){
	    $reg_today_date=date("m/d/Y");
			$filter.=" AND	str_to_date(join_date, '%m/%d/%Y') BETWEEN    STR_TO_DATE('$reg_today_date','%m/%d/%Y') AND  STR_TO_DATE( '$reg_today_date' ,'%m/%d/%Y')   ";
		}
		if($premium == 'on'){
			//$filter.=' AND premium = 1';
			$filter.=' AND onboarding > 0 and clickid !=""  ';
		}
		if($verified == 'on'){
			$filter.=' AND verified = 1';
		}else if($un_verified == 'on'){
			$filter.=' AND verified = 0';
		}
		if($fake == 'on' || $real == 'on' || $fake == 'off' || $real == 'off'){
			if($fake == 'on' && $real == 'on'){
				$filter.=' AND fake >= 0';
			} else if($fake == 'off' && $real == 'on'){
				$filter.=' AND fake = 0';
			} else if($fake == 'on' && $real == 'off'){
				$filter.=' AND fake > 0';
			} else {
				$filter.=' AND fake = -1';
			}	
		}		
		if($dateEnabled == 'on'){
		//	$filter.=' AND join_date BETWEEN "'.$date1.'" AND "'.$date2.'"';
		
		
		$filter.=" AND	str_to_date(join_date, '%m/%d/%Y') BETWEEN    STR_TO_DATE('$date1','%m/%d/%Y') AND  STR_TO_DATE( '$date2' ,'%m/%d/%Y') ";
		
		}

		if($country != 'all'){
			$filter.=' AND country = "'.$country.'"';
		}

		if($city != 'all'){
			$filter.=' AND city = "'.$city.'"';
		}							

		if($sm['user']['admin'] == 2){
			$filter.=' AND admin = 0';
		}

       // echo $filter;die;
        $search = array();
        if(!empty($_REQUEST['filter_usersOfNl']) ){
            $nlsUsers = $mysqli->query("SELECT u.id,u.name,u.email,u.age,u.city,u.country,u.fake,u.admin,u.last_access,u.credits,u.premium,u.ip,u.online_day,u.verified,u.moderator FROM newsletter_target_users ntu JOIN users u ON u.id = ntu.user_id WHERE ntu.newsletter_id = '".intval($_REQUEST['filter_usersOfNl'])."' ");
            $search = $nlsUsers->fetch_all(MYSQLI_ASSOC); 
        }else{       
		  $search =getSelectedArray('referido,onboarding,clickid,id,name,email,age,city,country,fake,admin,last_access,credits,premium,ip,online_day,verified,moderator','users',$filter,$order.$order_sort,'');
        }
        $i=0;
		if(isset($search)){
			foreach ($search as $value) { 
				
				$today = date('w');
				$onlineNow = '';
				$userPremium = 'No';
				$ip = $value['ip'];
				if($value['last_access'] >= $time || $value['fake'] == 1 && $value['online_day'] == $today){
					$onlineNow = 'avatar-online lg';
				}
			
				if($value['fake'] == 1){
					$badge = '<span class="badge badge-info">Fake user</span>';
					$ip = 'ip.fake.user';
				}else if($value['fake'] == 2){
					$badge = '<span class="badge badge-info">Dummy Fake user</span>';
					$ip = 'ip.fake.user';
				}else {
					$badge = '<span class="badge badge-success">User</span>';
				}

				if($value['admin'] == 1){
					$badge = '<span class="badge badge-warning">ADMIN</span>';
				}
				if($value['admin'] == 2){
					$badge = '<span class="badge badge-dark">'.$value['moderator'].'</span>';
				}	
				
			if($value['onboarding'] >0 && $value['clickid']!='' ){
					$badge = '<span class="badge badge-dark">API USER</span>';
					$userPremium = 'Yes';
				}	
				
				if($value['premium'] == 1){
					//$userPremium = 'Yes';
				}
				
					if($value['referido']=='google_lander' ){
$badge = '<span class="badge badge-danger">Google</span>';
$userPremium = 'Yes';
				}	

				$userVerified = '';
				if($value['verified'] == 1){
					$userVerified = '<i class="material-icons" style="color:#1CC5F7;font-size:13px">verified_user</i>';
				}

				$photo = "'".profilePhoto($value['id'])."'";
				
				$banEmail = "'".'email,'.$value['email'].','.$value['ip']."'";
				$banIP = "'".'ip,'.$value['ip']."'";	


				$storyFrom = $sm['plugins']['story']['days'];
				$time = time();	
				$extra = 86400 * $storyFrom;
				$storyFrom = $time - $extra;
				$storiesFilter = 'where uid = '.$value['id'].' and storyTime >'.$storyFrom.' and deleted = 0';

				$checkStory = selectC('users_story',$storiesFilter);
				$userStories = json_encode(getUserStories($value['name'],profilePhoto($value['id']),$storiesFilter,'storyTime ASC'),JSON_UNESCAPED_UNICODE);

				$onClickStories = '';
				$storyEl = "'.asd'";
				$storyBorder = '';
				if($checkStory > 0){
					$storyBorder = 'style="border:3px solid #7F17A9;cursor:pointer"';
					$onClickStories = 'onclick="openStoryDiscover('.$storyEl.','.$value['id'].',true);" ';
				}

				if($withStory == 'on'){
					if($checkStory == 0){
						continue;
					}
				}

				$goToEditMediaUser = "goTo('mediaPhotos','All',".$value['id'].")";
				$data[$i]='
			      <tr>
			          <td>
			              <div class="custom-control custom-checkbox">
			                  <input type="checkbox" onclick="checkUser(this,'.$value['id'].','.$photo.')" class="custom-control-input" data-check-user="'.$value['id'].'" data-check-user-photo="'.profilePhoto($value['id']).'" id="checkuser_'.$value['id'].'">
			                  <label class="custom-control-label" style="cursor: pointer;" for="checkuser_'.$value['id'].'">
			                  <span class="text-hide">Check</span></label>
			              </div>
			          </td>

			          <td style="max-width:230px;overflow-x:hidden">
			              <div class="media align-items-center" >
			                  <div class="avatar avatar-md mr-3 '.$onlineNow.'" '.$onClickStories.'  style="width:55px;height:55px">
			                      <img src="'.profilePhoto($value['id']).'" class="avatar-img rounded-circle avatar-online box-shadow" '.$storyBorder.'>
			                  </div>
			                  <div class="media-body">
			                      <strong class="js-lists-values-employee-name"><a href="'.$sm['config']['site_url'].'index.php?page=admin&p=user&id='.$value['id'].'"  style="color:#333">'.$value['name'].' ,'.$value['age'].' '.$userVerified.'</a></strong><br>
			                      <span class="text-muted js-lists-values-employee-title">
			                      ID: '.$value['id'].'</span><br>
			                      <span class="text-muted js-lists-values-employee-title">'.$value['email'].'</span>			                      
			                  </div>
			              </div>
			          </td>
			          <td style="min-width:150px;">
			              <div class="media align-items-center">
			                  <div class="media-body">
			                      <strong>'.$value['city'].'</strong>
			                      <br>
			                      <span class="text-muted">'.$value['country'].'</span>
			                  </div>
			              </div>
			          </td>                                                    
			          <td>'.$badge.'</td>
			          <td style="min-width:130px;"><small class="text-muted">'.time_elapsed_string($value['last_access']).'</small></td>
			          <td>'.$value['credits'].'</td>
			          <td><strong>'.$userPremium.'</strong></td>
			          <td><strong>'.$ip.'</strong></td>		                                
			          <td>
			              <div class="dropdown ml-auto" data-table-dropdown>
			                  <a href="#" data-toggle="dropdown" data-caret="false" class="btn btn-light text-muted"><i class="material-icons">more_vert</i></a>
			                  <div class="dropdown-menu dropdown-menu-right">
			                      <a class="dropdown-item" style="font-size: 13px" target="_blank" href="'.$sm['config']['site_url'].'@'.$value['id'].'">Open live profile</a>
			                      <div class="dropdown-divider"></div>
			                      <a class="dropdown-item" style="font-size: 13px" href="'.$sm['config']['site_url'].'index.php?page=admin&p=user&id='.$value['id'].'" >Edit account</a>
			                      <a class="dropdown-item" style="font-size: 13px" href="javascript:;"
			                      onclick="'.$goToEditMediaUser.'">Edit media files</a>			                      
			                      <div class="dropdown-divider"></div>
			                      <a class="dropdown-item" href="#" style="font-size: 13px" 
			                      onclick="adminDeleteProfile('.$value['id'].')">Delete account</a>
			                      <div class="dropdown-divider"></div>
					              <a class="dropdown-item" href="#" 
					              onclick="adminDeleteProfile('.$value['id'].',0,'.$banEmail.')" style="font-size: 13px">
					                Delete user and ban email
					              </a>                          
					              <a class="dropdown-item" href="#" 
					              onclick="adminDeleteProfile('.$value['id'].',0,'.$banIP.')" style="font-size: 13px">
					                Delete user and ban IP
					              </a>			                      
			                  </div>
			              </div>
			          </td>
			      </tr>';

			      $i++;
			}
		} else {
			$data = 'Nothing found';
		}

		$arr['data'] = $data;
		$arr['total'] = $i;
		echo json_encode($arr);
	break;


case 'search_blocked_user_agents':
    $data = array();
    $arr=array();

    $search_date=secureEncode($_POST['blockdate']);
    $from='';
    $till='';

    if(!empty($search_date)){
        $date = explode(' to ', $search_date);
        $date = array_map(function($el) {
            $el = trim($el);
            $el = explode('/', $el);
            $el = array($el[2], $el[0], $el[1]);
            $el = implode('-', $el);
            return $el;
        }, $date);

        if (count($date) == 2) {
            $from = strtotime($date[0] . ' 00:00:00');
            $till = strtotime($date[1] . ' 23:59:59');
        }else{
            $date = $date[0];
            if(empty($date))
                $date=date('Y/m/d');
            $from = strtotime($date . ' 00:00:00');
            $till = strtotime($date. ' 23:59:59');
        }
    }

    $filter = " WHERE 1=1 ";

    // Filter by block reason
    if(!empty($_POST['blockreason'])){
        $blockreason = secureEncode($_POST['blockreason']);
        $filter .= " AND block_reason = '$blockreason' ";
    }

    // Filter by search term (user agent or IP)
    if(!empty($_POST['search'])){
        $search = secureEncode($_POST['search']);
        $filter .= " AND (user_agent LIKE '%$search%' OR ip LIKE '%$search%') ";
    }

    // Date filter
    if(!empty($from) && !empty($till)){
        $filter .= " AND UNIX_TIMESTAMP(block_date) >= $from AND UNIX_TIMESTAMP(block_date) <= $till ";
    }

    $sql="SELECT id, user_agent, ip, block_reason, isp_info, block_date
          FROM blocked_user_agents
          $filter
          ORDER BY block_date DESC";

    $query = $mysqli->query($sql);
    $dataTmp = array();

    if ($query->num_rows > 0) {
        while($resp = $query->fetch_object()){
            // Truncate user agent for display
            $ua_display = strlen($resp->user_agent) > 100 ?
                substr($resp->user_agent, 0, 100) . '...' : $resp->user_agent;

            // Truncate ISP info for display
            $isp_display = strlen($resp->isp_info) > 50 ?
                substr($resp->isp_info, 0, 50) . '...' : $resp->isp_info;

            $dataTmp[] = array(
                'id'=>$resp->id,
                'user_agent'=>$ua_display,
                'ip'=>$resp->ip,
                'block_reason'=>$resp->block_reason,
                'isp_info'=>$isp_display,
                'block_date'=>date('m/d/Y H:i', strtotime($resp->block_date))
            );
        }
    }

    if(!empty($dataTmp)){
        $i = 0;
        foreach($dataTmp as $eachRow){
            $data[$i]='<tr>
                <td>'.$eachRow['id'].'</td>
                <td title="'.$eachRow['user_agent'].'">'.htmlspecialchars($eachRow['user_agent']).'</td>
                <td>'.$eachRow['ip'].'</td>
                <td>'.$eachRow['block_reason'].'</td>
                <td title="'.$eachRow['isp_info'].'">'.htmlspecialchars($eachRow['isp_info']).'</td>
                <td>'.$eachRow['block_date'].'</td>
            </tr>';
            $i++;
        }
    }else{
       $data = 'Nothing found';
    }

    $arr['data'] = $data;
    $arr['total'] = $i;
    echo json_encode($arr);
	break;


	case 'search_payment_history':
    $data = array();
    $arr=array();
    $time = time()-300;

    $filter = 'WHERE quantity>0';
    $dropdown = '';
    $cAvatar = '';
    $dAvatar = '';
    $reportedCol = '';

    $type = secureEncode($_POST['type']);
    
    if($type == 'users'){
    	$order = 'time';
    	if(!empty($_POST['search'])){
    		$search = secureEncode($_POST['search']);
    		$filter.=" AND u_id = '$search' ";
    	} else {
    		$filter.="";
    	}


	if(!empty($_POST['packagetype'])){
    		$search = secureEncode($_POST['packagetype']);
    		$filter.=" AND type LIKE '%$search%' ";
    	} else {
    		$filter.="";
    	}
	if(!empty($_POST['providertype'])){
    		$search = secureEncode($_POST['providertype']);
    		$filter.=" AND gateway LIKE '%$search%' ";
    	} else {
    		$filter.="";
    	}

		$date = secureEncode($_POST['paymentdate']);
		$dateEnabled = secureEncode($_POST['dateEnabled']);


		$date = str_replace(' ', '', $date);
		$date = explode('to',$date);
		$date1 = $date[0];
		$date2 = $date[1];
		if($dateEnabled == 'on'){

             if($date2!='')
		//	$filter.=' AND saledate BETWEEN "'.$date1.'" AND "'.$date2.'"';
					$filter.=" AND str_to_date(saledate, '%m/%d/%Y')    BETWEEN STR_TO_DATE('$date1','%m/%d/%Y') AND  STR_TO_DATE( '$date2' ,'%m/%d/%Y') ";    


           else
			 $filter.=' AND saledate = "'.$date1.'"';
		}else{
		    
		    	 $filter.=' AND saledate = "'.date('m/d/Y').'"';
		}




    	$search = getArray('sales',$filter,$order.' desc','');
    }

 
    $i=0;
	$total_amount=0;
    if(isset($search)){
      foreach ($search as $value) { 


 if($value['gateway']=='micropayment')
$value['amount']=$value['amount']/100;

 $value['time']=date("m-d-Y H:i:s", $value['time']);


    
    	$total_amount = $total_amount + $value['amount'];
  	


        $data[$i]='
            <tr>
               
                <td>'.$value['u_id'].'</td> 
				    <td>'.ucfirst($value['gateway']).'</td>
               <td>'.$value['time'].'</td>
			     <td>'.ucfirst($value['type']).'</td>
				      <td>'.$value['action'].'</td>
				   <td>'.$value['amount'].' €</td>
				   <td>'.$value['payment_status'].'</td>
            </tr>';

            $i++;
      }
    } else {
      $data = 'Nothing found';
    }
    $total_amount=$total_amount.' €';
    $arr['data'] = $data;
    $arr['total'] = $i;
	 $arr['total_amount'] = $total_amount;
    echo json_encode($arr);
	break;
//




    case 'search_intrest_campaign':
    $data = array();
    $arr=array();
    
        $dataTmp = array();
   
        
        $autoOpt=array();
          $allOptIntr = $mysqli->query("SELECT * FROM auto_message_options");
           while($intr = $allOptIntr->fetch_object()) { 
               $autoOpt[$intr->opt]=$intr->value;
           }


        
    	$intrArr=array();
          $allIntrests = $mysqli->query("SELECT * FROM auto_intrest_list");
           while($intr = $allIntrests->fetch_object()) { 
               
         
               if($autoOpt["intrest_".$intr->intrest_id."_msg_type_1"]=='image')
             $autoOpt["intrest_".$intr->intrest_id."_message_1"]="<img src='assets/sources/".$autoOpt['intrest_'.$intr->intrest_id.'_message_1']."' width='150' height='150'>";
             
                if($autoOpt["intrest_".$intr->intrest_id."_msg_type_2"]=='image')
             $autoOpt["intrest_".$intr->intrest_id."_message_2"]="<img src='assets/sources/".$autoOpt['intrest_'.$intr->intrest_id.'_message_2']."' width='150' height='150'>";
               
               
           $dataTmp[] = array('intrest_id'=>$intr->intrest_name,'intrest_message_1'=>$autoOpt["intrest_".$intr->intrest_id."_message_1"], 'intrest_message_2'=>$autoOpt["intrest_".$intr->intrest_id."_message_2"], 'intrest_fake_user'=>$autoOpt["intrest_".$intr->intrest_id."_fake_user"],'isenabled'=>($autoOpt["intrest_".$intr->intrest_id."_message_active"]=='0'?'Disabled':'Enabled'), 'edit'=>'<a href="#" onclick="javascript:openAutIntrEdit('.$intr->intrest_id.');">Edit<a>');     
               
               
           }

        
        
        if(!empty($dataTmp)){
            $i = 0;
            foreach($dataTmp as $eachRow){
                if(empty($eachRow['intrest_id']))
                     $eachRow['intrest_id']='Deleted!';
                
                $data[$i]='<tr><td>'.$eachRow['intrest_id'].'</td><td>'.$eachRow['intrest_message_1'].'</td><td>'.$eachRow['intrest_message_2'].'</td><td>'.$eachRow['intrest_fake_user'].'</td><td>'.$eachRow['isenabled'].'</td><td>'.$eachRow['edit'].'</td></tr>';
                $i++;  
            }
        }else{
           $data = 'Nothing found'; 
        }
    $arr['data'] = $data;
    $arr['total'] = $i;
    echo json_encode($arr);
	break;

case 'search_chats_campaign_tracking':
    $data = array();
    $arr=array();
    
    
    
       
        $search_date=secureEncode($_POST['chatdate']);
        $from='';
        $till='';

     $date = explode(' to ', $search_date);
            $date = array_map(function($el) {
            $el = trim($el);
            $el = explode('/', $el);
            $el = array($el[2], $el[0], $el[1]);
            $el = implode('-', $el);
            return $el;
            }, $date);
            
            if (count($date) == 2) {
            $from = strtotime($date[0] . ' 00:00:00');
            $till = strtotime($date[1] . ' 23:59:59');
            }else{
            
            $date = $date[0];
            if(empty($date))
            $date=date('Y/m/d');
            $from = strtotime($date . ' 00:00:00');
            $till = strtotime($date. ' 23:59:59');
            
            }
            
          
        
     $filter = " ";
     $filter2='';
        
        if(!empty($_POST['adservice'])){
            if(secureEncode($_POST['adservice'])=='direct')
              $filter ="TRIM(u.referido) =''  AND  ";
              else if(secureEncode($_POST['adservice'])=='all')
    		$filter ="   ";
    		else
    		  if(secureEncode($_POST['adservice'])=='best'){
    	$sql="SELECT u.referido,count(chat.id) as totalSent FROM chat JOIN users u ON u.id = chat.s_id WHERE u.referido!='' AND (chat.s_id > 0 
    	and chat.fake=1) GROUP BY u.referido ORDER BY totalSent DESC LIMIT 1";
        $sqlBest = $mysqli->query($sql);
        
         if ($sqlBest->num_rows > 0) { 
             $bestCamp = $sqlBest->fetch_object();
             $bestCampName=$bestCamp->referido;
             	$filter ="u.referido='$bestCampName'  AND  ";
         }else{
             	$filter ="   ";
         }
    		 }else
    		$filter ="u.referido='".secureEncode($_POST['adservice'])."' AND   ";

    		
    	}else{
    	    $filter ="u.referido!=''  AND  ";
    	} 
          if($_POST['todaySignup'] == 'on'){
                $date_1=date('m/d/Y');
                  $date_2=date('m/d/Y');
          		$filter2.=" 	str_to_date(u.join_date, '%m/%d/%Y') BETWEEN    STR_TO_DATE('$date_1','%m/%d/%Y') AND  STR_TO_DATE( '$date_2' ,'%m/%d/%Y') AND   ";
          }
		
			$sql="SELECT chat.s_id, count(chat.id) as totalSent, 
		(select join_date from users where users.id=chat.s_id) as join_date,
		(select name from users where users.id=chat.s_id) as s_name,
		(select referido from users where users.id=chat.s_id) as campaign_name 
		FROM chat
		JOIN users u ON u.id = chat.s_id
		WHERE $filter $filter2
		 (chat.s_id > 0 and chat.fake=1) AND (CAST(chat.time AS SIGNED) >= $from AND CAST(chat.time AS SIGNED) <= $till) GROUP BY chat.s_id ORDER  BY totalSent DESC";
   
   
    if(secureEncode($_POST['adservice'])=='onlybest'){
        $sql="SELECT u.referido as campaign_name,count(chat.id) as totalSent FROM chat JOIN users u ON u.id = chat.s_id WHERE u.referido!='' 
        AND (chat.s_id > 0 and chat.fake=1) GROUP BY u.referido ORDER BY totalSent desc
";
    }
        $dataTmp = array();
        
        $goalsquery = $mysqli->query($sql);
        if ($goalsquery->num_rows > 0) { 
            $campsArr=array();
              $allCampaigns = $mysqli->query("SELECT * FROM campaigns order by id DESC");
               while($stat = $allCampaigns->fetch_object()) { 
                   $campsArr[$stat->campaign_slug]=$stat->campaign_name;
               }
        
		  while($resp = $goalsquery->fetch_object()){  
              
              $dataTmp[] = array('s_id'=>$resp->s_id,'totalsent'=>$resp->totalSent, 'campaign_id'=>$resp->campaign_name, 's_name'=>$resp->s_name, 'user_join_date'=>$resp->join_date);
          }
        }
        
        if(!empty($dataTmp)){
            $i = 0;
            foreach($dataTmp as $eachRow){
                $campname=$campsArr[$eachRow['campaign_id']];
                if(empty($campname))
                $campname='Deleted!';
                
                $data[$i]='<tr><td>'.$eachRow['s_id'].'</td><td>'.$eachRow['totalsent'].'</td><td>'.$campname.'</td><td>'.$eachRow['s_name'].'</td><td>'.$eachRow['user_join_date'].'</td></tr>';
                $i++;  
            }
        }else{
           $data = 'Nothing found'; 
        }
    $arr['data'] = $data;
    $arr['total'] = $i;
    echo json_encode($arr);
	break;
	//
	case 'search_payment_goals_tracking':
    $data = array();
    $arr=array();
        
     $filter = " WHERE id > 0 ";
        
        if(!empty($_POST['adservice'])){
            
            if($_POST['adservice'] == 'exoclick')
    		$filter.=" AND service = 'exoclick' ";
            
            if($_POST['adservice'] == 'trafficfabric')
    		$filter.=" AND service = 'trafficfabric' ";
    	} 
        
        $date = secureEncode($_POST['paymentdate']);
        $date = str_replace(' ', '', $date);
		$date = explode('to',$date);
		$date1 = $date[0];
		$date2 = $date[1];
		
        if($date2!='')
		$filter.=" AND DATE(created_at)    BETWEEN STR_TO_DATE('$date1','%m/%d/%Y') AND  STR_TO_DATE( '$date2' ,'%m/%d/%Y') ";    
        
        $dataTmp = array();
        
        $goalsquery = $mysqli->query("SELECT campaign_id, service, SUM(paid_amount)  as paidamount, COUNT(if(goal_name = 'click', id, NULL)) as click , COUNT(if(goal_name = 'register', id, NULL)) as register , COUNT(if(goal_name = 'purchase', id, NULL)) as purchase  FROM `users_tracking_goals`  ".$filter." GROUP BY campaign_id, service");
        if ($goalsquery->num_rows > 0) { 
		  while($resp = $goalsquery->fetch_object()){  
              
              $dataTmp[] = array('paidamount'=>$resp->paidamount,'service'=>$resp->service, 'campaign_id'=>$resp->campaign_id, 'click'=>$resp->click, 'register'=>$resp->register, 'purchase'=>$resp->purchase);
          }
        }
        
        if(!empty($dataTmp)){
            $i = 0;
            foreach($dataTmp as $eachRow){
                $thisPaidValue = intval($eachRow['paidamount']);
                if($thisPaidValue > 0 ){$thisPaidValue = $thisPaidValue / 100;}
                $data[$i]='<tr><td>'.$eachRow['service'].'</td><td>'.$eachRow['campaign_id'].'</td><td>'.$eachRow['click'].'</td><td>'.$eachRow['register'].'</td><td>'.$eachRow['purchase'].'</td><td>'.$thisPaidValue.'</td></tr>';
                $i++;  
            }
        }else{
           $data = 'Nothing found'; 
        }
    $arr['data'] = $data;
    $arr['total'] = $i;
    echo json_encode($arr);
	break;
	
	/////////
	
		case 'search_banned_chats':
    $data = array();
    $arr=array();
    $time = time()-300;

    $filter = '';
    $dropdown = '';
    $cAvatar = '';
    $dAvatar = '';
    $reportedCol = '';
    
    $moderatorGroupId=$sm['user']['mod_group_id'];

    $type = secureEncode($_POST['type']);
    $col = 'moderator';
 
    if($type == 'reported'){
    	$order = 'reported_date';
     if( $sm['user']['admin'] == 1)
     	$filter = "WHERE viewed = 0 ";
     else
    	$filter = "WHERE viewed = 0 AND reported_by IN(select id from users where mod_group_id=$moderatorGroupId)";
    	$search = getArray('reports_chats',$filter,$order.' desc','');
    }        
    
    $i=0;
    if(isset($search)){
      foreach ($search as $value) { 

 
      
      	if($type == 'reported'){

      		$id = $i.'22900'.$value['reported'];
      		$userId = $value['reported_by'];
      		$col = 'city';
      		$checkData = $i.'22900'.$value['id'];
      		$val = $value['reason'];
      		$timeago = $value['reported_date'];
			$banEmail = "'".'both,'.getData('users','email','where id ='.$value['reported']).','.getData('users','ip','where id ='.$value['reported'])."'";
			$banIP = "'".'ip,'.getData('users','ip','where id ='.$value['reported'])."'"; 

			$adminUpdateDataCustomVal = "'removeFromChatReportList'"; 
/*
			$dropdown = '
			  <a class="dropdown-item" href="#" style="font-size: 13px" 
			  onclick="adminUpdateData('.$value['id'].',0,1,'.$adminUpdateDataCustomVal.')"> Chat freigeben</a>			  
			  
			  		  <div class="dropdown-divider"></div>
			  <a class="dropdown-item" href="#" style="font-size: 13px" 
			  onclick="adminSendModImage('.$value['user_from'].','.$value['user_to'].')"> Bild senden</a>';
			  
			   if($sm['moderator']['ChatFakeAdmin'] == 'Yes'  && $sm['user']['admin']!=1){    
			   }else{
				$dropdown .= '  <div class="dropdown-divider"></div>
			  <a class="dropdown-item" href="#" 
			  onclick="adminDeleteProfile('.$value['reported'].',0,'.$banEmail.')" style="font-size: 13px">
			    Benutzer löschen und Email | IP blocken
			  </a>                          
			 ';
			   }
*/

 
			   if($sm['moderator']['ChatFakeAdmin'] == 'Yes'  && $sm['user']['admin']!=1){    
				   
			  		$dropdown = '
			 
			  		 
			  <a class="dropdown-item" href="#" style="font-size: 13px" 
			  onclick="adminSpyModPopup('.$value['user_from'].','.$value['user_to'].')"> Msg Spy</a>';


			   }else{


			$dropdown = '
			  <a class="dropdown-item" href="#" style="font-size: 13px" 
			  onclick="adminUpdateData('.$value['id'].',0,1,'.$adminUpdateDataCustomVal.')"> Chat freigeben</a>			  
			  
			  		  <div class="dropdown-divider"></div>
			  <a class="dropdown-item" href="#" style="font-size: 13px" 
			  onclick="adminSendModImage('.$value['user_from'].','.$value['user_to'].')"> Bild senden</a>';

			  		$dropdown .= '
			 
			  		  <div class="dropdown-divider"></div>
			  <a class="dropdown-item" href="#" style="font-size: 13px" 
			  onclick="adminSpyModPopup('.$value['user_from'].','.$value['user_to'].')"> Msg Spy</a>';


				$dropdown .= '  <div class="dropdown-divider"></div>
			  <a class="dropdown-item" href="#" 
			  onclick="adminDeleteProfile('.$value['reported'].',0,'.$banEmail.')" style="font-size: 13px">
			    Benutzer löschen und Email | IP blocken
			  </a>                          
			 ';
			   }

			$cAvatar = '
				<img src="'.profilePhoto($value['reported_by']).'" class="avatar-img rounded">
			'; 			  
			$dAvatar = '
				<img src="'.profilePhoto($value['reported']).'" class="avatar-img rounded">
			'; 	
			
		$tAvatar = '
				<img src="'.profilePhoto($value['user_to']).'" class="avatar-img rounded">
			';
			
			
		   $reportedCol = '
	        <td>
	            <div class="media align-items-center">
	                <div class="avatar avatar-sm mr-3 ">
	                    '.$dAvatar.'
	                </div>
	                <div class="media-body">
	                    <strong class="js-lists-values-employee-name">'.getData('users','name','where id ='.$value['reported']).'</strong><span class="badge badge-warning"></span><br>
	                </div>
	            </div>
	        </td> 
		   ';      	
		   
		   
		   
		   
		   		   $reportedColF = '
	        <td>
	            <div class="media align-items-center">
	                <div class="avatar avatar-sm mr-3 ">
	                    '.$dAvatar.'
	                </div>
	                <div class="media-body">
	                    <strong class="js-lists-values-employee-name">'.getData('users','name','where id ='.$value['reported']).'</strong><span class="badge badge-warning"></span><br>
	                </div>
	            </div>
	        </td> 
		   ';      	
		   
		   
		   
		   		   $reportedColT = '
	        <td>
	            <div class="media align-items-center">
	                <div class="avatar avatar-sm mr-3 ">
	                    '.$tAvatar.'
	                </div>
	                <div class="media-body">
	                    <strong class="js-lists-values-employee-name">'.getData('users','name','where id ='.$value['user_to']).'</strong><span class="badge badge-warning"></span><br>
	                </div>
	            </div>
	        </td> 
		   ';      	
		   
		   
		   
		   
		}

      	$uName = getData('users','name','where id ='.$userId);
      //	$uMod = getData('users',$col,'where id ='.$userId);

        $data[$i]='
            <tr>
                <td>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" onclick="checkData(this,'.$checkData.')" class="custom-control-input" data-check-search="'.$checkData.'" id="checkcall_'.$id.'">
                        <label class="custom-control-label" style="cursor: pointer;" for="checkcall_'.$id.'">
                        <span class="text-hide">Check</span></label>
                    </div>
                </td>
                '.$reportedCol.'
                <td>'.$val.'</td>
                
                   '.$reportedColF.'
                     '.$reportedColT.'
                
                
                <td><small class="text-muted">'.time_elapsed_string($timeago).'</small></td>   
                
                
                
                <td>
                    <div class="media align-items-center">
                        <div class="avatar avatar-sm mr-3 ">
                            '.$cAvatar.'
                        </div>
                        <div class="media-body">
                            <strong class="js-lists-values-employee-name">'.$uName.'</strong><span class="badge badge-warning"></span><br>
                        </div>
                    </div>
                </td>                                                                                              
                <td>
                    <div class="dropdown ml-auto">
                        <a href="#" data-toggle="dropdown" data-caret="false" class="btn btn-light text-muted"><i class="material-icons">more_vert</i></a>
                        <div class="dropdown-menu dropdown-menu-right">                          
                            '.$dropdown.'
                        </div>
                    </div>
                </td>
            </tr>';

            $i++;
      }
    } else {
      $data = 'Nothing found';
    }

    $arr['data'] = $data;
    $arr['total'] = $i;
    echo json_encode($arr);
	break;


//////////

	case 'search_banned':
    $data = array();
    $arr=array();
    $time = time()-300;

    $filter = '';
    $dropdown = '';
    $cAvatar = '';
    $dAvatar = '';
    $reportedCol = '';

    $type = secureEncode($_POST['type']);
    $col = 'moderator';
    if($type == 'users'){
    	$order = 'banned_date';
    	if(!empty($_POST['search'])){
    		$search = secureEncode($_POST['search']);
    		$filter.=" WHERE email LIKE '%$search%' ";
    	} else {
    		$filter.="";
    	}
    	$search = getArray('blocked_users',$filter,$order.' desc','');
    }

    if($type == 'ip'){
    	$order = 'banned_date';
    	if(!empty($_POST['search'])){
    		$search = secureEncode($_POST['search']);
    		$filter.=" WHERE ip LIKE '%$search%' ";
    	} else {
    		$filter.="";
    	}    	
    	$search = getArray('blocked_ips',$filter,$order.' desc','');
    }  

    if($type == 'reported'){
    	$order = 'reported_date';
    	$filter = 'WHERE viewed = 0';
    	$search = getArray('reports',$filter,$order.' desc','');
    }        
    
    $i=0;
    if(isset($search)){
      foreach ($search as $value) { 

      	if($type == 'users'){
      		$id = $value['id'];
      		$val = $value['email'];
      		$checkData = $value['id'];
      		$userId = $value['banned_by'];
      		$timeago = $value['banned_date'];
      		$adminUpdateDataCustomVal = "'unbanUser'";

			$dropdown = '<a class="dropdown-item" href="javascript:;" 
			onclick="adminUpdateData('.$id.',0,1,'.$adminUpdateDataCustomVal.')">Unban email</a>';
	    	$cAvatar = '
	    		<img src="'.profilePhoto($value['banned_by']).'" class="avatar-img rounded">
	    	'; 			     		
      	}

      	if($type == 'ip'){
      		$id = $value['ip'];
      		$val = $value['ip'];
      		$checkData = $value['ip'];
      		$userId = $value['banned_by'];
      		$timeago = $value['banned_date'];

      		$adminUpdateDataCustomVal = "'unbanIP'";
      		$adminUpdateDataIp = "'".$id."'"; 
			$dropdown = '<a class="dropdown-item" href="javascript:;" 
			onclick="adminUpdateData('.$adminUpdateDataIp.',0,1,'.$adminUpdateDataCustomVal.')">Unban IP</a>';
	    	$cAvatar = '
	    		<img src="'.profilePhoto($value['banned_by']).'" class="avatar-img rounded">
	    	'; 			      		
      	}

      	if($type == 'reported'){

      		$id = $i.'22900'.$value['reported'];
      		$userId = $value['reported_by'];
      		$col = 'city';
      		$checkData = $i.'22900'.$value['reported'];
      		$val = $value['reason'];
      		$timeago = $value['reported_date'];
			$banEmail = "'".'email,'.getData('users','email','where id ='.$value['reported']).','.getData('users','ip','where id ='.$value['reported'])."'";
			$banIP = "'".'ip,'.getData('users','ip','where id ='.$value['reported'])."'"; 

			$adminUpdateDataCustomVal = "'removeFromReportList'"; 

			$dropdown = '<a class="dropdown-item" style="font-size: 13px" target="_blank" href="'.$sm['config']['site_url'].'@'.$value['reported'].'">Open live profile</a>
			  <a class="dropdown-item" style="font-size: 13px" href="'.$sm['config']['site_url'].'index.php?page=admin&p=user&id='.$value['reported'].'" target="_blank">Edit account</a>
			  <div class="dropdown-divider"></div>
			  <a class="dropdown-item" href="#" style="font-size: 13px" 
			  onclick="adminUpdateData('.$value['reported'].',0,1,'.$adminUpdateDataCustomVal.')">Remove from  report list</a>			  
			  <div class="dropdown-divider"></div>
			  <a class="dropdown-item" href="#" style="font-size: 13px" 
			  onclick="adminDeleteProfile('.$value['reported'].')">Delete account</a>
			  <div class="dropdown-divider"></div>
			  <a class="dropdown-item" href="#" 
			  onclick="adminDeleteProfile('.$value['reported'].',0,'.$banEmail.')" style="font-size: 13px">
			    Delete user and ban email
			  </a>                          
			  <a class="dropdown-item" href="#" 
			  onclick="adminDeleteProfile('.$value['reported'].',0,'.$banIP.')" style="font-size: 13px">
			    Delete user and ban IP
			  </a>';

			$cAvatar = '
				<img src="'.profilePhoto($value['reported_by']).'" class="avatar-img rounded">
			'; 			  
			$dAvatar = '
				<img src="'.profilePhoto($value['reported']).'" class="avatar-img rounded">
			'; 		    	
		   $reportedCol = '
	        <td>
	            <div class="media align-items-center">
	                <div class="avatar avatar-sm mr-3 ">
	                    '.$dAvatar.'
	                </div>
	                <div class="media-body">
	                    <strong class="js-lists-values-employee-name">'.getData('users','name','where id ='.$value['reported']).'</strong><span class="badge badge-warning"></span><br>
	                    <span class="text-muted js-lists-values-employee-title"><small>'.getData('users','city','where id ='.$value['reported']).'</small></span>
	                </div>
	            </div>
	        </td> 
		   ';      		
		}

      	$uName = getData('users','name','where id ='.$userId);
      	$uMod = getData('users',$col,'where id ='.$userId);

        $data[$i]='
            <tr>
                <td>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" onclick="checkData(this,'.$checkData.')" class="custom-control-input" data-check-search="'.$checkData.'" id="checkcall_'.$id.'">
                        <label class="custom-control-label" style="cursor: pointer;" for="checkcall_'.$id.'">
                        <span class="text-hide">Check</span></label>
                    </div>
                </td>
                '.$reportedCol.'
                <td>'.$val.'</td>
                <td><small class="text-muted">'.time_elapsed_string($timeago).'</small></td>                
                <td>
                    <div class="media align-items-center">
                        <div class="avatar avatar-sm mr-3 ">
                            '.$cAvatar.'
                        </div>
                        <div class="media-body">
                            <strong class="js-lists-values-employee-name">'.$uName.'</strong><span class="badge badge-warning"></span><br>
                            <span class="text-muted js-lists-values-employee-title"><small>'.$uMod.'</small></span>
                        </div>
                    </div>
                </td>                                                                                              
                <td>
                    <div class="dropdown ml-auto">
                        <a href="#" data-toggle="dropdown" data-caret="false" class="btn btn-light text-muted"><i class="material-icons">more_vert</i></a>
                        <div class="dropdown-menu dropdown-menu-right">                          
                            '.$dropdown.'
                        </div>
                    </div>
                </td>
            </tr>';

            $i++;
      }
    } else {
      $data = 'Nothing found';
    }

    $arr['data'] = $data;
    $arr['total'] = $i;
    echo json_encode($arr);
	break;

	case 'search_withdrawals':
    $data = array();
    $arr=array();
    $time = time()-300;

    $filter = '';

	$search = getArray('users_withdraw',$filter,'status DESC, id DESC','');
    $i=0;
    if(isset($search)){
      foreach ($search as $value) { 

      	$id = $value['id'];
      	$userId = $value['u_id'];
 
    	$adminUpdateDataCustomVal1 = "'withdrawComplete'";
    	$adminUpdateDataCustomVal2 = "'withdrawCanceled'"; 
    	$uName = getData('users','name','where id ='.$userId);

    	if($value['status'] == 'Pending'){
			$dropdown = '
			  <a class="dropdown-item" style="font-size: 13px" target="_blank" href="'.$sm['config']['site_url'].'@'.$value['u_id'].'">Open live profile</a>
			  <a class="dropdown-item" style="font-size: 13px" href="'.$sm['config']['site_url'].'index.php?page=admin&p=user&id='.$value['u_id'].'" target="_blank">Edit User</a>		  
			  <div class="dropdown-divider"></div>
			  <a class="dropdown-item" href="#" style="font-size: 13px" 
			  onclick="adminUpdateData('.$id.',0,1,'.$adminUpdateDataCustomVal1.')">
			  Withdrawal Complete</a>			  
			  <div class="dropdown-divider"></div>
			  <a class="dropdown-item" href="#" style="font-size: 13px" 
			  onclick="adminUpdateData('.$id.',0,1,'.$adminUpdateDataCustomVal2.')">
			  Cancel Withdrawal</a>'; 
    	} else {
			$dropdown = '
			  <a class="dropdown-item" style="font-size: 13px" target="_blank" href="'.$sm['config']['site_url'].'@'.$value['u_id'].'">Open live profile</a>
			  <a class="dropdown-item" style="font-size: 13px" href="'.$sm['config']['site_url'].'index.php?page=admin&p=user&id='.$value['u_id'].'" target="_blank">Edit User</a>'; 
    	}
      	
      	$uEmail = getData('users','email','where id ='.$userId);
      	$uPaypal = getData('users','paypal','where id ='.$userId);

        $data[$i]='
            <tr class="data-search-verifications">
                <td>
                    <div class="media align-items-center">
                        <div class="avatar avatar-sm mr-3 ">
                            <img src="'.profilePhoto($userId).'" class="avatar-img rounded">
                        </div>
                        <div class="media-body">
                            <strong class="js-lists-values-employee-name">'.$uName.'</strong><span class="badge badge-warning"></span><br>
                            <span class="text-muted js-lists-values-employee-title">'.$uEmail.'</span>
                        </div>
                    </div>
                </td>
                <td>
                	'.$sm['plugins']['settings']['currency'].' '.$value['withdraw_amount'].'</small>
                </td>
                <td>
                	'.$uPaypal.'</small>
                </td>                
                <td>
                	'.$value['withdraw_date'].'
                </td>
                <td>
                	'.$value['status'].'
                </td>                    
                <td>
                    <div class="dropdown ml-auto">
                        <a href="#" data-toggle="dropdown" data-caret="false" class="btn btn-light text-muted"><i class="material-icons">more_vert</i></a>
                        <div class="dropdown-menu dropdown-menu-right">                          
                            '.$dropdown.'
                        </div>
                    </div>
                </td>
            </tr>';

            $i++;
      }
    } else {
      $data = 'Nothing found';
    }

    $arr['data'] = $data;
    $arr['total'] = $i;
    echo json_encode($arr);
	break;

	case 'search_verifications':
    $data = array();
    $arr=array();
    $time = time()-300;

    $filter = 'WHERE status = "No"';

	$search = getArray('users_verification',$filter,'time ASC','');
    $i=0;
    if(isset($search)){
      foreach ($search as $value) { 

      	$checkData = $value['time'];
      	$id = $value['time'];
      	$userId = $value['uid'];
      	$timeago = $value['time'];
    	$uploadedPhoto = '
    		<img src="'.$value['media'].'" class="avatar-img rounded">
    	';
    	$uploadedPhotoOnClick = "'".$value['media']."'"; 
    	$adminUpdateDataCustomVal1 = "'approveUserVerification'";
    	$adminUpdateDataCustomVal2 = "'noapproveUserVerification'"; 
    	$uName = getData('users','name','where id ='.$userId);

		$dropdown = '
		  <a class="dropdown-item" style="font-size: 13px" target="_blank" href="'.$sm['config']['site_url'].'@'.$value['uid'].'">Open live profile</a>
		  <div class="dropdown-divider"></div>
		  <a class="dropdown-item" href="#" style="font-size: 13px" 
		  onclick="adminUpdateData('.$userId.',0,1,'.$adminUpdateDataCustomVal1.')">
		  Approve '.$uName.'</a>			  
		  <div class="dropdown-divider"></div>
		  <a class="dropdown-item" href="#" style="font-size: 13px" 
		  onclick="adminUpdateData('.$userId.',0,1,'.$adminUpdateDataCustomVal2.')">
		  No Approve '.$uName.'</a>'; 

      	
      	$uEmail = getData('users','email','where id ='.$userId);

        $data[$i]='
            <tr class="data-search-verifications">
                <td>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" onclick="checkData(this,'.$checkData.')" class="custom-control-input" data-check-search="'.$checkData.'" id="checkcall_'.$id.'">
                        <label class="custom-control-label" style="cursor: pointer;" for="checkcall_'.$id.'">
                        <span class="text-hide">Check</span></label>
                    </div>
                </td>
                <td>
                    <div class="media align-items-center">
                        <div class="avatar avatar-sm mr-3 ">
                            <img src="'.profilePhoto($userId).'" class="avatar-img rounded">
                        </div>
                        <div class="media-body">
                            <strong class="js-lists-values-employee-name">'.$uName.'</strong><span class="badge badge-warning"></span><br>
                            <span class="text-muted js-lists-values-employee-title">'.$uEmail.'</span>
                        </div>
                    </div>
                </td>
                <td onclick="showImageVerification('.$uploadedPhotoOnClick.')" style="cursor:pointer">
	                <div class="avatar avatar-sm mr-3" style="margin:0 auto;cursor:pointer">
	                	'.$uploadedPhoto.'
	                </div>
                </td>
                <td>
                	<small class="text-muted">'.time_elapsed_string($timeago).'</small>
                </td>                                                                               
                <td>
                    <div class="dropdown ml-auto">
                        <a href="#" data-toggle="dropdown" data-caret="false" class="btn btn-light text-muted"><i class="material-icons">more_vert</i></a>
                        <div class="dropdown-menu dropdown-menu-right">                          
                            '.$dropdown.'
                        </div>
                    </div>
                </td>
            </tr>';

            $i++;
      }
    } else {
      $data = 'Nothing found';
    }

    $arr['data'] = $data;
    $arr['total'] = $i;
    echo json_encode($arr);
	break;

	case 'search_media':
    $data = array();
    $arr=array();
    $time = time()-300;

    $searchInput = secureEncode($_POST['search']);
    $ptype = secureEncode($_POST['ptype']);
    $status = secureEncode($_POST['status']);
    $uploaded = secureEncode($_POST['uploaded']);
    $mediatype = secureEncode($_POST['mediatype']);

    if($mediatype == 'All'){
    	$filter = 'WHERE time >= 0';
    	$table = 'users_photos';	
    }

    if($mediatype == 'photo'){
    	$filter = 'WHERE video = 0 AND story = 0';
    	$table = 'users_photos';	
    }

    if($mediatype == 'video'){
    	$filter = 'WHERE video = 1 AND story = 0';
    	$table = 'users_photos';	
    }

    if($mediatype == 'story'){
    	$filter = 'WHERE story > 0';
    	$table = 'users_photos';	
    }        
    
	if($searchInput != ''){
		$filter.=" AND u_id = ".$searchInput;
	}

	if($status != 'All'){
		$filter.=' AND approved ='.$status;
	}

	if($uploaded != 'All'){
		$filter.=' AND fake ='.$uploaded;
	}	

	if($ptype != 'All'){
		$filter.=' AND blocked ='.$ptype.' AND private = '.$ptype;
	}	  

	if($searchInput != ''){
		$arr['searchUserId'] = $searchInput;
		$arr['searchUserPhoto'] = profilePhoto($searchInput);
		$arr['searchUserName'] = getData('users','name','WHERE id = '.$searchInput);
	} else {
		$arr['searchUserId'] = '';
	}	
	
	$search = getArray($table,$filter,'time DESC','');
    $i=0;
    if(isset($search)){
      foreach ($search as $value) { 

		$arr['searchUserId'] = '';
		if($searchInput != ''){
			$arr['searchUserPhoto'] = profilePhoto($value['u_id']);
			$arr['searchUserId'] = $value['u_id'];
			$arr['searchUserName'] = getData('users','name','WHERE id = '.$value['u_id']);
		}
      	$checkData = $value['id'];
      	$id = $value['id'];
      	$userId = $value['u_id'];
      	$timeago = $value['time'];

    	$uploadedPhoto = '
    		<img src="'.$value['thumb'].'" class="avatar-img rounded">
    	';
    	 
    	$adminUpdateDataCustomVal = "'updateMediaAdmin'";
    	$adminUpdateDataVal = '';

    	$uName = getData('users','name','where id ='.$userId);
    	$uEmail = getData('users','email','where id ='.$userId);
    	$uPhoto = profilePhoto($userId);
    	$uploadedPhotoOnClick = "'".$value['photo']."',".$userId.",'".$uName."','".$uEmail."','".$uPhoto."' ";



		$play_video = "'".$value['photo']."'";
      	$uName = getData('users','name','where id ='.$userId);

        if($value['video'] == 1){
        	$searchMedia = '
        	<td onclick="playVideo('.$play_video.')" style="position:relative;cursor:pointer;max-width:70px">
            	<video class="avatar avatar-img rounded avatar-lg mr-3 media-loading"
            	onclick="playVideo('.$play_video.')" style="width:60px;height:60px;cursor:pointer;border:0px solid #fff">
            		<source src="'.$value['photo'].'"/>
            	</video>
            	<div style="position:absolute;bottom:10px;left:15px;z-index:9">
            		<i class="material-icons" style="color:#fff;font-size:24px;cursor:pointer" onclick="playVideo('.$play_video.')">play_arrow</i>
            	</div>
            </td>
        	';
        	$tdOnClick = 'onclick="playVideo('.$play_video.')"';
        }  else {
        	$searchMedia = ' 
        		<td onclick="showMediaAdmin('.$uploadedPhotoOnClick.')" style="cursor:pointer;max-width:70px">
		            <div class="avatar avatar-lg mr-3 media-loading" style="margin:0 auto;cursor:pointer;">
		            	<a class="s-lightbox" href="'.$value['photo'].'" data-s-lightbox-caption="">'.$uploadedPhoto.'
		            	</a>
		            </div>
	            </td>
        	';
        	$tdOnClick = '';
        }

        $mediaType = 'Photo';
        $mediatypeLabel = 'primary';
        if($value['video'] == 1){
        	$mediaType = 'Video';
        	$mediatypeLabel = 'dark';
        }

        if($value['story'] > 0){
        	$mediaType = 'Story';
        	$mediatypeLabel = 'warning';
        }        

        $mediaArray = array();
        
        $mediaArray['mediaType'] = $mediaType;
        $mediaArray['mediaId'] = $value['id'];
        $mediaArray['mediaIdStory'] = $value['story'];
        $mediaArray['mediaUid'] = $value['u_id'];
        $mediaArray['mediaPhoto'] = $value['photo'];
        $mediaArray['mediaThumb'] = $value['thumb'];

        $mediaPublicDropdown = '';

  		$privateDropDown = 'none';
  		$publicDropDown = 'none';

        if($value['blocked'] == 1 || $value['private'] == 1){
        	$mediaPublic = 'Private';
        	$publicDropDown = 'block';
        	$publicLabel = 'dark';    	
        } else {
        	$mediaPublic = 'Public';
      	    $privateDropDown = 'block';
      	    $publicLabel = 'light';	
        }

		$mediaArray['action'] = 'updateMedia';
		$mediaArray['method'] = 'mediaSetPublic';

	    if($value['story'] == 0){
			$mediaPublicArrayOnClick = json_encode($mediaArray,JSON_UNESCAPED_UNICODE);
	        $mediaPublicArrayOnClick = "'adminUpdateData(".$mediaPublicArrayOnClick.")'";	        	
	   		$mediaPublicDropdown.= '<a class="dropdown-item" data-media-dropdown-public="'.$id.'" href="javascript:;" style="font-size: 13px;display:'.$publicDropDown.'" 
		  onclick='.$mediaPublicArrayOnClick.'> Set Public </a>';
		} 


    	$mediaArray['method'] = 'mediaSetPrivate';

        if($value['story'] == 0){
			$mediaPublicArrayOnClick = json_encode($mediaArray,JSON_UNESCAPED_UNICODE);
	        $mediaPublicArrayOnClick = "'adminUpdateData(".$mediaPublicArrayOnClick.")'";	        	
       		$mediaPublicDropdown.= '<a class="dropdown-item" data-media-dropdown-private="'.$id.'" href="javascript:;" style="font-size: 13px;display:'.$privateDropDown.'" 
		  onclick='.$mediaPublicArrayOnClick.'> Set private </a>';
		}

		$approveDropdown = '';

        $approved = '<span class="badge badge-success">Visible</span>';

        $approvedMedia = 'none';
        if($value['approved'] == 0){
        	$approvedMedia = 'block';
        	$approved = '<span class="badge badge-warning">Pending Review</span>'; 
        }
        $pendingMedia = 'none';
        if($value['approved'] == 1){
        	$pendingMedia = 'block';
        	$approved = '<span class="badge badge-success">Visible</span>';
        }        

    	$mediaArray['method'] = 'approveMedia';
    	$mediaArray['val'] = 1;
    	$mediaArray['html'] = '<span class="badge badge-success">Visible</span>';
		$mediaApproveArrayOnClick = json_encode($mediaArray,JSON_UNESCAPED_UNICODE);
        $mediaApproveArrayOnClick = "'adminUpdateData(".$mediaApproveArrayOnClick.")'"; 
               	
   		$approveDropdown.= '<a class="dropdown-item" data-media-dropdown-approve="'.$id.'" href="javascript:;" style="font-size: 13px;display:'.$approvedMedia.'" 
	  onclick='.$mediaApproveArrayOnClick.'> Approve media</a>';        	
    	      
    	$mediaArray['method'] = 'approveMedia';
    	$mediaArray['val'] = 0;
    	$mediaArray['html'] = '<span class="badge badge-warning">Pending Review</span>';
		$mediaApproveArrayOnClick = json_encode($mediaArray,JSON_UNESCAPED_UNICODE);
        $mediaApproveArrayOnClick = "'adminUpdateData(".$mediaApproveArrayOnClick.")'"; 
               	
   		$approveDropdown.= '<a class="dropdown-item" data-media-dropdown-pending="'.$id.'" href="javascript:;" style="font-size: 13px;display:'.$pendingMedia.'" 
	  onclick='.$mediaApproveArrayOnClick.'> Change to Pending</a>';        	
    	
              

        if($value['approved'] == 2 && $value['story'] == 0){
        	$mediaArray['method'] = 'approveMedia';
        	$mediaArray['val'] = 1;
        	$mediaArray['html'] = '<span class="badge badge-success">Visible</span>';
			$mediaApproveArrayOnClick = json_encode($mediaArray,JSON_UNESCAPED_UNICODE);
	        $mediaApproveArrayOnClick = "'adminUpdateData(".$mediaApproveArrayOnClick.")'"; 

       		$approveDropdown.= '<a class="dropdown-item" data-media-dropdown-approve="'.$id.'" href="javascript:;" style="font-size: 13px;" 
		  onclick='.$mediaApproveArrayOnClick.'> Approve media </a>
		  <div class="dropdown-divider"></div>';
        	$approved = '<span class="badge badge-danger">Deleted by user</span>';
        } 

        $uploadToProfileDropdown = '';
        if($value['story'] > 0){
        	$mediaArray['action'] = 'updateMedia';
        	$mediaArray['method'] = 'uploadToProfile';
			$mediaArrayOnClick = json_encode($mediaArray,JSON_UNESCAPED_UNICODE);
	        $mediaArrayOnClick = "'adminUpdateData(".$mediaArrayOnClick.")'"; 

       		$uploadToProfileDropdown = '<a class="dropdown-item" data-media-dropdown-uploadTo="'.$id.'" href="javascript:;" style="font-size: 13px;" 
		  onclick='.$mediaArrayOnClick.'> Upload to profile </a>
		  <div class="dropdown-divider"></div>';
        }

        $uploadToStoryDropdown = '';
        if($value['story'] == 0){
        	$mediaArray['action'] = 'updateMedia';
        	$mediaArray['method'] = 'uploadToStory';
			$mediaArrayOnClick = json_encode($mediaArray,JSON_UNESCAPED_UNICODE);
	        $mediaArrayOnClick = "'adminUpdateData(".$mediaArrayOnClick.")'"; 

       		$uploadToStoryDropdown = '<a class="dropdown-item" data-media-dropdown-uploadTo="'.$id.'" href="javascript:;" style="font-size: 13px;" 
		  onclick='.$mediaArrayOnClick.'> Upload to Story </a>
		  <div class="dropdown-divider"></div>';
        }        

        $setAsProfilePhotoDropdown = '';
        if($value['story'] == 0 && $value['video'] == 0 && $value['profile'] == 0){
        	$mediaArray['action'] = 'updateMedia';
        	$mediaArray['method'] = 'setAsProfilePhoto';
			$mediaArrayOnClick = json_encode($mediaArray,JSON_UNESCAPED_UNICODE);
	        $mediaArrayOnClick = "'adminUpdateData(".$mediaArrayOnClick.")'"; 

       		$setAsProfilePhotoDropdown = '
       		<a class="dropdown-item" data-media-dropdown-uploadTo="'.$id.'" href="javascript:;" style="font-size: 13px;" 
		  onclick='.$mediaArrayOnClick.'> Set as profile photo </a>
		  <div class="dropdown-divider"></div>';
        }   


		$storyFrom = $sm['plugins']['story']['days'];
		$time = time();	
		$extra = 86400 * $storyFrom;
		$storyFrom = $time - $extra;
		
		if($value['story'] > 0 && $value['approved'] == 1){
			$storiesFilter = 'where id = '.$value['story'];
			$checkStory = getData('users_story','storyTime',$storiesFilter);
			if($checkStory > $storyFrom){
				$approved = '<span class="badge badge-success">Visible</span>';
			} else {
				$approved = '<span class="badge badge-light">No visible</span>';

	        	$mediaArray['action'] = 'updateMedia';
	        	$mediaArray['method'] = 'reUploadStory';
	        	$mediaArray['html'] = '<span class="badge badge-success">Visible</span>';
				$mediaArrayOnClick = json_encode($mediaArray,JSON_UNESCAPED_UNICODE);
		        $mediaArrayOnClick = "'adminUpdateData(".$mediaArrayOnClick.")'"; 
	       		$uploadToStoryDropdown = '
				<div class="dropdown-divider" data-media-dropdown-reupload-story="'.$id.'"></div>
	       		<a class="dropdown-item" data-media-dropdown-reupload-story="'.$id.'" href="javascript:;" style="font-size: 13px;" 
			  onclick='.$mediaArrayOnClick.'>Re-upload Story</a>
			  <div class="dropdown-divider" data-media-dropdown-reupload-story="'.$id.'"></div>';
			}
		}

		if($value['story'] > 0 && $value['approved'] == 1){
			$storiesFilter = 'where id = '.$value['story'];
			$checkStory = getData('users_story','storyTime',$storiesFilter);
			if($checkStory > $storyFrom){
				$approved = '<span class="badge badge-success">Visible</span>';
			} else {
				$approved = '<span class="badge badge-light">No visible</span>';

	        	$mediaArray['action'] = 'updateMedia';
	        	$mediaArray['method'] = 'reUploadStory';
	        	$mediaArray['html'] = '<span class="badge badge-success">Visible</span>';
				$mediaArrayOnClick = json_encode($mediaArray,JSON_UNESCAPED_UNICODE);
		        $mediaArrayOnClick = "'adminUpdateData(".$mediaArrayOnClick.")'"; 
	       		$uploadToStoryDropdown = '
				<div class="dropdown-divider" data-media-dropdown-reupload-story="'.$id.'"></div>
	       		<a class="dropdown-item" data-media-dropdown-reupload-story="'.$id.'" href="javascript:;" style="font-size: 13px;" 
			  onclick='.$mediaArrayOnClick.'>Re-upload Story</a>
			  <div class="dropdown-divider" data-media-dropdown-reupload-story="'.$id.'"></div>';
			}
		}		

		$storyPriceDropdown = '';
        if($value['story'] > 0){
        	$checkStoryPrice = getData('users_story','credits','WHERE id = '.$value['story']);

        	if($checkStoryPrice > 0){
        		$mediaPublic = $checkStoryPrice.' Credits';
        	} else {
        		$mediaPublic = 'FREE';   		
        	}

        	$mediaArray['action'] = 'updateMedia';
        	$mediaArray['method'] = 'changeCreditPrice';
			$mediaArrayOnClick = json_encode($mediaArray,JSON_UNESCAPED_UNICODE);
	        $mediaArrayOnClick = "'adminUpdateData(".$mediaArrayOnClick.")'";

	        $storyPrices = explode(',',$sm['plugins']['story']['storyCreditsValues']);
       		$storyPriceDropdown = '
       		<div class="dropdown-item" data-media-dropdown-price-story="'.$id.'"
       		 style="font-size: 13px;">Story price</div>
       			<form>
	       			<select class="form-control" id="storyPriceSelect'.$id.'" 
	       			style="width:80%!important;margin-left:10%" onchange='.$mediaArrayOnClick.'>
	       				<option value="0">FREE</option>';
		       			foreach ($storyPrices as $price) {
		       				$selected = '';
		       				if($price == $checkStoryPrice){
		       					$selected = 'selected';
		       				}
		       				if($price == 0){
		       					$text = 'Free';
		       				} else {
		       					$text = $price.' Credits';
		       				}
		       				$storyPriceDropdown.='<option value="'.$price.'" '.$selected.'>
		       				'.$text.'</option>';
		       			}
	       				$storyPriceDropdown.='
	       			</select>
       			</form>
		  <div class="dropdown-divider" data-media-dropdown-price-story="'.$id.'"></div>';             	
        }		

        $mediaArray['action'] = 'deleteMedia';
        $deleteMedia = json_encode($mediaArray,JSON_UNESCAPED_UNICODE);
        $deleteMediaOnClick = "'adminDeleteData(".$deleteMedia.")'";	

		$dropdown = '
		  <a class="dropdown-item" style="font-size: 13px" target="_blank" href="'.$sm['config']['site_url'].'@'.$value['u_id'].'">Open live profile</a>
		  <a class="dropdown-item" style="font-size: 13px" href="'.$sm['config']['site_url'].'index.php?page=admin&p=user&id='.$value['u_id'].'" target="_blank">Edit User</a>		  
		  <div class="dropdown-divider"></div>
		  '.$setAsProfilePhotoDropdown.'
		  '.$mediaPublicDropdown.'
		  '.$approveDropdown.'
		  '.$uploadToStoryDropdown.'
		  '.$uploadToProfileDropdown.'
		  '.$storyPriceDropdown.'
		  <a class="dropdown-item" href="#" style="font-size: 13px;color: #b50000" 
		  onclick='.$deleteMediaOnClick.'>
		  Delete media</a>'; 

		$userTd = '
	        <td style="min-width:180px;cursor:pointer;" onclick="searchMediaById('.$userId.')">
	            <div class="media align-items-center">
	                <div class="avatar avatar-sm mr-3" style="width:34px;height:34px;border-radius:50%">
	                    <img data-media-profile-photo="'.$userId.'" src="'.profilePhoto($userId).'" class="avatar-img rounded" style="border-radius:50%!important">
	                </div>
	                <div class="media-body">
	                    <strong class="js-lists-values-employee-name">'.$uName.'</strong><span class="badge badge-warning"></span><br>
	                    <span class="text-muted js-lists-values-employee-title">ID: '.$userId.'</span>
	                </div>
	            </div>
	        </td> 
		';

		if(strpos($_SERVER['HTTP_REFERER'], 'admin&p=user&id=') == true){
			$userTd = '';
		}

        $data[$i]='
            <tr class="data-search-verifications" data-media-i="'.$i.'" data-media-id="'.$id.'" data-media-id-story="'.$value['story'].'" data-media-type="'.$mediaType.'" data-media-src="'.$value['photo'].'">
                <td>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" onclick="checkData(this,'.$checkData.')" class="custom-control-input" data-check-search="'.$checkData.'" id="checkcall_'.$id.'">
                        <label class="custom-control-label" style="cursor: pointer;" for="checkcall_'.$id.'">
                        <span class="text-hide">Check</span></label>
                    </div>
                </td>
                '.$searchMedia.'
                <td style="min-width:110px">
                	<small class="badge badge-'.$mediatypeLabel.'">'.$mediaType.'</small>
                </td>                
                <td style="min-width:115px">
                	<small class="text-muted">'.time_elapsed_string($timeago).'</small>
                </td>  
                <td style="min-width:85px">
                	<small class="badge badge-'.$publicLabel.'" data-media-public="'.$id.'">
                		'.$mediaPublic.'
                	</small>
                </td>
                <td style="min-width:105px">
                	<small class="text-muted" id="approveMedia'.$id.'">'.$approved.'</small>
                </td>
                '.$userTd.'                                 
                <td>
                    <div class="dropdown ml-auto" data-table-dropdown>
                        <a href="#" data-toggle="dropdown"  data-caret="false" class="btn btn-light text-muted"><i class="material-icons">more_vert</i></a>
                        <div class="dropdown-menu dropdown-menu-right">                          
                            '.$dropdown.'
                        </div>
                    </div>
                </td>
            </tr>';

            $i++;
      }
    } else {
      $data = 'Nothing found';
    }

    $arr['data'] = $data;
    $arr['total'] = $i;
    echo json_encode($arr);
	break;	

	case 'getData':
		$table = secureEncode($_POST['table']);
		$col = secureEncode($_POST['col']);
		$filter = secureEncode($_POST['filter']);
		echo getData($table,$col,$filter);
	break;

	case 'updateMedia':

		$id = secureEncode($_POST['mediaId']);
		$storyId = secureEncode($_POST['mediaIdStory']);
		$type = secureEncode($_POST['mediaType']);	
		$method = secureEncode($_POST['method']);

		if($method == 'uploadToProfile'){
			$time = time();
			$uid = secureEncode($_POST['mediaUid']);
			$p = secureEncode($_POST['mediaPhoto']);
			$t = secureEncode($_POST['mediaThumb']);
			if($type == 'Video'){
				$video = 1;
				exit;
			} else {
				$video = 0;
			}
			$fake = getData('users','fake','WHERE id ='.$uid);
			if($type == 'Story'){
				/*$storyType = getData('users_story','storyType','WHERE id ='.$storyId);
				if($storyType == 'video'){
					$video = 1;
				} else {
					$video = 0;
				}
				*/
			}
			$mysqli->query("INSERT INTO users_photos (u_id,photo,thumb,approved,video,time,fake)
			VALUES ('".$uid."','".$p."', '".$t."',1,'".$video."','".$time."',".$fake.")");

			exit;	
		}

		if($method == 'changeCreditPrice'){
		/*	$val = secureEncode($_POST['val']);
			$mysqli->query('UPDATE users_story SET credits = "'.$val.'" WHERE id = "'.$storyId.'"');
			*/
			exit;
		}

		if($method == 'reUploadStory'){
			/*$time = time();
			$mysqli->query('UPDATE users_story SET storyTime = "'.$time.'" WHERE id = "'.$storyId.'"');
			$mysqli->query('UPDATE users_photos SET time = "'.$time.'" WHERE story = "'.$storyId.'"');
			*/
			exit;
		}

		if($method == 'uploadToStory'){
		/*	$time = time();
			$uid = secureEncode($_POST['mediaUid']);
			$p = secureEncode($_POST['mediaPhoto']);
			$t = secureEncode($_POST['mediaThumb']);
			if($type == 'Video'){
				$video = 'video';
			} else {
				$video = 'image';
			}
			$lat = getData('users','lat','WHERE id ='.$uid);
			$lng = getData('users','lng','WHERE id ='.$uid);
			$fake = getData('users','fake','WHERE id ='.$uid);
      		$query = "INSERT INTO users_story (uid,storyTime,story,storyType,lat,lng,review)
      			 VALUES ('".$uid."','".$time."','".$p."','".$video."','".$lat."','".$lng."','No')";
      		if ($mysqli->query($query) === TRUE) {
      			$last_id = $mysqli->insert_id;
				if($type == 'Video'){
					$video = 1;
				} else {
					$video = 0;
				}      			
				$mysqli->query("INSERT INTO users_photos (u_id,time,photo,thumb,video,story,approved,fake)
      			 VALUES ('".$uid."','".$time."','".$p."','".$p."','".$video."',".$last_id.",1,".$fake.")");	
      		}
*/
			exit;	
		}	

		$updateData = '';

		if($method == 'mediaSetPrivate'){
			$updateData = 'private = 1, blocked = 1';
		}

		if($method == 'mediaSetPublic'){
			$updateData = 'private = 0, blocked = 0';
		}

		if($method == 'approveMedia'){
			$val = secureEncode($_POST['val']);
			$updateData = 'approved = '.$val;
			if($storyId > 0){
				$mysqli->query('UPDATE users_story SET review = "No" WHERE id = "'.$storyId.'"');
			}
		}

		if($method == 'setAsProfilePhoto'){
			$mediaUid = secureEncode($_POST['mediaUid']);
			$mysqli->query('UPDATE users_photos SET profile = 0 WHERE u_id = '.$mediaUid);
			$updateData = 'profile = 1';
		}

		$mysqli->query('UPDATE users_photos SET '.$updateData.' WHERE id = "'.$id.'"');
	break;

	case 'search_videocalls':
    $data = array();
    $arr=array();
    $time = time()-300;

    $order = 'call_date';
    $filter = '';
    if(isset($_POST['uid'])){
    	$uid = secureEncode($_POST['uid']);
    	$filter = 'WHERE c_id = "'.$uid.'" OR r_id = "'.$uid.'"';
    }
    $search = getArray('videocall',$filter,$order.' desc','');
    $i=0;
    if(isset($search)){
      foreach ($search as $value) { 
        
        $onlineNow = '';

        $c_video = false;
        $r_video = false;

        if(!empty($value['c_id_video'])){
          $c_video = true;
        }
        if(!empty($value['r_id_video'])){
          $r_video = true;
        }       
        if($value['status'] == 1){
          $badge = '<span class="badge badge-success">ANSWERED</span>';
        } else {
          $badge = '<span class="badge badge-white">NOT ANSWERED</span>';
        }

        $c_photo = "'".profilePhoto($value['c_id'])."'";
        $r_photo = "'".profilePhoto($value['r_id'])."'";  
        $c_video = "'".$value['c_id_video']."'";  
        $r_video = "'".$value['r_id_video']."'";    

        if(!empty($value['c_id_video'])){
        	$cAvatar = '
            	<video class="avatar-img rounded"
            	onclick="playVideo('.$c_video.')" style="width:100%;height:100%;cursor:pointer;border:2px solid #fff">
            		<source src="'.$value['c_id_video'].'"/>
            	</video>
            	<div style="position:absolute;bottom:0px;left:-3px;z-index:9">
            		<i class="material-icons" style="color:#fff;font-size:24px;cursor:pointer" onclick="playVideo('.$c_video.')">play_arrow</i>
            	</div>
        	';
        }  else {
        	$cAvatar = '
        		<img src="'.profilePhoto($value['c_id']).'" class="avatar-img rounded">
        	';
        }
        if(!empty($value['r_id_video'])){
        	$rAvatar = '
            	<video class="avatar-img rounded"
            	onclick="playVideo('.$r_video.')" style="width:100%;height:100%;cursor:pointer;border:2px solid #fff">
            		<source src="'.$value['r_id_video'].'"/>
            	</video>
            	<div style="position:absolute;bottom:0px;left:-3px;z-index:9">
            		<i class="material-icons" style="color:#fff;font-size:24px;cursor:pointer" onclick="playVideo('.$r_video.')">play_arrow</i>
            	</div>
        	';
        }  else {
        	$rAvatar = '
        		<img src="'.profilePhoto($value['r_id']).'" class="avatar-img rounded">
        	';
        }

        if(empty($value['duration'])){
        	$duration = '00:00';
        } else {
        	$duration = $value['duration'];
        }
        $call_id = $value['call_date'];

        $onclickData = array();
        $onclickData['action'] = 'deleteVideocall';
        $onclickData['videocall'] = $call_id;

        $onclickData = json_encode($onclickData,JSON_UNESCAPED_UNICODE);

        $onclick = "'adminDeleteData(".$onclickData.")'";	
        $data[$i]='

            <tr>
                <td>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" onclick="checkData(this,'.$call_id.')" class="custom-control-input" data-check-search="'.$call_id.'" id="checkcall_'.$call_id.'">
                        <label class="custom-control-label" style="cursor: pointer;" for="checkcall_'.$call_id.'">
                        <span class="text-hide">Check</span></label>
                    </div>
                </td>

                <td>
                    <div class="media align-items-center">
                        <div class="avatar avatar-sm mr-3 ">
                            '.$cAvatar.'
                        </div>
                        <div class="media-body">
                        	<a href="'.$sm['config']['site_url'].'index.php?page=admin&p=user&id='.$value['c_id'].'" target="_blank" style="text-decoration:none;color:auto">
                            <strong class="js-lists-values-employee-name">'.getData('users','name','where id ='.$value['c_id']).' , '.getData('users','age','where id ='.$value['c_id']).'</strong> <span class="badge badge-warning"></span><br>
                            <span class="text-muted js-lists-values-employee-title"><small>'.getData('users','credits','where id ='.$value['c_id']).' credits</small></span>
                            </a>
                        </div>
                    </div>
                </td>
                
                <td>
                    <div class="media align-items-center">
                        <div class="avatar avatar-sm mr-3 ">
                            '.$rAvatar.'
                        </div>
                        <div class="media-body">
                        	<a href="'.$sm['config']['site_url'].'index.php?page=admin&p=user&id='.$value['r_id'].'" target="_blank" style="text-decoration:none;color:auto">                        
                            <strong class="js-lists-values-employee-name">'.getData('users','name','where id ='.$value['r_id']).' , '.getData('users','age','where id ='.$value['r_id']).'</strong><br>
                            <span class="text-muted js-lists-values-employee-title"><small>'.getData('users','credits','where id ='.$value['r_id']).' credits</small></span>    
                            </a>                        
                        </div>
                    </div>
                </td>                                                    
                <td>'.$duration.'</td>
                <td><small class="text-muted">'.time_elapsed_string($value['call_date']).'</small></td>
                <td>'.$badge.'</td>                                 
                <td>
                    <div class="dropdown ml-auto">
                        <a href="#" data-toggle="dropdown" data-caret="false" class="text-muted"><i class="material-icons">more_vert</i></a>
                        <div class="dropdown-menu dropdown-menu-right">                          
                            <a class="dropdown-item" href="#" onclick='.$onclick.'>Delete call</a>
                        </div>
                    </div>
                </td>
            </tr>';

            $i++;
      }
    } else {
      $data = 'Nothing found';
    }

    $arr['data'] = $data;
    $arr['total'] = $i;
    echo json_encode($arr);
	break;	

	case 'usearch':
		$data = secureEncode($_POST['dat']);
		echo searchUser($data);		
	break;	

	case 'testsmtp':
		$arr = array();
		$arr['response'] = testMailNotification();
		$arr['sent'] = 'Ok';
		if (strpos($arr['response'], 'Error') !== false) {
		    $arr['sent'] = 'Error';
		}		
		echo json_encode($arr);		
	break;	
	case 'lang_visible':
		$lang = secureEncode($_POST['id']);
		$val = secureEncode($_POST['val']);
		$mysqli->query("UPDATE languages SET visible = '".$val."' where id = '".$lang."'");	

	break;	

	case 'loadChatAdmin':
		$uid = secureEncode($_POST['uid']);
		$cid = secureEncode($_POST['cid']);

		echo getChatControlPanel($uid,$cid);
	break;

	case 'push':
		$title = secureEncode($_POST['app_push_title']);
		$body = secureEncode($_POST['app_push_body']);
		$image = secureEncode($_POST['app_push_image']);
		appUsers($title,$body,$image);	
	break;
	case 'apps':
		$llogo = secureEncode($_POST['app_logo_login']);
		$logo = secureEncode($_POST['app_logo']);
		$main = secureEncode($_POST['app_first_color']);
		$second = secureEncode($_POST['app_second_color']);
		$mysqli->query("UPDATE config_app SET first_color = '".$main."', second_color = '".$second."', logo = '".$logo."', logo_login = '".$llogo."'");		
	break;
	case 'fakeu':
		$visit = secureEncode($_POST['fakeu_visit']);
		$like = secureEncode($_POST['fakeu_like']);
		$fcountry = secureEncode($_POST['fakeu_country']);
		$fapi = secureEncode($_POST['fakeu_api']);
		$fAI = secureEncode($_POST['fakeu_ai']);
		$fAiChance = secureEncode($_POST['fakeu_respond']);			
		$visit = str_replace('%', '', $visit);
		$like = str_replace('%', '', $like);	
		$mysqli->query("UPDATE config SET fcountry = '".$fcountry."', visit_back = '".$visit."', like_back = '".$like."', fAI = '".$fAI."', fapi = '".$fapi."', fAiChance = '".$fAiChance."'");		
	break;
	case 'engage':
		$e = secureEncode($_POST['engage']);
		$et = secureEncode($_POST['engage_time']);
		$el = secureEncode($_POST['engage_limit']);
		$mysqli->query("UPDATE config SET fEngage = '".$e."', fEngageTime = '".$et."', fEngageLimit = '".$el."'");		
	break;	
	case 'updateThemeSettings':
		$theme = secureEncode($_POST['theme']);
		$s = secureEncode($_POST['setting']);
		$sval = secureEncode($_POST['setting_val']);				
		$mysqli->query("UPDATE theme_settings SET setting_val = '".$sval."' where setting = '".$s."' and theme = '".$theme."'");			
	break;
	case 'aikey':
		$aikey = secureEncode($_POST['fakeu_aiapikey']);
		$mysqli->query("UPDATE config SET fapiKey = '".$aikey."'");		
	break;		
	case 'edit_u':
		$uid = secureEncode($_POST['edit_id']);	
		$name = secureEncode($_POST['edit_name']);
		$email = secureEncode($_POST['edit_email']);
		$age = secureEncode($_POST['edit_age']);
		$city = secureEncode($_POST['edit_city']);
		$country = secureEncode($_POST['edit_country']);
		$premium = secureEncode($_POST['edit_premium']);		
		$gender = secureEncode($_POST['edit_gender']);
		$lang = secureEncode($_POST['edit_lang']);
		$credits = secureEncode($_POST['edit_credits']);		
		$admin = secureEncode($_POST['edit_admin']);
		$verified = secureEncode($_POST['edit_verified']);		
		$mysqli->query("UPDATE users SET name = '".$name."' , email = '".$email."' , city = '".$city."', country = '".$country."',
					   age = '".$age."', gender = '".$gender."', credits = '".$credits."',
					   lang = '".$lang."', admin = '".$admin."', verified = '".$verified."' WHERE id = '".$uid."'");
		if($premium != ''){	
			$time = time();	
			$extra = 86400 * $premium;
			$premium = $time + $extra;
			$mysqli->query("UPDATE users_premium set premium = '".$premium."' where uid = '".$uid."' ");
		}
	break;	

	
	case 'approveUserVerification':
		$uid = secureEncode($_POST['uid']);
		$approve = secureEncode($_POST['approve']);
		if($approve == 1){
			$mysqli->query("UPDATE users SET verified = 1 where id = '".$uid."'");
			$mysqli->query("UPDATE users_verification SET verify = 1,status = 'Approved' where uid = '".$uid."'");
		} else {
			$mysqli->query("UPDATE users_verification SET verify = 0,status = 'Denied' where uid = '".$uid."'");	
		}
				
	break;	
	case 'removeFromReportList':
		$uid = secureEncode($_POST['uid']);
		$mysqli->query("UPDATE reports SET viewed = 1 where reported = '".$uid."'");
	break;
	
	
	
		case 'removeFromChatReportList':
		$uid = secureEncode($_POST['uid']);
		$mysqli->query("UPDATE reports_chats SET viewed = 1 where id = '".$uid."'");
	break;

	case 'withdrawComplete':
		$id = secureEncode($_POST['id']);
		$mysqli->query("UPDATE users_withdraw SET status = 'Complete' where id = '".$id."'");	
	break;

	case 'withdrawCanceled':
		$id = secureEncode($_POST['id']);
		$mysqli->query("UPDATE users_withdraw SET status = 'Canceled' where id = '".$id."'");	
	break;	
	
	case 'editlang':
		$langid = secureEncode($_POST['langid']);
		$val = secureEncode($_POST['val']);
		$lid = secureEncode($_POST['lid']);
		$table = secureEncode($_POST['table']);
		$landingPreset = secureEncode($_POST['landingPreset']);
		$landing = secureEncode($_POST['landing']);
		$theme = secureEncode($_POST['theme']);
		if(!empty($theme) && $theme != 'No'){

			if($landing == $theme){
				$mysqli->query("UPDATE $table SET text = '$val' where id = '$lid' and lang_id = '$langid' and theme = '$theme' and preset = '$landingPreset'");
			} else {
				$mysqli->query("UPDATE $table SET text = '$val' where id = '$lid' and lang_id = '$langid' and theme = '$theme'");
			}
		} else {
			$mysqli->query("UPDATE $table SET text = '$val' where id = '$lid' and lang_id = '$langid'");	
		}
		
	break;
	case 'editemaillang':
		$langid = secureEncode($_POST['langid']);
		$val = secureEncode($_POST['val']);
		$lid = secureEncode($_POST['lid']);
		$mysqli->query("UPDATE email_lang SET text = '$val' where id = '$lid' and lang_id = '$langid'");
	break;	
	case 'editlangt':
		$langid = secureEncode($_POST['langid']);
		$val = secureEncode($_POST['val']);
		$lid = secureEncode($_POST['lid']);
		$mysqli->query("UPDATE twoo_lang SET text = '$val' where id = '$lid' and lang_id = '$langid'");
	break;	
	case 'editlanga':
		$langid = secureEncode($_POST['langid']);
		$val = secureEncode($_POST['val']);
		$lid = secureEncode($_POST['lid']);
		$mysqli->query("UPDATE app_lang SET text = '$val' where id = '$lid' and lang_id = '$langid'");
	break;
	case 'editlangseo':
		$langid = secureEncode($_POST['langid']);
		$val = secureEncode($_POST['val']);
		$lid = secureEncode($_POST['id']);
		$page = secureEncode($_POST['page']);
		$mysqli->query("UPDATE seo_lang SET text = '$val' where id = '$lid' and lang_id = '$langid' and page = '$page'");
	break;
	case 'editlanglanding':
		$langid = secureEncode($_POST['langid']);
		$val = secureEncode($_POST['val']);
		$lid = secureEncode($_POST['lid']);
		$page = secureEncode($_POST['page']);
		$mysqli->query("UPDATE app_lang SET text = '$val' where id = '$lid' and lang_id = '$langid' and page = '$page'");
	break;		
	case 'editlanggender':
		$langid = secureEncode($_POST['langid']);
		$val = secureEncode($_POST['val']);
		$id = secureEncode($_POST['id']);
		$mysqli->query("UPDATE config_genders SET name = '$val' where id = '$id' and lang_id = '$langid'");
	break;
	case 'editlangq':
		$langid = secureEncode($_POST['langid']);
		$val = secureEncode($_POST['val']);
		$id = secureEncode($_POST['id']);
		$mysqli->query("UPDATE config_profile_questions SET question = '$val' where id = '$id' and lang_id = '$langid'");
	break;
	case 'editlanganswer':
		$langid = secureEncode($_POST['langid']);
		$val = secureEncode($_POST['val']);
		$id = secureEncode($_POST['id']);
		$qid = secureEncode($_POST['qid']);
		$mysqli->query("UPDATE config_profile_answers SET answer = '$val' where id = '$id' and qid = '$qid' and lang_id = '$langid'");
	break;				
	case 'gift':
		$giftid = secureEncode($_POST['giftid']);
		$val = secureEncode($_POST['val']);
		$mysqli->query("UPDATE gifts SET price = '$val' where id = '$giftid'");
	break;	
	case 'change_theme':
		$col = secureEncode($_POST['col']);
		$folder = secureEncode($_POST['folder']);
		$mysqli->query("UPDATE config SET $col = '$folder'");
	break;		
	case 'website':
		$name = secureEncode($_POST['site_name']);
		$email = secureEncode($_POST['site_email']);		
		$title = secureEncode($_POST['site_title']);
		$desc = secureEncode($_POST['site_desc']);
		$keywords = secureEncode($_POST['site_keywords']);
		$lang = secureEncode($_POST['site_lang']);
		$review = secureEncode($_POST['site_photo_review']);
		$email_verification = secureEncode($_POST['site_email_verification']);
		$credits = secureEncode($_POST['site_free_credits']);
		$premium = secureEncode($_POST['site_free_premium']);
		$wm = secureEncode($_POST['site_wm']);
		$dc = secureEncode($_POST['site_dc']);		
		$logo = secureEncode($_POST['site_logo']);
		$logoL = secureEncode($_POST['site_logo_landing']);
		$mobile = secureEncode($_POST['site_mobile']);	
		$mysqli->query("UPDATE config SET name = '$name', email = '$email', photo_review = '$review', title = '$title', description = '$desc', keywords = '$keywords', lang = '$lang', logo = '$logo', email_verification = '$email_verification', free_credits = '$credits', free_premium = '$premium', logo_landing = '$logoL', mobile_site = '$mobile', wm = '$wm', dc = '$dc'");
	break;
	case 'updateAnswer':
		$q = secureEncode($_POST['qid']);
		$a = secureEncode($_POST['answer']);
		$id = secureEncode($_POST['answerId']);
		if($a == ''){
			$mysqli->query("DELETE FROM config_profile_answers where id = '".$id."' and qid = '".$q."'");				
		} else {
			$query = $mysqli->query("SELECT * FROM languages order by id ASC");
				if ($query->num_rows > 0) { 
				while($re = $query->fetch_object()){  
					$mysqli->query("INSERT INTO config_profile_answers (id,qid,answer,lang_id)
					VALUES ('".$id."','".$q."','".$a."','".$re->id."') ON DUPLICATE KEY UPDATE answer = '".$a."'");		
				}
			}							
		}
		echo getAbsolutePageAdmin('questionsAjax');
	break;
	case 'smtp':
		$host = secureEncode($_POST['email_host']);
		$port = secureEncode($_POST['email_port']);		
		$username = secureEncode($_POST['email_email']);
		$password = secureEncode($_POST['email_pswd']);		
		$mysqli->query("UPDATE config_email SET host = '$host', port = '$port', user = '$username', password = '$password'");
	break;

	case 'rt':
		$pusher_id = secureEncode($_POST['pusher_id']);
		$pusher_key = secureEncode($_POST['pusher_key']);		
		$pusher_secret = secureEncode($_POST['pusher_secret']);
		$pusher_clauster = secureEncode($_POST['pusher_clauster']);		
		$mysqli->query("UPDATE config SET pusher_id = '$pusher_id', pusher_key = '$pusher_key', pusher_secret = '$pusher_secret', pusher_clauster = '$pusher_clauster'");
	break;		
	case 'vserver':
		$host = secureEncode($_POST['videocall_host']);	
		$mysqli->query("UPDATE config SET videocall = '$host'");
	break;			
	case 'social-connect':
		$id = secureEncode($_POST['fb_id']);	
		$key = secureEncode($_POST['fb_key']);	
		$google_key = secureEncode($_POST['google_key']);	
		$google_secret = secureEncode($_POST['google_secret']);	
		$twitter_key = secureEncode($_POST['twitter_key']);	
		$twitter_secret = secureEncode($_POST['twitter_secret']);	
		$instagram_key = secureEncode($_POST['instagram_key']);	
		$instagram_secret = secureEncode($_POST['instagram_secret']);	
		$mysqli->query("UPDATE config SET fb_app_id = '$id', fb_app_secret = '$key', twitter_key = '$twitter_key', twitter_secret = '$twitter_secret',
		instagram_key = '$instagram_key',instagram_secret = '$instagram_secret', google_key = '$google_key', google_secret = '$google_secret'");
	break;	
	case 'paypal':
		$id = secureEncode($_POST['site_paypal']);			
		$mysqli->query("UPDATE config SET paypal = '$id'");
	break;	
	case 'geokey':
		$id = secureEncode($_POST['google_maps']);			
		$mysqli->query("UPDATE config SET google_maps = '$id'");
	break;		
	case 'fortumo':
		$id = secureEncode($_POST['site_fortumo_service']);	
		$secret = secureEncode($_POST['site_fortumo_secret']);			
		$mysqli->query("UPDATE config SET fortumo_service = '$id', fortumo_secret = '$secret'");
	break;
	case 'stripe':
		$id = secureEncode($_POST['site_stripe_pub']);	
		$secret = secureEncode($_POST['site_stripe_secret']);			
		$mysqli->query("UPDATE config SET stripe_pub = '$id', stripe_secret = '$secret'");
	break;	
	case 'paygol':
		$id = secureEncode($_POST['site_paygol']);			
		$mysqli->query("UPDATE config SET paygol = '$id'");
	break;
	case 'currency':
		$id = secureEncode($_POST['site_currency']);			
		$mysqli->query("UPDATE config SET currency = '$id'");
	break;	
	case 'prices':
		$p1 = secureEncode($_POST['site_price_private']);	
		$p2 = secureEncode($_POST['site_price_spotlight']);	
		$p3 = secureEncode($_POST['site_price_chat']);	
		$p4 = secureEncode($_POST['site_price_boost']);	
		$p5 = secureEncode($_POST['site_price_discover']);	
		$p6 = secureEncode($_POST['site_price_first']);			
		$mysqli->query("UPDATE config_prices SET private = '$p1', spotlight = '$p2', chat = '$p3', boost = '$p4', discover = '$p5', first = '$p6'");
	break;	
	case 's3':
		$p1 = secureEncode($_POST['s3_bucket']);	
		$p2 = secureEncode($_POST['s3_key']);	
		$p3 = secureEncode($_POST['s3_secret']);			
		$mysqli->query("UPDATE config SET s3_bucket = '$p1', s3 = '$p2', s3_key = '$p3'");
	break;		
	case 'credits':
		$c1 = secureEncode($_POST['credits1']);	
		$c2 = secureEncode($_POST['credits2']);	
		$c3 = secureEncode($_POST['credits3']);	
		$c4 = secureEncode($_POST['credits4']);	
		$c5 = secureEncode($_POST['credits5']);			
		$mysqli->query("UPDATE config_credits SET price = '$c1' where id = 1");
		$mysqli->query("UPDATE config_credits SET price = '$c2' where id = 2");
		$mysqli->query("UPDATE config_credits SET price = '$c3' where id = 3");
		$mysqli->query("UPDATE config_credits SET price = '$c4' where id = 4");
		$mysqli->query("UPDATE config_credits SET price = '$c5' where id = 5");		
	break;	
	case 'premium':
		$c1 = secureEncode($_POST['premium1']);	
		$c2 = secureEncode($_POST['premium2']);	
		$c3 = secureEncode($_POST['premium3']);			
		$mysqli->query("UPDATE config_premium SET price = '$c1' where id = 1");
		$mysqli->query("UPDATE config_premium SET price = '$c2' where id = 2");
		$mysqli->query("UPDATE config_premium SET price = '$c3' where id = 3");		
	break;	
	case 'premium_acc':
		$c1 = secureEncode($_POST['site_premium_chat']);	
		$c2 = secureEncode($_POST['site_premium_videocall']);	
		$c3 = secureEncode($_POST['site_premium_private']);
		$c4 = secureEncode($_POST['site_premium_fans']);	
		$c5 = secureEncode($_POST['site_premium_visits']);
		$c6 = secureEncode($_POST['site_premium_mobile_ads']);		
		$mysqli->query("UPDATE config_accounts SET chat = '$c1' , videocall = '$c2' , private = '$c3', fans = '$c4', visits = '$c5', mobile_ads = '$c6' where type = 2");	
	break;
	case 'basic_acc':
		$c1 = secureEncode($_POST['site_basic_chat']);	
		$c2 = secureEncode($_POST['site_basic_videocall']);	
		$c3 = secureEncode($_POST['site_basic_private']);
		$c4 = secureEncode($_POST['site_basic_fans']);	
		$c5 = secureEncode($_POST['site_basic_visits']);
		$c6 = secureEncode($_POST['site_basic_mobile_ads']);		
		$mysqli->query("UPDATE config_accounts SET chat = '$c1' , videocall = '$c2' , private = '$c3', fans = '$c4', visits = '$c5', mobile_ads = '$c6' where type = 1");	
	break;	
		
	case 'login':
		$id = secureEncode($_POST['id']);	
		$password = secureEncode($_POST['pass']);			
		$user_check = $mysqli->query("SELECT * FROM users WHERE name = '".$id."'");
		if($user_check->num_rows == 0 ){
			echo 0;
			exit;
		}
		$pass = $user_check->fetch_object();
		if($password == $pass->screen_name) { 
			if($pass->admin == 1){
				$_SESSION['user'] = $pass->id;
				echo 1; 
			}else{
				echo 0; 
			}
			exit;	
		} else {
			echo 0;	
			exit;		
		}
	break;		
	case 'photo':
		$pid = secureEncode($_POST['photoid']);
		$m = secureEncode($_POST['method']);
		if($m == 1){
			$mysqli->query("UPDATE users_photos SET approved = 1 WHERE id ='$pid'");	
		}
		if($m == 2){
			$mysqli->query("UPDATE users_photos SET approved = 2 WHERE id ='$pid'");	
		}		
		if($m == 3){				
			$mysqli->query("UPDATE users_photos SET approved = 1 , blocked = 1 WHERE id ='$pid'");	
		}
	break;

	case 'unbanEmail':
		$id = secureEncode($_POST['email']);
		$email = getData('blocked_users','email','where id ='.$id);
		$mysqli->query('DELETE FROM blocked_users WHERE id = '.$id);

		if($sm['plugins']['logActivity']['enabled'] == 'Yes'){ 
			$activity = 'Email '.$email.' has been unbanned by '.$sm['user']['name'];
			activity('system',$activity,'Unbanned '.$email.'');	
		}	
	break;

	case 'deleteMedia':
		$id = secureEncode($_POST['mediaId']);
		$type = secureEncode($_POST['mediaType']);
		
		$mediaPhoto = getData('users_photos','photo','where id = '.$id);
		$mediaPhoto = str_replace($sm['config']['site_url'], '../', $mediaPhoto);
		$mediaThumb = getData('users_photos','thumb','where id = '.$id);
		$mediaThumb = str_replace($sm['config']['site_url'], '../', $mediaThumb);
		unlink($mediaPhoto);
		unlink($mediaThumb);

		$mysqli->query('DELETE FROM users_photos WHERE id = '.$id);

		if($type == 'Story'){
			$mysqli->query('DELETE FROM users_story WHERE id = '.secureEncode($_POST['mediaIdStory']));
		}

		if($sm['plugins']['logActivity']['enabled'] == 'Yes'){ 
			$activity = 'Media '.$id.' has been deleted';
			activity('system',$activity,'Media deleted');	
		}	
	break;	
	case 'delete_messages_adm':
	    $arr=array();
	    $year=$_POST['year'];
	    $month=$_POST['month'];
	     $maxid=intval($_POST['maxid']);
	     $limit=intval($_POST['perpage']);
	    
	    $date="01-$month-$year";
	  $timestamp=  strtotime($date);
	  
    $timestamp1 = time(); 
    $timestamp2 = $timestamp; 
    

    
    if($timestamp2 > $timestamp1 ){
        $arr['success']=1;
        $arr['maxid']='';
        $arr['message']="Future date is not allowed";
        echo json_encode($arr); 
        exit;
        
    }
    
    $date1 = DateTime::createFromFormat('U', $timestamp1);
    $date2 = DateTime::createFromFormat('U', $timestamp2);
    
    
        $interval = $date1->diff($date2);
        
        $totalMonths = $interval->y * 12 + $interval->m;
        
       // var_dump($totalMonths);die;
        
        if ($totalMonths <= 4) {
            $arr['success']=1;
            $arr['maxid']='';
            $arr['message']="Cannot delete data newer than 6 months";
            echo json_encode($arr); 
        exit;
        }

	  
	  if(empty($maxid)){
            $sql="SELECT MAX(id) as maxid FROM `chat` where time < $timestamp";
          //die;
            $data_check = $mysqli->query($sql);
            $data = $data_check->fetch_object();
            $maxid=$data_check->maxid;
        }
			if(empty($maxid)){
			        $arr['success']=1;
		             $arr['maxid']='';
		             $arr['message']="Data Deletion is Complete";
			}else{
	
                 $arr['success']=1;
                  $arr['maxid']=	$maxid;
                   $arr['message']='Data Deletion in progress';
                  $mysqli->query("DELETE FROM `chat`  WHERE id  <=".$maxid);
                
                  if( $mysqli->affected_rows <=0){
                      $arr['maxid']='';
                       $arr['message']="Data Deletion is Complete";
                  }
			}
	    	echo json_encode($arr); 
	    break;
	case 'unbanIP':
		$ip = secureEncode($_POST['ip']);
		$mysqli->query('DELETE FROM blocked_ips WHERE ip = "'.$ip.'"');

		if($sm['plugins']['logActivity']['enabled'] == 'Yes'){ 
			$activity = 'IP '.$ip.' has been unbanned by '.$sm['user']['name'];
			activity('system',$activity,'Unbanned IP '.$ip);	
		}	
	break;		

	case 'delete_profile':
		$uid = secureEncode($_POST['uid']);
		$banData = secureEncode($_POST['ban']);
		$dataBan = explode(',',$banData);
		$ban = $dataBan[0];
		$val = $dataBan[1];
		
	//	var_dump($uid);
	//	die;
		
		$time = time();
		$rand = rand(0,9999).$time;
		if($uid == $sm['user']['id']){
			exit;
		}
		
				///ban both starts
		if($ban=='bulkbanreport'){
		    
		    	$uid = getData('reports_chats','reported','where id ='.$uid);
				$val = getData('users','email','where id ='.$uid);
				$ip = getData('users','ip','where id ='.$uid);
			$mysqli->query("INSERT INTO blocked_users(id,email,banned_date,banned_by,ip) VALUES
				('".$rand."','".$val."','".$time."','".$sm['user']['id']."','".$ip."')");
			$mysqli->query("INSERT INTO blocked_ips(id,ip,banned_date,banned_by) VALUES
				('".$rand."','".$ip."','".$time."','".$sm['user']['id']."')");
				
		    
		}//end if ban both ends 
		
		
		
		
		///ban both starts
		if($ban=='both'){
		    
		    	if($val == 'No'){
		    	    
				$val = getData('users','email','where id ='.$uid);
				$ip = getData('users','ip','where id ='.$uid);
			} else {
				$ip = $dataBan[2];
			}
		
			$mysqli->query("INSERT INTO blocked_users(id,email,banned_date,banned_by,ip) VALUES
				('".$rand."','".$val."','".$time."','".$sm['user']['id']."','".$ip."')");
				
				
					if($val == 'No'){
				$val = getData('users','ip','where id ='.$uid);
			}
			$mysqli->query("INSERT INTO blocked_ips(id,ip,banned_date,banned_by) VALUES
				('".$rand."','".$dataBan[2]."','".$time."','".$sm['user']['id']."')");
				
		    
		}//end if ban both ends 
		
		
		
		
		if($ban == 'email'){
			if($val == 'No'){
				$val = getData('users','email','where id ='.$uid);
				$ip = getData('users','ip','where id ='.$uid);
			} else {
				$ip = $dataBan[2];
			}
			
			$mysqli->query("INSERT INTO blocked_users(id,email,banned_date,banned_by,ip) VALUES
				('".$rand."','".$val."','".$time."','".$sm['user']['id']."','".$ip."')");
		}	
		if($ban == 'ip'){
			if($val == 'No'){
				$val = getData('users','ip','where id ='.$uid);
			}
			$mysqli->query("INSERT INTO blocked_ips(id,ip,banned_date,banned_by) VALUES
				('".$rand."','".$val."','".$time."','".$sm['user']['id']."')");
		}	



		$mysqli->query("DELETE FROM reports WHERE reported = '".$uid."'");	
		$mysqli->query("DELETE FROM users WHERE id = '".$uid."'");
		
		
        $mysqli->query("DELETE FROM fake_conversations WHERE user_id = '".$uid."'");
        $mysqli->query("DELETE FROM fake_conversations WHERE fake_user_id = '".$uid."'");
        
        $mysqli->query("DELETE FROM reports_chats WHERE reported = '".$uid."'");
        
        //also delete chat from chat queue 
        
        $sqlDelQ="DELETE FROM chat_queue WHERE s_id=$uid";
	    $mysqli->query($sqlDelQ);
				
			
		$mysqli->query("DELETE FROM spotlight WHERE u_id = '".$uid."'");
		$mysqli->query("DELETE FROM chat WHERE s_id = '".$uid."'");	
		$mysqli->query("DELETE FROM chat WHERE r_id = '".$uid."'");
		$mysqli->query("DELETE FROM users_visits WHERE u1 = '".$uid."'");	
		$mysqli->query("DELETE FROM users_visits WHERE u2 = '".$uid."'");			
		$mysqli->query("DELETE FROM users_likes WHERE u1 = '".$uid."'");
		$mysqli->query("DELETE FROM users_likes WHERE u2 = '".$uid."'");		
		$mysqli->query("DELETE FROM users_photos WHERE u_id = '".$uid."'");
		$mysqli->query("DELETE FROM users_profile_questions WHERE uid = '".$uid."'");
		$mysqli->query("DELETE FROM users_interest WHERE u_id = '".$uid."'");
		$mysqli->query("DELETE FROM users_chats WHERE uid = '".$uid."'");
		$mysqli->query("DELETE FROM users_withdraw WHERE u_id = '".$uid."'");
		$mysqli->query("DELETE FROM users_verification WHERE uid = '".$uid."'");
		$mysqli->query("DELETE FROM users_premium WHERE uid = '".$uid."'");	
		$mysqli->query("DELETE FROM users_videocall WHERE u_id = '".$uid."'");
		$mysqli->query("DELETE FROM users_story WHERE uid = '".$uid."'");
		$mysqli->query("DELETE FROM users_notifications WHERE uid = '".$uid."'");

		if($sm['plugins']['logActivity']['enabled'] == 'Yes'){ 
			$activity = 'User ID ('.$uid.') has been deleted from the database';
			activity('system',$activity,'Deleted '.$uid.'');	
		}	

		

	break;	


	case 'update_auto_message_like_settings':
			//echo "<pre>";print_r($_POST);echo "</pre>";
			
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['like_message_1'])."'  WHERE opt='like_message_1'");
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['like_message_2'])."'  WHERE opt='like_message_2'");
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['like_message_3'])."'  WHERE opt='like_message_3'");
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['like_message_4'])."'  WHERE opt='like_message_4'");
			
			
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['like_1'])."'  WHERE opt='like_1'");
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['like_2'])."'  WHERE opt='like_2'");
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['like_3'])."'  WHERE opt='like_3'");
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['like_4'])."'  WHERE opt='like_4'");
			
			
			
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['like_message_active'])."'  WHERE opt='like_message_active'");
			
			
			
			$activity = 'update_auto_message_like_settings updated successfully by '.$uid.' at '.date('d-m-Y H:i:s');
			activity('system',$activity,'Updated  '.$uid.'');	
			
				$arr['success'] = 1;
				$arr['errors'] = array('message'=>'Updated Successfully.');
				echo json_encode($arr);
				exit;	
	
	break;
	
	
	case 'update_auto_message_visit_settings':
			//echo "<pre>";print_r($_POST);echo "</pre>";
			
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['visit_message_1'])."'  WHERE opt='visit_message_1'");
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['visit_message_2'])."'  WHERE opt='visit_message_2'");
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['visit_message_3'])."'  WHERE opt='visit_message_3'");
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['visit_message_4'])."'  WHERE opt='visit_message_4'");
			
			
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['visit_1'])."'  WHERE opt='visit_1'");
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['visit_2'])."'  WHERE opt='visit_2'");
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['visit_3'])."'  WHERE opt='visit_3'");
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['visit_4'])."'  WHERE opt='visit_4'");
			
			
			
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['visit_message_active'])."'  WHERE opt='visit_message_active'");
			
			
			
			$activity = 'update_auto_message_visit_settings updated successfully by '.$uid.' at '.date('d-m-Y H:i:s');
			activity('system',$activity,'Updated  '.$uid.'');	
			
				$arr['success'] = 1;
				$arr['errors'] = array('message'=>'Updated Successfully.');
				echo json_encode($arr);
				exit;	
	
	break;
	
	///update_auto_message_match_settings
	case 'update_auto_message_match_settings':
			//echo "<pre>";print_r($_POST);echo "</pre>";
			
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['match_message_1'])."'  WHERE opt='match_message_1'");
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['match_message_2'])."'  WHERE opt='match_message_2'");
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['match_message_3'])."'  WHERE opt='match_message_3'");
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['match_message_4'])."'  WHERE opt='match_message_4'");
			
			
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['match_1'])."'  WHERE opt='match_1'");
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['match_2'])."'  WHERE opt='match_2'");
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['match_3'])."'  WHERE opt='match_3'");
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['match_4'])."'  WHERE opt='match_4'");
			
			
			
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['match_message_active'])."'  WHERE opt='match_message_active'");
			
			
			
			$activity = 'update_auto_message_visit_settings updated successfully by '.$uid.' at '.date('d-m-Y H:i:s');
			activity('system',$activity,'Updated  '.$uid.'');	
			
				$arr['success'] = 1;
				$arr['errors'] = array('message'=>'Updated Successfully.');
				echo json_encode($arr);
				exit;	
	
	break;
	
	
	
	case 'update_auto_message_welcome_settings':
			/* echo "<pre>";
			 print_r($_POST);
			 print_r($_FILES);
			 echo "</pre>";*/
			 $welcome_message_1 = $welcome_message_2 = $welcome_message_3 = $welcome_message_4 = '';
			 if($_POST['welcome_msg_type_1']=="image")
			 {
				 if( $_FILES['welcome_file_1']['name']!=""){
						$welcome_message_1 =  upload_welcome_image('welcome_file_1');
				 }else{
						$welcome_message_1 = $_POST['welcome_file_1_hidden'];
				 }
				 
			 }else
			 {
				 $welcome_message_1 =  mysqli_escape_string($mysqli,$_POST['welcome_message_1']);
			 }
			 
			  
			 if($_POST['welcome_msg_type_2']=="image")
			 {
				if( $_FILES['welcome_file_2']['name']!=""){
						$welcome_message_2 =  upload_welcome_image('welcome_file_2');
				 }else{
						$welcome_message_2 = $_POST['welcome_file_2_hidden'];
				 }
				  
				 
			 }else
			 {
				 $welcome_message_2 =  mysqli_escape_string($mysqli,$_POST['welcome_message_2']);
			 }
			 
			 if($_POST['welcome_msg_type_3']=="image")
			 {
				if( $_FILES['welcome_file_3']['name']!=""){
						$welcome_message_3 =  upload_welcome_image('welcome_file_3');
				 }else{
						$welcome_message_3 = $_POST['welcome_file_3_hidden'];
				 }
				  
				 
			 }else
			 {
				 $welcome_message_3 =  mysqli_escape_string($mysqli,$_POST['welcome_message_3']);
			 }
			 
			  if($_POST['welcome_msg_type_4']=="image")
			 {
				 if( $_FILES['welcome_file_4']['name']!=""){
						$welcome_message_4 =  upload_welcome_image('welcome_file_4');
				 }else{
						$welcome_message_4 = $_POST['welcome_file_4_hidden'];
				 }
				  
				 
			 }else
			 {
				 $welcome_message_4 =  mysqli_escape_string($mysqli,$_POST['welcome_message_4']);
			 }
			 
			 
			
			$mysqli->query("update auto_message_options set  value='".$welcome_message_1."'  WHERE opt='welcome_message_1'");
			$mysqli->query("update auto_message_options set  value='".$welcome_message_2."'  WHERE opt='welcome_message_2'");
			$mysqli->query("update auto_message_options set  value='".$welcome_message_3."'  WHERE opt='welcome_message_3'");
			$mysqli->query("update auto_message_options set  value='".$welcome_message_4."'  WHERE opt='welcome_message_4'");
			
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['welcome_msg_type_1'])."'  WHERE opt='welcome_msg_type_1'");
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['welcome_msg_type_2'])."'  WHERE opt='welcome_msg_type_2'");
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['welcome_msg_type_3'])."'  WHERE opt='welcome_msg_type_3'");
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['welcome_msg_type_4'])."'  WHERE opt='welcome_msg_type_4'");
			
			
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['welcome_1'])."'  WHERE opt='welcome_1'");
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['welcome_2'])."'  WHERE opt='welcome_2'");
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['welcome_3'])."'  WHERE opt='welcome_3'");
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['welcome_4'])."'  WHERE opt='welcome_4'");
			
			
			
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['welcome_message_active'])."'  WHERE opt='welcome_message_active'");
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['welcome_fake_user'])."'  WHERE opt='welcome_fake_user'");
			
			
			
			$activity = 'update_auto_message_welcome_settings updated successfully by '.$uid.' at '.date('d-m-Y H:i:s');
			activity('system',$activity,'Updated  '.$uid.'');	
			
				$arr['success'] = 1;
				$arr['errors'] = array('message'=>'Updated Successfully.');
				echo json_encode($arr);
				exit;	
	
	break;
	
	case 'update_auto_message_welcome_settings_new':
			/* echo "<pre>";
			 print_r($_POST);
			 print_r($_FILES);
			 echo "</pre>";*/
			
			$index=intval($_POST['index']).'_';

			 $welcome_message_1 = $welcome_message_2 = $welcome_message_3 = $welcome_message_4 = '';
			 if($_POST['welcome_'.$index.'msg_type_1']=="image")
			 {
				 if( $_FILES['welcome_'.$index.'file_1']['name']!=""){
						$welcome_message_1 =  upload_welcome_image('welcome_'.$index.'file_1');
				 }else{
						$welcome_message_1 = $_POST['welcome_'.$index.'file_1_hidden'];
				 }
				 
			 }else
			 {
				 $welcome_message_1 =  mysqli_escape_string($mysqli,$_POST['welcome_'.$index.'message_1']);
			 }
			 
			  
			 if($_POST['welcome_'.$index.'msg_type_2']=="image")
			 {
				if( $_FILES['welcome_'.$index.'file_2']['name']!=""){
						$welcome_message_2 =  upload_welcome_image('welcome_'.$index.'file_2');
				 }else{
						$welcome_message_2 = $_POST['welcome_'.$index.'file_2_hidden'];
				 }
				  
				 
			 }else
			 {
				 $welcome_message_2 =  mysqli_escape_string($mysqli,$_POST['welcome_'.$index.'message_2']);
			 }
			 
			 if($_POST['welcome_'.$index.'msg_type_3']=="image")
			 {
				if( $_FILES['welcome_'.$index.'file_3']['name']!=""){
						$welcome_message_3 =  upload_welcome_image('welcome_'.$index.'file_3');
				 }else{
						$welcome_message_3 = $_POST['welcome_'.$index.'file_3_hidden'];
				 }
				  
				 
			 }else
			 {
				 $welcome_message_3 =  mysqli_escape_string($mysqli,$_POST['welcome_'.$index.'message_3']);
			 }
			 
			  if($_POST['welcome_'.$index.'msg_type_4']=="image")
			 {
				 if( $_FILES['welcome_'.$index.'file_4']['name']!=""){
						$welcome_message_4 =  upload_welcome_image('welcome_'.$index.'file_4');
				 }else{
						$welcome_message_4 = $_POST['welcome_'.$index.'file_4_hidden'];
				 }
				  
				 
			 }else
			 {
				 $welcome_message_4 =  mysqli_escape_string($mysqli,$_POST['welcome_'.$index.'message_4']);
			 }
			 
			 
			
			$mysqli->query("update auto_message_options set  value='".$welcome_message_1."'  WHERE opt='welcome_".$index."message_1'");
			$mysqli->query("update auto_message_options set  value='".$welcome_message_2."'  WHERE opt='welcome_".$index."message_2'");
			$mysqli->query("update auto_message_options set  value='".$welcome_message_3."'  WHERE opt='welcome_".$index."message_3'");
			$mysqli->query("update auto_message_options set  value='".$welcome_message_4."'  WHERE opt='welcome_".$index."message_4'");
			
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['welcome_'.$index.'msg_type_1'])."'  WHERE opt='welcome_".$index."msg_type_1'");
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['welcome_'.$index.'msg_type_2'])."'  WHERE opt='welcome_".$index."msg_type_2'");
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['welcome_'.$index.'msg_type_3'])."'  WHERE opt='welcome_".$index."msg_type_3'");
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['welcome_'.$index.'msg_type_4'])."'  WHERE opt='welcome_".$index."msg_type_4'");
			
			
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['welcome_'.$index.'1'])."'  WHERE opt='welcome_".$index."1'");
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['welcome_'.$index.'2'])."'  WHERE opt='welcome_".$index."2'");
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['welcome_'.$index.'3'])."'  WHERE opt='welcome_".$index."3'");
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['welcome_'.$index.'4'])."'  WHERE opt='welcome_".$index."4'");
			
			
			
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['welcome_'.$index.'message_active'])."'  WHERE opt='welcome_".$index."message_active'");
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['welcome_'.$index.'fake_user'])."'  WHERE opt='welcome_".$index."fake_user'");
			
			
			
			$activity = 'update_auto_message_welcome_settings updated successfully by '.$uid.' at '.date('d-m-Y H:i:s');
			activity('system',$activity,'Updated  '.$uid.'');	
			
				$arr['success'] = 1;
				$arr['errors'] = array('message'=>'Updated Successfully.');
				echo json_encode($arr);
				exit;	
				
			

case 'update_auto_message_intrest_settings_new':
			/* echo "<pre>";
			 print_r($_POST);
			 print_r($_FILES);
			 echo "</pre>";*/
			
			$index=intval($_POST['index']).'_';

			 $intrest_message_1 = $intrest_message_2 = $intrest_message_3 = $intrest_message_4 = '';
			 if($_POST['intrest_'.$index.'msg_type_1']=="image")
			 {
				 if( $_FILES['intrest_'.$index.'file_1']['name']!=""){
						$intrest_message_1 =  upload_welcome_image('intrest_'.$index.'file_1','intrest');
				 }else{
						$intrest_message_1 = $_POST['intrest_'.$index.'file_1_hidden'];
				 }
				 
			 }else
			 {
				 $intrest_message_1 =  mysqli_escape_string($mysqli,$_POST['intrest_'.$index.'message_1']);
			 }
			 
			  
			 if($_POST['intrest_'.$index.'msg_type_2']=="image")
			 {
				if( $_FILES['intrest_'.$index.'file_2']['name']!=""){
						$intrest_message_2 =  upload_welcome_image('intrest_'.$index.'file_2','intrest');
				 }else{
						$intrest_message_2 = $_POST['intrest_'.$index.'file_2_hidden'];
				 }
				  
				 
			 }else
			 {
				 $intrest_message_2 =  mysqli_escape_string($mysqli,$_POST['intrest_'.$index.'message_2']);
			 }
			 
			 if($_POST['intrest_'.$index.'msg_type_3']=="image")
			 {
				if( $_FILES['intrest_'.$index.'file_3']['name']!=""){
						$intrest_message_3 =  upload_welcome_image('intrest_'.$index.'file_3','intrest');
				 }else{
						$intrest_message_3 = $_POST['intrest_'.$index.'file_3_hidden'];
				 }
				  
				 
			 }else
			 {
				 $intrest_message_3 =  mysqli_escape_string($mysqli,$_POST['intrest_'.$index.'message_3']);
			 }
			 
			  if($_POST['intrest_'.$index.'msg_type_4']=="image")
			 {
				 if( $_FILES['intrest_'.$index.'file_4']['name']!=""){
						$intrest_message_4 =  upload_welcome_image('intrest_'.$index.'file_4','intrest');
				 }else{
						$intrest_message_4 = $_POST['intrest_'.$index.'file_4_hidden'];
				 }
				  
				 
			 }else
			 {
				 $intrest_message_4 =  mysqli_escape_string($mysqli,$_POST['intrest_'.$index.'message_4']);
			 }
			 
			 
			
			$mysqli->query("update auto_message_options set  value='".$intrest_message_1."'  WHERE opt='intrest_".$index."message_1'");
			$mysqli->query("update auto_message_options set  value='".$intrest_message_2."'  WHERE opt='intrest_".$index."message_2'");
			$mysqli->query("update auto_message_options set  value='".$intrest_message_3."'  WHERE opt='intrest_".$index."message_3'");
			$mysqli->query("update auto_message_options set  value='".$intrest_message_4."'  WHERE opt='intrest_".$index."message_4'");
			
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['intrest_'.$index.'msg_type_1'])."'  WHERE opt='intrest_".$index."msg_type_1'");
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['intrest_'.$index.'msg_type_2'])."'  WHERE opt='intrest_".$index."msg_type_2'");
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['intrest_'.$index.'msg_type_3'])."'  WHERE opt='intrest_".$index."msg_type_3'");
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['intrest_'.$index.'msg_type_4'])."'  WHERE opt='intrest_".$index."msg_type_4'");
			
			
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['intrest_'.$index.'1'])."'  WHERE opt='intrest_".$index."1'");
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['intrest_'.$index.'2'])."'  WHERE opt='intrest_".$index."2'");
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['intrest_'.$index.'3'])."'  WHERE opt='intrest_".$index."3'");
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['intrest_'.$index.'4'])."'  WHERE opt='intrest_".$index."4'");
			
			
			
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['intrest_'.$index.'message_active'])."'  WHERE opt='intrest_".$index."message_active'");
			$mysqli->query("update auto_message_options set  value='".secureEncode($_POST['intrest_'.$index.'fake_user'])."'  WHERE opt='intrest_".$index."fake_user'");
			
			
			
			$activity = 'update_auto_message_intrest_settings updated successfully by '.$uid.' at '.date('d-m-Y H:i:s');
			activity('system',$activity,'Updated  '.$uid.'');	
			
				$arr['success'] = 1;
				$arr['errors'] = array('message'=>'Updated Successfully.');
				echo json_encode($arr);
				exit;	
		
				
	
	break;
	
}
//CLOSE DB CONNECTION
$mysqli->close();