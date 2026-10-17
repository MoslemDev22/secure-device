
using System;
using System.Net.Http;
using System.Net.Http.Headers;
using System.Text;
using System.Text.Json;
using System.Threading.Tasks;
using SecureDeviceAgent.Models;

namespace SecureDeviceAgent.Services
{
    public class ApiService
    {
        private readonly HttpClient _httpClient;
        private readonly SystemInfoService _systemInfoService;

        private const string BaseUrl =
            "https://moslemdev.ir/api/";

        public ApiService()
        {
            _httpClient = new HttpClient
            {
                BaseAddress = new Uri(BaseUrl),
                Timeout = TimeSpan.FromSeconds(30)
            };

            _systemInfoService = new SystemInfoService();
        }

        public async Task<string> RegisterDeviceAsync(
            DeviceInfo deviceInfo)
        {
            string json =
                JsonSerializer.Serialize(deviceInfo);

            using StringContent content =
                new StringContent(
                    json,
                    Encoding.UTF8,
                    "application/json"
                );

            HttpResponseMessage response =
                await _httpClient.PostAsync(
                    "device/register.php",
                    content
                );

            string responseBody =
                await response.Content.ReadAsStringAsync();

            if (!response.IsSuccessStatusCode)
            {
                throw new HttpRequestException(
                    $"Device registration failed.\n\n" +
                    $"HTTP Status: {(int)response.StatusCode}\n" +
                    $"Response: {responseBody}"
                );
            }

            return responseBody;
        }

        public async Task<DeviceTokenResponse> GetDeviceTokenAsync(
            string deviceUuid)
        {
            var requestData = new
            {
                device_uuid = deviceUuid
            };

            string json =
                JsonSerializer.Serialize(requestData);

            using StringContent content =
                new StringContent(
                    json,
                    Encoding.UTF8,
                    "application/json"
                );

            HttpResponseMessage response =
                await _httpClient.PostAsync(
                    "device/token.php",
                    content
                );

            string responseBody =
                await response.Content.ReadAsStringAsync();

            DeviceTokenResponse? result =
                JsonSerializer.Deserialize<DeviceTokenResponse>(
                    responseBody,
                    new JsonSerializerOptions
                    {
                        PropertyNameCaseInsensitive = true
                    }
                );

            if (!response.IsSuccessStatusCode)
            {
                throw new HttpRequestException(
                    $"Device token request failed.\n\n" +
                    $"HTTP Status: {(int)response.StatusCode}\n" +
                    $"Response: {responseBody}"
                );
            }

            if (result == null)
            {
                throw new Exception(
                    "Invalid token response from server."
                );
            }

            return result;
        }

        public void SetDeviceToken(string token)
        {
            _httpClient.DefaultRequestHeaders.Authorization =
                new AuthenticationHeaderValue(
                    "Bearer",
                    token
                );
        }

        public async Task<string> SendHeartbeatAsync()
        {
            var requestData = new
            {
                ip_address =
                    _systemInfoService.GetLocalIpAddress()
            };

            string json =
                JsonSerializer.Serialize(requestData);

            using StringContent content =
                new StringContent(
                    json,
                    Encoding.UTF8,
                    "application/json"
                );

            HttpResponseMessage response =
                await _httpClient.PostAsync(
                    "device/heartbeat.php",
                    content
                );

            string responseBody =
                await response.Content.ReadAsStringAsync();

            if (!response.IsSuccessStatusCode)
            {
                throw new Exception(
                    $"Heartbeat failed.\n\n" +
                    $"HTTP Status: {(int)response.StatusCode}\n" +
                    $"Response: {responseBody}"
                );
            }

            return responseBody;
        }

        public async Task<string> SendMetricsAsync(
            MetricsInfo metrics)
        {
            string json =
                JsonSerializer.Serialize(metrics);

            using StringContent content =
                new StringContent(
                    json,
                    Encoding.UTF8,
                    "application/json"
                );

            HttpResponseMessage response =
                await _httpClient.PostAsync(
                    "device/metrics.php",
                    content
                );

            string responseBody =
                await response.Content.ReadAsStringAsync();

            if (!response.IsSuccessStatusCode)
            {
                throw new Exception(
                    $"Metrics submission failed.\n\n" +
                    $"HTTP Status: {(int)response.StatusCode}\n" +
                    $"Response: {responseBody}"
                );
            }

            return responseBody;
        }

        public async Task<string> SendLogAsync(
            string eventType,
            string message)
        {
            var requestData = new
            {
                event_type = eventType,
                message = message
            };

            string json =
                JsonSerializer.Serialize(requestData);

            using StringContent content =
                new StringContent(
                    json,
                    Encoding.UTF8,
                    "application/json"
                );

            HttpResponseMessage response =
                await _httpClient.PostAsync(
                    "device/log.php",
                    content
                );

            string responseBody =
                await response.Content.ReadAsStringAsync();

            if (!response.IsSuccessStatusCode)
            {
                throw new Exception(
                    $"Log submission failed.\n\n" +
                    $"HTTP Status: {(int)response.StatusCode}\n" +
                    $"Response: {responseBody}"
                );
            }

            return responseBody;
        }

        public async Task<string> SendOfflineAsync()
        {
            HttpResponseMessage response =
                await _httpClient.PostAsync(
                    "device/set_offline.php",
                    null
                );

            string responseBody =
                await response.Content.ReadAsStringAsync();

            if (!response.IsSuccessStatusCode)
            {
                throw new Exception(
                    $"Offline request failed.\n\n" +
                    $"HTTP Status: {(int)response.StatusCode}\n" +
                    $"Response: {responseBody}"
                );
            }

            return responseBody;
        }
    }
}
