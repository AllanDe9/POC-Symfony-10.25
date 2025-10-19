# 📘 Exemples de requêtes API - Video Games API

## 🔐 Authentification

### Obtenir un token JWT

```bash
curl -X POST http://localhost:8000/api/login_check \
  -H "Content-Type: application/json" \
  -d '{
    "username": "admin@example.com",
    "password": "password123"
  }'
```

**Réponse** :
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."
}
```

## 📚 Catégories

### Lister toutes les catégories (avec pagination)

```bash
curl -X GET "http://localhost:8000/api/v1/category/list?page=1&limit=3"
```

### Récupérer une catégorie par ID

```bash
curl -X GET http://localhost:8000/api/v1/category/1
```

### Créer une nouvelle catégorie (🔒 ADMIN)

```bash
curl -X POST http://localhost:8000/api/v1/category/add \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer VOTRE_TOKEN_ICI" \
  -d '{
    "name": "RPG"
  }'
```

### Modifier une catégorie (🔒 ADMIN)

```bash
curl -X PUT http://localhost:8000/api/v1/category/1 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer VOTRE_TOKEN_ICI" \
  -d '{
    "name": "Action-RPG"
  }'
```

### Supprimer une catégorie (🔒 ADMIN)

```bash
curl -X DELETE http://localhost:8000/api/v1/category/1 \
  -H "Authorization: Bearer VOTRE_TOKEN_ICI"
```

## 🎮 Jeux Vidéo

### Lister tous les jeux vidéo (avec pagination)

```bash
curl -X GET "http://localhost:8000/api/v1/video-game/list?page=1&limit=3"
```

### Récupérer un jeu vidéo par ID

```bash
curl -X GET http://localhost:8000/api/v1/video-game/1
```

### Créer un nouveau jeu vidéo (🔒 ADMIN)

```bash
curl -X POST http://localhost:8000/api/v1/video-game/add \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer VOTRE_TOKEN_ICI" \
  -d '{
    "title": "The Legend of Zelda: Breath of the Wild",
    "releaseDate": "2017-03-03",
    "description": "Un jeu d'\''aventure en monde ouvert épique"
  }'
```

### Modifier un jeu vidéo (🔒 ADMIN)

```bash
curl -X PUT http://localhost:8000/api/v1/video-game/1 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer VOTRE_TOKEN_ICI" \
  -d '{
    "title": "The Legend of Zelda: Tears of the Kingdom",
    "releaseDate": "2023-05-12",
    "description": "La suite tant attendue de Breath of the Wild"
  }'
```

### Supprimer un jeu vidéo (🔒 ADMIN)

```bash
curl -X DELETE http://localhost:8000/api/v1/video-game/1 \
  -H "Authorization: Bearer VOTRE_TOKEN_ICI"
```

## 🏢 Éditeurs

### Lister tous les éditeurs (avec pagination)

```bash
curl -X GET "http://localhost:8000/api/v1/editor/list?page=1&limit=3"
```

### Récupérer un éditeur par ID

```bash
curl -X GET http://localhost:8000/api/v1/editor/1
```

### Créer un nouvel éditeur (🔒 ADMIN)

```bash
curl -X POST http://localhost:8000/api/v1/editor/add \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer VOTRE_TOKEN_ICI" \
  -d '{
    "name": "Nintendo",
    "country": "Japon"
  }'
```

### Modifier un éditeur (🔒 ADMIN)

```bash
curl -X PUT http://localhost:8000/api/v1/editor/1 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer VOTRE_TOKEN_ICI" \
  -d '{
    "name": "Nintendo Co., Ltd.",
    "country": "Japon"
  }'
```

### Supprimer un éditeur (🔒 ADMIN)

```bash
curl -X DELETE http://localhost:8000/api/v1/editor/1 \
  -H "Authorization: Bearer VOTRE_TOKEN_ICI"
```

## 👥 Utilisateurs

### Lister tous les utilisateurs (🔒 ADMIN - avec pagination)

```bash
curl -X GET "http://localhost:8000/api/v1/user/list?page=1&limit=3" \
  -H "Authorization: Bearer VOTRE_TOKEN_ICI"
```

### Récupérer un utilisateur par ID (🔒 ADMIN)

```bash
curl -X GET http://localhost:8000/api/v1/user/1 \
  -H "Authorization: Bearer VOTRE_TOKEN_ICI"
```

### Créer un nouvel utilisateur (🔒 ADMIN)

```bash
curl -X POST http://localhost:8000/api/v1/user/add \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer VOTRE_TOKEN_ICI" \
  -d '{
    "email": "newuser@example.com",
    "password": "password123",
    "roles": ["ROLE_USER"],
    "subscriptionToNewsletter": false
  }'
```

### Modifier un utilisateur (🔒 ADMIN)

```bash
curl -X PUT http://localhost:8000/api/v1/user/1 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer VOTRE_TOKEN_ICI" \
  -d '{
    "email": "updated@example.com",
    "password": "newpassword123",
    "roles": ["ROLE_ADMIN"],
    "subscriptionToNewsletter": true
  }'
```

### Supprimer un utilisateur (🔒 ADMIN)

```bash
curl -X DELETE http://localhost:8000/api/v1/user/1 \
  -H "Authorization: Bearer VOTRE_TOKEN_ICI"
```

## 🧪 Script de test complet

Créez un fichier `test_api.sh` :

```bash
#!/bin/bash

# Couleurs pour l'affichage
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m' # No Color

BASE_URL="http://localhost:8000"

echo -e "${BLUE}=== Test de l'API Video Games ===${NC}\n"

# 1. Authentification
echo -e "${GREEN}1. Authentification...${NC}"
TOKEN_RESPONSE=$(curl -s -X POST "$BASE_URL/api/login_check" \
  -H "Content-Type: application/json" \
  -d '{"username":"admin@example.com","password":"password123"}')

TOKEN=$(echo $TOKEN_RESPONSE | grep -o '"token":"[^"]*' | cut -d'"' -f4)

if [ -z "$TOKEN" ]; then
  echo -e "${RED}Erreur: Impossible d'obtenir le token${NC}"
  exit 1
fi

echo -e "Token obtenu: ${TOKEN:0:50}...\n"

# 2. Liste des catégories
echo -e "${GREEN}2. Liste des catégories...${NC}"
curl -s -X GET "$BASE_URL/api/v1/category/list?page=1&limit=3" | jq .
echo ""

# 3. Créer une catégorie
echo -e "${GREEN}3. Création d'une catégorie...${NC}"
CATEGORY_RESPONSE=$(curl -s -X POST "$BASE_URL/api/v1/category/add" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"name":"RPG Test"}')
echo $CATEGORY_RESPONSE | jq .
echo ""

# 4. Liste des jeux vidéo
echo -e "${GREEN}4. Liste des jeux vidéo...${NC}"
curl -s -X GET "$BASE_URL/api/v1/video-game/list?page=1&limit=3" | jq .
echo ""

# 5. Liste des éditeurs
echo -e "${GREEN}5. Liste des éditeurs...${NC}"
curl -s -X GET "$BASE_URL/api/v1/editor/list?page=1&limit=3" | jq .
echo ""

echo -e "${BLUE}=== Tests terminés ===${NC}"
```

Rendre le script exécutable :
```bash
chmod +x test_api.sh
./test_api.sh
```

## 📊 Codes de réponse HTTP

| Code | Signification | Cas d'utilisation |
|------|---------------|-------------------|
| 200  | OK | GET, PUT, DELETE réussis |
| 201  | Created | POST réussi (création) |
| 400  | Bad Request | Données invalides |
| 401  | Unauthorized | Token JWT manquant/invalide |
| 403  | Forbidden | Permissions insuffisantes (pas ADMIN) |
| 404  | Not Found | Ressource inexistante |

## 🔍 Tester avec HTTPie (alternative à curl)

Installation :
```bash
brew install httpie  # macOS
```

Exemples :
```bash
# Login
http POST localhost:8000/api/login_check username=admin@example.com password=password123

# GET avec token
http GET localhost:8000/api/v1/category/list "Authorization: Bearer TOKEN"

# POST avec token
http POST localhost:8000/api/v1/category/add "Authorization: Bearer TOKEN" name="RPG"
```

## 🌐 Tester avec Postman

1. Importez la spécification OpenAPI depuis : `http://localhost:8000/api/doc.json`
2. Postman créera automatiquement toutes les requêtes
3. Configurez l'authentification Bearer Token dans l'onglet "Authorization"

## 💡 Conseils

1. **Sauvegardez votre token** : Il reste valide selon la configuration JWT (généralement 1h)
2. **Utilisez les fixtures** : Chargez des données de test avec `php bin/console doctrine:fixtures:load`
3. **Testez la pagination** : Variez les paramètres `page` et `limit`
4. **Vérifiez les validations** : Essayez d'envoyer des données invalides pour voir les messages d'erreur

---

**Note** : Remplacez `VOTRE_TOKEN_ICI` par le token JWT obtenu via `/api/login_check`
