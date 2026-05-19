# test-clients

Simple ping/pong clients for posting JSON to the HTTP test server in
[niek-veenstra-msquad/test-server](https://github.com/niek-veenstra-msquad/test-server).

Each client sends an HTTP `POST` request to:

`http://<SERVER_HOST>:<SERVER_PORT><SERVER_PATH>`

with a JSON body shaped like:

```json
{
  "message": "ping"
}
```

## Supported clients

- NodeJS
- Java
- Python
- C# .NET
- Go
- PHP

## Configuration

All clients use the same environment variables:

- `SERVER_HOST` - IPv4 address of the server, default `127.0.0.1`
- `SERVER_PORT` - HTTP port, default `8080`
- `SERVER_PATH` - request path, default `/`
- `CLIENT_MESSAGE` - message to send, for example `ping` or `pong`, default `ping`

## Run locally

Examples:

```bash
cd /home/runner/work/test-clients/test-clients/nodejs && node index.js
cd /home/runner/work/test-clients/test-clients/python && python3 app.py
cd /home/runner/work/test-clients/test-clients/go && go run .
cd /home/runner/work/test-clients/test-clients/php && php client.php
cd /home/runner/work/test-clients/test-clients/dotnet/PingPongClient && dotnet run
cd /home/runner/work/test-clients/test-clients/java/ping-pong-client && mvn -q compile exec:java
```

## Run ARM containers

Build and run any client with Docker Compose:

```bash
cd /home/runner/work/test-clients/test-clients
SERVER_HOST=192.0.2.10 SERVER_PORT=8080 SERVER_PATH=/ CLIENT_MESSAGE=ping docker compose up --build nodejs
```

Swap `nodejs` for `java`, `python`, `dotnet`, `go`, or `php` as needed, and change
`CLIENT_MESSAGE` to `pong` when you want the pong variant.

The Compose file defaults to `linux/arm64` images. For local validation on a non-ARM machine,
override the platform explicitly:

```bash
DOCKER_PLATFORM=linux/amd64 docker compose build
```
