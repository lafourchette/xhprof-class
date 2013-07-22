<?php

class Xhprof
{
    protected $time = null;

    protected $source = 'default';

    public function start($autoClose = true, $source = null)
    {
        $this->time = microtime(true);

        require_once '/usr/share/php/xhprof_lib/utils/xhprof_lib.php';
        require_once '/usr/share/php/xhprof_lib/utils/xhprof_runs.php';
        xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);

        if ($source !== null) {
            $this->source = $source;
        }

        register_shutdown_function(array($this, 'stop'));
    }

    public function stop()
    {
        $xhprof_data = xhprof_disable();
        $xhprof_runs = new XHProfRuns_Default();
        $run_id = $xhprof_runs->save_run($xhprof_data, $this->source);

        // url to the XHProf UI libraries (change the host name and path)
        $profiler_url = sprintf('http://xhprof.local/?run=%s&source=%s', $run_id, $this->source);

        file_put_contents(__DIR__ . '/app/logs/xhprof.log', (microtime(true) - $this->time) . " " . $_SERVER['REQUEST_URI'] . ' - <a href="'. $profiler_url .'" target="_blank">Profiler output</a>' . "\n", FILE_APPEND);
    }
}
