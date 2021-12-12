# SymfonyProject

Pour lancer le projet il faut:
  - Git clone le repo
  - Ajouter la variable d'environnement API_TOKEN=5001494949914493
  - Lancer un serveur Apache et MySQL avec XAMPP par exemple
  - Se placer dans le dossier du projet et lancer la commande "php bin/console doctrine:fixtures:load" qui va entrer des données de test en base de données puis "symfony serve" pour lancer le projet en local
  - Se rendre sur l'adresse http://127.0.0.1:8000/
  
Pour se connecter en tant qu'Admin et voir les fonctionnalités du projet se rendre sur http://127.0.0.1:8000/login et entrer les identifiants du professor X: 
  -  professorX@gmail.com
  - admin
  
Si vous voulez vous connecter en tant que Client ou Super Héro il faut modifier le mot de passe et l'email de l'utilisateur en question puis se déconnecter et entrer la nouvelle email et mot de passe de l'utilisateur.

Api:
L'api de l'application est effectuée avec Api Plateform. Pour puvoir voir tous les endpoints il suffit d'aller sur l'endpoint suivant: http://127.0.0.1:8000/api
On peut y voir tous les endpoints tels que l'endpoint /api/evils (méthode GET) qui permet de récupérer tous les méchants de l'API ou /api/users (méthode POST) qui permet d'ajouter un utilisateur.
Il y a donc des CRUD pour chaque entité (utilisateurs, méchants et tâches) mais chaque n'a pas les même permission ! Pour les méchants et utilisateurs tout le monde peut les utiliser par contre pour les tâches il afut forcément posséder un token JWT qui prouve qu'on est bien authentifié.
Pour recevoir le token JWT, il faut faire une requête POST sur l'endpoint http://127.0.0.1:8000/api/login avec les identifiants de l'utilisateur puis vous aurez le JWT token dans la réponse.
Puis sur l'endpoint http://127.0.0.1:8000/api/login il suffit de cliquer sur le bouton "Authorize" et entrer "Bearer ..." avec le token qui remplace les points de suspension et vous aurez enfin accès au CRUD des missions (Task).
