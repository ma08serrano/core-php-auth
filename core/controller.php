<?php
    class Controller {
        private static $_instance = null;
        private $_db;
        private $_query;
        private $_error = false;
        private $_results;
        private $_count = 0;

        // PDO connection
        private function __construct() {
            try {
                $this->_db = new PDO('mysql:host=' . Config::get('mysql/host') . ';dbname=' . Config::get('mysql/db'), Config::get('mysql/username'), Config::get('mysql/password'));
            }catch(PDOException $e) {
                die($e->getMessage());
            }
        }

        // It ensures that there can be only one instance of a class 
        // and provides a global access point to that instance and this is common
        // with the singleton pattern
        public static function getInstance() {
            if(!isset(self::$_instance)) {
                self::$_instance = new Controller();
            } 
            return self::$_instance;
        }

        public function query($sql, $params = array()) {
            $this->_error = false;
            if($this->_query = $this->_db->prepare($sql)) {
                $i = 1;
                if(count($params)) {
                    foreach($params as $param) {
                        $this->_query->bindValue($i, $param);
                        $i++;
                    }
                }

                if($this->_query->execute()) {
                    $this->_results = $this->_query->fetchAll(PDO::FETCH_OBJ);
                    $this->_count = $this->_query->rowCount();
                }else {
                    $this->_error = true;
                }
            }

            return $this;
        }

        public function action($action, $table, $where = array()) {
            if(count($where) === 3) {
                $operators = array('=', '>', '<', '>=', '<=');

                $field = $where[0];
                $operator = $where[1];
                $value = $where[2];

                if(in_array($operator, $operators)) {
                    $sql = "{$action} FROM {$table} WHERE {$field} {$operator} ?";

                    if($this->query($sql, array($value))->error()) {
                        return $this;
                    }
                }
            }
            return false;
        }

        // get a user from database
        public function get($table, $where) {
            return $this->action('SELECT *', $table, $where);
        }

        // delete a user in the database
        public function delete($table, $where) {
            return $this->action('DELETE', $table, $where);
        }

        // add a user to database
        public function insert($table, $fields = array()) {
            $keys = array_keys($fields);
            $values = null;
            $i = 1;

            foreach($fields as $field) {
                $values .= '?';
                if($i < count($fields)) {
                    $values .= ', ';
                }
                $i++;
            }

            $sql = "INSERT INTO {$table} (`" . implode('`, `', $keys) . "`) VALUES ({$values})";

            if($this->query($sql, $fields)->error()) {
                return true;
            }

            return false;
        }

        // update a user in database
        public function update($table, $id, $fields) {
            $set = '';
            $i = 1;

            foreach($fields as $name => $value) {
                $set .= "{$name} = ?";
                if($i < count($fields)) {
                    $set .= ', ';
                }
                $i++;
            }

            $sql = "UPDATE {$table} SET {$set} WHERE id = {$id}";

            if($this->query($sql, $fields)->error()) {
                return true;
            }

            return false;
        }

        // return results
        public function results() {
            return $this->_results;
        }

        // return first
        public function first() {
            return $this->_results()[0];
        }

        // return error
        public function error() {
            return $this->_error;
        }

        // return count
        public function count() {
            return $this->_count;
        }
    }
?> 