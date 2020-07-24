<?php
/*
        .__                      .__                          
  _____ |__|___.__._____    ____ |  |__  __ __  ____    ____  
 /     \|  <   |  |\__  \ _/ ___\|  |  \|  |  \/    \  / ___\ 
|  Y Y  \  |\___  | / __ \\  \___|   Y  \  |  /   |  \/ /_/  >
|__|_|  /__|/ ____|(____  /\___  >___|  /____/|___|  /\___  / 
      \/    \/          \/     \/     \/           \//_____/  

    @  DynDNSS Updater Tool
*/
error_reporting(E_ALL ^ E_NOTICE);


$username      = ''; // YOUR USERNAME ON DYNDNSS SERVICE
$password      = ''; // YOUR PASSWORD

// post-data = name=USERNMAE&pwd=PASSWORD

$query          = http_build_query(['name' => $username,'pwd' => $password]);
$login_page     = 'https://dyndnss.net/eng/login.php';


$login_post  = surf($login_page,$query,true);

if(login_control($login_post)){
    print "Logged in..".PHP_EOL;

    if($domains = grab_domains($login_post)){
        $path         = '&updater=manuell';
        $domain_count = count($domains);  

        if($domain_count > 1){
            print $domain_count." domains going to be updated".PHP_EOL;

            foreach($domains as $domain){
                $update_page = surf($domain.$path,false,false,true);

                if($result = update_result($update_page)){
                    print $domain.$path.PHP_EOL;
                    print $result;
                }else{
                    print "Can not see the message,please check your account and see if domain(s) has updated";
                }
            }
        }else{
            print $domains[0].$path." going to be updated".PHP_EOL;
            
            $update_page = surf($domains[0].$path,false,false,true);

            if($result = update_result($update_page)){
                print $result;
            }else{
                print "Can not see the message,please check your account and see if domain(s) has updated";
            }

        }

    }else{
        print "Can not find your domains..".PHP_EOL;
    }

}else{
    print "Can not log in! check your informations!".PHP_EOL;
}

@unlink('dyndnss.txt');


function surf( $url , $postfields = false, $cookiejar = false, $cookiefile = false){
    $curl = curl_init();
    curl_setopt_array($curl,[CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_URL => $url,
    CURLOPT_FOLLOWLOCATION => 1]);

    if($postfields != false){
        curl_setopt($curl,CURLOPT_POST,1);
        curl_setopt($curl,CURLOPT_POSTFIELDS,$postfields);
    }
    if( $cookiejar != false){
        curl_setopt($curl,CURLOPT_COOKIEJAR,'dyndnss.txt');
    }
    if($cookiefile != false){
        curl_setopt($curl,CURLOPT_COOKIEFILE,'dyndnss.txt');
    }

    $content = curl_exec($curl);
    curl_close($curl);
    return $content;
}

function login_control( $result ){
    if(strstr($result,'logout.php')){
        return true;
    }else{
        return false;
    }
}
function grab_domains( $result ){
    if(preg_match_all('@"href="(.*?)&updater=manuell" onfocus="@si',$result,$domains)){
        return $domains[1];
    }else{
        return false;
    }
}
function update_result( $content ){
    if(preg_match('@<div id="fiel">(.*?)<div class="team">@si',$content,$message)){
        return strip_tags($message[1]);
    }else{
        return false;
    }
}
