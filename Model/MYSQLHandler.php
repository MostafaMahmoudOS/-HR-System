<?php

class MYSQLHandler implements DbHandler
{

    private $_db_handler;
    private $_table;

    public function __construct($table)
    {
        $this->_table = $table;
       // $this->connect();
    }

    public function connect()
    {
        @$handler = mysqli_connect(__HOST__, __USER__, __PASS__, __DB__);
        // Check connection
        if ($handler) {
            $this->_db_handler = $handler;
            return true;
            // die("ERROR: Could not connect try later");
        }else {
            die("ERROR: Could not connect try later");
        return false;
        
      }
    }

    public function get_data($fields = array(), $start = 0)
    {
        $table = $this->_table;
        if (empty($fields)) {
            $sql = "select * from " . $table."where administrator=0";
        } else {
            $sql = "select ";
            foreach ($fields as $f) {
                $sql .= "`$f` ,";
            }
            $sql .= " from `$table` where administrator=0";
            $sql = str_replace(", from", "from", $sql);
        }
        $sql .= " limit $start," .__RECORDS_PER_PAGE__;
        return $this->get_results($sql);
    }

    public function get_all_data($fields = array())
    {
        $table = $this->_table;
        if (empty($fields)) {
            $sql = "select * from " . $table."where administrator=0";
        } else {
            $sql = "select ";
            foreach ($fields as $f) {
                $sql .= "`$f` ,";
            }
            $sql .= " from `$table` where administrator=0";
            $sql = str_replace(", from", "from", $sql);
        }
        return $this->get_results($sql);
    }

    public function disconnect()
    {
        if ($this->_db_handler) {
            mysqli_close($this->_db_handler);
        }
    }
    public function get_record_by_id($id, $primary_key)
    {
        $table = $this->_table;
        // $sql = "select * from `$table` where `$primary_key` = $id";
        $sql = "select * from `$table` where `$primary_key` = ? and administrator=0" ;
        return $this->get_results($sql, array($id)); //will not use mysqli_query use statment
        return $this->get_results($sql);
    }

    // private function get_results($sql)
    private function get_results($sql, $parameters = array())
    {
        //will not use mysqli_query use statment
        $this->debug($sql);
        $_handler_result="";
        // $_handler_result = mysqli_query($this->_db_handler, $sql); //mysqli_prepare instead
        $_arr_result = array();

        if ($stmt = mysqli_prepare($this->_db_handler, $sql)) {
            foreach ($parameters as $value) {
                if (is_numeric($value)) {
                    $stmt->bind_param("i", $value);
                } else {
                    $stmt->bind_param("s", $value);
                }

            }
            $stmt->execute();
            $_handler_result = $stmt->get_result();
           // echo var_dump($_handler_result);
        }
        if ($_handler_result) {
            while ($row = mysqli_fetch_array($_handler_result, MYSQLI_ASSOC)) {
                $_arr_result[] = array_change_key_case($row);
            }
            $this->disconnect();
            return $_arr_result;
        } else {
            $this->disconnect();
            return false;
        }

    }

    public function search($column, $column_value,$fields = array())
    {    $columns="";
        //$column_value=strtolower($column_value);
         if(empty($fields))
         {
            $columns="*";
         }else
         {
            foreach ($fields as $f) {
                $columns .= "$f ,";
            }
            $columns .= " from";
            $columns = str_replace(", from", "", $columns);
         }
        $table = $this->_table;
        $sql = "select $columns from `$table` where  LOWER( ".$column." )like  LOWER('" . $column_value ."%". "') and administrator=0";
        $this->debug($sql);
        
        return $this->get_results($sql);
    }

    public function save($new_value)
    {
        if (is_array($new_value)) {
            $table = $this->_table;
            $sql1 = "insert into `$table` (";
            $sql2 = "values (";
            foreach ($new_value as $key => $value) {
                $sql1 .= "`$key` ,";
                $sql2 .= "`$value` ,";
            }
            $sql1 = $sql1 . ")";
            $sql2 = $sql2 . ")";
            $sql1 = str_replace(",)", ")", $sql1);
            $sql2 = str_replace(",)", ")", $sql2);
            $sql = $sql1 . $sql2;
            $this->debug($sql);
            if (mysqli_query($this->_db_handler, $sql)) {
                $this->disconnect();
                return true;
            } else {
                $this->disconnect();
                return false;
            }

        }
    }

    private function debug($sql)
    {
        if (__DEBUG_MODE__ === 1) {
            echo "<h5> Sent Query </h5>" . $sql . "<br>";
        }
    }

    public function get_data_count()
    {
        $table = $this->_table;
        $sql = "select COUNT(*) from `$table` where administrator=0";
        $this->debug($sql);
        return $this->get_results($sql);
    }
   //test 
   public function export_users_excel($users)
   {
    //$result=$DB_conection->get_all_data(array("id","user_name","name","job"));
        foreach ($users as  $row)
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
    }
}
