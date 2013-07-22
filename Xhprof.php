<?php

class Xhprof
{
    /**
     * @var float The microtime at the moment of start
     */
    protected $time = null;

    /**
     * @var string The source name that will be use in xhprof report
     */
    protected $source = null;

    /**
     * @var null|bool If true, no need to call stop()
     */
    protected $useShutDown = null;

    /**
     * @var string The dir where to put log of old report
     *             if null, only xhprof report will be generated
     */
    protected $logDir = null;

    /**
     * @var string Base url to generate direct link to xhprof page reprot
     */
    protected $baseUrl = null;

    /**
     * @param bool $useShutDown
     * @param string $source
     * @param string $logDir if null, only xhprof report will be generated
     * @param string $baseUrl
     */
    public function __construct($useShutDown = true, $source = 'default', $logDir = '.', $baseUrl = 'http://xhprof.local')
    {
        $this->useShutDown = $useShutDown;
        $this->source = $source;
        $this->logDir = $logDir;
        $this->baseUrl = $baseUrl;
    }

    public function start()
    {
        require_once '/usr/share/php/xhprof_lib/utils/xhprof_lib.php';
        require_once '/usr/share/php/xhprof_lib/utils/xhprof_runs.php';
        xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);

        if ($this->useShutDown) {
            register_shutdown_function(array($this, 'stop'));
        }
        $this->time = microtime(true);
    }

    public function stop()
    {
        $timeElapsed = microtime(true) - $this->time;

        $xhprof_data = xhprof_disable();
        $xhprof_runs = new XHProfRuns_Default();
        $run_id = $xhprof_runs->save_run($xhprof_data, $this->source);

        if (null !== $this->logDir) {
            // url to the XHProf UI libraries (change the host name and path)
            $profiler_url = sprintf('%s?run=%s&source=%s', $this->baseUrl, $run_id, $this->source);

            $str = sprintf("%f %s - %s \n", $timeElapsed, $_SERVER['REQUEST_URI'], $profiler_url);
            file_put_contents($this->logDir . '/xhprof.log', $str, FILE_APPEND);
        }
    }
}
