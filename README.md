# Ekastola Poloko

Ekastola Poloko est une application web **responsive** conçue pour faciliter la gestion administrative dans un environnement scolaire (type ikastola).
Elle offre un ensemble de fonctionnalités essentielles pour les écoles, notamment la **gestion des présences**, la **facturation** et la **communication avec les familles**.

---

## ✨ Fonctionnalités principales

-   📄 Création et gestion de **factures**
-   📅 Gestion des **événements scolaires**
-   📝 Les parents peuvent **soumettre des demandes** via l’application
-   📌 Pointage des élèves à la **cantine** et à la **garderie**

---

## 🌍 Multilingue

L’application est disponible en **français** et en **basque**, offrant une expérience utilisateur adaptée aux besoins linguistiques de la région.

---

## 💻 Responsive Design

Compatible avec tous les types d’appareils (**ordinateur, tablette, smartphone**), Ekastola Poloko assure une accessibilité optimale pour le personnel administratif et les parents.

---

# 🚀 Déploiement & Environnements

L’application peut être exécutée dans différents environnements :

-   **Docker + Docker Compose** (recommandé)
-   **WAMP** pour un usage local

La configuration repose sur l’utilisation de **variables d’environnement** afin de séparer les paramètres sensibles du code source.

---

## 🧰 Prérequis

### Avec Docker

-   Docker
-   Docker Compose

### Avec WAMP

-   WAMP (PHP 8.4 recommandé)
-   Composer
-   Node.js & npm
-   MySQL

---

## ⚙️ Installation avec Docker (recommandée)

### 1️⃣ Récupération du projet

```bash
git clone <url-du-repo>
cd ekastola-poloko
```

### 2️⃣ Configuration de l’environnement

```bash
cp .env.example .env
```

Configurer notamment :

-   la base de données
-   le service mail de développement

### 3️⃣ Lancement de l’application

```bash
docker compose up -d --build
```

### 4️⃣ Initialisation Laravel

```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

### 5️⃣ Accès

-   Application : [http://localhost](http://localhost)
-   Interface Mailpit : [http://localhost:8025](http://localhost:8025)

---

## ⚙️ Installation avec WAMP

```bash
composer install
npm install
npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Accès : [http://127.0.0.1:8000](http://127.0.0.1:8000)

---

## 🔐 Variables d’environnement & sécurité

Les informations sensibles sont stockées dans le fichier `.env` (non versionné), notamment :

-   clés d’application
-   identifiants de base de données
-   configuration mail

👉 Le fichier `.env` ne doit jamais être publié.

---

## 🧪 Jeu de données d’essai

Un jeu de données est fourni via les **seeders Laravel** afin de tester rapidement les fonctionnalités principales :

```bash
php artisan db:seed
```

---

## ▶️ Mini-démo possible

-   Lancement de l’application avec Docker
-   Accès à l’interface web
-   Vérification du bon fonctionnement (facturation, présences, mails)

---

## 📄 Licence

Projet réalisé dans un cadre pédagogique.
