using System;
using System.Diagnostics;
using System.IO;

namespace SecureDeviceAgent.Services
{
    public class MetricsService
    {
        private readonly PerformanceCounter _cpuCounter;

        public MetricsService()
        {
            _cpuCounter = new PerformanceCounter(
                "Processor",
                "% Processor Time",
                "_Total"
            );

            // Initialize the CPU counter.
            _cpuCounter.NextValue();
        }

        public double GetCpuUsage()
        {
            double cpuUsage = _cpuCounter.NextValue();

            return Math.Round(cpuUsage, 2);
        }

        public long GetTotalRamMb()
        {
            using PerformanceCounter ramCounter =
                new PerformanceCounter(
                    "Memory",
                    "Available MBytes"
                );

            float availableRam =
                ramCounter.NextValue();

            return GetTotalPhysicalMemoryMb();
        }

        public long GetUsedRamMb()
        {
            using PerformanceCounter ramCounter =
                new PerformanceCounter(
                    "Memory",
                    "Available MBytes"
                );

            float availableRam =
                ramCounter.NextValue();

            long totalRam =
                GetTotalPhysicalMemoryMb();

            long usedRam =
                totalRam -
                (long)availableRam;

            return Math.Max(usedRam, 0);
        }

        public double GetRamUsagePercent()
        {
            long totalRam =
                GetTotalPhysicalMemoryMb();

            if (totalRam <= 0)
            {
                return 0;
            }

            long usedRam =
                GetUsedRamMb();

            double percentage =
                (double)usedRam /
                totalRam *
                100.0;

            return Math.Round(
                percentage,
                2
            );
        }

        private long GetTotalPhysicalMemoryMb()
        {
            using PerformanceCounter ramCounter =
                new PerformanceCounter(
                    "Memory",
                    "Available MBytes"
                );

            float availableRam =
                ramCounter.NextValue();

            // Read total physical memory using WMI.
            using System.Management.ManagementObjectSearcher searcher =
                new System.Management.ManagementObjectSearcher(
                    "SELECT TotalVisibleMemorySize FROM Win32_OperatingSystem"
                );

            foreach (
                System.Management.ManagementObject os
                in searcher.Get()
            )
            {
                if (
                    os["TotalVisibleMemorySize"] != null
                )
                {
                    long totalKb =
                        Convert.ToInt64(
                            os["TotalVisibleMemorySize"]
                        );

                    return totalKb / 1024;
                }
            }

            return 0;
        }

        public double GetDiskTotalGb()
        {
            DriveInfo drive =
                GetSystemDrive();

            return Math.Round(
                drive.TotalSize /
                1024.0 /
                1024.0 /
                1024.0,
                2
            );
        }

        public double GetDiskFreeGb()
        {
            DriveInfo drive =
                GetSystemDrive();

            return Math.Round(
                drive.AvailableFreeSpace /
                1024.0 /
                1024.0 /
                1024.0,
                2
            );
        }

        public double GetDiskUsedGb()
        {
            DriveInfo drive =
                GetSystemDrive();

            double totalGb =
                drive.TotalSize /
                1024.0 /
                1024.0 /
                1024.0;

            double freeGb =
                drive.AvailableFreeSpace /
                1024.0 /
                1024.0 /
                1024.0;

            return Math.Round(
                totalGb - freeGb,
                2
            );
        }

        public double GetDiskUsagePercent()
        {
            DriveInfo drive =
                GetSystemDrive();

            if (drive.TotalSize <= 0)
            {
                return 0;
            }

            double usage =
                100.0 -
                (
                    (double)drive.AvailableFreeSpace /
                    drive.TotalSize *
                    100.0
                );

            return Math.Round(
                usage,
                2
            );
        }

        public long GetUptimeSeconds()
        {
            return Environment.TickCount64 / 1000;
        }

        private DriveInfo GetSystemDrive()
        {
            string? systemPath =
                Path.GetPathRoot(
                    Environment.SystemDirectory
                );

            if (string.IsNullOrWhiteSpace(systemPath))
            {
                throw new InvalidOperationException(
                    "System drive could not be detected."
                );
            }

            return new DriveInfo(systemPath);
        }
    }
}