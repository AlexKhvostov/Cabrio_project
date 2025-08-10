# CabrioRide Frontend (Telegram WebApp)

## Env

Create `frontend/.env.local` from this template:

```
VITE_BACKEND_API_URL=http://localhost/app
```

Only variables prefixed with `VITE_` are available in the client code.

## Scripts

```
npm i
npm run dev
npm run build
```

## Notes

The app expects backend router at `${VITE_BACKEND_API_URL}/backend/routes/api.php`.
