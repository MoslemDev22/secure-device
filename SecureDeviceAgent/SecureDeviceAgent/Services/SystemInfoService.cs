
using System;
using System.IO;
using System.Linq;
using System.Management;
using System.Net;
using System.Net.NetworkInformation;
using System.Net.Sockets;
using System.Runtime.InteropServices;

namespace SecureDeviceAgent.Services
{
    public class SystemInfoService
    {
        public string GetComputerName()
        {
            return Environment.MachineName;
        }

        public string GetOperatingSystem()
        {
            return RuntimeInformation.OSDescription;
        }

        public string GetArchitecture()
        {
            return RuntimeInformation.OSArchitecture.ToString();
        }

        public string GetLocalIpAddress()
        {
            foreach (NetworkInterface networkInterface
                in NetworkInterface.GetAllNetworkInterfaces())
            {
                if (networkInterface.OperationalStatus
                    != OperationalStatus.Up)
                {
                    continue;
                }

                if (networkInterface.NetworkInterfaceType
                    == NetworkInterfaceType.Loopback)
                {
                    continue;
                }

                IPInterfaceProperties properties =
                    networkInterface.GetIPProperties();

                bool hasDefaultGateway =
                    properties.GatewayAddresses.Any(gateway =>
                        gateway.Address.AddressFamily
                            == AddressFamily.InterNetwork
                        && !gateway.Address.Equals(IPAddress.Any));

                if (!hasDefaultGateway)
                {
                    continue;
                }

                foreach (UnicastIPAddressInformation address
                    in properties.UnicastAddresses)
                {
                    if (address.Address.AddressFamily
                            != AddressFamily.InterNetwork)
                    {
                        continue;
                    }

                    if (IPAddress.IsLoopback(address.Address))
                    {
                        continue;
                    }

                    if (address.Address.Equals(IPAddress.Any))
                    {
                        continue;
                    }

                    return address.Address.ToString();
                }
            }

            return string.Empty;
        }

        public string GetCpuName()
        {
            using ManagementObjectSearcher searcher =
                new ManagementObjectSearcher(
                    "SELECT Name FROM Win32_Processor"
                );

            foreach (ManagementObject processor in searcher.Get())
            {
                return processor["Name"]?.ToString() ?? "Unknown CPU";
            }

            return "Unknown CPU";
        }

        public int GetCpuCores()
        {
            return Environment.ProcessorCount;
        }

        public long GetTotalRamMb()
        {
            using ManagementObjectSearcher searcher =
                new ManagementObjectSearcher(
                    "SELECT TotalVisibleMemorySize FROM Win32_OperatingSystem"
                );

            foreach (ManagementObject os in searcher.Get())
            {
                if (os["TotalVisibleMemorySize"] != null)
                {
                    long kb =
                        Convert.ToInt64(os["TotalVisibleMemorySize"]);

                    return kb / 1024;
                }
            }

            return 0;
        }

        public double GetTotalDiskGb()
        {
            DriveInfo drive =
                new DriveInfo(
                    Path.GetPathRoot(Environment.SystemDirectory)!
                );

            return Math.Round(
                drive.TotalSize / 1024.0 / 1024.0 / 1024.0,
                2
            );
        }

        public double GetFreeDiskGb()
        {
            DriveInfo drive =
                new DriveInfo(
                    Path.GetPathRoot(Environment.SystemDirectory)!
                );

            return Math.Round(
                drive.AvailableFreeSpace /
                1024.0 /
                1024.0 /
                1024.0,
                2
            );
        }

        public double GetUsedDiskGb()
        {
            DriveInfo drive =
                new DriveInfo(
                    Path.GetPathRoot(Environment.SystemDirectory)!
                );

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

            return Math.Round(totalGb - freeGb, 2);
        }

        public double GetDiskUsagePercent()
        {
            DriveInfo drive =
                new DriveInfo(
                    Path.GetPathRoot(Environment.SystemDirectory)!
                );

            if (drive.TotalSize == 0)
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

            return Math.Round(usage, 2);
        }
    }
}
