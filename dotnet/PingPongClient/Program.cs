using System.Net;
using System.Net.Sockets;
using System.Text;
using System.Text.Json;

var host = GetEnvironmentVariable("SERVER_HOST", "127.0.0.1");
var port = GetEnvironmentVariable("SERVER_PORT", "8080");
var path = NormalizePath(GetEnvironmentVariable("SERVER_PATH", "/"));
var message = GetEnvironmentVariable("CLIENT_MESSAGE", "ping");

if (!IPAddress.TryParse(host, out var ipAddress) || ipAddress.AddressFamily != AddressFamily.InterNetwork)
{
    Console.Error.WriteLine($"SERVER_HOST must be a valid IPv4 address: {host}");
    return 1;
}

using var httpClient = new HttpClient
{
    Timeout = TimeSpan.FromSeconds(10),
};

var payload = JsonSerializer.Serialize(new { message });
using var content = new StringContent(payload, Encoding.UTF8, "application/json");
using var response = await httpClient.PostAsync($"http://{host}:{port}{path}", content);
var body = await response.Content.ReadAsStringAsync();

Console.WriteLine($"Status: {(int)response.StatusCode}");

if (!string.IsNullOrWhiteSpace(body))
{
    Console.WriteLine(body);
}

return response.IsSuccessStatusCode ? 0 : 1;

static string GetEnvironmentVariable(string name, string fallback)
{
    var value = Environment.GetEnvironmentVariable(name);
    return string.IsNullOrWhiteSpace(value) ? fallback : value;
}

static string NormalizePath(string path)
{
    return path.StartsWith('/') ? path : $"/{path}";
}
