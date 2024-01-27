<?php 

class db 
{
    private static $instance;
    public static $request = ''; 
 
    /**
     * Устанавливает коннект с БД
     */
    public static function connect($host, $user, $pass, $name)
    { 
        $mysqli = new mysqli($host, $user, $pass, $name);
        
        if ($mysqli->connect_errno) { 
            die(sprintf("Не удалось подключиться к базе данных: %s", $mysqli->connect_errno));
        }

        $mysqli->query("SET NAMES 'UTF8'"); 

        return db::$instance = $mysqli;
    }

    /**
     * Произвольный запрос к базе данных
     * */

    public static function query($query)
    {
        self::$request = $query; 
        return self::instance()->query($query);
    }

    /**
     * Возвращает несколько рядов
     *
     * @param string  $query
     * @param bool    $output
     * @return array | null 
     */
    public static function select($query, $output = true)
    {
        $mysqli_result = self::instance()->query($query);

        if ($mysqli_result) {
            $r = array();

            if ($output === false) {
                $method = 'fetch_array'; 
            } elseif ($output === 0) {
                $method = 'fetch_object'; 
            } else {
                $method = 'fetch_assoc'; 
            }

            while ($row = call_user_func(array($mysqli_result, $method))) {
                $r[] = $output === true ? (array) $row : $row;
            }
 
            return $r;
        }
 
        return null;
    }
 
    /**
     * Возвращает один ряд таблицы
     *
     * @param string  $query
     * @param $output (true, 0, false)
     * @return array | null 
     */
    public static function fetch($query, $output = true)
    {
        $mysqli_result = self::instance()->query($query);

        if ($mysqli_result) {
            if ($output === false) {
                $row = $mysqli_result->fetch_array();
            } elseif ($output === 0) {
                $row = $mysqli_result->fetch_object();
            } else {
                $row = $mysqli_result->fetch_assoc();
            }

            return $row; 
        }
 
        return null;
    }
 
    /**
     * Возвращает значение одного поля
     *
     * @param $query  $query
     * @return string | array
     */
    public static function get_var($query, $rows = false)
    {
        $output = ($rows ? [] : null); 
        
        self::$request = $query; 
        $mysqli_result = self::instance()->query($query);

        if ($mysqli_result) {
            while($post = $mysqli_result->fetch_array()) {
                if ($rows === true) {
                    $output[] = array_shift($post); 
                } else {
                    $output = array_shift($post); 
                }
            }
        }

        return $output; 
    }
 
    /**
     * @param string $query
     * @return int
     */
    public static function count($query)
    {
        $count = self::get_var($query); 

        if (!is_numeric($count)) {
            $count = 0; 
        }
 
        return (int) $count;
    }

    /**
    * Обновление записи  
    * @table Имя таблицы 
    * @query Массив с ключами и значениями
    * @where Массив с ключами и значениями 
    */ 
    public static function update($table, $query, $where = array(), $limit = NULL) 
    {
        $query = self::get_construct_query_update($query);
        $where = self::get_construct_query_where($table, $where);

        $sql = 'UPDATE `' . $table . '` SET ' . join(',', $query['query_keys']) . ' WHERE 1=1 ' . $where . ' ' . $limit;
        self::$request = $sql; 

        if ($stmt = self::instance()->prepare($sql)) {
            $types = ''; 
            foreach($query['query_params'] AS $value) {
                $types .= self::get_type($value); 
                $bind_name = $value;
                $$bind_name = $value;
                $params[] = &$$bind_name;
            }

            call_user_func_array(array($stmt, 'bind_param'), array_merge(array($types), $params));

            if ($stmt->execute()) {
                $stmt->close();
                return true;
            }
        }

        return false; 
    }

    /**
    * Удаление записи  
    * @table Имя таблицы 
    * @where Массив с ключами и значениями 
    */ 
    public static function delete($table, $where = array(), $limit = '') 
    {
        $where = self::get_construct_query_where($table, $where);

        $sql = 'DELETE FROM `' . $table . '` WHERE 1=1 ' . $where . ' ' . $limit;
        self::$request = $sql; 

        if ($stmt = self::instance()->prepare($sql)) {
            if ($stmt->execute()) {
                $stmt->close();
                return true;
            }
        }

        return false; 
    }

    /**
    * Создание записи  
    * @table Имя таблицы 
    * @query Массив с ключами и значениями
    */ 
    public static function insert($table, $query) 
    {
        $query = self::get_construct_query_insert($query);
        $sql = "INSERT INTO `" . $table . "` (" . join(',', $query['query_keys']) . ") VALUES(" . join(',', $query['query_values']) . ")";
        self::$request = $sql; 

        if ($stmt = self::instance()->prepare($sql)) {
            $types = ''; 
            foreach($query['query_params'] AS $value) {
                $types .= self::get_type($value); 
                $bind_name = $value;
                $$bind_name = $value;
                $params[] = &$$bind_name;
            }

            call_user_func_array(array($stmt, 'bind_param'), array_merge(array($types), $params));

            if ($stmt->execute()) {
                $stmt->close();
                return true;
            }
        }

        return false; 
    }

    public static function insert_id()
    {
        return self::instance()->insert_id;
    }
 
    /**
     * Проверяет, существует ли указанная колонка в таблице
     * @return bolean
     * */
    public static function is_exsits_column($table_name, $column_name) 
    {
        $columns = array(); 
        $rows = self::select("SELECT COLUMN_NAME AS name FROM INFORMATION_SCHEMA.columns WHERE TABLE_NAME = '" . $table_name . "'"); 
        foreach($rows AS $column) {
            if(isset($column['name'])) {
                $columns[] = $column['name']; 
            }
        }

        if (in_array($column_name, $columns)) {
            return true; 
        }
        return false; 
    }
 
    /**
     * Возвращает строку с описанием последней ошибки
     * @return string
     * */
    public static function error()
    {
        return self::instance()->error;
    }
 
    /**
     * Экранирует специаотные символы
     * @return string
     * */
    public static function esc( $str ) 
    {
        return self::instance()->real_escape_string($str); 
    }

    public static function get_construct_where_field($operator, $value) 
    {
        $operator = strtoupper($operator); 

        if (is_array($value)) {
            $value = ' \'' . join('\', \'', $value) . '\''; 
        }

        if (in_array($operator, array('IN', 'NOT IN'))) {
            return $operator . ' (' . $value . ')';
        }

        return $operator . ' \'' . self::esc($value) . '\''; 
    }
    
    public static function get_construct_query_where($table, $args, $before = ' AND ') 
    {
        $sql = array(); 

        if ($before) {
            $str = " " . $before . " (";
        } else {
            $str = " (";
        }
        
        $relation = 'AND'; 

        foreach($args AS $key => $value) 
        {
            if ($key === 'relation') {
                $relation = $value; 
            }

            elseif (isset($value['field'])) {
                if (!isset($value['operator'])) {
                    $value['operator'] = '='; 
                }

                $sql[] = $table . '.' . $value['field'] . ' ' . self::get_construct_where_field($value['operator'], $value['value']);
            }  
            
            elseif (isset($value['relation'])) { 
                $sql[] = self::get_construct_query_where($table, $value, ''); 
            }

            elseif (!is_array($value)) {
                $sql[] = $table . '.' . $key . ' = \'' . $value . '\'';
            }
        }

        if (!$sql) {
            return '';
        }
        
        $str .= implode(' ' . strtoupper($relation) . ' ', $sql); 
        $str .= ")";
       
        return $str; 
    }

    public static function get_construct_query_insert($array) 
    {
        $construct = array(
            'query_keys' => array(), 
            'query_values' => array(), 
            'query_params' => array(), 
        ); 

        foreach($array AS $key => $value) {
            array_push($construct['query_keys'], '`' . $key . '`');
            array_push($construct['query_values'], '?');
            array_push($construct['query_params'], $value);
        }

        return $construct;
    }

    public static function get_construct_query_update($array) 
    {
        $construct = array(
            'query_keys'   => array(), 
            'query_params' => array(), 
        ); 

        foreach($array AS $key => $value) {
            array_push($construct['query_keys'], "`" . $key . "` = ?");
            array_push($construct['query_params'], $value);
        }

        return $construct;
    }

    private static function instance()
    {
        return self::$instance;
    }

    private static function get_type($str) 
    {
        if (ctype_digit((string) $str)) {
            return ($str <= PHP_INT_MAX ? 'i' : 's');
        }

        if (is_numeric($str)) {
            return 'd'; 
        }

        return 's'; 
    }

}