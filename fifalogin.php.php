<?php
/**
* @author - Fifa Guide
* @URL - tools.fifaguide.com
* @date - 01/01/14
* @version - 2.0
* based on code by Rob McGhee
**/
class Connector {
 
    private $user;
    private $password;
    private $hash;
    private $console;
 
    //initialize the class
    public function __construct($user, $password, $hash, $console) {
        $this->user  = $user;
        $this->password = $password;
        $this->hash  = $hash;
        $this->console   = $console;
    }
 
    public function connect()
    {
 
        echo"<pre>";
 
        $time = microtime( true );
        echo'<hr/>';
        ///// ZEROTH REDIRECT ////// gives us EASWkey
        echo "<br/><br/>Zeroth redirect (gets us EASWkey)<br/>";
        $cookie = "";
        $ch = curl_init( 'http://www.easports.com/' );
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_COOKIE, $cookie );
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: text/html,application/xhtml+xml,application/xml',
            'User-Agent:Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.65 Safari/537.36',
            'Accept-Encoding:gzip,deflate,sdch',
            'Accept-Language:en-US,en'));
 
        $r = curl_exec($ch);
        curl_close($ch);
 
        preg_match( '/easports_sess_com=(.*?)\;/', $r, $ea_sess_com );
        $ea_sess_com = $ea_sess_com[0];
 
        preg_match( '/EASW_KEY=(.*?)\;/', $r, $easw_key );
        $easw_key = $easw_key[0];
 
        echo "<br/><br/>EASW-Key<br/>";
        print_r($easw_key);
 
        echo'<hr/>';        
        ///// FIRST REDIRECT ////// gives us the url of the 2nd request
        echo "<br/><br/>first request (get EASFC-WEB-SESSION and xsrf)<br/>";
        $cookie = "";
        $ch = curl_init( 'http://www.easports.com/fifa/football-club/ultimate-team' );
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_COOKIE, $cookie );     
 
        $r = curl_exec($ch);
        curl_close($ch);
 
        $result = explode("\r\n", $r);
 
        foreach ($result as $key => $value) {
            if (strstr($value, 'Location')) 
            {
                $location = $value;
            }
        }
 
        $location = str_replace ('Location: ','', $location);
 
        preg_match( '/EASFC\-WEB\-SESSION\=(.*?)\;/i', $r, $easfc_web_session );
        $easfc_web_session = $easfc_web_session[0];
 
        preg_match( '/XSRF\-TOKEN\=(.*?)\;/i', $r, $xsrf );
        $xsrf = $xsrf[0];
 
        echo "<br/>result<br/>";
        print_r($result);
        echo "<br/>next location<br/>";
        print_r($location);  
 
echo'<hr/>';        
/////SECOND REDIRECT////// gives us the url to get the url to login
        echo "<br/><br/>second request (redireects to login page)<br/>";
        $cookie = $xsrf.'; '.$easfc_web_session.'; webun='.$this->user.';';
        $ch = curl_init( $location );
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_REFERER, 'http://www.easports.com/fifa/play');
        curl_setopt($ch, CURLOPT_COOKIE, $cookie );
 
        $r = curl_exec($ch);
        curl_close($ch);
 
        $result = explode("\r\n", $r);
 
        foreach ($result as $key => $value) {
            if (strstr($value, 'Location')) 
            {
                $location = $value;
            }
        }
        $location = str_replace ('Location: ','', $location);
 
        print_r($result);
        print_r($location);
 echo'<hr/>';       
/////THIRD REDIRECT////////// this is where we get the login url
 
        $ch = curl_init( $location );
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_REFERER, 'http://www.easports.com/fifa/play');
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie );
 
        $r = curl_exec($ch);
        curl_close($ch);
 
        $result = explode("\r\n", $r);
 
        foreach ($result as $key => $value) {
            if (strstr($value, 'Location')) 
            {
                $location = $value;
            }
        }
        $location = str_replace ('Location: ','', $location);
        $oldlocation = $location;
 
        //save jsessionid
        preg_match( '/JSESSIONID\=(.*?)\;/i', $r, $jsessionid );
        $jsessionid = $jsessionid[0];
        print_r($jsessionid);
 
        $cookie .= $jsessionid.";";
        echo "<br/><br/>third request<br/>";
        print_r($result);
        print_r($location);
        print_r($jsessionid);
echo'<hr/>';        
/////FOURTH REDIRECT////////// this is where we finally login
        echo "<br/><br/>fourth request(login, get x nexus)<br/>";
        $data_string = "email=".urlencode($this->user)."&password=".urlencode($this->password)."&_rememberMe=on&rememberMe=on&_eventId=submit&facebookAuth=";
        $ch = curl_init( $location );
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_COOKIE, $cookie );
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
            'Content-Type: application/x-www-form-urlencoded',                                                                                
            'Content-Length: ' . strlen($data_string),
            'Accept: text/html,application/xhtml+xml,application/xml',
            'Referer: '.$location,
            'User-Agent:Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.65 Safari/537.36',
            'Accept-Encoding:gzip,deflate,sdch',
            'Accept-Language:en-US,en')); 
 
        $r = curl_exec($ch);
        curl_close($ch);
        $result = explode("\r\n", $r);
 
        foreach ($result as $key => $value) {
            if (strstr($value, 'Location')) 
            {
                //save next location
                $location = $value;
            }
            else if (strstr($value, 'X-NEXUS-SEQUENCE')) 
            {
                //save X-NEXUS-SEQUENCE
                $xns = $value;
            }
        }
        $location = str_replace ('Location: ','', $location);
 
        print_r($result);
        print_r($location);
echo'<hr/>';
/////FIFTH REDIRECT////////// sid and remid
        echo "<br/><br/>fifth request(sid and remid)<br/>";
        $ch = curl_init( $location );
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_REFERER, $oldlocation);
        curl_setopt($ch, CURLOPT_COOKIE, $cookie );
 
        $r = curl_exec($ch);
        curl_close($ch);
 
        $result = explode("\r\n", $r);
 
        foreach ($result as $key => $value) {
            if (strstr($value, 'Location')) 
            {
                $location = $value;
            }
            else if (strstr($value, 'X-NEXUS-HOSTNAME')) 
            {
                //save X-NEXUS-HOSTNAME
                $xnh = $value;
            }
        }
 
        //save next location
        $location = str_replace ('Location: ','', $location);
 
        //save sid
        preg_match( '/sid\=(.*?)\;/i', $r, $sid );
        $sid = $sid[0];
 
        //save remid
        preg_match( '/remid\=(.*?)\;/i', $r, $remid );
        $remid = $remid[0];
 
        print_r($result);
        print_r($location);
 
echo'<hr/>';
/////SIX REDIRECT////////// update web session
        echo "<br/><br/>six request<br/>";
        $cookie = $xsrf.' '.$easfc_web_session.' hl=us;';
 
        $ch = curl_init( $location );
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_COOKIE, $cookie );
 
        $r = curl_exec($ch);
        curl_close($ch);
 
        $result = explode("\r\n", $r);
 
        //new web sesison id
        preg_match( '/EASFC\-WEB\-SESSION\=(.*?)\;/i', $r, $easfc_web_session );
        $easfc_web_session = $easfc_web_session[0];
 
        print_r($result);
echo'<hr/>';   
 
/////EIGHTH POINT 1 REDIRECT//////// get futweb
        echo "<br/><br/>8th request (fut id)<br/>";
        $location = 'http://www.easports.com/iframe/fut/?locale=en_US&baseShowoffUrl=http%3A%2F%2Fwww.easports.com%2Ffifa%2Ffootball-club%2Fultimate-team%2Fshow-off&guest_app_uri=http%3A%2F%2Fwww.easports.com%2Ffifa%2Ffootball-club%2Fultimate-team';
        $oldlocation = $location;
        $cookie = $xsrf.' '.$easfc_web_session.' hl=us;';
 
        $ch = curl_init( $location );
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_REFERER, 'http://www.easports.com/fifa/football-club/ultimate-team');
        curl_setopt($ch, CURLOPT_COOKIE, $cookie );
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                                                                                                        
            'Accept: text/html,application/xhtml+xml,application/xml',
            'Referer: http://www.easports.com/fifa/football-club/ultimate-team',
            'User-Agent:Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.65 Safari/537.36',
            'Accept-Encoding:gzip,deflate,sdch',
            'Accept-Language:en-US,en'));
 
        $r = curl_exec($ch);
        curl_close($ch);
 
        $result = explode("\r\n", $r);
        $location = '';
        foreach ($result as $key => $value) {
            if (strstr($value, 'Location')) 
            {
                //save next location
                $location = $value;             
                $location = str_replace ('Location: ','', $location);
            }
        }
 
        //save futid
        preg_match( '/futweb\=(.*?)\;/i', $r, $futweb );
        $futweb = $futweb[0];     
 
        print_r($location);
        echo'<br/>';
        print_r($futweb);
echo'<hr/>';
 
/////8.1 REDIRECT//////// gets url to do login_check
        echo "<br/><br/>8.1 request (get url to update fut id with login check)<br/>";
        $cookie = $remid.' '.$sid;
        $ch = curl_init( $location );
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_REFERER, 'http://www.easports.com/fifa/football-club/ultimate-team');
        curl_setopt($ch, CURLOPT_COOKIE, $cookie );
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                                                                                                        
            'Accept: text/html,application/xhtml+xml,application/xml',
            'Host:accounts.ea.com',
            'Referer: http://www.easports.com/fifa/football-club/ultimate-team',
            'User-Agent:Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.65 Safari/537.36',
            'Accept-Encoding:gzip,deflate,sdch',
            'Accept-Language:en-US,en'));
 
        $r = curl_exec($ch);
        curl_close($ch);
 
        $result = explode("\r\n", $r);
 
        $location = '';
        foreach ($result as $key => $value) {
            if (strstr($value, 'Location')) 
            {
                //save next location
                $location = $value;             
                $location = str_replace ('Location: ','', $location);
            }
        }
        echo'<xml>';
        print_r($location);
        echo'</xml><br/>';
 
echo'<hr/>'; 
/////8.2 REDIRECT//////// get new futweb
        echo "<br/><br/>8.2 request (get new futweb)<br/>";
        $cookie = $xsrf.' '.$easfc_web_session.' '.$futweb.' hl=us;';
 
        $ch = curl_init( $location );
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_REFERER, 'http://www.easports.com/fifa/football-club/ultimate-team');
        curl_setopt($ch, CURLOPT_COOKIE, $cookie );
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                                                                                                        
            'Accept: text/html,application/xhtml+xml,application/xml',
            'Referer: http://www.easports.com/fifa/football-club/ultimate-team',
            'User-Agent:Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.65 Safari/537.36',
            'Accept-Encoding:gzip,deflate,sdch',
            'Accept-Language:en-US,en'));
 
        $r = curl_exec($ch);
        curl_close($ch);
 
        $result = explode("\r\n", $r);
        $location = '';
        foreach ($result as $key => $value) {
            if (strstr($value, 'Location')) 
            {
                //save next location
                $location = $value;             
                $location = str_replace ('Location: ','', $location);
            }
        }
 
        //save futewb
        preg_match( '/futweb=(.*?);/', $r, $futweb );
        $futweb = $futweb[0];
        print_r($futweb);
 
echo'<hr/>';
 
/////8.3 REDIRECT//////// get nucleus
        echo "<br/><br/>8.3 request (get nucleus id)<br/>";
        $cookie = $xsrf.' '.$easfc_web_session.' '.$futweb.' hl=us;';
 
        $ch = curl_init( $location );
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_REFERER, 'http://www.easports.com/fifa/football-club/ultimate-team');
        curl_setopt($ch, CURLOPT_COOKIE, $cookie );
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                                                                                                        
            'Accept: text/html,application/xhtml+xml,application/xml',
            'Referer: http://www.easports.com/fifa/football-club/ultimate-team',
            'User-Agent:Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.65 Safari/537.36',
            'Accept-Encoding:gzip,deflate,sdch',
            'Accept-Language:en-US,en'));
 
        $r = curl_exec($ch);
        curl_close($ch);
 
        //save EASW_ID
        preg_match( "/EASW_ID = '(.*?)'/", $r, $nuc);
        print_r($nuc);
        $nuc = $nuc[1]; 
echo'<hr/>';
 
/////NINTH REDIRECT////////// get the shards
 
        $time = round(microtime(true) * 1000);
        $location = 'http://www.easports.com/iframe/fut/p/ut/shards?_='.$time;
        $cookie =  $easw_key.' '.$xsrf.' '.$easfc_web_session.' '.$futweb.' hl=us;';
        //$xns = substr($xns, -13);
 
        $ch = curl_init( $location );
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING ,"");
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_COOKIE, $cookie );
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                                                                                                        
            'Accept: application/json, text/javascript',
            'Content-Type: application/json',
            'Easw-Session-Data-Nucleus-Id: '.$nuc,
            'Referer: '.$oldlocation,
            'User-Agent:Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.65 Safari/537.36',
            'Accept-Encoding:gzip,deflate,sdch',
            'Accept-Language:en-US,en',
            'X-UT-Route:https://utas.fut.ea.com'));
 
        $r = curl_exec($ch);
        curl_close($ch);
 
        $result = explode("\r\n", $r);
 
        foreach ($result as $key => $value) {
            if (strstr($value, 'shardInfo')) 
            {
                $shards = json_decode($value, true);
            }
        }
 
        if ($this->console == 'PS')
        {
            $route = $shards['shardInfo']['1']['clientFacingIpPort'];
        }
        else
        {
            $route = $shards['shardInfo']['0']['clientFacingIpPort'];
        }
        $route = 'https://'.$route;
 
        echo "<br/><br/>Ninth request (shards)<br/>";
        print_r($result);
        print_r($route);
echo'<hr/>';        
/////TENTH REDIRECT////////// get the account persona info
        echo "<br/><br/>TENTH request (persona)<br/>";
        $time = round(microtime(true) * 1000);
        $location = 'http://www.easports.com/iframe/fut/p/ut/game/fifa14/user/accountinfo?_='.$time;
 
        $ch = curl_init( $location );
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_ENCODING ,"");
        curl_setopt($ch, CURLOPT_COOKIE, $cookie );
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                                                                                                        
            'Accept: text/json',
            'Easw-Session-Data-Nucleus-Id: '.$nuc,
            'Referer: '.$oldlocation,
            'User-Agent:Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.65 Safari/537.36',
            'Accept-Encoding:gzip,deflate,sdch',
            'Accept-Language:en-US,en',
            'X-UT-Embed-Error:true',
            'X-UT-Route:'.$route));
 
        $r = curl_exec($ch);
        curl_close($ch);
 
        $result = explode("\r\n", $r);
 
        foreach ($result as $key => $value) {
            if (strstr($value, 'AccountInfo')) 
            {
                $accountinfo = json_decode($value, true);
                $accountinfo = $accountinfo['userAccountInfo']['personas'];
                $personaId = $accountinfo[0]['personaId'];
                $last_access = array();
                //get most recent platform
                foreach ($accountinfo[0]['userClubList'] as $key => $value) {
                        $last_access[$key] = $value['lastAccessTime'];
                }
                $platform_index = array_keys($last_access, max($last_access));
                $platform = $accountinfo[0]['userClubList'][$platform_index[0]]['platform'];
            }
        }
 
        print_r($result);
        print_r($accountinfo);
        print_r($personaId);
        echo'<br/><br/>';
echo'<hr/>';        
/////ELEVENTH REDIRECT////////// get the X_UT_SID
        echo "<br/><br/>ELEVENTH request (X_UT_SID)<br/>";
        $time = round(microtime(true) * 1000);
        $location = 'http://www.easports.com/iframe/fut/p/ut/auth';
        $cookie =  $easw_key.' '.$sid.' '.$remid.' '.$xsrf.' '.$easfc_web_session.' '.$futweb.' '.$ea_sess_com.' '.$jsessionid.' hl=us; PRUM_EPISODES=s='.$time.'&r=http%3A//www.easports.com/;';
        echo $cookie;
        $account_data = array(
            'clientVersion' => 1,
            'identification' => array('authCode' => ''),
            'isReadOnly' => false,
            'locale' => "en-US",
            'method' => "authcode",
            'nuc' => (int)$nuc,
            'nucleusPersonaDisplayName' => $accountinfo[0]['personaName'],
            'nucleusPersonaId' => $personaId,
            'nucleusPersonaPlatform' => $platform,
            'priorityLevel' => 4,
            'sku' => "FUT14WEB"
        );
        $account_data = json_encode($account_data);
        print_r($account_data);
        $ch = curl_init( $location );
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_ENCODING ,"");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $account_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIE, $cookie );
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                                                                                                        
            'Content-Type: application/json',
            'Connection:keep-alive',
            'Accept: application/json, text/javascript',
            'Easw-Session-Data-Nucleus-Id: '.$nuc,
            'Content-Length: '.strlen($account_data),
            'Referer: http://www.easports.com/iframe/fut/?baseShowoffUrl=http%3A%2F%2Fwww.easports.com%2Ffifa%2Ffootball-club%2Fultimate-team%2Fshow-off&guest_app_uri=http%3A%2F%2Fwww.easports.com%2Ffifa%2Ffootball-club%2Fultimate-team&locale=en_US',
            'User-Agent:Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.65 Safari/537.36',
            'Accept-Encoding:gzip,deflate,sdch',
            'Accept-Language:en-US,en',
            'Origin:http://www.easports.com',
            'X-UT-Embed-Error:true',
            'X-Requested-With:XMLHttpRequest',
            'X-UT-Route:'.$route));
 
        $r = curl_exec($ch);
 
        preg_match( '/sid":"(.*?)"/', $r, $xutsid);
        print_r($xutsid);
        $xutsid = $xutsid[1];   
 
echo'<hr/>';
/////Twelth REDIRECT////////// validate
        $cookie =  $easw_key.' '.$sid.' '.$remid.' '.$xsrf.' '.$easfc_web_session.' '.$futweb.' '.$ea_sess_com.' '.$jsessionid.' hl=us; PRUM_EPISODES=s='.$time.'&r=http%3A//www.easports.com/;';
        echo "<br/><br/>Twelth request (validate)<br/>";
        $location = 'http://www.easports.com/iframe/fut/p/ut/game/fifa14/phishing/validate';
        $hash = 'answer='.$this->hash;
        $ch = curl_init( $location );
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $hash); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_ENCODING ,"");
        curl_setopt($ch, CURLOPT_COOKIE, $cookie );
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                                                                                                        
            'Accept: application/json',
            'Easw-Session-Data-Nucleus-Id: '.$nuc,
            'Content-Length: '.strlen($hash),
            'Content-Type: application/x-www-form-urlencoded',
            'Referer: '.$oldlocation,
            'Origin:http://www.easports.com',
            'User-Agent:Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.65 Safari/537.36',
            'Accept-Encoding:gzip,deflate,sdch',
            'Accept-Language:en-US,en',
            'X-UT-Embed-Error:true',
            'X-UT-Route:'.$route,
            'X-UT-SID:'.$xutsid));                                                                              
 
        $r = curl_exec($ch);
        curl_close($ch);
 
        preg_match( '/token":"(.*?)"/', $r, $phishkey);
        print_r($phishkey);
        $phishkey = $phishkey[1];   
 
        print_r($r);
 
echo "</pre>";
 
        //Build the array of items to return
        $returnitems = array(
        'EASW_KEY' => $easw_key,
        'EASF_SESS' => $easfc_web_session,
        'XSID' => $xutsid,
        'PHISHKEY' => $phishkey,
        'ROUTE' => $route
        );
 
        //Return the array
        return $returnitems;
    }
}
?>