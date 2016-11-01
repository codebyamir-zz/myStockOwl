<?php
abstract class Mapper {
    protected $db;
    protected $logger;
    public function __construct($db = NULL,$logger = NULL) {
        $this->db = $db;
        $this->logger = $logger;
    }
}
