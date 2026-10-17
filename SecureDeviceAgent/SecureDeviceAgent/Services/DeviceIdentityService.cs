using System;
using System.IO;

namespace SecureDeviceAgent.Services
{
    public class DeviceIdentityService
    {
        private readonly string _identityDirectory;
        private readonly string _identityFile;

        public DeviceIdentityService()
        {
            _identityDirectory = Path.Combine(
                Environment.GetFolderPath(
                    Environment.SpecialFolder.LocalApplicationData
                ),
                "SecureDeviceAgent"
            );

            _identityFile = Path.Combine(
                _identityDirectory,
                "device-id.txt"
            );
        }

        public string GetOrCreateDeviceUuid()
        {
            Directory.CreateDirectory(_identityDirectory);

            if (File.Exists(_identityFile))
            {
                string existingId = File.ReadAllText(
                    _identityFile
                ).Trim();

                if (Guid.TryParse(existingId, out Guid parsedId))
                {
                    return parsedId.ToString();
                }
            }

            string deviceUuid = Guid.NewGuid().ToString();

            File.WriteAllText(
                _identityFile,
                deviceUuid
            );

            return deviceUuid;
        }
    }
}