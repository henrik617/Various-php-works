<?php
class MySQL {
        
        // Base variables
        var $lastError;                                        // Holds the last error
        var $lastQuery;                                        // Holds the last query
        var $result;                                                // Holds the MySQL query result
        var $records;                                                // Holds the total number of records returned
        var $affected;                                        // Holds the total number of records affected
        var $rawResults;                                // Holds raw 'arrayed' results
        var $arrayedResult;                        // Holds an array of the result
        
        var $hostname;        // MySQL Hostname
        var $username;        // MySQL Username
        var $password;        // MySQL Password
        var $database;        // MySQL Database
        
        var $databaseLink;                // Database Connection Link
        


        /* *******************
         * Class Constructor *
         * *******************/
        
        function MySQL($database, $username, $password, $hostname='localhost'){
                $this->database = $database;
                $this->username = $username;
                $this->password = $password;
                $this->hostname = $hostname;
                
                if(!$this->Connect()){
					echo "Error: mysql connection error!";
					exit;
				} else {
					return true;
				}
        }
        
        
        
        /* *******************
         * Private Functions *
         * *******************/
        
        // Connects class to database
        // $persistant (boolean) - Use persistant connection?
        private function Connect($persistant = false){
                $this->CloseConnection();
                
                if($persistant){
                        $this->databaseLink = mysql_pconnect($this->hostname, $this->username, $this->password);
                }else{
                        //$this->databaseLink = mysql_connect($this->hostname, $this->username, $this->password);
			$this->databaseLink = mysqli_connect($this->hostname, $this->username, $this->password, $this->database);

                }
				
				mysqli_set_charset($this->databaseLink, 'utf8');
                
                if(!$this->databaseLink){
                   $this->lastError = 'Could not connect to server: ' . mysql_error($this->databaseLink);
                        return false;
                }
                /*
                if(!$this->UseDB()){
                        $this->lastError = 'Could not connect to database: ' . mysql_error($this->databaseLink);
                        return false;
                }
		*/
                return true;
        }
        
        
        // Select database to use
        private function UseDB(){
                if(!mysql_select_db($this->database, $this->databaseLink)){
                        $this->lastError = 'Cannot select database: ' . mysql_error($this->databaseLink);
                        return false;
                }else{
                        return true;
                }
        }
        
        
        // Performs a 'mysql_real_escape_string' on the entire array/string
        private function SecureData($data){
                if(is_array($data)){
                        foreach($data as $key=>$val){
                                if(!is_array($data[$key])){
                                        //$data[$key] = mysql_real_escape_string($data[$key], $this->databaseLink);
					$data[$key] = mysqli_real_escape_string($this->databaseLink,$data[$key]);

                                }
                        }
                }else{
                        $data = mysqli_real_escape_string($this->databaseLink, $data);
                }
                return $data;
        }
        
        
        
        /* ******************
         * Public Functions *
         * ******************/
        
        // Executes MySQL query
        function ExecuteSQL($query){
                $this->lastQuery         = $query;
		/*
                if($this->result         = mysql_query($query, $this->databaseLink)){
                        $this->records         = @mysql_num_rows($this->result);
                        $this->affected        = @mysql_affected_rows($this->databaseLink);
                        
                        return true;
                        
                }else{
                        $this->lastError = mysql_error($this->databaseLink);
                        return false;
                }
		*/
		if($this->result         = mysqli_query($this->databaseLink, $query)){
                        $this->records         = @mysql_num_rows($this->result);
                        $this->affected        = @mysql_affected_rows($this->databaseLink);
                        
                        return true;
                        
                }else{
                        $this->lastError = mysqli_error($this->databaseLink);
                        return false;
                }
        }
        
        
        // Adds a record to the database based on the array key names
        function Insert($table, $vars, $exclude = ''){
                
                // Catch Exclusions
                if($exclude == ''){
                        $exclude = array();
                }
                
                array_push($exclude, 'MAX_FILE_SIZE'); // Automatically exclude this one
                
                // Prepare Variables
                $vars = $this->SecureData($vars);
                
                $query = "INSERT INTO `{$table}` SET ";
                foreach($vars as $key=>$value){
                        if(in_array($key, $exclude)){
                                continue;
                        }
                        //$query .= '`' . $key . '` = "' . $value . '", ';
                        $query .= "`{$key}` = '{$value}', ";
                }
                
                $query = substr($query, 0, -2);
                
                return $this->ExecuteSQL($query);
        }
        
        // Deletes a record from the database
        function Delete($table, $where='', $limit='', $like=false){
                $query = "DELETE FROM `{$table}` WHERE ";
                if(is_array($where) && $where != ''){
                        // Prepare Variables
                        $where = $this->SecureData($where);
                        
                        foreach($where as $key=>$value){
                                if($like){
                                        //$query .= '`' . $key . '` LIKE "%' . $value . '%" AND ';
                                        $query .= "`{$key}` LIKE '%{$value}%' AND ";
                                }else{
                                        //$query .= '`' . $key . '` = "' . $value . '" AND ';
                                        $query .= "`{$key}` = '{$value}' AND ";
                                }
                        }
                        
                        $query = substr($query, 0, -5);
                }
                
                if($limit != ''){
                        $query .= ' LIMIT ' . $limit;
                }
                
                return $this->ExecuteSQL($query);
        }
        
        
        // Gets a single row from $from where $where is true
        function Select($from, $where='', $orderBy='', $limit='', $like=false, $operand='AND',$cols='*'){
                // Catch Exceptions
                if(trim($from) == ''){
                        return false;
                }
                
                $query = "SELECT {$cols} FROM `{$from}` WHERE ";
            
                if(is_array($where) && $where != ''){
                        // Prepare Variables
                        $where = $this->SecureData($where);
                        
                        foreach($where as $key=>$value){
                                if($like){
                                        //$query .= '`' . $key . '` LIKE "%' . $value . '%" ' . $operand . ' ';
                                        $query .= "`{$key}` LIKE '%{$value}%' {$operand} ";
                                }else{
                                        //$query .= '`' . $key . '` = "' . $value . '" ' . $operand . ' ';
                                        $query .= "`{$key}` = '{$value}' {$operand} ";
                                }
                        }
                        
                        $query = substr($query, 0, -(strlen($operand)+2));

                }else{
                        $query = substr($query, 0, -6);
                }
                
                if($orderBy != ''){
                        $query .= ' ORDER BY ' . $orderBy;
                }
                
                if($limit != ''){
                        $query .= ' LIMIT ' . $limit;
                }
                return $this->ExecuteSQL($query);
                
        }
    // Gets a single row from $from where $where is true
    function SelectCustom($from, $where='', $orderBy='', $limit='', $like=false, $operand='AND',$cols='*'){
        // Catch Exceptions
        if(trim($from) == ''){
            return false;
        }
        
        $query = "SELECT {$cols} FROM `{$from}` WHERE {$where}";
        
        if($orderBy != ''){
            $query .= ' ORDER BY ' . $orderBy;
        }
        
        if($limit != ''){
            $query .= ' LIMIT ' . $limit;
        }
        
        return $this->ExecuteSQL($query);
        
    }
    
    
        // Updates a record in the database based on WHERE
        function Update($table, $set, $where, $exclude = ''){
                // Catch Exceptions
                if(trim($table) == '' || !is_array($set) || !is_array($where)){
                        return false;
                }
                if($exclude == ''){
                        $exclude = array();
                }
                
                array_push($exclude, 'MAX_FILE_SIZE'); // Automatically exclude this one
                
                $set                 = $this->SecureData($set);
                $where         = $this->SecureData($where);
                
                // SET
                
                $query = "UPDATE `{$table}` SET ";
                
                foreach($set as $key=>$value){
                        if(in_array($key, $exclude)){
                                continue;
                        }
                        $query .= "`{$key}` = '{$value}', ";
                }
                
                $query = substr($query, 0, -2);
                
                // WHERE
                
                $query .= ' WHERE ';
                
                foreach($where as $key=>$value){
                        $query .= "`{$key}` = '{$value}' AND ";
                }
                
                $query = substr($query, 0, -5);
                
                return $this->ExecuteSQL($query);
        }
        
        // 'Arrays' a single result
        function ArrayResult(){
                //$this->arrayedResult = mysql_fetch_assoc($this->result);
		$this->arrayedResult = $this->result->fetch_assoc();

                return $this->arrayedResult;
        }

        // 'Arrays' multiple result
        function ArrayResults(){
                              
                $this->arrayedResult = array();
                while ($data = $this->result->fetch_assoc()){
                        $this->arrayedResult[] = $data;
                }
                return $this->arrayedResult;
        }
        
        // 'Arrays' multiple results with a key
        function ArrayResultsWithKey($key='id'){
                if(isset($this->arrayedResult)){
                        unset($this->arrayedResult);
                }
                $this->arrayedResult = array();
                while($row = $this->result->fetch_assoc()){
                        foreach($row as $theKey => $theValue){
                                $this->arrayedResult[$row[$key]][$theKey] = $theValue;
                        }
                }
                return $this->arrayedResult;
        }

        // Returns last insert ID
        function LastInsertID(){
                return mysqli_insert_id($this->databaseLink);
        }

        // Return number of rows
        function CountRows($from, $where=''){
                $this->Select($from, $where, '', '', false, 'AND','count(*)');
				$result = $this->ArrayResult();
                return $result["count(*)"];
        }

        // Closes the connections
        function CloseConnection(){
                if($this->databaseLink){
                        mysqli_close($this->databaseLink);
                }
        }
}
?>