function normalizePath(path) {
  return path.startsWith("/") ? path : `/${path}`;
}

function isValidIpv4(host) {
  const octets = host.split(".");

  return octets.length === 4 && octets.every((octet) => {
    if (!/^\d+$/.test(octet)) {
      return false;
    }

    const value = Number(octet);
    return value >= 0 && value <= 255;
  });
}

async function main() {
  const host = process.env.SERVER_HOST ?? "127.0.0.1";
  const port = process.env.SERVER_PORT ?? "8080";
  const path = normalizePath(process.env.SERVER_PATH ?? "/");
  const message = process.env.CLIENT_MESSAGE ?? "ping";

  if (!isValidIpv4(host)) {
    throw new Error(`SERVER_HOST must be a valid IPv4 address: ${host}`);
  }

  const url = `http://${host}:${port}${path}`;
  const response = await fetch(url, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "Client-Language": "nodejs",
    },
    body: JSON.stringify({ message }),
    signal: AbortSignal.timeout(10_000),
  });

  const body = await response.text();
  console.log(`Status: ${response.status}`);

  if (body) {
    console.log(body);
  }

  if (!response.ok) {
    process.exitCode = 1;
  }
}

main().catch((error) => {
  console.error(error.message);
  process.exit(1);
});
