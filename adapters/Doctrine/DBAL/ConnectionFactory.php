<?php

namespace Liuggio\Fastest\Doctrine\DBAL;

use Doctrine\Bundle\DoctrineBundle\ConnectionFactory as BaseConnectionFactory;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Liuggio\Fastest\Process\EnvCommandCreator;

/**
 * Creates a connection taking the db name from the env, this is great if you want to run parallel functional tests.
 */
class ConnectionFactory extends BaseConnectionFactory
{
    /**
     * Create a connection by name.
     *
     * @param array         $params
     * @param Configuration $config
     * @param EventManager  $eventManager
     * @param array         $mappingTypes
     *
     * @return \Doctrine\DBAL\Connection
     */
    public function createConnection(array $params, Configuration $config = null, EventManager $eventManager = null, array $mappingTypes = array())
    {
        if ($params['driver'] === 'pdo_sqlite') {
            $params['path'] = $this->getDbNameForSqlite($params);
        } elseif($params['driver'] === 'pdo_mysql') {
            $params['dbname'] = $this->getDbNameFromEnv($params['dbname']);
        }

        return parent::createConnection($params, $config, $eventManager, $mappingTypes);
    }

    private function getDbNameFromEnv($dbName)
    {
        if ($this->issetDbNameEnvValue()) {
            return $dbName.'_'.$this->getDbNameEnvValue();
        }

        return $dbName;
    }

    /**
     * @param array $params
     *
     * @return array
     */
    private function getDbNameForSqlite(array $params)
    {
        if ($this->issetDbNameEnvValue()) {
            $parts = explode('.', $params['path']);

            foreach ($parts as $key => &$part) {
                if ($key === (count($parts) - 2)) {
                    $part .= '_'.$this->getDbNameEnvValue();
                }
            }

            $params['path'] = implode('.', $parts);
        }

        return $params['path'];
    }

    private function issetDbNameEnvValue()
    {
        $dbName = $this->getDbNameEnvValue();

        return (!empty($dbName));
    }

    private function getDbNameEnvValue()
    {
        return getenv(EnvCommandCreator::ENV_TEST_CHANNEL_READABLE);
    }
}
