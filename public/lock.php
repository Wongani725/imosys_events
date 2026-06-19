<?php

$host = "167.99.92.105";
$port = 9700;
// don't timeout!
set_time_limit(0);
// create socket
$socket = socket() or die("Could not create socket\n");
echo('sent');
// bind socket to port
$result = socket_bind($socket, $host, $port) or die("Could not bind to socket\n");
echo('sent');
// start listening for connections
$result = socket_listen($socket, 3) or die("Could not set up socket listener\n");
echo('sent');
// accept incoming connections
// spawn another socket to handle communication
$spawn = socket_accept($socket) or die("Could not accept incoming connection\n");
echo('sent');
// read client input
$input = socket_read($spawn, 1024) or die("Could not read input\n");
echo('sent');


$output = hex2bin('3078464646462a434d44532c4f4d2c3836323230353035353430323234342c32303232313031323133353532302c52652c4c3123');
echo('sent');
socket_write($spawn, $output, strlen ($output)) or die("Could not write output\n");
// close sockets


//Send the message to the server
if( ! socket_send ( $sock , $output , strlen($output) , 0))
{
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);

    die("Could not send data: [$errorcode] $errormsg \n");
}

echo "Message send successfully \n";

//Now receive reply from server
if(socket_recv ( $sock , $buf , 2 , MSG_WAITALL ) === FALSE)
{
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);

    die("Could not receive data: [$errorcode] $errormsg \n");
}

//print the received message
echo $buf;

?>                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                
