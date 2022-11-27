<link href="<?php echo plugin_dir_url(__FILE__); ?>styles.css" rel="stylesheet"> 
<?php

global $wpdb;
if (!defined('ABSPATH'))
    die();
const MAGIC_SEP = "\1\2\1\2";
$lookupnick = "";
$errors = 0;
$errs = [];
if (isset($_POST))
{
    foreach($_POST as $key => $value)
    {
      if (!strcasecmp($key,"nick_lookup"))
      {
        if (!strlen($value))
        {
            ++$errors;
            $errs[] = "No user specified";
        }
        $lookupnick = $value;
      }
    }
}

/* User struct */
class IRC_User
{
    public $IsUser = 0; //track it
    function __construct($lkup)
    {
        global $wpdb;
        if (is_numeric($lkup[0])) // we're looking up a UID because the first char is a numeric
            $term = "UID"; // nicks can't start with numbers
        else
            $term = "nick"; // look up the nick then buddy
        $user_info = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM dalek_user WHERE $term = %s", $lkup)
        );
        if ($user_info)
        {
            ++$this->IsUser;
            $this->info = (object)$user_info[0];
            $usermeta = $wpdb->get_results(
                $wpdb->prepare("SELECT * FROM dalek_user_meta WHERE UID = %s", $this->info->UID)
            );
            foreach($usermeta as $i => $um)
                $this->user_meta->{$um->meta_key} = $um->meta_data;
            

            $ison = $wpdb->get_results(
                $wpdb->prepare("SELECT chan, mode FROM dalek_ison WHERE nick = %s", strtolower($this->info->UID))
            );
            foreach($ison as $i)
                $this->ison->{$i->chan} = $i->mode;
            

            $user_channels = $wpdb->get_results(
                $wpdb->prepare("SELECT * FROM dalek_chaninfo WHERE (LOWER)owner = %s", strtolower($this->info->nick))
            );
            foreach($user_channels as $ch)
                $this->channels_owned->{$ch->channel} = $ch->regdate;

            if ($this->info->account && strlen($this->info->account))
                $this->wp = get_user_by("slug", strtolower($this->info->account));
        }

    }
}

dalek_generate_brief();
dalek_generate_lookup($lookupnick);
if ($errors)
    foreach($errs as $err)
        dalek_print_notification($err);
        
function dalek_print_notification(...$lines)
{
    ?>
        <p class="notification"><?php foreach ($lines as $line) echo $line."<br>"; ?></p>
    <?php
}

function dalek_generate_brief()
{
    echo "<h1>Dalek IRC Services</h1>
    <h2>User Management Panel</h2>";
}

function dalek_generate_lookup($lookupnick)
{
    ?>
    <form method="post" action="">
        Enter a nick or UID:<br><input type="text" name="nick_lookup" value="<?php echo $lookupnick; ?>">
        <input type="submit" name="submit" value="Lookup">
    </form>
    <?php
    if (!strlen($lookupnick))
        return;

    $user = new IRC_User($lookupnick);
    if (!$user->IsUser)
        dalek_print_notification("No such nick: \"$lookupnick\"");
    
    else dalek_display_results($user);
}
function dalek_get_extra_user_info(IRC_User $user)
{
    echo "<h2>Channels occupying:</h2>";
    echo "<table class='dalektablethin'><tr><td><strong><u>Channel</u></strong></td><td>Status</td></tr>";
    
    $away = 0;
    foreach((array)$user->ison as $info => $value)
    {
        
        echo "<td><strong>$info</strong></td>";
        echo "<td>".Dalek::convert_mode_to_word($value)."</td>";
        echo "</tr>";
    }

    echo "</table>";
}
function dalek_display_results(IRC_User $user)
{

    echo "<br><h2>Showing WHOIS information for ".$user->info->nick.":</h2>";
    echo "<table class='dalektablethin'><tr><td><strong><u>Information</u></strong></td><td><u><strong>Value</strong></u></td></tr>";
    
    $away = 0;
    foreach((array)$user->info as $info => $value)
    {
        $info[0] = strtoupper($info[0]); // make it look nicer
        if ($info == "Id")
            continue;

        if ($info == "Nick")
            $info = "Nick / Username";

        elseif ($info == "Timestamp")
        {
            $info = "Online since";
            $value = gmdate('l jS \of F Y \a\t h:i:sa', $value);
        }

        elseif ($info == "Secure")
            continue;
        elseif ($info == "Ip")
                $info = "IP Address";

        elseif ($info == "Away")
        {
            $value = (strlen($value)) ? "✅" : "❌";
            if (!strlen($value))
                ++$away;
        }
        elseif ($info == "Awaymsg")
        {
            if (!$away)
                continue;

            $info = "Away message";
        }
        elseif ($info == "Last")
        {
            $info = "Last seen by services";
            $value = gmdate('l jS \of F Y \a\t\ h:i:sa', $value);
        }
        echo "<td><strong>$info</strong></td>";
        if ($info !== "IP Address")
            echo "<td>$value</td>";
        else
            echo "<td>$value   <button onClick=\"javascript:window.open('https://dnschecker.org/ip-whois-lookup.php?query=$value', '_blank');\">WHOIS IP</button></td>";
        echo "</tr>";
    }

    echo "</table><br>";
    dalek_get_extra_user_info($user);
}
?>
