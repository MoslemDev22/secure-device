
using System;
using System.Threading.Tasks;
using System.Windows;
using System.Windows.Media;
using System.Windows.Threading;
using SecureDeviceAgent.Services;

namespace SecureDeviceAgent
{
    public partial class MainWindow : Window
    {
        private readonly SystemInfoService _systemInfoService;
        private readonly MetricsService _metricsService;
        private readonly AgentService _agentService;

        private readonly DispatcherTimer _metricsTimer;
        private readonly DispatcherTimer _heartbeatTimer;
        private readonly DispatcherTimer _serverMetricsTimer;
        private readonly DispatcherTimer _reconnectTimer;

        private bool _isClosing;
        private bool _agentInitialized;
        private bool _isInitializing;
        private bool _isSendingHeartbeat;
        private bool _isSendingMetrics;
        private bool _isReconnecting;

        public MainWindow()
        {
            InitializeComponent();

            _systemInfoService = new SystemInfoService();
            _metricsService = new MetricsService();
            _agentService = new AgentService();

            SetConnectionStatus("Initializing", false);
            LoadSystemInformation();

            _metricsTimer = new DispatcherTimer
            {
                Interval = TimeSpan.FromSeconds(2)
            };
            _metricsTimer.Tick += MetricsTimer_Tick;
            _metricsTimer.Start();

            UpdateMetrics();

            _heartbeatTimer = new DispatcherTimer
            {
                Interval = TimeSpan.FromSeconds(30)
            };
            _heartbeatTimer.Tick += HeartbeatTimer_Tick;

            _serverMetricsTimer = new DispatcherTimer
            {
                Interval = TimeSpan.FromSeconds(30)
            };
            _serverMetricsTimer.Tick += ServerMetricsTimer_Tick;

            _reconnectTimer = new DispatcherTimer
            {
                Interval = TimeSpan.FromSeconds(15)
            };
            _reconnectTimer.Tick += ReconnectTimer_Tick;

            Loaded += MainWindow_Loaded;
        }

        private async void MainWindow_Loaded(
            object sender,
            RoutedEventArgs e)
        {
            StartBackgroundTimers();
            await InitializeAgentAsync();
        }

        private void StartBackgroundTimers()
        {
            if (!_heartbeatTimer.IsEnabled)
                _heartbeatTimer.Start();

            if (!_serverMetricsTimer.IsEnabled)
                _serverMetricsTimer.Start();

            if (!_reconnectTimer.IsEnabled)
                _reconnectTimer.Start();
        }

        private async Task InitializeAgentAsync()
        {
            if (_isClosing || _isInitializing || _agentInitialized)
                return;

            _isInitializing = true;

            try
            {
                await _agentService.InitializeAsync();

                if (_isClosing)
                    return;

                _agentInitialized = true;

                Console.WriteLine("Agent initialization succeeded.");

                await SendLogAsync(
                    "AGENT_INITIALIZED",
                    "Windows Agent initialized successfully."
                );

                // The initial heartbeat determines server connectivity.
                await SendHeartbeatAsync();

                if (_isClosing)
                    return;

                // Metrics are sent only after successful initialization.
                await SendMetricsToServerAsync();
            }
            catch (Exception ex)
            {
                _agentInitialized = false;

                Console.WriteLine(
                    $"Agent initialization failed: {ex}"
                );

                SetConnectionStatus(
                    "Connection Lost",
                    false
                );

                MessageBox.Show(
                    $"Agent initialization failed.\n\n{ex.Message}",
                    "Secure Device Agent",
                    MessageBoxButton.OK,
                    MessageBoxImage.Warning
                );
            }
            finally
            {
                _isInitializing = false;
            }
        }

        private async void ReconnectTimer_Tick(
            object? sender,
            EventArgs e)
        {
            if (_isClosing || _isReconnecting)
                return;

            _isReconnecting = true;

            try
            {
                if (!_agentInitialized)
                {
                    await InitializeAgentAsync();
                    return;
                }

                // A successful heartbeat restores connectivity.
                await SendHeartbeatAsync();
            }
            finally
            {
                _isReconnecting = false;
            }
        }

        private async void HeartbeatTimer_Tick(
            object? sender,
            EventArgs e)
        {
            if (_isClosing || !_agentInitialized || _isSendingHeartbeat)
                return;

            await SendHeartbeatAsync();
        }

        private async Task<bool> SendHeartbeatAsync()
        {
            if (_isClosing || !_agentInitialized || _isSendingHeartbeat)
                return false;

            _isSendingHeartbeat = true;

            try
            {
                string response =
                    await _agentService.SendHeartbeatAsync();

                Console.WriteLine($"Heartbeat response: {response}");

                SetConnectionStatus("Connected", true);

                await SendLogAsync(
                    "HEARTBEAT_SENT",
                    "Heartbeat sent successfully."
                );

                return true;
            }
            catch (Exception ex)
            {
                Console.WriteLine($"Heartbeat failed: {ex.Message}");

                SetConnectionStatus("Connection Lost", false);

                // Logging is best-effort and may also fail offline.
                await SendLogAsync(
                    "HEARTBEAT_ERROR",
                    $"Heartbeat failed: {ex.Message}"
                );

                return false;
            }
            finally
            {
                _isSendingHeartbeat = false;
            }
        }

        private async void ServerMetricsTimer_Tick(
            object? sender,
            EventArgs e)
        {
            if (_isClosing || !_agentInitialized || _isSendingMetrics)
                return;

            await SendMetricsToServerAsync();
        }

        private async Task SendMetricsToServerAsync()
        {
            if (_isClosing || !_agentInitialized || _isSendingMetrics)
                return;

            _isSendingMetrics = true;

            try
            {
                string response =
                    await _agentService.SendMetricsAsync();

                Console.WriteLine($"Metrics response: {response}");

                await SendLogAsync(
                    "METRICS_SENT",
                    "Device metrics sent successfully."
                );

                // Metrics success alone does not prove that
                // the heartbeat endpoint is reachable.
            }
            catch (Exception ex)
            {
                Console.WriteLine(
                    $"Metrics submission failed: {ex.Message}"
                );

                SetConnectionStatus("Connection Lost", false);

                await SendLogAsync(
                    "METRICS_ERROR",
                    $"Metrics submission failed: {ex.Message}"
                );
            }
            finally
            {
                _isSendingMetrics = false;
            }
        }

        private async Task SendLogAsync(
            string eventType,
            string message)
        {
            if (_isClosing || !_agentInitialized)
                return;

            try
            {
                string response = await _agentService.SendLogAsync(
                    eventType,
                    message
                );

                Console.WriteLine($"Log response: {response}");
            }
            catch (Exception ex)
            {
                Console.WriteLine(
                    $"Log submission failed: {ex.Message}"
                );
            }
        }

        private async Task SendOfflineAsync()
        {
            try
            {
                Console.WriteLine("Sending offline request...");

                string response =
                    await _agentService.SendOfflineAsync();

                Console.WriteLine($"Offline response: {response}");
            }
            catch (Exception ex)
            {
                Console.WriteLine(
                    $"Offline request failed: {ex.Message}"
                );
            }
        }

        private void SetConnectionStatus(
            string status,
            bool connected)
        {
            ConnectionStatusText.Text = status;

            if (connected)
            {
                ConnectionStatusText.Foreground =
                    new SolidColorBrush(
                        Color.FromRgb(134, 239, 172)
                    );

                ConnectionStatusIndicator.Fill =
                    new SolidColorBrush(
                        Color.FromRgb(34, 197, 94)
                    );
            }
            else
            {
                ConnectionStatusText.Foreground =
                    new SolidColorBrush(
                        Color.FromRgb(252, 165, 165)
                    );

                ConnectionStatusIndicator.Fill =
                    new SolidColorBrush(
                        Color.FromRgb(239, 68, 68)
                    );
            }
        }

        private void LoadSystemInformation()
        {
            try
            {
                ComputerNameText.Text =
                    _systemInfoService.GetComputerName();

                OperatingSystemText.Text =
                    _systemInfoService.GetOperatingSystem();

                ArchitectureText.Text =
                    _systemInfoService.GetArchitecture();

                CpuText.Text =
                    _systemInfoService.GetCpuName();

                CpuCoresText.Text =
                    _systemInfoService.GetCpuCores().ToString();

                RamText.Text =
                    $"{_systemInfoService.GetTotalRamMb():N0} MB";

                DiskTotalText.Text =
                    $"{_systemInfoService.GetTotalDiskGb():N2} GB";

                DiskUsedText.Text =
                    $"{_systemInfoService.GetUsedDiskGb():N2} GB";

                DiskFreeText.Text =
                    $"{_systemInfoService.GetFreeDiskGb():N2} GB";
            }
            catch (Exception ex)
            {
                MessageBox.Show(
                    $"Failed to load system information.\n\n{ex.Message}",
                    "Secure Device Agent",
                    MessageBoxButton.OK,
                    MessageBoxImage.Error
                );
            }
        }

        private void MetricsTimer_Tick(
            object? sender,
            EventArgs e)
        {
            UpdateMetrics();
        }

        private void UpdateMetrics()
        {
            try
            {
                double cpuUsage = _metricsService.GetCpuUsage();
                double ramUsage = _metricsService.GetRamUsagePercent();
                long ramUsed = _metricsService.GetUsedRamMb();
                long ramTotal = _metricsService.GetTotalRamMb();
                double diskUsage = _metricsService.GetDiskUsagePercent();
                double diskUsed = _metricsService.GetDiskUsedGb();
                double diskTotal = _metricsService.GetDiskTotalGb();

                CpuUsageText.Text = $"{cpuUsage:F1}%";
                CpuProgressBar.Value = Math.Clamp(cpuUsage, 0, 100);

                RamUsageText.Text = $"{ramUsage:F1}%";
                RamProgressBar.Value = Math.Clamp(ramUsage, 0, 100);
                RamDetailsText.Text = $"{ramUsed:N0} MB / {ramTotal:N0} MB";

                DiskUsageText.Text = $"{diskUsage:F1}%";
                DiskProgressBar.Value = Math.Clamp(diskUsage, 0, 100);
                DiskDetailsText.Text = $"{diskUsed:N1} GB / {diskTotal:N1} GB";
            }
            catch (Exception ex)
            {
                Console.WriteLine($"Metrics update failed: {ex.Message}");
            }
        }

        protected override async void OnClosing(
            System.ComponentModel.CancelEventArgs e)
        {
            if (_isClosing)
            {
                base.OnClosing(e);
                return;
            }

            e.Cancel = true;
            _isClosing = true;

            Console.WriteLine("Agent is closing...");

            _metricsTimer.Stop();
            _heartbeatTimer.Stop();
            _serverMetricsTimer.Stop();
            _reconnectTimer.Stop();

            if (_agentInitialized)
            {
                Task offlineTask = SendOfflineAsync();
                Task timeoutTask = Task.Delay(TimeSpan.FromSeconds(3));

                await Task.WhenAny(offlineTask, timeoutTask);
            }

            Console.WriteLine("Agent closing completed.");

            base.OnClosing(e);
        }
    }
}
