API sygal
=========

Ping
----

```bash
curl --insecure \
-H "Accept: application/json" \
-H "Content-Type: application/json" \
-H "Authorization: Basic c3lnYWwtYXBwOmF6ZXJ0eQ==" \
-X POST \
https://localhost:8003/ping
```

Exemple de résultat :

```json
{"id":"639f02bca6817","date":"2022-12-18T13:08:28+01:00"}
```

Inscription
-----------

```bash
curl --insecure \
-H "Accept: application/json" \
-H "Content-Type: application/json" \
-H "Authorization: Basic c3lnYWwtYXBwOmF6ZXJ0eQ==" \
-X POST \
-d '{"from_instance": "P4", "data": "{\"msg\": \"coucou\"}"}' \
https://localhost:8003/inscription
```

