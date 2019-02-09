<?php
   require_once("autoload.php");
   $DB_conection=new MYSQLHandler("users");
   $DB_conection->connect();
   $users=$DB_conection->get_all_data(array("id","user_name","name","job"));
    foreach ($result as  $row)
    {
 
        $rowData = '';  
        foreach ($row as $value) 
        {  
            $value = '"' . $value . '"' . "\t";  
            $rowData .= $value;  
        }  
        $setData .= trim($rowData) . "\n";  
     
    } 
    $columnHeader="ID"."\t"."USER_NAME"."\t"."NAME "."\t"."JOB";
    header("Content-type: application/octet-stream");  
    header("Content-Disposition: attachment; filename=User_Detail_Reoprt.xls");  
    header("Pragma: no-cache");  
    header("Expires: 0");  
    echo ucwords($columnHeader) . "\n" . $setData . "\n";  

?>