<?php
// Envoi Telegram à chaque visite
$telegram_bot_token = 'TON TOKEN BOT';
$telegram_chat_id = 'TON ID'; // ✅ Ton chat ID

function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
    else return $_SERVER['REMOTE_ADDR'];
}

function getGeoInfo($ip) {
    // API de géolocalisation gratuite : ip-api
    $json = file_get_contents("http://ip-api.com/json/$ip");
    return json_decode($json, true);
}

// Récupère l'IP de l'utilisateur
$ip = getUserIP();
$geoInfo = getGeoInfo($ip);  // Récupère les infos de géolocalisation

// Récupère la date et l'heure
$time = date('Y-m-d H:i:s');

// Récupère les informations du navigateur
$ua = $_SERVER['HTTP_USER_AGENT'];
$lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
$referer = $_SERVER['HTTP_REFERER'] ?? 'Accès direct';
$host = gethostbyaddr($ip);

// Récupère les informations de la session
$device = (strpos($ua, 'Mobile') !== false) ? 'Mobile' : 'Desktop';

// Information géographique
$city = $geoInfo['city'] ?? 'Inconnue';
$region = $geoInfo['regionName'] ?? 'Inconnue';
$country = $geoInfo['country'] ?? 'Inconnu';
$isp = $geoInfo['isp'] ?? 'Inconnu';
$zip = $geoInfo['zip'] ?? 'Inconnu';

// Détails de la visite
$device = (strpos($ua, 'Mobile') !== false) ? 'Mobile' : 'Desktop';

// Message Telegram contenant toutes les informations
$msg = "🔍 Nouvelle visite sur le site OSINT\n\n🕒 Heure : $time\n🌍 IP : $ip\n🧭 Navigateur : $ua\n🌐 Langue : $lang\n📍 Ville : $city\n🗺️ Région : $region\n🌎 Pays : $country\n🛠️ FAI : $isp\n🆔 Hostname : $host\n📌 Code Postal : $zip\n📱 Appareil : $device\n🔗 Référent : $referer";

file_get_contents("https://api.telegram.org/bot$telegram_bot_token/sendMessage?chat_id=$telegram_chat_id&text=" . urlencode($msg));

// Début HTML + traitement connexion
session_start();
$pdo = new PDO('mysql:host=localhost;dbname=osint_site', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mdp = $_POST['mot_de_passe'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($mdp, $user['mot_de_passe'])) {
        $_SESSION['user'] = $user;

        // Envoi message Telegram pour connexion réussie
        $msg = "✅ Connexion réussie\n👤 {$user['prenom']} {$user['nom']}\n📧 $email\n📱 Numéro : {$user['telephone']}\n🕒 Heure : $time\n🌍 IP : $ip";
        file_get_contents("https://api.telegram.org/bot$telegram_bot_token/sendMessage?chat_id=$telegram_chat_id&text=" . urlencode($msg));

        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Email ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Connexion | OSINT</title>
  <style>
    body {
      background: #111;
      color: #fff;
      font-family: Arial, sans-serif;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      margin: 0;
    }
    .box {
      background: #222;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 0 10px #0ff;
      width: 100%;
      max-width: 400px;
    }
    h2 {
      text-align: center;
      color: #0ff;
    }
    label {
      display: block;
      margin-top: 15px;
    }
    input {
      width: 100%;
      padding: 10px;
      border: none;
      border-radius: 8px;
      margin-top: 5px;
      background: #333;
      color: #fff;
    }
    button {
      margin-top: 20px;
      width: 100%;
      padding: 12px;
      background: #0ff;
      color: #000;
      border: none;
      border-radius: 8px;
      font-weight: bold;
      cursor: pointer;
    }
    .error {
      color: red;
      margin-top: 15px;
      text-align: center;
    }

    /* Style pour la popup des cookies */
    #cookie-popup {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      background: rgba(0, 0, 0, 0.8);
      color: white;
      padding: 15px;
      text-align: center;
      display: none;
      z-index: 1000;
    }
    #cookie-popup button {
      padding: 10px;
      background: #0ff;
      color: #000;
      border: none;
      border-radius: 8px;
      cursor: pointer;
    }

    /* Style de la popup Telegram */
    .popup {
      position: fixed;
      bottom: 20px;
      left: 50%;
      transform: translateX(-50%);
      background-color: #222;
      color: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0, 255, 255, 0.5);
      display: none;
      z-index: 9999;
      width: 90%;
      max-width: 400px;
    }

    .popup .close-btn {
      position: absolute;
      top: 5px;
      right: 10px;
      color: #fff;
      font-size: 20px;
      cursor: pointer;
    }

    .popup a {
      color: #0ff;
      text-decoration: none;
      font-weight: bold;
    }

    @keyframes popupShow {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .popup.show {
      display: block;
      animation: popupShow 0.5s ease-in-out;
    }

    /* Nouveau style pour le bouton d'inscription */
    .signup-btn {
      margin-top: 15px;
      padding: 12px;
      background: #0f8;
      color: #000;
      border: none;
      border-radius: 8px;
      font-weight: bold;
      cursor: pointer;
      width: 100%;
      transition: background-color 0.3s ease;
    }
    .signup-btn:hover {
      background-color: #0c6;
    }
  </style>
</head>
<body>
  <div class="box">
    <h2>Connexion OSINT</h2>
    <form method="POST">
      <label>Email</label>
      <input type="email" name="email" required>
      
      <label>Mot de passe</label>
      <input type="password" name="mot_de_passe" required>
      
      <button type="submit">Se connecter</button>

      <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
    </form>

    <!-- Bouton "S'inscrire" -->
    <button class="signup-btn" onclick="window.location.href='inscription.php'">S'inscrire</button>
  </div>

  <!-- Popup cookie -->
  <div id="cookie-popup">
    <p>Nous utilisons des cookies pour améliorer votre expérience. En continuant, vous acceptez notre politique de cookies.</p>
    <button onclick="acceptCookies()">Accepter</button>
  </div>

  <!-- Popup Telegram -->
  <div class="popup" id="popup">
    <span class="close-btn" onclick="closePopup()">×</span>
    <p>Rejoignez notre canal Telegram pour plus de contenu exclusif !</p>
    <a href="https://t.me/+R4VVM2ECK8lkMjc8" target="_blank">Cliquez ici pour rejoindre</a>
  </div>

  <script>
    // Vérifie si l'utilisateur a déjà accepté les cookies
    if (!localStorage.getItem('cookiesAccepted')) {
      document.getElementById('cookie-popup').style.display = 'block';
    }

    // Fonction pour accepter les cookies
    function acceptCookies() {
      localStorage.setItem('cookiesAccepted', 'true');
      document.getElementById('cookie-popup').style.display = 'none';
    }

    // Fonction pour afficher la popup Telegram
    function showPopup() {
      document.getElementById('popup').classList.add('show');
    }

    // Fonction pour fermer la popup Telegram
    function closePopup() {
      document.getElementById('popup').classList.remove('show');
      localStorage.setItem('popupClosed', 'true');
    }

    // Vérifier si la popup Telegram a été fermée
    if (!localStorage.getItem('popupClosed')) {
      setTimeout(showPopup, 2000); // Affiche la popup après 2 secondes
    }
  </script>
</body>
</html>
