<?php namespace Zephyrus\Security;

use Expose\FilterCollection;
use Expose\Manager;
use Expose\Report;
use Zephyrus\Application\Configuration;
use Zephyrus\Exceptions\IntrusionDetectionException;

class IntrusionDetection
{
    const REQUEST = 1;
    const GET = 2;
    const POST = 4;
    const COOKIE = 8;

    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var int
     */
    private $surveillance = 0;

    /**
     * @var IntrusionDetection
     */
    private static $instance = null;

    public static function getInstance(): self
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Execute the intrusion detection analysis using the specified monitored
     * inputs. If an intrusion is detected, the method will launch the detection
     * callback.
     *
     * @throws IntrusionDetectionException
     */
    public function run()
    {
        $guard = $this->getMonitoringInputs();
        if (empty($guard)) {
            throw new \RuntimeException("Nothing to monitor ! Either configure the IDS to monitor at least one input or 
                completely deactivate this feature.");
        }
        $this->manager->run($guard);
        if ($this->manager->getImpact() > 0) {
            $data = $this->getDetectionData($this->manager->getReports());

            throw new IntrusionDetectionException($data);
        }
    }

    /**
     * Applies the desired inputs to be analysed with bitwise operations using
     * the class constants (e.g. GET | POST | COOKIE). Default to GET and POST
     * only.
     *
     * @param int $surveillanceBitwise
     */
    public function setSurveillance(int $surveillanceBitwise)
    {
        $this->surveillance = $surveillanceBitwise;
    }

    /**
     * @return bool
     */
    public function isMonitoringRequest(): bool
    {
        return ($this->surveillance & self::REQUEST) > 0;
    }

    /**
     * @return bool
     */
    public function isMonitoringGet(): bool
    {
        return ($this->surveillance & self::GET) > 0;
    }

    /**
     * @return bool
     */
    public function isMonitoringPost(): bool
    {
        return ($this->surveillance & self::POST) > 0;
    }

    /**
     * @return bool
     */
    public function isMonitoringCookie(): bool
    {
        return ($this->surveillance & self::COOKIE) > 0;
    }

    /**
     * Retrieves the monitoring inputs to consider depending on the current
     * configuration.
     *
     * @return mixed[]
     */
    private function getMonitoringInputs(): array
    {
        $guard = [];
        if ($this->surveillance & self::REQUEST) {
            $guard['REQUEST'] = $_REQUEST;
        }
        if ($this->surveillance & self::GET) {
            $guard['GET'] = $_GET;
        }
        if ($this->surveillance & self::POST) {
            $guard['POST'] = $_POST;
        }
        if ($this->surveillance & self::COOKIE) {
            $guard['COOKIE'] = $_COOKIE;
        }
        return $guard;
    }

    /**
     * Constructs a custom basic associative array based on the PHPIDS report
     * when an intrusion is detected. Will contains essential data such as
     * impact, targeted inputs and detection descriptions.
     *
     * @param Report[] $reports
     * @return mixed[]
     */
    private function getDetectionData($reports): array
    {
        $data = [
            'impact' => 0,
            'detections' => []
        ];
        foreach ($reports as $report) {
            $variableName = $report->getVarName();
            $filters = $report->getFilterMatch();
            if (!isset($data['detections'][$variableName])) {
                $data['detections'][$variableName] = [
                    'value' => $report->getVarValue(),
                    'events' => []
                ];
            }
            foreach ($filters as $filter) {
                $data['detections'][$variableName]['events'][] = [
                    'description' => $filter->getDescription(),
                    'impact' => $filter->getImpact()
                ];
                $data['impact'] += $filter->getImpact();
            }
        }
        return $data;
    }

    /**
     * Constructor which initiates the configuration of the PHPIDS framework
     * and prepare the monitor component.
     */
    private function __construct()
    {
        $config = Configuration::getSecurityConfiguration();
        $filters = new FilterCollection();
        $filters->load();

        $this->manager = new Manager($filters, new class() extends \Psr\Log\AbstractLogger {
            public function log($level, $message, array $context = [])
            {
            }
        });
        if (isset($config['ids_exceptions'])) {
            $this->manager->setException($config['ids_exceptions']);
        }
        $this->surveillance = self::GET | self::POST;
    }
}
