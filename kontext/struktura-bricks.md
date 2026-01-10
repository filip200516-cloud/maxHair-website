# Struktura Bricks Builder

## Základní struktura elementů

Každá sekce má tuto hierarchii:

```json
{
  "id": "unique-id",
  "name": "section",
  "parent": 0,
  "children": ["container-id"],
  "settings": {
    "_width": "100vw",
    "_height": "100vh"
  }
}
```

### Kontejner

```json
{
  "id": "container-id",
  "name": "container",
  "parent": "section-id",
  "children": ["code-id"],
  "settings": {
    "_width": "100vw",
    "_height": "100vh"
  }
}
```

### Code blok

```json
{
  "id": "code-id",
  "name": "code",
  "parent": "container-id",
  "children": [],
  "settings": {
    "executeCode": true,
    "signature": "hash",
    "user_id": 1,
    "time": timestamp,
    "code": "<!-- HTML -->\n<style>/* CSS */</style>\n<script>/* JS */</script>"
  },
  "themeStyles": []
}
```

## Příklad kompletní sekce

```json
{
  "id": "section-1",
  "name": "section",
  "parent": 0,
  "children": ["container-1"],
  "settings": {
    "_width": "100vw",
    "_height": "100vh"
  }
},
{
  "id": "container-1",
  "name": "container",
  "parent": "section-1",
  "children": ["code-1"],
  "settings": {
    "_width": "100vw",
    "_height": "100vh"
  }
},
{
  "id": "code-1",
  "name": "code",
  "parent": "container-1",
  "children": [],
  "settings": {
    "executeCode": true,
    "code": "<!-- HTML kód -->"
  }
}
```

## Důležité poznámky

- Každý element musí mít unikátní `id`
- `parent` odkazuje na ID rodičovského elementu
- `children` obsahuje pole ID potomků
- Pro sekce je `parent: 0`
- Code bloky mají `children: []` (jsou listy)
- `executeCode: true` je nutné pro spuštění JavaScriptu

