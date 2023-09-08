<?php

if(!function_exists("tryConnect")){
    function tryConnect($serverName, $dbName, $userName, $password){
        error_log("Attempting Pconnect to Azure SQL from Pod:" . $_ENV['HOSTNAME'] . ":" . $serverName . $userName . $password);
        $preConnect = microtime(true);    
        $connectionInfo = array( "Database"=>$dbName, "UID"=>$userName, "PWD"=>$password);
        $connection = sqlsrv_connect( $serverName, $connectionInfo);
        $postConnect = microtime(true);
        // error_log("Db2 Pconnect took:" . (float)($postConnect-$preConnect));
        // error_log("Db2 Pconnection:" . print_r($connection,true));
        return $connection;
    }
}

if( isset($_ENV['db-server']) 
    && isset($_ENV['db-name']) 
    && isset($_ENV['db-user-name'])
    && isset($_ENV['db-user-pw'])
) {
    
    $serverName = $_ENV['db-server'];
    $dbName = $_ENV['db-name'];
    $userName = $_ENV['db-user-name'];
    $password = $_ENV['db-user-pw'];
    
    $conn=false;
    $attempts = 0;

    while(!$conn && ++$attempts < 3){
        // since Cirrus - we have the occasional problem connecting, so sleep and try again a couple of times 
        $conn = tryConnect($serverName, $dbName, $userName, $password);
        if(!$conn){
            error_log("Failed attempt $attempts to connect to Azure SQL");
            error_log("Msg:" . json_encode(sqlsrv_errors()));
            error_log("Err:" . json_encode(sqlsrv_errors()));
            sleep(1);
        } else {
            error_log("Connection successful on : $attempts Attempt");
        }
    }

    if( $conn ) {
        $GLOBALS['conn'] = $conn;
        // $schema = isset($GLOBALS['Db2Schema']) ? $GLOBALS['Db2Schema'] : 'REST';
        // // $statement = "SET CURRENT SCHEMA='$schema';";
        // $statement = "ALTER USER ".$userName." WITH DEFAULT_SCHEMA = KPES;";
        // $rs = sqlsrv_query($conn, $statement);

        // if (! $rs) {
        //     echo "<br/>" . $statement . "<br/>";

        //     // echo "<pre>";
        //     // print_r($_SESSION);
        //     // echo "</pre>";

        //     if( ($errors = json_encode(sqlsrv_errors()) ) != null) {
        //         foreach( $errors as $error ) {
        //             echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
        //             echo "code: ".$error[ 'code']."<br />";
        //             echo "message: ".$error[ 'message']."<br />";
        //         }
        //     }
        //     exit("Set current schema failed");
        // }
        // sqlsrv_commit($conn, TRUE); // This is how it was on the Wintel Box - so the code has no/few commit points.
    } else {
        error_log(__FILE__ . __LINE__ . " Connect to Azure SQL Failed");
        // error_log(__FILE__ . __LINE__ . $conn_string);
        // error_log(__FILE__ . __LINE__ . json_encode(sqlsrv_errors());
        // error_log(__FILE__ . __LINE__ . db2_conn_error());
        // throw new \Exception('Failed to connect to Azure SQL');
    }
} else {
    echo "<pre>";
    print_r($_ENV);
    echo "</pre>";
    echo "<p>No database credentials.</p>";
}
?>