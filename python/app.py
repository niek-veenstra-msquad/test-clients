import ipaddress
import json
import os
import sys
import urllib.error
import urllib.request


def normalize_path(path: str) -> str:
    return path if path.startswith("/") else f"/{path}"


def main() -> int:
    host = os.getenv("SERVER_HOST", "127.0.0.1")
    port = os.getenv("SERVER_PORT", "8080")
    path = normalize_path(os.getenv("SERVER_PATH", "/"))
    message = os.getenv("CLIENT_MESSAGE", "ping")

    try:
        ipaddress.IPv4Address(host)
    except ipaddress.AddressValueError as exc:
        print(f"SERVER_HOST must be a valid IPv4 address: {host}", file=sys.stderr)
        print(exc, file=sys.stderr)
        return 1

    request = urllib.request.Request(
        f"http://{host}:{port}{path}",
        data=json.dumps({"message": message}).encode("utf-8"),
        headers={"Content-Type": "application/json", "Client-Language": "python"},
        method="POST",
    )

    try:
        with urllib.request.urlopen(request, timeout=10) as response:
            body = response.read().decode("utf-8")
            print(f"Status: {response.status}")
            if body:
                print(body)
            return 0 if 200 <= response.status < 400 else 1
    except urllib.error.HTTPError as exc:
        body = exc.read().decode("utf-8")
        print(f"Status: {exc.code}")
        if body:
            print(body)
        return 1
    except urllib.error.URLError as exc:
        print(str(exc), file=sys.stderr)
        return 1


if __name__ == "__main__":
    raise SystemExit(main())
