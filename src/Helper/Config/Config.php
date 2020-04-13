<?php

namespace Mostertb\TransmissionTools\Helper\Config;


use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class Config
{
    /**
     * @var Config
     */
    static private $instance;

    /**
     * @var array
     */
    private $config;


    /**
     * Config constructor. Purposely private scope to enforce Singleton pattern. This ensures that the config is only parsed
     * once per execution
     *
     * @throws \Exception
     */
    private function __construct()
    {
        // Load configuration from config.yaml
        try{
            $config = Yaml::parse(
                file_get_contents(__DIR__.'/../../../var/config/config.yaml')
            );
        } catch (ParseException $e) {
            throw new \Exception('Unable to parse config.yml: '.$e->getMessage(), 0, $e);
        }

        $processor = new Processor();
        $this->config = $processor->processConfiguration(
            new ConfigTreeBuilderProvider(),
            [$config]
        );
    }

    /**
     * @return Config
     */
    public static function getInstance()
    {
        if(is_null(self::$instance)){
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param bool $enabledOnly
     *
     * @return array
     */
    public function getClientConfigs($enabledOnly = false)
    {
        if($enabledOnly){
            $configs = [];
            foreach ($this->getConfig()['clients'] as $clientConfig){
                if($clientConfig['enabled']){
                    $configs[$clientConfig['name']] = $clientConfig;
                }
            }
            return $configs;
        }

        return $this->getConfig()['clients'];
    }

    /**
     * @param $clientName
     *
     * @throws \Exception
     */
    public function getClientConfigByName($clientName)
    {
        $clientConfigs = $this->getConfig()['clients'];
        if(!array_key_exists($clientName, $clientConfigs)){
            throw new \Exception('No config for a Transmission Client with the name \''.$clientName.'\' defined '.
                'in config.yml');
        }

        return $clientConfigs[$clientName];
    }

    /**
     * @return integer
     */
    public function getHttpTimeout()
    {
        return $this->getConfig()['http_timeout'];
    }

    /**
     * @return array
     */
    private function getConfig(): array
    {
        return $this->config;
    }
}