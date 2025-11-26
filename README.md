# MeraShop

## Table of Contents

- [Quick Start](#quick-start)

## Quick Start

1. **Add local domains to `/etc/hosts`:**

   ```bash
   127.0.0.1 merashop.test
   127.0.0.1 api.merashop.test
   127.0.0.1 admin-api.merashop.test
   127.0.0.1 sources.merashop.test
   ```

2. **Start the application:**

   ```bash
   docker compose build
   docker compose up -d
   ```

3. **Access the application:**

- Web: [merashop.test](http://merashop.test)
- Public API: [api.merashop.test](http://api.merashop.test)
- Admin API: [admin-api.merashop.test](http://admin-api.merashop.test)
- Sources: [sources.merashop.test](http://sources.merashop.test)
