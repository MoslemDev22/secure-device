using System;
using System.Net;
using System.Net.Http;
using System.Threading.Tasks;
using SecureDeviceAgent.Models;

namespace SecureDeviceAgent.Services
{
    public class AgentService
    {
        private readonly SystemInfoService _systemInfoService;
        private readonly MetricsService _metricsService;
        private readonly ApiService _apiService;
        private readonly DeviceIdentityService _deviceIdentityService;

        public AgentService()
        {
            _systemInfoService = new SystemInfoService();
            _metricsService = new MetricsService();
            _apiService = new ApiService();
            _deviceIdentityService = new DeviceIdentityService();
        }

        public DeviceInfo CollectDeviceInfo()
        {
            return new DeviceInfo
            {
                DeviceUuid = GetDeviceUuid(),
                DeviceName = _systemInfoService.GetComputerName(),
                Hostname = _systemInfoService.GetComputerName(),
                OsName = "Windows",
                OsVersion = GetWindowsVersion(),
                Architecture = _systemInfoService.GetArchitecture(),
                CpuName = _systemInfoService.GetCpuName(),
                CpuCores = _systemInfoService.GetCpuCores(),
                RamTotalMb = _systemInfoService.GetTotalRamMb(),
                AgentName = "WindowsAgent",
                AgentVersion = "1.0.0"
            };
        }

        public MetricsInfo CollectMetrics()
        {
            return new MetricsInfo
            {
                CpuUsage = _metricsService.GetCpuUsage(),
                RamUsagePercent = _metricsService.GetRamUsagePercent(),
                RamUsedMb = _metricsService.GetUsedRamMb(),
                RamTotalMb = _metricsService.GetTotalRamMb(),
                DiskUsagePercent = _metricsService.GetDiskUsagePercent(),
                DiskUsedGb = _metricsService.GetDiskUsedGb(),
                DiskFreeGb = _metricsService.GetDiskFreeGb(),
                DiskTotalGb = _metricsService.GetDiskTotalGb(),
                UptimeSeconds = _metricsService.GetUptimeSeconds()
            };
        }

        public async Task<DeviceTokenResponse> InitializeAsync()
        {
            DeviceInfo deviceInfo = CollectDeviceInfo();

            DeviceTokenResponse tokenResponse;

            try
            {
                // First, try to get a token for the existing device.
                tokenResponse = await _apiService.GetDeviceTokenAsync(
                    deviceInfo.DeviceUuid
                );
            }
            catch (HttpRequestException ex)
                when (ex.StatusCode == HttpStatusCode.NotFound)
            {
                // Register only when the server confirms that
                // the device does not exist.
                await _apiService.RegisterDeviceAsync(deviceInfo);

                // Retry token creation after successful registration.
                tokenResponse = await _apiService.GetDeviceTokenAsync(
                    deviceInfo.DeviceUuid
                );
            }

            if (!tokenResponse.Success ||
                string.IsNullOrWhiteSpace(tokenResponse.DeviceToken))
            {
                throw new Exception(
                    "Server did not return a valid device token."
                );
            }

            _apiService.SetDeviceToken(tokenResponse.DeviceToken);

            return tokenResponse;
        }

        public async Task<string> SendHeartbeatAsync()
        {
            return await _apiService.SendHeartbeatAsync();
        }

        public async Task<string> SendMetricsAsync()
        {
            MetricsInfo metrics = CollectMetrics();

            return await _apiService.SendMetricsAsync(metrics);
        }

        public async Task<string> SendLogAsync(
            string eventType,
            string message)
        {
            return await _apiService.SendLogAsync(eventType, message);
        }

        public async Task<string> SendOfflineAsync()
        {
            return await _apiService.SendOfflineAsync();
        }

        private string GetDeviceUuid()
        {
            return _deviceIdentityService.GetOrCreateDeviceUuid();
        }

        private string GetWindowsVersion()
        {
            return Environment.OSVersion.Version.ToString();
        }
    }
}