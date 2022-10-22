

<?php

use WPMailSMTP\Vendor\phpseclib3\Common\Functions\Strings;

global $wpdb;

class TKL {
    static $list = [];
}

class Dalek_ChanList {
    static $list = [];
}
class Servers {
    static $list = [];
}

class Dalek_UserList {
    static $list = [];
    static $usercount = 0;
    static $servicescount = 0;
    static $opercount = 0;
    static $chancount = 0;
}

Dalek_UserList::$list = $wpdb->get_results("SELECT * FROM dalek_user");
Dalek_ChanList::$list = $wpdb->get_results("SELECT * FROM dalek_channels");
Servers::$list = $wpdb->get_results("SELECT * FROM dalek_server");
Dalek_UserList::$chancount = count($wpdb->get_results("SELECT * FROM dalek_channels"));
TKL::$list = $wpdb->get_results("SELECT * FROM dalek_tkldb");

foreach(Dalek_UserList::$list as $user)
{
    if (strpos($user->usermodes,"S") !== false)
    {
        ++Dalek_UserList::$servicescount;
        continue;
    }
    if (strpos($user->usermodes,"o") !== false)
    {
        ++Dalek_UserList::$opercount;
    }
    ++Dalek_UserList::$usercount;
}


dalek_generate_brief();

function dalek_generate_brief()
{
    echo "<h1>Dalek IRC Services</h1>
    <h2>IRC Overview Panel</h2>
    <ul class=\"brief\"><b><u>Online now:</b></u>
    <li class=\"brief\">Users: ".Dalek_UserList::$usercount."</li>
    <li class=\"brief\">Opers: ".Dalek_UserList::$opercount."</li>
    <li class=\"brief\">Services: ".Dalek_UserList::$servicescount."</li>
    <li class=\"brief\">Channels: ".Dalek_UserList::$chancount."</li></ul>";
}
function show_users(int $services = 0)
{
    if (!Dalek_UserList::$list)
    {
        echo "Could not find Dalek's SQL tables. Have you started Dalek yet?";
        return;
    }
    $usercount = 0;
    $opercount = 0;
    $servicescount = 0;
    ?>
    <table>
        <tr>
            <td><b>Nick</b></td>
            <?php if (!$services) echo "
            <td><b>IP</b></td>
            <td><b>GeoIP</b></td>
            <td><b>Account</b></td>"; ?>
            <td><b>Usermodes <a href="https://www.unrealircd.org/docs/User_Modes" target="_blank"><img src="https://cdn-icons-png.flaticon.com/512/159/159651.png" height="16" width="16"></a></b></td>
            <?php if (!$services) echo "
            <td><b>Oper</b></td>
            <td><b>Online since</b></td>"; ?>
            <td><b>Away</b></td>
            
        </tr>
    <?php
    foreach(Dalek_UserList::$list as $user)
    {
        if ((strpos($user->usermodes,"S") !== false && $services) || (!$services && strpos($user->usermodes,"S") == false))
        {
            ?>
            <tr>
                <td><?php echo $user->nick; ?></td>
                <?php echo (!$services) ? "<td>$user->ip</td>" : ""; ?>
                <?php echo (!$services) ? "<td>".dalek_get_geoip_string($user->UID)."</td>" : ""; ?>
                <?php echo (!$services) ? "<td>".dalek_get_account_with_link($user->account)."</td>" : ""; ?>
                <td><?php echo $user->usermodes; ?></td>
                <?php
                    if ($services) echo "";
                    elseif (strpos($user->usermodes,"o") != false)
                        echo "<td>".dalek_get_oper_type($user->UID)."</td>";
                    else echo "<td></td>";

                    if (!$services)
                        echo "<td>".gmdate("F j, Y, g:i a", dalek_get_umeta($user->UID, "creationtime"))."</td>";
                ?>
                <td><?php echo $user->awaymsg ? "Yes" : "No"; ?></td>
            </tr>
            <?php
        }
        
    }
    ?></table><?php
    
}


/**
 * Get GeoIP string for user in the table
 * @param UID
 * @return String
 */
function dalek_get_geoip_string($uid)
{
    $geoip = dalek_get_umeta($uid, "geoip");

    if (!BadPtr($geoip))
    {
        $tok = explode("|", $geoip);
        $tok2 = explode("=", $tok[0]);
        $cc = $tok2[1];
        $tok2 = explode("=", $tok[1]);
        $co = $tok2[1];

        return "$co, $cc ".get_country_flag_lmao($cc);
    }
    return "";
}

/**
 * BadPtr()
 * Convenience function for checking if a value is bad/empty/null
 */
function BadPtr($s)
{
    if (!isset($s) || $s == false || $s == null)
        return true;
    if (is_array($s) && empty($s))
        return true;
    if (is_string($s) && strlen($s) == 0)
        return true;
    return false;
}
/**
 * Get an ordered (structured) list of channels and status mode
 * @param uid the IRC UID of the user
 * @return String Ordered list of channels =]
 */
function dalek_ordered_channel_list($uid) : String
{
    global $wpdb;
    $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM dalek_ison WHERE nick = %s", $uid), ARRAY_A);
    if (!$result)
    {
        return "";
    }
    $list = "";

    foreach($result as $r)
    {
        $r = (object)$r;
        if (!BadPtr($r->mode))
        {
            if (strstr($r->mode,"Y"))
                $list .= "!";
            elseif (strstr($r->mode,"q"))
                $list .= "~";
            elseif (strstr($r->mode,"a"))
                $list .= "&";
            elseif (strstr($r->mode,"o"))
                $list .= "@";
            elseif (strstr($r->mode,"h"))
                $list .= "%";
            elseif (strstr($r->mode,"v"))
                $list .= "+";
        }
        $list .= $r->chan.", ";
    }
    $list = rtrim($list," ,");
    return $list;
}

/**
 * Lookup channel by name
 * @param name Name of the channel
 * @param info Name of the column in dalek_chaninfo
 */
function dalek_channel_get_reginfo($name, $info)
{
    global $wpdb;
    $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM dalek_chaninfo WHERE channel = %s", $name), ARRAY_A);
    if (!$result)
    {
        return false;
    }
    return $result[0][$info] ?? false;
}

function dalek_get_umeta($id, $name)
{
    global $wpdb;
    $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM dalek_user_meta WHERE UID = %s AND meta_key = %s", $id, $name), ARRAY_A);
    if (!$result)
        return "";
    return $result[0]['meta_data'] ?? "";
}

function dalek_get_oper_type($uid)
{
    global $wpdb;
    $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM dalek_user_meta WHERE UID = %s", $uid), ARRAY_A);
    if (!$result)
        return "";

    $str = "";
    $class = NULL;
    $login = NULL;
    foreach($result as $r)
    {
        if ($r["meta_key"] == "operclass")
            $class = $r["meta_data"];
        elseif ($r["meta_key"] == "operlogin")
            $login = $r["meta_data"];
    }
    return "$login ($class)";
}
function dalek_get_account_with_link($account)
{
    if (!($user = get_user_by('login', strtolower($account))))
        return "";
    $url = get_author_posts_url($user->ID);
    return "<a href=\"$url\">$account</a>";
}
function show_channels()
{
    if (!Dalek_ChanList::$list)
    {
        echo "Could not find Dalek's SQL tables. Have you started Dalek yet?";
        return;
    }
    ?>
    <table>
        <tr>
            <td><b>Channel</b></td>
            <td><b>Topic</b></td>
            <td><b>Modes </b><a href="https://www.unrealircd.org/docs/Channel_Modes" target="_blank"><img src="https://cdn-icons-png.flaticon.com/512/159/159651.png" height="16" width="16"></a></td>
            <td><b>Registered</b></td>
            <td><b>Owner</b></td>
        </tr>
    <?php
    foreach(Dalek_ChanList::$list as $chan)
    {
        ?>
        <tr>
            <td><?php echo $chan->channel; ?></td>
            <td><?php echo $chan->topic; ?></td>
            <td><?php echo $chan->modes; ?></td>
            <td><?php echo ($info = dalek_channel_get_reginfo($chan->channel, "regdate")) ? gmdate("F j, Y, g:i a", $info) : ""; ?></td>
            <td><?php echo ($info) ? dalek_get_account_with_link(dalek_channel_get_reginfo($chan->channel, "owner")) : ""; ?></td>
        </tr>
        <?php
    }
    ?></table><?php
}
function show_servers()
{
    if (!Servers::$list)
    {
        echo "Could not find Dalek's SQL tables. Have you started Dalek yet?";
        return;
    }
    ?>
    <table>
        <tr>
            <td><b>Server</b></td>
            <td><b>Version</b></td>
            <td><b>Description</b></td>
            <td><b>Linked since</b></td>
            <td><b>Link Security</b></td>
            <td><b>GeoIP</b></td>
            <td><b>TLS Cipher</b></td>
        </tr>
    <?php
    foreach(Servers::$list as $serv)
    {
        ?>
        <tr>
            <td><?php echo $serv->servername; ?></td>
            <td><?php echo dalek_get_server_version($serv->sid); ?></td>
            <td><?php echo $serv->version; ?></td>
            <td><?php echo gmdate("F j, Y, g:i a", dalek_get_umeta($serv->sid, "creationtime")); ?></td>
            <td><?php echo (dalek_get_umeta($serv->sid, "link-security") == 2) ? "Secure ✅" : "Insecure ❌"; ?></td>
            <td><?php echo dalek_get_geoip_string($serv->sid); ?></td>
            <td><?php echo dalek_get_umeta($serv->sid, "tls_cipher") ?? ""; ?></td>
        </tr>
        <?php
    }
    ?></table><?php
}

/**
 * Function get server version
 * @param sid
 * @return String
 */
function dalek_get_server_version($sid)
{
    global $wpdb;
    $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM dalek_server_version WHERE sid = %s", $sid), ARRAY_A);
    if (!$result)
        return "";
    return $result[0]['version'];
}

/**
 * Get the flag of a country code lmao
 * @param cc Country code lmao
 */
function get_country_flag_lmao($cc)
{
    if (BadPtr($cc))
        return "";
    return "<img src=\"https://flagcdn.com/16x12/".strtolower($cc).".png\">";
}

/**
 * Describe ban types
 * @param letter 
 * @return String
 */
function dalek_convert_tkl_letter($letter) : void
{
    if ($letter == "G")
        echo "Global mask (G)";
    if ($letter == "Z")
        echo "Global IP (Z)";
    if ($letter == "Q")   
        echo "Reserved nick (Q)";
    if ($letter == "s")
        echo "Shun (s)";
    
}

function show_bans()
{
    if (!TKL::$list)
    {
        echo "Could not find Dalek's SQL tables. Have you started Dalek yet?";
        return;
    }
    ?>
    <table>
        <tr>
            <td><b>IP / Mask</b></td>
            <td><b>Set by</b></td>
            <td><b>Set on</b></td>
            <td><b>Expiry</b></td>
            <td><b>Type</b></td>
            <td><b>Reason</b></td>
        </tr>
    <?php
    foreach(TKL::$list as $tkl)
    {
        ?>
        <tr>
            <td><?php echo $tkl->mask; ?></td>
            <td><?php echo $tkl->set_by; ?></td>
            <td><?php echo gmdate("F j, Y, g:i a", $tkl->timestamp); ?></td>
            <td><?php echo ($tkl->expiry == 0) ? "Never" : gmdate("F j, Y, g:i a", $tkl->expiry); ?></td>
            <td><?php dalek_convert_tkl_letter($tkl->type); ?></td>
            <td><?php echo $tkl->reason; ?></td>
        </tr>
        <?php
    }
    ?></table><?php
}
?>

<script src="<?php echo plugin_dir_url(__FILE__); ?>admin-menu-script.js" defer></script>
<link href="<?php echo plugin_dir_url(__FILE__); ?>styles.css" rel="stylesheet"> 
<ul class="tabs">
    <li data-tab-target="#users" class="tab">Users</li>
    <li data-tab-target="#channels" class="tab">Channels</li>
    <li data-tab-target="#network" class="tab">Network</li>
    <li data-tab-target="#bans" class="tab">Bans</li>
    <li data-tab-target="#services" class="tab">Services</li>
</ul>
<div class="tab-content">
    <div id="users" data-tab-content class="active">
        <h1>Users</h1>
        <?php show_users(); ?>
    </div>
    <div id="channels" data-tab-content>
        <h1>Channels</h1>
        <?php show_channels(); ?>
    </div>
    <div id="network" data-tab-content>
        <h1>Network</h1>
        <?php show_servers(); ?>
    </div>
    <div id="bans" data-tab-content>
        <h1>Bans</h1>
        <?php show_bans(); ?>
    </div>
    <div id="services" data-tab-content>
        <h1>Services</h1>
        <?php show_users(1); ?>
    </div>
</div>

