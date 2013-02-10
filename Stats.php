<?php
namespace __BOUNCER__;

class Bouncer_Stats
{

    protected static $_keys = array('id', 'fingerprint', 'time', 'hits', 'host', 'system', 'agent', 'referer', 'score');

    protected static $_namespace = '';

    protected static $_ignore_ips = array();

    protected static $_detailed_connections = false;

    protected static $_detailed_ips = false;

    protected static $_detailed_score = false;

    protected static $_detailed_host = false;

    protected static $_max_items = 100;

    protected static $_base_static_url = 'http://h6e.net/bouncer';

    public static function stats(array $options = array())
    {
        self::setOptions($options);

        if (isset($_GET['extract'])) {
            self::extract();
        } else if (isset($_GET['stats'])) {
            self::charts();
        } else if (isset($_GET['connections'])) {
            self::connections();
        } else if (isset($_GET['connection'])) {
            self::connection();
        } else if (isset($_GET['agent'])) {
            self::agent();
        } else {
            self::index();
        }
    }

    public static function setOptions(array $options = array())
    {
        if (isset($options['namespace'])) {
            self::$_namespace = $options['namespace'];
        }
        if (isset($options['keys'])) {
            self::$_keys = $options['keys'];
        }
        if (isset($options['ignore_ips'])) {
            self::$_ignore_ips = $options['ignore_ips'];
        }
        if (isset($options['detailed_connections'])) {
            self::$_detailed_connections = $options['detailed_connections'];
        }
        if (isset($options['detailed_ips'])) {
            self::$_detailed_ips = $options['detailed_ips'];
        }
        if (isset($options['detailed_score'])) {
            self::$_detailed_score = $options['detailed_score'];
        }
        if (isset($options['detailed_host'])) {
            self::$_detailed_host = $options['detailed_host'];
        }
        if (isset($options['max_items'])) {
            self::$_max_items = $options['max_items'];
        }
        if (isset($options['base_static_url'])) {
            self::$_base_static_url = $options['base_static_url'];
        }
    }

    public static function index()
    {
         require( dirname(__FILE__) . "/lib/browser.php" );
         require( dirname(__FILE__) . "/lib/os.php" );
         require( dirname(__FILE__) . "/lib/robot.php" );

         require_once dirname(__FILE__) . '/Rules/Basic.php';
         require_once dirname(__FILE__) . '/Rules/Fingerprint.php';
         require_once dirname(__FILE__) . '/Rules/Httpbl.php';

         $filters = array();
         if (!empty($_GET['filter'])) {
             foreach (explode(' ', $_GET['filter']) as $f) {
                 if (strpos($f, ':')) $filters[] = explode(':', trim($f));
             }
         }

         foreach ($filters as $filter) {
             list($filterKey, $filterValue) = $filter;
             if ($filterKey == 'fingerprint') {
                 $agents = Bouncer::getAgentsIndexFingerprint($filterValue, self::$_namespace);
                 break;
             }
             if ($filterKey == 'addr') {
                 $agents = Bouncer::getAgentsIndexHost(md5($filterValue), self::$_namespace);
                 break;
             }
         }

         if (empty($agents)) {
             $agents = Bouncer::getAgentsIndex(self::$_namespace);
         }

         $cssRules = array();
         $cssRules['unknown'] = 'background-image:url(' . self::$_base_static_url . '/images/os/question.png)';

         $count = 0;

         echo '<table class="bouncer-table">' . "\n";

         $linkify = true;

         echo '<tr>';
         foreach (self::$_keys as $key) {
             if ($key == 'fingerprint') {
                 echo '<th style="width:14px">', '', '</th>';
                 echo '<th colspan="3">', ucfirst($key), '</th>';
             } elseif ($key == 'host') {
                 if (self::$_detailed_host) {
                     echo '<th colspan="3">', ucfirst($key), '</th>';
                 } else {
                     echo '<th>', ucfirst($key), '</th>';
                 }
             } elseif ($key == 'features') {
                 echo '<th colspan="3">', ucfirst($key), '</th>';
             } else {
                 echo '<th>', ucfirst($key), '</th>';
             }
         }
         echo '</tr>' . "\n";

         foreach ($agents as $time => $id) {

             $identity = Bouncer::getIdentity($id);
             if (empty($identity)) {
                 continue;
             }

             if (in_array($identity['addr'], self::$_ignore_ips)) {
                 continue;
             }

             $last = Bouncer::getLastAgentConnection($id, self::$_namespace);
             if (empty($last)) {
                 continue;
             }

             $first = Bouncer::getFirstAgentConnection($id, self::$_namespace);

             $status = isset($last['result']) ? $last['result'][0] : 'neutral';
             $user = isset($last['request']['COOKIE']['user']) ? $last['request']['COOKIE']['user'] : 'none';
             $fingerprint = $identity['fingerprint'];
             $fgtype = Bouncer_Rules_Fingerprint::getType($identity);
             $fgtype = empty($fgtype) ? 'none' : $fgtype;
             $time = $last['time'];
             $hits = Bouncer::countAgentConnections($id, self::$_namespace);
             $addr = $identity['addr'];
             $host = $identity['host'];
             $useragent = isset($identity['headers']['User-Agent']) ? $identity['headers']['User-Agent'] : 'none';
             $extension = isset($identity['country']) ? $identity['country'] : (isset($identity['extension']) ? $identity['extension'] : 'numeric');
             $type = $identity['type'];
             $signature = $identity['signature'];
             $agent = $name = $identity['name'];
             $version = isset($identity['version']) ? $identity['version'] : null;
             $system = $system_name = isset($identity['os']) ? $identity['os'][0] : 'unknown';
             $system_version = isset($identity['os']) ? $identity['os'][1] : '';
             $referer = isset($first['request']['headers']['Referer']) ? $first['request']['headers']['Referer'] : 'none';
             $cookie = isset($last['request']['headers']['Cookie']) ? $last['request']['headers']['Cookie'] : '';
             $hascookie = isset($last['request']['headers']['Cookie']) ? 1 : 0;
             $score = isset($last['result']) ? round($last['result'][1]) : 0;
             $server = isset($last['request']['server']) ? $last['request']['server'] : '';
             $method = isset($last['request']['method']) ? $last['request']['method'] : 'GET';

             $kb = in_array($name, Bouncer::$known_browsers) ? 1 : 0;
             $te = isset($last['request']['headers']['TE']) ? 1 : 0;
             $via = isset($last['request']['headers']['Via']) ? $last['request']['headers']['Via'] : 'none';
             $cc = isset($last['request']['headers']['Cache-Control']) ? $last['request']['headers']['Cache-Control'] : 'none';
             $pragma = isset($last['request']['headers']['Pragma']) ? $last['request']['headers']['Pragma'] : 'none';
             $range = isset($last['request']['headers']['Range']) ? 1 : 0;
             $wap = isset($last['request']['headers']['x-wap-profile']) ? 1 : 0;

             $bluecoat = isset($last['request']['headers']['X-BlueCoat-Via']) ? 1 : 0;
             $squid = isset($last['request']['headers']['Via']) && isset($last['request']['headers']['Cache-Control'])
                 && $last['request']['headers']['Cache-Control'] == 'max-age=259200' ? 1 : 0;
             $proxy1 = isset($last['request']['headers']['FORWARDED_FOR']) && isset($last['request']['headers']['Client-ip']) ? 1 : 0;

             $proxy = ($bluecoat || $squid || $proxy1) ? 1 : 0;

             $xmoz = isset($last['request']['headers']['X-Moz']) ? $last['request']['headers']['X-Moz'] : 'none';
             $xpurpose = isset($last['request']['headers']['X-Purpose']) ? $last['request']['headers']['X-Purpose'] : 'none';
             $ka = isset($last['request']['headers']['Keep-Alive']) ? $last['request']['headers']['Keep-Alive'] : 'none';
             $conn = isset($last['request']['headers']['Connection']) ? $last['request']['headers']['Connection'] : 'none';
             $pc = isset($last['request']['headers']['Proxy-Connection']) ? $last['request']['headers']['Proxy-Connection'] : 'none';
             $pa = isset($last['request']['headers']['Proxy-Authentication']) ? $last['request']['headers']['Proxy-Authentication'] : 'none';

             $prefetch = ( (isset($xmoz) && $xmoz == 'prefetch') || (isset($xpurpose) && $xpurpose == 'prefetch') ) ? 1 : 0;

             $accept = isset($identity['headers']['Accept']) ? $identity['headers']['Accept'] : 'none';
             $ae = isset($identity['headers']['Accept-Encoding']) ? $identity['headers']['Accept-Encoding'] : 'none';
             $al = isset($identity['headers']['Accept-Language']) ? $identity['headers']['Accept-Language'] : 'none';
             $ac = isset($identity['headers']['Accept-Charset']) ? $identity['headers']['Accept-Charset'] : 'none';

             $java = isset($identity['headers']['Accept']) && $identity['headers']['Accept'] == 'text/html, image/gif, image/jpeg, *; q=.2, */*; q=.2' ? 1 : 0;
             $libwww = isset($last['request']['headers']['TE']) && $last['request']['headers']['TE'] == 'deflate,gzip;q=0.3' ? 1 : 0;
             $lwp = isset($last['request']['headers']['Cookie2']) && $last['request']['headers']['Cookie2'] == '$Version="1"' ? 1 : 0;

             $ie = $name == 'explorer' || in_array($name, Bouncer_Rules_Browser::$explorer_browsers) ? 1 : 0;
             $gecko = $name == 'firefox' || in_array($name, Bouncer_Rules_Browser::$gecko_browsers) ? 1 : 0;
             $webkit = in_array($name, Bouncer_Rules_Browser::$webkit_browsers) ? 1 : 0;

             $rss = ( in_array($name, Bouncer_Rules_Browser::$rss_browsers) || (isset($xmoz) && $xmoz == 'livebookmarks') ) ? 1 : 0;

             $js = isset($identity['features']['javascript']) && $identity['features']['javascript'] != 0 ? (int)($identity['features']['javascript'] > 0) : '';
             $img = isset($identity['features']['image']) && $identity['features']['image'] != 0 ? (int)($identity['features']['image'] > 0) : '';
             $iframe = isset($identity['features']['iframe']) && $identity['features']['iframe'] != 0 ? (int)($identity['features']['iframe'] > 0) : '';

             $ref = 0;
             if (!empty($first['request']['headers']['Referer'])) {
                 $preferer = @parse_url($first['request']['headers']['Referer']);
                 if (isset($preferer['host']) && $first['request']['server'] != $preferer['host']) {
                     $referer = $first['request']['headers']['Referer'];
                     $ref = 1;
                 }
             }

             $partialKeys = array('addr', 'host', 'referer', 'useragent', 'cookie', 'via');

             foreach ($filters as $filter) {
                 list($filterKey, $filterValue) = $filter;
                 $filterValue = str_replace('_', ' ', $filterValue);
                 if (strpos($filterKey, '-') === 0) {
                    $filterKey = substr($filterKey, 1);
                    if (isset($$filterKey)) {
                     if (in_array($filterKey, $partialKeys)) {
                         if (strpos($$filterKey, $filterValue) !== false) continue 2;
                     } else {
                         if ($$filterKey == $filterValue) continue 2;
                     }
                    }
                 } else {
                     if (isset($$filterKey)) {
                         if (in_array($filterKey, $partialKeys)) {
                             if (strpos($$filterKey, $filterValue) === false) continue 2;
                         } else {
                             if ($$filterKey != $filterValue) continue 2;
                         }
                     }
                 }
             }

             if ($linkify) {
                 $id = '<a href="?agent=' . $id . '">' . (strlen($id) == 32 ? substr($id, 0, 8) : $id) . '</a>';
             } else {
                 $id = substr($id, 0, 16);
             }

             if (isset($user) && $user != 'none' && $linkify) {
                 $user = '<a href="http://blogmarks.net/user/' . $user . '">' . $user . '</a>';
             }

             $fingerprint = substr($identity['fingerprint'], 0, 6);
             if ($linkify) {
                 $fingerprint = '<a href="?filter=fingerprint%3A' . $identity['fingerprint'] . '">' . $fingerprint . '</a>';
             }

             $time = date("d/m/Y.H:i:s", $last['time']);

             if ($type == 'browser' && isset($os[$system])) {
                 if (empty($cssRules[$system])) {
                     $cssRules[$system] = 'background-image:url(' . self::$_base_static_url . '/images/os/' . $os[$system]['icon'] . '.png)';
                 }
                 $system = $os[$system]['title'] . ' ' . $system_version;
             } else {
                 $system = '';
                 $system_name = '';
             }

             if (empty($cssRules[$extension])) {
                  $cssRules[$extension] = 'background-image:url(' . self::$_base_static_url . '/images/ext/' . $extension . '.png)';
             }
             if ($linkify) {
                 $host = '<a href="?filter=addr%3A' .  $addr . '">' .  $host . '</a>';
             }

             if ($type == 'browser') {
                 if (empty($cssRules[$name])) {
                     $cssRules[$name] = 'background-image:url(' . self::$_base_static_url . '/images/browser/' . $browser[$name]['icon'] . '.png)';
                 }
                 $agent = $browser[$name]['title'] . ' ' . $version;
             } else if ($type == 'robot') {
                 if (empty($cssRules[$name])) {
                     $cssRules[$name] = 'background-image:url(' . self::$_base_static_url . '/images/robot/' . $robot[$name]['icon'] . '.png)';
                 }
                 $agent = $robot[$name]['title'] . ' ' . $version;
             }

             if (!empty($referer)) {
                 $preferer = parse_url($referer);
             }
             if (!empty($referer) && isset($preferer['host']) && $first['request']['server'] != $preferer['host']) {
                 if ($linkify) {
                     $referer = '<a href="' . htmlspecialchars($referer) . '">' . $preferer['host'] . '</a>';
                 } else {
                     $referer = $preferer['host'];
                 }
             } else {
                 $referer = '';
             }

             echo '<tr class="', $status, '">';
             foreach (self::$_keys as $key) {
                 if ($key == 'fingerprint') {
                     echo '<td style="background:#' . substr($identity['fingerprint'], 0, 6) . '">&nbsp;</td>';
                     echo '<td>' . $fingerprint . '</td>';
                     echo '<td>' . ( isset($fgtype) && $fgtype != 'none' ? $fgtype : '' ) . '</td>';
                     if (method_exists('Bouncer', 'countAgentsFingerprint')) {
                         echo '<td>' . Bouncer::countAgentsFingerprint($identity['fingerprint'], self::$_namespace) . '</td>';
                     } else {
                         echo '<td>', '&nbsp;', '</td>';
                     }
                 } elseif ($key == 'features') {
                      if (isset($identity['features'])) {
                          echo '<td>' . $identity['features']['image'] . '</td>';
                          echo '<td>' . $identity['features']['iframe'] . '</td>';
                          echo '<td>' . $identity['features']['javascript'] . '</td>';
                          // echo '<td>' . $identity['features']['link'] . '</td>';
                      } else {
                          echo '<td colspan="3">', '&nbsp;', '</td>';
                      }
                 } else if ($key == 'host') {
                     echo '<td class="ic ' . $extension . '">', $host, '</td>';
                     if (self::$_detailed_host) {
                         echo '<td>', Bouncer_Rules_Httpbl::getType($identity), '</td>';
                         if (method_exists('Bouncer', 'countAgentsHost')) {
                             $hcount = Bouncer::countAgentsHost(md5($identity['addr']), self::$_namespace);
                             echo '<td>' . ($hcount ? $hcount : 1) . '</td>';
                         } else {
                             echo '<td>', '&nbsp;', '</td>';
                         }
                     }
                 } else if ($key == 'agent') {
                     echo '<td class="ic ' . $name . '">', $agent ,'</td>';
                 } else if ($key == 'system') {
                     echo '<td class="ic ' . $system_name . '">', $system ,'</td>';
                 } else {
                     if (isset($$key) && $$key != 'none') {
                          echo '<td>', $$key ,'</td>';
                     } else {
                         echo '<td>', '&nbsp;' ,'</td>';
                     }
                 }
             }
             echo '</tr>' . "\n";

             $count ++;
             if ($count >= self::$_max_items) {
                 break;
             }

         }

         echo '</table>';

         echo '<style type="text/css">' . "\n";
         foreach ($cssRules as $class => $content) {
             echo ".$class { $content; }\n";
         }
         echo '</style>';
    }

    public static function agent()
    {

        require_once dirname(__FILE__) . '/Rules/Network.php';
        require_once dirname(__FILE__) . '/Rules/Geoip.php';

        $id = $_GET['agent'];
        $identity = Bouncer::getIdentity($id);
        if (empty($identity)) {
            return;
        }

        list($identity, $result) = Bouncer::analyseIdentity($identity);
        list($status, $score, $details) = $result;

        echo '<table class="bouncer-table bouncer-table-agent">';
        echo '<tr>', '<th colspan="2">', 'Identity', '</th>', '</tr>';

        echo '<tr>', '<td>', 'Id', '</td>',
                     '<td>', $identity['id'], '</td>', '</tr>';

        echo '<tr>', '<td>', 'Signature', '</td>',
                     '<td>', '<a href="?filter=signature%3A' . $identity['signature'] . '">', $identity['signature'], '</a></td>', '</tr>';

        echo '<tr>', '<td>', 'Fingerprint', '</td>',
                     '<td>', '<a href="?filter=fingerprint%3A' . $identity['fingerprint'] . '">', $identity['fingerprint'], '</a></td>', '</tr>';

        echo '<tr>', '<td>', 'Type', '</td>',
             '<td>', '<a href="?filter=type%3A' . $identity['type'] . '">', $identity['type'], '</a></td>', '</tr>';

        echo '<tr>', '<td>', 'Name', '</td>',
                     '<td>', '<a href="?filter=name%3A' . $identity['name'] . '">', $identity['name'], '</a></td>', '</tr>';

        if (isset($identity['version'])) {
            echo '<tr>', '<td>', 'Version',
                         '</td>', '<td>', $identity['version'], '</td>', '</tr>';
        }
        if (isset($identity['os'])) {
            $system = $identity['os'][0];
            echo '<tr>', '<td>', 'OS', '</td>',
                         '<td>', '<a href="?filter=system%3A' . $system . '">', $system, '</a></td>', '</tr>';
        }

        echo '<tr>', '<td>', 'Addr', '</td>',
                     '<td>', '<a href="?filter=addr%3A' . $identity['addr'] . '">', $identity['addr'], '</a></td>', '</tr>';

        echo '<tr>', '<td>', 'Host', '</td>',
                     '<td>', $identity['host'], '</td>', '</tr>';

        echo '<tr>', '<td>', 'Extension', '</td>',
                     '<td>', '<a href="?filter=extension%3A' . $identity['extension'] . '">', $identity['extension'], '</a></td>', '</tr>';

        if (self::$_detailed_ips) {

            echo '<tr>', '<td>', 'Http:BL', '</td>';
            echo '<td>'; if (isset($identity['httpbl'])) print_r($identity['httpbl']); echo '</td>', '</tr>';

            echo '<tr>', '<td>', 'Reverse', '</td>', '<td>';
            $rev = preg_replace('/^(\\d+)\.(\\d+)\.(\\d+)\.(\\d+)$/', '$4.$3.$2.$1', $identity['addr']);
            $ptrs = dns_get_record("{$rev}.in-addr.arpa.", DNS_PTR);
            print_r($ptrs);
            echo '</td>', '</tr>';

            echo '<tr>', '<td>', 'DNS', '</td>', '<td>';
            $dns = dns_get_record($identity['host'], DNS_A);
            print_r($dns);
            echo '</td>', '</tr>';

            echo '<tr>', '<td>', 'Network', '</td>', '<td>';
            print_r(Bouncer_Rules_Network::doPWLookupBulk(array($identity['addr'])));
            echo '</td>', '</tr>';

        } else {

            $lookup = Bouncer_Rules_Network::doPWLookupBulk(array($identity['addr']));
            $network = $lookup[ $identity['addr'] ];

            echo '<tr>', '<td>', 'Network Org Name', '</td>', '<td>';
            echo $network['org-name'];
            echo '</td>', '</tr>';

            echo '<tr>', '<td>', 'Network Net Name', '</td>', '<td>';
            echo $network['net-name'];
            echo '</td>', '</tr>';

        }

        echo '<tr>', '<th colspan="2">', 'Agent HTTP Headers', '</th>', '</tr>';
        foreach ($identity['headers'] as $key => $value) {
            echo '<tr>', '<td>', $key, '</td>', '<td>', $value, '</td>', '</tr>';
        }

        if (self::$_detailed_score) {
            echo '<tr>', '<th>', 'Score', '</th>', '</tr>';
            foreach ($details as $detail) {
                list($value, $message) = $detail;
                echo '<tr>', '<td>', $message, '</td>', '<td>', $value, '</td>', '</tr>';
            }
            echo '<tr>', '<td>', 'Total', '</td>', '<td><b>', $score, '</b></td>', '</tr>';
        }

        echo '</table>';

        $connections = Bouncer::getAgentConnections($id, self::$_namespace);
        if (empty($connections)) {
            $connections = array();
        }

        self::_displayConnections($connections);
    }

    public static function connections()
    {
        $connections = Bouncer::backend()->getConnections(self::$_namespace);
        if (empty($connections)) {
            $connections = array();
        }

        self::_displayConnections($connections);
    }

    protected static function _displayConnections($connections)
    {
        echo '<table class="bouncer-table bouncer-table-connections">';
        echo '<tr>';
        if (self::$_detailed_connections) {
            echo '<th>', 'Id', '</th>';
        }
        echo '<th>', 'Time', '</th>';
        echo '<th>', 'Method', '</th>';
        echo '<th>', 'Server', '</th>';
        echo '<th>', 'URI', '</th>';
        echo '<th>', 'Referer', '</th>';
        if (self::$_detailed_connections) {
            echo '<th>', 'Score', '</th>';
            echo '<th>', 'Exec Time', '</th>';
            echo '<th>', 'Memory', '</th>';
            echo '<th>', 'Pid', '</th>';
        }
        echo '</tr>';
        foreach ($connections as $id => $connection) {
            if (empty($connection)) {
                continue;
            }
            $request = $connection['request'];
            $status = $connection['result'][0];
            echo '<tr class="', $status, '">';
            if (self::$_detailed_connections) {
                echo '<td>', '<a href="?connection=', $id, '">', substr($id, 0, 10), '</a></td>';
            }
            echo '<td>', date("d/m/Y H:i:s", $connection['time']), '</td>';
            echo '<td>' , $request['method'], '</td>';
            echo '<td>' , $request['server'], '</td>';
            echo '<td>' , urldecode($request['uri']), '</td>';
            if (!empty($request['headers']['Referer'])) {
                $preferer = parse_url($request['headers']['Referer']);
            }
            if (!empty($request['headers']['Referer']) && isset($preferer['host']) && $request['server'] != $preferer['host']) {
                echo '<td>', '<a href="', $request['headers']['Referer'], '">', $preferer['host'], '</td>';
            } else {
                echo '<td>', '</td>';
            }
            if (self::$_detailed_connections) {
                if (isset($connection['result'])) {
                    echo '<td>' , $connection['result'][1], '</td>';
                } else {
                    echo '<td>', '</td>';
                }
                if (isset($connection['exec_time'])) {
                    echo '<td>' , $connection['exec_time'] . 's', '</td>';
                } else {
                    echo '<td>', '</td>';
                }
                if (isset($connection['memory'])) {
                    echo '<td>' , round($connection['memory']/1024/1024) . 'M', '</td>';
                } else {
                    echo '<td>', '</td>';
                }
                if (isset($connection['pid'])) {
                    echo '<td>' , $connection['pid'], '</td>';
                } else {
                    echo '<td>', '</td>';
                }
            }
            echo '</tr>';
        }
        echo '</table>';
    }

    public static function connection()
    {
        if (!self::$_detailed_connections) {
            return;
        }

        $id = $_GET['connection'];
        $connection = Bouncer::get('connection-' . $id);
        if (empty($connection)) {
            return;
        }

        $request = $connection['request'];

        $identity = Bouncer::getIdentity($connection['identity']);
        $result = Bouncer::analyseRequest($identity, $request);
        list($status, $score, $details) = $result;

        echo '<table class="bouncer-table">';
        if (!empty($request['headers'])) {
            echo '<tr>', '<th>', 'Request Headers', '</th>' , '</tr>';
            foreach ($request['headers'] as $key => $value) {
                echo '<tr>', '<td>', $key, '</td>', '<td>', $value, '</td>', '</tr>';
            }
        }
        foreach (array('GET', 'COOKIE', 'POST') as $G) {
            if (!empty($request[$G])) {
                echo '<tr>', '<th>', $G, '</th>' , '</tr>';
                foreach ($request[$G] as $key => $value) {
                    echo '<tr>', '<td>', $key, '</td>', '<td>', $value, '</td>', '</tr>';
                }
            }
        }
        echo '<tr>', '<th>', 'Score', '</th>', '</tr>';
        foreach ($details as $detail) {
            list($value, $message) = $detail;
            echo '<tr>', '<td>', $message, '</td>', '<td>', $value, '</td>', '</tr>';
        }
        echo '<tr>', '<td>', 'Total', '</td>', '<td><b>', $score, '</b></td>', '</tr>';
        echo '</table>';
    }

    public static function charts()
    {
        $stats = array();
        $identities = array();
        $agents = Bouncer::getAgentsIndex(self::$_namespace);
        foreach ($agents as $id) {
            $key = $_GET['stats'];
            $identity = Bouncer::getIdentity($id);
            if (empty($identity) || empty($identity[$key])) {
                continue;
            }
            $value = $identity[$key];
            if (empty($stats[$value])) {
                $stats[$value] = 1;
            } else {
                $stats[$value] ++;
            }
            if (isset($_GET['aggregate'])) {
                $stats[$value] += Bouncer::countAgentConnections($id, self::$_namespace) - 1;
            }
            if (empty($identities[$value])) {
                $identities[$value] = $identity;
            }
        }

        ksort($stats);
        arsort($stats);

        echo '<table class="bouncer-table">';
        foreach ($stats as $value => $count) {
            $identity = $identities[$value];
            if ($count <= 2) {
                continue;
            }
            if (isset($_GET['unknown'])) {
                $type = Bouncer_Rules_Fingerprint::getType($identity);
                if (!empty($type)) {
                    continue;
                }
            }
            echo '<tr>';
            if ($key == 'fingerprint') {
                echo '<td width="20">', $count, '</td>';
                echo '<td width="20" style="background:#' . substr($value, 0, 6) . '">&nbsp;</td>';
                echo '<td width="20">', '<a href="?filter=fingerprint%3A' . $value . '">', $value, '</a></td>';
                echo '<td width="20">', Bouncer_Rules_Fingerprint::getType($identity) , '</td>';
                echo '<td class="ic ', $identity['name'], '">', '<a href="?filter=name%3A' . $identity['name'] . '">', $identity['name'], '</a> ',
                    isset($identity['version']) ? $identity['version'] : '', '</td>';

            } else if ($key == 'host') {
                echo '<td width="10">', $count, '</td>';
                echo '<td width="10">', '<a href="?filter=host%3A' . $value . '">', $value, '</a></td>';

            } else if ($key == 'addr') {
                echo '<td width="10">', $count, '</td>';
                echo '<td width="10">', '<a href="?filter=addr%3A' . $value . '">', $value, '</a></td>';

            } else if ($key == 'signature') {
                echo '<td width="10">', $count, '</td>';
                echo '<td width="10">', '<a href="?filter=signature%3A' . $value . '">', $value, '</a></td>';
                echo '<td width="10">', Bouncer_Rules_Fingerprint::getType($identity) , '</td>';
                echo '<td class="ic ', $identity['name'], '">', '<a href="?filter=name%3A' . $identity['name'] . '">', $identity['name'], '</a> ',
                    isset($identity['version']) ? $identity['version'] : '', '</td>';
                echo '<td>', isset($identity['headers']['User-Agent']) ? $identity['headers']['User-Agent'] : '' , '</td>';

            } else {
                echo '<td>', $count, '</td>';
                echo '<td>', $value, '</td>';
            }
            echo '</tr>';
        }
        echo '</table>';
    }

    public static function extract()
    {
        require_once dirname(__FILE__) . '/Rules/Fingerprint.php';

        $botnets = Bouncer_Rules_Fingerprint::get('botnet');

        $agents = Bouncer::getAgentsIndex(self::$_namespace);

        $fingerprints = array();

        // Collect level 1 fingerprints
        foreach ($agents as $id) {
            $key = $_GET['extract'];
            $identity = Bouncer::getIdentity($id);
            $fg = $identity['fingerprint'];
            if (strpos($identity['host'], $key) !== false) {
                $fingerprints[] = $fg;
            }
        }
        $fingerprints = array_unique($fingerprints);

        $hosts = array();

        // Collect level 1 hosts
        foreach ($agents as $id) {
            $identity = Bouncer::getIdentity($id);
            $fg = $identity['fingerprint'];
            $host = $identity['host'];
            if (in_array($fg, $fingerprints)) {
                if (empty($hosts[$host])) {
                    $hosts[$host] = 1;
                } else {
                    $hosts[$host] ++;
                }
            }
        }

        $fingerprints2 = array();

        // Collect level 2 fingerprints
        foreach ($agents as $id) {
            $identity = Bouncer::getIdentity($id);
            $fg = $identity['fingerprint'];
            $host = $identity['host'];
            if (isset($hosts[$host])) {
                 if (empty($fingerprints2[$fg])) {
                     $fingerprints2[$fg] = 1;
                 } else {
                     $fingerprints2[$fg] ++;
                 }
             }
        }

        $fingerprints3 = array();

        // Check Ambigous agents
        foreach ($agents as $id) {
            $identity = Bouncer::getIdentity($id);
            $fg = $identity['fingerprint'];
            $host = $identity['host'];
            if (isset($fingerprints2[$fg]) && empty($hosts[$host]) ) {
               if (empty($fingerprints3[$fg])) {
                     $fingerprints3[$fg] = 1;
                 } else {
                     $fingerprints3[$fg] ++;
                 }
            }
        }

        ksort($fingerprints2);
        arsort($fingerprints2);

        foreach ($fingerprints2 as $value => $count) {
            if (isset($fingerprints3[$value])) {
                continue;
            }
            if (in_array($value, $botnets)) {
                continue;
            }
            echo "\n'$value', // $count";
        }
    }

    public static function css()
    {
        ?>
        <style type="text/css">
        .bouncer-table { font-size:11px; font-family:"Helvetica Neue", Helvetica, Arial, sans-serif; }
        .bouncer-table th { color: #4A4A4A; }
        .bouncer-table td, .bouncer-table td a { color: #333333; }
        .bouncer-filter, .bouncer-table { width:100%; margin:auto; }
        .bouncer-filter { display:block; margin-bottom:10px; border:1px solid #DEDEDE; }
        .bouncer-table { border-collapse:collapse; }
        .bouncer-table td, .bouncer-table th { border:1px solid #DEDEDE; }
        .bouncer-table td { height:20px; padding:2px 4px; }
        .bouncer-table td.ic { padding-left:24px; }
        .bouncer-table tr.neutral { background-color:#E0E5F2; }
        .bouncer-table tr.bad { background-color:#EFE2EC; }
        .bouncer-table tr.suspicious { background-color:#f2e8e0; }
        .bouncer-table tr.nice { background-color:#e2f2e0; }
        .ic { padding-left:24px; background:4px 2px no-repeat }
        .fr { background-image:url(<?php echo self::$_base_static_url ?>/images/ext/fr.png) }
        .unknown  { background-image:url(<?php echo self::$_base_static_url ?>/images/os/question.png) }
        .explorer { background-image:url(<?php echo self::$_base_static_url ?>/images/browser/explorer.png) }
        .firefox  { background-image:url(<?php echo self::$_base_static_url ?>/images/browser/firefox.png) }
        .safari   { background-image:url(<?php echo self::$_base_static_url ?>/images/browser/safari.png) }
        .opera    { background-image:url(<?php echo self::$_base_static_url ?>/images/browser/opera.png) }
        .chrome   { background-image:url(<?php echo self::$_base_static_url ?>/images/browser/chrome.png) }
        .macosx   { background-image:url(<?php echo self::$_base_static_url ?>/images/os/macosx.png) }
        .windowsxp, .windowsmc { background-image:url(<?php echo self::$_base_static_url ?>/images/os/windowsxp.png) }
        .windowsvista, .windows7 { background-image:url(<?php echo self::$_base_static_url ?>/images/os/windowsvista.png) }
        </style>
        <?php
    }

    public static function search()
    {
        echo '<form method="get" action="" style="margin:0">';
        $value = isset($_GET['filter']) ? htmlspecialchars($_GET['filter']) : '';
        echo '<input type="search" name="filter" id="bouncer-filter" class="bouncer-filter" value="' . $value . '"/>';
        // echo '<script type="text/javascript">document.getElementById("bouncer-filter").focus();</script>';
        echo '</form>';
    }

}
