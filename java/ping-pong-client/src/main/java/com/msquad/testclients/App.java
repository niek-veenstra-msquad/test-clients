package com.msquad.testclients;

import java.io.IOException;
import java.net.Inet4Address;
import java.net.InetAddress;
import java.net.URI;
import java.net.http.HttpClient;
import java.net.http.HttpRequest;
import java.net.http.HttpResponse;
import java.time.Duration;

public final class App {
    private App() {
    }

    public static void main(String[] args) throws IOException, InterruptedException {
        String host = getEnvironmentVariable("SERVER_HOST", "127.0.0.1");
        String port = getEnvironmentVariable("SERVER_PORT", "8080");
        String path = normalizePath(getEnvironmentVariable("SERVER_PATH", "/"));
        String message = getEnvironmentVariable("CLIENT_MESSAGE", "ping");

        if (!isIpv4Address(host)) {
            System.err.printf("SERVER_HOST must be a valid IPv4 address: %s%n", host);
            System.exit(1);
        }

        HttpClient client = HttpClient.newBuilder()
            .connectTimeout(Duration.ofSeconds(10))
            .build();

        HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create("http://" + host + ":" + port + path))
            .timeout(Duration.ofSeconds(10))
            .header("Content-Type", "application/json")
            .header("Client-Language", "java")
            .POST(HttpRequest.BodyPublishers.ofString("{\"message\":\"" + escapeJson(message) + "\"}"))
            .build();

        HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());

        System.out.printf("Status: %d%n", response.statusCode());
        if (!response.body().isBlank()) {
            System.out.println(response.body());
        }

        if (response.statusCode() >= 400) {
            System.exit(1);
        }
    }

    private static String getEnvironmentVariable(String name, String fallback) {
        String value = System.getenv(name);
        return value == null || value.isBlank() ? fallback : value;
    }

    private static String normalizePath(String path) {
        return path.startsWith("/") ? path : "/" + path;
    }

    private static boolean isIpv4Address(String host) {
        try {
            InetAddress address = InetAddress.getByName(host);
            return address instanceof Inet4Address && host.equals(address.getHostAddress());
        } catch (IOException ex) {
            return false;
        }
    }

    private static String escapeJson(String value) {
        return value.replace("\\", "\\\\").replace("\"", "\\\"");
    }
}
