<?php


function getSecret($key){
include "/home/bkinney/includes/lti_mysqli.php";//just get fresh link	
$sql = sprintf("SELECT secret FROM blti_keys WHERE oauth_consumer_key='%s'",
         
                mysqli_real_escape_string($link,$key));
            // echo $sql;	
			$result = mysqli_query($link,$sql);
			
            $num_rows = mysqli_num_rows($result);
			
            if ( $num_rows != 1 ) {
                echo "Your consumer is not authorized oauth_consumer_key=".$key . " " . $sql;
                return;
           		 } else {
				$row = mysqli_fetch_assoc($result);
               
                   return $row['secret'];
					
                   
               }
                if ( ! is_string($secret) ) {
                    echo "Could not retrieve secret oauth_consumer_key=".$key;
                    return;
                }
				//mysql_close($link);
            //return $secret;
}
			?>