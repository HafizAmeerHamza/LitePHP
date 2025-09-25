<?php

use App\Core\Database;

class User {
    private $db;
    private $table = 'users';
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAll() {
        return $this->db->select("SELECT * FROM {$this->table}");
    }
    
    public function find($id) {
        return $this->db->selectOne("SELECT * FROM {$this->table} WHERE id = ?", [$id]);
    }
    
    public function create($data) {
        return $this->db->insert($this->table, $data);
    }
    
    public function update($id, $data) {
        return $this->db->update($this->table, $data, 'id = ?', [$id]);
    }
    
    public function delete($id) {
        return $this->db->delete($this->table, 'id = ?', [$id]);
    }
    
    public function findByEmail($email) {
        return $this->db->selectOne("SELECT * FROM {$this->table} WHERE email = ?", [$email]);
    }
}