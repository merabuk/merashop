## Warning

These certificates are self-signed. They must not be in the final docker image, and must not be used in environments other than local!

If you ever need to re-create them, use this example:

```bash
openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout cert.key -out cert.crt
```

---

For using SSL on Dev and other environments you have to mount the real SSL certificates to the addresses inside container:

1. /etc/nginx/ssl/cert.crt # certificate
2. /etc/nginx/ssl/cert.key # key
