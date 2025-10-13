# Documentation API Swagger (NelmioApiDocBundle)

## Accès à la documentation

### Interface Swagger UI
L'interface interactive Swagger est accessible à l'adresse :
```
http://localhost/api/doc
```

### JSON OpenAPI
Le schéma OpenAPI au format JSON est disponible à :
```
http://localhost/api/doc.json
```

## Configuration

- **Bundle** : `nelmio/api-doc-bundle` v4.38+
- **Schéma** : OpenAPI 3.0.0
- **Sécurité** : JWT Bearer Token (Lexik JWT Authentication)

## Utilisation

### 1. Authentification

Pour tester les endpoints protégés, vous devez d'abord obtenir un token JWT :

**POST** `/api/login_check`
```json
{
  "username": "user@example.com",
  "password": "password123"
}
```

Réponse :
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."
}
```

### 2. Autoriser dans Swagger UI

1. Cliquez sur le bouton **"Authorize"** 🔓 en haut à droite
2. Entrez : `Bearer <votre_token>`
3. Cliquez sur **"Authorize"** puis **"Close"**

### 3. Tester les endpoints

Tous les endpoints API sont documentés avec :
- **Tags** : Video Games, Categories, Editors, Authentication
- **Descriptions** détaillées
- **Paramètres** (query, path, body)
- **Réponses** possibles (200, 201, 400, 401, 403, 404)
- **Exemples** de requêtes/réponses

## Endpoints disponibles

### Authentication
- `POST /api/login_check` - Obtenir un token JWT

### Video Games
- `GET /api/v1/video-game/list` - Liste paginée
- `GET /api/v1/video-game/{id}` - Détails
- `POST /api/v1/video-game/add` - Créer (ADMIN)
- `PUT /api/v1/video-game/{id}` - Modifier (ADMIN)
- `DELETE /api/v1/video-game/{id}` - Supprimer (ADMIN)

### Categories
- `GET /api/v1/category/list` - Liste paginée
- `GET /api/v1/category/{id}` - Détails
- `POST /api/v1/category/add` - Créer (ADMIN)
- `PUT /api/v1/category/{id}` - Modifier (ADMIN)
- `DELETE /api/v1/category/{id}` - Supprimer (ADMIN)

### Editors
- `GET /api/v1/editor/list` - Liste paginée
- `GET /api/v1/editor/{id}` - Détails
- `POST /api/v1/editor/add` - Créer (ADMIN)
- `PUT /api/v1/editor/{id}` - Modifier (ADMIN)
- `DELETE /api/v1/editor/{id}` - Supprimer (ADMIN)

## Configuration personnalisée

Le fichier de configuration se trouve dans :
```
config/packages/nelmio_api_doc.yaml
```

Vous pouvez personnaliser :
- Les informations OpenAPI (titre, description, version)
- Les serveurs (dev, staging, production)
- Les schémas de sécurité
- Les filtres de routes (path_patterns)

## Commandes utiles

```bash
# Vider le cache
php bin/console cache:clear

# Lister les routes
php bin/console debug:router

# Lister les routes API
php bin/console debug:router | grep "/api"
```
