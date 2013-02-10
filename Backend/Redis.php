<?php
namespace __BOUNCER__;

require_once 'Rediska.php';
require_once 'Rediska/Key.php';
require_once 'Rediska/Key/List.php';
require_once 'Rediska/Key/Set.php';

class Bouncer_Backend_Redis
{

    protected static $_rediska = null;

    protected static $_keys = array();

    public function __construct(array $options = array())
    {
        $defaults = array(
            'servers' => array(array('host' => '127.0.0.1'))
        );
        $options = array_merge($defaults, $options);
        self::$_rediska = new \Rediska($options);
    }

    public static function rediska()
    {
        return self::$_rediska;
    }

    public function clean()
    {
        self::$_rediska = null;
    }

    public static function get($keyname)
    {
        if (empty(self::$_keys[$keyname])) {
            $key = new \Rediska_Key($keyname);
            self::$_keys[$keyname] = $key->getValue();
        }
        return self::$_keys[$keyname];
    }

    public static function set($keyname, $value = null)
    {
        self::$_keys[$keyname] = $value;
        $key = new \Rediska_Key($keyname);
        return $key->setValue($value);
    }

    public static function index($indexKey, $value)
    {
        // $agentsIndex = new Rediska_Key_List($indexKey);
        // $agentsIndex->remove($value);
        // $agentsIndex->prepend($value);
        self::rediska()
                ->transaction()
                ->deleteFromList($indexKey, $value)
                ->prependToList($indexKey, $value)
                ->execute();
    }

    public static function indexAgent($agent, $namespace = '')
    {
        $indexKey = empty($namespace) ? 'agents' : "agents-$namespace";
        self::index($indexKey, $agent);
    }

    public static function indexAgentFingerprint($agent, $fingerprint, $namespace = '')
    {
        $indexKey = empty($namespace) ? "agents-$fingerprint" : "agents-$fingerprint-$namespace";
        self::index($indexKey, $agent);
    }

    public static function indexAgentHost($agent, $haddr, $namespace = '')
    {
        $indexKey = empty($namespace) ? "agents-$haddr" : "agents-$haddr-$namespace";
        self::index($indexKey, $agent);
    }

    public static function getAgentsIndex($namespace = '')
    {
        $indexKey = empty($namespace) ? 'agents' : "agents-$namespace";
        $agentsIndex = new \Rediska_Key_List($indexKey);
        return $agentsIndex->toArray(0, 10000);
    }

    public static function getAgentsIndexFingerprint($fingerprint, $namespace = '')
    {
        $indexKey = empty($namespace) ? "agents-$fingerprint" : "agents-$fingerprint-$namespace";
        $agentsIndex = new \Rediska_Key_List($indexKey);
        return $agentsIndex->toArray(0, 10000);
    }

    public static function getAgentsIndexHost($haddr, $namespace = '')
    {
        $indexKey = empty($namespace) ? "agents-$haddr" : "agents-$haddr-$namespace";
        $agentsIndex = new \Rediska_Key_List($indexKey);
        return $agentsIndex->toArray(0, 10000);
    }

    public static function countAgentsFingerprint($fingerprint, $namespace = '')
    {
        $indexKey = empty($namespace) ? "agents-$fingerprint" : "agents-$fingerprint-$namespace";
        $agentsIndex = new \Rediska_Key_List($indexKey);
        return count($agentsIndex);
    }

    public static function countAgentsHost($haddr, $namespace = '')
    {
        $indexKey = empty($namespace) ? "agents-$haddr" : "agents-$haddr-$namespace";
        $agentsIndex = new \Rediska_Key_List($indexKey);
        return count($agentsIndex);
    }

    public static function storeConnection($connection)
    {
        $key = uniqid();
        self::set("connection-" . $key, $connection);
        return $key;
    }

    public static function getConnectionsKeyList($namespace = '')
    {
        $key = empty($namespace) ? "connections" : "connections-$namespace";
        return new \Rediska_Key_List($key);
    }

    public static function getAgentConnectionsKeyList($agent, $namespace = '')
    {
        $key = empty($namespace) ? "connections-$agent" : "connections-$namespace-$agent";
        return new \Rediska_Key_List($key);
    }

    public static function indexConnection($key, $agent, $namespace = '')
    {
        $connections = self::getConnectionsKeyList($namespace);
        $connections->prepend($key);

        $agentConnections = self::getAgentConnectionsKeyList($agent, $namespace);
        $agentConnections->prepend($key);
    }

    public static function getConnections($namespace = '')
    {
        $connections = self::getConnectionsKeyList($namespace);
        $keys = $connections->toArray(0, 250);
        $result = array();
        if (empty($keys)) {
            return null;
        }
        foreach ($keys as $key) {
            $result[$key] = self::get("connection-" . $key);
        }
        return $result;
    }

    public static function getAgentConnections($agent, $namespace = '')
    {
        $connections = self::getAgentConnectionsKeyList($agent, $namespace);
        $keys = $connections->toArray(0, 250);
        $result = array();
        if (empty($keys)) {
            return null;
        }
        foreach ($keys as $key) {
            $result[$key] = self::get("connection-" . $key);
        }
        return $result;
    }

    public static function getLastAgentConnection($agent, $namespace = '')
    {
        $connections = self::getAgentConnectionsKeyList($agent, $namespace);
        $key = $connections[0];
        return self::get("connection-" . $key);
    }

    public static function getFirstAgentConnection($agent, $namespace = '')
    {
        $connections = self::getAgentConnectionsKeyList($agent, $namespace);
        $key = $connections[-1];
        return self::get("connection-" . $key);
    }

    public static function countAgentConnections($agent, $namespace = '')
    {
        $connections = self::getAgentConnectionsKeyList($agent, $namespace);
        return count($connections);
    }

    public static function setIdentity($id, $identity)
    {
        return self::set("identity-$id", $identity);
    }

    public static function getIdentity($id)
    {
        return self::get("identity-$id");
    }

}
