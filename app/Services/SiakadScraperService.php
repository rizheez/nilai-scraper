<?php

namespace App\Services;

use App\Models\Configs;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\PendingRequest;

class SiakadScraperService
{
    protected string $baseUrl;
    protected string $cookie = '';
    protected array $userAgents;

    public function __construct()
    {
        $this->baseUrl = config('services.siakad.base_url');
        $this->userAgents = [
            'Mozilla/5.0 (X11; Ubuntu; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36',
            'Mozilla/5.0 (X11; Ubuntu; Linux x86_64) Gecko/20100101 Firefox/130.0',
            'Mozilla/5.0 (X11; Arch Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.6065.0 Safari/537.36',
            'Mozilla/5.0 (X11; Arch Linux x86_64; rv:129.0) Gecko/20100101 Firefox/129.0',
            'Mozilla/5.0 (X11; Fedora; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36',
            'Mozilla/5.0 (X11; Fedora; Linux x86_64; rv:128.0) Gecko/20100101 Firefox/128.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 13_5_1) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.7 Safari/605.1.15',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 13_5_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36',
        ];
    }

    protected function getHttpClient(): PendingRequest
    {
        $client = Http::withHeaders([
            'User-Agent' => $this->userAgents[array_rand($this->userAgents)],
            'X-Requested-With' => 'XMLHttpRequest',
            'Referer' => $this->baseUrl . '/media.php',
            'Origin' => $this->baseUrl,
            'Accept' => 'application/json, text/javascript, */*; q=0.01',
        ])->timeout(120)->retry(3, 5000);

        if ($this->cookie) {
            $client = $client->withHeaders(['Cookie' => $this->cookie]);
        }

        return $client;
    }

    public function login(string $username, string $password): bool
    {
        try {
            // Get initial session
            $response = Http::get($this->baseUrl . '/index.php');
            $sessionCookie = $this->extractSessionCookie($response->headers());
            // dd($sessionCookie);
            if (!$sessionCookie) {
                Log::error('Failed to get session cookie');
                return false;
            }

            // Prepare login data
            $hideValidation = $this->generateValidation();
            $hideIp = $this->getRandomIp();

            Configs::set('username_siakad', $username);
            Configs::set('password_siakad', $password, true);

            $loginData = [
                'username' => $username,
                'password' => $password,
                'validation' => $hideValidation,
                'hide_validation' => $hideValidation,
                'hide_ipnya' => $hideIp,
            ];

            // Perform login
            $response = Http::withHeaders([
                'User-Agent' => $this->userAgents[array_rand($this->userAgents)],
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Cookie' => $sessionCookie,
                'X-Requested-With' => 'XMLHttpRequest',
            ])->asForm()->post($this->baseUrl . '/ceklogin.php?h=', $loginData);

            $loginCookie = $this->extractSessionCookie($response->headers());
            if ($loginCookie) {
                $this->cookie = $loginCookie;
            }

            $responseData = $response->json();
            return isset($responseData['success']) && $responseData['success'] === true;
        } catch (\Exception $e) {
            Log::error('Login failed: ' . $e->getMessage());
            return false;
        }
    }

    public function isSessionValid(): bool
    {
        try {
            $response = $this->getHttpClient()->get($this->baseUrl . '/media.php');
            $body = $response->body();

            return !(str_contains($body, "window.location = 'index.php'") ||
                str_contains($body, 'login') ||
                str_contains($body, 'Username'));
        } catch (\Exception $e) {
            Log::error('Session validation failed: ' . $e->getMessage());
            return false;
        }
    }

    public function getSemesters(): array
    {
        try {
            $response = $this->getHttpClient()->post($this->baseUrl . '/_modul/aksi_umum.php?act=pilih_smtthnakd');
            return $response->json() ?: [];
        } catch (\Exception $e) {
            Log::error('Failed to get semesters: ' . $e->getMessage());
            return [];
        }
    }

    public function setProdi(string $kodeProdi, string $kodePk, string $smthn): bool
    {
        try {
            $data = [
                'ps' => $kodeProdi,
                'pk' => $kodePk,
                'smthn' => $smthn,
            ];

            $response = $this->getHttpClient()
                ->asForm()
                ->post($this->baseUrl . '/_modul/mod_prodi_smthn/aksi_prodi_smthn.php', $data);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Failed to set prodi: ' . $e->getMessage());
            return false;
        }
    }

    public function getRekapMataKuliah(): array
    {
        try {
            $data = 'page=1&rows=300&sort=hari&order=asc';

            $response = $this->getHttpClient()
                ->withBody($data, 'application/x-www-form-urlencoded')
                ->post($this->baseUrl . '/_modul/mod_nilmk/aksi_nilmk.php?act=rekapNILMK');

            $result = $response->json();
            return $result['rows'] ?? [];
        } catch (\Exception $e) {
            Log::error('Failed to get mata kuliah recap: ' . $e->getMessage());
            return [];
        }
    }

    public function getRekapMahasiswa(): array
    {
        try {
            $data = 'page=1&rows=500&';

            $response = $this->getHttpClient()
                ->withBody($data, 'application/x-www-form-urlencoded')
                ->post($this->baseUrl . '/_modul/mod_datamhs/aksi_datamhs.php?act=list');

            $result = $response->json();
            return $result['rows'] ?? [];
        } catch (\Exception $e) {
            Log::error('Failed to get mahasiswa recap: ' . $e->getMessage());
            return [];
        }
    }

    public function getListNilai(string $infomk): array
    {
        try {
            $data = [
                'param' => $infomk,
                'cetak' => '1',
            ];

            $response = $this->getHttpClient()
                ->asForm()
                ->post($this->baseUrl . '/_modul/mod_nilmk/aksi_nilmk.php?act=listNILMK', $data);

            return $response->json() ?: [];
        } catch (\Exception $e) {
            Log::error('Failed to get nilai list: ' . $e->getMessage());
            return [];
        }
    }

    public function getBobotMataKuliah(string $fak, string $kodeProdi, string $kodePk, string $kelas, string $kodeMk): array
    {
        try {
            $data = [
                'fak' => $fak,
                'jrs' => $kodeProdi,
                'prg' => $kodePk,
                'kls' => $kelas,
                'kmk' => $kodeMk,
            ];

            $response = $this->getHttpClient()
                ->asForm()
                ->post($this->baseUrl . '/_modul/mod_nilmk/aksi_nilmk.php?act=loadBOBOT', $data);

            return $response->json() ?: [];
        } catch (\Exception $e) {
            Log::error('Failed to get bobot mata kuliah: ' . $e->getMessage());
            return [];
        }
    }

    protected function extractSessionCookie(array $headers): ?string
    {
        if (!isset($headers['Set-Cookie'])) {
            return null;
        }

        $cookies = is_array($headers['Set-Cookie']) ? $headers['Set-Cookie'] : [$headers['Set-Cookie']];

        foreach ($cookies as $cookie) {
            if (str_contains($cookie, 'PHPSESSID=')) {
                preg_match('/PHPSESSID=([^;]+)/', $cookie, $matches);
                if (isset($matches[1])) {
                    return 'PHPSESSID=' . $matches[1];
                }
            }
        }

        return null;
    }

    protected function generateValidation(): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        return substr(str_shuffle($chars), 0, 3);
    }

    protected function getRandomIp(): string
    {
        try {
            $response = Http::timeout(5)->get('https://api.ipify.org');
            return $response->successful() ? trim($response->body()) : '182.8.179.9';
        } catch (\Exception $e) {
            return '182.8.179.9';
        }
    }

    public function setCookie(string $cookie): void
    {
        $this->cookie = $cookie;
    }

    public function getCookie(): string
    {
        return $this->cookie;
    }
}
