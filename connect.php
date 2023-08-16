<?php

if(!function_exists("tryConnect")){
    function tryConnect($serverName, $userName, $password){
        error_log("Attempting Pconnect to Azure SQL from Pod:" . $_ENV['HOSTNAME'] . ":" . $serverName . $userName . $password);
        $preConnect = microtime(true);    
        $connectionInfo = array( "Database"=>"KPES", "UID"=>$userName, "PWD"=>$password);
        $connection = sqlsrv_connect( $serverName, $connectionInfo);
        $postConnect = microtime(true);
        // error_log("Db2 Pconnect took:" . (float)($postConnect-$preConnect));
        // error_log("Db2 Pconnection:" . print_r($connection,true));
        return $connection;
    }
}

if( isset($_ENV['db-server']) && isset($_ENV['db-user-name']) && isset($_ENV['db-user-pw']) ) {
    
    $serverName = $_ENV['db-server'];
    $userName = $_ENV['db-user-name'];
    $password = $_ENV['db-user-pw'];
    
    $conn=false;
    $attempts = 0;

    while(!$conn && ++$attempts < 3){
        // since Cirrus - we have the occasional problem connecting, so sleep and try again a couple of times 
        $conn = tryConnect($serverName, $userName, $password);
        if(!$conn){
            error_log("Failed attempt $attempts to connect to Azure SQL");
            error_log("Msg:" . sqlsrv_errors());
            error_log("Err:" . sqlsrv_errors());
            sleep(1);
        } else {
            error_log("Connection successful on : $attempts Attempt");
        }
    }

    if( $conn ) {
        $GLOBALS['conn'] = $conn;
        $schema = isset($GLOBALS['Db2Schema']) ? $GLOBALS['Db2Schema'] : 'REST';
        $Statement = "SET CURRENT SCHEMA='$schema';";
        $rs = db2_exec($conn, $Statement);

        if (! $rs) {
            echo "<br/>" . $Statement . "<br/>";

            echo "<pre>";
            print_r($_SESSION);
            echo "</pre>";

            echo "<BR>" . db2_stmt_errormsg() . "<BR>";
            echo "<BR>" . db2_stmt_error() . "<BR>";
            exit("Set current schema failed");
        }
        db2_autocommit($conn, TRUE); // This is how it was on the Wintel Box - so the code has no/few commit points.
    } else {
        error_log(__FILE__ . __LINE__ . " Connect to DB2 Failed");
        // error_log(__FILE__ . __LINE__ . $conn_string);
        // error_log(__FILE__ . __LINE__ . db2_conn_errormsg());
        // error_log(__FILE__ . __LINE__ . db2_conn_error());
        // throw new \Exception('Failed to connect to DB2');
    }
} else {
    echo "<pre>";
    print_r($_ENV);
    echo "</pre>";
    echo "<p>No database credentials.</p>";
}
?>