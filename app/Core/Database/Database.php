<?php

namespace App\Core\Database;

use PDO;
use PDOException;

class Database
{
    /**
     * The PDO instance or connection.
     *
     * @var PDO
     */
    private $pdo_conn;

    /**
     * Attempts to connect to the database, show error page otherwise.
     *
     * @return \App\Core\Database\Database|void
     */
    public function initializeDB()
    {   
        try
        {
            $this->setPDO(new PDO(...config('database.pdo_construct')));
            $this->getPDO()->setAttribute(...config('database.pdo_errmode'));
            return $this;
        }
        catch(PDOException $error_data)
        {
            require get_view('error.error-no-database');
            exit;
        }
    }

    /**
     * Sets the PDO instance.
     *
     * @param PDO $pdo
     * 
     * @return \App\Core\Database\Database
     */
    public function setPDO(PDO $pdo)
    {
        $this->pdo_conn = $pdo;
        return $this;
    }

    /**
     * Returns the PDO instance.
     *
     * @return PDO
     */
    public function getPDO()
    {
        return $this->pdo_conn;
    }

    /**
     * Check connection of the database.
     *
     * @return boolean
     */
    public function isConnected()
    {
        return $this->pdo_conn != null;
    }
}