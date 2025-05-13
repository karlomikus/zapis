<h1 align="center">
  <img src="https://github.com/karlomikus/zapis/raw/master/public/favicon.png" width="64" alt="App Logo" />
  <br/>
  [⚡] Zapis - Note Taking App
</h1>

<p align="center">Easy to setup and use, single person note taking app. Just drop in folder containing your markdown file and start typing.</p>

## Features / Design decisions
- Single user, auth via GitHub
- Low on features, just basic note taking
- Open to suggestions, but the idea is to keep it simple
- Easy to use in browser markdown editor
- Drop in any folder containing text files and index them

## Installation

Pull the image and map a content folder. Use ENV variables to setup default auth credentials.

```yaml
services:
  notes:
    image: ghcr.io/karlomikus/zapis
    environment:
      - APP_ENV=prod
      - AUTH_EMAIL=user@email.com # User that will be authenticated
      - GITHUB_CLIENT_ID=
      - GITHUB_CLIENT_SECRET=
      - GITHUB_REDIRECT=/sso
    volumes:
      - ./my-notes:/app/content
    ports:
      - "8080:8080"
```