# Coaching Planning

## Description

Une api qui sera utilisée dans une application mobile de gestion de planing pour une salle de sport

## API Endpoints

### `/api/login_check`

Route pour se log avec l'api et recuperer le refresh cookie et le cookie d'authentification

Par défaut vous pouvez vous connecter avec :
{
    "username": "admin",
    "password": "password"
}

### `/api/doc`

Route pour acceder à la documentation de l'api avec Swagger UI

### `/api/doc.json`

Route pour acceder à la documentation de l'api avec Swagger json

## Setup and Installation

Initialiser la base de donnée avec :
php bin/console doctrine:database:create

Initialiser les tables avec :
php bin/console doctrine:schema:update --force


Lancer l'appFixture pour peupler la base : 
php bin/console doctrine:fixtures:load
