# Login-Pages-Osint
Dashboard PHP sécurisé pour site OSINT, avec gestion de sessions, journalisation avancée des connexions, et envoi d’alertes Telegram en temps réel. Idéal pour les projets de cybersécurité, de surveillance ou de renseignement open source (OSINT).

# SecureOSINT-Dashboard 🔐🕵️‍♂️

**Tableau de bord sécurisé avec journalisation OSINT + alertes Telegram**

---

## 📌 Description

Ce projet PHP fournit une **page de connexion sécurisée** ainsi qu’un **dashboard OSINT** (Open Source Intelligence) permettant de :

- 🔐 Gérer les connexions avec sessions sécurisées
- 📋 Journaliser les visites (IP, navigateur, hostname, etc.)
- 📡 Envoyer des alertes Telegram automatiques à chaque accès
- 🧠 Protéger contre les accès non autorisés
- 📂 Intégrer une base de données MySQL pour gérer les utilisateurs et les logs

---

## 🛠️ Fonctionnalités

- Connexion sécurisée avec gestion des sessions
- Interface dashboard stylisée en HTML/CSS
- Logs complets : IP, navigateur, user-agent, langue, géolocalisation
- Détection du type d’appareil (mobile / desktop)
- Envoi **en temps réel** de chaque visite à un bot **Telegram**
- Headers HTTP de sécurité : CSP, X-Frame, etc.

---

## 📦 Structure du projet

```bash
.
├── README.md              
├── config.php             # Config Bot
├── Index.php              # Page de connexion
├── Inscription.php        # Page d'inscription
.
