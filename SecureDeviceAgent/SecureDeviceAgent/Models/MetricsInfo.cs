using System.Text.Json.Serialization;

namespace SecureDeviceAgent.Models
{
    public class MetricsInfo
    {
        [JsonPropertyName("cpu_usage")]
        public double CpuUsage { get; set; }

        [JsonPropertyName("ram_usage_percent")]
        public double RamUsagePercent { get; set; }

        [JsonPropertyName("ram_used_mb")]
        public long RamUsedMb { get; set; }

        [JsonPropertyName("ram_total_mb")]
        public long RamTotalMb { get; set; }

        [JsonPropertyName("disk_usage_percent")]
        public double DiskUsagePercent { get; set; }

        [JsonPropertyName("disk_used_gb")]
        public double DiskUsedGb { get; set; }

        [JsonPropertyName("disk_free_gb")]
        public double DiskFreeGb { get; set; }

        [JsonPropertyName("disk_total_gb")]
        public double DiskTotalGb { get; set; }

        [JsonPropertyName("uptime_seconds")]
        public long UptimeSeconds { get; set; }
    }
}