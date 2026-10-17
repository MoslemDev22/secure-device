using System.Text.Json.Serialization;

namespace SecureDeviceAgent.Models
{
    public class DeviceTokenResponse
    {
        [JsonPropertyName("success")]
        public bool Success { get; set; }

        [JsonPropertyName("message")]
        public string Message { get; set; } = string.Empty;

        [JsonPropertyName("device_token")]
        public string DeviceToken { get; set; } = string.Empty;

        [JsonPropertyName("expires_at")]
        public string ExpiresAt { get; set; } = string.Empty;
    }
}