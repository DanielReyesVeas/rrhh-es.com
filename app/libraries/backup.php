<?php

class Backup_Database {
    /**
     * Host where database is located
     */
    var $host = '';

    /**
     * Username used to connect to database
     */
    var $username = '';

    /**
     * Password used to connect to database
     */
    var $passwd = '';

    /**
     * Database to backup
     */
    var $dbName = '';

    /**
     * Database charset
     */
    var $charset = '';
    
    var $conn = '';

    /**
     * Constructor initializes database
     */
    function Backup_Database($host, $username, $passwd, $dbName, $charset = 'utf8')
    {
        $this->host     = $host;
        $this->username = $username;
        $this->passwd   = $passwd;
        $this->dbName   = $dbName;
        $this->charset  = $charset;

        $this->initializeDatabase();
    }

    protected function initializeDatabase()
    {
        $conn = mysqli_connect($this->host, $this->username, $this->passwd);
        mysqli_select_db($conn, $this->dbName);
        if (! mysqli_set_charset ($conn, $this->charset))
        {
            mysqli_query($conn, 'SET NAMES '.$this->charset);
        }
        $this->conn = $conn;
    }

    /**
     * Backup the whole database or just some tables
     * Use '*' for whole database or 'table1 table2 table3...'
     * @param string $tables
     */
    public function backupTables($tables = '*', $outputDir = '.')
    {
        try
        {
            /**
            * Tables to export
            */
            if($tables == '*')
            {
                $tables = array();
                $result = mysqli_query($this->conn, 'SHOW TABLES');
                while($row = mysqli_fetch_row($result))
                {
                    $tables[] = $row[0];
                }
            }
            else
            {
                $tables = is_array($tables) ? $tables : explode(',',$tables);
            }

            $sql = 'CREATE DATABASE IF NOT EXISTS '.$this->dbName.";\n\n";
            $sql .= 'USE '.$this->dbName.";\n\n";

            /**
            * Iterate tables
            */
            foreach($tables as $table)
            {
        //        echo "Backing up ".$table." table...";

                $camposNulos=array();
                // para la tabla saber los campos nulos
                $consulta="show columns from ".$table;
                $result = mysqli_query($this->conn, $consulta);

                $indice=0;
                while( $fila = $result->fetch_assoc() ){
                    if( $fila['Null'] == "YES" ){
                        $camposNulos[]=$indice;
                    }
                    $indice++;
                }


/*
                $numFields = mysqli_num_fields($result);
                for ($i = 0; $i < $numFields; $i++) {
                    while ($row = mysqli_fetch_row($result)) {
                        if( $row[1] ){
                            $camposNulos[]=$i;
                        }
                    }
                }
*/
                /*
                if( count($camposNulos) ) {
                    var_dump($camposNulos);
                    echo "<BR/>--------------------------------<BR/>";
                }
                */
                $result = mysqli_query($this->conn, 'SELECT * FROM '.$table);
                $numFields = mysqli_num_fields($result);

                $sql .= 'DROP TABLE IF EXISTS '.$table.';';
                $row2 = mysqli_fetch_row(mysqli_query($this->conn, 'SHOW CREATE TABLE '.$table));
                $sql.= "\n\n".$row2[1].";\n\n";

                for ($i = 0; $i < $numFields; $i++) 
                {
                    while($row = mysqli_fetch_row($result))
                    {
                        $sql .= 'INSERT INTO '.$table.' VALUES(';
                        for($j=0; $j<$numFields; $j++) 
                        {
                            $row[$j] = addslashes($row[$j]);
                            $row[$j] = preg_replace("/\n/","\\n",$row[$j]);
                            if (isset($row[$j]))
                            {
                                if( in_array($j, $camposNulos) and !trim($row[$j]) ) {
                                    $sql .= 'NULL';
                                }else {
                                    $sql .= '"' . $row[$j] . '"';
                                }
                            }
                            else
                            {
                                if( in_array($j, $camposNulos) ) {
                                    $sql .= 'NULL';
                                }else{
                                    $sql .= '""';
                                }
                            }

                            if ($j < ($numFields-1))
                            {
                                $sql .= ',';
                            }
                        }

                        $sql.= ");\n";
                    }
                }

                $sql.="\n\n\n";

          //      echo " OK" . "";
            }
        }
        catch (Exception $e)
        {
            var_dump($e->getMessage());
            return false;
        }

        return $this->saveFile( $sql, $outputDir);
    }

    /**
     * Save SQL to file
     * @param string $sql
     */
    protected function saveFile(&$sql, $outputDir = '.')
    {
        if (!$sql) return false;
        $nombre='';
        try
        {
            $nombre = 'db-backup-'.$this->dbName.'-'.date("Ymd-His", time()).'.sql';
            $handle = fopen($outputDir.'/'.$nombre,'w+');
            fwrite($handle, utf8_decode($sql) );
            fclose($handle);
            
        }
        catch (Exception $e)
        {
            var_dump($e->getMessage());
            return false;
        }

        return $nombre;
    }
}
?>