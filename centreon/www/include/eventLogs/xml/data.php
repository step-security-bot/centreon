<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/*
 * Copyright 2005-2021 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

// Include configurations files
include_once "../../../../config/centreon.config.php";

// Require Classes
require_once _CENTREON_PATH_ . "www/class/centreonSession.class.php";
require_once _CENTREON_PATH_ . "www/class/centreon.class.php";
require_once __DIR__ . '/../../../../bootstrap.php';
require_once __DIR__ . '/../Paginator.php';
require_once __DIR__ . '/PaginationRenderer.php';

// Connect to DB
$pearDB = $dependencyInjector['configuration_db'];
$pearDBO = $dependencyInjector['realtime_db'];

// Check Session
CentreonSession::start();
if (!CentreonSession::checkSession(session_id(), $pearDB)) {
    print "Bad Session";
    exit();
}

/**
 * @var Centreon $centreon
 */
$centreon = $_SESSION["centreon"];

/**
 * true: URIs will correspond to deprecated pages
 * false: URIs will correspond to new page (Resource Status)
 */
$useDeprecatedPages = $centreon->user->doesShowDeprecatedPages();

/**
 * Language informations init
 */
$locale = $centreon->user->get_lang();
putenv("LANG=$locale");
setlocale(LC_ALL, $locale);
bindtextdomain("messages", _CENTREON_PATH_ . "/www/locale/");
bind_textdomain_codeset("messages", "UTF-8");
textdomain("messages");

define("STATUS_OK", 0);
define("STATUS_WARNING", 1);
define("STATUS_CRITICAL", 2);
define("STATUS_UNKNOWN", 3);
define("STATUS_PENDING", 4);
define("STATUS_UP", 0);
define("STATUS_DOWN", 1);
define("STATUS_UNREACHABLE", 2);
define("TYPE_SOFT", 0);
define("TYPE_HARD", 1);

/**
 * Defining constants for the ACK message types
 */
define('SERVICE_ACKNOWLEDGEMENT_MSG_TYPE', 10);
define('HOST_ACKNOWLEDGEMENT_MSG_TYPE', 11);

// Include Access Class
include_once _CENTREON_PATH_ . "www/class/centreonACL.class.php";
include_once _CENTREON_PATH_ . "www/class/centreonXML.class.php";
include_once _CENTREON_PATH_ . "www/class/centreonGMT.class.php";
include_once _CENTREON_PATH_ . "www/include/common/common-Func.php";

$defaultLimit = $centreon->optGen['maxViewConfiguration'] > 1
    ? (int) $centreon->optGen['maxViewConfiguration']
    : 30;

/**
 * Get input vars
 */
$inputGet = [
    'lang' => isset($_GET['lang']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['lang']) : null,
    'id' => isset($_GET['id']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['id']) : null,
    'num' => filter_input(INPUT_GET, 'num', FILTER_VALIDATE_INT, ['options' => [ 'default' => 0]]),
    'limit' => filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT, ['options' => [ 'default' => $defaultLimit]]),
    'StartDate' => isset($_GET['StartDate']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['StartDate']) : null,
    'EndDate' => isset($_GET['EndDate']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['EndDate']) : null,
    'StartTime' => isset($_GET['StartTime']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['StartTime']) : null,
    'EndTime' => isset($_GET['EndTime']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['EndTime']) : null,
    'period' => filter_input(INPUT_GET, 'period', FILTER_VALIDATE_INT),
    'engine' => isset($_GET['engine']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['engine']) : null,
    'up' => isset($_GET['up']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['up']) : null,
    'down' => isset($_GET['down']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['down']) : null,
    'unreachable' => isset($_GET['unreachable'])
        ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['unreachable'])
        : null,
    'ok' => isset($_GET['ok']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['ok']) : null,
    'warning' => isset($_GET['warning']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['warning']) : null,
    'critical' => isset($_GET['critical']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['critical']) : null,
    'unknown' => isset($_GET['unknown']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['unknown']) : null,
    'notification' => isset($_GET['notification'])
        ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['notification'])
        : null,
    'alert' => isset($_GET['alert']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['alert']) : null,
    'oh' => isset($_GET['oh']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['oh']) : null,
    'error' => isset($_GET['error']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['error']) : null,
    'output' => isset($_GET['output']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['output']) : null,
    'search_H' => isset($_GET['search_H']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['search_H']) : null,
    'search_S' => isset($_GET['search_S']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['search_S']) : null,
    'search_host' => isset($_GET['search_host'])
        ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['search_host'])
        : null,
    'search_service' => isset($_GET['search_service'])
        ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['search_service'])
        : null,
    'export' => isset($_GET['export']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['export']) : null,
];

$inputPost = [
    'lang' => isset($_POST['lang']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['lang']) : null,
    'id' => isset($_POST['id']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['id']) : null,
    'num' => filter_input(INPUT_POST, 'num', FILTER_VALIDATE_INT, ['options' => [ 'default' => 0]]),
    'limit' => filter_input(INPUT_POST, 'limit', FILTER_VALIDATE_INT, ['options' => [ 'default' => $defaultLimit]]),
    'StartDate' => isset($_POST['StartDate']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['StartDate']) : null,
    'EndDate' => isset($_POST['EndDate']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['EndDate']) : null,
    'StartTime' => isset($_POST['StartTime']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['StartTime']) : null,
    'EndTime' => isset($_POST['EndTime']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['EndTime']) : null,
    'period' => filter_input(INPUT_POST, 'period', FILTER_VALIDATE_INT),
    'engine' => isset($_POST['engine']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['engine']) : null,
    'up' => isset($_POST['up']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['up']) : null,
    'down' => isset($_POST['down']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['down']) : null,
    'unreachable' => isset($_POST['unreachable'])
        ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['unreachable'])
        : null,
    'ok' => isset($_POST['ok']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['ok']) : null,
    'warning' => isset($_POST['warning']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['warning']) : null,
    'critical' => isset($_POST['critical']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['critical']) : null,
    'unknown' => isset($_POST['unknown']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['unknown']) : null,
    'notification' => isset($_POST['notification'])
        ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['notification'])
        : null,
    'alert' => isset($_POST['alert']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['alert']) : null,
    'oh' => isset($_POST['oh']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['oh']) : null,
    'error' => isset($_POST['error']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['error']) : null,
    'output' => isset($_POST['output']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['output']) : null,
    'search_H' => isset($_POST['search_H']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['search_H']) : null,
    'search_S' => isset($_POST['search_S']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['search_S']) : null,
    'search_host' => isset($_POST['search_host'])
        ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['search_host'])
        : null,
    'search_service' => isset($_POST['search_service'])
        ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['search_service'])
        : null,
    'export' => isset($_POST['export']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['export']) : null,
];

// Saving bound values
$queryValues = [];

$inputs = array();
foreach ($inputGet as $argumentName => $argumentValue) {
    if (!empty($inputGet[$argumentName])) {
        $inputs[$argumentName] = $inputGet[$argumentName];
    } elseif ((!empty($inputPost[$argumentName]))) {
        $inputs[$argumentName] = $inputPost[$argumentName];
    } else {
        $inputs[$argumentName] = null;
    }
}

$kernel = \App\Kernel::createForWeb();
$resourceController = $kernel->getContainer()->get(
    \Centreon\Application\Controller\MonitoringResourceController::class
);

// Start XML document root
$buffer = new CentreonXML();
$buffer->startElement("root");

/*
 * Security check
 */
$lang_ = \HtmlAnalyzer::sanitizeAndRemoveTags($inputs["lang"] ?? "-1");
$openid = \HtmlAnalyzer::sanitizeAndRemoveTags($inputs["id"] ?? "-1");
$sid = session_id();
(isset($sid)) ? $sid = $sid : $sid = "-1";

/*
 * Init GMT class
 */
$centreonGMT = new CentreonGMT($pearDB);
$centreonGMT->getMyGMTFromSession($sid, $pearDB);

/*
 * Check Session
 */
$contact_id = check_session($sid, $pearDB);

$is_admin = isUserAdmin($sid);
if (isset($sid) && $sid) {
    $access = new CentreonAcl($contact_id, $is_admin);
    $lca = array(
        "LcaHost" => $access->getHostsServices($pearDBO, 1),
        "LcaHostGroup" => $access->getHostGroups(),
        "LcaSG" => $access->getServiceGroups()
    );
}

// binding limit value
$num = filter_var($inputs['num'], FILTER_VALIDATE_INT, ['options' => ['default' => 0, 'min_range' => 0]]);
$limit = filter_var($inputs['limit'], FILTER_VALIDATE_INT, ['options' => ['default' => 30]]);

$StartDate = isset($inputs["StartDate"]) ? htmlentities($inputs["StartDate"]) : "";
$EndDate = isset($inputs["EndDate"]) ? $EndDate = htmlentities($inputs["EndDate"]) : "";
$StartTime = isset($inputs["StartTime"]) ? $StartTime = htmlentities($inputs["StartTime"]) : "";
$EndTime = isset($inputs["EndTime"]) ? $EndTime = htmlentities($inputs["EndTime"]) : "";
$auto_period = isset($inputs["period"]) ? $auto_period = (int) $inputs["period"] : -1;
$engine = isset($inputs["engine"]) ? $engine = htmlentities($inputs["engine"]) : "false";
$up = isset($inputs["up"]) ? htmlentities($inputs["up"]) : "true";
$down = isset($inputs["down"]) ? htmlentities($inputs["down"]) : "true";
$unreachable = isset($inputs["unreachable"]) ? htmlentities($inputs["unreachable"]) : "true";
$ok = isset($inputs["ok"]) ? htmlentities($inputs["ok"]) : "true";
$warning = isset($inputs["warning"]) ? htmlentities($inputs["warning"]) : "true";
$critical = isset($inputs["critical"]) ? htmlentities($inputs["critical"]) : "true";
$unknown = isset($inputs["unknown"]) ? htmlentities($inputs["unknown"]) : "true";
$notification = isset($inputs["notification"]) ? htmlentities($inputs["notification"]) : "false";
$alert = isset($inputs["alert"]) ? htmlentities($inputs["alert"]) : "true";
$oh = isset($inputs["oh"]) ? htmlentities($inputs["oh"]) : "false";
$error = isset($inputs["error"]) ? htmlentities($inputs["error"]) : "false";
$output = isset($inputs["output"]) ? urldecode($inputs["output"]) : $output = "";
$search_H = isset($inputs["search_H"]) ? htmlentities($inputs["search_H"]) : "VIDE";
$search_S = isset($inputs["search_S"]) ? htmlentities($inputs["search_S"]) : "VIDE";
$search_host = isset($inputs["search_host"]) ? htmlentities($inputs["search_host"], ENT_QUOTES, "UTF-8") : "";
$search_service = isset($inputs["search_service"]) ? htmlentities($inputs["search_service"], ENT_QUOTES, "UTF-8") : "";
$export = isset($inputs["export"]) ? htmlentities($inputs["export"], ENT_QUOTES, "UTF-8") : 0;

$start = 0;
$end = time();

if ($engine == "true") {
    $ok = "false";
    $up = "false";
    $unknown = "false";
    $unreachable = "false";
    $down = "false";
    $warning = "false";
    $critical = "false";
    $oh = "false";
    $notification = "false";
    $alert = "false";
}

if ($StartDate != "" && $StartTime == "") {
    $StartTime = "00:00";
}

if ($EndDate != "" && $EndTime == "") {
    $EndTime = "00:00";
}

if ($StartDate != "") {
    preg_match("/^([0-9]*)\/([0-9]*)\/([0-9]*)/", $StartDate, $matchesD);
    preg_match("/^([0-9]*):([0-9]*)/", $StartTime, $matchesT);
    $start = mktime($matchesT[1], $matchesT[2], "0", $matchesD[1], $matchesD[2], $matchesD[3]);
}
if ($EndDate != "") {
    preg_match("/^([0-9]*)\/([0-9]*)\/([0-9]*)/", $EndDate, $matchesD);
    preg_match("/^([0-9]*):([0-9]*)/", $EndTime, $matchesT);
    $end = mktime($matchesT[1], $matchesT[2], "0", $matchesD[1], $matchesD[2], $matchesD[3]);
}

// setting the startDate/Time using the user's chosen period
// and checking if the start date/time was set by the user, to avoid to display/export the whole data since 1/1/1970
$period = 86400;
if ($auto_period > 0 || $start === 0) {
    $period = (int)$auto_period;
    $start = time() - ($period);
    $end = time();
}

$general_opt = getStatusColor($pearDB);

$tab_color_service = array(
    STATUS_OK => 'service_ok',
    STATUS_WARNING => 'service_warning',
    STATUS_CRITICAL => 'service_critical',
    STATUS_UNKNOWN => 'service_unknown',
    STATUS_PENDING => 'pending'
);
$tab_color_host = array(
    STATUS_UP => 'host_up',
    STATUS_DOWN => 'host_down',
    STATUS_UNREACHABLE => 'host_unreachable'
);

$tab_type = array(
    "1" => "HARD",
    "0" => "SOFT"
);
$tab_class = array(
    "0" => "list_one",
    "1" => "list_two"
);
$tab_status_host = array(
    "0" => "UP",
    "1" => "DOWN",
    "2" => "UNREACHABLE"
);
$tab_status_service = array(
    "0" => "OK",
    "1" => "WARNING",
    "2" => "CRITICAL",
    "3" => "UNKNOWN"
);
$acknowlegementMessageType = [
    'badgeColor' => 'ack',
    'badgeText' => 'ACK'
];

/*
 * Create IP Cache
 */
if ($export) {
    $HostCache = array();
    $dbResult = $pearDB->query("SELECT host_name, host_address FROM host WHERE host_register = '1'");
    while ($h = $dbResult->fetch()) {
        $HostCache[$h["host_name"]] = $h["host_address"];
    }
    $dbResult->closeCursor();
}

$logs = array();

/*
 * Print infos..
 */
$buffer->startElement("infos");
$buffer->writeElement("opid", $openid);
$buffer->writeElement("start", $start);
$buffer->writeElement("end", $end);
$buffer->writeElement("notification", $notification);
$buffer->writeElement("alert", $alert);
$buffer->writeElement("error", $error);
$buffer->writeElement("up", $up);
$buffer->writeElement("down", $down);
$buffer->writeElement("unreachable", $unreachable);
$buffer->writeElement("ok", $ok);
$buffer->writeElement("warning", $warning);
$buffer->writeElement("critical", $critical);
$buffer->writeElement("unknown", $unknown);
$buffer->writeElement("oh", $oh);
$buffer->writeElement("search_H", $search_H);
$buffer->writeElement("search_S", $search_S);
$buffer->endElement();

$msg_type_set = array();
if ($alert == 'true') {
    array_push($msg_type_set, "'0'");
}
if ($alert == 'true') {
    array_push($msg_type_set, "'1'");
}
if ($notification == 'true') {
    array_push($msg_type_set, "'2'");
}
if ($notification == 'true') {
    array_push($msg_type_set, "'3'");
}
if ($error == 'true') {
    array_push($msg_type_set, "'4'");
}

$msg_req = '';
$suffix_order = " ORDER BY ctime DESC ";

$host_msg_status_set = array();
if ($up == 'true') {
    array_push($host_msg_status_set, "'" . STATUS_UP . "'");
}
if ($down == 'true') {
    array_push($host_msg_status_set, "'" . STATUS_DOWN . "'");
}
if ($unreachable == 'true') {
    array_push($host_msg_status_set, "'" . STATUS_UNREACHABLE . "'");
}

$svc_msg_status_set = array();
if ($ok == 'true') {
    array_push($svc_msg_status_set, "'" . STATUS_OK . "'");
}
if ($warning == 'true') {
    array_push($svc_msg_status_set, "'" . STATUS_WARNING . "'");
}
if ($critical == 'true') {
    array_push($svc_msg_status_set, "'" . STATUS_CRITICAL . "'");
}
if ($unknown == 'true') {
    array_push($svc_msg_status_set, "'" . STATUS_UNKNOWN . "'");
}

$flag_begin = 0;

$whereOutput = "";
if (isset($output) && $output != "") {
    $queryValues[':output'] = [\PDO::PARAM_STR => '%' . $output . '%'];
    $whereOutput = " AND logs.output like :output ";
}

$innerJoinEngineLog = "";
if ($engine == "true" && isset($openid) && $openid != "") {
    // filtering poller ids and keeping only real ids
    $pollerIds = explode(',', $openid);
    $filteredIds = array_filter($pollerIds, function ($id) {
        return is_numeric($id);
    });

    $pollerParams = [];
    if (count($filteredIds) > 0) {
        $in = '';
        foreach ($filteredIds as $index => $filteredId) {
            $key = ':pollerId' . $index;
            $queryValues[$key] = [\PDO::PARAM_INT => $filteredId];
            $pollerIds[] = $key;
        }
        $innerJoinEngineLog = ' INNER JOIN instances i ON i.name = logs.instance_name'
            . ' AND i.instance_id IN ( ' . implode(',', array_values($pollerIds)) . ')';
    }
}

if ($notification == 'true') {
    if (count($host_msg_status_set)) {
        $msg_req .= "(";
        $flag_begin = 1;
        $msg_req .= " (`msg_type` = '3' ";
        $msg_req .= " AND `status` IN (" . implode(',', $host_msg_status_set) . "))";
        $msg_req .= ") ";
    }
    if (count($svc_msg_status_set)) {
        if ($flag_begin == 0) {
            $msg_req .= "(";
        } else {
            $msg_req .= " OR ";
        }
        $msg_req .= " (`msg_type` = '2' ";
        $msg_req .= " AND `status` IN (" . implode(',', $svc_msg_status_set) . "))";
        if ($flag_begin == 0) {
            $msg_req .= ") ";
        }
        $flag_begin = 1;
    }
}
if ($alert == 'true') {
    if (count($host_msg_status_set)) {
        if ($flag_begin) {
            $msg_req .= " OR ";
        }
        if ($oh == true) {
            $msg_req .= " ( ";
            $flag_oh = true;
        }
        $flag_begin = 1;
        $msg_req .= " ((`msg_type` IN ('1', '10', '11') ";
        $msg_req .= " AND `status` IN (" . implode(',', $host_msg_status_set) . ")) ";
        $msg_req .= ") ";
    }
    if (count($svc_msg_status_set)) {
        if ($flag_begin) {
            $msg_req .= " OR ";
        }
        if ($oh == true && !isset($flag_oh)) {
            $msg_req .= " ( ";
        }
        $flag_begin = 1;
        $msg_req .= " ((`msg_type` IN ('0', '10', '11') ";
        $msg_req .= " AND `status` IN (" . implode(',', $svc_msg_status_set) . ")) ";
        $msg_req .= ") ";
    }
    if ($flag_begin) {
        $msg_req .= ")";
    }
    if ((count($host_msg_status_set) || count($svc_msg_status_set)) && $oh == 'true') {
        $msg_req .= " AND ";
    }
    if ($oh == 'true') {
        $flag_begin = 1;
        $msg_req .= " `type` = '" . TYPE_HARD . "' ";
    }
}
// Error filter is only used in the engine log page.
if ($error == 'true') {
    if ($flag_begin == 0) {
        $msg_req .= "AND ";
    } else {
        $msg_req .= " OR ";
    }
    $msg_req .= " `msg_type` IN ('4','5') ";
}
if ($flag_begin) {
    $msg_req = " AND (" . $msg_req . ") ";
}

$tab_id = preg_split("/\,/", $openid);
$tab_host_ids = array();
$tab_svc = array();
$filters = false;
foreach ($tab_id as $openid) {
    $tab_tmp = preg_split("/\_/", $openid);
    $id = "";
    $hostId = "";

    if (isset($tab_tmp[2])) {
        $hostId = (int)$tab_tmp[1];
        $id = (int)$tab_tmp[2];
    } elseif (isset($tab_tmp[1])) {
        $id = (int)$tab_tmp[1];
    }

    if ($id == "") {
        continue;
    }

    $type = $tab_tmp[0];
    if ($type == "HG" && (isset($lca["LcaHostGroup"][$id]) || $is_admin)) {
        $filters = true;
        // Get hosts from hostgroups
        $hosts = getMyHostGroupHosts($id);
        if (count($hosts) == 0) {
            $tab_host_ids[] = "-1";
        } else {
            foreach ($hosts as $h_id) {
                if (isset($lca["LcaHost"][$h_id])) {
                    $tab_host_ids[] = $h_id;
                    $tab_svc[$h_id] = $lca["LcaHost"][$h_id];
                }
            }
        }
    } elseif ($type == 'SG' && (isset($lca["LcaSG"][$id]) || $is_admin)) {
        $filters = true;
        $services = getMyServiceGroupServices($id);
        if (count($services) == 0) {
            $tab_svc[] = "-1";
        } else {
            foreach ($services as $svc_id => $svc_name) {
                $tab_tmp = preg_split("/\_/", $svc_id);
                $tmp_host_id = $tab_tmp[0];
                $tmp_service_id = $tab_tmp[1];
                $tab = preg_split("/\:/", $svc_name);
                $host_name = $tab[3];
                if (isset($lca["LcaHost"][$tmp_host_id][$tmp_service_id])) {
                    $tab_svc[$tmp_host_id][$tmp_service_id] = $lca["LcaHost"][$tmp_host_id][$tmp_service_id];
                }
            }
        }
    } elseif ($type == "HH" && isset($lca["LcaHost"][$id])) {
        $filters = true;
        $tab_host_ids[] = $id;
        $tab_svc[$id] = $lca["LcaHost"][$id];
    } elseif ($type == "HS" && isset($lca["LcaHost"][$hostId][$id])) {
        $filters = true;
        $tab_svc[$hostId][$id] = $lca["LcaHost"][$hostId][$id];
    } elseif ($type == "MS") {
        $filters = true;
        $tab_svc["_Module_Meta"][$id] = "meta_" . $id;
    }
}

// Build final request
$req = "SELECT SQL_CALC_FOUND_ROWS " . (!$is_admin ? "DISTINCT" : "") . "
        1 AS REALTIME,
        logs.ctime,
        logs.host_id,
        logs.host_name,
        logs.service_id,
        logs.service_description,
        logs.msg_type,
        logs.notification_cmd,
        logs.notification_contact,
        logs.output,
        logs.retry,
        logs.status,
        logs.type,
        logs.instance_name
        FROM logs " . $innerJoinEngineLog
    . (
    !$is_admin ?
        " INNER JOIN centreon_acl acl ON (logs.host_id = acl.host_id AND (acl.service_id IS NULL OR "
        . " acl.service_id = logs.service_id)) "
        . " WHERE acl.group_id IN (" . $access->getAccessGroupsString() . ") AND " :
        "WHERE "
    )
    . " logs.ctime > '{$start}' AND logs.ctime <= '{$end}' {$whereOutput} {$msg_req}";

/*
 * Add Host
 */
$str_unitH = "";
$str_unitH_append = "";
$host_search_sql = "";
if (count($tab_host_ids) == 0 && count($tab_svc) == 0) {
    if ($engine == "false") {
        $req .= " AND `msg_type` NOT IN ('4','5') ";
        $req .= " AND logs.host_name NOT LIKE '_Module_BAM%' ";
    }
} else {
    foreach ($tab_host_ids as $host_id) {
        if ($host_id != "") {
            $str_unitH .= $str_unitH_append . "'$host_id'";
            $str_unitH_append = ", ";
        }
    }
    if ($str_unitH != "") {
        $str_unitH = "(logs.host_id IN ($str_unitH) AND (logs.service_id IS NULL OR logs.service_id = 0))";
        if (isset($search_host) && $search_host != "") {
            $host_search_sql = " AND logs.host_name LIKE '%" . $pearDBO->escape($search_host) . "%' ";
        }
    }

    /*
     * Add services
     */
    $flag = 0;
    $str_unitSVC = "";
    $service_search_sql = "";
    if (
        (count($tab_svc) || count($tab_host_ids)) &&
        (
            $up == 'true' ||
            $down == 'true' ||
            $unreachable == 'true' ||
            $ok == 'true' || $warning == 'true' ||
            $critical == 'true' ||
            $unknown == 'true'
        )
    ) {
        $req_append = "";
        foreach ($tab_svc as $host_id => $services) {
            $str = "";
            $str_append = "";
            foreach ($services as $svc_id => $svc_name) {
                if ($svc_id != "") {
                    $str .= $str_append . $svc_id;
                    $str_append = ", ";
                }
            }
            if ($str != "") {
                if ($host_id === '_Module_Meta') {
                    $str_unitSVC .= $req_append . " (logs.host_name = '" . $host_id . "' "
                        . "AND logs.service_id IN (" . $str . ")) ";
                } else {
                    $str_unitSVC .= $req_append . " (logs.host_id = '" . $host_id . "' AND logs.service_id IN ($str)) ";
                }
                $req_append = " OR";
            }
        }
        if (isset($search_service) && $search_service != "") {
            $service_search_sql = " AND logs.service_description LIKE '%" . $pearDBO->escape($search_service) . "%' ";
        }
        if ($str_unitH != "" && $str_unitSVC != "") {
            $str_unitSVC = " OR " . $str_unitSVC;
        }
        if ($str_unitH != "" || $str_unitSVC != "") {
            $req .= " AND (" . $str_unitH . $str_unitSVC . ")";
        }
    } else {
        $req .= "AND 0 ";
    }
    $req .= " AND logs.host_name NOT LIKE '_Module_BAM%' ";
    $req .= $host_search_sql . $service_search_sql;
}

/*
 * calculate size before limit for pagination
 */
if (isset($req) && $req) {
    /*
     * Add Suffix for order
     */
    $req .= $suffix_order;
    $paginator = new Paginator((int) $num, (int) $limit);

    $limitReq = '';
    if (!$export) {
        $queryValues['offset'] = [\PDO::PARAM_INT => $paginator->getOffset()];
        $queryValues['limit'] = [\PDO::PARAM_INT => $paginator->nbResultsPerPage];
        $limitReq = ' LIMIT :offset, :limit';
    }

    $stmt = $pearDBO->prepare($req . $limitReq);
    foreach ($queryValues as $bindId => $bindData) {
        foreach ($bindData as $bindType => $bindValue) {
            $stmt->bindValue($bindId, $bindValue, $bindType);
        }
    }
    $stmt->execute();
    $rows = $stmt->rowCount();

    if ($result = $pearDBO->query('SELECT FOUND_ROWS()')) {
        $paginator = $paginator->withTotalRecordCount((int) $result->fetchColumn());
    }

    // If the current page is out of bounds, we force the max page correctly.
    if (!$export && 0 === $rows && $paginator->isOutOfUpperBound()) {
        $stmt->bindValue(':offset', $paginator->getOffsetMaximum(), \PDO::PARAM_INT);
        $stmt->execute();
    }

    $logs = $stmt->fetchAll();
    $stmt->closeCursor();

    $buffer->startElement("selectLimit");
    foreach ([10, 20, 30, 40, 50, 60, 70, 80, 90, 100] as $i) {
        $buffer->writeElement("limitValue", $i);
    }
    $buffer->writeElement("limit", $limit);
    $buffer->endElement();

    // add generated pages into xml
    $paginationRenderer = new PaginationRenderer($buffer);
    $paginationRenderer->render($paginator);

    /*
     * Full Request
     */
    $cpts = 0;
    // The query retrieves more than $limit results, but only the first $limit elements should be displayed
    foreach (array_slice($logs, 0, $limit) as $log) {
        $buffer->startElement("line");
        $buffer->writeElement("msg_type", $log["msg_type"]);

        /**
         * For an ACK there is no point to display RETRY and TYPE columns
         */
        $displayType = '';
        if (
            $log['msg_type'] != HOST_ACKNOWLEDGEMENT_MSG_TYPE
            && $log['msg_type'] != SERVICE_ACKNOWLEDGEMENT_MSG_TYPE
        ) {
            $displayType = $log['type'];

            if (isset($tab_type[$log['type']])) {
                $displayType = $tab_type[$log['type']];
            }
            $log["msg_type"] > 1 ? $buffer->writeElement("retry", "") : $buffer->writeElement("retry", $log["retry"]);
            $log["msg_type"] == 2 || $log["msg_type"] == 3
                ? $buffer->writeElement("type", "NOTIF")
                : $buffer->writeElement("type", $displayType);
        }

        /*
         * Color initialisation for services and hosts status
         * For ACK message types, display a badge 'ACK' in Yellow
         */
        $color = '';
        if (
            $log['msg_type'] == HOST_ACKNOWLEDGEMENT_MSG_TYPE
            || $log['msg_type'] == SERVICE_ACKNOWLEDGEMENT_MSG_TYPE
        ) {
            $color = $acknowlegementMessageType['badgeColor'];
        } elseif (isset($log["status"])) {
            if (
                isset($tab_color_service[$log["status"]])
                && !empty($log["service_description"])
            ) {
                $color = $tab_color_service[$log["status"]];
            } elseif (isset($tab_color_host[$log["status"]])) {
                $color = $tab_color_host[$log["status"]];
            }
        }

        /*
         * Variable initialisation to color "INITIAL STATE" on event logs
         */
        if ($log["output"] == "" && $log["status"] != "") {
            $log["output"] = "INITIAL STATE";
        }

        $buffer->startElement("status");
        $buffer->writeAttribute("color", $color);
        $displayStatus = $log["status"];
        if (
            $log['msg_type'] == HOST_ACKNOWLEDGEMENT_MSG_TYPE
            || $log['msg_type'] == SERVICE_ACKNOWLEDGEMENT_MSG_TYPE
        ) {
            $displayStatus = $acknowlegementMessageType['badgeText'];
        } elseif ($log['service_description'] && isset($tab_status_service[$log['status']])) {
            $displayStatus = $tab_status_service[$log['status']];
        } elseif (isset($tab_status_host[$log['status']])) {
            $displayStatus = $tab_status_host[$log['status']];
        }
        $buffer->text($displayStatus);
        $buffer->endElement();

        if (!strncmp($log["host_name"], "_Module_Meta", strlen("_Module_Meta"))) {
            preg_match('/meta_([0-9]*)/', $log["service_description"], $matches);
            $dbResult2 = $pearDB->query("SELECT meta_name FROM meta_service WHERE meta_id = '" . $matches[1] . "'");
            $meta = $dbResult2->fetch();
            $dbResult2->closeCursor();
            $buffer->writeElement("host_name", "Meta", false);
            $buffer->writeElement("real_service_name", $log["service_description"], false);
            $buffer->writeElement("service_description", $meta["meta_name"], false);
            unset($meta);
        } else {
            $buffer->writeElement("host_name", $log["host_name"], false);
            if ($export) {
                $buffer->writeElement("address", $HostCache[$log["host_name"]], false);
            }
            $buffer->writeElement("service_description", $log["service_description"], false);
            $buffer->writeElement("real_service_name", $log["service_description"], false);

            $serviceTimelineRedirectionUri = $useDeprecatedPages
                ? 'main.php?p=20201&amp;o=svcd&amp;host_name=' . $log['host_name'] . '&amp;service_description='
                    . $log['service_description']
                : $resourceController->buildServiceUri(
                    $log['host_id'],
                    $log['service_id'],
                    $resourceController::TAB_TIMELINE_NAME
                );

            $buffer->writeElement(
                "s_timeline_uri",
                $serviceTimelineRedirectionUri
            );
        }
        $buffer->writeElement("real_name", $log["host_name"], false);

        $hostTimelineRedirectionUri = $useDeprecatedPages
            ? 'main.php?p=20202&amp;o=hd&amp;host_name=' . $log['host_name']
            : $resourceController->buildHostUri($log['host_id'], $resourceController::TAB_TIMELINE_NAME);

        $buffer->writeElement(
            "h_timeline_uri",
            $hostTimelineRedirectionUri
        );
        $buffer->writeElement("class", $tab_class[$cpts % 2]);
        $buffer->writeElement("poller", $log["instance_name"]);
        $buffer->writeElement("date", $log["ctime"]);
        $buffer->writeElement("time", $log["ctime"]);
        $buffer->writeElement("output", $log["output"]);
        $buffer->writeElement("contact", $log["notification_contact"], false);
        $buffer->writeElement("contact_cmd", $log["notification_cmd"], false);
        $buffer->endElement();
        $cpts++;
    }
} else {
    $buffer->startElement("page");
    $buffer->writeElement("limit", $limit);
    $buffer->writeElement("selected", "1");
    $buffer->writeElement("num", 0);
    $buffer->writeElement("url_page", "");
    $buffer->writeElement("label_page", "");
    $buffer->endElement();
}

/*
 * Translation for tables.
 */
$buffer->startElement("lang");
$buffer->writeElement("d", _("Day"), 0);
$buffer->writeElement("t", _("Time"), 0);
$buffer->writeElement("O", _("Object name"), 0);
$buffer->writeElement("T", _("Type"), 0);
$buffer->writeElement("R", _("Retry"), 0);
$buffer->writeElement("o", _("Output"), 0);
$buffer->writeElement("c", _("Contact"), 0);
$buffer->writeElement("C", _("Command"), 0);
$buffer->writeElement("P", _("Poller"), 0);

$buffer->endElement();
$buffer->endElement();


/*
 * XML tag
 */
stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml") ?
    header("Content-type: application/xhtml+xml") : header("Content-type: text/xml");
header('Content-Disposition: attachment; filename="eventLogs-' . time() . '.xml"');

$buffer->output();
