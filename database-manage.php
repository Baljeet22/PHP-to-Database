/**
 * Class db_manage
 * @version 1.0
 * @date 01/jul/2015
 * @author Baljeet Singh <baljeetroot@gmail.com>
 */
class db_manage
{
///////////////////////////////////////////////////////////////////////////////////////////////////////


    /**
     * @return array
     */
    function connectdb() //Database Connection Function
    {
        try {
            $db = new PDO('mysql:host=' . HOST . ';dbname=' . UDB_NAME . ';charset=latin1', UDB_LOGIN_NAME, UDB_LOGIN_PASS);
            $ret['genre'] = 'success';
            $ret['type'] = 'connectdb';
            $ret['desc'] = 'Connection to database made successfully';
            $ret['code'] = 1;
            $ret['obj'] = $db;
        } catch (PDOException $ex) {
            $ret['genre'] = 'error';
            $ret['type'] = 'connectdb';
            $ret['desc'] = $ex;
            $ret['code'] = 107;
        }
        return $ret;
    }

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * This function is used tab_user insert values in database
     * @param string $table table name
     * @param $data
     * @param $db
     * @return array
     */
    function insert_db($table, $data, $db) // Database Insert function
    {
        try {
            $data_temp = array();
            foreach ($data as $keys => $value) { // Adding a :(colon) precede to keys of array
                $data_temp[':' . $keys] = $value;
            }
            if ($stmt = $db->prepare("INSERT INTO " . $table . "( " . implode(',', array_keys($data)) . ") VALUES(" . implode(',', array_keys($data_temp)) . " )")) {
                if ($stmt->execute($data_temp)) {
                    $affected_rows = $stmt->rowCount();
                    $ret['genre'] = 'success';
                    $ret['type'] = 'db_insert';
                    $ret['desc'] = 'Data loaded to Database';
                    $ret['code'] = 1;
                    $ret['rows_affected'] = $affected_rows;
                } else {
                    $ret['genre'] = 'error';
                    $ret['type'] = 'db_insert';
                    $ret['desc'] = 'Unable to Insert data in table,execute() function not executed, [May be a duplicate entry in unique field]';
                    $ret['code'] = 0;
                }
            } else {
                $ret['genre'] = 'error';
                $ret['type'] = 'db_insert';
                $ret['desc'] = 'Unable to prepare statement,prepare() function not executed';
                $ret['code'] = 0;
            }
        } catch (Exception $ex) {
            $ret['genre'] = 'error';
            $ret['type'] = 'unknown';
            $ret['desc'] = $ex;
            $ret['code'] = 107;
        }
        return $ret;
    }


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @param string $table name of table
     * @param array $where WHERE clause elements
     * @param int $start_limit limit skip
     * @param int $end_limit limit max
     * @param array $return elements that need to access to get
     * @param string $order ORDER BY CLAUSE
     * @param $db object Database connection object Object
     * @return array
     */
    function select_db($table, $where = NULL, $start_limit, $end_limit, $return, $order, $db)
    {
        try {
            if ($order == NULL) {
                $order = '';
            } else {
                $order = ' ORDER BY ' . $order . ' ';
            }
            if ($start_limit == NULL) { //Setting default limit if no limit is provided in the function call
                $start_limit = 0;
            }
            if ($end_limit == NULL) {
                $end_limit = 20;
            }
            $limit_range = $end_limit - $start_limit;
            if ($limit_range > 200) { //Limiting the range of table value fetch to save the memory and processing
                $end_limit = $start_limit + 200;
            }
            if ($where == NULL) { //If No where is provided execute this block
                if ($return == NULL) {
                    $stmt = $db->prepare("SELECT * FROM " . $table . " " . $order . " LIMIT :skip,:max");
                } else {
                    $stmt = $db->prepare("SELECT " . implode(',', $return) . " FROM " . $table . " " . $order . " LIMIT :skip,:max");
                }
                $stmt->bindValue(':skip', (int)$start_limit, PDO::PARAM_INT); //Binding values of Limit parameter
                $stmt->bindValue(':max', (int)$end_limit, PDO::PARAM_INT); //Binding values of Limit parameter
                $stmt->execute();
                if ($rows = $stmt->fetchAll(PDO::FETCH_ASSOC)) {
                    $ack_help = array( //Creating a acknowledgement help to send back to the calling method with data
                        'ack_help' => array(
                            'genre' => 'success',
                            'type' => 'db_select',
                            'desc' => 'execute() method executed successfully, available data will be in the array to access if any',
                            'code' => 1
                        )
                    );
                    $ret = $ack_help + $rows; //Combining DATA and acknowledgement help
                } else {
                    $ret['ack_help']['genre'] = 'error';
                    $ret['ack_help']['type'] = 'db_select';
                    $ret['ack_help']['desc'] = 'Unable to run execute function,execute() function not executed';
                    $ret['ack_help']['code'] = 0;
                }
            } else { //If there is a Where in call then execute this block
                $where_temp_str = '';
                $temp_int = 0;
                foreach ($where as $key => $values) {
                    if ($temp_int > 0) {
                        $where_temp_str = $where_temp_str . '  AND  ' . $key . ' = :' . $key;
                    } else {
                        $where_temp_str = $where_temp_str . '  ' . $key . ' = :' . $key;
                    }
                    $temp_int++;
                }
                $where_temp_str = ' WHERE ' . $where_temp_str;
                if ($return == NULL) {
                    $prepare_str = "SELECT * FROM " . $table . " " . $where_temp_str . " " . $order . " LIMIT " . $start_limit . "," . $end_limit;

                } else {
                    $prepare_str = "SELECT " . implode(',', $return) . " FROM " . $table . " " . $where_temp_str . " " . $order . " LIMIT " . $start_limit . "," . $end_limit;
                }
                $stmt = $db->prepare($prepare_str);
                $data_temp = array();
                foreach ($where as $keys => $value) { // Adding a :(colon) precede to keys of array
                    $data_temp[':' . $keys] = $value;
                }
                if ($stmt->execute($data_temp)) {
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    $ack_help = array( //Creating a acknowledgement help to send back to the calling method with data
                        'ack_help' => array(
                            'genre' => 'success',
                            'type' => 'db_select',
                            'desc' => 'execute() method executed successfully, available data will be in the array to access if any',
                            'code' => 1
                        )
                    );
                    $ret = $ack_help + $rows; //Combining DATA and acknowledgement help
                } else {
                    $ret['ack_help']['genre'] = 'error';
                    $ret['ack_help']['type'] = 'db_select';
                    $ret['ack_help']['desc'] = 'Unable to run execute function,execute() function not executed';
                    $ret['ack_help']['code'] = 0;
                }
            }
            return $ret;
        } catch (Exception $ex) {
            $ret['ack_help']['genre'] = 'error';
            $ret['ack_help']['type'] = 'unknown';
            $ret['ack_help']['desc'] = $ex;
            $ret['ack_help']['code'] = 107;
        }
        return $ret;
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * @param string $table name of table
     * @param array $where where clause for query
     * @param array $set elements that has to update
     * @param object $db object from database connection
     * @return array
     */
    function update_db($table, $where = NULL, $set, $db)
    {
        try {
            $where_temp_str = '';
            $where_text = '';
            $temp_int = 0;
            if ($where == NULL) {
                $where_temp_str = '';
            } else {
                foreach ($where as $key => $values) { //Placing "ADD" between multiple where statement
                    $where_text = 'WHERE';
                    if ($temp_int > 0) {
                        $where_temp_str = $where_temp_str . '  AND  ' . $key . ' = :' . $key;
                    } else {
                        $where_temp_str = $where_temp_str . '  ' . $key . ' = :' . $key;
                    }
                    $temp_int++;
                }
            }
            $temp_int = 0;
            $set_temp_str = '';
            foreach ($set as $key => $value) { // Creating a proper SET string as field=:field
                if ($temp_int > 0) {
                    $set_temp_str = $set_temp_str . ' , ' . $key . '=' . ':' . $key . ' ';
                } else {
                    $set_temp_str = $set_temp_str . ' ' . $key . '=' . ':' . $key . ' ';
                }
                $temp_int++;
            }
            $set_temp = array();
            $where_temp = array();
            if ($stmt = $db->prepare("UPDATE " . $table . " SET " . $set_temp_str . " " . $where_text . " " . $where_temp_str)) {
                foreach ($set as $keys => $value) { // Adding a :(colon) precede to keys of array
                    $set_temp[':' . $keys] = $value;
                }
                foreach ($where as $keys => $value) { // Adding a :(colon) precede to keys of array
                    $where_temp[':' . $keys] = $value;
                }
                $set_temp = $set_temp + $where_temp; //Getting ready binding data
                if ($stmt->execute($set_temp)) { //Binding and Running the query
                    $affected_rows = $stmt->rowCount();
                    $ret['genre'] = 'success';
                    $ret['type'] = 'db_update';
                    $ret['desc'] = 'Data updated in Database';
                    $ret['code'] = 1;
                    $ret['rows_affected'] = $affected_rows;
                } else {
                    $ret['genre'] = 'error';
                    $ret['type'] = 'db_update';
                    $ret['desc'] = 'Unable to run execute function,execute() function not executed(Data not updated[May be a duplicate entry in unique field])';
                    $ret['code'] = 0;
                }
            } else {
                $ret['genre'] = 'error';
                $ret['type'] = 'db_update';
                $ret['desc'] = 'Unable to run prepare function,prepare() function not executed(Data not updated)';
                $ret['code'] = 0;
            }
        } catch (Exception $ex) {
            $ret['genre'] = 'error';
            $ret['type'] = 'db_update';
            $ret['desc'] = $ex;
            $ret['code'] = 107;
        }
        return $ret;
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * @param string $table name of table
     * @param array $where where clause
     * @param object $db PDO connection object
     * @return array single dimension associative array
     */
    function delete_db($table, $where, $db)
    {
        try {
            $where_temp_str = '';
            $temp_int = 0;
            if ($where == NULL) {
                $where_temp_str = '';
            } else {
                foreach ($where as $key => $values) { //Placing "ADD" between multiple where statement
                    if ($temp_int > 0) {
                        $where_temp_str = $where_temp_str . '  AND  ' . $key . ' = :' . $key;
                    } else {
                        $where_temp_str = $where_temp_str . '  ' . $key . ' = :' . $key;
                    }
                    $temp_int++;
                }
            }
            $where_temp = array();
            foreach ($where as $keys => $value) { // Adding a :(colon) precede to keys of array
                $where_temp[':' . $keys] = $value;
            }
            if ($stmt = $db->prepare("DELETE FROM " . $table . "  WHERE  " . $where_temp_str)) {
                if ($stmt->execute($where_temp)) {
                    $affected_rows = $stmt->rowCount();
                    $ret['genre'] = 'success';
                    $ret['type'] = 'db_delete';
                    $ret['desc'] = 'Data deleted from Database';
                    $ret['code'] = 1;
                    $ret['rows_affected'] = $affected_rows;

                } else {
                    $ret['genre'] = 'error';
                    $ret['type'] = 'db_delete';
                    $ret['desc'] = 'Unable to run execute function,execute() function not executed(Data not deleted).';
                    $ret['code'] = 0;
                }
            } else { //prepare not block
                $ret['genre'] = 'error';
                $ret['type'] = 'db_delete';
                $ret['desc'] = 'Unable to run prepare function,prepare() function not executed(Data not deleted)';
                $ret['code'] = 0;
            }
        } catch (Exception $ex) {
            $ret['genre'] = 'error';
            $ret['type'] = 'db_delete';
            $ret['desc'] = $ex;
            $ret['code'] = 107;
        }
        return $ret;
    }


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    /**
     * @param String $table
     * @param null /array $where
     * @param array $set
     * @param object $db
     * @return mixed
     */
    function upload_db($table, $where = NULL, $set, $db)
    {
        try {
            $where_temp_str = '';
            $where_text = '';
            $temp_int = 0;
            if ($where == NULL) {
                $where_temp_str = '';
            } else {
                $where_text = 'WHERE';
                foreach ($where as $key => $values) { //Placing "ADD" between multiple where statement
                    if ($temp_int > 0) {
                        $where_temp_str = $where_temp_str . '  AND  ' . $key . ' = :' . $key;
                    } else {
                        $where_temp_str = $where_temp_str . '  ' . $key . ' = :' . $key;
                    }
                    $temp_int++;
                }

            }
            $temp_int = 0;
            $set_temp_str = '';
            foreach ($set as $key => $value) { // Creating a proper SET string as field=:field
                if ($temp_int > 0) {
                    $set_temp_str = $set_temp_str . ' , ' . $key . '=' . ':' . $key . ' ';
                } else {
                    $set_temp_str = $set_temp_str . ' ' . $key . '=' . ':' . $key . ' ';
                }
                $temp_int++;
            }

            if ($stmt = $db->prepare("UPDATE " . $table . " SET " . $set_temp_str . " " . $where_text . " " . $where_temp_str)) {
                if ($where != NULL) {
                    foreach ($where as $keys => $value) { // Adding a :(colon) precede to keys of array
                        $colon = ':' . $keys;
                        $stmt->bindValue($colon, $value);
                    }
                }
                foreach ($set as $keys => $value) { // Adding a :(colon) precede to keys of array
                    $colon = ':' . $keys;
                    $stmt->bindParam($colon, $value, PDO::PARAM_LOB);
                }
                if ($stmt->execute()) { //Binding and Running the query
                    $affected_rows = $stmt->rowCount();
                    $ret['genre'] = 'success';
                    $ret['type'] = 'db_upload';
                    $ret['desc'] = 'Data updated in Database';
                    $ret['code'] = 1;
                    $ret['rows_affected'] = $affected_rows;
                } else {
                    $ret['genre'] = 'error';
                    $ret['type'] = 'db_upload';
                    $ret['desc'] = 'Unable to run execute function,execute() function not executed(Data not updated[May be a duplicate entry in unique field])';
                    $ret['code'] = 0;
                }
            } else {
                $ret['genre'] = 'error';
                $ret['type'] = 'db_upload';
                $ret['desc'] = 'Unable to run prepare function,prepare() function not executed(Data not updated)';
                $ret['code'] = 0;
            }
        } catch (Exception $ex) {
            $ret['genre'] = 'error';
            $ret['type'] = 'db_upload';
            $ret['desc'] = $ex;
            $ret['code'] = 107;
        }
        return $ret;
    }
}
