# qivolt.com

Site da QIVOLT em **PHP + SQLite**, sem frameworks nem Composer, no mesmo modelo do `fbnsantosSI`:
front end público e um backoffice em `/admin` para editar a página inicial e as outras páginas,
ver as estatísticas de visitantes e atualizar o site a partir do GitHub.

Requisitos: PHP 8.1+ com `pdo_sqlite`, `mbstring`, `fileinfo`, `dom` (e `gd`, opcional, para reduzir imagens grandes).

## Estrutura

```
index.php        ← router (a pasta do projeto é a raiz web)
git.php          ← deploy automático (webhook do GitHub)
assets/          ← CSS/JS
uploads/         ← imagens enviadas no backoffice (fora do git)
app/             ← lógica e vistas (não acessível pela web)
app/seed.php     ← conteúdo inicial, copiado de qivolt.com
data/            ← base de dados SQLite e registos (fora do git)
config.php       ← configuração e segredos (copiar de config.sample.php; fora do git)
```

## Backoffice (`/admin`)

| Menu | O que faz |
|---|---|
| Painel | Visitas de hoje e dos últimos 30 dias, versão instalada, avisos |
| Página inicial | Título, linha pequena, lema e frase do topo + o texto, num editor visual |
| Páginas | Criar, editar e apagar as outras páginas (menu, endereço, descrição para o Google) |
| Estatísticas | Visualizações e visitantes por dia, páginas mais vistas, origens, países, dispositivos, robôs, erros 404 |
| Imagens | Biblioteca de imagens e PDF |
| Definições | Nome e descrição do site, rodapé, indexação no Google |
| Atualizar (git) | `git fetch` + `git reset --hard origin/main`: põe o servidor igual ao GitHub |

A página inicial é desenhada a partir do texto: duas ou mais secções seguidas com "Título 2" ficam lado a lado
como cartões; uma linha horizontal separa blocos; o último bloco fica centrado, com a citação em destaque.

As estatísticas são contadas pelo próprio site, sem cookies nem serviços externos e sem guardar IPs
(o visitante é um hash diário). Robôs e visitas com sessão iniciada no backoffice não contam.

## Arrancar localmente

```bash
cp config.sample.php config.php   # e mudar setup_key
```

```bash
php -S localhost:8080 index.php
```

Abrir http://localhost:8080/admin → a primeira vez pede a `setup_key` do `config.php` para criar o administrador.
A base de dados é criada sozinha em `data/site.sqlite`, já com o conteúdo de `app/seed.php`.

## Publicar (cPanel / Apache)

O código vem do GitHub; o servidor só guarda o que não está no git: `config.php`, `data/` e `uploads/`.

1. **Clonar** o repositório no servidor (cPanel → *Git Version Control*, ou `git clone` no terminal) e apontar
   a raiz do domínio para essa pasta. O botão "Atualizar" só funciona se a pasta for um clone git com o remoto `origin`.
2. **Configurar:** `cp config.sample.php config.php && chmod 600 config.php` e definir
   `setup_key` e `deploy_secret` com valores longos e aleatórios (`php -r 'echo bin2hex(random_bytes(24));'`).
3. **Criar o administrador** em `https://qivolt.com/admin/setup`. Depois de criado, o setup deixa de existir.
4. **Deploy automático (opcional):** no GitHub → *Settings* → *Webhooks* → Payload URL `https://qivolt.com/git.php`,
   Content type `application/json`, Secret = `deploy_secret`, evento *push*. Sem `deploy_secret`, o `git.php` devolve 404.

O `.htaccess` bloqueia `.git`, `app/`, `data/`, `config.php` e `README.md`. Não edite ficheiros do repositório
diretamente no servidor: a atualização repõe-nos.

Os endereços do site antigo (`index.php?page=pkmt-privacy`, `…?page=pkmt_manual`) são redirecionados para os novos
(`/pkmt-privacy`, `/pkmt_manual`).

## Segurança

- Palavras-passe com `password_hash`; sessões `HttpOnly`/`SameSite`; token CSRF em todos os formulários.
- Limite de 5 tentativas / 15 min por IP no login e no setup.
- HTML do editor filtrado por lista branca (sem scripts nem iframes que não sejam YouTube).
- Uploads validados pelo conteúdo real, com nomes aleatórios e sem execução de PHP em `uploads/`.
- `git.php` exige a assinatura HMAC do GitHub ou o token, comparados em tempo constante.
