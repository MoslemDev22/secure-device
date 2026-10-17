using System.Text.Json.Serialization;

namespace SecureDeviceAgent.Models
{
    public class DeviceInfo
    {
        [JsonPropertyName("device_uuid")]
        public string DeviceUuid { get; set; } = string.Empty;

        [JsonPropertyName("device_name")]
        public string DeviceName { get; set; } = string.Empty;

        [JsonPropertyName("hostname")]
        public string Hostname { get; set; } = string.Empty;

        [JsonPropertyName("os_name")]
        public string OsName { get; set; } = string.Empty;

        [JsonPropertyName("os_version")]
        public string OsVersion { get; set; } = string.Empty;

        [JsonPropertyName("architecture")]
        public string Architecture { get; set; } = string.Empty;

        [JsonPropertyName("cpu_name")]
        public string CpuName { get; set; } = string.Empty;

        [JsonPropertyName("cpu_cores")]
        public int CpuCores { get; set; }

        [JsonPropertyName("ram_total_mb")]
        public long RamTotalMb { get; set; }

        [JsonPropertyName("agent_name")]
        public string AgentName { get; set; } = "WindowsAgent";

        [JsonPropertyName("agent_version")]
        public string AgentVersion { get; set; } = "1.0.0";
    }
}