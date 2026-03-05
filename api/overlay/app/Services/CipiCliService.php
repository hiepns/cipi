<?php

namespace App\Services;

class CipiCliService
{
    public function run(string $command): array
    {
        $fullCommand = 'sudo /usr/local/bin/cipi ' . $command . ' 2>&1';
        $output = [];
        exec($fullCommand, $output, $exitCode);
        return [
            'output' => implode("\n", $output),
            'exit_code' => $exitCode,
            'success' => $exitCode === 0,
        ];
    }

    public function appList(): array
    {
        return $this->run('app list');
    }

    public function appShow(string $name): array
    {
        return $this->run('app show ' . escapeshellarg($name));
    }

    public function appCreate(array $params): array
    {
        $args = ['app create'];
        foreach ($params as $k => $v) {
            if ($v !== null && $v !== '') {
                $args[] = '--' . $k . '=' . escapeshellarg((string) $v);
            }
        }
        return $this->run(implode(' ', $args));
    }

    public function appEdit(string $name, array $params): array
    {
        $args = ['app edit', escapeshellarg($name)];
        foreach ($params as $k => $v) {
            if ($v !== null && $v !== '') {
                $args[] = '--' . $k . '=' . escapeshellarg((string) $v);
            }
        }
        return $this->run(implode(' ', $args));
    }

    public function appDelete(string $name): array
    {
        return $this->run('app delete ' . escapeshellarg($name) . ' --force');
    }

    public function aliasList(string $app): array
    {
        return $this->run('alias list ' . escapeshellarg($app));
    }

    public function aliasAdd(string $app, string $alias): array
    {
        return $this->run('alias add ' . escapeshellarg($app) . ' ' . escapeshellarg($alias));
    }

    public function aliasRemove(string $app, string $alias): array
    {
        return $this->run('alias remove ' . escapeshellarg($app) . ' ' . escapeshellarg($alias));
    }

    public function sslInstall(string $app): array
    {
        return $this->run('ssl install ' . escapeshellarg($app));
    }
}
