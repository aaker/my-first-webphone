<?php
session_start();
define("SERVER", "alpha.netsapiens.com");
define("CLIENTID", "api@aaker.com");
define("CLIENTSECRET", "0c3e0a133a49189780c83181f27254de");


?>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<!-- Latest compiled and minified JavaScript -->
<!--<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>-->


<?php

  if (isset($_REQUEST['logout']))
  {
      unset($_SESSION['access']);
      unset($_SESSION['username']);

  }
  if (isset($_POST['username']))
  {
      $token = signin($_POST['username'],$_POST['password']);
      $_SESSION['access'] = $token;
      $_SESSION['username'] = $_POST['username'];
  }

  if (!isset($_SESSION['access']))
  {
    ?>
    <div class = "container">
       <form  role = "form" action = "index.php" method = "post">
          <h4 class = "form-signin-heading">My First WebRTC APP</h4>
          <input type = "text" class = "form-control" name = "username" required autofocus></br>
          <input type = "password" class = "form-control"  name = "password" placeholder></br>
          <button class = "btn btn-lg btn-primary btn-block" type = "submit" name = "login">Login</button>
       </form>
    </div>
    <?
  }
  else {
    ?>
    <div class = "container">

          <input type = "text" id="number2dial" class = "form-control" name = "number" placeholder="number to dial" required autofocus>
          <label><input id="isAudio" type="checkbox">Audio</label>
          <label><input id="isVideo" type="checkbox">Video</label>
          <button class = "btn btn-lg btn-primary btn-block" type = "submit" name = "login" onclick="makeCall(myUA, document.getElementById('number2dial').value)" >Call</button>
          <button class = "btn btn-lg btn-secondary btn-block" type = "submit" name = "login" onclick="endCall()" >Hangup</button>
            <BR>
          <table>
            <tbody>
              <tr>
                <td><video id="localVideo" width="300" height="150"></video></td>
                <td><video id="remoteVideo" width="300" height="150"></video></td>
              </tr>
            </tbody>
          </table>
    </div>

    <?
    $device = getDeviceInfo($_SESSION['username']);

    ?>
    <script>
    var token = "<?php echo $_SESSION['access'];?>";
    var username = "<?php echo $_SESSION['username'];?>";
    var device =  "<?php echo $device['aor']; ?>";
    var password ="<?php echo $device['authentication_key']; ?>";
    var server =  "<?php echo SERVER ?>:9002";
    var displayName = "<?php echo $device['sub_fullname']; ?>";
    </script>
    <?
  }
?>

<script src="sip.js" ></script>
<script src="phone.js"></script>


<?php
function __doCurl($url, $method, $authorization, $query, $postFields, &$http_response)
  {
    $start        = microtime(true);
    $curl_options = array(
        CURLOPT_URL => $url . ($query ? '?' . http_build_query($query) : ''),
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FORBID_REUSE => true,
        CURLOPT_TIMEOUT => 60
    );

    $headers = array();
    if ($authorization != NULL)
      {
        $headers[$authorization] = $authorization;
      } //$authorization != NULL



    $curl_options[$method] = true;
    if ($postFields != NULL)
      {
        $curl_options[CURLOPT_POSTFIELDS] = $postFields;
      } //$postFields != NULL

    if (sizeof($headers) > 0)
        $curl_options[CURLOPT_HTTPHEADER] = $headers;

    $curl_handle = curl_init();
    curl_setopt_array($curl_handle, $curl_options);
    $curl_result   = curl_exec($curl_handle);
    $http_response = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
    //print_r($http_response);
    curl_close($curl_handle);
    $end = microtime(true);
    if (!$curl_result)
        return NULL;
    else if ($http_response >= 400)
        return NULL;
    else
        return $curl_result;
  }

  function signin($username, $password)
  {
    $query = array(
        'grant_type' => 'password',
        'username' => $username,
        'password' => $password,
        'client_id' => CLIENTID,
        'client_secret' => CLIENTSECRET
    );

    $postFields    = http_build_query($query);

    $curl_result = __doCurl("https://" . SERVER . "/ns-api/oauth2/token", CURLOPT_POST, NULL, NULL, $postFields, $http_response);


    if (!$curl_result)
      {
        header('Location: index.php?error=server');
        exit;

      } //!$curl_result

    $token = json_decode($curl_result, /*assoc*/ true);

    if (!isset($token['access_token']))
      {
        header('Location: index.php?error=server');
        exit;

      } //!isset($token['access_token'])


    $token = $token['access_token'];
    return  $token;
  }

  function getDeviceInfo($username)
  {
    $query = array(
        'device' => "sip:".$username,
        'object' => "device",
        'action' => "read",
        'domain' => "aaker.com",
        'noNDP' => "yes",
        'format' => "json"
    );

    $postFields    = http_build_query($query);
    $curl_result = __doCurl("https://" . SERVER . "/ns-api/", CURLOPT_POST, "Authorization: Bearer " . $_SESSION['access'], NULL, $postFields, $http_response);

    if (!$curl_result)
      {
        header('Location: index.php?error=server');
        exit;
      }

    $token = json_decode($curl_result, /*assoc*/ true);

    if (!isset($token[0]['aor']))
      {
        header('Location: index.php?error=server');
        exit;
      }


    return  $token[0];
  }
?>
