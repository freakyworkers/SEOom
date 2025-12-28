<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class MasterMonitoringController extends Controller
{
    public function __construct()
    {
        $this->middleware(['web', 'auth:master']);
    }

    /**
     * Display monitoring dashboard.
     */
    public function index()
    {
        // Site statistics
        $siteStats = [
            'total' => Site::count(),
            'active' => Site::where('status', 'active')->count(),
            'suspended' => Site::where('status', 'suspended')->count(),
        ];

        // Database size (approximate)
        $dbSize = $this->getDatabaseSize();

        // Top sites by users
        $topSitesByUsers = Site::withCount('users')
            ->orderBy('users_count', 'desc')
            ->limit(10)
            ->get();

        // Top sites by posts
        $topSitesByPosts = Site::withCount('posts')
            ->orderBy('posts_count', 'desc')
            ->limit(10)
            ->get();

        // AWS EC2 Instance Info
        $awsInfo = $this->getAwsInstanceInfo();
        
        // Server Resources
        $serverResources = $this->getServerResources();

        return view('master.monitoring', compact('siteStats', 'dbSize', 'topSitesByUsers', 'topSitesByPosts', 'awsInfo', 'serverResources'));
    }

    /**
     * Get database size.
     */
    protected function getDatabaseSize()
    {
        try {
            $database = DB::connection()->getDatabaseName();
            $size = DB::select("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb 
                               FROM information_schema.tables 
                               WHERE table_schema = ?", [$database]);
            
            return $size[0]->size_mb ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get AWS EC2 Instance Info via Instance Metadata Service (IMDS)
     */
    protected function getAwsInstanceInfo()
    {
        // Cache the result for 5 minutes
        return Cache::remember('aws_instance_info', 300, function () {
            $info = [
                'instance_id' => null,
                'instance_type' => null,
                'region' => null,
                'availability_zone' => null,
                'public_ip' => null,
                'private_ip' => null,
                'monthly_cost_estimate' => null,
                'is_aws' => false,
            ];

            try {
                // EC2 Instance Metadata Service v2 (IMDSv2)
                $token = $this->getImdsToken();
                
                if ($token) {
                    $info['is_aws'] = true;
                    $info['instance_id'] = $this->getMetadata('instance-id', $token);
                    $info['instance_type'] = $this->getMetadata('instance-type', $token);
                    $info['availability_zone'] = $this->getMetadata('placement/availability-zone', $token);
                    $info['region'] = substr($info['availability_zone'], 0, -1);
                    $info['public_ip'] = $this->getMetadata('public-ipv4', $token);
                    $info['private_ip'] = $this->getMetadata('local-ipv4', $token);
                    
                    // Calculate estimated monthly cost based on instance type
                    $info['monthly_cost_estimate'] = $this->getEstimatedMonthlyCost($info['instance_type'], $info['region']);
                }
            } catch (\Exception $e) {
                // Not running on AWS or IMDS not available
                $info['error'] = $e->getMessage();
            }

            return $info;
        });
    }

    /**
     * Get IMDSv2 Token
     */
    protected function getImdsToken()
    {
        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => 'http://169.254.169.254/latest/api/token',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => ['X-aws-ec2-metadata-token-ttl-seconds: 21600'],
                CURLOPT_CUSTOMREQUEST => 'PUT',
                CURLOPT_TIMEOUT => 2,
                CURLOPT_CONNECTTIMEOUT => 1,
            ]);
            $token = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            return $httpCode === 200 ? $token : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get metadata from IMDS
     */
    protected function getMetadata($path, $token)
    {
        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => "http://169.254.169.254/latest/meta-data/{$path}",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => ["X-aws-ec2-metadata-token: {$token}"],
                CURLOPT_TIMEOUT => 2,
                CURLOPT_CONNECTTIMEOUT => 1,
            ]);
            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            return $httpCode === 200 ? $result : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get estimated monthly cost based on instance type
     * Prices are approximate on-demand prices in USD for ap-northeast-2 (Seoul)
     */
    protected function getEstimatedMonthlyCost($instanceType, $region = 'ap-northeast-2')
    {
        // Approximate hourly prices for common instance types in Seoul region (on-demand, Linux)
        $hourlyPrices = [
            // General Purpose
            't2.micro' => 0.0116,
            't2.small' => 0.0232,
            't2.medium' => 0.0464,
            't2.large' => 0.0928,
            't2.xlarge' => 0.1856,
            't3.micro' => 0.0104,
            't3.small' => 0.0208,
            't3.medium' => 0.0416,
            't3.large' => 0.0832,
            't3.xlarge' => 0.1664,
            't3a.micro' => 0.0094,
            't3a.small' => 0.0188,
            't3a.medium' => 0.0376,
            't3a.large' => 0.0752,
            'm5.large' => 0.096,
            'm5.xlarge' => 0.192,
            'm5.2xlarge' => 0.384,
            'm5a.large' => 0.086,
            'm5a.xlarge' => 0.172,
            'm6i.large' => 0.096,
            'm6i.xlarge' => 0.192,
            // Compute Optimized
            'c5.large' => 0.085,
            'c5.xlarge' => 0.170,
            'c5.2xlarge' => 0.340,
            'c6i.large' => 0.085,
            'c6i.xlarge' => 0.170,
            // Memory Optimized
            'r5.large' => 0.126,
            'r5.xlarge' => 0.252,
            'r5.2xlarge' => 0.504,
            'r6i.large' => 0.126,
            'r6i.xlarge' => 0.252,
        ];

        if (!$instanceType || !isset($hourlyPrices[$instanceType])) {
            return null;
        }

        // Calculate monthly cost (assuming 730 hours/month)
        $hourlyPrice = $hourlyPrices[$instanceType];
        $monthlyPrice = $hourlyPrice * 730;

        return [
            'hourly' => $hourlyPrice,
            'monthly' => round($monthlyPrice, 2),
            'monthly_krw' => round($monthlyPrice * 1400, 0), // Approximate USD to KRW
        ];
    }

    /**
     * Get server resource usage
     */
    protected function getServerResources()
    {
        $resources = [
            'cpu_usage' => null,
            'memory_total' => null,
            'memory_used' => null,
            'memory_percent' => null,
            'disk_total' => null,
            'disk_used' => null,
            'disk_percent' => null,
            'uptime' => null,
            'load_average' => null,
        ];

        try {
            // CPU Usage (Linux only)
            if (PHP_OS_FAMILY === 'Linux') {
                $load = sys_getloadavg();
                $resources['load_average'] = [
                    '1min' => round($load[0], 2),
                    '5min' => round($load[1], 2),
                    '15min' => round($load[2], 2),
                ];
                
                // Get CPU cores
                $cpuCores = (int) trim(shell_exec("nproc 2>/dev/null") ?: 1);
                $resources['cpu_cores'] = $cpuCores;
                $resources['cpu_usage'] = round(($load[0] / $cpuCores) * 100, 1);
            }

            // Memory Info (Linux only)
            if (PHP_OS_FAMILY === 'Linux' && file_exists('/proc/meminfo')) {
                $meminfo = file_get_contents('/proc/meminfo');
                preg_match('/MemTotal:\s+(\d+)/', $meminfo, $totalMatch);
                preg_match('/MemAvailable:\s+(\d+)/', $meminfo, $availMatch);
                
                if ($totalMatch && $availMatch) {
                    $totalKb = (int) $totalMatch[1];
                    $availKb = (int) $availMatch[1];
                    $usedKb = $totalKb - $availKb;
                    
                    $resources['memory_total'] = round($totalKb / 1024 / 1024, 2); // GB
                    $resources['memory_used'] = round($usedKb / 1024 / 1024, 2); // GB
                    $resources['memory_percent'] = round(($usedKb / $totalKb) * 100, 1);
                }
            }

            // Disk Usage
            $diskTotal = disk_total_space('/');
            $diskFree = disk_free_space('/');
            if ($diskTotal && $diskFree) {
                $diskUsed = $diskTotal - $diskFree;
                $resources['disk_total'] = round($diskTotal / 1024 / 1024 / 1024, 2); // GB
                $resources['disk_used'] = round($diskUsed / 1024 / 1024 / 1024, 2); // GB
                $resources['disk_percent'] = round(($diskUsed / $diskTotal) * 100, 1);
            }

            // Uptime (Linux only)
            if (PHP_OS_FAMILY === 'Linux' && file_exists('/proc/uptime')) {
                $uptime = file_get_contents('/proc/uptime');
                $uptimeSeconds = (int) explode(' ', $uptime)[0];
                $days = floor($uptimeSeconds / 86400);
                $hours = floor(($uptimeSeconds % 86400) / 3600);
                $minutes = floor(($uptimeSeconds % 3600) / 60);
                $resources['uptime'] = "{$days}일 {$hours}시간 {$minutes}분";
                $resources['uptime_seconds'] = $uptimeSeconds;
            }
        } catch (\Exception $e) {
            // Ignore errors
        }

        return $resources;
    }
}

