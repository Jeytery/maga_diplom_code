<?php

/*
 * Beta Orionis (Rigel) PHP Scripts
 * Rigel The White Blue Giant, The Leg of Orion, Osiris
 * Amon Ra Eye
 * API repository v.1.0.0
 * 3bit.app 2024
 */

class Repository
{

    private const LOG_DATA_MAX_LEN = 1024;
    private const DEFAULT_ROW_DELIM = ", ";

    private $connection; // Connection descriptor (Force commit for connection: $database->getConnection()->commit())
    private $message;
    private $errorMessage;
    private $logFilename; // File of log
    private $sessionId; // Session identifier of connection
    private $isDebug;
    private $debugCount;

    public function getConnection()
    {
        return $this->connection;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function __construct($logFilename, $sessionId, $isDebug)
    {
        $this->logFilename = $logFilename;
        $this->sessionId = $sessionId;
        $this->isDebug = $isDebug;
        $debugCount = 0;
    }

    // Connect to database with charset and collate
    // Connection charset = utf8mb4 | utf8mb4_unicode_ci

    public function connect($hostname, $username, $password, $database, $charset='utf8', $collate='')
    {
        $connected = true;
        $isCharsetSucceeded = false;
        $this->connection = mysqli_connect($hostname, $username, $password, $database);
        if (!$this->connection) {
            $this->errorMessage = mysqli_error($this->connection);
            if ($this->logFilename) {
                writeToLog($this->logFilename, "Error|Database connection failed|#" .
                    $this->sessionId . "|" . $this->errorMessage);
            }
            $connected = false;
            return $connected;
        }
        $this->message = mysqli_get_host_info($this->connection);
        if ($this->logFilename) {
            writeToLog($this->logFilename, "Info|Database connection established|#" .
                $this->sessionId . "|" . $username . "@" . $hostname . "/" . $database);
        }
        if (function_exists('mysqli_set_charset') && !empty($charset)) {
//            $this->connection->set_charset($charset);
            $isCharsetSucceeded = mysqli_set_charset($this->connection, $charset);
        }
        if ($isCharsetSucceeded && !empty($charset)) {
            $query = "SET NAMES " . $charset;
            if (!empty($collate)) {
                $query = $query . " COLLATE " . $collate;
            }
            mysqli_query($this->connection, $query);
        }
        return $connected;
    }

    // Close database connection if was opened

    public function close()
    {
        if ($this->connection) {
            mysqli_close($this->connection);
            if ($this->logFilename) {
                writeToLog($this->logFilename, "Info|Database connection closed|#" .
                    $this->sessionId);
            }
        }
    }

    // PL-SQL queries execution

    public function query($sqlQuery)
    {
        if (mysqli_query($this->connection, $sqlQuery)) {
            if ($this->logFilename) {
                writeToLog($this->logFilename, "Info|Write to database|#" .
                    $this->sessionId . "|" . $sqlQuery);
            }
            return true;
        } else {
            $this->errorMessage = mysqli_error($this->connection);
            if ($this->logFilename) {
                writeToLog($this->logFilename, "Error|Database SQL query failed|#" .
                    $this->sessionId . "|" . $sqlQuery . "|" . $this->errorMessage);
            }
            return false;
        }
    }

    // SQL Insert, Update, Delete queries execution
    // When stored procedure or function returns a value

    public function updateQuery($sqlQuery)
    {
        $updated = null;
        $result = mysqli_query($this->connection, $sqlQuery);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_row();
            $updated = $row[0];
            if ($this->logFilename) {
                writeToLog($this->logFilename, "Info|Update to database|#" .
                    $this->sessionId . "|" . $this->getLogData($sqlQuery) . "|=" . $updated);
            }
        } else {
            $this->error_message = mysqli_error($this->connection);
            if ($this->logFilename) {
                writeToLog($this->logFilename, "Error|Database SQL query failed|#" .
                    $this->sessionId . "|" . $this->getLogData($sqlQuery) . "|" . $this->errorMessage);
            }
        }
        return $updated;
    }

    // SQL multi queries execution

    public function multiQuery($sqlQuery)
    {
        if (mysqli_multi_query($this->connection, $sqlQuery)) {
            if ($this->logFilename) {
                writeToLog($this->logFilename, "Info|Write to database|#" .
                    $this->sessionId . "|" . $sqlQuery);
            }
            return true;
        } else {
            $this->errorMessage = mysqli_error($this->connection);
            if ($this->logFilename) {
                writeToLog($this->logFilename, "Error|Database SQL query failed|#" .
                    $this->sessionId . "|" . $sqlQuery . "|" . $this->errorMessage);
            }
            return false;
        }
    }

    // SQL Select of Integer value

    public function getIntValue($sqlQuery)
    {
        $selected = -1;
        $result = mysqli_query($this->connection, $sqlQuery);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_row();
            $selected = $row[0];
            if ($this->logFilename) {
                writeToLog($this->logFilename, "Info|Read from database|#" .
                    $this->sessionId . "|" . $sqlQuery . "|=" . $selected);
            }
        } else {
            $this->errorMessage = mysqli_error($this->connection);
            if ($this->logFilename) {
                writeToLog($this->logFilename, "Error|Database SQL query failed|#" .
                    $this->sessionId . "|" . $sqlQuery . "|" . $this->errorMessage);
            }
        }
        return $selected;
    }

    // SQL Select of String value

    public function getStringValue($sqlQuery)
    {
        $selected = "";
        $result = mysqli_query($this->connection, $sqlQuery);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_row();
            $selected = $row[0];
            if ($this->logFilename) {
                writeToLog($this->logFilename, "Info|Read from database|#" .
                    $this->sessionId . "|" . $sqlQuery . "|=" . $this->getLogData($selected));
            }
        } else {
            $this->errorMessage = mysqli_error($this->connection);
            if ($this->logFilename) {
                writeToLog($this->logFilename, "Error|Database SQL query failed|#" .
                    $this->sessionId . "|" . $sqlQuery . "|" . $this->errorMessage);
            }
        }
        return $selected;
    }

    // SQL Select of String List

    public function getStringList(
        $sqlQuery,
        $responseRowDelim = /*$this->*/self::DEFAULT_ROW_DELIM
    ) {
        $selected = "";

        $result = mysqli_query($this->connection, $sqlQuery);
        if ($result && ($result->num_rows >= 0)) {
            $numRows = $result->num_rows;
            while ($row = $result->fetch_row()) {
                if ($row[0]) {
                    $selected .= $row[0] . $responseRowDelim;
                }
            }
            // For -1 is a strlen($responseRowDelim)
            $selected = substr($selected, 0, strlen($selected) - 2); // 2 the length of row delim
            // The mysqli_next_result() function prepares the next result set from mysqli_multi_query().
            //$this->connection->next_result();
            $result->close();
            if ($this->logFilename) {
                writeToLog($this->logFilename, "Info|Read from database|#" .
                    $this->sessionId . "|" . $sqlQuery . "|" . $numRows);
            }
            if ($this->isDebug) {
                $this->debugCount++;
                if ($this->logFilename) {
                    writeToLog("logfile_debug_session_" . $this->sessionId . "_" .
                        $this->debugCount . ".log", $selected);
                }
            }
        } else {
            //mysqli_connect_error();
            $this->errorMessage = mysqli_error($this->connection);
            if ($this->logFilename) {
                writeToLog($this->logFilename, "Error|Database SQL query failed|#" .
                    $this->sessionId . "|" . $sqlQuery . "|" . $this->errorMessage);
            }
        }
        return $selected;
    }

    // Fetch data array

    public function getArrayList($sqlQuery)
    {
        $result = mysqli_query($this->connection, $sqlQuery);
        $dataArray = mysqli_fetch_array($result, MYSQLI_NUM);
        return $dataArray;
    }

    // Load data restriction by max len

    private function getLogData($data)
    {
        $logData = $data;
        $logDataLength = strlen($data);
        if ($logDataLength > self::LOG_DATA_MAX_LEN) {
            $logData = substr($data, 0, self::LOG_DATA_MAX_LEN) . " ..";
        }
        return $logData;
    }

}

?>
