package main

import (
	"bytes"
	"encoding/json"
	"fmt"
	"io"
	"net/http"
	"net/netip"
	"os"
	"strings"
	"time"
)

type requestBody struct {
	Message string `json:"message"`
}

func normalizePath(path string) string {
	if strings.HasPrefix(path, "/") {
		return path
	}

	return "/" + path
}

func main() {
	host := getEnv("SERVER_HOST", "127.0.0.1")
	port := getEnv("SERVER_PORT", "8080")
	path := normalizePath(getEnv("SERVER_PATH", "/"))
	message := getEnv("CLIENT_MESSAGE", "ping")

	addr, err := netip.ParseAddr(host)
	if err != nil || !addr.Is4() {
		fmt.Fprintf(os.Stderr, "SERVER_HOST must be a valid IPv4 address: %s\n", host)
		os.Exit(1)
	}

	payload, err := json.Marshal(requestBody{Message: message})
	if err != nil {
		fmt.Fprintln(os.Stderr, err.Error())
		os.Exit(1)
	}

	client := &http.Client{Timeout: 10 * time.Second}
	request, err := http.NewRequest(http.MethodPost, "http://"+host+":"+port+path, bytes.NewReader(payload))
	if err != nil {
		fmt.Fprintln(os.Stderr, err.Error())
		os.Exit(1)
	}

	request.Header.Set("Content-Type", "application/json")
	request.Header.Set("Client-Language", "go")

	response, err := client.Do(request)
	if err != nil {
		fmt.Fprintln(os.Stderr, err.Error())
		os.Exit(1)
	}
	defer response.Body.Close()

	body, err := io.ReadAll(response.Body)
	if err != nil {
		fmt.Fprintln(os.Stderr, err.Error())
		os.Exit(1)
	}

	fmt.Printf("Status: %d\n", response.StatusCode)
	if len(body) > 0 {
		fmt.Println(string(body))
	}

	if response.StatusCode >= http.StatusBadRequest {
		os.Exit(1)
	}
}

func getEnv(key string, fallback string) string {
	value := os.Getenv(key)
	if value == "" {
		return fallback
	}

	return value
}
