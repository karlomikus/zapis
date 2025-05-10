# [⚡] Zapis - Note Taking App

Easy to setup and use, single person note taking app. Just drop in folder containing your markdown file and start typing.

## Features / Design decisions
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
      - USERNAME=admin
      - PASSWORD=12345
    volumes:
      - ./my-notes:/var/www/notesapp/content
    ports:
      - "80:80"
```