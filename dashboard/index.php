<?php
require '../keyauth.php';
require '../credentials.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['user_data'])) // if user not logged in
{
    header("Location: ../");
    exit();
}

$KeyAuthApp = new KeyAuth\api($name, $ownerid);

function findSubscription($name, $list)
{
    for ($i = 0; $i < count($list); $i++) {
        if ($list[$i]->subscription == $name) {
            return true;
        }
    }
    return false;
}

$username = $_SESSION["user_data"]["username"];
$subscriptions = $_SESSION["user_data"]["subscriptions"];
$subscription = $_SESSION["user_data"]["subscriptions"][0]->subscription;
$expiry = $_SESSION["user_data"]["subscriptions"][0]->expiry;
$ip = $_SESSION["user_data"]["ip"];
$creationDate = $_SESSION["user_data"]["createdate"];
$lastLogin = $_SESSION["user_data"]["lastlogin"];

if (isset($_POST['logout'])) {
    $KeyAuthApp->logout();
    session_destroy();
    header("Location: ../");
    exit();
}


?>
<!DOCTYPE html>
<html lang="en" class="bg-[#09090d] text-white overflow-x-hidden">

<head>
    <title>Orythia Chat</title>
    <script src="https://cdn.keyauth.cc/dashboard/unixtolocal.js"></script>

    <link rel="stylesheet" href="https://cdn.keyauth.cc/v3/dist/output.css">
</head>

<body class="min-h-screen bg-[#09090d] text-white">
  <div class="container mx-auto px-4">
    <!-- Header -->
    <header class="flex justify-end py-4">
      <form method="post">
        <button
          name="logout"
          class="inline-flex text-white bg-red-700 hover:opacity-60 focus:ring-0 font-medium rounded-lg text-sm px-5 py-2.5 transition duration-200">
          Logout
        </button>
      </form>
    </header>

    <!-- Main section for user data -->
    <main class="flex flex-col items-center justify-center py-8">
      <div class="text-center mb-8">
        <p class="text-md"><b>Logged in as:</b> <?= $username; ?></p>
        <p class="text-md">
            <b>IP:</b>
            <span class="inline-block ml-1 filter blur-sm hover:blur-none transition duration-300">
                <?= $ip; ?>
            </span>
        </p>
        <p class="text-md"><b>Creation Date:</b> <?= date('Y-m-d H:i:s', $creationDate); ?></p>
        <p class="text-md"><b>Last Login:</b> <?= date('Y-m-d H:i:s', $lastLogin); ?></p>
        <p class="text-md">
          <b>Subscription name:</b>
          <code class="bg-blue-800 rounded-md font-mono px-1">default</code>
          Active: <?= ((findSubscription("default", $_SESSION["user_data"]["subscriptions"]) ? 1 : 0) ? 'yes' : 'no'); ?>
        </p>
        <?php 
          for ($i = 0; $i < count($subscriptions); $i++) {
            echo "<p class='text-md'>#" . ($i+1) . " Subscription: " . $subscriptions[$i]->subscription . " - Subscription Expires: <script>document.write(convertTimestamp(" . $subscriptions[$i]->expiry . "));</script></p>";
          }
        ?>
      </div>
    </main>

    <!-- 2FA Section-->
    <section class="max-w-lg mx-auto pb-8">
      <?php
$chatChannel = "OrythiaChat"; // Passe ggf. den Channelnamen an

// Nachricht senden
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["chatmsg"])) {
    $msg = trim($_POST["chatmsg"]);
    if ($msg !== "") {
        $KeyAuthApp->ChatSend($msg, $chatChannel);
    }
    // Nach dem Senden neu laden, damit kein Resubmit bei Refresh
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}

// Nachrichten abrufen
$messages = $KeyAuthApp->ChatGet($chatChannel);
?>

<div class="bg-[#181a1b] rounded-xl shadow p-6 mb-4">
    <h3 class="text-xl font-bold mb-4">Orythia Chat</h3>
    <div class="h-64 overflow-y-auto bg-[#23272a] p-3 rounded mb-4">
        <?php
        if (!empty($messages) && is_array($messages)) {
            foreach (array_reverse($messages) as $msg) {
                echo "<div class='mb-2'><span class='text-blue-400 font-semibold'>"
                    . htmlspecialchars($msg->author)
                    . ":</span> "
                    . htmlspecialchars($msg->message)
                    . "</div>";
            }
        } else {
            echo "<div class='text-gray-400'>Noch keine Nachrichten.</div>";
        }
        ?>
    </div>
    <form method="post" class="flex gap-2">
        <input type="text" name="chatmsg" maxlength="200" required
       class="flex-1 px-3 py-2 rounded bg-white focus:outline-none"
       style="color: black;"
       placeholder="Nachricht eingeben...">
        <button type="submit"
                class="bg-blue-700 hover:bg-blue-800 px-4 py-2 rounded text-black font-semibold transition">
            Senden
        </button>
    </form>
</div>
    </section>
  </div>
</body>
</html>
